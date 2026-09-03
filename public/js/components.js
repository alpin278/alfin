/**
 * DTE VirtualLab V2 — Component Registry & Drag-Drop Engine (Clean Terminal Protection)
 */

import { stateManager } from "./state.js";

// Standard EIA Resistor Color Code Table
export const RESISTOR_COLORS = [
  { digit: 0, name: "Hitam", hex: "#0f172a" },
  { digit: 1, name: "Coklat", hex: "#854d0e" },
  { digit: 2, name: "Merah", hex: "#dc2626" },
  { digit: 3, name: "Oranye", hex: "#ea580c" },
  { digit: 4, name: "Kuning", hex: "#facc15" },
  { digit: 5, name: "Hijau", hex: "#16a34a" },
  { digit: 6, name: "Biru", hex: "#2563eb" },
  { digit: 7, name: "Ungu", hex: "#9333ea" },
  { digit: 8, name: "Abu-abu", hex: "#64748b" },
  { digit: 9, name: "Putih", hex: "#f8fafc" }
];

export function calculateResistorBands(resistance) {
  const r = Math.max(Number(resistance) || 1, 0.1);
  let d1 = 2, d2 = 2, mult = 1;

  if (r >= 10) {
    const exponent = Math.floor(Math.log10(r));
    const normalized = r / Math.pow(10, exponent - 1);
    const rounded = Math.round(normalized);
    d1 = Math.floor(rounded / 10) % 10;
    d2 = rounded % 10;
    mult = Math.max(exponent - 1, 0);
  } else {
    d1 = Math.floor(r);
    d2 = Math.round((r - d1) * 10);
    mult = 8;
  }

  const b1Hex = RESISTOR_COLORS[d1]?.hex || "#dc2626";
  const b2Hex = RESISTOR_COLORS[d2]?.hex || "#dc2626";
  const b3Hex = mult === 8 ? "#d97706" : (RESISTOR_COLORS[mult]?.hex || "#854d0e");
  const b4Hex = "#d97706";

  return { b1Hex, b2Hex, b3Hex, b4Hex };
}

/**
 * Calculates the exact rotated world coordinate for any port or relative point on a component
 */
export function getRotatedPosition(originX, originY, width, height, relX, relY, rotation = 0) {
  const cx = width / 2;
  const cy = height / 2;
  const dx = relX - cx;
  const dy = relY - cy;

  const rad = (rotation * Math.PI) / 180;
  const cos = Math.cos(rad);
  const sin = Math.sin(rad);

  const rotX = dx * cos - dy * sin;
  const rotY = dx * sin + dy * cos;

  return {
    x: Math.round(originX + cx + rotX),
    y: Math.round(originY + cy + rotY)
  };
}

/**
 * Single Source of Truth for Terminal / Pin World Positions:
 * Direct mathematical calculation from component top-left placement, dimensions, terminal relative offsets, and rotation.
 * Invariant: Wire endpoints, DOM terminal markers, hitboxes, and leads all share this exact coordinate.
 */
export function getTerminalWorldPosition(compId, termId) {
  const state = stateManager.getState();
  const comp = state.components?.find(c => c.id === compId);
  if (!comp) return null;

  const term = comp.terminals?.find(t => t.id === termId);
  if (!term) return null;

  return getRotatedPosition(comp.x, comp.y, comp.width, comp.height, term.relX, term.relY, comp.rotation || 0);
}

/**
 * Terminal-First Grid Snapping:
 * Aligns the primary electrical terminal to the exact workspace grid dots,
 * ensuring all terminals land on the grid across all 4 rotations (0°, 90°, 180°, 270°).
 */
export function snapComponentToGrid(rawCompX, rawCompY, comp, gridSize = 20) {
  if (!comp || !comp.terminals || comp.terminals.length === 0 || comp.type === "multimeter") {
    return {
      x: Math.round(rawCompX / gridSize) * gridSize,
      y: Math.round(rawCompY / gridSize) * gridSize
    };
  }

  const t0 = comp.terminals[0];
  const width = comp.width;
  const height = comp.height;
  const rotation = comp.rotation || 0;

  const cx = width / 2;
  const cy = height / 2;
  const dx = t0.relX - cx;
  const dy = t0.relY - cy;

  const rad = (rotation * Math.PI) / 180;
  const cos = Math.cos(rad);
  const sin = Math.sin(rad);

  const rotX0 = dx * cos - dy * sin;
  const rotY0 = dx * sin + dy * cos;

  // 1. Calculate raw world position of reference terminal 0
  const rawTerm0X = rawCompX + cx + rotX0;
  const rawTerm0Y = rawCompY + cy + rotY0;

  // 2. Snap reference terminal 0 to exact grid dot
  const snappedTerm0X = Math.round(rawTerm0X / gridSize) * gridSize;
  const snappedTerm0Y = Math.round(rawTerm0Y / gridSize) * gridSize;

  // 3. Derive component top-left position
  const compX = Math.round(snappedTerm0X - cx - rotX0);
  const compY = Math.round(snappedTerm0Y - cy - rotY0);

  return { x: compX, y: compY };
}

/**
 * Single Source of Truth for Multimeter Banana Jack Relative Coordinates (Casing transform origin)
 * Derived from DOM elements: .meter-casing-vertical (132x194), .meter-jacks-panel flex distribution
 */
export const MULTIMETER_JACK_POSITIONS = {
  "10A":   { relX: 23, relY: 202, name: "10A",     label: "10A Jack (Arus Tinggi)" },
  "COM":   { relX: 52, relY: 202, name: "COM",     label: "COM Jack (Ground / Common)" },
  "V_OHM": { relX: 80, relY: 202, name: "V_OHM",   label: "V·Ω·mA Jack (Tegangan & Hambatan)" },
  "MA":    { relX: 109, relY: 202, name: "MA",      label: "mA Jack (Arus Rendah)" }
};

/**
 * Standard Multimeter Ranges for Voltage, Current, and Resistance
 */
export const MULTIMETER_RANGES = {
  "V_DC": [
    { label: "AUTO", max: Infinity, decimals: 2 },
    { label: "600mV", max: 0.6, decimals: 3, unit: "mV", scale: 1000 },
    { label: "6V", max: 6.0, decimals: 3 },
    { label: "60V", max: 60.0, decimals: 2 },
    { label: "600V", max: 600.0, decimals: 1 }
  ],
  "V_AC": [
    { label: "AUTO", max: Infinity, decimals: 2 },
    { label: "600mV", max: 0.6, decimals: 3, unit: "mV", scale: 1000 },
    { label: "6V", max: 6.0, decimals: 3 },
    { label: "60V", max: 60.0, decimals: 2 },
    { label: "600V", max: 600.0, decimals: 1 }
  ],
  "A_DC": [
    { label: "AUTO", max: Infinity, decimals: 3 },
    { label: "60mA", max: 0.06, decimals: 2, unit: "mA", scale: 1000 },
    { label: "600mA", max: 0.6, decimals: 1, unit: "mA", scale: 1000 },
    { label: "10A", max: 10.0, decimals: 3 }
  ],
  "A_AC": [
    { label: "AUTO", max: Infinity, decimals: 3 },
    { label: "60mA", max: 0.06, decimals: 2, unit: "mA", scale: 1000 },
    { label: "600mA", max: 0.6, decimals: 1, unit: "mA", scale: 1000 },
    { label: "10A", max: 10.0, decimals: 3 }
  ],
  "OHM": [
    { label: "AUTO", max: Infinity },
    { label: "600Ω", max: 600, decimals: 1 },
    { label: "6kΩ", max: 6000, decimals: 3, unit: "kΩ", scale: 0.001 },
    { label: "60kΩ", max: 60000, decimals: 2, unit: "kΩ", scale: 0.001 },
    { label: "600kΩ", max: 600000, decimals: 1, unit: "kΩ", scale: 0.001 },
    { label: "6MΩ", max: 6000000, decimals: 3, unit: "MΩ", scale: 0.000001 }
  ]
};

/**
 * Single Source of Truth for Multimeter Rotary Dial Angles (Degrees)
 */
export const MULTIMETER_MODE_ANGLES = {
  "V_DC": -57,
  "V_AC": -90,
  "OHM":    0,
  "A_DC":  57,
  "A_AC":  90
};

/**
 * Maps measurement mode and probeKey to the corresponding banana jack identifier
 */
export function getMultimeterJackKey(mode, probeKey) {
  if (probeKey === "com") {
    return "COM";
  }
  // Red probe (vwma) mapping based on measurement mode
  switch (mode) {
    case "A_DC":
    case "A_AC":
    case "MA_DC":
    case "MA_AC":
    case "MA":
      return "MA"; // Generic Ampere / mA mode uses right-side mA/A jack (relX: 109, relY: 202)
    case "10A_DC":
    case "10A_AC":
    case "10A":
      return "10A"; // Dedicated 10A high current mode (relX: 23, relY: 202)
    case "V_DC":
    case "V_AC":
    case "OHM":
    default:
      return "V_OHM"; // Voltage / Resistance uses V·Ω·mA jack (relX: 80, relY: 202)
  }
}

/**
 * Single Source of Truth for Multimeter Jack World Coordinates (Derived from transform & mode)
 */
export function getMultimeterJackPosition(comp, probeKey) {
  const rotation = comp.rotation || 0;
  const mode = comp.properties?.mode || "V_DC";
  const jackKey = getMultimeterJackKey(mode, probeKey);
  const jack = MULTIMETER_JACK_POSITIONS[jackKey] || MULTIMETER_JACK_POSITIONS["COM"];

  return getRotatedPosition(comp.x, comp.y, comp.width, comp.height, jack.relX, jack.relY, rotation);
}

/**
 * Single Source of Truth for Multimeter Front Lead Handoff World Coordinates (Casing bottom rim relY = 234)
 */
export function getMultimeterHandoffPosition(comp, probeKey) {
  const rotation = comp.rotation || 0;
  const mode = comp.properties?.mode || "V_DC";
  const jackKey = getMultimeterJackKey(mode, probeKey);
  const jack = MULTIMETER_JACK_POSITIONS[jackKey] || MULTIMETER_JACK_POSITIONS["COM"];

  return getRotatedPosition(comp.x, comp.y, comp.width, comp.height, jack.relX, 234, rotation);
}

/**
 * Single Source of Truth for Probe Tip / Endpoint World Coordinates
 */
export function getProbeTipPosition(comp, probeKey, workspace = null) {
  const probeState = comp.properties?.probes?.[probeKey];
  const rotation = comp.rotation || 0;
  const defaultRelX = probeKey === "com" ? 28 : 104;
  const defaultRelY = 285;

  // PRIORITY 1: Probe is actively being dragged (realtime drag coordinates take precedence)
  if (probeState?.isDragging && probeState.dragWorldX !== undefined && probeState.dragWorldY !== undefined) {
    return {
      pos: { x: probeState.dragWorldX, y: probeState.dragWorldY },
      isConnected: false,
      attachedTo: probeState.attachedTo || null
    };
  }

  // PRIORITY 2: Probe is attached to a circuit component terminal
  if (probeState?.attachedTo && probeState.attachedTo.compId && probeState.attachedTo.termId) {
    const connEngine = workspace?.connectionEngine;
    const targetPos = connEngine
      ? connEngine.getTerminalWorldPosition(probeState.attachedTo.compId, probeState.attachedTo.termId)
      : getTerminalWorldPosition(probeState.attachedTo.compId, probeState.attachedTo.termId);
    if (targetPos) {
      return {
        pos: targetPos,
        isConnected: true,
        attachedTo: probeState.attachedTo
      };
    }
    // Target terminal / component was deleted or unavailable
    probeState.attachedTo = null;
    probeState.isPlaced = false;
    delete probeState.worldX;
    delete probeState.worldY;
  }

  // PRIORITY 3: Probe is freely placed in open space (custom world coordinates)
  if (probeState?.isPlaced && probeState.worldX !== undefined && probeState.worldY !== undefined) {
    return {
      pos: { x: probeState.worldX, y: probeState.worldY },
      isConnected: false,
      attachedTo: null
    };
  }

  // PRIORITY 4: Idle / Docked on multimeter body (follows multimeter casing transform)
  const defaultPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, defaultRelX, defaultRelY, rotation);
  return {
    pos: defaultPos,
    isConnected: false,
    attachedTo: null
  };
}

export const COMPONENT_PROTOTYPES = {
  battery: {
    type: "battery",
    name: "Baterai DC",
    icon: "🔋",
    width: 140,
    height: 80,
    defaultProps: { voltage: 12 },
    terminals: [
      { id: "term_pos", name: "+", label: "+ (12V)", relX: 130, relY: 40, color: "#ef4444" },
      { id: "term_neg", name: "-", label: "- (GND)", relX: 10, relY: 40, color: "#0f172a" }
    ]
  },
  switch_spst: {
    type: "switch_spst",
    name: "Saklar Rocker",
    icon: "🎚️",
    width: 140,
    height: 80,
    defaultProps: { isClosed: false },
    terminals: [
      { id: "term_1", name: "1", label: "Pin Input (1)", relX: 10, relY: 40, color: "#38bdf8" },
      { id: "term_2", name: "2", label: "Pin Output (2)", relX: 130, relY: 40, color: "#38bdf8" }
    ]
  },
  lamp: {
    type: "lamp",
    name: "Lampu Pijar",
    icon: "💡",
    width: 100,
    height: 100,
    defaultProps: { nominalVoltage: 12, powerRating: 20, resistance: 7.2 },
    terminals: [
      { id: "term_pos", name: "+", label: "Pin +", relX: 20, relY: 80, color: "#ef4444" },
      { id: "term_neg", name: "-", label: "Pin -", relX: 80, relY: 80, color: "#0f172a" }
    ]
  },
  led: {
    type: "led",
    name: "LED Merah",
    icon: "🔴",
    width: 80,
    height: 80,
    defaultProps: { forwardVoltage: 2.0, nominalCurrent: 0.020, maxContinuousCurrent: 0.025 },
    terminals: [
      { id: "term_anode", name: "A", label: "Anoda (+)", relX: 20, relY: 60, color: "#ef4444" },
      { id: "term_cathode", name: "K", label: "Katoda (-)", relX: 60, relY: 60, color: "#0f172a" }
    ]
  },
  resistor: {
    type: "resistor",
    name: "Resistor",
    icon: "〰️",
    width: 80,
    height: 40,
    defaultProps: { resistance: 220 },
    terminals: [
      { id: "term_a", name: "A", label: "Pin A", relX: 0, relY: 20, color: "#38bdf8" },
      { id: "term_b", name: "B", label: "Pin B", relX: 80, relY: 20, color: "#38bdf8" }
    ]
  },
  multimeter: {
    type: "multimeter",
    name: "Multimeter Digital",
    icon: "📟",
    width: 132,
    height: 304,
    defaultProps: {
      powerOn: false,
      holdEnabled: false,
      heldReading: null,
      heldDisplay: null,
      mode: "V_DC",
      rangeMode: "AUTO",
      rangeIndex: 0,
      reading: "OFF",
      unit: "V"
    },
    terminals: [
      { id: "term_com", name: "COM", label: "Probe COM (Hitam / Ground)", relX: 28, relY: 285, color: "#0f172a" },
      { id: "term_vwma", name: "VΩ", label: "Probe VΩmA (Merah / +)", relX: 104, relY: 285, color: "#ef4444" }
    ]
  },
  motor_dc: {
    type: "motor_dc",
    name: "Motor DC",
    icon: "⚙️",
    width: 120,
    height: 100,
    defaultProps: {
      nominalVoltage: 12,
      maxRpm: 3000,
      noLoadRpm: 3000,
      noLoadCurrent: 0.30,
      armatureResistance: 1.0,
      resistance: 1.0,
      ratedCurrent: 2.0,
      loadPercent: 0,
      currentRpm: 0,
      direction: "CW"
    },
    terminals: [
      { id: "term_pos", name: "+", label: "Pin + (Merah)", relX: 20, relY: 80, color: "#ef4444" },
      { id: "term_neg", name: "-", label: "Pin - (Hitam)", relX: 100, relY: 80, color: "#0f172a" }
    ]
  },
  diode: {
    type: "diode",
    name: "Dioda 1N4007",
    icon: "🔺",
    width: 80,
    height: 40,
    defaultProps: { forwardVoltage: 0.7, model: "1N4007", state: "IDLE", resistance: 0.5 },
    terminals: [
      { id: "term_anode", name: "A", label: "Anoda (A / +)", relX: 0, relY: 20, color: "#38bdf8" },
      { id: "term_cathode", name: "K", label: "Katoda (K / - Garis)", relX: 80, relY: 20, color: "#94a3b8" }
    ]
  }
};

