/**
 * FLUXUS / DTE VirtualLab — Modified Nodal Analysis (MNA) Engine
 * Solves arbitrary electrical networks via linear matrix equations [G B; C D] * [v; j] = [i; e]
 * Implements Ohm's Law, KCL, KVL, and Joule Heating across all nodes and branches.
 */

import { NetlistBuilder } from "./netlist.js";
import { ComponentModels } from "./models.js";

export class MNACircuitSolver {
  /**
   * Solves A * x = b via Gaussian Elimination with Partial Pivoting
   */
  static solveLinearSystem(A, b) {
    const n = A.length;
    if (n === 0) return [];

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
   * Solves the active circuit using Augmented MNA Matrix Form
   * 
   * @param {Object} primaryBattery - Main battery or DC power supply
   * @param {Array} components - All components
   * @param {Array} connections - All wire connections
   * @returns {Object} Solved state: { totalVoltage, totalCurrent, equivalentResistance, nodeVoltages, branchVoltages, branchCurrents, ammeterCurrents, shortCircuit, openCircuit }
   */
  static solve(primaryBattery, components = [], connections = []) {
    const netlist = NetlistBuilder.build(components, connections);
    const { uf, nets, netToIndex, groundNet } = netlist;

    if (!primaryBattery || nets.length === 0) {
      return {
        openCircuit: true,
        shortCircuit: false,
        totalVoltage: 0,
        totalCurrent: 0,
        equivalentResistance: Infinity,
        nodeVoltages: new Map(),
        branchVoltages: new Map(),
        branchCurrents: new Map(),
        ammeterCurrents: new Map()
      };
    }

    const batPosNet = uf.find(`${primaryBattery.id}:term_pos`);
    const batNegNet = uf.find(`${primaryBattery.id}:term_neg`);
    const sourceVoltage = Number(primaryBattery.properties?.voltage ?? 12);
    const internalR = Math.max(0, Number(primaryBattery.properties?.internalResistance || 0));

    // Direct short circuit between battery terminals
    if (batPosNet === batNegNet) {
      const nodeVoltages = new Map();
      nodeVoltages.set(batNegNet, 0);
      const isRealistic = internalR > 0;
      const shortCurrent = isRealistic ? (sourceVoltage / internalR) : null;
      const shortSourcePower = isRealistic ? (sourceVoltage * shortCurrent) : null;
      const shortLoadPower = isRealistic ? 0 : null;
      const shortInternalLoss = isRealistic ? (shortCurrent * shortCurrent * internalR) : null;
      return {
        shortCircuit: true,
        openCircuit: false,
        sourceEMF: sourceVoltage,
        terminalVoltage: 0,
        totalVoltage: 0,
        emfVoltage: sourceVoltage,
        totalCurrent: shortCurrent,
        totalPower: shortSourcePower,
        sourcePower: shortSourcePower,
        loadPower: shortLoadPower,
        internalLoss: shortInternalLoss,
        power: {
          source: shortSourcePower,
          load: shortLoadPower,
          internalLoss: shortInternalLoss
        },
        equivalentResistance: 0,
        nodeVoltages,
        branchVoltages: new Map(),
        branchCurrents: new Map(),
        ammeterCurrents: new Map(),
        message: isRealistic
          ? `Short circuit: current limited by internal resistance (${shortCurrent.toFixed(2)} A)`
          : "Short circuit: ideal current is undefined / unbounded on ideal source model"
      };
    }

    // 1. Calculate Equivalent Load Resistance seen by the Battery
    const equivalentR = this.calculateEquivalentResistance(
      `${primaryBattery.id}:term_pos`,
      `${primaryBattery.id}:term_neg`,
      components,
      connections
    );

    if (!isFinite(equivalentR) || equivalentR > 1e7) {
      // Open circuit: compute static DC potentials
      const nodeVoltages = this.computeStaticVoltages(components, connections, uf, primaryBattery);
      return {
        openCircuit: true,
        shortCircuit: false,
        sourceEMF: sourceVoltage,
        terminalVoltage: sourceVoltage,
        totalVoltage: sourceVoltage,
        emfVoltage: sourceVoltage,
        totalCurrent: 0,
        totalPower: 0,
        sourcePower: 0,
        loadPower: 0,
        internalLoss: 0,
        power: {
          source: 0,
          load: 0,
          internalLoss: 0
        },
        equivalentResistance: Infinity,
        nodeVoltages,
        branchVoltages: new Map(),
        branchCurrents: new Map(),
        ammeterCurrents: new Map()
      };
    }

    if (equivalentR < 0.05) {
      // Near-short circuit condition:
      // For ideal battery: current is physically undefined/unbounded, report totalCurrent = null
      // For realistic battery: current is limited by internal resistance
      const isRealistic = internalR > 0;
      const shortCurrent = isRealistic ? (sourceVoltage / (internalR + equivalentR)) : null;
      const termV = isRealistic ? (sourceVoltage * (equivalentR / (internalR + equivalentR))) : 0;
      const shortSourcePower = isRealistic ? (sourceVoltage * shortCurrent) : null;
      const shortLoadPower = isRealistic ? (termV * shortCurrent) : null;
      const shortInternalLoss = isRealistic ? (shortCurrent * shortCurrent * internalR) : null;
      const nodeVoltages = new Map();
      nodeVoltages.set(batNegNet, 0);
      nodeVoltages.set(batPosNet, termV);
      return {
        shortCircuit: true,
        openCircuit: false,
        sourceEMF: sourceVoltage,
        terminalVoltage: termV,
        totalVoltage: termV,
        emfVoltage: sourceVoltage,
        totalCurrent: shortCurrent,
        totalPower: shortSourcePower,
        sourcePower: shortSourcePower,
        loadPower: shortLoadPower,
        internalLoss: shortInternalLoss,
        power: {
          source: shortSourcePower,
          load: shortLoadPower,
          internalLoss: shortInternalLoss
        },
        equivalentResistance: equivalentR,
        nodeVoltages,
        branchVoltages: new Map(),
        branchCurrents: new Map(),
        ammeterCurrents: new Map(),
        message: isRealistic
          ? `Short circuit: current limited by internal resistance (${shortCurrent.toFixed(2)} A)`
          : "Short circuit: ideal current is undefined / unbounded on ideal source model"
      };
    }

    // 2. Build Augmented MNA System [G B; C D] * [V; J] = [I; E]
    const N = nets.length;
    const G = Array.from({ length: N }, () => new Array(N).fill(0));

    // Populate Conductances for all passive components & meter shunts
    components.forEach(c => {
      if (["resistor", "lamp", "motor_dc", "switch_spst", "diode", "led"].includes(c.type) && c.terminals?.length >= 2) {
        const u = netToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
        const v = netToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));
        if (u !== undefined && v !== undefined && u !== v) {
          let g = 0;
          if (c.type === "resistor") g = ComponentModels.getResistorModel(c).conductance;
          else if (c.type === "lamp") g = ComponentModels.getLampModel(c).conductance;
          else if (c.type === "motor_dc") g = ComponentModels.getMotorModel(c).conductance;
          else if (c.type === "switch_spst") g = ComponentModels.getSwitchModel(c).conductance;
          else if (c.type === "diode") g = 10; // 0.1 ohm forward conduction in active solved loop
          else if (c.type === "led") g = 1 / ComponentModels.getLEDModel(c).resistance;

          G[u][u] += g;
          G[v][v] += g;
          G[u][v] -= g;
          G[v][u] -= g;
        }
      } else if (c.type === "multimeter") {
        let tCom = `${c.id}:term_com`;
        let tVwma = `${c.id}:term_vwma`;
        if (c.properties?.probes?.com?.attachedTo) {
          tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
        }
        if (c.properties?.probes?.vwma?.attachedTo) {
          tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
        }
        const u = netToIndex.get(uf.find(tVwma));
        const v = netToIndex.get(uf.find(tCom));
        if (u !== undefined && v !== undefined && u !== v) {
          const mode = c.properties?.mode || "V_DC";
          let gMeter = 1e-7; // 10 MΩ Voltmeter input resistance
          if (mode === "A_DC") {
            gMeter = 1000; // 0.001 Ω (1 mΩ) Ammeter shunt
          }
          G[u][u] += gMeter;
          G[v][v] += gMeter;
          G[u][v] -= gMeter;
          G[v][u] -= gMeter;
        }
      }
    });

