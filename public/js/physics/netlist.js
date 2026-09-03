/**
 * FLUXUS / DTE VirtualLab — Electrical Netlist & Topology Graph Builder
 * Pure mathematical representation mapping components, terminals, and connections into electrical nodes.
 * Completely independent of screen positions (X/Y) and DOM rendering.
 */

export class UnionFind {
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

export class NetlistBuilder {
  /**
   * Builds an electrical netlist from the current state components and connections.
   * 
   * @param {Array} components - Array of workspace components
   * @param {Array} connections - Array of wire connections
   * @returns {Object} { uf, nets, netToIndex, groundNet, groundIndex, numNodes }
   */
  static build(components = [], connections = []) {
    const uf = new UnionFind();

    // 1. Group wires and terminal connections into equipotential nets
    connections.forEach(conn => {
      if (conn.id) {
        if (conn.from?.componentId && conn.from?.terminalId) {
          uf.union(conn.id, `${conn.from.componentId}:${conn.from.terminalId}`);
        }
        if (!conn.to?.isHanging && conn.to?.componentId && conn.to?.terminalId) {
          uf.union(conn.id, `${conn.to.componentId}:${conn.to.terminalId}`);
        }
        if (conn.to?.isWireBranch && conn.to?.targetWireId) {
          uf.union(conn.id, conn.to.targetWireId);
        }
        if (conn.from?.isWireBranch && conn.from?.targetWireId) {
          uf.union(conn.id, conn.from.targetWireId);
        }
      }
      if (!conn.to?.isHanging && conn.from?.componentId && conn.to?.componentId) {
        uf.union(`${conn.from.componentId}:${conn.from.terminalId}`, `${conn.to.componentId}:${conn.to.terminalId}`);
      }
    });

    // 2. Closed switches with ideal zero contact resistance merge terminals into the same net
    components.forEach(comp => {
      if (comp.type === "switch_spst" && comp.properties?.isClosed && comp.terminals?.length >= 2) {
        uf.union(`${comp.id}:${comp.terminals[0].id}`, `${comp.id}:${comp.terminals[1].id}`);
      }
    });

    // 3. Collect all unique active nets across all component terminals and connections
    const allNetsSet = new Set();

    components.forEach(c => {
      if (c.terminals) {
        c.terminals.forEach(t => allNetsSet.add(uf.find(`${c.id}:${t.id}`)));
      }
      if (c.type === "multimeter") {
        allNetsSet.add(uf.find(`${c.id}:term_com`));
        allNetsSet.add(uf.find(`${c.id}:term_vwma`));
        if (c.properties?.probes?.com?.attachedTo) {
          allNetsSet.add(uf.find(`${c.properties.probes.com.attachedTo.compId}:${c.properties.probes.com.attachedTo.termId}`));
        }
        if (c.properties?.probes?.vwma?.attachedTo) {
          allNetsSet.add(uf.find(`${c.properties.probes.vwma.attachedTo.compId}:${c.properties.probes.vwma.attachedTo.termId}`));
        }
      }
    });

    connections.forEach(c => {
      if (c.from?.componentId && c.from?.terminalId) allNetsSet.add(uf.find(`${c.from.componentId}:${c.from.terminalId}`));
      if (!c.to?.isHanging && c.to?.componentId && c.to?.terminalId) allNetsSet.add(uf.find(`${c.to.componentId}:${c.to.terminalId}`));
    });

    // 4. Identify Ground Reference Net (Node 0)
    // Default to the negative terminal of the primary battery or power supply
    let groundNet = null;
    const battery = components.find(c => c.type === "battery" || c.type === "power_supply");
    if (battery) {
      groundNet = uf.find(`${battery.id}:term_neg`);
    } else if (allNetsSet.size > 0) {
      groundNet = Array.from(allNetsSet)[0];
    }

    if (groundNet) {
      allNetsSet.add(groundNet);
    }

    // Node 0 must always be the ground reference net
    const otherNets = Array.from(allNetsSet).filter(n => n !== groundNet);
    const nets = groundNet ? [groundNet, ...otherNets] : otherNets;

    const netToIndex = new Map();
    nets.forEach((net, idx) => netToIndex.set(net, idx));

    return {
      uf,
      nets,
      netToIndex,
      groundNet,
      groundIndex: groundNet ? 0 : -1,
      numNodes: nets.length
    };
  }
}
