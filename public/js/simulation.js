/**
 * FLUXUS / DTE VirtualLab — Simulation Orchestrator
 * Connects the Physics Engine (MNA Solver, Netlist, Measurement Engine) with UI, State, and Visuals.
 * Separates UI rendering from mathematical physics calculations.
 */

import { stateManager } from "./state.js";
import { NetlistBuilder } from "./physics/netlist.js";
import { MNACircuitSolver } from "./physics/solver.js";
import { MeasurementEngine } from "./physics/measurement.js";
import { PhysicsDiagnostics } from "./physics/diagnostics.js";

export class SimulationEngine {
  constructor() {
    this.metricVoltage = document.getElementById("metric-voltage");
    this.metricCurrent = document.getElementById("metric-current");
    this.metricPower = document.getElementById("metric-power");
    this.metricStatus = document.getElementById("metric-status");
    this.simSwitchBtn = document.getElementById("btn-run-sim");
    this.simSwitchText = document.getElementById("sim-switch-text");
    this.isAnalyzing = false;
  }

  init() {
    stateManager.subscribe("simulation", () => this.analyzeCircuit());
    stateManager.subscribe("connections", () => this.analyzeCircuit());
    stateManager.subscribe("components", () => this.analyzeCircuit());

    // Expose test suite globally and run verification at boot
    if (typeof window !== "undefined") {
      window.runFluxusPhysicsTests = () => PhysicsDiagnostics.runAllAutomatedTests();
      window.runDTEResistanceTests = () => PhysicsDiagnostics.runAllAutomatedTests();
      setTimeout(() => PhysicsDiagnostics.runAllAutomatedTests(), 200);
    }
  }