    const idxNeg = netToIndex.get(batNegNet);
    const idxPos = netToIndex.get(batPosNet);

    // Calculate actual terminal voltage:
    // - Ideal battery (default): V_terminal = sourceVoltage (EMF)
    // - Realistic battery (if internalResistance > 0): V_terminal = EMF * (R_eq / (R_int + R_eq))
    const terminalVoltage = internalR > 0
      ? (sourceVoltage * (equivalentR / (internalR + equivalentR)))
      : sourceVoltage;

    // Solve for all unknown node voltages (excluding fixed ground idxNeg and fixed source idxPos)
    const unknownIndices = [];
    for (let i = 0; i < N; i++) {
      if (i !== idxNeg && i !== idxPos) unknownIndices.push(i);
    }

    const M = unknownIndices.length;
    const A_mat = Array.from({ length: M }, () => new Array(M).fill(0));
    const b_vec = new Array(M).fill(0);

    for (let i = 0; i < M; i++) {
      const oldI = unknownIndices[i];
      for (let j = 0; j < M; j++) {
        const oldJ = unknownIndices[j];
        A_mat[i][j] = G[oldI][oldJ];
      }
      b_vec[i] = -G[oldI][idxPos] * terminalVoltage - G[oldI][idxNeg] * 0;
    }

