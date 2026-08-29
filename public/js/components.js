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

export const COMPONENT_PROTOTYPES = {
  battery: {
    type: "battery",
    name: "Baterai DC",
    icon: "🔋",
    width: 140,
    height: 70,
    defaultProps: { voltage: 12, internalResistance: 0.05 },
    terminals: [
      { id: "term_pos", name: "+", label: "+ (12V)", relX: 135, relY: 35, color: "#ef4444" },
      { id: "term_neg", name: "-", label: "- (GND)", relX: 5, relY: 35, color: "#0f172a" }
    ]
  },
  switch_spst: {
    type: "switch_spst",
    name: "Saklar Rocker",
    icon: "🎚️",
    width: 130,
    height: 75,
    defaultProps: { isClosed: false },
    terminals: [
      { id: "term_1", name: "1", label: "Pin Input (1)", relX: 10, relY: 37, color: "#38bdf8" },
      { id: "term_2", name: "2", label: "Pin Output (2)", relX: 120, relY: 37, color: "#38bdf8" }
    ]
  },
  lamp: {
    type: "lamp",
    name: "Lampu Pijar",
    icon: "💡",
    width: 100,
    height: 95,
    defaultProps: { nominalVoltage: 12, powerRating: 20, resistance: 7.2 },
    terminals: [
      { id: "term_pos", name: "+", label: "Pin +", relX: 15, relY: 72, color: "#ef4444" },
      { id: "term_neg", name: "-", label: "Pin -", relX: 85, relY: 72, color: "#0f172a" }
    ]
  },
  led: {
    type: "led",
    name: "LED Merah",
    icon: "🔴",
    width: 90,
    height: 85,
    defaultProps: { forwardVoltage: 2.0, maxCurrent: 0.025, resistance: 10 },
    terminals: [
      { id: "term_anode", name: "A", label: "Anoda (+)", relX: 15, relY: 65, color: "#ef4444" },
      { id: "term_cathode", name: "K", label: "Katoda (-)", relX: 75, relY: 65, color: "#0f172a" }
    ]
  },
  resistor: {
    type: "resistor",
    name: "Resistor",
    icon: "〰️",
    width: 90,
    height: 40,
    defaultProps: { resistance: 220 },
    terminals: [
      { id: "term_a", name: "A", label: "Pin A", relX: 4, relY: 20, color: "#38bdf8" },
      { id: "term_b", name: "B", label: "Pin B", relX: 86, relY: 20, color: "#38bdf8" }
    ]
  },
  multimeter: {
    type: "multimeter",
    name: "Multimeter Digital",
    icon: "📟",
    width: 132,
    height: 262,
    defaultProps: { mode: "V_DC", reading: "0.00 V" },
    terminals: [
      { id: "term_com", name: "COM", label: "Probe COM (Hitam / Ground)", relX: 28, relY: 245, color: "#0f172a" },
      { id: "term_vwma", name: "VΩ", label: "Probe VΩmA (Merah / +)", relX: 104, relY: 245, color: "#ef4444" }
    ]
  },
  motor_dc: {
    type: "motor_dc",
    name: "Motor DC",
    icon: "⚙️",
    width: 120,
    height: 100,
    defaultProps: { nominalVoltage: 12, powerRating: 24, resistance: 6, maxRpm: 3000, currentRpm: 0, direction: "CW" },
    terminals: [
      { id: "term_pos", name: "+", label: "Pin + (Merah)", relX: 18, relY: 76, color: "#ef4444" },
      { id: "term_neg", name: "-", label: "Pin - (Hitam)", relX: 102, relY: 76, color: "#0f172a" }
    ]
  },
  diode: {
    type: "diode",
    name: "Dioda 1N4007",
    icon: "🔺",
    width: 84,
    height: 40,
    defaultProps: { forwardVoltage: 0.7, model: "1N4007", state: "IDLE", resistance: 0.5 },
    terminals: [
      { id: "term_anode", name: "A", label: "Anoda (A / +)", relX: 4, relY: 20, color: "#38bdf8" },
      { id: "term_cathode", name: "K", label: "Katoda (K / - Garis)", relX: 80, relY: 20, color: "#94a3b8" }
    ]
  }
};

let componentCounter = 1;

export class ComponentEngine {
  /**
   * @param {import("./workspace.js").WorkspaceEngine} workspaceEngine 
   */
  constructor(workspaceEngine) {
    this.workspace = workspaceEngine;
    this.layer = document.getElementById("components-layer");
    this.draggedItemType = null;
    this.floatingToolbar = null;
  }

