/**
 * DTE VirtualLab V2 — Professional Schematic Orthogonal Wiring & Magnetic Snapping Engine
 */

import { stateManager } from "./state.js";

let connectionCounter = 1;

export class ConnectionEngine {
  /**
   * @param {import("./workspace.js").WorkspaceEngine} workspaceEngine 
   */
  constructor(workspaceEngine) {
    this.workspace = workspaceEngine;
    this.svgLayer = document.getElementById("svg-cable-layer");
    this.wiresGroup = document.getElementById("wires-group");
    this.wirePreview = document.getElementById("wire-preview");
    this.canvasLayer = document.getElementById("canvas-layer");

    this.isConnecting = false;
    this.sourceTerminal = null;
    this.waypoints = [];
    this.currentMousePos = { x: 0, y: 0 };
    this.hoveredTerminal = null; // { compId, termId, pos: {x, y}, el }
    this.snapTarget = null;      // { conn, point: {x, y} }
    this.snapIndicator = null;
    this.floatingToolbar = null;
  }

  init() {
    this.createSnapIndicator();
    this.bindTerminalEvents();
    this.bindCanvasWiringEvents();
    this.bindKeyboardEvents();

    stateManager.subscribe("connections", () => this.renderWires());
    stateManager.subscribe("components", () => this.renderWires());
    stateManager.subscribe("components_moving", () => this.renderWires());
    stateManager.subscribe("selection", () => {
      this.updateSelectionVisuals();
      this.renderFloatingToolbar();
    });
  }

  createSnapIndicator() {
    if (!this.svgLayer) return;
    const snapCircle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
    snapCircle.setAttribute("id", "wire-snap-indicator");
    snapCircle.setAttribute("r", "7");
    snapCircle.setAttribute("fill", "#00c853");
    snapCircle.setAttribute("stroke", "#ffffff");
    snapCircle.setAttribute("stroke-width", "2.5");
    snapCircle.style.display = "none";
    snapCircle.style.pointerEvents = "none";
    this.svgLayer.appendChild(snapCircle);
    this.snapIndicator = snapCircle;
  }

  bindTerminalEvents() {
    const compLayer = document.getElementById("components-layer");
    if (!compLayer) return;

    const onPointerDown = (e) => {
      const termEl = e.target.closest(".terminal-node");
      if (!termEl) return;

      e.stopPropagation();
      e.preventDefault();
      const compId = termEl.getAttribute("data-comp-id");
      const termId = termEl.getAttribute("data-term-id");

      this.handleTerminalClick(compId, termId, termEl);
    };

    compLayer.addEventListener("pointerdown", onPointerDown, { passive: false });
  }

