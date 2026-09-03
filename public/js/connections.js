/**
 * DTE VirtualLab V2 — Professional Schematic Orthogonal Wiring & Magnetic Snapping Engine
 * Supports Mobile Touch Drag-and-Drop, Touch/Click Select-to-Connect & Proteus-Style Cut-Wire
 */

import { stateManager } from "./state.js";
import { getTerminalWorldPosition } from "./components.js";

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
    this.bindCancelButtonEvents();

    stateManager.subscribe("connections", () => this.renderWires());
    stateManager.subscribe("components", () => this.renderWires());
    stateManager.subscribe("components_moving", () => this.renderWires());
    stateManager.subscribe("selection", () => {
      this.renderWires();
      this.updateSelectionVisuals();
      this.renderFloatingToolbar();
    });
  }

  bindCancelButtonEvents() {
    const btnCancel = document.getElementById("btn-cancel-wire");
    if (btnCancel) {
      btnCancel.addEventListener("click", (e) => {
        e.stopPropagation();
        e.preventDefault();
        this.cancelActiveConnection();
      });
    }
  }

  updateCancelButtonUI(isConnecting) {
    const btnCancel = document.getElementById("btn-cancel-wire");
    const btnRotate = document.getElementById("btn-rotate-component");
    const btnDelete = document.getElementById("btn-delete-component");

    if (btnCancel) {
      if (isConnecting) {
        btnCancel.style.display = "inline-flex";
        btnCancel.classList.add("active");
        if (btnRotate) btnRotate.style.display = "none";
        if (btnDelete) btnDelete.style.display = "none";
      } else {
        btnCancel.style.display = "none";
        btnCancel.classList.remove("active");
        if (btnRotate) btnRotate.style.display = "";
        if (btnDelete) btnDelete.style.display = "";
      }
    }
  }

  cancelActiveConnection() {
    this.cancelConnecting();
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

    compLayer.addEventListener("pointerdown", (e) => {
      const termEl = e.target.closest(".terminal-node");
      if (!termEl) return;

      // Never start wiring if workspace is currently in multi-touch PINCH_ZOOM mode
      if (this.workspace?.gestureState === "PINCH_ZOOM" || (this.workspace?.activePointers?.size || 0) >= 2) {
        return;
      }

      e.stopPropagation();
      if (e.cancelable) e.preventDefault();

      const compId = termEl.getAttribute("data-comp-id");
      const termId = termEl.getAttribute("data-term-id");

      this.activeTerminalPointerId = e.pointerId;
      this.activeTerminalEl = termEl;

      try {
        termEl.setPointerCapture(e.pointerId);
      } catch (err) {}

      this.handleTerminalClick(compId, termId, termEl, e);
    }, { passive: false });
  }

  bindCanvasWiringEvents() {
    const onPointerMove = (e) => {
      if (!this.isConnecting || !this.sourceTerminal) return;
      if (e.cancelable) e.preventDefault();

      if (this.isDraggingWire && this.dragStartCoords) {
        if (Math.hypot(e.clientX - this.dragStartCoords.x, e.clientY - this.dragStartCoords.y) > 4) {
          this.dragHasMoved = true;
        }
      }

      const rawPos = this.workspace?.screenToCanvasRaw 
        ? this.workspace.screenToCanvasRaw(e.clientX, e.clientY) 
        : this.workspace.screenToCanvas(e.clientX, e.clientY);
      
      // Screen-space magnetic tolerances with hysteresis
      const zoom = this.workspace?.zoom || 1;
      const isTouch = (e.pointerType === "touch");
      const enterRadius = isTouch ? 20 : 12;
      const releaseRadius = isTouch ? 28 : 18;
      const screenSnapTol = this.hoveredTerminal ? releaseRadius : enterRadius;
      const worldSnapTol = screenSnapTol / zoom;

      // 1. Check Magnetic Snapping to any Component Terminal
      const termSnap = this.findNearestTerminalSnap(rawPos.x, rawPos.y, worldSnapTol, this.hoveredTerminal);
      
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

        // 2. Check Snapping to an existing Wire Branch
        const wireSnapTol = (isTouch ? 22 : 16) / zoom;
        const wireSnap = this.findNearestWireSnap(rawPos.x, rawPos.y, wireSnapTol);
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
          const gridSize = this.workspace?.gridSize || 20;
          this.currentMousePos = {
            x: Math.round(rawPos.x / gridSize) * gridSize,
            y: Math.round(rawPos.y / gridSize) * gridSize
          };
          if (this.snapIndicator) {
            this.snapIndicator.style.display = "none";
          }
        }
      }

      this.drawWirePreview();
    };

    const onPointerUp = (e) => {
      if (this.activeTerminalEl && this.activeTerminalPointerId !== null) {
        try { this.activeTerminalEl.releasePointerCapture(this.activeTerminalPointerId); } catch (err) {}
      }
      this.activeTerminalPointerId = null;
      this.activeTerminalEl = null;

      if (!this.isConnecting || !this.sourceTerminal) return;

      // If user performed a continuous drag-and-drop gesture to a terminal or wire branch:
      if (this.isDraggingWire && this.dragHasMoved) {
        this.isDraggingWire = false;

        // ONLY commit to terminal if hoveredTerminal is active (terminal was highlighted)
        const termSnap = this.hoveredTerminal;
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

        // Dropped in open space during drag-and-drop -> clean rollback / cancel (no ghost wire!)
        this.cancelConnecting();
        stateManager.notify("connections");
        return;
      }

      this.isDraggingWire = false;
    };

    const onPointerCancel = (e) => {
      if (this.activeTerminalEl && this.activeTerminalPointerId !== null) {
        try { this.activeTerminalEl.releasePointerCapture(this.activeTerminalPointerId); } catch (err) {}
      }
      this.activeTerminalPointerId = null;
      this.activeTerminalEl = null;

      if (this.isConnecting) {
        this.cancelConnecting();
        stateManager.notify("connections");
      }
    };

    window.addEventListener("pointermove", onPointerMove, { passive: false });
    window.addEventListener("pointerup", onPointerUp);
    window.addEventListener("pointercancel", onPointerCancel);

    const container = this.workspace.container;
    if (container) {
      // Helper method for canvas tap action during wire connection (click-to-connect mode)
      const handleCanvasTapAction = (e, clientX, clientY) => {
        if (!this.isConnecting) return;

        if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge") || e.target.closest(".probe-assembly")) {
          return;
        }

        // 1. If snapped to a valid terminal (highlight is active) -> FINISH CONNECTION!
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

        // 3. If snapped to an existing wire -> FINISH CLEAN BRANCH!
        if (this.snapTarget) {
          e.stopPropagation();
          this.finishJunctionConnection(this.snapTarget.conn, this.snapTarget.point);
          return;
        }

        // 4. Tap / Left Click on empty canvas -> add manual waypoint (snapped to grid)
        const rawPos = this.workspace.screenToCanvas(clientX, clientY);
        this.addWaypoint(rawPos.x, rawPos.y);
      };

      container.addEventListener("pointerdown", (e) => {
        if (!this.isConnecting) return;

        if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge") || e.target.closest(".hanging-wire-node") || e.target.closest(".probe-assembly")) {
          return;
        }

        if (e.button === 2) {
          this.cancelActiveConnection();
          return;
        }

        if (e.button === 0 || e.pointerType === "touch" || e.pointerType === "pen") {
          handleCanvasTapAction(e, e.clientX, e.clientY);
        }
      }, { passive: false });
    }

    window.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.isConnecting) {
        this.cancelActiveConnection();
      }
    });

    window.addEventListener("contextmenu", (e) => {
      if (this.isConnecting) {
        e.preventDefault();
        this.cancelActiveConnection();
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
        this.updateCancelButtonUI(true);
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
      this.updateCancelButtonUI(true);
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

  /**
   * Add a persistent junction point to an existing wire
   */
  addWireJunction(conn, clickX, clickY) {
    if (!conn) return null;
    const p1 = this.getConnectionEndpoint(conn.from);
    const p2 = this.getConnectionEndpoint(conn.to);
    if (!p1 || !p2) return null;

    const poly = this.getPolylinePoints(p1, p2, conn.waypoints);
    if (poly.length < 2) return null;

    // 1. Find the nearest segment [poly[i], poly[i+1]] to the click
    let bestSegIdx = 0;
    let minDistance = Infinity;
    let bestClosestPt = { x: clickX, y: clickY };

    for (let i = 0; i < poly.length - 1; i++) {
      const a = poly[i];
      const b = poly[i + 1];
      const closest = this.getClosestPointOnSegment(a, b, { x: clickX, y: clickY });
      const dist = Math.hypot(clickX - closest.x, clickY - closest.y);
      if (dist < minDistance) {
        minDistance = dist;
        bestSegIdx = i;
        bestClosestPt = closest;
      }
    }

    const segA = poly[bestSegIdx];
    const segB = poly[bestSegIdx + 1];
    const gridSize = this.workspace?.gridSize || 20;

    // 2. Snap strictly to the workspace grid on the segment
    let snappedX = bestClosestPt.x;
    let snappedY = bestClosestPt.y;

    const isHorizontal = Math.abs(segA.y - segB.y) < 1e-4;
    if (isHorizontal) {
      snappedY = segA.y;
      snappedX = Math.round(bestClosestPt.x / gridSize) * gridSize;
      const minX = Math.min(segA.x, segB.x);
      const maxX = Math.max(segA.x, segB.x);
      snappedX = Math.max(minX, Math.min(maxX, snappedX));
    } else {
      snappedX = segA.x;
      snappedY = Math.round(bestClosestPt.y / gridSize) * gridSize;
      const minY = Math.min(segA.y, segB.y);
      const maxY = Math.max(segA.y, segB.y);
      snappedY = Math.max(minY, Math.min(maxY, snappedY));
    }

    // 3. Initialize junctions array if not present
    if (!Array.isArray(conn.junctions)) {
      conn.junctions = [];
    }

    // 4. Duplicate Check: If a junction already exists within 10px, do NOT add duplicate
    const existing = conn.junctions.find(j => Math.hypot(j.x - snappedX, j.y - snappedY) < 10);
    if (existing) {
      return existing;
    }

    // 5. Append new junction (never overwrite or mutate previous junctions)
    stateManager.recordHistory();

    const newJunction = {
      id: `junc-${conn.id}-${Date.now()}-${conn.junctions.length + 1}`,
      x: snappedX,
      y: snappedY
    };

    conn.junctions.push(newJunction);

    stateManager.notify("connections");
    return newJunction;
  }

  startConnectingFromJunction(conn, junc) {
    if (this.isConnecting) {
      // If already connecting, connect to this junction
      this.createConnection(
        this.sourceTerminal.isHanging ? this.sourceTerminal : { componentId: this.sourceTerminal.componentId, terminalId: this.sourceTerminal.terminalId },
        {
          componentId: conn.from?.componentId || conn.id,
          terminalId: conn.from?.terminalId || "junction",
          isWireBranch: true,
          targetWireId: conn.id,
          junctionPoint: { x: junc.x, y: junc.y }
        },
        [...this.waypoints]
      );
      this.cancelConnecting();
      return;
    }

    this.isConnecting = true;
    this.isDraggingWire = true;
    this.dragHasMoved = false;

    this.sourceHangingWire = null;
    this.sourceTerminal = {
      isWireBranch: true,
      targetWireId: conn.id,
      junctionPoint: { x: junc.x, y: junc.y },
      worldX: junc.x,
      worldY: junc.y
    };
    this.waypoints = [];
    this.snapTarget = null;
    this.hoveredTerminal = null;

    if (this.wirePreview) {
      this.wirePreview.style.display = "block";
      this.currentMousePos = { x: junc.x, y: junc.y };
      this.drawWirePreview();
    }
    this.updateCancelButtonUI(true);
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

  findNearestTerminalSnap(mouseX, mouseY, tolerance = 16, currentHoverTarget = null) {
    const state = stateManager.getState();

    // Hysteresis check: if already hovering a terminal, check if pointer is still within release radius
    if (currentHoverTarget) {
      const pos = this.getTerminalWorldPosition(currentHoverTarget.compId, currentHoverTarget.termId);
      if (pos) {
        const dist = Math.hypot(mouseX - pos.x, mouseY - pos.y);
        if (dist <= tolerance) {
          return {
            compId: currentHoverTarget.compId,
            termId: currentHoverTarget.termId,
            pos: pos,
            el: currentHoverTarget.el || document.getElementById(`term-${currentHoverTarget.compId}-${currentHoverTarget.termId}`)
          };
        }
      }
    }

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
        if (dist <= minDistance) {
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
   * Normalizes an orthogonal route:
   * 1. Removes consecutive duplicate points (within tolerance)
   * 2. Collapses 3+ collinear points ONLY if the middle point is not a manual waypoint
   * 3. Snaps nearly-straight 2-point routes to strictly identical coordinates
   */
  normalizeOrthogonalRoute(points, tolerance = 2) {
    if (!points || points.length <= 1) return points || [];

    // Step 1: Remove consecutive duplicates
    let result = [points[0]];
    for (let i = 1; i < points.length; i++) {
      const prev = result[result.length - 1];
      const curr = points[i];
      if (Math.abs(curr.x - prev.x) > tolerance || Math.abs(curr.y - prev.y) > tolerance) {
        result.push(curr);
      }
    }

    if (result.length <= 2) {
      if (result.length === 2) {
        if (Math.abs(result[0].y - result[1].y) <= tolerance) {
          result[1].y = result[0].y;
        }
        if (Math.abs(result[0].x - result[1].x) <= tolerance) {
          result[1].x = result[0].x;
        }
      }
      return result;
    }

    // Step 2: Iteratively collapse collinear triplets ONLY if the middle point is NOT a manual waypoint
    let changed = true;
    while (changed && result.length >= 3) {
      changed = false;
      const simplified = [result[0]];

      for (let i = 1; i < result.length - 1; i++) {
        const pPrev = simplified[simplified.length - 1];
        const pCurr = result[i];
        const pNext = result[i + 1];

        // Never collapse a user-created manual waypoint
        if (pCurr.isManual) {
          simplified.push(pCurr);
          continue;
        }

        // Horizontal collinearity: pPrev.y == pCurr.y == pNext.y
        const isHorizontalCollinear =
          Math.abs(pPrev.y - pCurr.y) <= tolerance &&
          Math.abs(pCurr.y - pNext.y) <= tolerance;

        // Vertical collinearity: pPrev.x == pCurr.x == pNext.x
        const isVerticalCollinear =
          Math.abs(pPrev.x - pCurr.x) <= tolerance &&
          Math.abs(pCurr.x - pNext.x) <= tolerance;

        if (isHorizontalCollinear || isVerticalCollinear) {
          changed = true;
        } else {
          simplified.push(pCurr);
        }
      }

      simplified.push(result[result.length - 1]);
      result = simplified;
    }

    // Final alignment on 2 remaining endpoints if collapsed to single segment
    if (result.length === 2) {
      if (Math.abs(result[0].y - result[1].y) <= tolerance) {
        result[1].y = result[0].y;
      }
      if (Math.abs(result[0].x - result[1].x) <= tolerance) {
        result[1].x = result[0].x;
      }
    }

    return result;
  }

  /**
   * Smart CAD Schematic Manhattan Routing
   * 1. Direct connection (0 manual waypoints): Straight-first Proteus Manhattan routing (auto-bends vanish when aligned)
   * 2. Manual waypoints present: Segment-by-segment routing preserving all user waypoints
   */
  getPolylinePoints(p1, p2, waypoints) {
    if (!p1 || !p2) return [];

    const tolerance = 2;
    const pt1 = { x: Math.round(p1.x), y: Math.round(p1.y) };
    const pt2 = { x: Math.round(p2.x), y: Math.round(p2.y) };

    if (!waypoints || waypoints.length === 0) {
      // Direct connection between p1 and p2 (0 manual waypoints)
      const dx = Math.abs(pt2.x - pt1.x);
      const dy = Math.abs(pt2.y - pt1.y);

      // Case 1: Same Y -> ONE straight horizontal segment
      if (dy <= tolerance) {
        return [{ x: pt1.x, y: pt1.y }, { x: pt2.x, y: pt1.y }];
      }

      // Case 2: Same X -> ONE straight vertical segment
      if (dx <= tolerance) {
        return [{ x: pt1.x, y: pt1.y }, { x: pt1.x, y: pt2.y }];
      }

      // Case 3: Diagonal -> ONE deterministic horizontal-first L-bend
      return [
        { x: pt1.x, y: pt1.y },
        { x: pt2.x, y: pt1.y, isAuto: true },
        { x: pt2.x, y: pt2.y }
      ];
    }

    // Manual waypoints present: route segment-by-segment
    const keypoints = [
      pt1,
      ...waypoints.map(wp => ({ x: Math.round(wp.x), y: Math.round(wp.y), isManual: true })),
      pt2
    ];

    const poly = [keypoints[0]];

    for (let i = 0; i < keypoints.length - 1; i++) {
      const from = poly[poly.length - 1];
      const to = keypoints[i + 1];

      const dx = Math.abs(to.x - from.x);
      const dy = Math.abs(to.y - from.y);

      if (dx > tolerance && dy > tolerance) {
        // Insert auto orthogonal bend for this segment (horizontal-first)
        poly.push({ x: to.x, y: from.y, isAuto: true });
      }
      poly.push(to);
    }

    return this.normalizeOrthogonalRoute(poly, tolerance);
  }

  addWaypoint(x, y) {
    if (!this.sourceTerminal) return;

    const lastPoint = this.waypoints.length > 0 
      ? this.waypoints[this.waypoints.length - 1] 
      : { x: this.sourceTerminal.worldX, y: this.sourceTerminal.worldY };

    // Snap to workspace grid
    const gridSize = this.workspace?.gridSize || 20;
    const wpX = Math.round(x / gridSize) * gridSize;
    const wpY = Math.round(y / gridSize) * gridSize;

    const dist = Math.hypot(wpX - lastPoint.x, wpY - lastPoint.y);

    // Prevent duplicate waypoint at exact same grid point
    if (dist < 4) return;

    this.waypoints.push({ x: wpX, y: wpY, isManual: true });
    
    // Update currentMousePos immediately to the new waypoint
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

    if (this.activeTerminalEl && this.activeTerminalPointerId !== null) {
      try { this.activeTerminalEl.releasePointerCapture(this.activeTerminalPointerId); } catch (err) {}
    }
    this.activeTerminalPointerId = null;
    this.activeTerminalEl = null;

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

    this.updateCancelButtonUI(false);
  }

  createConnection(from, to, waypoints = []) {
    const state = stateManager.getState();

    // If continuing an existing hanging wire:
    if (this.sourceHangingWire) {
      stateManager.recordHistory();
      if (this.sourceTerminal?.isHangingStart) {
        this.sourceHangingWire.from = to;
      } else {
        this.sourceHangingWire.to = to;
      }
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
    return getTerminalWorldPosition(compId, termId);
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

    // Always use orthogonal (Manhattan) routing — no diagonal preview
    this.wirePreview.setAttribute("d", this.computeOrthogonalPath(p1, p2, this.waypoints));
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

      // 1. Desktop: double-click adds persistent junction
      path.addEventListener("dblclick", (e) => {
        e.stopPropagation();
        if (e.cancelable) e.preventDefault();
        const pos = this.workspace.screenToCanvas(e.clientX, e.clientY);
        this.addWireJunction(conn, pos.x, pos.y);
      });

      // 2. iOS Safari: prevent double-tap to zoom
      let lastTouchStartTime = 0;
      path.addEventListener("touchstart", (e) => {
        const now = Date.now();
        if (now - lastTouchStartTime < 300) {
          if (e.cancelable) e.preventDefault();
        }
        lastTouchStartTime = now;
      }, { passive: false });

      // 3. Mobile Touchscreen: double-tap (< 350ms) adds persistent junction
      let lastWireTap = 0;
      let lastWireTapPos = { x: 0, y: 0 };
      path.addEventListener("touchend", (e) => {
        const currentTime = Date.now();
        const tapGap = currentTime - lastWireTap;
        const touch = e.changedTouches && e.changedTouches[0] ? e.changedTouches[0] : null;
        if (touch && tapGap < 350 && tapGap > 0) {
          const dist = Math.hypot(touch.clientX - lastWireTapPos.x, touch.clientY - lastWireTapPos.y);
          if (dist < 40) {
            if (e.cancelable) e.preventDefault();
            e.stopPropagation();
            const pos = this.workspace.screenToCanvas(touch.clientX, touch.clientY);
            this.addWireJunction(conn, pos.x, pos.y);
            lastWireTap = 0;
            return;
          }
        }
        if (touch) {
          lastWireTapPos = { x: touch.clientX, y: touch.clientY };
        }
        lastWireTap = currentTime;
      }, { passive: false });

      this.wiresGroup.appendChild(path);

      // Render all persistent junctions for this connection
      if (Array.isArray(conn.junctions) && conn.junctions.length > 0) {
        const compLayer = document.getElementById("components-layer");
        conn.junctions.forEach((junc) => {
          const juncNode = document.createElement("div");
          juncNode.className = "wire-junction-node";
          juncNode.id = `junction-node-${junc.id}`;
          juncNode.setAttribute("data-conn-id", conn.id);
          juncNode.setAttribute("data-junc-id", junc.id);
          juncNode.style.left = `${junc.x}px`;
          juncNode.style.top = `${junc.y}px`;
          juncNode.title = "Titik Percabangan / Junction (Klik / Tap untuk menarik cabang kabel)";

          const dotEl = document.createElement("div");
          dotEl.className = "wire-junction-dot";
          juncNode.appendChild(dotEl);

          // Click / Tap to start routing a branch wire from this junction
          const onJuncClick = (e) => {
            e.stopPropagation();
            if (e.cancelable) e.preventDefault();
            this.startConnectingFromJunction(conn, junc);
          };

          juncNode.addEventListener("touchstart", onJuncClick, { passive: false });
          juncNode.addEventListener("pointerdown", (e) => {
            if (e.pointerType === "touch") return;
            onJuncClick(e);
          });

          if (compLayer) compLayer.appendChild(juncNode);
        });
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
    if (!conn.waypoints || conn.waypoints.length === 0) return;
    
    // Only render handles for explicit user manual waypoints (auto-bends are derived)
    const manualWaypoints = conn.waypoints.filter(wp => wp.isManual);
    if (manualWaypoints.length === 0) return;

    manualWaypoints.forEach((wp) => {
      const handle = document.createElement("div");
      handle.className = "wire-handle orthogonal-handle";
      handle.style.left = `${wp.x}px`;
      handle.style.top = `${wp.y}px`;
      handle.title = "Tarik untuk memindahkan waypoint kabel";

      let startX = 0, startY = 0;
      let initX = wp.x, initY = wp.y;

      handle.addEventListener("pointerdown", (e) => {
        e.stopPropagation();
        startX = e.clientX;
        startY = e.clientY;
        initX = wp.x;
        initY = wp.y;

        const onMove = (mv) => {
          const dx = (mv.clientX - startX) / this.workspace.zoom;
          const dy = (mv.clientY - startY) / this.workspace.zoom;
          const newX = Math.round(initX + dx);
          const newY = Math.round(initY + dy);

          wp.x = newX;
          wp.y = newY;
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
    });
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
      <button class="btn-cut-wire danger" id="btn-delete-wire-action" title="Hapus kabel terpilih (Delete)">
        <span>🗑️</span> Hapus
      </button>
    `;

    tb.querySelector("#btn-delete-wire-action")?.addEventListener("click", (e) => {
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

