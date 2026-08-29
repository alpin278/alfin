/**
 * DTE VirtualLab V2 — Smart Numbering Engine (Official Green Circle Badges)
 */

import { stateManager } from "./state.js";

export class SmartNumberingEngine {
  constructor() {
    this.layer = document.getElementById("components-layer");
  }

  init() {
    stateManager.subscribe("connections", () => this.calculateAndRender());
    stateManager.subscribe("components", () => this.calculateAndRender());
    stateManager.subscribe("components_moving", () => {});
  }

  calculateAndRender() {
    document.querySelectorAll(".smart-number-badge").forEach(b => b.remove());

    const state = stateManager.getState();
    if (!state.connections.length) return;

    let currentNumber = 1;
    const terminalNumberMap = new Map();

    state.connections.forEach(conn => {
      if (conn.from?.componentId && conn.from?.terminalId) {
        const keyFrom = `${conn.from.componentId}:${conn.from.terminalId}`;
        if (!terminalNumberMap.has(keyFrom)) {
          terminalNumberMap.set(keyFrom, currentNumber++);
        }
      }
      if (!conn.to?.isHanging && conn.to?.componentId && conn.to?.terminalId) {
        const keyTo = `${conn.to.componentId}:${conn.to.terminalId}`;
        if (!terminalNumberMap.has(keyTo)) {
          terminalNumberMap.set(keyTo, currentNumber++);
        }
      }
    });

    terminalNumberMap.forEach((num, key) => {
      const [compId, termId] = key.split(":");
      const termEl = document.getElementById(`term-${compId}-${termId}`);
      if (termEl) {
        const badge = document.createElement("div");
        badge.className = "smart-number-badge";
        badge.id = `badge-${compId}-${termId}`;
        badge.textContent = num;
        termEl.appendChild(badge);
      }
    });
  }
}