  init() {
    this.bindPaletteDragEvents();
    this.bindWorkspaceDropEvents();
    this.bindKeyboardEvents();

    stateManager.subscribe("components", () => {
      this.syncDOM();
      this.updateFloatingToolbarPosition();
      this.updateAllMultimeterProbes();
    });
    stateManager.subscribe("components_moving", () => {
      this.updateFloatingToolbarPosition();
      this.updateAllMultimeterProbes();
    });
    stateManager.subscribe("selection", () => {
      this.updateSelectionVisuals();
      this.renderFloatingToolbar();
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
          this.createComponent(type, pos.x - 70, pos.y - 35);
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
      
      const x = pos.x - Math.round(proto.width / 2);
      const y = pos.y - Math.round(proto.height / 2);

      this.createComponent(type, x, y);
    });
  }

  bindKeyboardEvents() {
    window.addEventListener("keydown", (e) => {
      const state = stateManager.getState();
      
      if (state.selection.type === "component" && state.selection.id) {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement.tagName)) return;

        if (e.key === "r" || e.key === "R") {
          e.preventDefault();
          stateManager.rotateComponent(state.selection.id, 90);
        }

        if (e.key === "Delete" || e.key === "Backspace") {
          stateManager.deleteComponent(state.selection.id);
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

      this.updateMultimeterProbeVisuals(comp);
    });

    this.renderFloatingToolbar();
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
        el.appendChild(termEl);
      });
    } else {
      // Dedicated Multimeter Controls (Mode button, Range button, Dial clicks)
      const modeBtn = el.querySelector(".meter-function-row .meter-chip-btn:nth-child(3)");
      const rangeBtn = el.querySelector(".meter-function-row .meter-chip-btn:nth-child(4)");
      const dialEl = el.querySelector(".meter-rotary-knob");
      const labelV = el.querySelector(".label-v");
      const labelOhm = el.querySelector(".label-ohm");
      const labelA = el.querySelector(".label-a");

      const cycleMultimeterMode = (e) => {
        e.stopPropagation();
        if (e.cancelable) e.preventDefault();
        const modes = ["V_DC", "OHM", "A_DC"];
        const curr = comp.properties.mode || "V_DC";
        const nextMode = modes[(modes.indexOf(curr) + 1) % modes.length];
        comp.properties.mode = nextMode;
        this.updateComponentVisualProperties(el, comp);
        stateManager.updateComponentProperty(comp.id, "mode", nextMode);
        stateManager.notify("simulation");
      };

      if (modeBtn) modeBtn.addEventListener("click", cycleMultimeterMode);
      if (rangeBtn) rangeBtn.addEventListener("click", cycleMultimeterMode);
      if (dialEl) dialEl.addEventListener("click", cycleMultimeterMode);

      if (labelV) {
        labelV.addEventListener("click", (e) => {
          e.stopPropagation();
          comp.properties.mode = "V_DC";
          this.updateComponentVisualProperties(el, comp);
          stateManager.updateComponentProperty(comp.id, "mode", "V_DC");
          stateManager.notify("simulation");
        });
      }
      if (labelOhm) {
        labelOhm.addEventListener("click", (e) => {
          e.stopPropagation();
          comp.properties.mode = "OHM";
          this.updateComponentVisualProperties(el, comp);
          stateManager.updateComponentProperty(comp.id, "mode", "OHM");
          stateManager.notify("simulation");
        });
      }
      if (labelA) {
        labelA.addEventListener("click", (e) => {
          e.stopPropagation();
          comp.properties.mode = "A_DC";
          this.updateComponentVisualProperties(el, comp);
          stateManager.updateComponentProperty(comp.id, "mode", "A_DC");
          stateManager.notify("simulation");
        });
      }
    }

    this.bindComponentDrag(el, comp);

    const handleComponentModal = (e) => {
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
      const unit = mode === "OHM" ? "Ω" : (mode === "A_DC" ? "A" : "V");
      const modeBadge = mode === "OHM" ? "Ω" : "DC";
      let dialAngle = -45;
      if (mode === "OHM") dialAngle = 0;
      else if (mode === "A_DC") dialAngle = 45;

      return `
        <div class="multimeter-visual fluke-179-style dark-edition">
          <!-- Main Vertical Handheld Casing with Yellow Holster Bumper -->
          <div class="meter-casing-vertical">
            <!-- Top Header Branding -->
            <div class="meter-header">
              <span class="meter-brand-badge">FLUXUS</span>
              <span class="meter-model-text">FL-179 TRUE RMS</span>
            </div>

            <!-- Authentic Light Fluke STN LCD Screen -->
            <div class="meter-lcd-bezel">
              <div class="meter-lcd-screen-light">
                <div class="meter-lcd-top-bar">
                  <span class="lcd-badge-auto">AUTO</span>
                  <span class="lcd-badge-mode" id="meter-mode-badge-${comp.id}">${modeBadge}</span>
                  <span class="lcd-badge-rms">True RMS</span>
                </div>
                <div class="meter-lcd-main-light">
                  <span class="meter-comp-val" id="meter-val-${comp.id}">${comp.properties.reading || '0.00'}</span>
                  <span class="meter-comp-unit" id="meter-unit-${comp.id}">${unit}</span>
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

            <!-- Function Push Buttons Bar (Red Power Button + Function Keys) -->
            <div class="meter-function-row">
              <span class="meter-power-btn" title="Power">⏻</span>
              <span class="meter-chip-btn">HOLD</span>
              <span class="meter-chip-btn">MODE</span>
              <span class="meter-chip-btn">RANGE</span>
            </div>

            <!-- Center Rotary Selector Dial Section with Detailed Scale -->
            <div class="meter-dial-section">
              <!-- Radial Markings around Dial -->
              <div class="meter-dial-label label-v ${mode === 'V_DC' ? 'active' : ''}">V⎓</div>
              <div class="meter-dial-label label-vac">V~</div>
              <div class="meter-dial-label label-ohm ${mode === 'OHM' ? 'active' : ''}">Ω</div>
              <div class="meter-dial-label label-cont">·)))</div>
              <div class="meter-dial-label label-a ${mode === 'A_DC' ? 'active' : ''}">A⎓</div>
              <div class="meter-dial-label label-aac">A~</div>

              <div class="meter-rotary-knob" id="meter-dial-${comp.id}" style="transform: rotate(${dialAngle}deg);">
                <div class="knob-face">
                  <div class="knob-bar-handle">
                    <div class="knob-pointer-arrow"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom 4-Port Jack Panel (COM, V/Ω, mA, 10A) with Yellow Accents -->
            <div class="meter-jacks-panel">
              <div class="meter-banana-jack jack-10a">
                <div class="jack-socket-rim">
                  <div class="jack-socket-hole"></div>
                </div>
                <span class="jack-name">10A</span>
              </div>

              <div class="meter-banana-jack jack-com">
                <div class="jack-socket-rim">
                  <div class="jack-socket-hole"></div>
                </div>
                <span class="jack-name">COM</span>
              </div>

              <div class="meter-banana-jack jack-vwma">
                <div class="jack-socket-rim">
                  <div class="jack-socket-hole"></div>
                </div>
                <span class="jack-name">V·Ω·mA</span>
              </div>

              <div class="meter-banana-jack jack-temp">
                <div class="jack-socket-rim">
                  <div class="jack-socket-hole"></div>
                </div>
                <span class="jack-name">mA</span>
              </div>
            </div>
          </div>
        </div>
      `;
    } else if (comp.type === "led") {
      return `
        <div class="led-visual" id="led-vis-${comp.id}">
          <div class="led-dome"></div>
          <div class="led-label">LED 2V</div>
        </div>
      `;
    } else if (comp.type === "motor_dc") {
      return `
        <div class="motor-visual" id="motor-vis-${comp.id}">
          <div class="motor-casing">
            <div class="motor-metal-shine"></div>
            <div class="motor-rotor-hub">
              <div class="motor-rotor-blades" id="motor-rotor-${comp.id}">
                <div class="motor-blade blade-1"></div>
                <div class="motor-blade blade-2"></div>
                <div class="motor-blade blade-3"></div>
                <div class="motor-shaft-center"></div>
              </div>
            </div>
            <div class="motor-vents">
              <span class="motor-vent"></span>
              <span class="motor-vent"></span>
              <span class="motor-vent"></span>
            </div>
          </div>
          <div class="motor-rpm-badge" id="motor-rpm-${comp.id}">
            <span class="rpm-number">0</span> <span class="rpm-unit">RPM</span>
          </div>
          <div class="motor-label" id="motor-lbl-${comp.id}">${comp.properties.nominalVoltage}V / ${comp.properties.powerRating}W</div>
        </div>
      `;
    } else if (comp.type === "diode") {
      return `
        <div class="diode-visual" id="diode-vis-${comp.id}">
          <div class="diode-lead lead-left"></div>
          <div class="diode-body">
            <div class="diode-cathode-band"></div>
            <div class="diode-text">${comp.properties.model || '1N4007'}</div>
            <div class="diode-symbol">▶|</div>
          </div>
          <div class="diode-lead lead-right"></div>
          <div class="diode-bias-badge" id="diode-bias-${comp.id}">
            <span class="bias-indicator-dot"></span>
            <span class="bias-status-text">STANDBY</span>
          </div>
          <div class="diode-label" id="diode-lbl-${comp.id}">Vf: ${comp.properties.forwardVoltage || 0.7}V</div>
        </div>
      `;
    }
    return "";
  }

  updateComponentVisualProperties(el, comp) {
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
    } else if (comp.type === "multimeter") {
      const valEl = el.querySelector(`#meter-val-${comp.id}`);
      const unitEl = el.querySelector(`#meter-unit-${comp.id}`);
      const modeBadge = el.querySelector(`#meter-mode-badge-${comp.id}`);
      const dial = el.querySelector(`#meter-dial-${comp.id}`) || el.querySelector(".meter-rotary-knob") || el.querySelector(".meter-comp-dial");
      const mode = comp.properties.mode || "V_DC";

      if (valEl && comp.properties.reading) valEl.textContent = comp.properties.reading;
      if (unitEl) unitEl.textContent = mode === "OHM" ? "Ω" : (mode === "A_DC" ? "A" : "V");
      if (modeBadge) modeBadge.textContent = mode === "OHM" ? "Ω" : "DC";

      const labelV = el.querySelector(".label-v");
      const labelOhm = el.querySelector(".label-ohm");
      const labelA = el.querySelector(".label-a");
      if (labelV) labelV.classList.toggle("active", mode === "V_DC");
      if (labelOhm) labelOhm.classList.toggle("active", mode === "OHM");
      if (labelA) labelA.classList.toggle("active", mode === "A_DC");

      if (dial) {
        let angle = -45;
        if (mode === "OHM") angle = 0;
        else if (mode === "A_DC") angle = 45;
        dial.style.transform = `rotate(${angle}deg)`;
        dial.style.transition = "transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1)";
      }

      this.updateMultimeterProbeVisuals(comp);
    } else if (comp.type === "motor_dc") {
      const lbl = el.querySelector(`#motor-lbl-${comp.id}`);
      const rpmNum = el.querySelector(`#motor-rpm-${comp.id} .rpm-number`);
      const rotor = el.querySelector(`#motor-rotor-${comp.id}`);
      if (lbl) lbl.textContent = `${comp.properties.nominalVoltage}V / ${comp.properties.powerRating}W`;
      if (rpmNum) rpmNum.textContent = comp.properties.currentRpm || 0;
      if (rotor) {
        if ((comp.properties.currentRpm || 0) > 0) {
          rotor.classList.add("spinning");
        } else {
          rotor.classList.remove("spinning");
        }
      }
    } else if (comp.type === "diode") {
      const biasBadge = el.querySelector(`#diode-bias-${comp.id}`);
      const biasStatus = biasBadge?.querySelector(".bias-status-text");
      const diodeState = comp.properties.state || "IDLE";

      if (biasBadge && biasStatus) {
        biasBadge.classList.remove("bias-forward", "bias-reverse", "bias-idle");
        if (diodeState === "FORWARD") {
          biasBadge.classList.add("bias-forward");
          biasStatus.textContent = "FORWARD BIAS";
        } else if (diodeState === "REVERSE") {
          biasBadge.classList.add("bias-reverse");
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
          const rawPos = this.workspace.screenToCanvas(clientX, clientY);
          const nearTerm = this.workspace.connectionEngine.findNearestTerminalSnap(rawPos.x, rawPos.y, 45);
          if (nearTerm) {
            e.stopPropagation();
            if (e.cancelable) e.preventDefault();
            this.workspace.connectionEngine.handleTerminalClick(nearTerm.compId, nearTerm.termId, nearTerm.el);
            return;
          }
        }
        return;
      }

      // NEVER intercept if clicking a terminal node, smart numbering badge, hanging wire node, or probe assembly
      if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge") || e.target.closest(".hanging-wire-node") || e.target.closest(".probe-assembly")) {
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
          const newX = Math.round(compStartX + deltaX);
          const newY = Math.round(compStartY + deltaY);

          el.style.left = `${newX}px`;
          el.style.top = `${newY}px`;
          comp.x = newX;
          comp.y = newY;

          this.updateFloatingToolbarPosition();
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
          stateManager.updateComponentPosition(comp.id, comp.x, comp.y);
          this.updateFloatingToolbarPosition();
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

  updateFloatingToolbarPosition() {
    if (!this.floatingToolbar) return;
    const state = stateManager.getState();
    if (state.selection.type !== "component" || !state.selection.id) return;

    const comp = state.components.find(c => c.id === state.selection.id);
    if (!comp) return;

    const rot = (comp.rotation || 0) % 360;
    const isOrthogonal = (rot === 90 || rot === 270);
    const effectiveHeight = isOrthogonal ? comp.width : comp.height;

    const centerX = comp.x + comp.width / 2;
    const topY = comp.y + comp.height / 2 - effectiveHeight / 2;

    this.floatingToolbar.style.left = `${Math.round(centerX)}px`;
    this.floatingToolbar.style.top = `${Math.round(topY - 12)}px`;
  }

  updateAllMultimeterProbes() {
    const state = stateManager.getState();
    const multimeters = state.components.filter(c => c.type === "multimeter");
    multimeters.forEach(m => {
      this.updateMultimeterProbeVisuals(m);
    });
  }

  renderFloatingToolbar() {
    if (this.floatingToolbar) {
      this.floatingToolbar.remove();
      this.floatingToolbar = null;
    }

    const state = stateManager.getState();
    if (state.selection.type !== "component" || !state.selection.id) return;

    const comp = state.components.find(c => c.id === state.selection.id);
    if (!comp) return;

    const rot = (comp.rotation || 0) % 360;
    const isOrthogonal = (rot === 90 || rot === 270);
    const effectiveHeight = isOrthogonal ? comp.width : comp.height;

    const centerX = comp.x + comp.width / 2;
    const topY = comp.y + comp.height / 2 - effectiveHeight / 2;

    const tb = document.createElement("div");
    tb.className = "component-floating-toolbar";
    tb.style.left = `${Math.round(centerX)}px`;
    tb.style.top = `${Math.round(topY - 12)}px`;

    tb.innerHTML = `
      <button class="btn-comp-action" id="btn-comp-rotate" title="Putar 90° (Shortcut: Tombol R)">
        <span>🔄</span> Putar 90°
      </button>
      <button class="btn-comp-action danger" id="btn-comp-del" title="Hapus Komponen (Shortcut: Del)">
        <span>🗑️</span>
      </button>
    `;

    tb.querySelector("#btn-comp-rotate").addEventListener("click", (e) => {
      e.stopPropagation();
      stateManager.rotateComponent(comp.id, 90);
    });

    tb.querySelector("#btn-comp-del").addEventListener("click", (e) => {
      e.stopPropagation();
      stateManager.deleteComponent(comp.id);
    });

    const compLayer = document.getElementById("components-layer");
    if (compLayer) compLayer.appendChild(tb);
    this.floatingToolbar = tb;
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
    const currentW = comp.properties.powerRating || 24;
    const currentRpm = comp.properties.maxRpm || 3000;

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

          <label class="input-label">Daya Motor (Watt):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-motor-w" value="${currentW}" min="1" max="500">
            <span class="input-unit">W</span>
          </div>

          <label class="input-label">Kecepatan Maksimal (RPM):</label>
          <div class="modal-input-group">
            <input type="number" class="modal-input" id="modal-motor-rpm" value="${currentRpm}" min="100" max="20000" step="100">
            <span class="input-unit">RPM</span>
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
      const w = Number(backdrop.querySelector("#modal-motor-w").value) || 24;
      const rpm = Number(rpmInput.value) || 3000;
      const r = (v * v) / w;
      stateManager.updateComponentProperty(comp.id, "nominalVoltage", v);
      stateManager.updateComponentProperty(comp.id, "powerRating", w);
      stateManager.updateComponentProperty(comp.id, "maxRpm", rpm);
      stateManager.updateComponentProperty(comp.id, "resistance", Number(r.toFixed(2)));
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

  calculateProbeWirePath(startX, startY, endX, endY) {
    const dx = endX - startX;
    const dy = endY - startY;
    const dist = Math.hypot(dx, dy);

    // Natural cable sag factor based on distance
    const sag = Math.min(80, Math.max(20, dist * 0.22));

    // Control point 1: leaves the multimeter socket going straight downwards then curves
    const cp1x = Math.round(startX + dx * 0.15);
    const cp1y = Math.round(startY + sag + (dy > 0 ? dy * 0.15 : 0));

    // Control point 2: enters the top of the probe lead collar
    const cp2x = Math.round(endX - dx * 0.15);
    const cp2y = Math.round(endY - sag * 0.55 + (dy < 0 ? dy * 0.15 : 0));

    return `M ${startX} ${startY} C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${endX} ${endY}`;
  }

  /**
   * Calculates the probe orientation and top cable entry coordinates
   * Connected: stands upright (angle = 0) plugged into pin socket depth (40px)
   * Idle: docks aligned with multimeter body casing (52px)
   */
  calculateProbeOrientation(probePos, jackPos, isConnected, compRotation = 0) {
    const originX = jackPos.x;
    const originY = jackPos.y;
    const targetX = probePos.x;
    const targetY = probePos.y;

    console.log('origin:', originX, originY);
    console.log('target:', targetX, targetY);

    // Connected state: stands upright on pin (0 deg) matching hosting version
    // Idle state: rotates with the multimeter casing
    const angleDeg = isConnected ? 0 : (compRotation || 0);
    const rad = (angleDeg * Math.PI) / 180;

    // Y offset when plugged into pin (40px) vs idle on casing (52px)
    const probeHeightOffset = isConnected ? 40 : 52;
    const topY = targetY - probeHeightOffset;
    const cableX = Math.round(targetX + probeHeightOffset * Math.sin(rad));
    const cableY = Math.round(targetY - probeHeightOffset * Math.cos(rad));

    const posisiUjungX = targetX;
    const posisiUjungY = targetY;
    console.log('ujung jarum akhir:', posisiUjungX, posisiUjungY);

    return { angleDeg, topY, cableX, cableY, targetX, targetY, posisiUjungX, posisiUjungY };
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
        
        <!-- Top Cable Strain Relief (y: 0-4, center x=12) -->
        <rect x="9" y="0" width="6" height="4" rx="1.5" fill="#0f172a" stroke="#334155" stroke-width="0.75"/>
        
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
    let isDragging = false;
    let hasMoved = false;
    let activeSnap = null;
    let currentRawPos = null;
    let startScreenX = 0;
    let startScreenY = 0;
    let initialAttachedTo = null;

    const getEvtCoords = (evt) => {
      if (evt.touches && evt.touches.length > 0) {
        return { x: evt.touches[0].clientX, y: evt.touches[0].clientY };
      }
      if (evt.changedTouches && evt.changedTouches.length > 0) {
        return { x: evt.changedTouches[0].clientX, y: evt.changedTouches[0].clientY };
      }
      return { x: evt.clientX, y: evt.clientY };
    };

    const onPointerDown = (e) => {
      e.stopPropagation();
      if (e.cancelable) e.preventDefault();

      if (e.pointerId !== undefined && probeEl.setPointerCapture) {
        try { probeEl.setPointerCapture(e.pointerId); } catch (err) {}
      }

      isDragging = true;
      hasMoved = false;
      activeSnap = null;

      const coords = getEvtCoords(e);
      startScreenX = coords.x ?? 0;
      startScreenY = coords.y ?? 0;

      // Capture initial attachment state
      initialAttachedTo = comp.properties.probes?.[probeKey]?.attachedTo 
        ? { ...comp.properties.probes[probeKey].attachedTo } 
        : null;

      probeEl.classList.add("is-dragging");

      const onPointerMove = (moveEvt) => {
        if (!isDragging) return;
        if (moveEvt.cancelable) moveEvt.preventDefault();

        const moveCoords = getEvtCoords(moveEvt);
        if (moveCoords.x === undefined || moveCoords.y === undefined) return;

        const screenDist = Math.hypot(moveCoords.x - startScreenX, moveCoords.y - startScreenY);
        if (screenDist > 3) {
          hasMoved = true;
        }

        if (!hasMoved) return;

        const rawPos = this.workspace.screenToCanvas(moveCoords.x, moveCoords.y);
        currentRawPos = rawPos;

        // If previously attached, remove old highlight and detach active property during drag
        if (initialAttachedTo) {
          const oldTargetEl = document.getElementById(
            `term-${initialAttachedTo.compId}-${initialAttachedTo.termId}`
          );
          if (oldTargetEl) oldTargetEl.classList.remove(`probe-attached-${probeKey}`);
        }
        comp.properties.probes[probeKey].attachedTo = null;

        // Clean up previous highlights
        document.querySelectorAll(".terminal-node.probe-snap-highlight").forEach(t => t.classList.remove("probe-snap-highlight"));

        // Terminal Pin Snap (radius 35px)
        const snap = this.workspace.connectionEngine.findNearestTerminalSnap(rawPos.x, rawPos.y, 35);

        let finalPos = rawPos;
        if (snap && snap.compId !== comp.id) {
          activeSnap = snap;
          snap.el.classList.add("probe-snap-highlight");
          finalPos = snap.pos;
        } else {
          activeSnap = null;
        }

        comp.properties.probes[probeKey].worldX = finalPos.x;
        comp.properties.probes[probeKey].worldY = finalPos.y;
        comp.properties.probes[probeKey].isPlaced = true;

        const jackRelX = probeKey === "com" ? 48 : 84;
        const jackPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, jackRelX, 186, comp.rotation);
        const orient = this.calculateProbeOrientation(finalPos, jackPos, !!activeSnap, comp.rotation);

        // Deterministic Locked Anchor with hosting socket depth
        probeEl.style.left = `${orient.targetX - 12}px`;
        probeEl.style.top = `${orient.topY}px`;
        probeEl.style.transformOrigin = "12px 52px";
        probeEl.style.transform = orient.angleDeg ? `rotate(${orient.angleDeg}deg)` : "none";

        const wireEl = document.getElementById(`meter-wire-${probeKey}-${comp.id}`);
        if (wireEl) {
          wireEl.setAttribute("d", this.calculateProbeWirePath(jackPos.x, jackPos.y, orient.cableX, orient.cableY));
        }
      };

      const onPointerUp = (upEvt) => {
        if (!isDragging) return;
        isDragging = false;
        probeEl.classList.remove("is-dragging");

        if (e.pointerId !== undefined && probeEl.releasePointerCapture) {
          try { probeEl.releasePointerCapture(e.pointerId); } catch (err) {}
        }

        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", onPointerUp);
        window.removeEventListener("pointercancel", onPointerUp);
        window.removeEventListener("mousemove", onPointerMove);
        window.removeEventListener("mouseup", onPointerUp);
        window.removeEventListener("touchmove", onPointerMove);
        window.removeEventListener("touchend", onPointerUp);
        window.removeEventListener("touchcancel", onPointerUp);

        document.querySelectorAll(".terminal-node.probe-snap-highlight").forEach(t => t.classList.remove("probe-snap-highlight"));

        // Case 1: Simple click without actual drag -> PRESERVE CURRENT ATTACHMENT STATE
        if (!hasMoved) {
          if (initialAttachedTo) {
            comp.properties.probes[probeKey].attachedTo = initialAttachedTo;
            const oldTargetEl = document.getElementById(
              `term-${initialAttachedTo.compId}-${initialAttachedTo.termId}`
            );
            if (oldTargetEl) oldTargetEl.classList.add(`probe-attached-${probeKey}`);
          }
          this.updateMultimeterProbeVisuals(comp);
          return;
        }

        // Case 2: Dragged and released over a valid component terminal -> CONNECTED (State 2)
        if (activeSnap && activeSnap.compId !== comp.id) {
          comp.properties.probes[probeKey].attachedTo = {
            compId: activeSnap.compId,
            termId: activeSnap.termId
          };
          comp.properties.probes[probeKey].worldX = activeSnap.pos.x;
          comp.properties.probes[probeKey].worldY = activeSnap.pos.y;
          comp.properties.probes[probeKey].isPlaced = true;
          if (activeSnap.el) {
            activeSnap.el.classList.add(`probe-attached-${probeKey}`);
          }
          this.updateMultimeterProbeVisuals(comp);
          stateManager.updateComponentProperty(comp.id, "probes", comp.properties.probes);
          stateManager.notify("simulation");
        } else {
          // Case 3: Dragged away and released in open space -> AUTO-ROLLBACK to IDLE (State 1)
          comp.properties.probes[probeKey].attachedTo = null;
          comp.properties.probes[probeKey].isPlaced = false;

          const releasePos = currentRawPos || this.workspace.screenToCanvas(getEvtCoords(upEvt).x, getEvtCoords(upEvt).y);
          const startX = releasePos ? releasePos.x : (comp.properties.probes[probeKey].worldX || comp.x);
          const startY = releasePos ? releasePos.y : (comp.properties.probes[probeKey].worldY || comp.y);

          delete comp.properties.probes[probeKey].worldX;
          delete comp.properties.probes[probeKey].worldY;

          stateManager.updateComponentProperty(comp.id, "probes", comp.properties.probes);
          this.animateProbeRollback(comp, probeKey, startX, startY);
          stateManager.notify("simulation");
        }
      };

      window.addEventListener("pointermove", onPointerMove, { passive: false });
      window.addEventListener("pointerup", onPointerUp);
      window.addEventListener("pointercancel", onPointerUp);
      window.addEventListener("mousemove", onPointerMove, { passive: false });
      window.addEventListener("mouseup", onPointerUp);
      window.addEventListener("touchmove", onPointerMove, { passive: false });
      window.addEventListener("touchend", onPointerUp);
      window.addEventListener("touchcancel", onPointerUp);
    };

    probeEl.addEventListener("pointerdown", onPointerDown, { passive: false });
    probeEl.addEventListener("mousedown", onPointerDown, { passive: false });
    probeEl.addEventListener("touchstart", onPointerDown, { passive: false });
  }

  /**
   * Smoothly animates probe from its release point back to its resting dock on the multimeter body
   */
  animateProbeRollback(comp, probeKey, fromX, fromY) {
    const startTime = performance.now();
    const duration = 280; // ms

    const step = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // Cubic ease-out
      const ease = 1 - Math.pow(1 - progress, 3);

      const defaultRelX = probeKey === "com" ? 28 : 104;
      const targetPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, defaultRelX, 245, comp.rotation);

      const curX = fromX + (targetPos.x - fromX) * ease;
      const curY = fromY + (targetPos.y - fromY) * ease;

      const probeEl = document.getElementById(`probe-${probeKey}-${comp.id}`);
      const wireEl = document.getElementById(`meter-wire-${probeKey}-${comp.id}`);
      const jackRelX = probeKey === "com" ? 48 : 84;
      const jackPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, jackRelX, 186, comp.rotation);
      const orient = this.calculateProbeOrientation({ x: Math.round(curX), y: Math.round(curY) }, jackPos, false, comp.rotation);

      if (probeEl) {
        probeEl.style.left = `${orient.targetX - 12}px`;
        probeEl.style.top = `${orient.topY}px`;
        probeEl.style.transformOrigin = "12px 52px";
        probeEl.style.transform = orient.angleDeg ? `rotate(${orient.angleDeg}deg)` : "none";
      }
      if (wireEl) {
        wireEl.setAttribute("d", this.calculateProbeWirePath(jackPos.x, jackPos.y, orient.cableX, orient.cableY));
      }

      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        comp.properties.probes[probeKey].isPlaced = false;
        comp.properties.probes[probeKey].attachedTo = null;
        delete comp.properties.probes[probeKey].worldX;
        delete comp.properties.probes[probeKey].worldY;
        this.updateMultimeterProbeVisuals(comp);
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

    const comProbe = document.getElementById(`probe-com-${comp.id}`);
    const vwmaProbe = document.getElementById(`probe-vwma-${comp.id}`);
    const comWire = document.getElementById(`meter-wire-com-${comp.id}`);
    const vwmaWire = document.getElementById(`meter-wire-vwma-${comp.id}`);

    // Compute Jack World Positions (accounting for body rotation!)
    const comJackPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, 48, 186, comp.rotation);
    const vwmaJackPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, 84, 186, comp.rotation);

    // Default resting positions on multimeter body (State 1: Idle)
    const comDefaultPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, 28, 245, comp.rotation);
    const vwmaDefaultPos = getRotatedPosition(comp.x, comp.y, comp.width, comp.height, 104, 245, comp.rotation);

    const state = stateManager.getState();

    // --- COM Probe Position ---
    let comPos = comDefaultPos;
    let isComConnected = false;
    if (comp.properties.probes.com?.attachedTo) {
      const targetPos = this.workspace?.connectionEngine?.getTerminalWorldPosition(
        comp.properties.probes.com.attachedTo.compId,
        comp.properties.probes.com.attachedTo.termId
      );
      if (targetPos) {
        comPos = targetPos;
        isComConnected = true;
        comp.properties.probes.com.worldX = targetPos.x;
        comp.properties.probes.com.worldY = targetPos.y;
      } else {
        comp.properties.probes.com.attachedTo = null;
        comp.properties.probes.com.isPlaced = false;
        delete comp.properties.probes.com.worldX;
        delete comp.properties.probes.com.worldY;
        comPos = comDefaultPos;
        isComConnected = false;
      }
    } else {
      comPos = comDefaultPos;
      isComConnected = false;
    }

    const comOrient = this.calculateProbeOrientation(comPos, comJackPos, isComConnected, comp.rotation);
    if (comProbe) {
      comProbe.style.left = `${comOrient.targetX - 12}px`;
      comProbe.style.top = `${comOrient.topY}px`;
      comProbe.style.transformOrigin = "12px 52px";
      comProbe.style.transform = comOrient.angleDeg ? `rotate(${comOrient.angleDeg}deg)` : "none";
    }
    if (comWire) {
      comWire.setAttribute("d", this.calculateProbeWirePath(comJackPos.x, comJackPos.y, comOrient.cableX, comOrient.cableY));
    }

    // --- VΩmA Probe Position ---
    let vwmaPos = vwmaDefaultPos;
    let isVwmaConnected = false;
    if (comp.properties.probes.vwma?.attachedTo) {
      const targetPos = this.workspace?.connectionEngine?.getTerminalWorldPosition(
        comp.properties.probes.vwma.attachedTo.compId,
        comp.properties.probes.vwma.attachedTo.termId
      );
      if (targetPos) {
        vwmaPos = targetPos;
        isVwmaConnected = true;
        comp.properties.probes.vwma.worldX = targetPos.x;
        comp.properties.probes.vwma.worldY = targetPos.y;
      } else {
        comp.properties.probes.vwma.attachedTo = null;
        comp.properties.probes.vwma.isPlaced = false;
        delete comp.properties.probes.vwma.worldX;
        delete comp.properties.probes.vwma.worldY;
        vwmaPos = vwmaDefaultPos;
        isVwmaConnected = false;
      }
    } else {
      vwmaPos = vwmaDefaultPos;
      isVwmaConnected = false;
    }

    const vwmaOrient = this.calculateProbeOrientation(vwmaPos, vwmaJackPos, isVwmaConnected, comp.rotation);
    if (vwmaProbe) {
      vwmaProbe.style.left = `${vwmaOrient.targetX - 12}px`;
      vwmaProbe.style.top = `${vwmaOrient.topY}px`;
      vwmaProbe.style.transformOrigin = "12px 52px";
      vwmaProbe.style.transform = vwmaOrient.angleDeg ? `rotate(${vwmaOrient.angleDeg}deg)` : "none";
    }
    if (vwmaWire) {
      vwmaWire.setAttribute("d", this.calculateProbeWirePath(vwmaJackPos.x, vwmaJackPos.y, vwmaOrient.cableX, vwmaOrient.cableY));
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
