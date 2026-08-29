/**
 * FLUXUS / DTE VirtualLab — Component Electrical Models
 * Pure mathematical physics models for all electrical components.
 * Adheres strictly to Ohm's Law, Joule Heating, and Semiconductor PN characteristics.
 */

export class ComponentModels {
  /**
   * Resistor Model: V = I * R, P = V * I = I^2 * R = V^2 / R
   */
  static getResistorModel(comp) {
    const resistance = Math.max(0.001, Number(comp.properties?.resistance ?? 220));
    return {
      type: "resistor",
      id: comp.id,
      resistance,
      conductance: 1 / resistance,
      evaluate(vDiff) {
        const v = Math.abs(vDiff);
        const i = v / resistance;
        const p = v * i;
        return { voltage: v, current: i, power: p };
      }
    };
  }

  /**
   * Battery / DC Voltage Source Model: V_terminal = E - I * R_internal
   */
  static getBatteryModel(comp) {
    const voltage = Math.max(0, Number(comp.properties?.voltage ?? 12));
    const internalResistance = Math.max(0, Number(comp.properties?.internalResistance || 0));
    return {
      type: "battery",
      id: comp.id,
      voltage,
      internalResistance,
      conductance: internalResistance > 0 ? (1 / internalResistance) : 10000,
      evaluate(currentDraw) {
        const terminalVoltage = Math.max(0, voltage - currentDraw * internalResistance);
        const powerSupplied = terminalVoltage * currentDraw;
        return { voltage: terminalVoltage, current: currentDraw, power: powerSupplied };
      }
    };
  }

  /**
   * Lamp Model: R_nominal = V_rated^2 / P_rated
   * Actual Power P_actual = V_actual^2 / R_nominal
   */
  static getLampModel(comp) {
    const nominalVoltage = Math.max(1, Number(comp.properties?.nominalVoltage ?? 12));
    const powerRating = Math.max(1, Number(comp.properties?.powerRating ?? 20));
    const nominalResistance = Number(comp.properties?.resistance) || ((nominalVoltage * nominalVoltage) / powerRating);
    const resistance = Math.max(0.001, Number(nominalResistance.toFixed(3)));

    return {
      type: "lamp",
      id: comp.id,
      nominalVoltage,
      powerRating,
      resistance,
      conductance: 1 / resistance,
      evaluate(vDiff) {
        const v = Math.abs(vDiff);
        const i = v / resistance;
        const p = v * i;
        const brightnessRatio = Math.min(2.0, p / powerRating);
        return { voltage: v, current: i, power: p, brightnessRatio };
      }
    };
  }

