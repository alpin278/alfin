/**
 * DTE VirtualLab V2 — Central State Management
 * Single Source of Truth for Application State
 */

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
        zoom: 1.0,
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
    const comp = this.state.components.find(c => c.id === id);
    if (comp?.type === "multimeter" && comp.properties?.insertedIntoWire) {
      const { originalWire } = comp.properties.insertedIntoWire;
      if (!this.state.connections.some(c => c.id === originalWire.id)) {
        this.state.connections.push(originalWire);
      }
    }
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
    this.state.workspace.zoom = (window.innerWidth < 768) ? 0.78 : 1.0;
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