  /**
   * Main Circuit Analyzer — Runs on every circuit topology or state change
   */
  analyzeCircuit() {
    if (this.isAnalyzing) return;
    this.isAnalyzing = true;

    try {
      const state = stateManager.getState();
      const isRunning = state.simulation.running;
      const { components, connections } = state;

      const battery = components.find(c => c.type === "battery" || c.type === "power_supply");

      // 1. Build Electrical Netlist
      const netlist = NetlistBuilder.build(components, connections);

      // 2. Solve Circuit via MNA Engine
      let circuitResult = null;
      if (battery) {
        circuitResult = MNACircuitSolver.solve(battery, components, connections);
      }

      // 3. Always evaluate Multimeter readings (even if simulation/battery is OFF, e.g. Ohmmeter test)
      this.evaluateAllMultimeters(circuitResult, netlist);

      this.resetComponentGlow();

      if (!isRunning) {
        state.simulation.status = "STANDBY";
        this.updateMetricsUI(0, 0, 0, "STANDBY", "Standby");
        if (this.simSwitchBtn) this.simSwitchBtn.classList.remove("active");
        if (this.simSwitchText) this.simSwitchText.textContent = "OFF";
        this.updateWireVisuals("standby");
        return;
      }

      if (this.simSwitchBtn) this.simSwitchBtn.classList.add("active");
      if (this.simSwitchText) this.simSwitchText.textContent = "ON";

      if (!battery) {
        state.simulation.status = "INCOMPLETE";
        this.updateMetricsUI(0, 0, 0, "STANDBY", "No Baterai");
        this.updateWireVisuals("inactive");
        return;
      }

      const sourceVoltage = Number(battery.properties?.voltage ?? 12);

      if (circuitResult?.shortCircuit) {
        state.simulation.status = "SHORT_CIRCUIT";
        state.simulation.metrics = {
          sourceEMF: circuitResult.sourceEMF ?? sourceVoltage,
          terminalVoltage: circuitResult.terminalVoltage ?? 0,
          totalVoltage: circuitResult.terminalVoltage ?? 0,
          totalCurrent: circuitResult.totalCurrent,
          totalPower: circuitResult.totalPower,
          sourcePower: circuitResult.sourcePower,
          loadPower: circuitResult.loadPower,
          internalLoss: circuitResult.internalLoss,
          power: circuitResult.power,
          equivalentResistance: circuitResult.equivalentResistance
        };
        this.updateMetricsUI(
          circuitResult.terminalVoltage ?? 0,
          circuitResult.totalCurrent,
          circuitResult.totalPower,
          "ERROR",
          "Short Circuit",
          state.simulation.metrics
        );
        this.updateWireVisuals("short-circuit");
        return;
      }

      if (circuitResult?.overcurrent) {
        state.simulation.status = "OVERCURRENT";
        state.simulation.metrics = {
          sourceEMF: circuitResult.sourceEMF ?? sourceVoltage,
          terminalVoltage: circuitResult.terminalVoltage ?? sourceVoltage,
          totalVoltage: circuitResult.terminalVoltage ?? sourceVoltage,
          totalCurrent: circuitResult.totalCurrent,
          totalPower: circuitResult.totalPower,
          sourcePower: circuitResult.sourcePower,
          loadPower: circuitResult.loadPower,
          internalLoss: circuitResult.internalLoss,
          power: circuitResult.power,
          equivalentResistance: 0
        };
        // Update active loads (Lamps, LEDs, Motors) so conducting components render their visual states
        this.updateLoadVisuals(components, circuitResult.branchVoltages, circuitResult.branchCurrents, circuitResult.motorResults);
        this.updateMetricsUI(
          circuitResult.terminalVoltage ?? sourceVoltage,
          circuitResult.totalCurrent,
          circuitResult.totalPower,
          "WARNING",
          "LED Overcurrent",
          state.simulation.metrics
        );
        this.updateWireVisuals("active");
        return;
      }

      if (!circuitResult || circuitResult.openCircuit || circuitResult.totalCurrent < 1e-6) {
        state.simulation.status = "INCOMPLETE";
        state.simulation.metrics = {
          sourceEMF: sourceVoltage,
          terminalVoltage: sourceVoltage,
          totalVoltage: sourceVoltage,
          totalCurrent: 0,
          totalPower: 0,
          sourcePower: 0,
          loadPower: 0,
          internalLoss: 0,
          power: { source: 0, load: 0, internalLoss: 0 },
          equivalentResistance: Infinity
        };
        this.updateMetricsUI(sourceVoltage, 0, 0, "STANDBY", "Terbuka", state.simulation.metrics);
        this.updateWireVisuals("inactive");
        return;
      }

      state.simulation.status = "OK";
      state.simulation.metrics = {
        sourceEMF: circuitResult.sourceEMF ?? sourceVoltage,
        terminalVoltage: circuitResult.terminalVoltage ?? circuitResult.totalVoltage,
        totalVoltage: circuitResult.terminalVoltage ?? circuitResult.totalVoltage,
        totalCurrent: circuitResult.totalCurrent,
        totalPower: circuitResult.totalPower,
        sourcePower: circuitResult.sourcePower,
        loadPower: circuitResult.loadPower,
        internalLoss: circuitResult.internalLoss,
        power: circuitResult.power,
        equivalentResistance: circuitResult.equivalentResistance
      };

      // 4. Update Loads (Lamps, LEDs, Motors, Diodes) based on exact physical branch voltages and solver results
      this.updateLoadVisuals(components, circuitResult.branchVoltages, circuitResult.branchCurrents, circuitResult.motorResults);

      this.updateMetricsUI(
        circuitResult.terminalVoltage ?? circuitResult.totalVoltage,
        circuitResult.totalCurrent,
        circuitResult.totalPower,
        "OK",
        "Normal",
        state.simulation.metrics
      );
      this.updateWireVisuals("active");

      // Verify Power Balance Diagnostics
      const pb = PhysicsDiagnostics.verifyPowerBalance(battery, circuitResult, components);
      if (!pb.isBalanced) {
        console.warn(`[Physics Power Balance Warning] Supplied = ${pb.powerSupplied.toFixed(3)} W, Consumed = ${pb.powerConsumed.toFixed(3)} W`);
      }
    } finally {
      this.isAnalyzing = false;
    }
  }

