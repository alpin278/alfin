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
        sourceEMF: 0,
        terminalVoltage: 0,
        totalVoltage: 0,
        emfVoltage: 0,
        totalCurrent: 0,
        totalPower: 0,
        sourcePower: 0,
        loadPower: 0,
        internalLoss: 0,
        power: { source: 0, load: 0, internalLoss: 0 },
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

    // Identify all Diodes / LEDs
    const diodes = components.filter(c => (c.type === "led" || c.type === "diode") && c.terminals?.length >= 2);

    // 1. Calculate Equivalent Load Resistance seen by the Battery
    const equivalentR = this.calculateEquivalentResistance(
      `${primaryBattery.id}:term_pos`,
      `${primaryBattery.id}:term_neg`,
      components,
      connections
    );

    if (!isFinite(equivalentR) || equivalentR > 1e7) {
      // If open circuit and no diodes/LEDs in circuit, return static potentials
      if (diodes.length === 0) {
        const nodeVoltages = this.computeStaticVoltages(components, connections, uf, primaryBattery);
        const branchVoltages = new Map();
        const branchCurrents = new Map();
        components.forEach(c => {
          if (["resistor", "lamp", "motor_dc", "switch_spst", "led", "diode"].includes(c.type) && c.terminals?.length >= 2) {
            const v1 = nodeVoltages.get(uf.find(`${c.id}:${c.terminals[0].id}`)) || 0;
            const v2 = nodeVoltages.get(uf.find(`${c.id}:${c.terminals[1].id}`)) || 0;
            const vDiff = v1 - v2;
            branchVoltages.set(c.id, vDiff);
            branchCurrents.set(c.id, 0);
          }
        });
        let totalInstI = 0;
        components.forEach(c => {
          if (c.type === "multimeter" && (c.properties?.mode === "V_DC" || !c.properties?.mode)) {
            let tCom = `${c.id}:term_com`;
            let tVwma = `${c.id}:term_vwma`;
            if (c.properties?.probes?.com?.attachedTo) tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
            if (c.properties?.probes?.vwma?.attachedTo) tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
            const vVwma = nodeVoltages.get(uf.find(tVwma)) || 0;
            const vCom = nodeVoltages.get(uf.find(tCom)) || 0;
            const vDiff = Math.abs(vVwma - vCom);
            totalInstI += vDiff / 10e6;
          }
        });

        return {
          openCircuit: true,
          shortCircuit: false,
          sourceEMF: sourceVoltage,
          terminalVoltage: sourceVoltage,
          totalVoltage: sourceVoltage,
          emfVoltage: sourceVoltage,
          totalCurrent: 0,
          sourceCurrent: totalInstI,
          mainLoadCurrent: 0,
          physicalLoadCurrent: 0,
          instrumentCurrent: totalInstI,
          gminCurrent: 0,
          totalPower: sourceVoltage * totalInstI,
          sourcePower: sourceVoltage * totalInstI,
          loadPower: 0,
          internalLoss: 0,
          power: { source: sourceVoltage * totalInstI, load: 0, instrument: sourceVoltage * totalInstI, gmin: 0, internalLoss: 0 },
          equivalentResistance: Infinity,
          nodeVoltages,
          branchVoltages,
          branchCurrents,
          ammeterCurrents: new Map()
        };
      }
    }

    if (equivalentR < 0.05 && diodes.length === 0) {
      // Direct near-short circuit (pure resistor loop)
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

    // Check for direct LED connection without current-limiting resistor across battery
    let isLEDOvercurrent = false;
    let warningMessage = null;
    diodes.forEach(d => {
      const uNet = uf.find(`${d.id}:${d.terminals[0].id}`);
      const vNet = uf.find(`${d.id}:${d.terminals[1].id}`);
      if ((uNet === batPosNet && vNet === batNegNet) || (uNet === batNegNet && vNet === batPosNet)) {
        isLEDOvercurrent = true;
        warningMessage = "LED Overcurrent / Current-Limiting Resistor Required";
      }
    });

    if (isLEDOvercurrent && internalR === 0) {
      // Direct connected to ideal battery: dangerous unbounded overcurrent
      const nodeVoltages = new Map();
      nodeVoltages.set(batNegNet, 0);
      nodeVoltages.set(batPosNet, sourceVoltage);
      const branchVoltages = new Map();
      const branchCurrents = new Map();
      branchVoltages.set(diodes[0].id, sourceVoltage);
      branchCurrents.set(diodes[0].id, null);

      return {
        openCircuit: false,
        shortCircuit: false,
        overcurrent: true,
        sourceEMF: sourceVoltage,
        terminalVoltage: sourceVoltage,
        totalVoltage: sourceVoltage,
        emfVoltage: sourceVoltage,
        totalCurrent: null,
        totalPower: null,
        sourcePower: null,
        loadPower: null,
        internalLoss: 0,
        power: { source: null, load: null, internalLoss: 0 },
        equivalentResistance: 0,
        nodeVoltages,
        branchVoltages,
        branchCurrents,
        ammeterCurrents: new Map(),
        warning: warningMessage,
        message: warningMessage
      };
    }

    // 2. Build Augmented MNA System [G B; C D] * [V; J] = [I; E] with Iterative Piecewise Diode Evaluation
    const N = nets.length;
    const idxNeg = netToIndex.get(batNegNet);
    const idxPos = netToIndex.get(batPosNet);

    // Initial diode states: start as "OFF"
    const diodeStates = new Map();
    diodes.forEach(d => diodeStates.set(d.id, "OFF"));

    // Initial motor states & voltages
    const motors = components.filter(c => c.type === "motor_dc");
    const motorVoltages = new Map();
    motors.forEach(m => motorVoltages.set(m.id, 0));

    let finalNodeVoltages = new Map();
    let finalBranchVoltages = new Map();
    let finalBranchVoltagesSigned = new Map();
    let finalBranchCurrents = new Map();
    let finalBranchCurrentsSigned = new Map();
    let finalBranchCurrentMagnitudes = new Map();
    let finalMotorResults = new Map();
    let finalAmmeterCurrents = new Map();
    let finalTotalCurrent = 0;
    let finalSourceCurrentSigned = 0;
    let finalTerminalVoltage = sourceVoltage;
    let finalIterationCount = 1;
    let isConverged = true;

    for (let iter = 0; iter < 20; iter++) {
      finalIterationCount = iter + 1;
      const activeDiodes = diodes.filter(d => diodeStates.get(d.id) === "ON");
      const numActiveDiodes = activeDiodes.length;
      const K = 1 + numActiveDiodes;
      const totalDim = N + K;

      const A = Array.from({ length: totalDim }, () => new Array(totalDim).fill(0));
      const b = new Array(totalDim).fill(0);

      // Passive component conductances into G
      components.forEach(c => {
        if (["resistor", "lamp", "switch_spst"].includes(c.type) && c.terminals?.length >= 2) {
          const u = netToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
          const v = netToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));
          if (u !== undefined && v !== undefined && u !== v) {
            let g = 0;
            if (c.type === "resistor") g = ComponentModels.getResistorModel(c).conductance;
            else if (c.type === "lamp") g = ComponentModels.getLampModel(c).conductance;
            else if (c.type === "switch_spst") g = ComponentModels.getSwitchModel(c).conductance;

            A[u][u] += g; A[v][v] += g; A[u][v] -= g; A[v][u] -= g;
          }
        } else if (c.type === "motor_dc" && c.terminals?.length >= 2) {
          const u = netToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
          const v = netToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));
          const vd = motorVoltages.get(c.id) || 0;
          const mModel = ComponentModels.getMotorModel(c);
          const mOp = mModel.evaluate(vd);
          const Ra = mModel.armatureResistance;
          const Ga = 1 / Ra;
          const Iemf = Math.abs(vd) >= 1e-4 ? (mOp.backEmf / Ra) : 0;

          if (u !== undefined && v !== undefined && u !== v) {
            A[u][u] += Ga; A[v][v] += Ga; A[u][v] -= Ga; A[v][u] -= Ga;
          }
          if (u !== undefined && u !== idxNeg) b[u] += Iemf;
          if (v !== undefined && v !== idxNeg) b[v] -= Iemf;
        } else if (c.type === "multimeter") {
          let tCom = `${c.id}:term_com`;
          let tVwma = `${c.id}:term_vwma`;
          if (c.properties?.probes?.com?.attachedTo) tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
          if (c.properties?.probes?.vwma?.attachedTo) tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
          const u = netToIndex.get(uf.find(tVwma));
          const v = netToIndex.get(uf.find(tCom));
          if (u !== undefined && v !== undefined && u !== v) {
            const mode = c.properties?.mode || "V_DC";
            let gMeter = 1e-7;
            if (mode === "A_DC") gMeter = 1000;
            A[u][u] += gMeter; A[v][v] += gMeter; A[u][v] -= gMeter; A[v][u] -= gMeter;
          }
        } else if (c.type === "led" || c.type === "diode") {
          if (diodeStates.get(c.id) === "OFF") {
            const u = netToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
            const v = netToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));
            if (u !== undefined && v !== undefined && u !== v) {
              const gOff = 1e-9;
              A[u][u] += gOff; A[v][v] += gOff; A[u][v] -= gOff; A[v][u] -= gOff;
            }
          }
        }
      });

      // Ground reference equation (Row idxNeg)
      for (let j = 0; j < totalDim; j++) A[idxNeg][j] = 0;
      A[idxNeg][idxNeg] = 1;
      b[idxNeg] = 0;

      // Battery branch: row idxPos has -J_bat (current leaving positive terminal)
      const batVarIdx = N + 0;
      A[idxPos][batVarIdx] -= 1;

      // Battery voltage constraint (Row N + 0):
      // V[idxPos] - V[idxNeg] + r_int * J_bat = sourceVoltage
      A[batVarIdx][idxPos] = 1;
      A[batVarIdx][idxNeg] = -1;
      if (internalR > 0) {
        A[batVarIdx][batVarIdx] = internalR;
      }
      b[batVarIdx] = sourceVoltage;

      // Active ON Diode / LED branches
      activeDiodes.forEach((d, dIdx) => {
        const varIdx = N + 1 + dIdx;
        const u = netToIndex.get(uf.find(`${d.id}:${d.terminals[0].id}`));
        const v = netToIndex.get(uf.find(`${d.id}:${d.terminals[1].id}`));
        const vf = Number(d.properties?.forwardVoltage ?? (d.type === "led" ? 2.0 : 0.7));

        if (u !== idxNeg) A[u][varIdx] += 1;
        if (v !== idxNeg) A[v][varIdx] -= 1;

        A[varIdx][u] = 1;
        A[varIdx][v] = -1;
        b[varIdx] = vf;
      });

      const sol = this.solveLinearSystem(A, b);
      const nodeV = new Map();
      for (let i = 0; i < N; i++) {
        nodeV.set(nets[i], sol[i] || 0);
      }

      const jBat = sol[batVarIdx] || 0;

      let stateChanged = false;
      diodes.forEach((d) => {
        const u = netToIndex.get(uf.find(`${d.id}:${d.terminals[0].id}`));
        const v = netToIndex.get(uf.find(`${d.id}:${d.terminals[1].id}`));
        const vf = Number(d.properties?.forwardVoltage ?? (d.type === "led" ? 2.0 : 0.7));
        const vAnode = sol[u] || 0;
        const vCathode = sol[v] || 0;
        const vDiff = vAnode - vCathode;
        const currentState = diodeStates.get(d.id);

        if (currentState === "OFF") {
          if (vDiff >= vf - 1e-4) {
            diodeStates.set(d.id, "ON");
            stateChanged = true;
          }
        } else {
          const activeIdx = activeDiodes.indexOf(d);
          const jDiode = activeIdx !== -1 ? (sol[N + 1 + activeIdx] || 0) : 0;
          if (jDiode < -1e-6) {
            diodeStates.set(d.id, "OFF");
            stateChanged = true;
          }
        }
      });

      // Update DC Motor voltages and check motor convergence
      motors.forEach((m) => {
        const u = netToIndex.get(uf.find(`${m.id}:${m.terminals[0].id}`));
        const v = netToIndex.get(uf.find(`${m.id}:${m.terminals[1].id}`));
        const vPos = u !== undefined ? (sol[u] || 0) : 0;
        const vNeg = v !== undefined ? (sol[v] || 0) : 0;
        const newVd = vPos - vNeg;

        const mModel = ComponentModels.getMotorModel(m);
        const prevMOp = mModel.evaluate(motorVoltages.get(m.id) || 0);
        const newMOp = mModel.evaluate(newVd);

        const deltaRPM = Math.abs(newMOp.rpm - prevMOp.rpm);
        const deltaI = Math.abs(newMOp.currentMagnitude - prevMOp.currentMagnitude);

        if (deltaRPM > 0.01 || deltaI > 1e-6) {
          stateChanged = true;
        }
        motorVoltages.set(m.id, newVd);
      });

      if (!stateChanged || iter === 19) {
        isConverged = !stateChanged;
        finalNodeVoltages = nodeV;
        finalTotalCurrent = Math.abs(jBat);
        finalSourceCurrentSigned = jBat;
        finalTerminalVoltage = Math.abs((sol[idxPos] || 0) - (sol[idxNeg] || 0));

        // Compute directed signed branch voltages and branch currents for all components
        components.forEach(c => {
          if (["resistor", "lamp", "switch_spst"].includes(c.type) && c.terminals?.length >= 2) {
            const v1 = nodeV.get(uf.find(`${c.id}:${c.terminals[0].id}`)) || 0;
            const v2 = nodeV.get(uf.find(`${c.id}:${c.terminals[1].id}`)) || 0;
            const signedV = v1 - v2;
            const vDiff = Math.abs(signedV);
            let R = 10;
            if (c.type === "resistor") R = ComponentModels.getResistorModel(c).resistance;
            else if (c.type === "lamp") R = ComponentModels.getLampModel(c).resistance;
            else if (c.type === "switch_spst") R = ComponentModels.getSwitchModel(c).resistance;

            const signedI = signedV / Math.max(0.001, R);
            finalBranchVoltages.set(c.id, vDiff);
            finalBranchVoltagesSigned.set(c.id, signedV);
            finalBranchCurrents.set(c.id, Math.abs(signedI));
            finalBranchCurrentsSigned.set(c.id, signedI);
            finalBranchCurrentMagnitudes.set(c.id, Math.abs(signedI));
          } else if (c.type === "motor_dc" && c.terminals?.length >= 2) {
            const v1 = nodeV.get(uf.find(`${c.id}:${c.terminals[0].id}`)) || 0;
            const v2 = nodeV.get(uf.find(`${c.id}:${c.terminals[1].id}`)) || 0;
            const signedVd = v1 - v2;
            const mModel = ComponentModels.getMotorModel(c);
            const mOp = mModel.evaluate(signedVd);

            finalMotorResults.set(c.id, mOp);
            finalBranchVoltages.set(c.id, Math.abs(signedVd));
            finalBranchVoltagesSigned.set(c.id, signedVd);
            finalBranchCurrents.set(c.id, mOp.currentMagnitude);
            finalBranchCurrentsSigned.set(c.id, mOp.current);
            finalBranchCurrentMagnitudes.set(c.id, mOp.currentMagnitude);

            if (mOp.warning && !warningMessage) {
              warningMessage = mOp.warning;
            }
          } else if (c.type === "led" || c.type === "diode") {
            const v1 = nodeV.get(uf.find(`${c.id}:${c.terminals[0].id}`)) || 0;
            const v2 = nodeV.get(uf.find(`${c.id}:${c.terminals[1].id}`)) || 0;
            const signedV = v1 - v2;
            const vDiff = Math.abs(signedV);
            const isOn = diodeStates.get(c.id) === "ON";
            const vf = Number(c.properties?.forwardVoltage ?? (c.type === "led" ? 2.0 : 0.7));

            if (isOn) {
              const activeIdx = activeDiodes.indexOf(c);
              const jDiode = activeIdx !== -1 ? Math.max(0, sol[N + 1 + activeIdx] || 0) : 0;
              finalBranchVoltages.set(c.id, vf);
              finalBranchVoltagesSigned.set(c.id, vf);
              finalBranchCurrents.set(c.id, jDiode);
              finalBranchCurrentsSigned.set(c.id, jDiode);
              finalBranchCurrentMagnitudes.set(c.id, jDiode);
            } else {
              const gOff = 1e-9;
              finalBranchVoltages.set(c.id, signedV);
              finalBranchVoltagesSigned.set(c.id, signedV);
              finalBranchCurrents.set(c.id, 0);
              finalBranchCurrentsSigned.set(c.id, signedV * gOff);
              finalBranchCurrentMagnitudes.set(c.id, 0);
            }
          } else if (c.type === "multimeter") {
            let tCom = `${c.id}:term_com`;
            let tVwma = `${c.id}:term_vwma`;
            if (c.properties?.probes?.com?.attachedTo) tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
            if (c.properties?.probes?.vwma?.attachedTo) tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
            const vVwma = nodeV.get(uf.find(tVwma)) || 0;
            const vCom = nodeV.get(uf.find(tCom)) || 0;
            const signedV = vVwma - vCom;
            const vDiff = Math.abs(signedV);
            const mode = c.properties?.mode || "V_DC";
            if (mode === "A_DC") {
              const Rshunt = 0.001;
              const signedIMeter = signedV / Rshunt;
              finalAmmeterCurrents.set(c.id, signedIMeter);
              finalBranchVoltages.set(c.id, vDiff);
              finalBranchVoltagesSigned.set(c.id, signedV);
              finalBranchCurrents.set(c.id, Math.abs(signedIMeter));
              finalBranchCurrentsSigned.set(c.id, signedIMeter);
              finalBranchCurrentMagnitudes.set(c.id, Math.abs(signedIMeter));
            } else {
              const Rin = 10e6;
              const signedInst = signedV / Rin;
              finalBranchVoltages.set(c.id, vDiff);
              finalBranchVoltagesSigned.set(c.id, signedV);
              finalBranchCurrents.set(c.id, Math.abs(signedInst));
              finalBranchCurrentsSigned.set(c.id, signedInst);
              finalBranchCurrentMagnitudes.set(c.id, Math.abs(signedInst));
            }
          }
        });
        break;
      }
    }

    // 1. Instrument current & power (Voltmeter input impedance Rin = 10 MΩ)
    let totalInstrumentCurrent = 0;
    let totalInstrumentPower = 0;
    components.forEach(c => {
      if (c.type === "multimeter" && (c.properties?.mode === "V_DC" || !c.properties?.mode)) {
        let tCom = `${c.id}:term_com`;
        let tVwma = `${c.id}:term_vwma`;
        if (c.properties?.probes?.com?.attachedTo) tCom = `${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`;
        if (c.properties?.probes?.vwma?.attachedTo) tVwma = `${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`;
        const vVwma = finalNodeVoltages.get(uf.find(tVwma)) || 0;
        const vCom = finalNodeVoltages.get(uf.find(tCom)) || 0;
        const vDiff = Math.abs(vVwma - vCom);
        const Rin = 10e6; // 10 MΩ Voltmeter input resistance
        const iInst = vDiff / Rin;
        totalInstrumentCurrent += iInst;
        totalInstrumentPower += vDiff * iInst;
      }
    });

    // 2. Numerical Gmin stabilization current & power (gOff = 1e-9 S for matrix singularity prevention)
    let totalGminCurrent = 0;
    let totalGminPower = 0;
    components.forEach(c => {
      if ((c.type === "led" || c.type === "diode") && diodeStates.get(c.id) === "OFF") {
        const v = Math.abs(finalBranchVoltages.get(c.id) || 0);
        const gOff = 1e-9; // 1 nS numerical Gmin
        const iGmin = v * gOff;
        totalGminCurrent += iGmin;
        totalGminPower += v * iGmin;
      }
    });

    // 3. Physical circuit load current & power (excluding instrument & numerical Gmin)
    const hasActiveDiodes = diodes.some(d => diodeStates.get(d.id) === "ON");
    let mainLoadCurrent = Math.max(0, finalTotalCurrent - totalInstrumentCurrent - totalGminCurrent);
    if (!hasActiveDiodes && diodes.length > 0 && mainLoadCurrent < 1e-6) {
      mainLoadCurrent = 0;
    }

    let physicalLoadPower = 0;
    components.forEach(c => {
      if (["resistor", "lamp", "motor_dc", "switch_spst"].includes(c.type)) {
        const v = finalBranchVoltages.get(c.id) || 0;
        const i = finalBranchCurrents.get(c.id) || 0;
        physicalLoadPower += Math.abs(v * i);
      } else if (c.type === "led" || c.type === "diode") {
        if (diodeStates.get(c.id) === "ON") {
          const v = Math.abs(finalBranchVoltages.get(c.id) || 0);
          const i = finalBranchCurrents.get(c.id) || 0;
          physicalLoadPower += v * i;
        }
      }
    });

    // Evaluate overcurrent across all LEDs based on solved forward branch current (> maxContinuousCurrent = 0.025 A)
    let hasOvercurrentLED = false;
    components.forEach(c => {
      if (c.type === "led") {
        const iLed = finalBranchCurrents.get(c.id) || 0;
        const maxContinuousI = Math.max(0.005, Number(c.properties?.maxContinuousCurrent ?? c.properties?.maxCurrent ?? 0.025));
        if (iLed > maxContinuousI) {
          hasOvercurrentLED = true;
        }
      }
    });

    if (hasOvercurrentLED && !warningMessage) {
      warningMessage = "LED Overcurrent / Current-Limiting Resistor Required";
      isLEDOvercurrent = true;
    }

    const isOpenCircuit = mainLoadCurrent < 1e-6 && !hasActiveDiodes;
    const sourceEMF = sourceVoltage;
    const sourcePower = sourceEMF * finalTotalCurrent;
    const loadPower = physicalLoadPower;
    const instrumentLoss = totalInstrumentPower;
    const gminLoss = totalGminPower;
    const internalLoss = internalR > 0 ? (finalTotalCurrent * finalTotalCurrent * internalR) : 0;
    const solvedEquivalentR = finalTotalCurrent > 1e-12 ? (finalTerminalVoltage / finalTotalCurrent) : Infinity;

    return {
      openCircuit: isOpenCircuit,
      shortCircuit: false,
      overcurrent: isLEDOvercurrent,
      iterationCount: finalIterationCount,
      converged: isConverged,
      diodeStates,
      sourceEMF,
      terminalVoltage: finalTerminalVoltage,
      totalVoltage: finalTerminalVoltage,
      emfVoltage: sourceEMF,
      totalCurrent: finalTotalCurrent,
      sourceCurrent: finalTotalCurrent,
      mainLoadCurrent: mainLoadCurrent,
      physicalLoadCurrent: mainLoadCurrent,
      instrumentCurrent: totalInstrumentCurrent,
      gminCurrent: totalGminCurrent,
      totalPower: sourcePower,
      sourcePower,
      loadPower,
      instrumentLoss,
      gminLoss,
      internalLoss,
      power: {
        source: sourcePower,
        load: loadPower,
        instrument: instrumentLoss,
        gmin: gminLoss,
        internalLoss
      },
      sourceCurrentSigned: finalSourceCurrentSigned,
      equivalentResistance: solvedEquivalentR,
      nodeVoltages: finalNodeVoltages,
      branchVoltages: finalBranchVoltages,
      branchVoltagesSigned: finalBranchVoltagesSigned,
      branchCurrents: finalBranchCurrents,
      branchCurrentsSigned: finalBranchCurrentsSigned,
      branchCurrentMagnitudes: finalBranchCurrentMagnitudes,
      motorResults: finalMotorResults,
      ammeterCurrents: finalAmmeterCurrents,
      warning: warningMessage,
      message: warningMessage
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
      if (["resistor", "lamp", "motor_dc", "switch_spst"].includes(c.type) && c.terminals?.length >= 2) {
        const u = netToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
        const v = netToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));
        if (u !== undefined && v !== undefined && u !== v) {
          let R = 220;
          if (c.type === "resistor") R = ComponentModels.getResistorModel(c).resistance;
          else if (c.type === "lamp") R = ComponentModels.getLampModel(c).resistance;
          else if (c.type === "motor_dc") R = ComponentModels.getMotorModel(c).resistance;
          else if (c.type === "switch_spst") R = ComponentModels.getSwitchModel(c).resistance;

          R = Math.max(0.001, R);
          const g = 1 / R;
          G[u][u] += g; G[v][v] += g; G[u][v] -= g; G[v][u] -= g;
          adj[u].push(v); adj[v].push(u);
        }
      } else if (c.type === "led" || c.type === "diode") {
        const u = netToIndex.get(uf.find(`${c.id}:${c.terminals[0].id}`));
        const v = netToIndex.get(uf.find(`${c.id}:${c.terminals[1].id}`));
        if (u !== undefined && v !== undefined && u !== v) {
          const g = 0.1;
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
          const g = 1000;
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
