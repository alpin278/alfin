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

    // Default unprobed state
    if (!hasValidVwma || !hasValidCom) {
      if (mode === "OHM" || mode === "DIODE" || mode === "CONT") {
        return { readingText: "O.L", rawValue: Infinity, unit: "Ω", isWarning: false, warningMessage: "Probes Open" };
      }
      return { readingText: "0.00", rawValue: 0, unit: mode === "A_DC" ? "A" : "V", isWarning: false, warningMessage: "Probes Floating" };
    }

    const netVwma = uf.find(termVwma);
    const netCom = uf.find(termCom);

    // --- MODE 1: DC VOLTAGE (V_DC) ---
    if (mode === "V_DC") {
      if (netVwma === netCom) {
        return { readingText: "0.00", rawValue: 0, unit: "V", isWarning: false, warningMessage: "Same Node" };
      }

      const nodeVoltages = circuitResult?.nodeVoltages || new Map();
      const vRed = nodeVoltages.get(netVwma) ?? 0;
      const vBlack = nodeVoltages.get(netCom) ?? 0;

      // Signed potential difference: V_measured = V_red - V_black
      const vDiff = vRed - vBlack;
      const rawValue = vDiff;
      const formatted = (Math.abs(vDiff) < 1e-4) ? "0.00" : vDiff.toFixed(2);

      return {
        readingText: formatted,
        rawValue,
        unit: "V",
        polarity: vDiff >= 0 ? "+" : "-",
        isWarning: false,
        warningMessage: null
      };
    }

    // --- MODE 2: DC CURRENT (A_DC) ---
    if (mode === "A_DC") {
      if (netVwma === netCom) {
        return { readingText: "0.00", rawValue: 0, unit: "A", isWarning: false, warningMessage: "Shunted Probes" };
      }

      let currentVal = 0;
      if (circuitResult?.ammeterCurrents && circuitResult.ammeterCurrents.has(meterComp.id)) {
        currentVal = circuitResult.ammeterCurrents.get(meterComp.id) || 0;
      } else if (circuitResult?.totalCurrent) {
        currentVal = circuitResult.totalCurrent;
      }

      const isOvercurrent = currentVal > 10.0;
      let formatted = "0.00";
      if (currentVal >= 10.0) formatted = currentVal.toFixed(2);
      else if (currentVal >= 1.0) formatted = currentVal.toFixed(2);
      else if (currentVal > 0.0001) formatted = currentVal.toFixed(3);

      return {
        readingText: formatted,
        rawValue: currentVal,
        unit: "A",
        isWarning: isOvercurrent,
        warningMessage: isOvercurrent ? "⚠️ OVERCURRENT / KORSLET" : null
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
          readingText: "ERR: LIVE",
          rawValue: 0,
          unit: "Ω",
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

      return { readingText: `${formatted} ${unit === "Ω" ? "" : unit}`, rawValue: req, unit, isWarning: false, warningMessage: null };
    }

    // --- MODE 4: CONTINUITY (CONT) ---
    if (mode === "CONT") {
      const req = MNACircuitSolver.calculateEquivalentResistance(termVwma, termCom, components, connections);
      const isContinuous = isFinite(req) && req < 50; // Threshold 50 ohms

      return {
        readingText: isContinuous ? `${req.toFixed(1)} Ω` : "O.L",
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
        return { readingText: "0.68 V", rawValue: 0.68, unit: "V", isWarning: false, warningMessage: "Diode Forward Bias" };
      }
      return { readingText: "O.L", rawValue: Infinity, unit: "V", isWarning: false, warningMessage: "Diode Reverse Bias / Open" };
    }

    return { readingText: "0.00", rawValue: 0, unit: "V", isWarning: false, warningMessage: null };
  }
}