  /**
   * Evaluates all Multimeters in workspace using MeasurementEngine
   */
  evaluateAllMultimeters(circuitResult = null, netlist = null) {
    const state = stateManager.getState();
    const { components, connections } = state;
    const meters = components.filter(c => c.type === "multimeter");

    if (!netlist) {
      netlist = NetlistBuilder.build(components, connections);
    }

    meters.forEach(m => {
      const measurement = MeasurementEngine.evaluate(m, circuitResult, components, connections, netlist.uf);
      m.properties.reading = measurement.readingText;
      if (measurement.unit !== undefined) {
        m.properties.unit = measurement.unit;
      }

      const compEl = document.getElementById(`comp-${m.id}`);
      if (compEl) {
        if (window.componentEngine) {
          window.componentEngine.updateComponentVisualProperties(compEl, m);
        } else {
          const valEl = document.getElementById(`meter-val-${m.id}`);
          if (valEl) {
            valEl.textContent = measurement.readingText;
          }
          const unitEl = document.getElementById(`meter-unit-${m.id}`);
          if (unitEl) {
            unitEl.textContent = m.properties.powerOn !== false ? (measurement.unit || "") : "";
          }
        }
      }
    });
  }

  /**
   * Update visual representations of active loads using Single Source of Truth
   */
  updateLoadVisuals(components, branchVoltages, branchCurrents, motorResults = null) {
    components.forEach(comp => {
      const compEl = document.getElementById(`comp-${comp.id}`);
      if (!compEl) return;

      const compV = branchVoltages?.get(comp.id) || 0;
      const compI = branchCurrents?.get(comp.id) || 0;

      if (comp.type === "lamp") {
        const nominalV = Number(comp.properties?.nominalVoltage || 12);
        const ratedP = Number(comp.properties?.powerRating || 20);
        
        // Single Source of Truth: Branch solved power for this specific lamp
        const solvedP = Math.abs(compV * compI);

        // Threshold: P <= 0.005 W is treated as OFF
        if (solvedP > 0.005 && compV > 0.1) {
          const powerRatio = Math.min(Math.max(solvedP / ratedP, 0), 2.0);
          // Gentle visual mapping for educational visibility (dim glow visible at low voltages/powers)
          const visualIntensity = Math.min(1.5, Math.max(0.15, Math.sqrt(powerRatio)));

          compEl.classList.add("lit");
          compEl.style.setProperty("--glow-intensity", visualIntensity.toFixed(2));
          const lampVis = compEl.querySelector(".lamp-visual");
          if (lampVis) {
            lampVis.classList.add("lit");
            lampVis.style.setProperty("--glow-intensity", visualIntensity.toFixed(2));
          }
        } else {
          compEl.classList.remove("lit");
          compEl.style.removeProperty("--glow-intensity");
          const lampVis = compEl.querySelector(".lamp-visual");
          if (lampVis) {
            lampVis.classList.remove("lit");
            lampVis.style.removeProperty("--glow-intensity");
          }
        }
      } else if (comp.type === "led") {
        const vf = Number(comp.properties?.forwardVoltage || 2.0);
        if (compI > 1e-4 && compV >= vf * 0.9) {
          compEl.classList.add("lit");
          const ledVis = compEl.querySelector(".led-visual");
          if (ledVis) ledVis.classList.add("lit");
        }
      } else if (comp.type === "motor_dc") {
        const motorData = motorResults?.get(comp.id);
        const rpm = motorData ? motorData.rpm : 0;
        const direction = motorData ? motorData.direction : "CW";
        const state = motorData ? motorData.state : (compV > 0.5 ? "RUNNING" : "OFF");

        comp.properties.currentRpm = rpm;
        comp.properties.direction = direction;

        const rpmEl = compEl.querySelector(".rpm-number");
        if (rpmEl) rpmEl.textContent = String(rpm);

        const rotor = compEl.querySelector(".motor-rotor-blades");
        if (state === "RUNNING" || state === "OVERLOAD") {
          const speedRatio = Math.max(0.05, rpm / 3000);
          const spinDuration = Math.max(0.05, (0.4 / speedRatio)).toFixed(3);

          compEl.classList.add("spinning");
          compEl.style.setProperty("--spin-speed", `${spinDuration}s`);

          if (direction === "CCW") {
            compEl.classList.add("ccw");
          } else {
            compEl.classList.remove("ccw");
          }

          if (rotor) {
            rotor.classList.add("spinning");
            if (direction === "CCW") rotor.classList.add("ccw");
            else rotor.classList.remove("ccw");
            rotor.style.setProperty("--spin-speed", `${spinDuration}s`);
          }
        } else {
          compEl.classList.remove("spinning", "ccw");
          if (rotor) rotor.classList.remove("spinning", "ccw");
        }
      }
    });
  }

