/**
 * DTE VirtualLab V2 — Workspace Engine
 * Handles Canvas Pan, Zoom, Infinite Viewport Grid, Screen-to-Canvas Matrix & Grid Snapping
 */

import { stateManager, getDefaultWorkspaceZoom } from "./state.js";

export class WorkspaceEngine {
  constructor() {
    this.container = document.getElementById("workspace-container");
    this.canvas = document.getElementById("workspace-canvas");
    this.canvasLayer = document.getElementById("canvas-layer");
    this.zoomDisplay = document.getElementById("zoom-value");

    this.panX = 0;
    this.panY = 0;
    this.zoom = stateManager.getState().workspace?.zoom || getDefaultWorkspaceZoom();
    this.minZoom = 0.25;
    this.maxZoom = 3.0;
    this.gridSize = 20;

    // Explicit Gesture State Machine ("IDLE" | "PAN" | "PINCH_ZOOM")
    this.gestureState = "IDLE";
    this.activePointers = new Map(); // pointerId -> { x, y }
    this.startX = 0;
    this.startY = 0;

    // Pinch Zoom Geometry Tracking
    this.pinchStartDistance = 0;
    this.pinchStartMidpoint = { x: 0, y: 0 };
    this.pinchStartZoom = 1.0;
    this.pinchStartPan = { x: 0, y: 0 };
    this.pinchWorldMidpoint = { x: 0, y: 0 };
  }

  init() {
    this.zoom = stateManager.getState().workspace?.zoom || getDefaultWorkspaceZoom();
    this.bindEvents();
    this.bindControls();
    
    stateManager.subscribe("workspace", (ws) => {
      this.panX = ws.panX;
      this.panY = ws.panY;
      this.zoom = ws.zoom;
      this.renderTransform();
    });

    this.renderTransform();
  }

  bindEvents() {
    if (!this.container) return;

    // PointerDown on workspace background
    this.container.addEventListener("pointerdown", (e) => {
      if (this.connectionEngine?.isConnecting) return;

      // Only handle background surface touches
      const isBg = (
        e.target === this.container ||
        e.target === this.canvas ||
        e.target.id === "grid-layer" ||
        e.target.id === "svg-cable-layer" ||
        e.target.classList?.contains("workspace-wrapper")
      );

      if (!isBg) return;

      this.activePointers.set(e.pointerId, { x: e.clientX, y: e.clientY });

      if (this.activePointers.size === 1) {
        this.gestureState = "PAN";
        this.startX = e.clientX - this.panX;
        this.startY = e.clientY - this.panY;
        try { this.container.setPointerCapture(e.pointerId); } catch (err) {}
        stateManager.setSelection(null, null);
      } else if (this.activePointers.size >= 2) {
        // Switch to PINCH_ZOOM
        this.gestureState = "PINCH_ZOOM";
        const pointers = Array.from(this.activePointers.values());
        const p1 = pointers[0];
        const p2 = pointers[1];
        
        this.pinchStartDistance = Math.hypot(p2.x - p1.x, p2.y - p1.y);
        const midScreenX = (p1.x + p2.x) / 2;
        const midScreenY = (p1.y + p2.y) / 2;
        
        const rect = this.container.getBoundingClientRect();
        const localMidX = midScreenX - rect.left;
        const localMidY = midScreenY - rect.top;
        
        this.pinchStartMidpoint = { x: localMidX, y: localMidY };
        this.pinchStartZoom = this.zoom;
        this.pinchStartPan = { x: this.panX, y: this.panY };
        this.pinchWorldMidpoint = {
          x: (localMidX - this.pinchStartPan.x) / this.pinchStartZoom,
          y: (localMidY - this.pinchStartPan.y) / this.pinchStartZoom
        };
      }
    });

    window.addEventListener("pointermove", (e) => {
      if (this.activePointers.has(e.pointerId)) {
        this.activePointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
      }

      if (this.gestureState === "PINCH_ZOOM" && this.activePointers.size >= 2) {
        if (e.cancelable) e.preventDefault();

        const pointers = Array.from(this.activePointers.values());
        const p1 = pointers[0];
        const p2 = pointers[1];

        const currentDistance = Math.hypot(p2.x - p1.x, p2.y - p1.y);
        const scale = currentDistance / (this.pinchStartDistance || 1);
        const targetZoom = Math.min(Math.max(this.pinchStartZoom * scale, this.minZoom), this.maxZoom);

        const midScreenX = (p1.x + p2.x) / 2;
        const midScreenY = (p1.y + p2.y) / 2;
        const rect = this.container.getBoundingClientRect();
        const localMidX = midScreenX - rect.left;
        const localMidY = midScreenY - rect.top;

        // Invariant: keep world point at midpoint stationary under the fingers
        this.panX = Math.round(localMidX - this.pinchWorldMidpoint.x * targetZoom);
        this.panY = Math.round(localMidY - this.pinchWorldMidpoint.y * targetZoom);
        this.zoom = targetZoom;

        this.renderTransform();
        stateManager.setWorkspaceTransform(this.panX, this.panY, this.zoom);
      } else if (this.gestureState === "PAN" && this.activePointers.size === 1) {
        this.panX = e.clientX - this.startX;
        this.panY = e.clientY - this.startY;
        this.renderTransform();
      }
    }, { passive: false });

    const onPointerEnd = (e) => {
      this.activePointers.delete(e.pointerId);
      try { this.container.releasePointerCapture(e.pointerId); } catch (err) {}

      if (this.activePointers.size === 1) {
        // Transition remaining touch to PAN smoothly
        this.gestureState = "PAN";
        const remaining = Array.from(this.activePointers.values())[0];
        this.startX = remaining.x - this.panX;
        this.startY = remaining.y - this.panY;
      } else if (this.activePointers.size === 0) {
        if (this.gestureState !== "IDLE") {
          this.gestureState = "IDLE";
          stateManager.setWorkspaceTransform(this.panX, this.panY, this.zoom);
        }
      }
    };

    window.addEventListener("pointerup", onPointerEnd);
    window.addEventListener("pointercancel", onPointerEnd);

    // Zoom on wheel relative to cursor point
    this.container.addEventListener("wheel", (e) => {
      e.preventDefault();
      const rect = this.container.getBoundingClientRect();
      const cursorX = e.clientX - rect.left;
      const cursorY = e.clientY - rect.top;

      const zoomFactor = e.deltaY < 0 ? 1.12 : 0.88;
      this.applyZoomAt(cursorX, cursorY, this.zoom * zoomFactor);
    }, { passive: false });
  }