let componentCounter = 1;
let probeDragDebugId = 0;
let globalProbeBindingCounter = 0;

export class ComponentEngine {
  /**
   * @param {import("./workspace.js").WorkspaceEngine} workspaceEngine 
   */
  constructor(workspaceEngine) {
    this.workspace = workspaceEngine;
    this.layer = document.getElementById("components-layer");
    this.draggedItemType = null;
    this.multimeterPlugAnimations = new Map();
  }

  init() {
    this.bindPaletteDragEvents();
    this.bindWorkspaceDropEvents();
    this.bindKeyboardEvents();

    stateManager.subscribe("components", () => {
      this.syncDOM();
      this.updateAllMultimeterProbes();
    });
    stateManager.subscribe("components_moving", () => {
      this.updateAllMultimeterProbes();
    });
    stateManager.subscribe("selection", () => {
      this.updateSelectionVisuals();
    });
    stateManager.subscribe("connections", () => {
      this.updateAllMultimeterProbes();
    });
  }

  bindPaletteDragEvents() {
    const paletteCards = document.querySelectorAll(".component-card");
    paletteCards.forEach((card) => {
      card.addEventListener("dragstart", (e) => {
        const type = card.getAttribute("data-component-type");
        this.draggedItemType = type;
        e.dataTransfer.setData("text/plain", type);
        e.dataTransfer.effectAllowed = "copy";
      });

      card.addEventListener("dragend", () => {
        this.draggedItemType = null;
      });

      card.addEventListener("click", () => {
        const type = card.getAttribute("data-component-type");
        if (type && COMPONENT_PROTOTYPES[type]) {
          const rect = this.workspace.container.getBoundingClientRect();
          const pos = this.workspace.screenToCanvas(rect.left + rect.width / 2, rect.top + rect.height / 2);
          const gridSize = this.workspace.gridSize || 20;
          const rawX = pos.x - Math.round(COMPONENT_PROTOTYPES[type].width / 2);
          const rawY = pos.y - Math.round(COMPONENT_PROTOTYPES[type].height / 2);
          const snapped = snapComponentToGrid(rawX, rawY, COMPONENT_PROTOTYPES[type], gridSize);
          this.createComponent(type, snapped.x, snapped.y);
        }
      });
    });

    const searchInput = document.getElementById("palette-search-input");
    if (searchInput) {
      searchInput.addEventListener("input", (e) => {
        const q = e.target.value.toLowerCase().trim();
        paletteCards.forEach((card) => {
          const name = card.querySelector(".component-item-name")?.textContent.toLowerCase() || "";
          const sub = card.querySelector(".component-item-sub")?.textContent.toLowerCase() || "";
          if (name.includes(q) || sub.includes(q)) {
            card.style.display = "flex";
          } else {
            card.style.display = "none";
          }
        });
      });
    }
  }

  bindWorkspaceDropEvents() {
    const container = this.workspace.container;
    if (!container) return;

    container.addEventListener("dragover", (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = "copy";
    });

    container.addEventListener("drop", (e) => {
      e.preventDefault();
      const type = e.dataTransfer.getData("text/plain") || this.draggedItemType;
      if (!type) return;

      if (!COMPONENT_PROTOTYPES[type]) return;

      const pos = this.workspace.screenToCanvas(e.clientX, e.clientY);
      const proto = COMPONENT_PROTOTYPES[type];
      const gridSize = this.workspace.gridSize || 20;
      
      const rawX = pos.x - Math.round(proto.width / 2);
      const rawY = pos.y - Math.round(proto.height / 2);
      const snapped = snapComponentToGrid(rawX, rawY, proto, gridSize);

      this.createComponent(type, snapped.x, snapped.y);
    });
  }