  /**
   * Educational Steady-State DC Motor Electromechanical Model
   *
   * "Motor DC menggunakan steady-state electromechanical educational model.
   * Startup transient, rotor inertia, armature inductance, commutation,
   * temperature effects, saturation, and transient inrush evolution are not modeled."
   *
   * Parameters:
   * - nominalVoltage (V_rated): default 12 V
   * - maxRpm / noLoadRpm (N_noLoad): default 3000 RPM
   * - noLoadCurrent (I_noLoad): default 0.30 A
   * - armatureResistance (Ra): default 1.0 Ω
   * - ratedCurrent (I_rated): default 2.0 A (warning threshold)
   * - loadPercent: default 0 (0..100 % of stall torque)
   *
   * Equations:
   * - omegaNoLoad = noLoadRpm * 2*pi / 60 ≈ 314.159265 rad/s
   * - Ke = (nominalVoltage - noLoadCurrent * Ra) / omegaNoLoad ≈ 0.0372422567 V/(rad/s)
   * - Kt = Ke ≈ 0.0372422567 Nm/A
   * - b = (Kt * noLoadCurrent) / omegaNoLoad ≈ 3.55637e-5 Nm*s/rad
   * - IstallNominal = nominalVoltage / Ra = 12 A
   * - TstallNominal = Kt * IstallNominal ≈ 0.446907 Nm
   * - Tload = (loadPercent / 100) * TstallNominal
   *
   * Signed Steady-State Governing Equations:
   * - omega_mag = max(0, (Kt * |V| / Ra - Tload) / (Kt * Ke / Ra + b))
   * - angularSpeed (signed) = sign(V) * omega_mag (rad/s, >0 CW, <0 CCW)
   * - backEmf (signed) = Ke * angularSpeed (V, >0 CW, <0 CCW)
   * - current (signed) = (V - backEmf) / Ra (A)
   * - Identity: V = current * Ra + backEmf (residual < 1e-9 V)
   */
  static getMotorModel(comp) {
    const nominalVoltage = Math.max(1, Number(comp.properties?.nominalVoltage ?? 12));
    const noLoadRpm = Math.max(100, Number(comp.properties?.maxRpm ?? comp.properties?.noLoadRpm ?? 3000));
    const noLoadCurrent = Math.max(0.001, Number(comp.properties?.noLoadCurrent ?? 0.30));
    const armatureResistance = Math.max(0.001, Number(comp.properties?.armatureResistance ?? comp.properties?.resistance ?? 1.0));
    const ratedCurrent = Math.max(0.1, Number(comp.properties?.ratedCurrent ?? 2.0));
    const loadPercent = Math.max(0, Math.min(100, Number(comp.properties?.loadPercent ?? 0)));

    // Derived electromechanical constants
    const omegaNoLoad = noLoadRpm * (2 * Math.PI / 60);
    const Ke = (nominalVoltage - noLoadCurrent * armatureResistance) / omegaNoLoad;
    const Kt = Ke;
    const b = (Kt * noLoadCurrent) / omegaNoLoad;

    const IstallNominal = nominalVoltage / armatureResistance;
    const TstallNominal = Kt * IstallNominal;
    const Tload = (loadPercent / 100) * TstallNominal;

    return {
      type: "motor_dc",
      id: comp.id,
      nominalVoltage,
      noLoadRpm,
      noLoadCurrent,
      armatureResistance,
      resistance: armatureResistance,
      conductance: 1 / armatureResistance,
      ratedCurrent,
      loadPercent,
      Ke,
      Kt,
      b,
      TstallNominal,
      Tload,
      limitationNotes: "Motor DC menggunakan steady-state electromechanical educational model. Startup transient, rotor inertia, armature inductance, commutation, temperature effects, saturation, and transient inrush evolution are not modeled.",
      evaluate(vTerminalSigned) {
        const vMag = Math.abs(vTerminalSigned);
        const sign = vTerminalSigned >= 0 ? 1 : -1;

        if (vMag < 1e-4) {
          return {
            type: "motor_dc",
            id: comp.id,
            terminalVoltage: 0,
            voltage: 0,
            current: 0,
            currentMagnitude: 0,
            backEmf: 0,
            backEmfMagnitude: 0,
            rpm: 0,
            rpmMagnitude: 0,
            rpmSigned: 0,
            angularSpeed: 0,
            angularSpeedMagnitude: 0,
            torque: 0,
            loadTorque: 0,
            direction: "CW",
            state: "OFF",
            electricalInputPower: 0,
            copperLoss: 0,
            backEmfPower: 0,
            mechanicalOutputPower: 0,
            frictionLoss: 0,
            power: 0,
            powerBalanceResidual: 0,
            electricalResidual: 0,
            warning: null
          };
        }

        // Steady-state angular speed: Te = Tload + b*omega
        // Kt*(vMag - Ke*omega)/Ra = Tload + b*omega
        // omega * (Kt*Ke/Ra + b) = Kt*vMag/Ra - Tload
        const numerator = (Kt * vMag / armatureResistance) - Tload;
        const denominator = (Kt * Ke / armatureResistance) + b;
        const omegaCalc = numerator / denominator;

        let omegaMag = 0;
        let isStall = false;

        if (omegaCalc <= 0) {
          omegaMag = 0;
          isStall = true;
        } else {
          omegaMag = omegaCalc;
        }

        const signedOmega = sign * omegaMag;
        const signedEb = Ke * signedOmega; // Signed Back-EMF (+ for CW, - for CCW)
        const signedCurrent = (vTerminalSigned - signedEb) / armatureResistance;
        const iMag = Math.abs(signedCurrent);
        const Te = Kt * iMag;
        const rpmMag = Math.round(omegaMag * (60 / (2 * Math.PI)));
        const rpmSigned = Math.round(signedOmega * (60 / (2 * Math.PI)));
        const direction = sign >= 0 ? "CW" : "CCW";

        // Power breakdown
        const electricalInputPower = vMag * iMag;
        const copperLoss = iMag * iMag * armatureResistance;
        const backEmfPower = Math.abs(signedEb * signedCurrent);
        const mechanicalOutputPower = Tload * omegaMag;
        const frictionLoss = b * omegaMag * omegaMag;
        const powerBalanceResidual = Math.abs(electricalInputPower - (copperLoss + mechanicalOutputPower + frictionLoss));
        const electricalResidual = Math.abs(vTerminalSigned - (signedCurrent * armatureResistance + signedEb));

        // State priority: STALL > OVERLOAD > RUNNING
        let state = "RUNNING";
        let warning = null;

        if (isStall) {
          state = "STALL";
          warning = "Motor Stall / High Current Overload";
        } else if (iMag > ratedCurrent) {
          state = "OVERLOAD";
          warning = "Motor Overload";
        }

        return {
          type: "motor_dc",
          id: comp.id,
          terminalVoltage: vTerminalSigned,
          voltage: vMag,
          current: signedCurrent,
          currentMagnitude: iMag,
          backEmf: signedEb,
          backEmfMagnitude: Math.abs(signedEb),
          rpm: rpmMag,
          rpmMagnitude: rpmMag,
          rpmSigned,
          angularSpeed: signedOmega,
          angularSpeedMagnitude: omegaMag,
          torque: Te,
          loadTorque: Tload,
          direction,
          state,
          electricalInputPower,
          copperLoss,
          backEmfPower,
          mechanicalOutputPower,
          frictionLoss,
          power: electricalInputPower,
          powerBalanceResidual,
          electricalResidual,
          warning
        };
      }
    };
  }