    const unkSol = this.solveLinearSystem(A_mat, b_vec);
    const nodeVoltages = new Map();
    nodeVoltages.set(batNegNet, 0);
    nodeVoltages.set(batPosNet, terminalVoltage);

    unknownIndices.forEach((oldIdx, unkIdx) => {
      nodeVoltages.set(nets[oldIdx], unkSol ? unkSol[unkIdx] : 0);
    });

    // Build a complete voltage vector indexed by original net indices
    const V = new Array(N).fill(0);
    V[idxNeg] = 0;
    V[idxPos] = terminalVoltage;
    unknownIndices.forEach((oldIdx, unkIdx) => {
      V[oldIdx] = unkSol ? unkSol[unkIdx] : 0;
    });

    // 3. Derive source current from KCL at battery positive node using the G matrix.
    //    I_source = Σ_k G[idxPos][k] * V[k]
    //    This is the net current flowing OUT of batPosNet, which by KCL equals
    //    the sum of all branch currents leaving that node — guaranteed consistent.
    let sourceCurrent = 0;
    for (let k = 0; k < N; k++) {
      sourceCurrent += G[idxPos][k] * V[k];
    }
    sourceCurrent = Math.abs(sourceCurrent);

    // Derive equivalentResistance, terminal voltage, and power from the same solved values
    const solvedEquivalentR = sourceCurrent > 1e-12 ? (terminalVoltage / sourceCurrent) : Infinity;
    const totalPower = sourceVoltage * sourceCurrent;

    // 4. Compute exact branch voltages and currents for all components
    const branchVoltages = new Map();
    const branchCurrents = new Map();

    components.forEach(c => {
      if (["resistor", "lamp", "led", "motor_dc", "diode"].includes(c.type) && c.terminals?.length >= 2) {
        const v1 = nodeVoltages.get(uf.find(`${c.id}:${c.terminals[0].id}`)) || 0;
        const v2 = nodeVoltages.get(uf.find(`${c.id}:${c.terminals[1].id}`)) || 0;
        const vDiff = Math.abs(v1 - v2);
        let R = 10;
        if (c.type === "resistor") R = ComponentModels.getResistorModel(c).resistance;
        else if (c.type === "lamp") R = ComponentModels.getLampModel(c).resistance;
        else if (c.type === "motor_dc") R = ComponentModels.getMotorModel(c).resistance;
        else if (c.type === "diode") R = 0.1;
        else if (c.type === "led") R = ComponentModels.getLEDModel(c).resistance;

        branchVoltages.set(c.id, vDiff);
        branchCurrents.set(c.id, vDiff / Math.max(0.001, R));
      }
    });

    // 5. Compute exact current passing through each Ammeter branch
    const ammeterCurrents = new Map();
    components.forEach(c => {
      if (c.type === "multimeter") {
        let tCom = `${c.id}:term_com`;
        let tVwma = `${c.id}:term_vwma`;
        if (c.properties?.probes?.com?.attachedTo) {
          tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
        }
        if (c.properties?.probes?.vwma?.attachedTo) {
          tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
        }
        const vVwma = nodeVoltages.get(uf.find(tVwma)) || 0;
        const vCom = nodeVoltages.get(uf.find(tCom)) || 0;
        const Rshunt = 0.001; // 1 mΩ
        const iMeter = Math.abs(vVwma - vCom) / Rshunt;
        ammeterCurrents.set(c.id, iMeter);
      }
    });

    const sourceEMF = sourceVoltage;
    const sourcePower = sourceEMF * sourceCurrent;
    const loadPower = terminalVoltage * sourceCurrent;
    const internalLoss = internalR > 0 ? (sourceCurrent * sourceCurrent * internalR) : 0;