  bindKeyboardEvents() {
    window.addEventListener("keydown", (e) => {
      const state = stateManager.getState();
      if (["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement?.tagName)) return;
      
      if (state.selection.type === "component" && state.selection.id) {
        if (e.key === "r" || e.key === "R") {
          e.preventDefault();
          stateManager.rotateComponent(state.selection.id, 90);
        }

        if (e.key === "Delete" || e.key === "Backspace") {
          e.preventDefault();
          stateManager.deleteComponent(state.selection.id);
        }
      } else if (state.selection.type === "connection" && state.selection.id) {
        if (e.key === "Delete" || e.key === "Backspace") {
          e.preventDefault();
          if (this.workspace?.connectionEngine) {
            this.workspace.connectionEngine.deleteConnection(state.selection.id);
          } else {
            stateManager.deleteConnection(state.selection.id);
          }
        }
      }
    });
  }

  createComponent(type, x, y) {
    // Batalkan kabel in-progress jika user sedang mode menyambung saat menambah komponen baru
    if (this.workspace?.connectionEngine?.isConnecting) {
      this.workspace.connectionEngine.cancelConnecting();
    }

    const proto = COMPONENT_PROTOTYPES[type];
    if (!proto) return;

    const state = stateManager.getState();
    const id = `${type}-${String(componentCounter++).padStart(3, "0")}`;

    // Prevent direct overlap with existing components
    let finalX = Math.round(x);
    let finalY = Math.round(y);
    const compW = proto.width || 100;
    const compH = proto.height || 60;

    let overlap = true;
    let attempts = 0;
    while (overlap && attempts < 10) {
      overlap = state.components.some(c => {
        const otherW = c.width || 100;
        const otherH = c.height || 60;
        return (
          Math.abs(finalX - c.x) < Math.max(compW, otherW) * 0.75 &&
          Math.abs(finalY - c.y) < Math.max(compH, otherH) * 0.75
        );
      });
      if (overlap) {
        finalX += 30;
        finalY += 30;
        attempts++;
      }
    }

    if (type !== "multimeter") {
      const snapped = snapComponentToGrid(finalX, finalY, proto, this.workspace?.gridSize || 20);
      finalX = snapped.x;
      finalY = snapped.y;
    }

    const newComponent = {
      id,
      type,
      name: `${proto.name} ${componentCounter - 1}`,
      x: finalX,
      y: finalY,
      rotation: 0,
      width: proto.width,
      height: proto.height,
      properties: JSON.parse(JSON.stringify(proto.defaultProps)),
      terminals: JSON.parse(JSON.stringify(proto.terminals))
    };

    if (type === "multimeter") {
      newComponent.properties.probes = {
        com: { attachedTo: null, isPlaced: false },
        vwma: { attachedTo: null, isPlaced: false }
      };
    }

    stateManager.addComponent(newComponent);
  }

  syncDOM() {
    if (!this.layer) return;

    const state = stateManager.getState();
    const existingIds = new Set();

    // 1. Sync Component Bodies
    state.components.forEach((comp) => {
      existingIds.add(`comp-${comp.id}`);
      let el = document.getElementById(`comp-${comp.id}`);

      if (!el) {
        el = this.createComponentElement(comp);
        this.layer.appendChild(el);
      } else {
        this.updateComponentVisualProperties(el, comp);
      }
    });

    const currentEls = this.layer.querySelectorAll(".workspace-component");
    currentEls.forEach((el) => {
      if (!existingIds.has(el.id)) {
        el.remove();
      }
    });

    // 2. Sync Multimeter Probes & Wires (Decoupled at Canvas Level)
    const svgLayer = document.getElementById("svg-cable-layer");
    let meterProbesGroup = document.getElementById("meter-probes-wires-group");
    if (!meterProbesGroup && svgLayer) {
      meterProbesGroup = document.createElementNS("http://www.w3.org/2000/svg", "g");
      meterProbesGroup.setAttribute("id", "meter-probes-wires-group");
      svgLayer.appendChild(meterProbesGroup);
    }

    let svgFrontLayer = document.getElementById("svg-front-cable-layer");
    if (!svgFrontLayer && this.workspace?.container) {
      const canvasLayer = document.getElementById("canvas-layer");
      if (canvasLayer) {
        svgFrontLayer = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svgFrontLayer.setAttribute("id", "svg-front-cable-layer");
        svgFrontLayer.setAttribute("class", "cables-front-svg-layer");
        canvasLayer.appendChild(svgFrontLayer);
      }
    }

    let meterFrontGroup = document.getElementById("meter-front-group");
    if (!meterFrontGroup && svgFrontLayer) {
      meterFrontGroup = document.createElementNS("http://www.w3.org/2000/svg", "g");
      meterFrontGroup.setAttribute("id", "meter-front-group");
      svgFrontLayer.appendChild(meterFrontGroup);
    }

    const multimeters = state.components.filter(c => c.type === "multimeter");
    const activeMeterIds = new Set(multimeters.map(m => m.id));

    // Remove orphaned probe elements & wires
    this.layer.querySelectorAll(".probe-assembly").forEach(el => {
      const compId = el.getAttribute("data-comp-id");
      if (!activeMeterIds.has(compId)) {
        el.remove();
      }
    });

    if (meterProbesGroup) {
      meterProbesGroup.querySelectorAll(".meter-probe-wire").forEach(el => {
        const compId = el.getAttribute("data-comp-id");
        if (!activeMeterIds.has(compId)) {
          el.remove();
        }
      });
    }

    if (meterFrontGroup) {
      meterFrontGroup.querySelectorAll(".meter-front-lead, .meter-banana-plug").forEach(el => {
        const compId = el.getAttribute("data-comp-id");
        if (!activeMeterIds.has(compId)) {
          el.remove();
          this.multimeterPlugAnimations?.delete(compId);
        }
      });
    }

    // Ensure probe elements & wires exist and are updated
    multimeters.forEach(comp => {
      if (!document.getElementById(`probe-com-${comp.id}`)) {
        const comEl = this.createProbeElement(comp, "com");
        this.layer.appendChild(comEl);
      }
      if (!document.getElementById(`probe-vwma-${comp.id}`)) {
        const vwmaEl = this.createProbeElement(comp, "vwma");
        this.layer.appendChild(vwmaEl);
      }

      if (meterProbesGroup) {
        if (!document.getElementById(`meter-wire-com-${comp.id}`)) {
          const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
          path.setAttribute("id", `meter-wire-com-${comp.id}`);
          path.setAttribute("class", "meter-probe-wire probe-wire-black");
          path.setAttribute("data-comp-id", comp.id);
          path.setAttribute("fill", "none");
          path.setAttribute("stroke", "#0f172a");
          path.setAttribute("stroke-width", "4.5");
          path.setAttribute("stroke-linecap", "round");
          meterProbesGroup.appendChild(path);
        }
        if (!document.getElementById(`meter-wire-vwma-${comp.id}`)) {
          const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
          path.setAttribute("id", `meter-wire-vwma-${comp.id}`);
          path.setAttribute("class", "meter-probe-wire probe-wire-red");
          path.setAttribute("data-comp-id", comp.id);
          path.setAttribute("fill", "none");
          path.setAttribute("stroke", "#dc2626");
          path.setAttribute("stroke-width", "4.5");
          path.setAttribute("stroke-linecap", "round");
          meterProbesGroup.appendChild(path);
        }
      }

      if (meterFrontGroup) {
        if (!document.getElementById(`meter-front-lead-com-${comp.id}`)) {
          const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
          path.setAttribute("id", `meter-front-lead-com-${comp.id}`);
          path.setAttribute("class", "meter-front-lead front-lead-black");
          path.setAttribute("data-comp-id", comp.id);
          path.setAttribute("fill", "none");
          path.setAttribute("stroke", "#0f172a");
          path.setAttribute("stroke-width", "4.5");
          path.setAttribute("stroke-linecap", "round");
          path.setAttribute("style", "filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); pointer-events: none;");
          meterFrontGroup.appendChild(path);
        }
        if (!document.getElementById(`meter-plug-com-${comp.id}`)) {
          const plugCom = this.createBananaPlugElement("com", comp.id);
          meterFrontGroup.appendChild(plugCom);
        }
        if (!document.getElementById(`meter-front-lead-vwma-${comp.id}`)) {
          const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
          path.setAttribute("id", `meter-front-lead-vwma-${comp.id}`);
          path.setAttribute("class", "meter-front-lead front-lead-red");
          path.setAttribute("data-comp-id", comp.id);
          path.setAttribute("fill", "none");
          path.setAttribute("stroke", "#dc2626");
          path.setAttribute("stroke-width", "4.5");
          path.setAttribute("stroke-linecap", "round");
          path.setAttribute("style", "filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); pointer-events: none;");
          meterFrontGroup.appendChild(path);
        }
        if (!document.getElementById(`meter-plug-vwma-${comp.id}`)) {
          const plugVwma = this.createBananaPlugElement("vwma", comp.id);
          meterFrontGroup.appendChild(plugVwma);
        }
      }

      this.updateMultimeterProbeVisuals(comp);
    });
  }

  createComponentElement(comp) {
    const el = document.createElement("div");
    el.className = `workspace-component comp-${comp.type}`;
    el.id = `comp-${comp.id}`;
    el.style.left = `${comp.x}px`;
    el.style.top = `${comp.y}px`;
    el.style.width = `${comp.width}px`;
    el.style.height = `${comp.height}px`;
    el.style.transform = `rotate(${comp.rotation || 0}deg)`;
    el.style.transformOrigin = "center center";

    const state = stateManager.getState();
    if (state.selection.id === comp.id) {
      el.classList.add("selected");
    }

    el.innerHTML = this.getComponentInnerHTML(comp);

    if (comp.type !== "multimeter") {
      comp.terminals.forEach((term) => {
        const termEl = document.createElement("div");
        termEl.className = "terminal-node";
        termEl.id = `term-${comp.id}-${term.id}`;
        termEl.setAttribute("data-comp-id", comp.id);
        termEl.setAttribute("data-term-id", term.id);
        termEl.style.left = `${term.relX}px`;
        termEl.style.top = `${term.relY}px`;
        termEl.title = `${comp.name}: ${term.label}`;

        const dotEl = document.createElement("div");
        dotEl.className = "terminal-dot";
        termEl.appendChild(dotEl);

        el.appendChild(termEl);
      });
    } else {
      // Dedicated Multimeter Controls: Power, Hold, Mode, Range & Dial Selectors
      const powerBtn = el.querySelector(".meter-power-btn") || el.querySelector(`#meter-btn-power-${comp.id}`);
      const holdBtn = el.querySelector(".btn-hold") || el.querySelector(`#meter-btn-hold-${comp.id}`) || el.querySelector(".meter-function-row .meter-chip-btn:nth-child(2)");
      const modeBtn = el.querySelector(".btn-mode") || el.querySelector(`#meter-btn-mode-${comp.id}`) || el.querySelector(".meter-function-row .meter-chip-btn:nth-child(3)");
      const rangeBtn = el.querySelector(".btn-range") || el.querySelector(`#meter-btn-range-${comp.id}`) || el.querySelector(".meter-function-row .meter-chip-btn:nth-child(4)");
      const dialEl = el.querySelector(".meter-rotary-knob") || el.querySelector(`#meter-dial-${comp.id}`);
      const labelV = el.querySelector(".label-v");
      const labelVac = el.querySelector(".label-vac");
      const labelOhm = el.querySelector(".label-ohm");
      const labelA = el.querySelector(".label-a");
      const labelAac = el.querySelector(".label-aac");

      // 1. POWER Button
      const handlePowerToggle = (e) => {
        if (e) {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
        }
        const currentPower = comp.properties.powerOn !== false;
        const nextPower = !currentPower;
        comp.properties.powerOn = nextPower;
        if (!nextPower) {
          comp.properties.holdEnabled = false;
          comp.properties.heldDisplay = null;
          comp.properties.heldReading = null;
        }
        this.updateComponentVisualProperties(el, comp);
        stateManager.updateComponentProperty(comp.id, "powerOn", nextPower);
        stateManager.updateComponentProperty(comp.id, "holdEnabled", comp.properties.holdEnabled);
        stateManager.updateComponentProperty(comp.id, "heldDisplay", comp.properties.heldDisplay);
        stateManager.updateComponentProperty(comp.id, "heldReading", comp.properties.heldReading);
        stateManager.notify("simulation");
      };

      if (powerBtn) {
        powerBtn.addEventListener("click", handlePowerToggle);
        powerBtn.addEventListener("pointerdown", (e) => e.stopPropagation());
      }

      // 2. HOLD Button
      const handleHoldToggle = (e) => {
        if (e) {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
        }
        if (comp.properties.powerOn === false) return;

        const isHeld = comp.properties.holdEnabled === true;
        if (!isHeld) {
          comp.properties.holdEnabled = true;
          const heldObj = {
            text: comp.properties.reading || "0.00",
            unit: comp.properties.unit || (comp.properties.mode === "OHM" ? "Ω" : (comp.properties.mode?.startsWith("A") ? "A" : "V"))
          };
          comp.properties.heldDisplay = heldObj;
          comp.properties.heldReading = heldObj;
        } else {
          comp.properties.holdEnabled = false;
          comp.properties.heldDisplay = null;
          comp.properties.heldReading = null;
        }
        this.updateComponentVisualProperties(el, comp);
        stateManager.updateComponentProperty(comp.id, "holdEnabled", comp.properties.holdEnabled);
        stateManager.updateComponentProperty(comp.id, "heldDisplay", comp.properties.heldDisplay);
        stateManager.updateComponentProperty(comp.id, "heldReading", comp.properties.heldReading);
        stateManager.notify("simulation");
      };

      if (holdBtn) {
        holdBtn.addEventListener("click", handleHoldToggle);
        holdBtn.addEventListener("pointerdown", (e) => e.stopPropagation());
      }

      // 3. MODE Button (Cycles submode within category: V_DC <-> V_AC, A_DC <-> A_AC, OHM)
      const handleModeToggle = (e) => {
        if (e) {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
        }
        if (comp.properties.powerOn === false) return;

        const curr = comp.properties.mode || "V_DC";
        let nextMode = curr;
        if (curr === "V_DC") nextMode = "V_AC";
        else if (curr === "V_AC") nextMode = "V_DC";
        else if (curr === "A_DC") nextMode = "A_AC";
        else if (curr === "A_AC") nextMode = "A_DC";
        else if (curr === "OHM") nextMode = "OHM";

        comp.properties.mode = nextMode;
        comp.properties.holdEnabled = false;
        comp.properties.heldDisplay = null;
        comp.properties.heldReading = null;
        comp.properties.rangeIndex = 0;
        comp.properties.rangeMode = "AUTO";
        comp.properties.selectedRange = null;

        this.updateComponentVisualProperties(el, comp);
        stateManager.updateComponentProperty(comp.id, "mode", nextMode);
        stateManager.updateComponentProperty(comp.id, "holdEnabled", false);
        stateManager.updateComponentProperty(comp.id, "heldDisplay", null);
        stateManager.updateComponentProperty(comp.id, "heldReading", null);
        stateManager.updateComponentProperty(comp.id, "rangeIndex", 0);
        stateManager.updateComponentProperty(comp.id, "rangeMode", "AUTO");
        stateManager.updateComponentProperty(comp.id, "selectedRange", null);
        stateManager.notify("simulation");
      };

      if (modeBtn) {
        modeBtn.addEventListener("click", handleModeToggle);
        modeBtn.addEventListener("pointerdown", (e) => e.stopPropagation());
      }

      // 4. RANGE Button (Cycles AUTO -> Manual Ranges -> AUTO)
      const handleRangeToggle = (e) => {
        if (e) {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
        }
        if (comp.properties.powerOn === false) return;

        const mode = comp.properties.mode || "V_DC";
        const ranges = MULTIMETER_RANGES[mode] || MULTIMETER_RANGES["V_DC"];
        const currIndex = comp.properties.rangeIndex || 0;
        const nextIndex = (currIndex + 1) % ranges.length;

        comp.properties.rangeIndex = nextIndex;
        comp.properties.rangeMode = nextIndex === 0 ? "AUTO" : "MANUAL";
        comp.properties.selectedRange = nextIndex === 0 ? null : ranges[nextIndex];

        this.updateComponentVisualProperties(el, comp);
        stateManager.updateComponentProperty(comp.id, "rangeIndex", nextIndex);
        stateManager.updateComponentProperty(comp.id, "rangeMode", comp.properties.rangeMode);
        stateManager.updateComponentProperty(comp.id, "selectedRange", comp.properties.selectedRange);
        stateManager.notify("simulation");
      };

      if (rangeBtn) {
        rangeBtn.addEventListener("click", handleRangeToggle);
        rangeBtn.addEventListener("pointerdown", (e) => e.stopPropagation());
      }

      // 5. Rotary Knob & Dial Label Selectors
      const setMeterMode = (newMode, e) => {
        if (e) {
          e.stopPropagation();
          if (e.cancelable) e.preventDefault();
        }
        if (comp.properties.powerOn === false) return;
        comp.properties.mode = newMode;
        comp.properties.holdEnabled = false;
        comp.properties.heldDisplay = null;
        comp.properties.heldReading = null;
        comp.properties.rangeIndex = 0;
        comp.properties.rangeMode = "AUTO";
        comp.properties.selectedRange = null;

        this.updateComponentVisualProperties(el, comp);
        stateManager.updateComponentProperty(comp.id, "mode", newMode);
        stateManager.updateComponentProperty(comp.id, "holdEnabled", false);
        stateManager.updateComponentProperty(comp.id, "heldDisplay", null);
        stateManager.updateComponentProperty(comp.id, "heldReading", null);
        stateManager.updateComponentProperty(comp.id, "rangeIndex", 0);
        stateManager.updateComponentProperty(comp.id, "rangeMode", "AUTO");
        stateManager.updateComponentProperty(comp.id, "selectedRange", null);
        stateManager.notify("simulation");
      };

      if (dialEl) {
        const onDialClick = (e) => {
          const mainModes = ["V_DC", "OHM", "A_DC"];
          const curr = comp.properties.mode || "V_DC";
          const next = mainModes[(mainModes.indexOf(curr) + 1) % mainModes.length] || "V_DC";
          setMeterMode(next, e);
        };
        dialEl.addEventListener("click", onDialClick);
        dialEl.addEventListener("pointerdown", (e) => e.stopPropagation());
      }

      if (labelV) {
        labelV.addEventListener("click", (e) => setMeterMode("V_DC", e));
        labelV.addEventListener("pointerdown", (e) => e.stopPropagation());
      }
      if (labelVac) {
        labelVac.addEventListener("click", (e) => setMeterMode("V_AC", e));
        labelVac.addEventListener("pointerdown", (e) => e.stopPropagation());
      }
      if (labelOhm) {
        labelOhm.addEventListener("click", (e) => setMeterMode("OHM", e));
        labelOhm.addEventListener("pointerdown", (e) => e.stopPropagation());
      }
      if (labelA) {
        labelA.addEventListener("click", (e) => setMeterMode("A_DC", e));
        labelA.addEventListener("pointerdown", (e) => e.stopPropagation());
      }
      if (labelAac) {
        labelAac.addEventListener("click", (e) => setMeterMode("A_AC", e));
        labelAac.addEventListener("pointerdown", (e) => e.stopPropagation());
      }
    }

    this.bindComponentDrag(el, comp);

    const handleComponentModal = (e) => {
      if (
        e?.target?.closest(".meter-function-row") ||
        e?.target?.closest(".meter-power-btn") ||
        e?.target?.closest(".meter-chip-btn") ||
        e?.target?.closest(".meter-rotary-knob") ||
        e?.target?.closest(".meter-dial-label") ||
        e?.target?.closest(".terminal-node") ||
        e?.target?.closest(".probe-assembly")
      ) {
        return;
      }
      if (e) {
        e.stopPropagation();
        if (e.cancelable) e.preventDefault();
      }
      if (comp.type === "resistor") {
        this.openResistorModal(comp);
      } else if (comp.type === "battery") {
        this.openBatteryModal(comp);
      } else if (comp.type === "lamp") {
        this.openLampModal(comp);
      } else if (comp.type === "multimeter") {
        this.openMultimeterModal(comp);
      } else if (comp.type === "motor_dc") {
        this.openMotorModal(comp);
      } else if (comp.type === "diode") {
        this.openDiodeModal(comp);
      }
    };

    // 1. Desktop: event dblclick
    el.addEventListener("dblclick", handleComponentModal);

    // 2. iOS Safari: Cegah double-tap to zoom pada touchstart ke-2
    let lastCompTouchStart = 0;
    el.addEventListener("touchstart", (e) => {
      const now = Date.now();
      if (now - lastCompTouchStart < 300) {
        if (e.cancelable) e.preventDefault();
      }
      lastCompTouchStart = now;
    }, { passive: false });

    // 3. Mobile / Touchscreen: deteksi double-tap manual via touchend
    let lastCompTap = 0;
    el.addEventListener("touchend", (e) => {
      const currentTime = Date.now();
      const tapGap = currentTime - lastCompTap;
      if (tapGap < 300 && tapGap > 0) {
        if (e.cancelable) e.preventDefault();
        handleComponentModal(e);
      }
      lastCompTap = currentTime;
    }, { passive: false });

    return el;
  }

  getComponentInnerHTML(comp) {
    if (comp.type === "battery") {
      return `
        <div class="battery-visual">
          <div class="battery-body">
            <div class="battery-stripe"></div>
            <span class="battery-pole-neg">-</span>
            <div class="battery-center-label">
              <span class="battery-volt-text" id="volt-text-${comp.id}">${comp.properties.voltage}V</span>
              <span class="battery-type-text">DC POWER</span>
            </div>
            <span class="battery-pole-pos">+</span>
          </div>
        </div>
      `;
    } else if (comp.type === "switch_spst") {
      const isClosed = comp.properties.isClosed;
      return `
        <div class="switch-visual ${isClosed ? 'closed' : ''}" id="switch-vis-${comp.id}">
          <div class="switch-casing">
            <div class="switch-mount-screw screw-left"></div>
            <div class="switch-bezel">
              <div class="rocker-button ${isClosed ? 'on' : 'off'}" id="rocker-btn-${comp.id}">
                <div class="rocker-half rocker-half-on">
                  <span class="rocker-symbol">I</span>
                  <span class="rocker-neon-dot"></span>
                </div>
                <div class="rocker-divider"></div>
                <div class="rocker-half rocker-half-off">
                  <span class="rocker-symbol">O</span>
                </div>
              </div>
            </div>
            <div class="switch-mount-screw screw-right"></div>
          </div>
          <div class="switch-status-label" id="switch-lbl-${comp.id}">${isClosed ? 'ON (TERTUTUP)' : 'OFF (TERBUKA)'}</div>
        </div>
      `;
    } else if (comp.type === "lamp") {
      return `
        <div class="lamp-visual" id="lamp-vis-${comp.id}">
          <div class="lamp-bulb" id="lamp-bulb-${comp.id}">
            <div class="lamp-filament"></div>
          </div>
          <div class="lamp-base"></div>
          <div class="lamp-leads-container">
            <div class="lamp-lead-wire lamp-lead-left"></div>
            <div class="lamp-lead-wire lamp-lead-right"></div>
          </div>
          <div class="lamp-label" id="lamp-lbl-${comp.id}">${comp.properties.powerRating}W / ${comp.properties.nominalVoltage}V</div>
        </div>
      `;
    } else if (comp.type === "resistor") {
      const bands = calculateResistorBands(comp.properties.resistance || 220);
      return `
        <div class="resistor-visual" id="res-vis-${comp.id}">
          <div class="resistor-lead resistor-lead-left"></div>
          <div class="resistor-body">
            <div class="resistor-band band-1" style="background-color: ${bands.b1Hex};"></div>
            <div class="resistor-band band-2" style="background-color: ${bands.b2Hex};"></div>
            <div class="resistor-band band-3" style="background-color: ${bands.b3Hex};"></div>
            <div class="resistor-band band-4" style="background-color: ${bands.b4Hex};"></div>
          </div>
          <div class="resistor-lead resistor-lead-right"></div>
          <div class="resistor-label" id="res-lbl-${comp.id}">${comp.properties.resistance} Ω</div>
        </div>
      `;
    } else if (comp.type === "multimeter") {
      const mode = comp.properties.mode || "V_DC";
      const powerOn = comp.properties.powerOn !== false;
      const holdEnabled = comp.properties.holdEnabled === true;
      const rangeMode = comp.properties.rangeMode || "AUTO";
      const unit = mode === "OHM" ? "Ω" : (mode.startsWith("A") ? "A" : "V");
      let modeBadge = "DC";
      if (mode === "OHM") modeBadge = "Ω";
      else if (mode.endsWith("AC")) modeBadge = "AC";

      const dialAngle = MULTIMETER_MODE_ANGLES[mode] ?? -50;

      return `
        <div class="multimeter-visual fluke-179-style dark-edition ${powerOn ? '' : 'power-off'}">
          <div class="meter-casing-vertical">
            <div class="meter-header">
              <span class="meter-brand-badge">FLUXUS</span>
            </div>
            <div class="meter-lcd-bezel">
              <div class="meter-lcd-screen-light">
                <div class="meter-lcd-top-bar multimeter-lcd-status">
                  <span class="status-range lcd-badge-auto" id="meter-range-badge-${comp.id}">${rangeMode}</span>
                  <span class="status-mode lcd-badge-mode" id="meter-mode-badge-${comp.id}">${modeBadge}</span>
                  <span class="status-hold lcd-badge-hold" id="meter-hold-badge-${comp.id}" style="${holdEnabled ? 'visibility: visible;' : 'visibility: hidden;'}">HOLD</span>
                  <span class="status-rms lcd-badge-rms">RMS</span>
                </div>
                <div class="meter-lcd-main-light reading-row">
                  <span class="meter-comp-val" id="meter-val-${comp.id}">${powerOn ? (comp.properties.reading || '0.00') : 'OFF'}</span>
                  <span class="meter-comp-unit" id="meter-unit-${comp.id}">${powerOn ? (comp.properties.unit || unit) : ''}</span>
                </div>
                <div class="meter-lcd-bar-graph">
                  <div class="lcd-bar-scale">
                    <span>0</span>
                    <span>30</span>
                    <span>60</span>
                  </div>
                  <div class="lcd-bar-track">
                    <div class="lcd-bar-fill" id="meter-bargraph-${comp.id}"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="meter-function-row">
              <div class="meter-power-btn ${powerOn ? '' : 'off'}" id="meter-btn-power-${comp.id}" title="Toggle Multimeter Power">⏻</div>
              <div class="meter-chip-btn btn-hold ${holdEnabled ? 'active' : ''}" id="meter-btn-hold-${comp.id}" title="Hold Current Reading">HOLD</div>
              <div class="meter-chip-btn btn-mode" id="meter-btn-mode-${comp.id}" title="Toggle Submode (DC/AC)">MODE</div>
              <div class="meter-chip-btn btn-range ${rangeMode === 'MANUAL' ? 'active' : ''}" id="meter-btn-range-${comp.id}" title="Cycle Range (Auto/Manual)">RANGE</div>
            </div>

            <div class="meter-dial-section">
              <div class="meter-dial-scale">
                <span class="meter-dial-label label-v ${mode === 'V_DC' ? 'active' : ''}" title="Tegangan DC (Volt DC)">V⎓</span>
                <span class="meter-dial-label label-vac ${mode === 'V_AC' ? 'active' : ''}" title="Tegangan AC (Volt AC)">V~</span>
                <span class="meter-dial-label label-ohm ${mode === 'OHM' ? 'active' : ''}" title="Resistansi / Hambatan (Ohm)">Ω</span>
                <span class="meter-dial-label label-a ${mode === 'A_DC' ? 'active' : ''}" title="Arus DC (Ampere DC)">A⎓</span>
                <span class="meter-dial-label label-aac ${mode === 'A_AC' ? 'active' : ''}" title="Arus AC (Ampere AC)">A~</span>
              </div>
              <div class="meter-rotary-knob" id="meter-dial-${comp.id}" style="transform: rotate(${dialAngle}deg);">
                <div class="knob-face">
                  <div class="knob-bar-handle">
                    <div class="knob-pointer-arrow"></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="meter-jacks-panel jack-row">
              <div class="multimeter-jack jack-10a meter-jack-housing" title="10A Jack (High Current)">
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">10A</span>
              </div>
              <div class="multimeter-jack jack-com meter-jack-housing" title="COM Jack (Common / Ground Negative)">
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">COM</span>
              </div>
              <div class="multimeter-jack jack-v-ohm-ma jack-vwma meter-jack-housing" title="V-Ω-mA Jack (Voltage, Ohm & Milliamp)">
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">V-Ω-mA</span>
              </div>
              <div class="multimeter-jack jack-ma meter-jack-housing" title="mA / A Jack (Current Measurement)">
                <div class="jack-socket-rim jack-hole">
                  <div class="jack-metal-core jack-socket-hole"></div>
                </div>
                <span class="jack-label">mA/A</span>
              </div>
            </div>
          </div>
        </div>
      `;
    } else if (comp.type === "led") {
      const vf = comp.properties?.forwardVoltage || 2;
      const lbl = comp.properties?.label || `LED ${vf}V`;
      return `
        <div class="led-visual" id="led-vis-${comp.id}">
          <div class="led-leads-container">
            <div class="led-lead-wire led-lead-anode"></div>
            <div class="led-lead-wire led-lead-cathode"></div>
          </div>
          <div class="led-casing">
            <div class="led-dome">
              <div class="led-specular-highlight"></div>
              <div class="led-die-core">
                <div class="led-post"></div>
                <div class="led-anvil"></div>
              </div>
            </div>
            <div class="led-flange"></div>
          </div>
          <div class="led-label" id="led-lbl-${comp.id}">${lbl}</div>
        </div>
      `;
    } else if (comp.type === "motor_dc") {
      const v = comp.properties.nominalVoltage || 12;
      const rpm = comp.properties.maxRpm || comp.properties.noLoadRpm || 3000;
      return `
        <div class="motor-visual" id="motor-vis-${comp.id}">
          <div class="motor-casing">
            <div class="motor-metal-shine"></div>
            <div class="motor-rotor-hub">
              <div class="motor-rotor-blades" id="motor-rotor-${comp.id}">
                <div class="motor-blade blade-1"></div>
                <div class="motor-blade blade-2"></div>
                <div class="motor-blade blade-3"></div>
              </div>
              <div class="motor-shaft-center"></div>
            </div>
            <div class="motor-vents">
              <div class="motor-vent"></div>
              <div class="motor-vent"></div>
              <div class="motor-vent"></div>
            </div>
          </div>
          <div class="motor-label" id="motor-lbl-${comp.id}">${v}V (${rpm} RPM)</div>
          <div class="motor-rpm-badge" id="motor-rpm-${comp.id}">
            <span class="rpm-number">${comp.properties.currentRpm || 0}</span> <span class="rpm-unit">RPM</span>
          </div>
        </div>
      `;
    } else if (comp.type === "diode") {
      const model = comp.properties.model || "1N4007";
      return `
        <div class="diode-visual" id="diode-vis-${comp.id}">
          <div class="diode-lead diode-lead-left"></div>
          <div class="diode-body">
            <span class="diode-text">${model}</span>
            <span class="diode-symbol">▶|</span>
            <div class="diode-cathode-band"></div>
          </div>
          <div class="diode-lead diode-lead-right"></div>
          <div class="diode-label" id="diode-lbl-${comp.id}">${model}</div>
          <div class="diode-bias-badge bias-idle" id="diode-bias-${comp.id}">
            <span class="bias-indicator-dot"></span>
            <span class="bias-status-text">STANDBY</span>
          </div>
        </div>
      `;
    }
    return `<div class="unknown-component">${comp.name}</div>`;
  }

  updateComponentVisualProperties(el, comp) {
    if (!el || !comp) return;

    el.style.left = `${comp.x}px`;
    el.style.top = `${comp.y}px`;
    el.style.transform = `rotate(${comp.rotation || 0}deg)`;
    el.style.transformOrigin = "center center";

    if (comp.type === "battery") {
      const voltEl = el.querySelector(`#volt-text-${comp.id}`);
      if (voltEl) voltEl.textContent = `${comp.properties.voltage}V`;
    } else if (comp.type === "switch_spst") {
      const vis = el.querySelector(`#switch-vis-${comp.id}`);
      const rocker = el.querySelector(`#rocker-btn-${comp.id}`);
      const lbl = el.querySelector(`#switch-lbl-${comp.id}`);
      if (vis) {
        if (comp.properties.isClosed) {
          vis.classList.add("closed");
          el.classList.add("closed");
        } else {
          vis.classList.remove("closed");
          el.classList.remove("closed");
        }
      }
      if (rocker) {
        if (comp.properties.isClosed) {
          rocker.classList.remove("off");
          rocker.classList.add("on");
        } else {
          rocker.classList.remove("on");
          rocker.classList.add("off");
        }
      }
      if (lbl) lbl.textContent = comp.properties.isClosed ? "ON (TERTUTUP)" : "OFF (TERBUKA)";
    } else if (comp.type === "resistor") {
      const bands = calculateResistorBands(comp.properties.resistance || 220);
      const b1 = el.querySelector(".band-1");
      const b2 = el.querySelector(".band-2");
      const b3 = el.querySelector(".band-3");
      const b4 = el.querySelector(".band-4");
      const lbl = el.querySelector(`#res-lbl-${comp.id}`);

      if (b1) b1.style.backgroundColor = bands.b1Hex;
      if (b2) b2.style.backgroundColor = bands.b2Hex;
      if (b3) b3.style.backgroundColor = bands.b3Hex;
      if (b4) b4.style.backgroundColor = bands.b4Hex;
      if (lbl) lbl.textContent = `${comp.properties.resistance} Ω`;
    } else if (comp.type === "lamp") {
      const lbl = el.querySelector(`#lamp-lbl-${comp.id}`);
      if (lbl) lbl.textContent = `${comp.properties.powerRating}W / ${comp.properties.nominalVoltage}V`;
    } else if (comp.type === "led") {
      const lbl = el.querySelector(`#led-lbl-${comp.id}`) || el.querySelector(".led-label");
      const vf = comp.properties?.forwardVoltage || 2;
      if (lbl) lbl.textContent = comp.properties?.label || `LED ${vf}V`;
    } else if (comp.type === "multimeter") {
      const powerOn = comp.properties.powerOn !== false;
      const holdEnabled = comp.properties.holdEnabled === true;
      const rangeMode = comp.properties.rangeMode || "AUTO";
      const mode = comp.properties.mode || "V_DC";

      el.classList.toggle("power-off", !powerOn);
      const vis = el.querySelector(".multimeter-visual");
      if (vis) vis.classList.toggle("power-off", !powerOn);

      const valEl = el.querySelector(`#meter-val-${comp.id}`);
      const unitEl = el.querySelector(`#meter-unit-${comp.id}`);
      const modeBadge = el.querySelector(`#meter-mode-badge-${comp.id}`);
      const autoBadge = el.querySelector(`#meter-range-badge-${comp.id}`);
      const holdBadge = el.querySelector(`#meter-hold-badge-${comp.id}`);
      const dial = el.querySelector(`#meter-dial-${comp.id}`);
      const powerBtn = el.querySelector(`#meter-btn-power-${comp.id}`);
      const holdBtn = el.querySelector(`#meter-btn-hold-${comp.id}`);
      const rangeBtn = el.querySelector(`#meter-btn-range-${comp.id}`);

      if (powerBtn) powerBtn.classList.toggle("off", !powerOn);
      if (holdBtn) holdBtn.classList.toggle("active", holdEnabled);
      if (rangeBtn) rangeBtn.classList.toggle("active", rangeMode === "MANUAL");

      if (autoBadge) autoBadge.textContent = rangeMode;
      if (holdBadge) {
        holdBadge.style.visibility = holdEnabled ? "visible" : "hidden";
      }

      if (valEl) {
        if (!powerOn) {
          valEl.textContent = "OFF";
        } else {
          valEl.textContent = comp.properties.reading || "0.00";
        }
      }

      if (unitEl) {
        if (!powerOn) {
          unitEl.textContent = "";
        } else {
          unitEl.textContent = comp.properties.unit || (mode === "OHM" ? "Ω" : (mode.startsWith("A") ? "A" : "V"));
        }
      }

      if (modeBadge) {
        let badgeText = "DC";
        if (mode === "OHM") badgeText = "Ω";
        else if (mode.endsWith("AC")) badgeText = "AC";
        modeBadge.textContent = badgeText;
      }

      const labelV = el.querySelector(".label-v");
      const labelVac = el.querySelector(".label-vac");
      const labelOhm = el.querySelector(".label-ohm");
      const labelA = el.querySelector(".label-a");
      const labelAac = el.querySelector(".label-aac");
      if (labelV) labelV.classList.toggle("active", mode === "V_DC");
      if (labelVac) labelVac.classList.toggle("active", mode === "V_AC");
      if (labelOhm) labelOhm.classList.toggle("active", mode === "OHM");
      if (labelA) labelA.classList.toggle("active", mode === "A_DC");
      if (labelAac) labelAac.classList.toggle("active", mode === "A_AC");

      if (dial) {
        const angle = MULTIMETER_MODE_ANGLES[mode] ?? -57.3;
        dial.style.transform = `rotate(${angle}deg)`;
        dial.style.transition = "transform 160ms ease";
      }

      this.updateMultimeterProbeVisuals(comp);
    } else if (comp.type === "motor_dc") {
      const lbl = el.querySelector(`#motor-lbl-${comp.id}`) || el.querySelector(`.motor-label`);
      const rpmNum = el.querySelector(`#motor-rpm-${comp.id} .rpm-number`) || el.querySelector(`.rpm-number`);
      const rotor = el.querySelector(`#motor-rotor-${comp.id}`) || el.querySelector(`.motor-rotor-blades`);
      const v = comp.properties.nominalVoltage || 12;
      const rpm = comp.properties.maxRpm || comp.properties.noLoadRpm || 3000;
      if (lbl) lbl.textContent = `${v}V (${rpm} RPM)`;
      if (rpmNum) rpmNum.textContent = String(comp.properties.currentRpm || 0);
      if (rotor) {
        if ((comp.properties.currentRpm || 0) > 0) {
          rotor.classList.add("spinning");
          if (comp.properties.direction === "CCW") {
            rotor.classList.add("ccw");
          } else {
            rotor.classList.remove("ccw");
          }
        } else {
          rotor.classList.remove("spinning", "ccw");
        }
      }
    } else if (comp.type === "diode") {
      const lbl = el.querySelector(`#diode-lbl-${comp.id}`) || el.querySelector(".diode-label");
      const diodeVis = el.querySelector(`#diode-vis-${comp.id}`) || el.querySelector(".diode-visual");
      const biasBadge = el.querySelector(`#diode-bias-${comp.id}`) || el.querySelector(".diode-bias-badge");
      const biasStatus = biasBadge?.querySelector(".bias-status-text");
      const diodeState = comp.properties.state || "IDLE";
      const model = comp.properties.model || "1N4007";

      if (lbl) lbl.textContent = model;

      if (biasBadge && biasStatus) {
        biasBadge.classList.remove("bias-forward", "bias-reverse", "bias-idle", "state-forward_bias", "state-reverse_bias");
        if (diodeVis) diodeVis.classList.remove("forward-bias", "reverse-bias");

        if (diodeState === "FORWARD") {
          biasBadge.classList.add("bias-forward", "state-forward_bias");
          if (diodeVis) diodeVis.classList.add("forward-bias");
          biasStatus.textContent = "FORWARD BIAS";
        } else if (diodeState === "REVERSE") {
          biasBadge.classList.add("bias-reverse", "state-reverse_bias");
          if (diodeVis) diodeVis.classList.add("reverse-bias");
          biasStatus.textContent = "REVERSE BIAS";
        } else {
          biasBadge.classList.add("bias-idle");
          biasStatus.textContent = "STANDBY";
        }
      }
    }
  }

  bindComponentDrag(el, comp) {
    let startX = 0;
    let startY = 0;
    let compStartX = 0;
    let compStartY = 0;
    let isDragging = false;
    let hasMoved = false;
    let pointerId = null;

    const onPointerDown = (e) => {
      // If clicking near a terminal in wire-connecting mode -> prioritize terminal wiring
      if (this.workspace?.connectionEngine?.isConnecting) {
        const clientX = e.clientX ?? e.touches?.[0]?.clientX;
        const clientY = e.clientY ?? e.touches?.[0]?.clientY;
        if (clientX !== undefined && clientY !== undefined) {
          const rawPos = this.workspace?.screenToCanvasRaw 
            ? this.workspace.screenToCanvasRaw(clientX, clientY) 
            : this.workspace.screenToCanvas(clientX, clientY);
          const nearTerm = this.workspace.connectionEngine.findNearestTerminalSnap(rawPos.x, rawPos.y, 45);
          if (nearTerm) {
            e.stopPropagation();
            if (e.cancelable) e.preventDefault();
            this.workspace.connectionEngine.handleTerminalClick(nearTerm.compId, nearTerm.termId, nearTerm.el, e);
            return;
          }
        }
        return;
      }

      // NEVER intercept during multi-touch PINCH_ZOOM
      if (this.workspace?.gestureState === "PINCH_ZOOM" || (this.workspace?.activePointers?.size || 0) >= 2) {
        return;
      }

      // NEVER intercept if clicking a terminal node, smart numbering badge, hanging wire node, probe assembly, or interactive meter buttons & dial
      if (
        e.target.closest(".terminal-node") ||
        e.target.closest(".smart-number-badge") ||
        e.target.closest(".hanging-wire-node") ||
        e.target.closest(".probe-assembly") ||
        e.target.closest(".meter-function-row") ||
        e.target.closest(".meter-power-btn") ||
        e.target.closest(".meter-chip-btn") ||
        e.target.closest(".meter-rotary-knob") ||
        e.target.closest(".meter-dial-label")
      ) {
        return;
      }

      e.stopPropagation();
      if (e.cancelable) e.preventDefault();

      pointerId = e.pointerId ?? null;
      if (pointerId !== null && el.setPointerCapture) {
        try { el.setPointerCapture(pointerId); } catch (err) {}
      }

      stateManager.setSelection("component", comp.id);

      const clientX = e.clientX ?? (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
      const clientY = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : 0);

      startX = clientX;
      startY = clientY;
      compStartX = comp.x;
      compStartY = comp.y;
      hasMoved = false;
      isDragging = true;

      const onPointerMove = (moveEvent) => {
        if (!isDragging) return;
        if (moveEvent.cancelable) moveEvent.preventDefault();

        const curX = moveEvent.clientX ?? (moveEvent.touches && moveEvent.touches[0] ? moveEvent.touches[0].clientX : (moveEvent.changedTouches && moveEvent.changedTouches[0] ? moveEvent.changedTouches[0].clientX : startX));
        const curY = moveEvent.clientY ?? (moveEvent.touches && moveEvent.touches[0] ? moveEvent.touches[0].clientY : (moveEvent.changedTouches && moveEvent.changedTouches[0] ? moveEvent.changedTouches[0].clientY : startY));

        const deltaX = (curX - startX) / this.workspace.zoom;
        const deltaY = (curY - startY) / this.workspace.zoom;

        if (Math.hypot(deltaX, deltaY) > 2) {
          hasMoved = true;
        }

        if (hasMoved) {
          const gridSize = this.workspace.gridSize || 20;
          const rawX = compStartX + deltaX;
          const rawY = compStartY + deltaY;
          const snapped = snapComponentToGrid(rawX, rawY, comp, gridSize);

          el.style.left = `${snapped.x}px`;
          el.style.top = `${snapped.y}px`;
          comp.x = snapped.x;
          comp.y = snapped.y;

          this.updateAllMultimeterProbes();

          stateManager.notify("components_moving");
        }
      };

      const onPointerUp = (upEvent) => {
        if (!isDragging) return;
        isDragging = false;

        if (pointerId !== null && el.releasePointerCapture) {
          try { el.releasePointerCapture(pointerId); } catch (err) {}
        }
        pointerId = null;

        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", onPointerUp);
        window.removeEventListener("pointercancel", onPointerUp);
        window.removeEventListener("touchmove", onPointerMove);
        window.removeEventListener("touchend", onPointerUp);
        window.removeEventListener("touchcancel", onPointerUp);

        if (hasMoved) {
          const gridSize = this.workspace.gridSize || 20;
          const snapped = snapComponentToGrid(comp.x, comp.y, comp, gridSize);
          comp.x = snapped.x;
          comp.y = snapped.y;
          el.style.left = `${snapped.x}px`;
          el.style.top = `${snapped.y}px`;
          stateManager.updateComponentPosition(comp.id, snapped.x, snapped.y);
          this.updateAllMultimeterProbes();
        } else {
          if (comp.type === "switch_spst") {
            const nextState = !comp.properties.isClosed;
            comp.properties.isClosed = nextState;
            this.updateComponentVisualProperties(el, comp);
            stateManager.updateComponentProperty(comp.id, "isClosed", nextState);
          } else if (comp.type === "multimeter") {
            const modes = ["V_DC", "OHM", "A_DC"];
            const curr = comp.properties.mode || "V_DC";
            const nextMode = modes[(modes.indexOf(curr) + 1) % modes.length];
            comp.properties.mode = nextMode;
            this.updateComponentVisualProperties(el, comp);
            stateManager.updateComponentProperty(comp.id, "mode", nextMode);
            stateManager.notify("simulation");
            this.updateAllMultimeterProbes();
          }
        }
      };

      window.addEventListener("pointermove", onPointerMove, { passive: false });
      window.addEventListener("pointerup", onPointerUp);
      window.addEventListener("pointercancel", onPointerUp);
      window.addEventListener("touchmove", onPointerMove, { passive: false });
      window.addEventListener("touchend", onPointerUp);
      window.addEventListener("touchcancel", onPointerUp);
    };

    el.addEventListener("pointerdown", onPointerDown, { passive: false });
  }

  updateAllMultimeterProbes() {
    const state = stateManager.getState();
    const multimeters = state.components.filter(c => c.type === "multimeter");
    multimeters.forEach(m => {
      this.updateMultimeterProbeVisuals(m);
    });
  }

  openMultimeterModal(comp) {
    const backdrop = document.createElement("div");
    backdrop.className = "quick-edit-modal-backdrop";

    const currentMode = comp.properties.mode || "V_DC";

    backdrop.innerHTML = `
      <div class="quick-edit-modal">
        <div class="modal-header">
          <div class="modal-title"><span>📟</span> Pengaturan Multimeter Digital</div>
          <button class="btn-icon" id="modal-close-btn">✕</button>
        </div>
        <div class="modal-body">
          <label class="input-label">Pilih Mode Pengukuran:</label>
          <div class="quick-presets" style="gap: 8px;">
            <button class="preset-chip chip-mode ${currentMode === 'V_DC' ? 'active' : ''}" data-mode="V_DC" style="flex: 1; padding: 10px; font-weight: 700;">V DC (Tegangan)</button>
            <button class="preset-chip chip-mode ${currentMode === 'OHM' ? 'active' : ''}" data-mode="OHM" style="flex: 1; padding: 10px; font-weight: 700;">Ω (Hambatan)</button>
            <button class="preset-chip chip-mode ${currentMode === 'A_DC' ? 'active' : ''}" data-mode="A_DC" style="flex: 1; padding: 10px; font-weight: 700;">A DC (Arus)</button>
          </div>
          <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 8px;">
            Sambungkan terminal <strong>COM</strong> ke ground/negatif, dan terminal <strong>VΩ</strong> ke titik pengukuran.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" id="modal-cancel-btn">Tutup</button>
        </div>
      </div>
    `;

    document.body.appendChild(backdrop);

    const close = () => backdrop.remove();

    backdrop.querySelector("#modal-close-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-cancel-btn").addEventListener("click", close);

    backdrop.querySelectorAll(".chip-mode").forEach(btn => {
      btn.addEventListener("click", () => {
        const mode = btn.getAttribute("data-mode");
        stateManager.updateComponentProperty(comp.id, "mode", mode);
        close();
      });
    });
  }

  openResistorModal(comp) {
    const backdrop = document.createElement("div");
    backdrop.className = "quick-edit-modal-backdrop";

    const currentR = comp.properties.resistance || 220;
    const initBands = calculateResistorBands(currentR);

    backdrop.innerHTML = `
      <div class="quick-edit-modal">
        <div class="modal-header">
          <div class="modal-title"><span>〰️</span> Atur Nilai Resistor</div>
          <button class="btn-icon" id="modal-close-btn">✕</button>
        </div>
        <div class="modal-body">
          <label class="input-label">Masukkan Nilai Hambatan (Resistansi):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-r-input" value="${currentR}" min="1" max="1000000" autofocus>
            <span class="input-unit">Ω</span>
          </div>

          <label class="input-label">Live Preview Gelang Warna Kode EIA:</label>
          <div class="band-preview-container">
            <div class="band-preview-resistor">
              <div class="band-bar" id="modal-b1" style="background-color: ${initBands.b1Hex};"></div>
              <div class="band-bar" id="modal-b2" style="background-color: ${initBands.b2Hex};"></div>
              <div class="band-bar" id="modal-b3" style="background-color: ${initBands.b3Hex};"></div>
              <div class="band-bar" id="modal-b4" style="background-color: ${initBands.b4Hex};"></div>
            </div>
          </div>

          <label class="input-label">Preset Cepat Standar:</label>
          <div class="quick-presets">
            <span class="preset-chip chip-r" data-val="100">100 Ω</span>
            <span class="preset-chip chip-r" data-val="220">220 Ω</span>
            <span class="preset-chip chip-r" data-val="330">330 Ω</span>
            <span class="preset-chip chip-r" data-val="470">470 Ω</span>
            <span class="preset-chip chip-r" data-val="1000">1 kΩ</span>
            <span class="preset-chip chip-r" data-val="4700">4.7 kΩ</span>
            <span class="preset-chip chip-r" data-val="10000">10 kΩ</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" id="modal-cancel-btn">Batal</button>
          <button class="btn-primary" id="modal-save-btn">Terapkan</button>
        </div>
      </div>
    `;

    document.body.appendChild(backdrop);

    const input = backdrop.querySelector("#modal-r-input");
    const b1 = backdrop.querySelector("#modal-b1");
    const b2 = backdrop.querySelector("#modal-b2");
    const b3 = backdrop.querySelector("#modal-b3");

    const updateLiveBands = (val) => {
      const b = calculateResistorBands(val);
      b1.style.backgroundColor = b.b1Hex;
      b2.style.backgroundColor = b.b2Hex;
      b3.style.backgroundColor = b.b3Hex;
    };

    input.addEventListener("input", (e) => {
      updateLiveBands(Number(e.target.value) || 220);
    });

    backdrop.querySelectorAll(".chip-r").forEach(chip => {
      chip.addEventListener("click", () => {
        const val = Number(chip.getAttribute("data-val"));
        input.value = val;
        updateLiveBands(val);
      });
    });

    const close = () => backdrop.remove();

    backdrop.querySelector("#modal-close-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-cancel-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-save-btn").addEventListener("click", () => {
      const val = Number(input.value) || 220;
      stateManager.updateComponentProperty(comp.id, "resistance", val);
      close();
    });

    input.focus();
    input.select();
  }

  openBatteryModal(comp) {
    const backdrop = document.createElement("div");
    backdrop.className = "quick-edit-modal-backdrop";

    const currentV = comp.properties.voltage || 12;

    backdrop.innerHTML = `
      <div class="quick-edit-modal">
        <div class="modal-header">
          <div class="modal-title"><span>🔋</span> Atur Tegangan Baterai</div>
          <button class="btn-icon" id="modal-close-btn">✕</button>
        </div>
        <div class="modal-body">
          <label class="input-label">Masukkan Tegangan Sumber Daya (DC):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-v-input" value="${currentV}" min="0.5" max="220" step="0.5" autofocus>
            <span class="input-unit">V</span>
          </div>

          <label class="input-label">Preset Standar Laboratorium:</label>
          <div class="quick-presets">
            <span class="preset-chip chip-v" data-val="1.5">1.5 V</span>
            <span class="preset-chip chip-v" data-val="3">3.0 V</span>
            <span class="preset-chip chip-v" data-val="5">5.0 V</span>
            <span class="preset-chip chip-v" data-val="9">9.0 V</span>
            <span class="preset-chip chip-v" data-val="12">12.0 V</span>
            <span class="preset-chip chip-v" data-val="24">24.0 V</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" id="modal-cancel-btn">Batal</button>
          <button class="btn-primary" id="modal-save-btn">Terapkan</button>
        </div>
      </div>
    `;

    document.body.appendChild(backdrop);

    const input = backdrop.querySelector("#modal-v-input");
    backdrop.querySelectorAll(".chip-v").forEach(chip => {
      chip.addEventListener("click", () => {
        input.value = chip.getAttribute("data-val");
      });
    });

    const close = () => backdrop.remove();

    backdrop.querySelector("#modal-close-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-cancel-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-save-btn").addEventListener("click", () => {
      const val = Number(input.value) || 12;
      stateManager.updateComponentProperty(comp.id, "voltage", val);
      close();
    });

    input.focus();
    input.select();
  }

  openLampModal(comp) {
    const backdrop = document.createElement("div");
    backdrop.className = "quick-edit-modal-backdrop";

    const currentW = comp.properties.powerRating || 20;
    const currentV = comp.properties.nominalVoltage || 12;

    backdrop.innerHTML = `
      <div class="quick-edit-modal">
        <div class="modal-header">
          <div class="modal-title"><span>💡</span> Parameter Lampu Pijar</div>
          <button class="btn-icon" id="modal-close-btn">✕</button>
        </div>
        <div class="modal-body">
          <label class="input-label">Daya Nominal (Watt):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-w-input" value="${currentW}" min="1" max="100">
            <span class="input-unit">W</span>
          </div>

          <label class="input-label">Tegangan Kerja (Volt):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-lamp-v-input" value="${currentV}" min="1" max="220">
            <span class="input-unit">V</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" id="modal-cancel-btn">Batal</button>
          <button class="btn-primary" id="modal-save-btn">Terapkan</button>
        </div>
      </div>
    `;

    document.body.appendChild(backdrop);

    const close = () => backdrop.remove();

    backdrop.querySelector("#modal-close-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-cancel-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-save-btn").addEventListener("click", () => {
      const w = Number(backdrop.querySelector("#modal-w-input").value) || 20;
      const v = Number(backdrop.querySelector("#modal-lamp-v-input").value) || 12;
      const r = (v * v) / w;
      stateManager.updateComponentProperty(comp.id, "powerRating", w);
      stateManager.updateComponentProperty(comp.id, "nominalVoltage", v);
      stateManager.updateComponentProperty(comp.id, "resistance", Number(r.toFixed(2)));
      close();
    });
  }

  openMotorModal(comp) {
    const backdrop = document.createElement("div");
    backdrop.className = "quick-edit-modal-backdrop";

    const currentV = comp.properties.nominalVoltage || 12;
    const currentRpm = comp.properties.maxRpm || comp.properties.noLoadRpm || 3000;
    const currentI0 = comp.properties.noLoadCurrent || 0.30;
    const currentRa = comp.properties.armatureResistance || comp.properties.resistance || 1.0;
    const currentLoad = comp.properties.loadPercent || 0;

    backdrop.innerHTML = `
      <div class="quick-edit-modal">
        <div class="modal-header">
          <div class="modal-title"><span>⚙️</span> Parameter Motor Listrik (DC)</div>
          <button class="btn-icon" id="modal-close-btn">✕</button>
        </div>
        <div class="modal-body">
          <label class="input-label">Tegangan Kerja Nominal (Volt):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-motor-v" value="${currentV}" min="1" max="220">
            <span class="input-unit">V</span>
          </div>

          <label class="input-label">Kecepatan Tanpa Beban (RPM):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-motor-rpm" value="${currentRpm}" min="100" max="20000" step="100">
            <span class="input-unit">RPM</span>
          </div>

          <label class="input-label">Resistansi Jangkar / Armature (Ra):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-motor-ra" value="${currentRa}" min="0.05" max="100" step="0.1">
            <span class="input-unit">Ω</span>
          </div>

          <label class="input-label" title="0% = tanpa beban, 100% = torsi yang menyebabkan motor stall pada tegangan nominal.">Beban Mekanik (% Torsi Stall):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-motor-load" value="${currentLoad}" min="0" max="100" step="5" title="0% = tanpa beban, 100% = torsi yang menyebabkan motor stall pada tegangan nominal.">
            <span class="input-unit">% T_stall</span>
          </div>

          <label class="input-label">Preset Kecepatan Standar:</label>
          <div class="quick-presets">
            <span class="preset-chip chip-rpm" data-val="1000">1000 RPM</span>
            <span class="preset-chip chip-rpm" data-val="3000">3000 RPM</span>
            <span class="preset-chip chip-rpm" data-val="6000">6000 RPM</span>
            <span class="preset-chip chip-rpm" data-val="10000">10000 RPM</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" id="modal-cancel-btn">Batal</button>
          <button class="btn-primary" id="modal-save-btn">Terapkan</button>
        </div>
      </div>
    `;

    document.body.appendChild(backdrop);

    const rpmInput = backdrop.querySelector("#modal-motor-rpm");
    backdrop.querySelectorAll(".chip-rpm").forEach(chip => {
      chip.addEventListener("click", () => {
        rpmInput.value = chip.getAttribute("data-val");
      });
    });

    const close = () => backdrop.remove();

    backdrop.querySelector("#modal-close-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-cancel-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-save-btn").addEventListener("click", () => {
      const v = Number(backdrop.querySelector("#modal-motor-v").value) || 12;
      const rpm = Number(rpmInput.value) || 3000;
      const ra = Number(backdrop.querySelector("#modal-motor-ra").value) || 1.0;
      const load = Number(backdrop.querySelector("#modal-motor-load").value) || 0;

      stateManager.updateComponentProperty(comp.id, "nominalVoltage", v);
      stateManager.updateComponentProperty(comp.id, "maxRpm", rpm);
      stateManager.updateComponentProperty(comp.id, "noLoadRpm", rpm);
      stateManager.updateComponentProperty(comp.id, "armatureResistance", ra);
      stateManager.updateComponentProperty(comp.id, "resistance", ra);
      stateManager.updateComponentProperty(comp.id, "loadPercent", load);
      close();
    });
  }

  openDiodeModal(comp) {
    const backdrop = document.createElement("div");
    backdrop.className = "quick-edit-modal-backdrop";

    const currentModel = comp.properties.model || "1N4007";
    const currentVf = comp.properties.forwardVoltage || 0.7;

    backdrop.innerHTML = `
      <div class="quick-edit-modal">
        <div class="modal-header">
          <div class="modal-title"><span>🔺</span> Parameter Dioda Semikonduktor</div>
          <button class="btn-icon" id="modal-close-btn">✕</button>
        </div>
        <div class="modal-body">
          <label class="input-label">Model / Tipe Dioda:</label>
          <div class="modal-input-group">
            <input type="text" class="modal-input" id="modal-diode-model" value="${currentModel}">
          </div>

          <label class="input-label">Tegangan Maju / Forward Drop (Vf):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-diode-vf" value="${currentVf}" min="0.1" max="5.0" step="0.05">
            <span class="input-unit">V</span>
          </div>

          <label class="input-label">Preset Dioda Populer:</label>
          <div class="quick-presets">
            <span class="preset-chip chip-diode" data-model="1N4007" data-vf="0.7">1N4007 (Silikon 0.7V)</span>
            <span class="preset-chip chip-diode" data-model="1N4148" data-vf="0.7">1N4148 (Fast 0.7V)</span>
            <span class="preset-chip chip-diode" data-model="1N5819" data-vf="0.25">1N5819 (Schottky 0.25V)</span>
            <span class="preset-chip chip-diode" data-model="1N34A" data-vf="0.3">1N34A (Germanium 0.3V)</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" id="modal-cancel-btn">Batal</button>
          <button class="btn-primary" id="modal-save-btn">Terapkan</button>
        </div>
      </div>
    `;

    document.body.appendChild(backdrop);

    const modelInput = backdrop.querySelector("#modal-diode-model");
    const vfInput = backdrop.querySelector("#modal-diode-vf");

    backdrop.querySelectorAll(".chip-diode").forEach(chip => {
      chip.addEventListener("click", () => {
        modelInput.value = chip.getAttribute("data-model");
        vfInput.value = chip.getAttribute("data-vf");
      });
    });

    const close = () => backdrop.remove();

    backdrop.querySelector("#modal-close-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-cancel-btn").addEventListener("click", close);
    backdrop.querySelector("#modal-save-btn").addEventListener("click", () => {
      const model = modelInput.value.trim() || "1N4007";
      const vf = Number(vfInput.value) || 0.7;
      stateManager.updateComponentProperty(comp.id, "model", model);
      stateManager.updateComponentProperty(comp.id, "forwardVoltage", vf);
      close();
    });
  }

  calculateProbeWirePath(startX, startY, endX, endY, probeAngle = 0) {
    const dx = endX - startX;
    const dy = endY - startY;
    const dist = Math.hypot(dx, dy);

    // Natural cable sag factor based on distance
    const sag = Math.min(80, Math.max(20, dist * 0.22));

    // Tangent lead length leaving rear connector along probe longitudinal axis (16-32px)
    const tangentLen = Math.min(32, Math.max(16, dist * 0.15));

    const rad = ((probeAngle || 0) * Math.PI) / 180;
    const rearDirX = Math.sin(rad);
    const rearDirY = -Math.cos(rad);

    // Control point 1: leaves the multimeter socket dropping smoothly
    const cp1x = Math.round(startX + dx * 0.15);
    const cp1y = Math.round(startY + sag + (dy > 0 ? dy * 0.15 : 0));

    // Control point 2: approaches rear connector strictly along probe longitudinal axis
    const cp2x = Math.round(endX + rearDirX * tangentLen);
    const cp2y = Math.round(endY + rearDirY * tangentLen);

    return `M ${startX} ${startY} C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${endX} ${endY}`;
  }

  /**
   * Calculates the probe orientation and top cable entry coordinates
   * Baseline layout: probes stand upright above terminal (angle = 0 deg) at component rotation = 0 deg
   * Attached probe inherits the attached component's rotation as a group: finalAngle = 0 + attachedComp.rotation
   * Idle / docked: rotates with multimeter casing
   */
  calculateProbeOrientation(probePos, jackPos, isConnected, compRotation = 0, attachedTarget = null) {
    const targetX = probePos.x;
    const targetY = probePos.y;

    let angleDeg = 0;
    if (isConnected && attachedTarget?.compId) {
      const state = stateManager.getState();
      const targetComp = state.components?.find(c => c.id === attachedTarget.compId);
      angleDeg = (targetComp?.rotation || 0) % 360;
    } else if (!isConnected) {
      // Idle / docked state: rotates with multimeter casing
      angleDeg = (compRotation || 0) % 360;
    }

    if (angleDeg < 0) angleDeg += 360;

    const rad = (angleDeg * Math.PI) / 180;

    // Anchor is metallic probe tip apex at local (12, 52)
    // Placing probeEl at left: (targetX - 12), top: (targetY - 52)
    // with transformOrigin: "12px 52px" keeps the tip apex EXACTLY on (targetX, targetY) across all rotations!
    const topY = targetY - 52;

    // Cable entry is at local (12, 0) - exactly 52px along the probe body from tip
    // In world coordinates after rotation around tip:
    const cableX = Math.round(targetX + 52 * Math.sin(rad));
    const cableY = Math.round(targetY - 52 * Math.cos(rad));

    return { angleDeg, topY, cableX, cableY, targetX, targetY };
  }

  getMultimeterJackPosition(comp, probeKey) {
    return getMultimeterJackPosition(comp, probeKey);
  }

  getMultimeterHandoffPosition(comp, probeKey) {
    return getMultimeterHandoffPosition(comp, probeKey);
  }

  getProbeTipPosition(comp, probeKey) {
    return getProbeTipPosition(comp, probeKey, this.workspace);
  }

  updateProbeCable(comp, probeKey) {
    const isCom = probeKey === "com";
    const jackPos = isCom 
      ? this.getMultimeterJackPosition(comp, "com")
      : (() => {
          const anim = this.multimeterPlugAnimations?.get(comp.id);
          if (anim?.isAnimating) {
            return getRotatedPosition(comp.x, comp.y, comp.width, comp.height, anim.currentRelX, anim.currentRelY, comp.rotation || 0);
          }
          return this.getMultimeterJackPosition(comp, "vwma");
        })();

    const handoffPos = isCom
      ? this.getMultimeterHandoffPosition(comp, "com")
      : (() => {
          const anim = this.multimeterPlugAnimations?.get(comp.id);
          if (anim?.isAnimating) {
            return getRotatedPosition(comp.x, comp.y, comp.width, comp.height, anim.currentRelX, 234, comp.rotation || 0);
          }
          return this.getMultimeterHandoffPosition(comp, "vwma");
        })();

    const tipInfo = this.getProbeTipPosition(comp, probeKey);
    const probeState = comp.properties?.probes?.[probeKey];
    const attachedTarget = tipInfo.attachedTo || probeState?.snapCandidate || null;
    const isConnected = tipInfo.isConnected || !!probeState?.snapCandidate;

    const orient = this.calculateProbeOrientation(tipInfo.pos, handoffPos, isConnected, comp.rotation || 0, attachedTarget);

    const probeEl = document.getElementById(`probe-${probeKey}-${comp.id}`);
    if (probeEl) {
      probeEl.style.left = `${orient.targetX - 12}px`;
      probeEl.style.top = `${orient.topY}px`;
      probeEl.style.transformOrigin = "12px 52px";
      probeEl.style.transform = orient.angleDeg ? `rotate(${orient.angleDeg}deg)` : "none";
    }

    // 1. Main Cable (in background SVG layer, starts from handoffPos at casing edge)
    const wireEl = document.getElementById(`meter-wire-${probeKey}-${comp.id}`);
    if (wireEl) {
      wireEl.setAttribute("d", this.calculateProbeWirePath(handoffPos.x, handoffPos.y, orient.cableX, orient.cableY, orient.angleDeg));
    }

    // 2. Front Banana Plug (in front SVG layer, positioned directly at jackPos)
    const plugEl = document.getElementById(`meter-plug-${probeKey}-${comp.id}`);
    if (plugEl) {
      plugEl.setAttribute("transform", `translate(${jackPos.x}, ${jackPos.y}) rotate(${comp.rotation || 0})`);
    }

    // 3. Short Front Lead (in front SVG layer, connects jackPos to handoffPos in front of casing)
    const frontLeadEl = document.getElementById(`meter-front-lead-${probeKey}-${comp.id}`);
    if (frontLeadEl) {
      frontLeadEl.setAttribute("d", `M ${jackPos.x} ${jackPos.y} L ${handoffPos.x} ${handoffPos.y}`);
    }
  }

  createBananaPlugElement(probeKey, compId) {
    const isCom = probeKey === "com";
    const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
    g.setAttribute("id", `meter-plug-${probeKey}-${compId}`);
    g.setAttribute("class", `meter-banana-plug meter-plug-${probeKey}`);
    g.setAttribute("data-comp-id", compId);
    g.setAttribute("style", "pointer-events: none;");

    const gradId = isCom ? `plug-grad-black-${compId}` : `plug-grad-red-${compId}`;
    const strokeColor = isCom ? "#020617" : "#7f1d1d";

    g.innerHTML = `
      <defs>
        <linearGradient id="${gradId}" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="${isCom ? '#475569' : '#f87171'}"/>
          <stop offset="35%" stop-color="${isCom ? '#1e293b' : '#dc2626'}"/>
          <stop offset="100%" stop-color="${isCom ? '#020617' : '#991b1b'}"/>
        </linearGradient>
      </defs>
      <!-- Plug Shadow onto Casing Face -->
      <ellipse cx="0" cy="9" rx="6.5" ry="11" fill="rgba(0,0,0,0.5)" filter="blur(1.5px)"/>
      <!-- Metallic Socket Pin Rim & Hole Insert (Covers socket from front) -->
      <circle cx="0" cy="0" r="5" fill="#475569" stroke="#1e293b" stroke-width="1"/>
      <circle cx="0" cy="0" r="3" fill="#020617"/>
      <!-- Molded Banana Barrel Body (Extends downward y: 0 to 16, width 11) -->
      <path d="M -5.5 0 L 5.5 0 L 4.5 16 L -4.5 16 Z" fill="url(#${gradId})" stroke="${strokeColor}" stroke-width="0.9"/>
      <!-- Tactile Grips -->
      <line x1="-5" y1="4.5" x2="5" y2="4.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
      <line x1="-4.6" y1="8.5" x2="4.6" y2="8.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
      <line x1="-4.2" y1="12.5" x2="4.2" y2="12.5" stroke="#000000" stroke-width="0.9" opacity="0.45"/>
      <!-- Highlight Sheen -->
      <line x1="-2.2" y1="2" x2="-2.2" y2="14.5" stroke="#ffffff" stroke-width="0.8" opacity="0.5" stroke-linecap="round"/>
      <!-- Strain Relief Collar -->
      <rect x="-3.5" y="15.5" width="7" height="3.5" rx="1" fill="#0f172a" stroke="#334155" stroke-width="0.7"/>
    `;
    return g;
  }

  checkAndTriggerPlugAnimation(comp) {
    if (!this.multimeterPlugAnimations) {
      this.multimeterPlugAnimations = new Map();
    }

    const currentMode = comp.properties?.mode || "V_DC";
    const targetJackKey = getMultimeterJackKey(currentMode, "vwma");
    const targetJack = MULTIMETER_JACK_POSITIONS[targetJackKey] || MULTIMETER_JACK_POSITIONS["V_OHM"];

    let anim = this.multimeterPlugAnimations.get(comp.id);
    if (!anim) {
      anim = {
        currentRelX: targetJack.relX,
        currentRelY: targetJack.relY,
        sourceRelX: targetJack.relX,
        sourceRelY: targetJack.relY,
        targetRelX: targetJack.relX,
        targetRelY: targetJack.relY,
        isAnimating: false,
        lastMode: currentMode
      };
      this.multimeterPlugAnimations.set(comp.id, anim);
      return;
    }

    if (anim.lastMode !== currentMode) {
      anim.lastMode = currentMode;
      if (Math.abs(anim.currentRelX - targetJack.relX) > 0.5 || Math.abs(anim.currentRelY - targetJack.relY) > 0.5) {
        anim.sourceRelX = anim.currentRelX;
        anim.sourceRelY = anim.currentRelY;
        anim.targetRelX = targetJack.relX;
        anim.targetRelY = targetJack.relY;
        anim.startTime = performance.now();
        anim.duration = 220; // 220 ms
        anim.isAnimating = true;
        this.stepPlugAnimation(comp);
      }
    }
  }

  stepPlugAnimation(comp) {
    const anim = this.multimeterPlugAnimations?.get(comp.id);
    if (!anim || !anim.isAnimating) return;

    const now = performance.now();
    const elapsed = now - anim.startTime;
    const progress = Math.min(1, elapsed / anim.duration);

    // Easing: Smooth cubic in-out
    const u = progress < 0.5
      ? 4 * progress * progress * progress
      : 1 - Math.pow(-2 * progress + 2, 3) / 2;

    // 3-Phase motion trajectory:
    // Phase 1 (Unplug): arcs forward/downward
    // Phase 2 (Travel): moves between jacks in front of casing
    // Phase 3 (Insert): seats into target socket
    const arcY = 8.5 * Math.sin(Math.PI * u);

    anim.currentRelX = anim.sourceRelX + (anim.targetRelX - anim.sourceRelX) * u;
    anim.currentRelY = anim.sourceRelY + (anim.targetRelY - anim.sourceRelY) * u + arcY;

    // Transform relative casing position to current world position with rotation
    const animJackWorld = getRotatedPosition(
      comp.x,
      comp.y,
      comp.width,
      comp.height,
      anim.currentRelX,
      anim.currentRelY,
      comp.rotation || 0
    );

    const animHandoffWorld = getRotatedPosition(
      comp.x,
      comp.y,
      comp.width,
      comp.height,
      anim.currentRelX,
      234 + arcY * 0.5,
      comp.rotation || 0
    );

    // 1. Update Red Banana Plug visual element (in front SVG layer)
    const plugEl = document.getElementById(`meter-plug-vwma-${comp.id}`);
    if (plugEl) {
      plugEl.setAttribute("transform", `translate(${animJackWorld.x}, ${animJackWorld.y}) rotate(${comp.rotation || 0})`);
    }

    // 2. Update Short Front Lead (in front SVG layer)
    const frontLeadEl = document.getElementById(`meter-front-lead-vwma-${comp.id}`);
    if (frontLeadEl) {
      frontLeadEl.setAttribute("d", `M ${animJackWorld.x} ${animJackWorld.y} L ${animHandoffWorld.x} ${animHandoffWorld.y}`);
    }

    // 3. Update Red Main Cable (in background SVG layer) from animHandoffWorld to circuit-side probe tip
    const tipInfo = this.getProbeTipPosition(comp, "vwma");
    const probeState = comp.properties?.probes?.vwma;
    const attachedTarget = tipInfo.attachedTo || probeState?.snapCandidate || null;
    const orient = this.calculateProbeOrientation(tipInfo.pos, animHandoffWorld, tipInfo.isConnected, comp.rotation || 0, attachedTarget);

    const wireEl = document.getElementById(`meter-wire-vwma-${comp.id}`);
    if (wireEl) {
      wireEl.setAttribute("d", this.calculateProbeWirePath(animHandoffWorld.x, animHandoffWorld.y, orient.cableX, orient.cableY, orient.angleDeg));
    }

    if (progress < 1) {
      requestAnimationFrame(() => this.stepPlugAnimation(comp));
    } else {
      anim.isAnimating = false;
      anim.currentRelX = anim.targetRelX;
      anim.currentRelY = anim.targetRelY;
      this.updateProbeCable(comp, "vwma");
    }
  }

  createProbeElement(comp, probeKey) {
    const isCom = probeKey === "com";
    const label = isCom ? "COM" : "VΩ";
    const title = isCom ? "Tarik Probe COM (Hitam) ke titik pengukuran" : "Tarik Probe VΩmA (Merah) ke titik pengukuran";
    const gradLight = isCom ? "#1e293b" : "#ef4444";
    const gradDark = isCom ? "#0f172a" : "#b91c1c";

    const el = document.createElement("div");
    el.className = `probe-assembly probe-${probeKey}-assembly`;
    el.id = `probe-${probeKey}-${comp.id}`;
    el.setAttribute("data-probe", probeKey);
    el.setAttribute("data-comp-id", comp.id);
    el.title = title;

    el.innerHTML = `
      <div class="probe-touch-hitbox"></div>
      <svg width="24" height="52" viewBox="0 0 24 52" fill="none" xmlns="http://www.w3.org/2000/svg" class="probe-svg-visual">
        <defs>
          <linearGradient id="needle-metal-${probeKey}-${comp.id}" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ffffff"/>
            <stop offset="45%" stop-color="#e2e8f0"/>
            <stop offset="70%" stop-color="#94a3b8"/>
            <stop offset="100%" stop-color="#475569"/>
          </linearGradient>
          <linearGradient id="collar-grad-${probeKey}-${comp.id}" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" stop-color="${gradLight}"/>
            <stop offset="100%" stop-color="${gradDark}"/>
          </linearGradient>
        </defs>
        
        <!-- Top Cable Strain Relief & Flexible Collar (y: 0-4.5, center x=12) -->
        <rect x="9.25" y="0" width="5.5" height="2" rx="1" fill="#0f172a" stroke="#334155" stroke-width="0.6"/>
        <rect x="8.5" y="1.8" width="7" height="2.7" rx="1.2" fill="#1e293b" stroke="#0f172a" stroke-width="0.7"/>
        
        <!-- Molded Finger Guard (y: 4-8.5, width: 20) -->
        <rect x="2" y="4" width="20" height="4.5" rx="2" fill="${gradDark}" stroke="#0f172a" stroke-width="1"/>
        
        <!-- Probe Handle Body (y: 8.5-26, width: 14) -->
        <rect x="5" y="8.5" width="14" height="17.5" rx="2.5" fill="url(#collar-grad-${probeKey}-${comp.id})" stroke="#0f172a" stroke-width="1"/>
        <!-- Ribbed Grips -->
        <rect x="6.5" y="11" width="11" height="1.5" rx="0.5" fill="#0f172a" opacity="0.45"/>
        <rect x="6.5" y="14.5" width="11" height="1.5" rx="0.5" fill="#0f172a" opacity="0.45"/>
        <rect x="6.5" y="18" width="11" height="1.5" rx="0.5" fill="#0f172a" opacity="0.45"/>
        
        <!-- Lead Label (COM / VΩ) -->
        <text x="12" y="23" font-size="5" font-family="monospace" font-weight="900" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">${label}</text>
        
        <!-- Tapered Collar Nose (y: 26-36) -->
        <path d="M6 26 L18 26 L14 36 L10 36 Z" fill="url(#collar-grad-${probeKey}-${comp.id})" stroke="#0f172a" stroke-width="1"/>
        
        <!-- Metallic Pin Needle (y: 36-52, apex touches EXACTLY x=12, y=52!) -->
        <path d="M10.5 36 L13.5 36 L12.5 50 L12 52 L11.5 50 Z" fill="url(#needle-metal-${probeKey}-${comp.id})" stroke="#334155" stroke-width="0.6"/>
      </svg>
    `;

    this.bindProbeDragEvents(el, comp, probeKey);
    return el;
  }

  bindProbeDragEvents(probeEl, comp, probeKey) {
    const bindingId = ++globalProbeBindingCounter;
    console.log("PROBE_BIND", {
      bindingId,
      probeId: probeEl.id,
      probeKey,
      compId: comp.id,
    });

    let isDragging = false;
    let hasMoved = false;
    let activeSnap = null;
    let startScreenX = 0;
    let startScreenY = 0;
    let pointerId = null;
    let dragOrigin = null;
    let dragSessionId = null;

    const probeDebugSnapshot = (probeState) => ({
      attachedTo: probeState?.attachedTo ? { ...probeState.attachedTo } : null,
      isPlaced: probeState?.isPlaced,
      worldX: probeState?.worldX,
      worldY: probeState?.worldY,
      dragWorldX: probeState?.dragWorldX,
      dragWorldY: probeState?.dragWorldY,
    });

    const logProbeAfterNotify = (phase, sessionId) => {
      const logCurrentProbe = () => {
        const currentProbe = comp.properties.probes[probeKey];
        const tip = this.getProbeTipPosition(comp, probeKey).pos;
        return {
          sessionId,
          ...probeDebugSnapshot(currentProbe),
          tip,
        };
      };

      const label = (timing) => phase === "POST_COMMIT"
        ? `POST_COMMIT_${timing}`
        : `PROBE_AFTER_NOTIFY_${timing}:${phase}`;

      console.log(label("IMMEDIATE"), logCurrentProbe());
      queueMicrotask(() => {
        console.log(label("MICROTASK"), logCurrentProbe());
      });
      setTimeout(() => {
        console.log(label("TIMEOUT"), logCurrentProbe());
      }, 0);
    };

    const getEvtCoords = (evt) => {
      if (evt.touches && evt.touches.length > 0) {
        return { x: evt.touches[0].clientX, y: evt.touches[0].clientY };
      }
      return { x: evt.clientX, y: evt.clientY };
    };

    const onPointerDown = (e) => {
      e.stopPropagation();
      if (e.cancelable) e.preventDefault();

      pointerId = e.pointerId ?? null;
      if (pointerId !== null && probeEl.setPointerCapture) {
        try { probeEl.setPointerCapture(pointerId); } catch (err) {}
      }

      isDragging = true;
      hasMoved = false;
      activeSnap = null;
      dragSessionId = ++probeDragDebugId;

      const coords = getEvtCoords(e);
      startScreenX = coords.x ?? 0;
      startScreenY = coords.y ?? 0;

      probeEl.classList.add("is-dragging");

      const currentProbe = comp.properties.probes[probeKey];
      console.log("PROBE_DOWN_BINDING", {
        bindingId,
        probeId: probeEl.id,
        attachedBefore: currentProbe.attachedTo ? { ...currentProbe.attachedTo } : null,
        isPlacedBefore: currentProbe.isPlaced,
      });
      console.log("NEXT_DRAG_DOWN_RAW", {
        sessionId: dragSessionId,
        ...probeDebugSnapshot(currentProbe),
      });
      const currentTip = this.getProbeTipPosition(comp, probeKey);

      // RUNTIME-ONLY LOCAL DRAG ORIGIN (not persisted in stateManager/probes object)
      dragOrigin = {
        attachedTo: currentProbe.attachedTo
          ? {
              compId: currentProbe.attachedTo.compId,
              termId: currentProbe.attachedTo.termId
            }
          : null,
        worldX: currentTip.pos.x,
        worldY: currentTip.pos.y,
        wasDocked: !currentProbe.attachedTo && !currentProbe.isPlaced,
        wasPlaced: currentProbe.isPlaced === true
      };

      console.log("PROBE_ORIGIN_CAPTURED", {
        bindingId,
        dragOrigin: { ...dragOrigin, attachedTo: dragOrigin.attachedTo ? { ...dragOrigin.attachedTo } : null },
      });

      console.log("PROBE_EVENT_DOWN", {
        sessionId: dragSessionId,
        ...probeDebugSnapshot(currentProbe),
        dragOrigin: { ...dragOrigin, attachedTo: dragOrigin.attachedTo ? { ...dragOrigin.attachedTo } : null },
      });

      currentProbe.isDragging = true;
      currentProbe.dragWorldX = currentTip.pos.x;
      currentProbe.dragWorldY = currentTip.pos.y;

      // Actual electrical detach happens AFTER dragOrigin & currentTip captured
      currentProbe.attachedTo = null;

      stateManager.updateComponentProperty(comp.id, "probes", comp.properties.probes);
      stateManager.notify("simulation");
      logProbeAfterNotify("DOWN", dragSessionId);

      const onPointerMove = (moveEvt) => {
        if (!isDragging) return;
        if (moveEvt.cancelable) moveEvt.preventDefault();

        const moveCoords = getEvtCoords(moveEvt);
        if (moveCoords.x === undefined || moveCoords.y === undefined) return;

        const screenDist = Math.hypot(moveCoords.x - startScreenX, moveCoords.y - startScreenY);
        if (screenDist > 2) hasMoved = true;
        if (!hasMoved) return;

        const rawPos = this.workspace.screenToCanvas(moveCoords.x, moveCoords.y);
        const currentProbeState = comp.properties.probes[probeKey];

        // Clean up previous highlights
        document.querySelectorAll(".terminal-node.probe-snap-highlight").forEach(t => t.classList.remove("probe-snap-highlight"));

        // SNAP HYSTERESIS: Enter radius 18px, Release radius 28px
        const SNAP_ENTER_RADIUS = 18;
        const SNAP_RELEASE_RADIUS = 28;
        const origin = dragOrigin?.attachedTo;

        if (activeSnap) {
          const snapDist = Math.hypot(rawPos.x - activeSnap.pos.x, rawPos.y - activeSnap.pos.y);
          if (snapDist > SNAP_RELEASE_RADIUS) {
            activeSnap = null;
          }
        }

        console.log("PROBE_EVENT_MOVE", {
          sessionId: dragSessionId,
          activeSnap: activeSnap ? { compId: activeSnap.compId, termId: activeSnap.termId } : null,
          hasMoved,
          ...probeDebugSnapshot(currentProbeState),
          dragOrigin: dragOrigin ? { ...dragOrigin, attachedTo: dragOrigin.attachedTo ? { ...dragOrigin.attachedTo } : null } : null,
        });

        if (!activeSnap) {
          let candidate = this.workspace.connectionEngine.findNearestTerminalSnap(rawPos.x, rawPos.y, SNAP_ENTER_RADIUS);
          if (candidate && origin && candidate.compId === origin.compId && candidate.termId === origin.termId) {
            candidate = null;
          }
          if (candidate && candidate.compId !== comp.id) {
            activeSnap = candidate;
          } else {
            activeSnap = null;
          }
        }

        if (activeSnap) {
          activeSnap.el?.classList.add("probe-snap-highlight");
          currentProbeState.dragWorldX = activeSnap.pos.x;
          currentProbeState.dragWorldY = activeSnap.pos.y;
          currentProbeState.snapCandidate = { compId: activeSnap.compId, termId: activeSnap.termId };
        } else {
          currentProbeState.dragWorldX = rawPos.x;
          currentProbeState.dragWorldY = rawPos.y;
          delete currentProbeState.snapCandidate;
        }

        // Realtime render of probe tip and dynamic cable bezier path
        this.updateProbeCable(comp, probeKey);
      };

      const cleanupListeners = () => {
        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", onPointerUp);
        window.removeEventListener("pointercancel", onPointerCancel);
        window.removeEventListener("touchmove", onPointerMove);
        window.removeEventListener("touchend", onPointerUp);
        window.removeEventListener("touchcancel", onPointerCancel);
        window.removeEventListener("keydown", onKeyDown);
      };

      const onPointerUp = (upEvt) => {
        if (!isDragging) return;
        isDragging = false;
        probeEl.classList.remove("is-dragging");

        if (pointerId !== null && probeEl.releasePointerCapture) {
          try { probeEl.releasePointerCapture(pointerId); } catch (err) {}
        }
        pointerId = null;

        cleanupListeners();

        document.querySelectorAll(".terminal-node.probe-snap-highlight").forEach(t => t.classList.remove("probe-snap-highlight"));

        const currentProbe = comp.properties.probes[probeKey];
        const lastDragX = currentProbe.dragWorldX ?? dragOrigin?.worldX;
        const lastDragY = currentProbe.dragWorldY ?? dragOrigin?.worldY;

        console.log("PROBE_EVENT_UP_BEFORE", {
          sessionId: dragSessionId,
          activeSnap: activeSnap ? { compId: activeSnap.compId, termId: activeSnap.termId } : null,
          hasMoved,
          ...probeDebugSnapshot(currentProbe),
          dragOrigin: dragOrigin ? { ...dragOrigin, attachedTo: dragOrigin.attachedTo ? { ...dragOrigin.attachedTo } : null } : null,
        });
        console.log("PROBE_UP_BINDING", {
          bindingId,
          dragOrigin: dragOrigin ? { ...dragOrigin, attachedTo: dragOrigin.attachedTo ? { ...dragOrigin.attachedTo } : null } : null,
          activeSnap: activeSnap ? { compId: activeSnap.compId, termId: activeSnap.termId } : null,
        });

        // Clear temporary drag runtime properties
        currentProbe.isDragging = false;
        delete currentProbe.dragWorldX;
        delete currentProbe.dragWorldY;
        delete currentProbe.snapCandidate;

        let branchTaken;
        if (!hasMoved) {
          if (dragOrigin?.attachedTo) {
            currentProbe.attachedTo = {
              compId: dragOrigin.attachedTo.compId,
              termId: dragOrigin.attachedTo.termId
            };
            currentProbe.isPlaced = true;
            delete currentProbe.worldX;
            delete currentProbe.worldY;
            branchTaken = "NO_MOVE_ATTACHED";
          } else {
            currentProbe.attachedTo = null;
            currentProbe.isPlaced = false;
            delete currentProbe.worldX;
            delete currentProbe.worldY;
            branchTaken = "NO_MOVE_DOCK";
          }
          stateManager.updateComponentProperty(comp.id, "probes", comp.properties.probes);
          this.updateProbeCable(comp, probeKey);
          stateManager.notify("simulation");
          console.log("PROBE_EVENT_UP_AFTER", {
            sessionId: dragSessionId,
            branchTaken,
            ...probeDebugSnapshot(currentProbe),
            tipPosition: this.getProbeTipPosition(comp, probeKey).pos,
          });
          logProbeAfterNotify("UP", dragSessionId);
          dragOrigin = null;
          return;
        }

        // CASE 1: Dropped on valid new terminal -> COMMIT new connection
        if (activeSnap && activeSnap.compId !== comp.id) {
          currentProbe.attachedTo = {
            compId: activeSnap.compId,
            termId: activeSnap.termId
          };
          currentProbe.isPlaced = true;
          delete currentProbe.worldX;
          delete currentProbe.worldY;
          branchTaken = "COMMIT_NEW_NODE";
        }
        // CASE 2: Dropped on empty space / invalid target -> ALWAYS RETURN TO DOCK
        else {
          currentProbe.attachedTo = null;
          currentProbe.isPlaced = false;
          delete currentProbe.worldX;
          delete currentProbe.worldY;
          branchTaken = "RETURN_TO_DOCK";
        }

        console.log("PROBE_EVENT_UP_AFTER", {
          sessionId: dragSessionId,
          branchTaken,
          ...probeDebugSnapshot(currentProbe),
          tipPosition: this.getProbeTipPosition(comp, probeKey).pos,
        });

        dragOrigin = null;

        stateManager.updateComponentProperty(comp.id, "probes", comp.properties.probes);
        this.updateProbeCable(comp, probeKey);
        stateManager.notify("simulation");
        logProbeAfterNotify(branchTaken === "COMMIT_NEW_NODE" ? "POST_COMMIT" : "UP", dragSessionId);
      };

      const onPointerCancel = () => {
        if (!isDragging) return;
        console.log("PROBE_EVENT_CANCEL", { sessionId: dragSessionId });
        isDragging = false;
        probeEl.classList.remove("is-dragging");

        if (pointerId !== null && probeEl.releasePointerCapture) {
          try { probeEl.releasePointerCapture(pointerId); } catch (err) {}
        }
        pointerId = null;

        cleanupListeners();

        document.querySelectorAll(".terminal-node.probe-snap-highlight").forEach(t => t.classList.remove("probe-snap-highlight"));

        const currentProbe = comp.properties.probes[probeKey];

        // Clear temporary drag runtime properties
        currentProbe.isDragging = false;
        delete currentProbe.dragWorldX;
        delete currentProbe.dragWorldY;
        delete currentProbe.snapCandidate;

        // ONLY ON CANCEL / ESCAPE: Return to dock
        currentProbe.attachedTo = null;
        currentProbe.isPlaced = false;
        delete currentProbe.worldX;
        delete currentProbe.worldY;

        dragOrigin = null;

        stateManager.updateComponentProperty(comp.id, "probes", comp.properties.probes);
        this.updateProbeCable(comp, probeKey);
        stateManager.notify("simulation");
        logProbeAfterNotify("CANCEL", dragSessionId);
      };

      const onKeyDown = (keyEvt) => {
        if (keyEvt.key === "Escape" && isDragging) {
          keyEvt.preventDefault();
          onPointerCancel();
        }
      };

      window.addEventListener("pointermove", onPointerMove, { passive: false });
      window.addEventListener("pointerup", onPointerUp);
      window.addEventListener("pointercancel", onPointerCancel);
      window.addEventListener("touchmove", onPointerMove, { passive: false });
      window.addEventListener("touchend", onPointerUp);
      window.addEventListener("touchcancel", onPointerCancel);
      window.addEventListener("keydown", onKeyDown);
    };

    probeEl.addEventListener("pointerdown", onPointerDown, { passive: false });

    // Double click on probe docks it cleanly back onto the multimeter casing
    probeEl.addEventListener("dblclick", (e) => {
      e.stopPropagation();
      comp.properties.probes[probeKey].attachedTo = null;
      comp.properties.probes[probeKey].isPlaced = false;
      delete comp.properties.probes[probeKey].worldX;
      delete comp.properties.probes[probeKey].worldY;
      stateManager.updateComponentProperty(comp.id, "probes", comp.properties.probes);
      this.updateProbeCable(comp, probeKey);
      stateManager.notify("simulation");
    });
  }

  animateProbeRollback(comp, probeKey, fromX, fromY) {
    const startTime = performance.now();
    const duration = 280; // ms

    const step = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // Cubic ease-out
      const ease = 1 - Math.pow(1 - progress, 3);

      const defaultRelX = probeKey === "com" ? 28 : 104;
      const defaultRelY = 285;
      const targetPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, defaultRelX, defaultRelY, comp.rotation || 0);

      const curX = fromX + (targetPos.x - fromX) * ease;
      const curY = fromY + (targetPos.y - fromY) * ease;

      const handoffPos = this.getMultimeterHandoffPosition(comp, probeKey);
      const orient = this.calculateProbeOrientation({ x: Math.round(curX), y: Math.round(curY) }, handoffPos, false, comp.rotation || 0);

      const probeEl = document.getElementById(`probe-${probeKey}-${comp.id}`);
      const wireEl = document.getElementById(`meter-wire-${probeKey}-${comp.id}`);

      if (probeEl) {
        probeEl.style.left = `${orient.targetX - 12}px`;
        probeEl.style.top = `${orient.topY}px`;
        probeEl.style.transformOrigin = "12px 52px";
        probeEl.style.transform = orient.angleDeg ? `rotate(${orient.angleDeg}deg)` : "none";
      }
      if (wireEl) {
        wireEl.setAttribute("d", this.calculateProbeWirePath(handoffPos.x, handoffPos.y, orient.cableX, orient.cableY, orient.angleDeg));
      }

      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        comp.properties.probes[probeKey].isPlaced = false;
        comp.properties.probes[probeKey].attachedTo = null;
        delete comp.properties.probes[probeKey].worldX;
        delete comp.properties.probes[probeKey].worldY;
        this.updateProbeCable(comp, probeKey);
      }
    };

