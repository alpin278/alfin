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
    width: 140,
    height: 125,
    defaultProps: { mode: "V_DC", reading: "0.00 V" },
    terminals: [
      { id: "term_com", name: "COM", label: "COM (Hitam)", relX: 35, relY: 110, color: "#0f172a" },
      { id: "term_vwma", name: "VΩ", label: "VΩ (Merah)", relX: 105, relY: 110, color: "#ef4444" }
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

    stateManager.subscribe("components", () => this.syncDOM());
    stateManager.subscribe("selection", () => {
      this.updateSelectionVisuals();
      this.renderFloatingToolbar();
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
    const proto = COMPONENT_PROTOTYPES[type];
    if (!proto) return;

    const id = `${type}-${String(componentCounter++).padStart(3, "0")}`;
    const newComponent = {
      id,
      type,
      name: `${proto.name} ${componentCounter - 1}`,
      x,
      y,
      rotation: 0,
      width: proto.width,
      height: proto.height,
      properties: JSON.parse(JSON.stringify(proto.defaultProps)),
      terminals: JSON.parse(JSON.stringify(proto.terminals))
    };

    stateManager.addComponent(newComponent);
  }

  syncDOM() {
    if (!this.layer) return;

    const state = stateManager.getState();
    const existingIds = new Set();

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

    this.bindComponentDrag(el, comp);

    el.addEventListener("dblclick", (e) => {
      e.stopPropagation();
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
    });

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
      return `
        <div class="multimeter-visual">
          <div class="meter-comp-lcd">
            <span class="meter-comp-val" id="meter-val-${comp.id}">${comp.properties.reading || '0.00'}</span>
            <span class="meter-comp-unit" id="meter-unit-${comp.id}">${comp.properties.mode === 'OHM' ? 'Ω' : (comp.properties.mode === 'A_DC' ? 'A' : 'V')}</span>
          </div>
          <div class="meter-comp-dial">
            <div class="dial-knob">
              <div class="dial-notch"></div>
            </div>
          </div>
          <div class="meter-comp-jacks">
            <div class="meter-jack-socket jack-black">
              <span class="jack-label">COM</span>
              <div class="probe-wire-lead probe-black-lead">
                <div class="probe-pin-handle handle-black"></div>
              </div>
            </div>
            <div class="meter-jack-socket jack-red">
              <span class="jack-label">VΩmA</span>
              <div class="probe-wire-lead probe-red-lead">
                <div class="probe-pin-handle handle-red"></div>
              </div>
            </div>
          </div>
          <div class="multimeter-label">Multimeter Digital</div>
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
      const dial = el.querySelector(".meter-comp-dial");
      const mode = comp.properties.mode || "V_DC";

      if (valEl && comp.properties.reading) valEl.textContent = comp.properties.reading;
      if (unitEl) unitEl.textContent = mode === "OHM" ? "Ω" : (mode === "A_DC" ? "A" : "V");
      if (dial) {
        let angle = 0;
        if (mode === "OHM") angle = 120;
        else if (mode === "A_DC") angle = 240;
        dial.style.transform = `rotate(${angle}deg)`;
        dial.style.transition = "transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1)";
      }
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
      const lbl = el.querySelector(`#diode-lbl-${comp.id}`);
      const badge = el.querySelector(`#diode-bias-${comp.id}`);
      const statusText = el.querySelector(`#diode-bias-${comp.id} .bias-status-text`);
      const vis = el.querySelector(`#diode-vis-${comp.id}`);

      if (lbl) lbl.textContent = `Vf: ${comp.properties.forwardVoltage || 0.7}V`;
      const state = comp.properties.state || "STANDBY";
      
      if (vis) {
        vis.classList.remove("forward-bias", "reverse-bias");
        if (state === "FORWARD_BIAS") vis.classList.add("forward-bias");
        if (state === "REVERSE_BIAS") vis.classList.add("reverse-bias");
      }

      if (badge && statusText) {
        badge.className = `diode-bias-badge state-${state.toLowerCase()}`;
        if (state === "FORWARD_BIAS") {
          statusText.textContent = "BIAS MAJU (ON)";
        } else if (state === "REVERSE_BIAS") {
          statusText.textContent = "BIAS MUNDUR (BLOK)";
        } else {
          statusText.textContent = "STANDBY";
        }
      }
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
      if (valEl && comp.properties.reading) valEl.textContent = comp.properties.reading;
      if (unitEl) unitEl.textContent = comp.properties.mode === "OHM" ? "Ω" : (comp.properties.mode === "A_DC" ? "A" : "V");
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
    }
  }

  bindComponentDrag(el, comp) {
    let startX = 0, startY = 0;
    let compStartX = 0, compStartY = 0;
    let hasMoved = false;

    const onPointerDown = (e) => {
      // If we are currently drawing a wire, check if tapping this component near any of its terminals to complete connection!
      if (this.workspace?.connectionEngine?.isConnecting) {
        const rawPos = this.workspace.screenToCanvas(e.clientX, e.clientY);
        const nearTerm = this.workspace.connectionEngine.findNearestTerminalSnap(rawPos.x, rawPos.y, 45);
        if (nearTerm) {
          e.stopPropagation();
          e.preventDefault();
          this.workspace.connectionEngine.handleTerminalClick(nearTerm.compId, nearTerm.termId, nearTerm.el);
          return;
        }
      }

      // NEVER intercept if clicking a terminal node or its smart numbering badge
      if (e.target.closest(".terminal-node") || e.target.closest(".smart-number-badge")) {
        return;
      }

      e.stopPropagation();
      stateManager.setSelection("component", comp.id);

      startX = e.clientX;
      startY = e.clientY;
      compStartX = comp.x;
      compStartY = comp.y;
      hasMoved = false;

      const onPointerMove = (moveEvent) => {
        const deltaX = (moveEvent.clientX - startX) / this.workspace.zoom;
        const deltaY = (moveEvent.clientY - startY) / this.workspace.zoom;

        if (Math.abs(deltaX) > 3 || Math.abs(deltaY) > 3) {
          hasMoved = true;
        }

        if (hasMoved) {
          const newX = Math.round(compStartX + deltaX);
          const newY = Math.round(compStartY + deltaY);

          el.style.left = `${newX}px`;
          el.style.top = `${newY}px`;
          comp.x = newX;
          comp.y = newY;

          stateManager.notify("components_moving");
        }
      };

      const onPointerUp = () => {
        window.removeEventListener("pointermove", onPointerMove);
        window.removeEventListener("pointerup", onPointerUp);

        if (hasMoved) {
          stateManager.updateComponentPosition(comp.id, comp.x, comp.y);
        } else {
          if (comp.type === "switch_spst") {
            const nextState = !comp.properties.isClosed;
            comp.properties.isClosed = nextState;
            this.updateComponentVisualProperties(el, comp);
            stateManager.updateComponentProperty(comp.id, "isClosed", nextState);
          } else if (comp.type === "multimeter") {
            // Direct Click / Tap to Cycle Multimeter Modes: V_DC -> OHM -> A_DC
            const modes = ["V_DC", "OHM", "A_DC"];
            const curr = comp.properties.mode || "V_DC";
            const nextMode = modes[(modes.indexOf(curr) + 1) % modes.length];
            comp.properties.mode = nextMode;
            this.updateComponentVisualProperties(el, comp);
            stateManager.updateComponentProperty(comp.id, "mode", nextMode);
            stateManager.notify("simulation");
          }
        }
      };

      window.addEventListener("pointermove", onPointerMove);
      window.addEventListener("pointerup", onPointerUp);
    };

    el.addEventListener("pointerdown", onPointerDown);
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

    const tb = document.createElement("div");
    tb.className = "component-floating-toolbar";
    tb.style.left = `${comp.x + comp.width / 2}px`;
    tb.style.top = `${comp.y - 12}px`;

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
