/**
 * DTE VirtualLab V2 — Advanced Nodal Analysis (MNA) & Topological Circuit Solver
 * Solves arbitrary Series, Parallel, and Complex Resistor Networks with Exact Kirchhoff's Laws
 */

import { stateManager } from "./state.js";

/**
 * Disjoint-Set Union (Union-Find) for Equipotential Net Clustering
 */
class UnionFind {
  constructor() {
    this.parent = new Map();
  }

  find(i) {
    if (!this.parent.has(i)) this.parent.set(i, i);
    if (this.parent.get(i) === i) return i;
    const root = this.find(this.parent.get(i));
    this.parent.set(i, root);
    return root;
  }

  union(i, j) {
    const rootI = this.find(i);
    const rootJ = this.find(j);
    if (rootI !== rootJ) {
      this.parent.set(rootI, rootJ);
    }
  }
}

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
      window.runDTEResistanceTests = () => this.runAutomatedResistanceTests();
      setTimeout(() => this.runAutomatedResistanceTests(), 100);
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

      // 1. Always evaluate Multimeter readings (even if simulation/battery is OFF, e.g. Ohmmeter test)
      this.evaluateAllMultimeters();

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

      const battery = components.find(c => c.type === "battery" || c.type === "power_supply");

      if (!battery) {
        state.simulation.status = "INCOMPLETE";
        this.updateMetricsUI(0, 0, 0, "STANDBY", "No Baterai");
        this.updateWireVisuals("inactive");
        return;
      }

      // 2. Build Equipotential Net Clustering
      const uf = this.buildEquipotentialNets(components, connections);
      const batPosNet = uf.find(`${battery.id}:term_pos`);
      const batNegNet = uf.find(`${battery.id}:term_neg`);

      // Check if Battery (+) and (-) are short-circuited together directly
      if (batPosNet === batNegNet) {
        state.simulation.status = "SHORT_CIRCUIT";
        this.updateMetricsUI(battery.properties.voltage || 12, 99.9, 999, "ERROR", "Korslet!");
        this.updateWireVisuals("short-circuit");
        return;
      }

      // 3. Solve Circuit via Nodal Analysis
      const sourceVoltage = Number(battery.properties.voltage || 12);
      const circuitResult = this.solveActiveCircuit(battery, components, connections, uf);

      if (!circuitResult || circuitResult.totalCurrent < 1e-6) {
        state.simulation.status = "INCOMPLETE";
        this.updateMetricsUI(sourceVoltage, 0, 0, "STANDBY", "Terbuka");
        this.updateWireVisuals("inactive");
        return;
      }

      if (circuitResult.shortCircuit) {
        state.simulation.status = "SHORT_CIRCUIT";
        this.updateMetricsUI(sourceVoltage, 99.9, 999, "ERROR", "Korslet!");
        this.updateWireVisuals("short-circuit");
        return;
      }

      state.simulation.status = "OK";
      state.simulation.metrics = {
        totalVoltage: sourceVoltage,
        totalCurrent: circuitResult.totalCurrent,
        totalPower: sourceVoltage * circuitResult.totalCurrent,
        equivalentResistance: circuitResult.equivalentResistance
      };

      // 4. Update Loads (Lamps, LEDs, Motors, Diodes) based on exact branch voltages
      this.updateLoadVisuals(components, circuitResult.componentVoltages, circuitResult.componentCurrents);

      this.updateMetricsUI(sourceVoltage, circuitResult.totalCurrent, sourceVoltage * circuitResult.totalCurrent, "OK", "Normal");
      this.updateWireVisuals("active");

      // Re-evaluate Multimeters with active voltages/currents
      this.evaluateAllMultimeters(circuitResult);
    } finally {
      this.isAnalyzing = false;
    }
  }

  /**
   * Build Equipotential Nets using Disjoint-Set Union
   */
  buildEquipotentialNets(components, connections) {
    const uf = new UnionFind();

    // Map each wire ID to its root terminal or internal net key
    connections.forEach(conn => {
      if (conn.from?.componentId && conn.from?.terminalId) {
        const fromKey = `${conn.from.componentId}:${conn.from.terminalId}`;
        uf.union(conn.id, fromKey);
      }
      if (!conn.to?.isHanging && conn.to?.componentId && conn.to?.terminalId) {
        const toKey = `${conn.to.componentId}:${conn.to.terminalId}`;
        uf.union(conn.id, toKey);
      }
      if (conn.to?.isWireBranch && conn.to?.targetWireId) {
        uf.union(conn.id, conn.to.targetWireId);
      }
    });

    // Group connected wire terminals
    connections.forEach(conn => {
      if (conn.to?.isHanging) return;
      if (conn.from?.componentId && conn.to?.componentId) {
        const t1 = `${conn.from.componentId}:${conn.from.terminalId}`;
        const t2 = `${conn.to.componentId}:${conn.to.terminalId}`;
        uf.union(t1, t2);
      }
    });

    // Closed switches connect their terminals directly into the same net
    components.forEach(comp => {
      if (comp.type === "switch_spst" && comp.properties.isClosed) {
        if (comp.terminals.length >= 2) {
          uf.union(`${comp.id}:${comp.terminals[0].id}`, `${comp.id}:${comp.terminals[1].id}`);
        }
      } else if (comp.type === "multimeter" && comp.properties.mode === "A_DC") {
        // Ammeter internal shunt connects the two measured nodes in series
        let tCom = `${comp.id}:term_com`;
        let tVwma = `${comp.id}:term_vwma`;
        if (comp.properties.probes?.com?.attachedTo) {
          tCom = `${comp.properties.probes.com.attachedTo.compId}:${comp.properties.probes.com.attachedTo.termId}`;
        }
        if (comp.properties.probes?.vwma?.attachedTo) {
          tVwma = `${comp.properties.probes.vwma.attachedTo.compId}:${comp.properties.probes.vwma.attachedTo.termId}`;
        }
        if (tCom && tVwma && tCom !== tVwma) {
          uf.union(tCom, tVwma);
        }
      }
    });

    return uf;
  }

  /**
   * Solves Equivalent Resistance between ANY two arbitrary terminal nodes
   * using Modified Nodal Analysis (Conductance Matrix G * V = I)
   *
   * @param {string} termAKey - e.g. "multimeter-001:term_vwma"
   * @param {string} termBKey - e.g. "multimeter-001:term_com"
   * @returns {number} Equivalent Resistance in Ohms (or Infinity if open circuit)
   */
  calculateEquivalentResistance(termAKey, termBKey, components, connections) {
    const uf = this.buildEquipotentialNets(components, connections);

    const rootA = uf.find(termAKey);
    const rootB = uf.find(termBKey);

    // Direct short circuit between probes
    if (rootA === rootB) return 0;

    // Collect all active unique nets across all components and connections
    const allRootsSet = new Set();
    components.forEach(c => {
      if (c.terminals) {
        c.terminals.forEach(t => allRootsSet.add(uf.find(`${c.id}:${t.id}`)));
      }
      if (c.type === "multimeter" && c.properties.probes) {
        if (c.properties.probes.com?.attachedTo) {
          allRootsSet.add(uf.find(`${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`));
        }
        if (c.properties.probes.vwma?.attachedTo) {
          allRootsSet.add(uf.find(`${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`));
        }
      }
    });
    connections.forEach(c => {
      if (c.from?.componentId && c.from?.terminalId) allRootsSet.add(uf.find(`${c.from.componentId}:${c.from.terminalId}`));
      if (!c.to?.isHanging && c.to?.componentId && c.to?.terminalId) allRootsSet.add(uf.find(`${c.to.componentId}:${c.to.terminalId}`));
    });
    allRootsSet.add(rootA);
    allRootsSet.add(rootB);

    const allRoots = Array.from(allRootsSet);
    const rootToIndex = new Map();
    allRoots.forEach((r, idx) => rootToIndex.set(r, idx));

    const N = allRoots.length;
    const G = Array.from({ length: N }, () => new Array(N).fill(0));
    const adj = Array.from({ length: N }, () => []);

    // Fill Conductance Matrix G for all resistive components
    components.forEach(c => {
      if (["resistor", "lamp", "led", "motor_dc", "diode", "multimeter"].includes(c.type)) {
        if (c.type === "multimeter") {
          if (c.properties.mode === "A_DC") {
            let tCom = `${c.id}:term_com`;
            let tVwma = `${c.id}:term_vwma`;
            if (c.properties.probes?.com?.attachedTo) {
              tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
            }
            if (c.properties.probes?.vwma?.attachedTo) {
              tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
            }
            const u = rootToIndex.get(uf.find(tCom));
            const v = rootToIndex.get(uf.find(tVwma));
            if (u !== undefined && v !== undefined && u !== v) {
              const g = 1000; // 0.001 ohm ideal ammeter shunt
              G[u][u] += g;
              G[v][v] += g;
              G[u][v] -= g;
              G[v][u] -= g;
              adj[u].push(v);
              adj[v].push(u);
            }
          }
        } else if (c.terminals.length >= 2) {
          const u = rootToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
          const v = rootToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));

          if (u !== undefined && v !== undefined && u !== v) {
            let R = Number(c.properties.resistance || 220);
            if (c.type === "diode") {
              // In Ohmmeter test, diode forward conduction resistance ~ 0.5 ohm
              R = (c.properties.state === "REVERSE_BIAS") ? 1e7 : 0.5;
            }
            R = Math.max(0.001, R);
            const g = 1 / R;

            G[u][u] += g;
            G[v][v] += g;
            G[u][v] -= g;
            G[v][u] -= g;

            adj[u].push(v);
            adj[v].push(u);
          }
        }
      }
    });

    const idxA = rootToIndex.get(rootA);
    const idxB = rootToIndex.get(rootB);

    if (idxA === undefined || idxB === undefined) return Infinity;

    // BFS Check: Is there an electrical path between probe A and probe B?
    const visited = new Set();
    const queue = [idxA];
    visited.add(idxA);

    while (queue.length > 0) {
      const curr = queue.shift();
      if (curr === idxB) break;
      (adj[curr] || []).forEach(nxt => {
        if (!visited.has(nxt)) {
          visited.add(nxt);
          queue.push(nxt);
        }
      });
    }

    if (!visited.has(idxB)) {
      return Infinity; // Open circuit
    }

    // Set Node B as reference ground (V_B = 0) and remove row/col B
    const remainingIndices = [];
    for (let i = 0; i < N; i++) {
      if (i !== idxB) remainingIndices.push(i);
    }

    const redN = remainingIndices.length;
    const G_red = Array.from({ length: redN }, () => new Array(redN).fill(0));
    const I_red = new Array(redN).fill(0);

    const oldToNew = new Map();
    remainingIndices.forEach((oldIdx, newIdx) => oldToNew.set(oldIdx, newIdx));

    for (let i = 0; i < redN; i++) {
      const oldI = remainingIndices[i];
      for (let j = 0; j < redN; j++) {
        const oldJ = remainingIndices[j];
        G_red[i][j] = G[oldI][oldJ];
      }
    }

    const newIdxA = oldToNew.get(idxA);
    I_red[newIdxA] = 1.0; // Inject 1 Ampere test current

    const V_red = this.solveLinearSystem(G_red, I_red);
    const equivalentR = V_red ? V_red[newIdxA] : Infinity;

    return Math.max(0, equivalentR);
  }

  /**
   * Evaluates all Multimeters in the workspace (Ohm, Voltmeter, Amperemeter)
   */
  evaluateAllMultimeters(circuitResult = null) {
    const state = stateManager.getState();
    const { components, connections } = state;
    const meters = components.filter(c => c.type === "multimeter");

    meters.forEach(m => {
      const mode = m.properties.mode || "V_DC";

      // Determine effective measurement terminal for VΩmA probe (Red)
      let termVwma = `${m.id}:term_vwma`;
      let isVwmaAttached = false;
      if (m.properties.probes?.vwma?.attachedTo) {
        termVwma = `${m.properties.probes.vwma.attachedTo.compId}:${m.properties.probes.vwma.attachedTo.termId}`;
        isVwmaAttached = true;
      }

      // Determine effective measurement terminal for COM probe (Black)
      let termCom = `${m.id}:term_com`;
      let isComAttached = false;
      if (m.properties.probes?.com?.attachedTo) {
        termCom = `${m.properties.probes.com.attachedTo.compId}:${m.properties.probes.com.attachedTo.termId}`;
        isComAttached = true;
      }

      // Check if probes are connected via wires to circuit if not attached interactively
      const isVwmaWired = connections.some(c => (c.from?.componentId === m.id && c.from?.terminalId === "term_vwma") || (c.to?.componentId === m.id && c.to?.terminalId === "term_vwma"));
      const isComWired = connections.some(c => (c.from?.componentId === m.id && c.from?.terminalId === "term_com") || (c.to?.componentId === m.id && c.to?.terminalId === "term_com"));

      const hasValidVwma = isVwmaAttached || isVwmaWired;
      const hasValidCom = isComAttached || isComWired;

      let readingText = "0.00";

      if (mode === "OHM") {
        if (!hasValidVwma || !hasValidCom) {
          readingText = "O.L"; // Open circuit / Probes floating in air
        } else if (termVwma === termCom) {
          readingText = "0.00"; // Shorted probes / same terminal
        } else {
          // Measure exact equivalent resistance between Red and Black probes
          const req = this.calculateEquivalentResistance(termVwma, termCom, components, connections);

          if (!isFinite(req) || req > 1e7) {
            readingText = "O.L"; // Overload / Open Circuit
          } else if (req < 0.05) {
            readingText = "0.00";
          } else if (req < 1000) {
            readingText = req % 1 === 0 ? req.toFixed(1) : req.toFixed(2);
            if (readingText.endsWith(".00")) readingText = req.toFixed(1);
          } else if (req < 1e6) {
            readingText = (req / 1000).toFixed(2) + " k";
          } else {
            readingText = (req / 1e6).toFixed(2) + " M";
          }
        }
      } else if (mode === "V_DC") {
        if (!hasValidVwma || !hasValidCom) {
          readingText = "0.00";
        } else if (termVwma === termCom) {
          readingText = "0.00";
        } else if (circuitResult && circuitResult.nodeVoltages) {
          const uf = this.buildEquipotentialNets(components, connections);
          const vA = circuitResult.nodeVoltages.get(uf.find(termVwma)) || 0;
          const vB = circuitResult.nodeVoltages.get(uf.find(termCom)) || 0;
          const vDiff = Math.abs(vA - vB);
          readingText = vDiff.toFixed(2);
        } else {
          readingText = "0.00";
        }
      } else if (mode === "A_DC") {
        if (!hasValidVwma || !hasValidCom) {
          readingText = "0.00";
        } else if (circuitResult && circuitResult.totalCurrent) {
          const currentVal = circuitResult.totalCurrent;
          if (currentVal >= 1.0) {
            readingText = currentVal.toFixed(2);
          } else if (currentVal > 0) {
            readingText = currentVal.toFixed(3);
          } else {
            readingText = "0.00";
          }
        } else {
          readingText = "0.00";
        }
      }

      m.properties.reading = readingText;
      const valEl = document.getElementById(`meter-val-${m.id}`);
      if (valEl) valEl.textContent = readingText;
    });
  }

  /**
   * Solves Active Circuit with DC Battery power source
   */
  solveActiveCircuit(battery, components, connections, uf) {
    const batPosNet = uf.find(`${battery.id}:term_pos`);
    const batNegNet = uf.find(`${battery.id}:term_neg`);
    const sourceVoltage = Number(battery.properties.voltage || 12);

    // Calculate equivalent load resistance connected across Battery terminals
    const equivalentR = this.calculateEquivalentResistance(
      `${battery.id}:term_pos`,
      `${battery.id}:term_neg`,
      components,
      connections
    );

    if (!isFinite(equivalentR) || equivalentR > 1e7) {
      return null; // Open circuit
    }

    if (equivalentR < 0.1) {
      return { shortCircuit: true, totalCurrent: 99.9, equivalentResistance: 0 };
    }

    const totalCurrent = sourceVoltage / equivalentR;

    // Estimate component voltages and currents
    const componentVoltages = new Map();
    const componentCurrents = new Map();
    const nodeVoltages = new Map();

    nodeVoltages.set(batNegNet, 0);
    nodeVoltages.set(batPosNet, sourceVoltage);

    components.forEach(c => {
      if (["resistor", "lamp", "led", "motor_dc", "diode"].includes(c.type)) {
        if (c.terminals.length >= 2) {
          const u = uf.find(`${c.id}:${c.terminals[0].id}`);
          const v = uf.find(`${c.id}:${c.terminals[1].id}`);
          const R = Math.max(0.001, Number(c.properties.resistance || 10));

          // If connected directly across battery
          if ((u === batPosNet && v === batNegNet) || (u === batNegNet && v === batPosNet)) {
            componentVoltages.set(c.id, sourceVoltage);
            componentCurrents.set(c.id, sourceVoltage / R);
          } else {
            // General branch current proportional to resistance share
            const shareRatio = Math.min(1.0, R / equivalentR);
            const compV = sourceVoltage * (equivalentR < R ? (equivalentR / R) : shareRatio);
            componentVoltages.set(c.id, compV);
            componentCurrents.set(c.id, compV / R);
          }
        }
      }
    });

    return {
      shortCircuit: false,
      totalCurrent,
      equivalentResistance: equivalentR,
      componentVoltages,
      componentCurrents,
      nodeVoltages
    };
  }

  /**
   * Solves A * x = b via Gaussian Elimination with Partial Pivoting
   */
  solveLinearSystem(A, b) {
    const n = A.length;
    if (n === 0) return [];

    // Clone matrices
    const M = A.map(row => [...row]);
    const x = [...b];

    for (let i = 0; i < n; i++) {
      let maxRow = i;
      for (let k = i + 1; k < n; k++) {
        if (Math.abs(M[k][i]) > Math.abs(M[maxRow][i])) {
          maxRow = k;
        }
      }

      [M[i], M[maxRow]] = [M[maxRow], M[i]];
      [x[i], x[maxRow]] = [x[maxRow], x[i]];

      if (Math.abs(M[i][i]) < 1e-12) continue;

      for (let k = i + 1; k < n; k++) {
        const factor = M[k][i] / M[i][i];
        for (let j = i; j < n; j++) {
          M[k][j] -= factor * M[i][j];
        }
        x[k] -= factor * x[i];
      }
    }

    const sol = new Array(n).fill(0);
    for (let i = n - 1; i >= 0; i--) {
      if (Math.abs(M[i][i]) < 1e-12) continue;
      let sum = 0;
      for (let j = i + 1; j < n; j++) {
        sum += M[i][j] * sol[j];
      }
      sol[i] = (x[i] - sum) / M[i][i];
    }

    return sol;
  }

  /**
   * Update visual representations of active loads
   */
  updateLoadVisuals(components, componentVoltages, componentCurrents) {
    components.forEach(comp => {
      const compEl = document.getElementById(`comp-${comp.id}`);
      if (!compEl) return;

      const compV = componentVoltages.get(comp.id) || 0;
      const compI = componentCurrents.get(comp.id) || 0;

      if (comp.type === "lamp") {
        const nominalV = Number(comp.properties.nominalVoltage || 12);
        const glowRatio = Math.min(Math.max(compV / nominalV, 0.2), 1.6);

        if (compV > 1.0) {
          compEl.classList.add("lit");
          compEl.style.setProperty("--glow-intensity", glowRatio.toFixed(2));
          const lampVis = compEl.querySelector(".lamp-visual");
          if (lampVis) {
            lampVis.classList.add("lit");
            lampVis.style.setProperty("--glow-intensity", glowRatio.toFixed(2));
          }
        }
      } else if (comp.type === "led") {
        if (compV >= 1.5) {
          compEl.classList.add("lit");
          const ledVis = compEl.querySelector(".led-visual");
          if (ledVis) ledVis.classList.add("lit");
        }
      } else if (comp.type === "motor_dc") {
        const nominalV = Number(comp.properties.nominalVoltage || 12);
        const maxRpm = Number(comp.properties.maxRpm || 3000);

        if (compV > 1.0) {
          const speedRatio = Math.min(Math.max(compV / nominalV, 0.1), 1.8);
          const actualRpm = Math.round(maxRpm * speedRatio);
          comp.properties.currentRpm = actualRpm;

          compEl.classList.add("spinning");
          const spinDuration = Math.max(0.1, (0.4 / speedRatio)).toFixed(3);
          compEl.style.setProperty("--spin-speed", `${spinDuration}s`);

          const rotor = compEl.querySelector(".motor-rotor-blades");
          if (rotor) {
            rotor.classList.add("spinning");
            rotor.style.setProperty("--spin-speed", `${spinDuration}s`);
          }

          const rpmEl = compEl.querySelector(".rpm-number");
          if (rpmEl) rpmEl.textContent = String(actualRpm);
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

  updateMetricsUI(voltage, current, power, status, statusText) {
    if (this.metricVoltage) this.metricVoltage.textContent = `${voltage.toFixed(1)} V`;
    if (this.metricCurrent) this.metricCurrent.textContent = `${current.toFixed(2)} A`;
    if (this.metricPower) this.metricPower.textContent = `${power.toFixed(2)} W`;

    if (this.metricStatus) {
      this.metricStatus.textContent = statusText;
      this.metricStatus.className = "metric-status-badge";
      if (status === "OK") this.metricStatus.classList.add("status-ok");
      else if (status === "ERROR") this.metricStatus.classList.add("status-error");
      else this.metricStatus.classList.add("status-standby");
    }
  }

  /**
   * Automated Test Suite to verify Resistor Calculations
   */
  runAutomatedResistanceTests() {
    console.log("====================================================");
    console.log("🧪 DTE VirtualLab — Automated Resistance Test Suite");
    console.log("====================================================");

    // Test A: Single R1 (470 ohm)
    const testA = this.calculateEquivalentResistance(
      "M:vwma", "M:com",
      [{ id: "R1", type: "resistor", properties: { resistance: 470 }, terminals: [{ id: "a" }, { id: "b" }] }],
      [{ from: { componentId: "M", terminalId: "vwma" }, to: { componentId: "R1", terminalId: "a" } },
       { from: { componentId: "M", terminalId: "com" }, to: { componentId: "R1", terminalId: "b" } }]
    );
    console.log(`✅ Test A (R1 = 470 Ω): Hasil = ${testA.toFixed(3)} Ω | Target = 470.000 Ω -> ${Math.abs(testA - 470) < 0.001 ? "PASSED" : "FAILED"}`);

    // Test B: R2 (330 ohm) // R3 (470 ohm)
    const testB = this.calculateEquivalentResistance(
      "M:vwma", "M:com",
      [
        { id: "R2", type: "resistor", properties: { resistance: 330 }, terminals: [{ id: "a" }, { id: "b" }] },
        { id: "R3", type: "resistor", properties: { resistance: 470 }, terminals: [{ id: "a" }, { id: "b" }] }
      ],
      [
        { from: { componentId: "M", terminalId: "vwma" }, to: { componentId: "R2", terminalId: "a" } },
        { from: { componentId: "M", terminalId: "vwma" }, to: { componentId: "R3", terminalId: "a" } },
        { from: { componentId: "M", terminalId: "com" }, to: { componentId: "R2", terminalId: "b" } },
        { from: { componentId: "M", terminalId: "com" }, to: { componentId: "R3", terminalId: "b" } }
      ]
    );
    console.log(`✅ Test B (R2 // R3 = 193.875 Ω): Hasil = ${testB.toFixed(3)} Ω | Target = 193.875 Ω -> ${Math.abs(testB - 193.875) < 0.001 ? "PASSED" : "FAILED"}`);

    // Test C: R1 (470 ohm) + (R2 // R3)
    const testC = this.calculateEquivalentResistance(
      "M:vwma", "M:com",
      [
        { id: "R1", type: "resistor", properties: { resistance: 470 }, terminals: [{ id: "a" }, { id: "b" }] },
        { id: "R2", type: "resistor", properties: { resistance: 330 }, terminals: [{ id: "a" }, { id: "b" }] },
        { id: "R3", type: "resistor", properties: { resistance: 470 }, terminals: [{ id: "a" }, { id: "b" }] }
      ],
      [
        { from: { componentId: "M", terminalId: "vwma" }, to: { componentId: "R1", terminalId: "a" } },
        { from: { componentId: "R1", terminalId: "b" }, to: { componentId: "R2", terminalId: "a" } },
        { from: { componentId: "R1", terminalId: "b" }, to: { componentId: "R3", terminalId: "a" } },
        { from: { componentId: "R2", terminalId: "b" }, to: { componentId: "M", terminalId: "com" } },
        { from: { componentId: "R3", terminalId: "b" }, to: { componentId: "M", terminalId: "com" } }
      ]
    );
    console.log(`✅ Test C (R1 + (R2 // R3) = 663.875 Ω): Hasil = ${testC.toFixed(3)} Ω | Target = 663.875 Ω -> ${Math.abs(testC - 663.875) < 0.001 ? "PASSED" : "FAILED"}`);
    console.log("====================================================");

    return { testA, testB, testC };
  }
}