  bindControls() {
    const btnZoomIn = document.getElementById("btn-zoom-in");
    const btnZoomOut = document.getElementById("btn-zoom-out");
    const btnZoomReset = document.getElementById("btn-zoom-reset");

    if (btnZoomIn) {
      btnZoomIn.addEventListener("click", () => {
        const rect = this.container.getBoundingClientRect();
        this.applyZoomAt(rect.width / 2, rect.height / 2, this.zoom * 1.2);
      });
    }

    if (btnZoomOut) {
      btnZoomOut.addEventListener("click", () => {
        const rect = this.container.getBoundingClientRect();
        this.applyZoomAt(rect.width / 2, rect.height / 2, this.zoom / 1.2);
      });
    }

    if (btnZoomReset) {
      btnZoomReset.addEventListener("click", () => {
        this.panX = 0;
        this.panY = 0;
        this.zoom = getDefaultWorkspaceZoom();
        stateManager.resetWorkspace();
        this.renderTransform();
      });
    }
  }

  applyZoomAt(screenX, screenY, newZoom) {
    const clampedZoom = Math.min(Math.max(newZoom, this.minZoom), this.maxZoom);
    
    const worldX = (screenX - this.panX) / this.zoom;
    const worldY = (screenY - this.panY) / this.zoom;

    this.panX = screenX - worldX * clampedZoom;
    this.panY = screenY - worldY * clampedZoom;
    this.zoom = clampedZoom;

    this.renderTransform();
    stateManager.setWorkspaceTransform(this.panX, this.panY, this.zoom);
  }

  renderTransform() {
    if (!this.canvasLayer) return;
    this.canvasLayer.style.transform = `translate(${this.panX}px, ${this.panY}px) scale(${this.zoom})`;
    
    // Set inverse zoom CSS custom properties for constant screen-space sizing (CAD-style terminals)
    const invZoom = 1 / (this.zoom || 1);
    this.canvasLayer?.style?.setProperty?.("--inv-zoom", invZoom.toFixed(4));
    this.canvasLayer?.style?.setProperty?.("--workspace-zoom", this.zoom.toFixed(4));
    
    if (this.container) {
      this.container.style?.setProperty?.("--inv-zoom", invZoom.toFixed(4));
      this.container.style?.setProperty?.("--workspace-zoom", this.zoom.toFixed(4));
      const dynamicGridSize = this.gridSize * this.zoom;
      this.container.style.backgroundSize = `${dynamicGridSize}px ${dynamicGridSize}px`;
      this.container.style.backgroundPosition = `${this.panX}px ${this.panY}px`;
    }

    if (this.zoomDisplay) {
      this.zoomDisplay.textContent = `${Math.round(this.zoom * 100)}%`;
    }
  }

  screenToCanvas(clientX, clientY) {
    const rect = this.container.getBoundingClientRect();
    const screenX = clientX - rect.left;
    const screenY = clientY - rect.top;

    let worldX = (screenX - this.panX) / this.zoom;
    let worldY = (screenY - this.panY) / this.zoom;

    return {
      x: this.snap(worldX),
      y: this.snap(worldY)
    };
  }

  screenToCanvasRaw(clientX, clientY) {
    const rect = this.container.getBoundingClientRect();
    const screenX = clientX - rect.left;
    const screenY = clientY - rect.top;

    return {
      x: (screenX - this.panX) / this.zoom,
      y: (screenY - this.panY) / this.zoom
    };
  }

  snap(val) {
    const state = stateManager.getState();
    if (!state.workspace.snapToGrid) return Math.round(val);
    return Math.round(val / this.gridSize) * this.gridSize;
  }
}

/**
 * Standalone grid snap utility — single source of truth.
 * Snaps a world-coordinate value to the nearest grid multiple.
 * @param {number} value - World coordinate value
 * @param {number} gridSize - Grid spacing (default 20)
 * @returns {number} Snapped value
 */
export function snapToGrid(value, gridSize = 20) {
  return Math.round(value / gridSize) * gridSize;
}