  bindCanvasWiringEvents() {
    const onPointerMove = (e) => {
      if (!this.isConnecting || !this.sourceTerminal) return;

      const rawPos = this.workspace.screenToCanvas(e.clientX, e.clientY);
      
      // 1. Check Magnetic Snapping to any Component Terminal (radius 32px for touch accessibility)
      const termSnap = this.findNearestTerminalSnap(rawPos.x, rawPos.y, 32);
      
      // Remove previous terminal hover highlights
      document.querySelectorAll(".terminal-node.snap-hover").forEach(el => el.classList.remove("snap-hover"));

      if (termSnap) {
        this.hoveredTerminal = termSnap;
        this.snapTarget = null;
        this.currentMousePos = termSnap.pos;
        termSnap.el.classList.add("snap-hover");

        if (this.snapIndicator) {
          this.snapIndicator.setAttribute("cx", termSnap.pos.x);
          this.snapIndicator.setAttribute("cy", termSnap.pos.y);
          this.snapIndicator.style.display = "block";
        }
      } else {
        this.hoveredTerminal = null;

        // 2. Check Snapping to an existing Wire Branch (radius 24px)
        const wireSnap = this.findNearestWireSnap(rawPos.x, rawPos.y, 24);
        if (wireSnap) {
          this.snapTarget = wireSnap;
          this.currentMousePos = wireSnap.point;
          if (this.snapIndicator) {
            this.snapIndicator.setAttribute("cx", wireSnap.point.x);
            this.snapIndicator.setAttribute("cy", wireSnap.point.y);
            this.snapIndicator.style.display = "block";
          }
        } else {
          this.snapTarget = null;
          this.currentMousePos = rawPos;
          if (this.snapIndicator) {
            this.snapIndicator.style.display = "none";
          }
        }
      }

      this.drawWirePreview();
    };

    window.addEventListener("pointermove", onPointerMove);
    window.addEventListener("touchmove", (e) => {
      if (e.touches && e.touches.length > 0) {
        onPointerMove(e.touches[0]);
      }
    }, { passive: true });

    const container = this.workspace.container;
    if (container) {
      container.addEventListener("pointerdown", (e) => {
        if (!this.isConnecting) return;

        // If clicking terminal directly, handled in bindTerminalEvents
        if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge")) {
          return;
        }

        const isLeftOrTouch = e.button === 0 || e.pointerType === "touch" || e.button === undefined;

        // 1. If snapped to a valid terminal -> FINISH CONNECTION INSTANTLY!
        if (this.hoveredTerminal && isLeftOrTouch) {
          e.stopPropagation();
          const target = this.hoveredTerminal;
          this.createConnection(
            { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
            { componentId: target.compId, terminalId: target.termId },
            [...this.waypoints]
          );
          this.cancelConnecting();
          return;
        }

        // 2. Also check direct snap at click position (within 36px for easy touch)
        const rawPos = this.workspace.screenToCanvas(e.clientX, e.clientY);
        const nearSnap = this.findNearestTerminalSnap(rawPos.x, rawPos.y, 36);
        if (nearSnap && isLeftOrTouch) {
          e.stopPropagation();
          this.createConnection(
            { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
            { componentId: nearSnap.compId, terminalId: nearSnap.termId },
            [...this.waypoints]
          );
          this.cancelConnecting();
          return;
        }

        // 3. If snapped to an existing wire -> FINISH CLEAN BRANCH!
        if (this.snapTarget && isLeftOrTouch) {
          e.stopPropagation();
          this.finishJunctionConnection(this.snapTarget.conn, this.snapTarget.point);
          return;
        }

        // 4. Left Click on empty canvas -> add 90° corner waypoint
        if (isLeftOrTouch) {
          this.addWaypoint(rawPos.x, rawPos.y);
        } else if (e.button === 2) {
          this.cancelConnecting();
        }
      });
    }

    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.isConnecting) {
        this.cancelConnecting();
      }
    });

    window.addEventListener("contextmenu", (e) => {
      if (this.isConnecting) {
        e.preventDefault();
        this.cancelConnecting();
      }
    });
  }

  bindKeyboardEvents() {
    window.addEventListener("keydown", (e) => {
      const state = stateManager.getState();
      if ((e.key === "Delete" || e.key === "Backspace") && state.selection.type === "connection") {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement.tagName)) return;
        this.deleteConnection(state.selection.id);
      }
    });
  }

  handleTerminalClick(compId, termId, termEl) {
    const termPos = this.getTerminalWorldPosition(compId, termId);
    if (!termPos) return;

    if (!this.isConnecting) {
      this.isConnecting = true;
      this.waypoints = [];
      this.snapTarget = null;
      this.hoveredTerminal = null;
      this.sourceTerminal = {
        componentId: compId,
        terminalId: termId,
        worldX: termPos.x,
        worldY: termPos.y
      };

      termEl.classList.add("connecting-source");
      if (this.wirePreview) {
        this.wirePreview.style.display = "block";
        this.currentMousePos = { x: termPos.x, y: termPos.y };
        this.drawWirePreview();
      }
    } else {
      const source = this.sourceTerminal;

      // Cancel if clicked the same terminal
      if (source.componentId === compId && source.terminalId === termId) {
        this.cancelConnecting();
        return;
      }

      this.createConnection(
        { componentId: source.componentId, terminalId: source.terminalId },
        { componentId: compId, terminalId: termId },
        [...this.waypoints]
      );

      this.cancelConnecting();
    }
  }

  finishJunctionConnection(targetConn, clickPos) {
    const source = this.sourceTerminal;
    if (!source) return;

    this.createConnection(
      { componentId: source.componentId, terminalId: source.terminalId },
      { 
        componentId: targetConn.from.componentId, 
        terminalId: targetConn.from.terminalId,
        isWireBranch: true,
        targetWireId: targetConn.id,
        junctionPoint: { x: clickPos.x, y: clickPos.y }
      },
      [...this.waypoints]
    );

    this.cancelConnecting();
  }

  findNearestTerminalSnap(mouseX, mouseY, tolerance = 24) {
    const state = stateManager.getState();
    let bestSnap = null;
    let minDistance = tolerance;

    state.components.forEach(comp => {
      comp.terminals.forEach(term => {
        // Do not snap to the source terminal itself
        if (this.sourceTerminal && comp.id === this.sourceTerminal.componentId && term.id === this.sourceTerminal.terminalId) {
          return;
        }

        const pos = this.getTerminalWorldPosition(comp.id, term.id);
        if (!pos) return;

        const dist = Math.hypot(mouseX - pos.x, mouseY - pos.y);
        if (dist < minDistance) {
          minDistance = dist;
          const el = document.getElementById(`term-${comp.id}-${term.id}`);
          bestSnap = {
            compId: comp.id,
            termId: term.id,
            pos: pos,
            el: el
          };
        }
      });
    });

    return bestSnap;
  }

  findNearestWireSnap(mouseX, mouseY, tolerance = 20) {
    const state = stateManager.getState();
    let bestMatch = null;
    let minDistance = tolerance;

    state.connections.forEach(conn => {
      const p1 = this.getConnectionEndpoint(conn.from);
      const p2 = this.getConnectionEndpoint(conn.to);
      if (!p1 || !p2) return;

      const polyPoints = this.getPolylinePoints(p1, p2, conn.waypoints);

      for (let i = 0; i < polyPoints.length - 1; i++) {
        const a = polyPoints[i];
        const b = polyPoints[i + 1];

        const closest = this.getClosestPointOnSegment(a, b, { x: mouseX, y: mouseY });
        const dist = Math.hypot(mouseX - closest.x, mouseY - closest.y);

        if (dist < minDistance) {
          minDistance = dist;
          bestMatch = {
            conn,
            point: closest
          };
        }
      }
    });

    return bestMatch;
  }

  getClosestPointOnSegment(a, b, p) {
    const abx = b.x - a.x;
    const aby = b.y - a.y;
    const lengthSq = abx * abx + aby * aby;

    if (lengthSq === 0) return { x: a.x, y: a.y };

    let t = ((p.x - a.x) * abx + (p.y - a.y) * aby) / lengthSq;
    t = Math.max(0, Math.min(1, t));

    const px = a.x === b.x ? a.x : (a.x + t * abx);
    const py = a.y === b.y ? a.y : (a.y + t * aby);

    return { x: px, y: py };
  }

  /**
   * Smart CAD Schematic Manhattan Routing
   * Produces clean horizontal/vertical orthogonal paths with zero backward looping
   */
  getPolylinePoints(p1, p2, waypoints) {
    const points = [p1];

    if (waypoints && waypoints.length > 0) {
      points.push(...waypoints);
    } else {
      const dx = p2.x - p1.x;
      const dy = p2.y - p1.y;

      // 1. Direct Straight Alignment
      if (Math.abs(dy) <= 2) {
        points.push(p2);
        return points;
      }
      if (Math.abs(dx) <= 2) {
        points.push(p2);
        return points;
      }

      // 2. Symmetrical Manhattan Routing (no backward loops)
      if (Math.abs(dx) >= Math.abs(dy)) {
        if (Math.abs(dx) > 20) {
          const midX = Math.round((p1.x + p2.x) / 2);
          points.push({ x: midX, y: p1.y });
          points.push({ x: midX, y: p2.y });
        } else {
          points.push({ x: p2.x, y: p1.y });
        }
      } else {
        if (Math.abs(dy) > 20) {
          const midY = Math.round((p1.y + p2.y) / 2);
          points.push({ x: p1.x, y: midY });
          points.push({ x: p2.x, y: midY });
        } else {
          points.push({ x: p1.x, y: p2.y });
        }
      }
    }

    points.push(p2);
    return points;
  }

  addWaypoint(x, y) {
    const lastPoint = this.waypoints.length > 0 
      ? this.waypoints[this.waypoints.length - 1] 
      : { x: this.sourceTerminal.worldX, y: this.sourceTerminal.worldY };

    const dx = Math.abs(x - lastPoint.x);
    const dy = Math.abs(y - lastPoint.y);

    let wpX = x;
    let wpY = y;

    if (dx > dy) {
      wpY = lastPoint.y;
    } else {
      wpX = lastPoint.x;
    }

    this.waypoints.push({ x: wpX, y: wpY });
    this.drawWirePreview();
  }

  cancelConnecting() {
    this.isConnecting = false;
    this.sourceTerminal = null;
    this.waypoints = [];
    this.snapTarget = null;
    this.hoveredTerminal = null;
    
    if (this.snapIndicator) {
      this.snapIndicator.style.display = "none";
    }

    document.querySelectorAll(".terminal-node.connecting-source").forEach(el => {
      el.classList.remove("connecting-source");
    });
    document.querySelectorAll(".terminal-node.snap-hover").forEach(el => {
      el.classList.remove("snap-hover");
    });

    if (this.wirePreview) {
      this.wirePreview.style.display = "none";
    }
  }

  createConnection(from, to, waypoints = []) {
    const state = stateManager.getState();

    // Prevent identical duplicated wire
    const duplicate = state.connections.some(c => 
      (c.from.componentId === from.componentId && c.from.terminalId === from.terminalId &&
       c.to.componentId === to.componentId && c.to.terminalId === to.terminalId) ||
      (c.from.componentId === to.componentId && c.from.terminalId === to.terminalId &&
       c.to.componentId === from.componentId && c.to.terminalId === from.terminalId)
    );
    if (duplicate) return;

    const id = `conn-${String(connectionCounter++).padStart(3, "0")}`;
    const newConn = {
      id,
      from,
      to,
      color: "#f88c00",
      waypoints: waypoints.length > 0 ? waypoints : null
    };

    stateManager.recordHistory();
    state.connections.push(newConn);
    stateManager.setSelection("connection", id);
    stateManager.notify("connections");
  }

  deleteConnection(id) {
    const state = stateManager.getState();
    stateManager.recordHistory();
    state.connections = state.connections.filter(c => 
      c.id !== id && c.to?.targetWireId !== id && c.from?.targetWireId !== id
    );
    if (state.selection.id === id) {
      stateManager.setSelection(null, null);
    }
    stateManager.notify("connections");
  }

  getConnectionEndpoint(endpoint) {
    if (!endpoint) return null;
    if (endpoint.isWireBranch && endpoint.targetWireId) {
      const state = stateManager.getState();
      const hostWire = state.connections.find(c => c.id === endpoint.targetWireId);
      if (hostWire) {
        const hp1 = this.getConnectionEndpoint(hostWire.from);
        const hp2 = this.getConnectionEndpoint(hostWire.to);
        if (hp1 && hp2) {
          const poly = this.getPolylinePoints(hp1, hp2, hostWire.waypoints);
          let closest = endpoint.junctionPoint;
          let minDist = Infinity;
          for (let i = 0; i < poly.length - 1; i++) {
            const pt = this.getClosestPointOnSegment(poly[i], poly[i + 1], endpoint.junctionPoint);
            const dist = Math.hypot(endpoint.junctionPoint.x - pt.x, endpoint.junctionPoint.y - pt.y);
            if (dist < minDist) {
              minDist = dist;
              closest = pt;
            }
          }
          return closest;
        }
      }
      return endpoint.junctionPoint;
    }
    return this.getTerminalWorldPosition(endpoint.componentId, endpoint.terminalId);
  }

  getTerminalWorldPosition(compId, termId) {
    const state = stateManager.getState();
    const comp = state.components.find(c => c.id === compId);
    if (!comp) return null;

    const term = comp.terminals.find(t => t.id === termId);
    if (!term) return null;

    const rotation = comp.rotation || 0;
    const cx = comp.width / 2;
    const cy = comp.height / 2;

    const dx = term.relX - cx;
    const dy = term.relY - cy;

    const rad = (rotation * Math.PI) / 180;
    const cos = Math.cos(rad);
    const sin = Math.sin(rad);

    const rotX = dx * cos - dy * sin;
    const rotY = dx * sin + dy * cos;

    return {
      x: Math.round(comp.x + cx + rotX),
      y: Math.round(comp.y + cy + rotY)
    };
  }

  computeOrthogonalPath(p1, p2, waypoints = []) {
    if (!p1 || !p2) return "";

    const points = this.getPolylinePoints(p1, p2, waypoints);

    let d = `M ${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
      d += ` L ${points[i].x} ${points[i].y}`;
    }

    return d;
  }

  drawWirePreview() {
    if (!this.wirePreview || !this.sourceTerminal) return;

    const p1 = { x: this.sourceTerminal.worldX, y: this.sourceTerminal.worldY };
    const p2 = this.currentMousePos;

    this.wirePreview.setAttribute("d", this.computeOrthogonalPath(p1, p2, this.waypoints));
  }

  renderWires() {
    if (!this.wiresGroup) return;
    this.wiresGroup.innerHTML = "";

    document.querySelectorAll(".wire-handle").forEach(h => h.remove());

    const state = stateManager.getState();
    const isSimRunning = state.simulation.running;
    const isShortCircuit = state.simulation.status === "SHORT_CIRCUIT";
    const isClosedCircuit = state.simulation.status === "OK";
    const connectedTerminals = new Set();

    state.connections.forEach((conn) => {
      const p1 = this.getConnectionEndpoint(conn.from);
      const p2 = this.getConnectionEndpoint(conn.to);
      if (!p1 || !p2) return;

      if (!conn.from.isWireBranch) connectedTerminals.add(`${conn.from.componentId}-${conn.from.terminalId}`);
      if (!conn.to.isWireBranch) connectedTerminals.add(`${conn.to.componentId}-${conn.to.terminalId}`);

      const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
      path.setAttribute("id", `wire-${conn.id}`);
      path.setAttribute("d", this.computeOrthogonalPath(p1, p2, conn.waypoints, conn.from, conn.to));
      
      let classes = "circuit-wire orthogonal";
      if (state.selection.id === conn.id) classes += " selected";
      
      if (isSimRunning) {
        if (isShortCircuit) {
          classes += " short-circuit";
        } else if (isClosedCircuit) {
          classes += " active";
        }
      }
      path.setAttribute("class", classes);

      this.bindDirectWireDrag(path, conn, p1, p2);

      path.addEventListener("dblclick", (e) => {
        e.stopPropagation();
        this.deleteConnection(conn.id);
      });

      this.wiresGroup.appendChild(path);

      if (state.selection.type === "connection" && state.selection.id === conn.id) {
        this.renderOrthogonalHandles(conn, p1, p2);
      }
    });

    // Update connected styling on terminal DOM nodes
    document.querySelectorAll(".terminal-node").forEach(el => {
      const cId = el.getAttribute("data-comp-id");
      const tId = el.getAttribute("data-term-id");
      if (connectedTerminals.has(`${cId}-${tId}`)) {
        el.classList.add("connected");
      } else {
        el.classList.remove("connected");
      }
    });
  }

  bindDirectWireDrag(pathEl, conn, p1, p2) {
    let startX = 0, startY = 0;
    let hasMoved = false;

    pathEl.addEventListener("pointerdown", (e) => {
      e.stopPropagation();
      stateManager.setSelection("connection", conn.id);

      startX = e.clientX;
      startY = e.clientY;
      hasMoved = false;

      const onMove = (moveEv) => {
        const dx = (moveEv.clientX - startX) / this.workspace.zoom;
        const dy = (moveEv.clientY - startY) / this.workspace.zoom;

        if (Math.abs(dx) > 3 || Math.abs(dy) > 3) {
          hasMoved = true;
        }

        if (hasMoved) {
          const mousePos = this.workspace.screenToCanvas(moveEv.clientX, moveEv.clientY);
          conn.waypoints = [
            { x: Math.round(mousePos.x), y: Math.round(mousePos.y) }
          ];

          pathEl.setAttribute("d", this.computeOrthogonalPath(p1, p2, conn.waypoints, conn.from, conn.to));
        }
      };

      const onUp = () => {
        window.removeEventListener("pointermove", onMove);
        window.removeEventListener("pointerup", onUp);

        if (hasMoved) {
          stateManager.notify("connections");
        }
      };

      window.addEventListener("pointermove", onMove);
      window.addEventListener("pointerup", onUp);
    });
  }

  renderOrthogonalHandles(conn, p1, p2) {
    const midX = conn.waypoints && conn.waypoints[0] ? conn.waypoints[0].x : (p1.x + p2.x) / 2;
    const midY = conn.waypoints && conn.waypoints[0] ? conn.waypoints[0].y : (p1.y + p2.y) / 2;

    const handle = document.createElement("div");
    handle.className = "wire-handle orthogonal-handle";
    handle.style.left = `${midX}px`;
    handle.style.top = `${midY}px`;
    handle.title = "Tarik untuk memindahkan jalur kabel (Terminal tetap tersambung)";

    let startX = 0, startY = 0;
    let initX = midX, initY = midY;

    handle.addEventListener("pointerdown", (e) => {
      e.stopPropagation();
      startX = e.clientX;
      startY = e.clientY;
      initX = midX;
      initY = midY;

      const onMove = (mv) => {
        const dx = (mv.clientX - startX) / this.workspace.zoom;
        const dy = (mv.clientY - startY) / this.workspace.zoom;
        const newX = Math.round(initX + dx);
        const newY = Math.round(initY + dy);

        conn.waypoints = [{ x: newX, y: newY }];
        handle.style.left = `${newX}px`;
        handle.style.top = `${newY}px`;

        const path = document.getElementById(`wire-${conn.id}`);
        if (path) {
          path.setAttribute("d", this.computeOrthogonalPath(p1, p2, conn.waypoints, conn.from, conn.to));
        }
      };

      const onUp = () => {
        window.removeEventListener("pointermove", onMove);
        window.removeEventListener("pointerup", onUp);
        stateManager.notify("connections");
      };

      window.addEventListener("pointermove", onMove);
      window.addEventListener("pointerup", onUp);
    });

    const compLayer = document.getElementById("components-layer");
    if (compLayer) compLayer.appendChild(handle);
  }

  renderFloatingToolbar() {
    if (this.floatingToolbar) {
      this.floatingToolbar.remove();
      this.floatingToolbar = null;
    }

    const state = stateManager.getState();
    if (state.selection.type !== "connection" || !state.selection.id) return;

    const conn = state.connections.find(c => c.id === state.selection.id);
    if (!conn) return;

    const p1 = this.getConnectionEndpoint(conn.from);
    const p2 = this.getConnectionEndpoint(conn.to);
    if (!p1 || !p2) return;

    const midX = conn.waypoints && conn.waypoints[0] ? conn.waypoints[0].x : (p1.x + p2.x) / 2;
    const midY = conn.waypoints && conn.waypoints[0] ? conn.waypoints[0].y : (p1.y + p2.y) / 2;

    const tb = document.createElement("div");
    tb.className = "wire-floating-toolbar";
    tb.style.left = `${midX}px`;
    tb.style.top = `${midY - 24}px`;

    tb.innerHTML = `
      <button class="btn-cut-wire" id="btn-cut-wire-action">
        <span>✂️</span> Potong Kabel
      </button>
    `;

    tb.querySelector("#btn-cut-wire-action").addEventListener("click", (e) => {
      e.stopPropagation();
      this.deleteConnection(conn.id);
    });

    const compLayer = document.getElementById("components-layer");
    if (compLayer) compLayer.appendChild(tb);
    this.floatingToolbar = tb;
  }

  updateSelectionVisuals() {
    const state = stateManager.getState();
    document.querySelectorAll(".circuit-wire").forEach(w => w.classList.remove("selected"));

    if (state.selection.type === "connection" && state.selection.id) {
      const selectedWire = document.getElementById(`wire-${state.selection.id}`);
      if (selectedWire) selectedWire.classList.add("selected");
    }
  }
}