  /**
   * Switch Model: Closed (0.0001 ohm), Open (10^9 ohm)
   */
  static getSwitchModel(comp) {
    const isClosed = Boolean(comp.properties?.isClosed);
    const resistance = isClosed ? 0.0001 : 1e9;
    return {
      type: "switch_spst",
      id: comp.id,
      isClosed,
      resistance,
      conductance: 1 / resistance
    };
  }

  /**
   * Diode Model: Forward threshold Vf (0.7V for 1N4007)
   */
  static getDiodeModel(comp) {
    const forwardVoltage = Math.max(0.1, Number(comp.properties?.forwardVoltage ?? 0.7));
    return {
      type: "diode",
      id: comp.id,
      forwardVoltage,
      evaluate(anodeVoltage, cathodeVoltage, current = 0) {
        const vDiff = anodeVoltage - cathodeVoltage;
        const isForward = current > 1e-6 || vDiff >= forwardVoltage;
        return {
          state: isForward ? "FORWARD_BIAS" : "REVERSE_BIAS",
          voltage: isForward ? forwardVoltage : Math.max(0, -vDiff),
          current: isForward ? current : 0,
          power: (isForward ? forwardVoltage : 0) * current
        };
      }
    };
  }

  /**
   * LED Model: Forward threshold Vf (2.0V), nominalCurrent (20 mA), maxContinuousCurrent (25 mA)
   * 
   * Catatan Fisika & Desain Edukasi:
   * LED menggunakan simplified fixed forward-voltage model untuk pembelajaran,
   * bukan model Shockley / temperature-dependent.
   */
  static getLEDModel(comp) {
    const forwardVoltage = Math.max(0.5, Number(comp.properties?.forwardVoltage ?? 2.0));
    const nominalCurrent = Math.max(0.001, Number(comp.properties?.nominalCurrent ?? 0.020));
    const maxContinuousCurrent = Math.max(0.005, Number(comp.properties?.maxContinuousCurrent ?? comp.properties?.maxCurrent ?? 0.025));

    return {
      type: "led",
      id: comp.id,
      forwardVoltage,
      nominalCurrent,
      maxContinuousCurrent,
      maxCurrent: maxContinuousCurrent,
      evaluate(anodeVoltage, cathodeVoltage, current = 0) {
        const vDiff = anodeVoltage - cathodeVoltage; // Signed V_anode - V_cathode
        const isLit = current > 1e-4 && vDiff >= forwardVoltage * 0.9;
        const isOvercurrent = current > maxContinuousCurrent;
        const power = (isLit ? forwardVoltage : Math.max(0, vDiff)) * current;
        return {
          isLit,
          current,
          voltage: isLit ? forwardVoltage : vDiff, // Signed Vd
          power,
          isOvercurrent,
          warning: isOvercurrent ? "LED Overcurrent / Current-Limiting Resistor Required" : null
        };
      }
    };
  }

  /**
   * Capacitor (DC Steady-State: Open Circuit 10^9 ohm)
   */
  static getCapacitorModel(comp) {
    const capacitance = Math.max(1e-12, Number(comp.properties?.capacitance ?? 100e-6));
    return {
      type: "capacitor",
      id: comp.id,
      capacitance,
      resistance: 1e9, // DC steady state
      conductance: 1e-9
    };
  }

  /**
   * Inductor (DC Steady-State: Short Circuit 0.0001 ohm)
   */
  static getInductorModel(comp) {
    const inductance = Math.max(1e-9, Number(comp.properties?.inductance ?? 10e-3));
    return {
      type: "inductor",
      id: comp.id,
      inductance,
      resistance: 0.0001, // DC steady state
      conductance: 10000
    };
  }
}