    return {
      openCircuit: false,
      shortCircuit: false,
      sourceEMF,
      terminalVoltage,
      totalVoltage: terminalVoltage,
      emfVoltage: sourceEMF,
      totalCurrent: sourceCurrent,
      totalPower: sourcePower,
      sourcePower,
      loadPower,
      internalLoss,
      power: {
        source: sourcePower,
        load: loadPower,
        internalLoss: internalLoss
      },
      equivalentResistance: solvedEquivalentR,
      nodeVoltages,
      branchVoltages,
      branchCurrents,
      ammeterCurrents
    };
  }

  /**
   * Calculates Equivalent Resistance between two arbitrary terminals
   */
  static calculateEquivalentResistance(termAKey, termBKey, components = [], connections = []) {
    const netlist = NetlistBuilder.build(components, connections);
    const { uf, nets, netToIndex } = netlist;

    const rootA = uf.find(termAKey);
    const rootB = uf.find(termBKey);

    if (rootA === rootB) return 0;

    const N = nets.length;
    const G = Array.from({ length: N }, () => new Array(N).fill(0));
    const adj = Array.from({ length: N }, () => []);

    components.forEach(c => {
      if (["resistor", "lamp", "led", "motor_dc", "diode", "switch_spst"].includes(c.type) && c.terminals?.length >= 2) {
        const u = netToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
        const v = netToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));
        if (u !== undefined && v !== undefined && u !== v) {
          let R = 220;
          if (c.type === "resistor") R = ComponentModels.getResistorModel(c).resistance;
          else if (c.type === "lamp") R = ComponentModels.getLampModel(c).resistance;
          else if (c.type === "motor_dc") R = ComponentModels.getMotorModel(c).resistance;
          else if (c.type === "switch_spst") R = ComponentModels.getSwitchModel(c).resistance;
          else if (c.type === "diode") R = 0.1;
          else if (c.type === "led") R = ComponentModels.getLEDModel(c).resistance;

          R = Math.max(0.001, R);
          const g = 1 / R;
          G[u][u] += g; G[v][v] += g; G[u][v] -= g; G[v][u] -= g;
          adj[u].push(v); adj[v].push(u);
        }
      } else if (c.type === "multimeter" && c.properties?.mode === "A_DC") {
        let tCom = `${c.id}:term_com`;
        let tVwma = `${c.id}:term_vwma`;
        if (c.properties?.probes?.com?.attachedTo) tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
        if (c.properties?.probes?.vwma?.attachedTo) tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
        const u = netToIndex.get(uf.find(tVwma));
        const v = netToIndex.get(uf.find(tCom));
        if (u !== undefined && v !== undefined && u !== v) {
          const g = 1000; // 0.001 ohm shunt
          G[u][u] += g; G[v][v] += g; G[u][v] -= g; G[v][u] -= g;
          adj[u].push(v); adj[v].push(u);
        }
      }
    });

    const idxA = netToIndex.get(rootA);
    const idxB = netToIndex.get(rootB);

    if (idxA === undefined || idxB === undefined) return Infinity;

    // Connectivity BFS check
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

    if (!visited.has(idxB)) return Infinity;

    const remainingIndices = [];
    for (let i = 0; i < N; i++) if (i !== idxB) remainingIndices.push(i);

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
    I_red[newIdxA] = 1.0; // Inject 1.0 A test current
    const V_red = this.solveLinearSystem(G_red, I_red);
    return V_red ? Math.abs(V_red[newIdxA]) : Infinity;
  }

  /**
   * Computes Static DC Potentials for open circuits or unpowered state
   */
  static computeStaticVoltages(components, connections, uf, primaryBattery) {
    const nodeVoltages = new Map();
    if (!primaryBattery) return nodeVoltages;

    const batNegNet = uf.find(`${primaryBattery.id}:term_neg`);
    const batPosNet = uf.find(`${primaryBattery.id}:term_pos`);
    const vBat = Number(primaryBattery.properties?.voltage ?? 12);

    nodeVoltages.set(batNegNet, 0);
    nodeVoltages.set(batPosNet, vBat);

    let changed = true;
    let iterations = 0;
    while (changed && iterations < 15) {
      changed = false;
      iterations++;

      components.forEach(c => {
        if (c.type === "switch_spst" && c.properties?.isClosed && c.terminals?.length >= 2) {
          const n1 = uf.find(`${c.id}:${c.terminals[0].id}`);
          const n2 = uf.find(`${c.id}:${c.terminals[1].id}`);
          if (nodeVoltages.has(n1) && !nodeVoltages.has(n2)) {
            nodeVoltages.set(n2, nodeVoltages.get(n1));
            changed = true;
          } else if (nodeVoltages.has(n2) && !nodeVoltages.has(n1)) {
            nodeVoltages.set(n1, nodeVoltages.get(n2));
            changed = true;
          }
        } else if (["resistor", "lamp", "led", "motor_dc", "diode"].includes(c.type) && c.terminals?.length >= 2) {
          const n1 = uf.find(`${c.id}:${c.terminals[0].id}`);
          const n2 = uf.find(`${c.id}:${c.terminals[1].id}`);
          if (nodeVoltages.has(n1) && !nodeVoltages.has(n2)) {
            nodeVoltages.set(n2, nodeVoltages.get(n1));
            changed = true;
          } else if (nodeVoltages.has(n2) && !nodeVoltages.has(n1)) {
            nodeVoltages.set(n1, nodeVoltages.get(n2));
            changed = true;
          }
        }
      });
    }

    return nodeVoltages;
  }
}
