/**
 * FLUXUS / DTE VirtualLab — Multimeter & Electrical Measurement Physics Engine
 * Models real instrument physics: input impedance loading (10 MΩ), shunt resistances (1 mΩ),
 * signed probe polarity (+/-), and safety checks for resistance measurements on live circuits.
 */

import { MNACircuitSolver } from "./solver.js";

export class MeasurementEngine {
  /**
   * Evaluates measurement for a specific multimeter instance
   * 
   * @param {Object} meterComp - Multimeter component
   * @param {Object} circuitResult - Result from MNACircuitSolver
   * @param {Array} components - All components
   * @param {Array} connections - All wire connections
   * @param {Object} uf - UnionFind instance
   * @returns {Object} { readingText, rawValue, unit, isWarning, warningMessage, polarity }
   */
  static evaluate(meterComp, circuitResult, components, connections, uf) {
    const mode = meterComp.properties?.mode || "V_DC";

    // 0. POWER CHECK: If meter is turned OFF, display OFF and do not measure
    if (meterComp.properties?.powerOn === false) {
      return {
        readingText: "OFF",
        rawValue: 0,
        unit: "",
        isWarning: false,
        warningMessage: null,
        powerOff: true
      };
    }

    // 1. Identify effective terminal connection for Red Probe (VΩmA)
    let termVwma = `${meterComp.id}:term_vwma`;
    let isVwmaAttached = false;
    if (meterComp.properties?.probes?.vwma?.attachedTo) {
      termVwma = `${meterComp.properties.probes.vwma.attachedTo.compId}:${meterComp.properties.probes.vwma.attachedTo.termId}`;
      isVwmaAttached = true;
    }

    // 2. Identify effective terminal connection for Black Probe (COM)
    let termCom = `${meterComp.id}:term_com`;
    let isComAttached = false;
    if (meterComp.properties?.probes?.com?.attachedTo) {
      termCom = `${meterComp.properties.probes.com.attachedTo.compId}:${meterComp.properties.probes.com.attachedTo.termId}`;
      isComAttached = true;
    }

    // 3. Check direct wire connections if probes are not attached interactively
    const isVwmaWired = connections.some(c => (c.from?.componentId === meterComp.id && c.from?.terminalId === "term_vwma") || (c.to?.componentId === meterComp.id && c.to?.terminalId === "term_vwma"));
    const isComWired = connections.some(c => (c.from?.componentId === meterComp.id && c.from?.terminalId === "term_com") || (c.to?.componentId === meterComp.id && c.to?.terminalId === "term_com"));

    const hasValidVwma = isVwmaAttached || isVwmaWired;
    const hasValidCom = isComAttached || isComWired;

    // Helper to extract held display when HOLD is active
    const getHeld = () => {
      if (!meterComp.properties?.holdEnabled) return null;
      const h = meterComp.properties?.heldDisplay || meterComp.properties?.heldReading;
      if (!h) return null;
      if (typeof h === "string") return { text: h, unit: "" };
      return { text: h.text, unit: h.unit || "" };
    };

    const held = getHeld();

    // Default unprobed state
    if (!hasValidVwma || !hasValidCom) {
      if (held) {
        return {
          readingText: held.text,
          rawValue: 0,
          unit: held.unit || (mode.startsWith("A") ? "A" : (mode === "OHM" ? "Ω" : "V")),
          isWarning: false,
          warningMessage: null
        };
      }
      if (mode === "OHM" || mode === "DIODE" || mode === "CONT") {
        return { readingText: "O.L", rawValue: Infinity, unit: "Ω", isWarning: false, warningMessage: "Probes Open" };
      }
      return { readingText: "0.00", rawValue: 0, unit: mode.startsWith("A") ? "A" : "V", isWarning: false, warningMessage: "Probes Floating" };
    }

    const netVwma = uf.find(termVwma);
    const netCom = uf.find(termCom);

    // --- MODE 1: VOLTAGE (V_DC / V_AC) ---
    if (mode === "V_DC" || mode === "V_AC") {
      let vDiff = 0;
      if (netVwma !== netCom) {
        const nodeVoltages = circuitResult?.nodeVoltages || new Map();
        const vRed = nodeVoltages.get(netVwma) ?? 0;
        const vBlack = nodeVoltages.get(netCom) ?? 0;
        vDiff = vRed - vBlack;
      }

      const rawValue = vDiff;
      let formatted = (Math.abs(vDiff) < 1e-4) ? "0.00" : vDiff.toFixed(2);
      let unit = "V";

      if (mode === "V_AC") {
        // True RMS of pure DC in AC coupled mode is 0.00 VAC
        formatted = "0.00";
      }

      // Range check
      const rangeIndex = meterComp.properties?.rangeIndex || 0;
      const rangeMode = meterComp.properties?.rangeMode || "AUTO";
      if (rangeMode === "MANUAL" && rangeIndex > 0) {
        const ranges = [
          { label: "AUTO", max: Infinity },
          { label: "600mV", max: 0.6, decimals: 3, unit: "mV", scale: 1000 },
          { label: "6V", max: 6.0, decimals: 3 },
          { label: "60V", max: 60.0, decimals: 2 },
          { label: "600V", max: 600.0, decimals: 1 }
        ];
        const range = ranges[rangeIndex] || ranges[0];
        if (Math.abs(rawValue) > range.max) {
          formatted = "OL";
        } else {
          const scaledVal = range.scale ? rawValue * range.scale : rawValue;
          formatted = (Math.abs(scaledVal) < 1e-5) ? (0).toFixed(range.decimals) : scaledVal.toFixed(range.decimals);
          if (range.unit) unit = range.unit;
        }
      }

      // HOLD override (freezes LCD display text only, rawValue stays realtime)
      if (held) {
        formatted = held.text;
        if (held.unit) unit = held.unit;
      }

      return {
        readingText: formatted,
        rawValue,
        unit,
        polarity: vDiff >= 0 ? "+" : "-",
        isWarning: false,
        warningMessage: null
      };
    }

    // --- MODE 2: CURRENT (A_DC / A_AC) ---
    if (mode === "A_DC" || mode === "A_AC") {
      let currentVal = 0;
      if (netVwma !== netCom) {
        if (circuitResult?.ammeterCurrents && circuitResult.ammeterCurrents.has(meterComp.id)) {
          currentVal = circuitResult.ammeterCurrents.get(meterComp.id) || 0;
        } else if (circuitResult?.totalCurrent !== undefined && circuitResult.totalCurrent !== null) {
          currentVal = circuitResult.totalCurrent;
        }
      }

      const rawValue = currentVal;
      const currentMag = Math.abs(currentVal);
      const isShortCircuit = circuitResult?.shortCircuit === true;
      const isOvercurrent = currentMag > 10.0 || isShortCircuit;

      let formatted = "0.00";
      let unit = "A";
      let warningMsg = null;

      if (isShortCircuit || currentMag > 10.0) {
        formatted = "OVERLOAD";
        warningMsg = "⚠️ Short Circuit Risk — Ammeter connected in parallel! Ammeter must be connected in series with load.";
      } else if (mode === "A_AC") {
        formatted = "0.000";
      } else if (currentMag < 1e-4) {
        formatted = "0.000";
      } else if (currentMag >= 1.0) {
        formatted = currentVal.toFixed(2);
      } else {
        formatted = currentVal.toFixed(3);
      }

      // Range check
      const rangeIndex = meterComp.properties?.rangeIndex || 0;
      const rangeMode = meterComp.properties?.rangeMode || "AUTO";
      if (rangeMode === "MANUAL" && rangeIndex > 0) {
        const ranges = [
          { label: "AUTO", max: Infinity },
          { label: "60mA", max: 0.06, decimals: 2, unit: "mA", scale: 1000 },
          { label: "600mA", max: 0.6, decimals: 1, unit: "mA", scale: 1000 },
          { label: "10A", max: 10.0, decimals: 3 }
        ];
        const range = ranges[rangeIndex] || ranges[0];
        if (currentMag > range.max) {
          formatted = "OL";
        } else {
          const scaledVal = range.scale ? currentVal * range.scale : currentVal;
          formatted = (Math.abs(scaledVal) < 1e-5) ? (0).toFixed(range.decimals) : scaledVal.toFixed(range.decimals);
          if (range.unit) unit = range.unit;
        }
      }

      // HOLD override
      if (held) {
        formatted = held.text;
        if (held.unit) unit = held.unit;
      }

      return {
        readingText: formatted,
        rawValue,
        currentMagnitude: currentMag,
        polarity: currentVal >= 0 ? "+" : "-",
        unit,
        isWarning: isOvercurrent,
        warningMessage: warningMsg
      };
    }

    // --- MODE 3: RESISTANCE (OHM) ---
    if (mode === "OHM") {
      // Safety Check: Check if measured nodes have active live voltage
      const nodeVoltages = circuitResult?.nodeVoltages || new Map();
      const vRed = nodeVoltages.get(netVwma) ?? 0;
      const vBlack = nodeVoltages.get(netCom) ?? 0;
      const liveVoltage = Math.abs(vRed - vBlack);

      if (liveVoltage > 0.1 && (circuitResult?.totalCurrent || 0) > 0.001) {
        return {
          readingText: "ERR",
          rawValue: 0,
          unit: "LIVE",
          isWarning: true,
          warningMessage: "⚠️ Matikan sumber daya sebelum mengukur resistansi!"
        };
      }

      if (netVwma === netCom) {
        return { readingText: "0.00", rawValue: 0, unit: "Ω", isWarning: false, warningMessage: "Direct Short" };
      }

      const req = MNACircuitSolver.calculateEquivalentResistance(termVwma, termCom, components, connections);

      if (!isFinite(req) || req > 1e7) {
        return { readingText: "O.L", rawValue: Infinity, unit: "Ω", isWarning: false, warningMessage: "Open Circuit" };
      }

      let formatted = "0.00";
      let unit = "Ω";

      const rangeIndex = meterComp.properties?.rangeIndex || 0;
      const rangeMode = meterComp.properties?.rangeMode || "AUTO";
      if (rangeMode === "MANUAL" && rangeIndex > 0) {
        const ranges = [
          { label: "AUTO", max: Infinity },
          { label: "600Ω", max: 600, decimals: 1 },
          { label: "6kΩ", max: 6000, decimals: 3, unit: "kΩ", scale: 0.001 },
          { label: "60kΩ", max: 60000, decimals: 2, unit: "kΩ", scale: 0.001 },
          { label: "600kΩ", max: 600000, decimals: 1, unit: "kΩ", scale: 0.001 },
          { label: "6MΩ", max: 6000000, decimals: 3, unit: "MΩ", scale: 0.000001 }
        ];
        const range = ranges[rangeIndex] || ranges[0];
        if (req > range.max) {
          formatted = "OL";
        } else {
          const scaledVal = range.scale ? req * range.scale : req;
          formatted = scaledVal.toFixed(range.decimals || 1);
          if (range.unit) unit = range.unit;
        }
      } else {
        if (req < 0.05) {
          formatted = "0.00";
        } else if (req < 1000) {
          formatted = req % 1 === 0 ? req.toFixed(1) : req.toFixed(2);
          if (formatted.endsWith(".00")) formatted = req.toFixed(1);
        } else if (req < 1e6) {
          formatted = (req / 1000).toFixed(2);
          unit = "kΩ";
        } else {
          formatted = (req / 1e6).toFixed(2);
          unit = "MΩ";
        }
      }

      // HOLD override
      if (held) {
        formatted = held.text;
        if (held.unit) unit = held.unit;
      }

      return { readingText: formatted.trim(), rawValue: req, unit, isWarning: false, warningMessage: null };
    }

    // --- MODE 4: CONTINUITY (CONT) ---
    if (mode === "CONT") {
      const req = MNACircuitSolver.calculateEquivalentResistance(termVwma, termCom, components, connections);
      const isContinuous = isFinite(req) && req < 50;

      return {
        readingText: isContinuous ? req.toFixed(1) : "O.L",
        rawValue: req,
        unit: "Ω",
        isContinuous,
        isWarning: false,
        warningMessage: isContinuous ? "🔔 CONTINUITY OK (BUZZER)" : "NO CONTINUITY"
      };
    }

    // --- MODE 5: DIODE TEST ---
    if (mode === "DIODE") {
      const req = MNACircuitSolver.calculateEquivalentResistance(termVwma, termCom, components, connections);
      if (req < 100) {
        return { readingText: "0.68", rawValue: 0.68, unit: "V", isWarning: false, warningMessage: "Diode Forward Bias" };
      }
      return { readingText: "O.L", rawValue: Infinity, unit: "V", isWarning: false, warningMessage: "Diode Reverse Bias / Open" };
    }

    return { readingText: "0.00", rawValue: 0, unit: "V", isWarning: false, warningMessage: null };
  }
}
