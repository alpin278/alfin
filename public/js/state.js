/**
 * Calculates the responsive default workspace zoom based on viewport width.
 * Desktop (> 1024px): 100% (1.00)
 * Tablet (769px - 1024px): 82% (0.82)
 * Mobile (431px - 768px): 60% (0.60)
 * Narrow Mobile (<= 430px): 58% (0.58)
 */
export function getDefaultWorkspaceZoom(width = (typeof window !== "undefined" ? window.innerWidth : 1200)) {
  if (width <= 430) return 0.58;
  if (width <= 768) return 0.60;
  if (width <= 1024) return 0.82;
  return 1.0;
}

class AppState {
  constructor() {
    this.state = {
      version: 1,
      components: [],
      connections: [],
      selection: {
        type: null, // "component" | "connection" | null
        id: null
      },
      workspace: {
        zoom: getDefaultWorkspaceZoom(),
        panX: 0,
        panY: 0,
        gridSize: 20,
        snapToGrid: true
      },
      simulation: {
        running: false,
        status: "STANDBY", // "STANDBY" | "OK" | "INCOMPLETE" | "SHORT_CIRCUIT" | "OVERLOAD"
        metrics: {
          totalVoltage: 0,
          totalCurrent: 0,
          totalPower: 0
        },
        message: "Rangkaian siap disimulasikan."
      },
      history: {
        undoStack: [],
        redoStack: [],
        maxDepth: 30
      }
    };

    this.listeners = new Map();
  }

  getState() {
    return this.state;
  }

  /**
   * Subscribe to state changes on specific keys
   * @param {string} key - e.g. "components", "workspace", "selection", "simulation"
   * @param {Function} callback 
   */
  subscribe(key, callback) {
    if (!this.listeners.has(key)) {
      this.listeners.set(key, new Set());
    }
    this.listeners.get(key).add(callback);
    return () => this.listeners.get(key).delete(callback);
  }

  notify(key) {
    if (this.listeners.has(key)) {
      this.listeners.get(key).forEach(cb => cb(this.state[key], this.state));
    }
    // Global listener
    if (this.listeners.has("*")) {
      this.listeners.get("*").forEach(cb => cb(this.state));
    }
  }

  // --- Snapshot History for Undo/Redo ---
  recordHistory() {
    const snapshot = JSON.stringify({
      components: this.state.components,
      connections: this.state.connections
    });
    
    this.state.history.undoStack.push(snapshot);
    if (this.state.history.undoStack.length > this.state.history.maxDepth) {
      this.state.history.undoStack.shift();
    }
    this.state.history.redoStack = []; // clear redo on new action
  }

  undo() {
    if (this.state.history.undoStack.length === 0) return false;
    
    const currentSnapshot = JSON.stringify({
      components: this.state.components,
      connections: this.state.connections
    });
    this.state.history.redoStack.push(currentSnapshot);

    const prevSnapshot = JSON.parse(this.state.history.undoStack.pop());
    this.state.components = prevSnapshot.components;
    this.state.connections = prevSnapshot.connections;
    this.state.selection = { type: null, id: null };

    this.notify("components");
    this.notify("connections");
    this.notify("selection");
    return true;
  }

  redo() {
    if (this.state.history.redoStack.length === 0) return false;

    const currentSnapshot = JSON.stringify({
      components: this.state.components,
      connections: this.state.connections
    });
    this.state.history.undoStack.push(currentSnapshot);

    const nextSnapshot = JSON.parse(this.state.history.redoStack.pop());
    this.state.components = nextSnapshot.components;
    this.state.connections = nextSnapshot.connections;

    this.notify("components");
    this.notify("connections");
    this.notify("selection");
    return true;
  }

  // --- Component Actions ---
  addComponent(component) {
    this.recordHistory();
    this.state.components.push(component);
    this.setSelection("component", component.id);
    this.notify("components");
  }

  updateComponentPosition(id, x, y) {
    const comp = this.state.components.find(c => c.id === id);
    if (comp) {
      comp.x = x;
      comp.y = y;
      this.notify("components");
    }
  }

  rotateComponent(id, angle = 90) {
    const comp = this.state.components.find(c => c.id === id);
    if (comp) {
      this.recordHistory();
      comp.rotation = ((comp.rotation || 0) + angle) % 360;
      if (comp.type !== "multimeter" && comp.terminals && comp.terminals.length > 0) {
        const t0 = comp.terminals[0];
        const width = comp.width;
        const height = comp.height;
        const rotation = comp.rotation || 0;
        const gridSize = this.state.workspace?.gridSize || 20;

        const cx = width / 2;
        const cy = height / 2;
        const dx = t0.relX - cx;
        const dy = t0.relY - cy;

        const rad = (rotation * Math.PI) / 180;
        const cos = Math.cos(rad);
        const sin = Math.sin(rad);

        const rotX0 = dx * cos - dy * sin;
        const rotY0 = dx * sin + dy * cos;

        const rawTerm0X = comp.x + cx + rotX0;
        const rawTerm0Y = comp.y + cy + rotY0;

        const snappedTerm0X = Math.round(rawTerm0X / gridSize) * gridSize;
        const snappedTerm0Y = Math.round(rawTerm0Y / gridSize) * gridSize;

        comp.x = Math.round(snappedTerm0X - cx - rotX0);
        comp.y = Math.round(snappedTerm0Y - cy - rotY0);
      }
      this.notify("components");
      this.notify("connections");
    }
  }

  updateComponentProperty(id, propertyKey, value) {
    const comp = this.state.components.find(c => c.id === id);
    if (comp && comp.properties) {
      this.recordHistory();
      comp.properties[propertyKey] = value;
      this.notify("components");
    }
  }

  deleteComponent(id) {
    this.recordHistory();
    this.state.components = this.state.components.filter(c => c.id !== id);
    // Remove all associated connections
    this.state.connections = this.state.connections.filter(
      conn => conn.from?.componentId !== id && (!conn.to?.componentId || conn.to.componentId !== id)
    );
    if (this.state.selection.id === id) {
      this.setSelection(null, null);
    }
    this.notify("components");
    this.notify("connections");
  }

  deleteConnection(id) {
    this.recordHistory();
    this.state.connections = this.state.connections.filter(c => 
      c.id !== id && c.to?.targetWireId !== id && c.from?.targetWireId !== id
    );
    if (this.state.selection.id === id) {
      this.setSelection(null, null);
    }
    this.notify("connections");
  }

  deleteSelection() {
    const { type, id } = this.state.selection;
    if (!type || !id) return;
    if (type === "component") {
      this.deleteComponent(id);
    } else if (type === "connection") {
      this.deleteConnection(id);
    }
  }

  // --- Selection ---
  setSelection(type, id) {
    this.state.selection = { type, id };
    this.notify("selection");
  }

  // --- Workspace Pan & Zoom ---
  setWorkspaceTransform(panX, panY, zoom) {
    this.state.workspace.panX = panX;
    this.state.workspace.panY = panY;
    this.state.workspace.zoom = zoom;
    this.notify("workspace");
  }

  resetWorkspace() {
    this.state.workspace.panX = 0;
    this.state.workspace.panY = 0;
    this.state.workspace.zoom = getDefaultWorkspaceZoom();
    this.notify("workspace");
  }

  clearWorkspace() {
    this.recordHistory();
    this.state.components = [];
    this.state.connections = [];
    this.state.selection = { type: null, id: null };
    this.notify("components");
    this.notify("connections");
    this.notify("selection");
  }
}

export const stateManager = new AppState();
