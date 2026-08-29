/**
 * DTE VirtualLab V2 — Professional Schematic Orthogonal Wiring & Magnetic Snapping Engine
 * Supports Mobile Touch Drag-and-Drop, Touch/Click Select-to-Connect & Proteus-Style Cut-Wire
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
    this.sourceHangingWire = null;
    this.waypoints = [];
    this.currentMousePos = { x: 0, y: 0 };
    this.hoveredTerminal = null; // { compId, termId, pos: {x, y}, el }
    this.snapTarget = null;      // { conn, point: {x, y} }
    this.snapIndicator = null;
    this.floatingToolbar = null;

    // Endpoint Lifting / Detaching / Re-dragging State
    this.isReconnectingEndpoint = false;
    this.reconnectingConn = null;
    this.detachedEnd = null;     // "from" | "to"
    this.originalTarget = null;

    // Mobile & Touch Gesture Tracking
    this.isDraggingWire = false;
    this.dragHasMoved = false;
    this.dragStartCoords = null;
    this.lastTapTime = 0;
    this.lastTapPos = { x: 0, y: 0 };
    this.lastConnectionFinishTime = 0;
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
    snapCircle.setAttribute("r", "8");
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

    const onTerminalTouchStart = (e) => {
      const termEl = e.target.closest(".terminal-node");
      if (!termEl) return;

      e.stopPropagation();
      if (e.cancelable) e.preventDefault();
      const compId = termEl.getAttribute("data-comp-id");
      const termId = termEl.getAttribute("data-term-id");

      this.handleTerminalClick(compId, termId, termEl, e);
    };

    const onPointerDown = (e) => {
      if (e.pointerType === "touch") return; // Handled by touchstart
      const termEl = e.target.closest(".terminal-node");
      if (!termEl) return;

      e.stopPropagation();
      if (e.cancelable) e.preventDefault();
      const compId = termEl.getAttribute("data-comp-id");
      const termId = termEl.getAttribute("data-term-id");

      this.handleTerminalClick(compId, termId, termEl, e);
    };

    compLayer.addEventListener("touchstart", onTerminalTouchStart, { passive: false });
    compLayer.addEventListener("pointerdown", onPointerDown, { passive: false });
  }

  bindCanvasWiringEvents() {
    const onPointerMove = (e) => {
      if (!this.isConnecting || !this.sourceTerminal) return;
      if (e.cancelable) e.preventDefault();

      const clientX = e.clientX ?? (e.touches && e.touches[0] ? e.touches[0].clientX : (e.changedTouches && e.changedTouches[0] ? e.changedTouches[0].clientX : 0));
      const clientY = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : (e.changedTouches && e.changedTouches[0] ? e.changedTouches[0].clientY : 0));

      if (this.isDraggingWire && this.dragStartCoords) {
        if (Math.hypot(clientX - this.dragStartCoords.x, clientY - this.dragStartCoords.y) > 8) {
          this.dragHasMoved = true;
        }
      }

      const rawPos = this.workspace.screenToCanvas(clientX, clientY);
      
      // 1. Check Magnetic Snapping to any Component Terminal (radius 36px for touch accessibility)
      const termSnap = this.findNearestTerminalSnap(rawPos.x, rawPos.y, 36);
      
      // Remove previous terminal hover highlights
      document.querySelectorAll(".terminal-node.snap-hover").forEach(el => el.classList.remove("snap-hover"));

      if (termSnap) {
        this.hoveredTerminal = termSnap;
        this.snapTarget = null;
        this.currentMousePos = termSnap.pos;
        if (termSnap.el) termSnap.el.classList.add("snap-hover");

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

    const onPointerUp = (e) => {
      if (!this.isConnecting || !this.sourceTerminal) return;

      const clientX = e.clientX ?? (e.changedTouches && e.changedTouches[0] ? e.changedTouches[0].clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0));
      const clientY = e.clientY ?? (e.changedTouches && e.changedTouches[0] ? e.changedTouches[0].clientY : (e.touches && e.touches[0] ? e.touches[0].clientY : 0));
      const rawPos = this.workspace.screenToCanvas(clientX, clientY);

      // If user performed a continuous drag-and-drop gesture to a terminal or wire branch:
      if (this.isDraggingWire && this.dragHasMoved) {
        this.isDraggingWire = false;

        // Check if released on a hovered terminal or near a terminal
        const termSnap = this.hoveredTerminal || this.findNearestTerminalSnap(rawPos.x, rawPos.y, 40);
        if (termSnap) {
          if (e.cancelable) e.preventDefault();
          if (this.isReconnectingEndpoint && this.reconnectingConn) {
            if (termSnap.compId !== this.sourceTerminal.componentId || termSnap.termId !== this.sourceTerminal.terminalId) {
              stateManager.recordHistory();
              if (this.detachedEnd === "to") {
                this.reconnectingConn.to = { componentId: termSnap.compId, terminalId: termSnap.termId };
              } else {
                this.reconnectingConn.from = { componentId: termSnap.compId, terminalId: termSnap.termId };
              }
              this.reconnectingConn.waypoints = this.waypoints.length > 0 ? [...this.waypoints] : null;
              const connId = this.reconnectingConn.id;
              this.isReconnectingEndpoint = false;
              this.reconnectingConn = null;
              this.cancelConnecting();
              stateManager.setSelection("connection", connId);
              stateManager.notify("connections");
              stateManager.notify("simulation");
              return;
            }
          } else {
            this.createConnection(
              this.sourceTerminal.isHanging ? this.sourceTerminal : { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
              { componentId: termSnap.compId, terminalId: termSnap.termId },
              [...this.waypoints]
            );
            this.cancelConnecting();
            return;
          }
        }

        // Check if released on a wire branch
        if (this.snapTarget) {
          if (e.cancelable) e.preventDefault();
          this.finishJunctionConnection(this.snapTarget.conn, this.snapTarget.point);
          return;
        }

        // If reconnecting and dropped in open air -> rollback to original terminal!
        if (this.isReconnectingEndpoint) {
          this.cancelConnecting();
          stateManager.notify("connections");
          return;
        }
      }

      this.isDraggingWire = false;
    };

    window.addEventListener("pointermove", onPointerMove, { passive: false });
    window.addEventListener("pointerup", onPointerUp);
    window.addEventListener("mousemove", onPointerMove, { passive: false });
    window.addEventListener("mouseup", onPointerUp);
    window.addEventListener("touchmove", onPointerMove, { passive: false });
    window.addEventListener("touchend", onPointerUp);
    document.addEventListener("pointermove", onPointerMove, { passive: false });
    document.addEventListener("mousemove", onPointerMove, { passive: false });

    const container = this.workspace.container;
    if (container) {
      // Double click to cut wire on desktop
      container.addEventListener("dblclick", (e) => {
        if (!this.isConnecting) return;
        e.stopPropagation();
        e.preventDefault();
        const rawPos = this.workspace.screenToCanvas(e.clientX, e.clientY);
        this.cutWireAtPoint(rawPos.x, rawPos.y);
      });

      // Helper method for canvas tap action during wire connection
      const handleCanvasTapAction = (e, clientX, clientY) => {
        if (!this.isConnecting) return;

        if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge") || e.target.closest(".hanging-wire-node") || e.target.closest(".probe-assembly")) {
          return;
        }

        const now = Date.now();

        // Double-tap cut detection for mobile & touchscreen (< 350ms)
        if (now - this.lastTapTime < 350 && Math.hypot(clientX - this.lastTapPos.x, clientY - this.lastTapPos.y) < 45) {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
          const rawPos = this.workspace.screenToCanvas(clientX, clientY);
          this.cutWireAtPoint(rawPos.x, rawPos.y);
          this.lastTapTime = 0;
          return;
        }
        this.lastTapTime = now;
        this.lastTapPos = { x: clientX, y: clientY };

        // 1. If snapped to a valid terminal -> FINISH CONNECTION INSTANTLY!
        if (this.hoveredTerminal) {
          e.stopPropagation();
          const target = this.hoveredTerminal;
          this.createConnection(
            this.sourceTerminal.isHanging ? this.sourceTerminal : { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
            { componentId: target.compId, terminalId: target.termId },
            [...this.waypoints]
          );
          this.cancelConnecting();
          return;
        }

        // 2. If snapped to a hanging wire node -> FINISH CONNECTION!
        if (this.hoveredHangingNode) {
          e.stopPropagation();
          this.createConnection(
            this.sourceTerminal.isHanging ? this.sourceTerminal : { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
            { isHanging: true, connectionId: this.hoveredHangingNode.connectionId, pointIndex: this.hoveredHangingNode.pointIndex },
            [...this.waypoints]
          );
          return;
        }

        // 3. Also check direct snap at click position (within 40px for easy touch)
        const rawPos = this.workspace.screenToCanvas(clientX, clientY);
        const nearSnap = this.findNearestTerminalSnap(rawPos.x, rawPos.y, 40);
        if (nearSnap) {
          e.stopPropagation();
          this.createConnection(
            this.sourceTerminal.isHanging ? this.sourceTerminal : { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
            { componentId: nearSnap.compId, terminalId: nearSnap.termId },
            [...this.waypoints]
          );
          this.cancelConnecting();
          return;
        }

        // 4. If snapped to an existing wire -> FINISH CLEAN BRANCH!
        if (this.snapTarget) {
          e.stopPropagation();
          this.finishJunctionConnection(this.snapTarget.conn, this.snapTarget.point);
          return;
        }

        // 5. Tap / Left Click on empty canvas -> add 90° corner waypoint
        this.addWaypoint(rawPos.x, rawPos.y);
      };

      // Touchstart on container to completely prevent iOS Safari double-tap zoom during wire drawing
      container.addEventListener("touchstart", (e) => {
        if (!this.isConnecting) return;

        if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge") || e.target.closest(".hanging-wire-node") || e.target.closest(".probe-assembly")) {
          return;
        }

        if (e.cancelable) e.preventDefault();

        const touch = e.touches[0] || (e.changedTouches ? e.changedTouches[0] : null);
        if (!touch) return;

        handleCanvasTapAction(e, touch.clientX, touch.clientY);
      }, { passive: false });

      // Pointerdown on container for desktop mouse interactions
      container.addEventListener("pointerdown", (e) => {
        if (!this.isConnecting) return;
        if (e.pointerType === "touch") return; // Handled by touchstart

        if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge") || e.target.closest(".hanging-wire-node") || e.target.closest(".probe-assembly")) {
          return;
        }

        if (e.button === 2) {
          this.cancelConnecting();
          return;
        }

        if (e.button === 0) {
          handleCanvasTapAction(e, e.clientX, e.clientY);
        }
      }, { passive: false });
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

  handleTerminalClick(compId, termId, termEl, e) {
    const termPos = this.getTerminalWorldPosition(compId, termId);
    if (!termPos) return;

    const state = stateManager.getState();

    if (!this.isConnecting) {
      // Cooldown guard: Cegah ghost click/pointerdown ganda memulai kabel baru seketika setelah kabel difinalisasi
      if (Date.now() - this.lastConnectionFinishTime < 300) {
        return;
      }

      // Check if there is an existing connection attached to this terminal (endpoint lifting / re-dragging)
      const existingConn = state.connections.find(
        c => (!c.to?.isHanging && !c.to?.isWireBranch && c.to?.componentId === compId && c.to?.terminalId === termId) ||
             (!c.from?.isWireBranch && !c.from?.isHanging && c.from?.componentId === compId && c.from?.terminalId === termId)
      );

      if (existingConn) {
        // Detach this endpoint and start live re-dragging to a new pin!
        this.isConnecting = true;
        this.isDraggingWire = true;
        this.isReconnectingEndpoint = true;
        this.reconnectingConn = existingConn;
        this.dragHasMoved = false;

        const clientX = e?.clientX ?? (e?.touches && e.touches[0] ? e.touches[0].clientX : 0);
        const clientY = e?.clientY ?? (e?.touches && e.touches[0] ? e.touches[0].clientY : 0);
        this.dragStartCoords = { x: clientX, y: clientY };

        if (existingConn.to?.componentId === compId && existingConn.to?.terminalId === termId) {
          this.detachedEnd = "to";
          this.originalTarget = { ...existingConn.to };
          const pAnchor = this.getConnectionEndpoint(existingConn.from);
          this.sourceTerminal = {
            ...existingConn.from,
            worldX: pAnchor ? pAnchor.x : termPos.x,
            worldY: pAnchor ? pAnchor.y : termPos.y
          };
        } else {
          this.detachedEnd = "from";
          this.originalTarget = { ...existingConn.from };
          const pAnchor = this.getConnectionEndpoint(existingConn.to);
          this.sourceTerminal = {
            ...existingConn.to,
            worldX: pAnchor ? pAnchor.x : termPos.x,
            worldY: pAnchor ? pAnchor.y : termPos.y
          };
        }

        // Visually fade original path while dragging its detached endpoint
        const origPath = document.getElementById(`wire-${existingConn.id}`);
        if (origPath) origPath.style.opacity = "0.2";

        termEl.classList.add("connecting-source");
        if (this.wirePreview) {
          this.wirePreview.style.display = "block";
          this.currentMousePos = { x: termPos.x, y: termPos.y };
          this.drawWirePreview();
        }
        return;
      }

      // No existing connection on this terminal -> Start brand new wire connection
      this.isConnecting = true;
      this.isDraggingWire = true;
      this.isReconnectingEndpoint = false;
      this.reconnectingConn = null;
      this.dragHasMoved = false;
      const clientX = e?.clientX ?? (e?.touches && e.touches[0] ? e.touches[0].clientX : 0);
      const clientY = e?.clientY ?? (e?.touches && e.touches[0] ? e.touches[0].clientY : 0);
      this.dragStartCoords = { x: clientX, y: clientY };

      this.waypoints = [];
      this.snapTarget = null;
      this.hoveredTerminal = null;
      this.sourceHangingWire = null;
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
      if (this.isReconnectingEndpoint && this.reconnectingConn) {
        if (compId === this.sourceTerminal.componentId && termId === this.sourceTerminal.terminalId) {
          this.cancelConnecting();
          return;
        }

        stateManager.recordHistory();
        if (this.detachedEnd === "to") {
          this.reconnectingConn.to = { componentId: compId, terminalId: termId };
        } else {
          this.reconnectingConn.from = { componentId: compId, terminalId: termId };
        }
        this.reconnectingConn.waypoints = this.waypoints.length > 0 ? [...this.waypoints] : null;

        const connId = this.reconnectingConn.id;
        this.isReconnectingEndpoint = false;
        this.reconnectingConn = null;
        this.cancelConnecting();
        stateManager.setSelection("connection", connId);
        stateManager.notify("connections");
        stateManager.notify("simulation");
        return;
      }

      const source = this.sourceTerminal;

      // Cancel if clicked the same terminal
      if (!source.isHanging && source.componentId === compId && source.terminalId === termId) {
        this.cancelConnecting();
        return;
      }

      this.createConnection(
        source.isHanging ? source : { componentId: source.componentId, terminalId: source.terminalId },
        { componentId: compId, terminalId: termId },
        [...this.waypoints]
      );

      this.cancelConnecting();
    }
  }

  handleHangingNodeClick(conn, point, nodeEl, e) {
    if (!this.isConnecting) {
      // Cooldown guard: Cegah ghost click/pointerdown ganda
      if (Date.now() - this.lastConnectionFinishTime < 350) {
        return;
      }

      this.isConnecting = true;
      this.isDraggingWire = true;
      this.dragHasMoved = false;
      const clientX = e?.clientX ?? (e?.touches && e.touches[0] ? e.touches[0].clientX : 0);
      const clientY = e?.clientY ?? (e?.touches && e.touches[0] ? e.touches[0].clientY : 0);
      this.dragStartCoords = { x: clientX, y: clientY };

      this.sourceHangingWire = conn;
      this.sourceTerminal = {
        isHanging: true,
        connectionId: conn.id,
        worldX: point.x,
        worldY: point.y
      };
      this.waypoints = conn.waypoints ? [...conn.waypoints] : [];
      this.snapTarget = null;
      this.hoveredTerminal = null;

      nodeEl.classList.add("connecting-source");
      if (this.wirePreview) {
        this.wirePreview.style.display = "block";
        this.currentMousePos = { x: point.x, y: point.y };
        this.drawWirePreview();
      }
    } else {
      this.cutWireAtPoint(point.x, point.y);
    }
  }

  /**
   * Proteus-Style: Cut wire mid-way and preserve as hanging wire
   */
  cutWireAtPoint(cutX, cutY) {
    if (!this.isConnecting || !this.sourceTerminal) return;

    const state = stateManager.getState();
    const cutPoint = { x: Math.round(cutX), y: Math.round(cutY) };

    if (this.sourceHangingWire) {
      stateManager.recordHistory();
      this.sourceHangingWire.to = { isHanging: true, point: cutPoint };
      this.sourceHangingWire.waypoints = this.waypoints.length > 0 ? [...this.waypoints] : null;
      this.sourceHangingWire = null;
      stateManager.notify("connections");
    } else {
      const id = `conn-${String(connectionCounter++).padStart(3, "0")}`;
      const newConn = {
        id,
        from: { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
        to: { isHanging: true, point: cutPoint },
        color: "#f88c00",
        waypoints: this.waypoints.length > 0 ? [...this.waypoints] : null
      };

      stateManager.recordHistory();
      state.connections.push(newConn);
      stateManager.notify("connections");
    }

    this.cancelConnecting();
  }

  finishJunctionConnection(targetConn, clickPos) {
    const source = this.sourceTerminal;
    if (!source) return;

    this.createConnection(
      source.isHanging ? source : { componentId: source.componentId, terminalId: source.terminalId },
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

  findNearestTerminalSnap(mouseX, mouseY, tolerance = 32) {
    const state = stateManager.getState();
    let bestSnap = null;
    let minDistance = tolerance;

    state.components.forEach(comp => {
      comp.terminals.forEach(term => {
        // Do not snap to the source terminal itself
        if (this.sourceTerminal && !this.sourceTerminal.isHanging && comp.id === this.sourceTerminal.componentId && term.id === this.sourceTerminal.terminalId) {
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

  findNearestWireSnap(mouseX, mouseY, tolerance = 24) {
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
   * Produces clean horizontal/vertical orthogonal paths with zero diagonal or backward lines
   */
  getPolylinePoints(p1, p2, waypoints) {
    if (!p1 || !p2) return [];

    const points = [p1];

    if (waypoints && waypoints.length > 0) {
      // Add all fixed waypoints with orthogonal elbows if needed
      for (let i = 0; i < waypoints.length; i++) {
        const prev = points[points.length - 1];
        const wp = waypoints[i];
        
        if (Math.abs(prev.x - wp.x) > 2 && Math.abs(prev.y - wp.y) > 2) {
          points.push({ x: wp.x, y: prev.y });
        }
        points.push(wp);
      }

      // From last waypoint to p2 (target terminal or live cursor preview):
      const lastWp = points[points.length - 1];
      if (Math.abs(lastWp.x - p2.x) > 2 || Math.abs(lastWp.y - p2.y) > 2) {
        const dx = p2.x - lastWp.x;
        const dy = p2.y - lastWp.y;
        if (Math.abs(dx) >= Math.abs(dy)) {
          points.push({ x: p2.x, y: lastWp.y });
        } else {
          points.push({ x: lastWp.x, y: p2.y });
        }
        points.push(p2);
      }
    } else {
      // No waypoints: direct Manhattan routing from p1 to p2
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
      points.push(p2);
    }

    return points;
  }

  addWaypoint(x, y) {
    if (!this.sourceTerminal) return;

    const lastPoint = this.waypoints.length > 0 
      ? this.waypoints[this.waypoints.length - 1] 
      : { x: this.sourceTerminal.worldX, y: this.sourceTerminal.worldY };

    const dx = Math.abs(x - lastPoint.x);
    const dy = Math.abs(y - lastPoint.y);

    // Prevent duplicate waypoint
    if (dx < 4 && dy < 4) return;

    let wpX = Math.round(x);
    let wpY = Math.round(y);

    if (dx > dy) {
      wpY = lastPoint.y;
    } else {
      wpX = lastPoint.x;
    }

    this.waypoints.push({ x: wpX, y: wpY });
    
    // Update currentMousePos immediately to the new waypoint so no trailing stray loop is drawn
    this.currentMousePos = { x: wpX, y: wpY };
    this.drawWirePreview();
  }

  cancelConnecting() {
    this.isConnecting = false;
    this.isDraggingWire = false;
    this.dragHasMoved = false;
    this.dragStartCoords = null;
    this.sourceTerminal = null;
    this.sourceHangingWire = null;
    this.waypoints = [];
    this.snapTarget = null;
    this.hoveredTerminal = null;
    this.lastConnectionFinishTime = Date.now();

    if (this.isReconnectingEndpoint && this.reconnectingConn) {
      const origPath = document.getElementById(`wire-${this.reconnectingConn.id}`);
      if (origPath) origPath.style.opacity = "";
    }
    this.isReconnectingEndpoint = false;
    this.reconnectingConn = null;
    this.detachedEnd = null;
    this.originalTarget = null;
    
    if (this.snapIndicator) {
      this.snapIndicator.style.display = "none";
    }

    document.querySelectorAll(".terminal-node.connecting-source, .hanging-wire-node.connecting-source").forEach(el => {
      el.classList.remove("connecting-source");
    });
    document.querySelectorAll(".terminal-node.snap-hover").forEach(el => {
      el.classList.remove("snap-hover");
    });

    if (this.wirePreview) {
      this.wirePreview.style.display = "none";
      this.wirePreview.setAttribute("d", "");
    }
  }

  createConnection(from, to, waypoints = []) {
    const state = stateManager.getState();

    // If continuing an existing hanging wire:
    if (this.sourceHangingWire) {
      stateManager.recordHistory();
      this.sourceHangingWire.to = to;
      this.sourceHangingWire.waypoints = waypoints.length > 0 ? waypoints : null;
      const wireId = this.sourceHangingWire.id;
      this.sourceHangingWire = null;
      this.cancelConnecting();
      stateManager.setSelection("connection", wireId);
      stateManager.notify("connections");
      return;
    }

    // Prevent identical duplicated wire
    const duplicate = state.connections.some(c => 
      !c.to?.isHanging &&
      ((c.from.componentId === from.componentId && c.from.terminalId === from.terminalId &&
       c.to.componentId === to.componentId && c.to.terminalId === to.terminalId) ||
      (c.from.componentId === to.componentId && c.from.terminalId === to.terminalId &&
       c.to.componentId === from.componentId && c.to.terminalId === from.terminalId))
    );
    if (duplicate) {
      this.cancelConnecting();
      return;
    }

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
    this.cancelConnecting();
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
    if (endpoint.isHanging && endpoint.point) {
      return endpoint.point;
    }
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

  computeFlexibleCablePath(p1, p2) {
    const dx = p2.x - p1.x;
    const dy = p2.y - p1.y;
    const dist = Math.hypot(dx, dy);

    // Natural responsive elastic sag curve
    const sag = Math.min(55, Math.max(12, dist * 0.16));
    const cp1x = Math.round(p1.x + dx * 0.25);
    const cp1y = Math.round(p1.y + sag + (dy > 0 ? dy * 0.12 : -dy * 0.08));
    const cp2x = Math.round(p2.x - dx * 0.25);
    const cp2y = Math.round(p2.y + sag - (dy > 0 ? dy * 0.08 : -dy * 0.12));

    return `M ${p1.x} ${p1.y} C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${p2.x} ${p2.y}`;
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
    if (!this.wirePreview) {
      this.wirePreview = document.getElementById("wire-preview");
    }
    if (!this.wirePreview) return;
    if (!this.isConnecting || !this.sourceTerminal) {
      this.wirePreview.style.display = "none";
      this.wirePreview.setAttribute("d", "");
      return;
    }

    const p1 = { x: this.sourceTerminal.worldX, y: this.sourceTerminal.worldY };
    const p2 = this.currentMousePos;

    if (this.waypoints.length === 0) {
      this.wirePreview.setAttribute("d", `M ${p1.x} ${p1.y} L ${p2.x} ${p2.y}`);
    } else {
      this.wirePreview.setAttribute("d", this.computeOrthogonalPath(p1, p2, this.waypoints));
    }
    this.wirePreview.style.display = "block";
  }

  renderWires() {
    if (!this.wiresGroup) return;
    this.wiresGroup.innerHTML = "";

    // Reset and hide any preview line when rendering finalized wires
    if (!this.isConnecting && this.wirePreview) {
      this.wirePreview.style.display = "none";
      this.wirePreview.setAttribute("d", "");
    }

    document.querySelectorAll(".wire-handle, .hanging-wire-node").forEach(h => h.remove());

    const state = stateManager.getState();
    const isSimRunning = state.simulation.running;
    const isShortCircuit = state.simulation.status === "SHORT_CIRCUIT";
    const isClosedCircuit = state.simulation.status === "OK";
    const connectedTerminals = new Set();

    state.connections.forEach((conn) => {
      const p1 = this.getConnectionEndpoint(conn.from);
      const p2 = this.getConnectionEndpoint(conn.to);
      if (!p1 || !p2) return;

      if (!conn.from.isWireBranch && !conn.from.isHanging) connectedTerminals.add(`${conn.from.componentId}-${conn.from.terminalId}`);
      if (!conn.to.isWireBranch && !conn.to.isHanging) connectedTerminals.add(`${conn.to.componentId}-${conn.to.terminalId}`);

      const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
      path.setAttribute("id", `wire-${conn.id}`);
      path.setAttribute("d", this.computeOrthogonalPath(p1, p2, conn.waypoints, conn.from, conn.to));
      
      let classes = "circuit-wire orthogonal";
      if (state.selection.id === conn.id) classes += " selected";
      
      if (isSimRunning) {
        if (isShortCircuit) {
          classes += " short-circuit";
        } else if (isClosedCircuit && !conn.to?.isHanging) {
          classes += " active";
        }
      }
      path.setAttribute("class", classes);

      this.bindDirectWireDrag(path, conn, p1, p2);

      // Handler untuk memutus / menghapus kabel
      const handleDisconnect = (e) => {
        if (e) {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
        }
        this.deleteConnection(conn.id);
      };

      // 1. Desktop: event dblclick
      path.addEventListener("dblclick", handleDisconnect);

      // 2. iOS Safari: Cegah double-tap to zoom pada touchstart ke-2
      let lastTouchStartTime = 0;
      path.addEventListener("touchstart", (e) => {
        const now = Date.now();
        if (now - lastTouchStartTime < 300) {
          if (e.cancelable) e.preventDefault();
        }
        lastTouchStartTime = now;
      }, { passive: false });

      // 3. Mobile Touchscreen: Deteksi double-tap manual via touchend (< 300ms)
      let lastWireTap = 0;
      path.addEventListener("touchend", (e) => {
        const currentTime = Date.now();
        const tapGap = currentTime - lastWireTap;
        if (tapGap < 300 && tapGap > 0) {
          if (e.cancelable) e.preventDefault();
          handleDisconnect(e);
        }
        lastWireTap = currentTime;
      }, { passive: false });

      this.wiresGroup.appendChild(path);

      // Render solid metallic terminal plugs at connection endpoints
      if (!conn.from.isWireBranch && !conn.from.isHanging) {
        const plugFrom = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        plugFrom.setAttribute("cx", p1.x);
        plugFrom.setAttribute("cy", p1.y);
        plugFrom.setAttribute("r", "5");
        plugFrom.setAttribute("class", "wire-terminal-plug");
        this.wiresGroup.appendChild(plugFrom);
      }

      if (!conn.to.isWireBranch && !conn.to.isHanging) {
        const plugTo = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        plugTo.setAttribute("cx", p2.x);
        plugTo.setAttribute("cy", p2.y);
        plugTo.setAttribute("r", "5");
        plugTo.setAttribute("class", "wire-terminal-plug");
        this.wiresGroup.appendChild(plugTo);
      }

      // Render Cut / Hanging Wire Endpoint Node
      if (conn.to?.isHanging) {
        const hangingNode = document.createElement("div");
        hangingNode.className = "hanging-wire-node";
        hangingNode.id = `hanging-node-${conn.id}`;
        hangingNode.setAttribute("data-conn-id", conn.id);
        hangingNode.style.left = `${p2.x}px`;
        hangingNode.style.top = `${p2.y}px`;
        hangingNode.title = "Ujung kabel terputus (Klik / Tap untuk melanjutkan penyambungan)";

        if (this.sourceHangingWire && this.sourceHangingWire.id === conn.id) {
          hangingNode.classList.add("connecting-source");
        }

        const onHangingTouchStart = (e) => {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
          this.handleHangingNodeClick(conn, p2, hangingNode, e);
        };

        const onHangingPointerDown = (e) => {
          if (e.pointerType === "touch") return; // Handled by touchstart
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
          this.handleHangingNodeClick(conn, p2, hangingNode, e);
        };

        hangingNode.addEventListener("touchstart", onHangingTouchStart, { passive: false });
        hangingNode.addEventListener("pointerdown", onHangingPointerDown, { passive: false });
        
        const compLayer = document.getElementById("components-layer");
        if (compLayer) compLayer.appendChild(hangingNode);
      }

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

  bindDirectWireDrag(pathEl, conn, p1, p2) {
    let startX = 0, startY = 0;
    let hasMoved = false;

    pathEl.addEventListener("pointerdown", (e) => {
      e.stopPropagation();
      this.lastWireClickPos = this.workspace.screenToCanvas(e.clientX, e.clientY);
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
    const anchor = this.lastWireClickPos || { x: midX, y: midY };

    const tb = document.createElement("div");
    tb.className = "wire-floating-toolbar";
    tb.style.left = `${anchor.x}px`;
    tb.style.top = `${anchor.y - 24}px`;

    tb.innerHTML = `
      <button class="btn-cut-wire" id="btn-cut-wire-at-point" title="Potong kabel di titik ini (bagian sebelum titik potong tetap tersimpan)">
        <span>✂️</span> Potong Kabel
      </button>
      <button class="btn-cut-wire danger" id="btn-delete-wire-action" title="Hapus kabel sepenuhnya">
        <span>🗑️</span> Hapus
      </button>
    `;

    tb.querySelector("#btn-cut-wire-at-point").addEventListener("click", (e) => {
      e.stopPropagation();
      this.splitWireAtPoint(conn, anchor.x, anchor.y);
    });

    tb.querySelector("#btn-delete-wire-action").addEventListener("click", (e) => {
      e.stopPropagation();
      this.deleteConnection(conn.id);
    });

    const compLayer = document.getElementById("components-layer");
    if (compLayer) compLayer.appendChild(tb);
    this.floatingToolbar = tb;
  }

  splitWireAtPoint(conn, cutX, cutY) {
    const cutPoint = { x: Math.round(cutX), y: Math.round(cutY) };
    stateManager.recordHistory();
    conn.to = { isHanging: true, point: cutPoint };
    if (this.floatingToolbar) {
      this.floatingToolbar.remove();
      this.floatingToolbar = null;
    }
    stateManager.notify("connections");
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

