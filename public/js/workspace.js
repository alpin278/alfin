/**
 * DTE VirtualLab V2 — Workspace Engine
 * Handles Canvas Pan, Zoom, Infinite Viewport Grid, Screen-to-Canvas Matrix & Grid Snapping
 */

import { stateManager } from "./state.js";

export class WorkspaceEngine {
  constructor() {
    this.container = document.getElementById("workspace-container");
    this.canvas = document.getElementById("workspace-canvas");
    this.canvasLayer = document.getElementById("canvas-layer");
    this.zoomDisplay = document.getElementById("zoom-value");

    this.panX = 0;
    this.panY = 0;
    this.zoom = (window.innerWidth < 768) ? 0.78 : 1.0;
    this.minZoom = 0.25;
    this.maxZoom = 3.0;
    this.gridSize = 20;

    this.isPanning = false;
    this.startX = 0;
    this.startY = 0;
  }

  init() {
    if (window.innerWidth < 768) {
      const state = stateManager.getState();
      state.workspace.zoom = 0.78;
    }

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

    // Pan on background drag
    this.container.addEventListener("pointerdown", (e) => {
      if (
        e.target === this.container ||
        e.target === this.canvas ||
        e.target.id === "grid-layer" ||
        e.target.id === "svg-cable-layer"
      ) {
        this.isPanning = true;
        this.startX = e.clientX - this.panX;
        this.startY = e.clientY - this.panY;
        this.container.setPointerCapture(e.pointerId);
        
        stateManager.setSelection(null, null);
      }
    });

    window.addEventListener("pointermove", (e) => {
      if (this.isPanning) {
        this.panX = e.clientX - this.startX;
        this.panY = e.clientY - this.startY;
        this.renderTransform();
      }
    });

    window.addEventListener("pointerup", (e) => {
      if (this.isPanning) {
        this.isPanning = false;
        stateManager.setWorkspaceTransform(this.panX, this.panY, this.zoom);
      }
    });

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
        this.zoom = 1.0;
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
    
    // Synchronize infinite background grid scale & offset with 100% viewport coverage
    if (this.container) {
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

  snap(val) {
    const state = stateManager.getState();
    if (!state.workspace.snapToGrid) return Math.round(val);
    return Math.round(val / this.gridSize) * this.gridSize;
  }
}