  resetComponentGlow() {
    document.querySelectorAll(".workspace-component").forEach(el => {
      el.classList.remove("lit", "spinning");
      el.style.removeProperty("--glow-intensity");
      el.style.removeProperty("--spin-speed");
    });
    document.querySelectorAll(".lamp-visual").forEach(el => {
      el.classList.remove("lit");
      el.style.removeProperty("--glow-intensity");
    });
    document.querySelectorAll(".led-visual").forEach(el => {
      el.classList.remove("lit");
      el.style.removeProperty("--glow-intensity");
    });
    document.querySelectorAll(".motor-rotor-blades").forEach(el => {
      el.classList.remove("spinning");
      el.style.removeProperty("--spin-speed");
    });
  }

  updateWireVisuals(mode) {
    document.querySelectorAll(".circuit-wire").forEach(w => {
      w.classList.remove("active", "short-circuit");
      if (mode === "active") w.classList.add("active");
      if (mode === "short-circuit") w.classList.add("short-circuit");
    });
  }

  updateMetricsUI(voltage, current, power, status, statusText, details = null) {
    if (this.metricVoltage) {
      this.metricVoltage.textContent = (voltage !== null && voltage !== undefined && !isNaN(voltage))
        ? `${Number(voltage).toFixed(1)} V`
        : "—";
    }
    if (this.metricCurrent) {
      this.metricCurrent.textContent = (current !== null && current !== undefined && !isNaN(current))
        ? `${Number(current).toFixed(2)} A`
        : "—";
    }
    if (this.metricPower) {
      this.metricPower.textContent = (power !== null && power !== undefined && !isNaN(power))
        ? `${Number(power).toFixed(2)} W`
        : "—";
    }

    if (this.metricStatus) {
      this.metricStatus.textContent = statusText;
      this.metricStatus.className = "metric-status-badge";
      if (status === "OK") this.metricStatus.classList.add("status-ok");
      else if (status === "ERROR") this.metricStatus.classList.add("status-error");
      else this.metricStatus.classList.add("status-standby");
    }

    // Set informative tooltip on the metrics ribbon if detailed metrics exist
    const ribbon = document.getElementById("sim-metrics-ribbon");
    if (ribbon && details) {
      const emfStr = details.sourceEMF != null ? `${Number(details.sourceEMF).toFixed(2)} V` : "—";
      const termStr = details.terminalVoltage != null ? `${Number(details.terminalVoltage).toFixed(2)} V` : "—";
      const iStr = details.totalCurrent != null ? `${Number(details.totalCurrent).toFixed(2)} A` : "—";
      const pSrcStr = details.power?.source != null ? `${Number(details.power.source).toFixed(2)} W` : "—";
      const pLoadStr = details.power?.load != null ? `${Number(details.power.load).toFixed(2)} W` : "—";
      const pLossStr = details.power?.internalLoss != null ? `${Number(details.power.internalLoss).toFixed(2)} W` : "—";
      ribbon.title = `EMF Sumber: ${emfStr} | Tegangan Terminal: ${termStr} | Arus: ${iStr} | Daya Sumber: ${pSrcStr} | Daya Beban: ${pLoadStr} | Rugi Internal: ${pLossStr} | Status: ${statusText}`;
    }
  }
}