    requestAnimationFrame(step);
  }

  updateMultimeterProbeVisuals(comp) {
    if (!comp || comp.type !== "multimeter") return;

    if (!comp.properties.probes) {
      comp.properties.probes = {
        com: { attachedTo: null, isPlaced: false },
        vwma: { attachedTo: null, isPlaced: false }
      };
    }

    // Ensure probe elements exist
    if (!document.getElementById(`probe-com-${comp.id}`) && this.layer) {
      const comEl = this.createProbeElement(comp, "com");
      this.layer.appendChild(comEl);
    }
    if (!document.getElementById(`probe-vwma-${comp.id}`) && this.layer) {
      const vwmaEl = this.createProbeElement(comp, "vwma");
      this.layer.appendChild(vwmaEl);
    }

    // Ensure SVG wire elements exist in meter-probes-wires-group (background layer)
    const svgLayer = document.getElementById("svg-cable-layer");
    let meterProbesGroup = document.getElementById("meter-probes-wires-group");
    if (!meterProbesGroup && svgLayer) {
      meterProbesGroup = document.createElementNS("http://www.w3.org/2000/svg", "g");
      meterProbesGroup.setAttribute("id", "meter-probes-wires-group");
      svgLayer.appendChild(meterProbesGroup);
    }

    if (meterProbesGroup) {
      if (!document.getElementById(`meter-wire-com-${comp.id}`)) {
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute("id", `meter-wire-com-${comp.id}`);
        path.setAttribute("class", "meter-probe-wire probe-wire-black");
        path.setAttribute("data-comp-id", comp.id);
        path.setAttribute("fill", "none");
        path.setAttribute("stroke", "#0f172a");
        path.setAttribute("stroke-width", "4.5");
        path.setAttribute("stroke-linecap", "round");
        meterProbesGroup.appendChild(path);
      }
      if (!document.getElementById(`meter-wire-vwma-${comp.id}`)) {
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute("id", `meter-wire-vwma-${comp.id}`);
        path.setAttribute("class", "meter-probe-wire probe-wire-red");
        path.setAttribute("data-comp-id", comp.id);
        path.setAttribute("fill", "none");
        path.setAttribute("stroke", "#dc2626");
        path.setAttribute("stroke-width", "4.5");
        path.setAttribute("stroke-linecap", "round");
        meterProbesGroup.appendChild(path);
      }
    }

    // Ensure Front SVG layer & front plugs/leads exist in meter-front-group (front layer)
    let svgFrontLayer = document.getElementById("svg-front-cable-layer");
    if (!svgFrontLayer && this.workspace?.container) {
      const canvasLayer = document.getElementById("canvas-layer");
      if (canvasLayer) {
        svgFrontLayer = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svgFrontLayer.setAttribute("id", "svg-front-cable-layer");
        svgFrontLayer.setAttribute("class", "cables-front-svg-layer");
        canvasLayer.appendChild(svgFrontLayer);
      }
    }

    let meterFrontGroup = document.getElementById("meter-front-group");
    if (!meterFrontGroup && svgFrontLayer) {
      meterFrontGroup = document.createElementNS("http://www.w3.org/2000/svg", "g");
      meterFrontGroup.setAttribute("id", "meter-front-group");
      svgFrontLayer.appendChild(meterFrontGroup);
    }

    if (meterFrontGroup) {
      if (!document.getElementById(`meter-front-lead-com-${comp.id}`)) {
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute("id", `meter-front-lead-com-${comp.id}`);
        path.setAttribute("class", "meter-front-lead front-lead-black");
        path.setAttribute("data-comp-id", comp.id);
        path.setAttribute("fill", "none");
        path.setAttribute("stroke", "#0f172a");
        path.setAttribute("stroke-width", "4.5");
        path.setAttribute("stroke-linecap", "round");
        path.setAttribute("style", "filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); pointer-events: none;");
        meterFrontGroup.appendChild(path);
      }
      if (!document.getElementById(`meter-plug-com-${comp.id}`)) {
        const plugCom = this.createBananaPlugElement("com", comp.id);
        meterFrontGroup.appendChild(plugCom);
      }
      if (!document.getElementById(`meter-front-lead-vwma-${comp.id}`)) {
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute("id", `meter-front-lead-vwma-${comp.id}`);
        path.setAttribute("class", "meter-front-lead front-lead-red");
        path.setAttribute("data-comp-id", comp.id);
        path.setAttribute("fill", "none");
        path.setAttribute("stroke", "#dc2626");
        path.setAttribute("stroke-width", "4.5");
        path.setAttribute("stroke-linecap", "round");
        path.setAttribute("style", "filter: drop-shadow(0 2px 2px rgba(0,0,0,0.5)); pointer-events: none;");
        meterFrontGroup.appendChild(path);
      }
      if (!document.getElementById(`meter-plug-vwma-${comp.id}`)) {
        const plugVwma = this.createBananaPlugElement("vwma", comp.id);
        meterFrontGroup.appendChild(plugVwma);
      }
    }

    this.checkAndTriggerPlugAnimation(comp);
    this.updateProbeCable(comp, "com");
    if (!this.multimeterPlugAnimations?.get(comp.id)?.isAnimating) {
      this.updateProbeCable(comp, "vwma");
    }
  }

  updateSelectionVisuals() {
    const state = stateManager.getState();
    const allCompEls = document.querySelectorAll(".workspace-component");
    allCompEls.forEach((el) => el.classList.remove("selected"));

    if (state.selection.type === "component" && state.selection.id) {
      const selectedEl = document.getElementById(`comp-${state.selection.id}`);
      if (selectedEl) selectedEl.classList.add("selected");
    }
  }
}
