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
   * Motor DC Model: R = V_rated^2 / P_rated
   * Dynamic RPM proportional to voltage ratio
   */
  static getMotorModel(comp) {
    const nominalVoltage = Math.max(1, Number(comp.properties?.nominalVoltage ?? 12));
    const powerRating = Math.max(1, Number(comp.properties?.powerRating ?? 24));
    const maxRpm = Math.max(100, Number(comp.properties?.maxRpm ?? 3000));
    const nominalResistance = Number(comp.properties?.resistance) || ((nominalVoltage * nominalVoltage) / powerRating);
    const resistance = Math.max(0.001, Number(nominalResistance.toFixed(3)));

    return {
      type: "motor_dc",
      id: comp.id,
      nominalVoltage,
      powerRating,
      maxRpm,
      resistance,
      conductance: 1 / resistance,
      evaluate(vDiff) {
        const v = Math.abs(vDiff);
        const i = v / resistance;
        const p = v * i;
        const speedRatio = Math.min(2.0, v / nominalVoltage);
        const actualRpm = Math.round(maxRpm * speedRatio);
        return { voltage: v, current: i, power: p, rpm: actualRpm };
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
      getConductance(anodeVoltage, cathodeVoltage) {
        const vDiff = anodeVoltage - cathodeVoltage;
        if (vDiff >= forwardVoltage) {
          return { conductance: 10, resistance: 0.1, state: "FORWARD_BIAS" }; // 0.1 ohm dynamic
        } else {
          return { conductance: 1e-8, resistance: 1e8, state: "REVERSE_BIAS" }; // 100 MOhm reverse
        }
      }
    };
  }

  /**
   * LED Model: Vf (2.0V), forward series resistor (10 ohm), I_max (25 mA)
   */
  static getLEDModel(comp) {
    const forwardVoltage = Math.max(1.0, Number(comp.properties?.forwardVoltage ?? 2.0));
    const maxCurrent = Math.max(0.005, Number(comp.properties?.maxCurrent ?? 0.025));
    const resistance = Math.max(1, Number(comp.properties?.resistance ?? 10));

    return {
      type: "led",
      id: comp.id,
      forwardVoltage,
      maxCurrent,
      resistance,
      evaluate(anodeVoltage, cathodeVoltage) {
        const vDiff = anodeVoltage - cathodeVoltage;
        if (vDiff > forwardVoltage) {
          const current = (vDiff - forwardVoltage) / resistance;
          const isOvercurrent = current > maxCurrent;
          const power = vDiff * current;
          return { isLit: true, current, voltage: vDiff, power, isOvercurrent };
        }
        return { isLit: false, current: 0, voltage: Math.max(0, vDiff), power: 0, isOvercurrent: false };
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
