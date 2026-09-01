/**
 * DTE VirtualLab V2 — Main Application Controller (Fully Featured with Component Rotation)
 */

import { stateManager } from "./state.js";
import { WorkspaceEngine } from "./workspace.js";
import { ComponentEngine } from "./components.js";
import { ConnectionEngine } from "./connections.js";
import { SmartNumberingEngine } from "./numbering.js";
import { SimulationEngine } from "./simulation.js";

class DTEVirtualLabApp {
  constructor() {
    this.workspaceEngine = null;
    this.componentEngine = null;
    this.connectionEngine = null;
    this.numberingEngine = null;
    this.simulationEngine = null;
  }

  init() {
    console.log("⚡ DTE VirtualLab — Bootstrapping...");

    this.workspaceEngine = new WorkspaceEngine();
    this.workspaceEngine.init();

    this.componentEngine = new ComponentEngine(this.workspaceEngine);
    this.componentEngine.init();
    window.componentEngine = this.componentEngine;

    this.connectionEngine = new ConnectionEngine(this.workspaceEngine);
    this.connectionEngine.init();

    this.workspaceEngine.connectionEngine = this.connectionEngine;
    this.workspaceEngine.componentEngine = this.componentEngine;

    this.numberingEngine = new SmartNumberingEngine();
    this.numberingEngine.init();

    this.simulationEngine = new SimulationEngine();
    this.simulationEngine.init();

    this.bindHeaderControls();
    this.bindBottomToolbar();
    this.bindFloatingMultimeter();
    this.bindCaseStudies();
    this.bindMobileNavigation();

    // Check if opened with case_study_id or legacy case parameter
    const urlParams = new URLSearchParams(window.location.search);
    const caseStudyId = urlParams.get("case_study_id");
    const caseParam = urlParams.get("case");

    if (caseStudyId) {
      setTimeout(() => {
        this.loadCaseStudyFromAPI(caseStudyId);
      }, 100);
    } else if (caseParam) {
      setTimeout(() => {
        this.resetCanvas();
        if (caseParam === "1") this.loadPrebuiltCase1();
        else if (caseParam === "2") this.loadPrebuiltCase2();
        else if (caseParam === "3") this.loadPrebuiltCase3();
        else if (caseParam === "4") this.loadPrebuiltCase4();
      }, 120);
    } else {
      // Mode Simulasi Bebas: Wajib mulai dari kondisi kanvas KOSONG bersih
      this.resetCanvas();
    }

    // Handle contextual back button strictly based on URL parameters
    const btnBackNav = document.getElementById("btn-back-nav");
    if (btnBackNav) {
      const fromParam = urlParams.get("from");
      if (fromParam === "materi") {
        btnBackNav.href = "/materi";
        btnBackNav.title = "Kembali ke Materi Pembelajaran";
      } else if (fromParam === "studi-kasus" || caseStudyId) {
        btnBackNav.href = "/studi-kasus";
        btnBackNav.title = "Kembali ke Daftar Studi Kasus";
      } else {
        btnBackNav.href = "/beranda";
        btnBackNav.title = "Kembali ke Beranda / Dasbor";
      }
    }

    // Handle Back/Forward Cache (BFCache) saat user berpindah halaman via tombol Back / navbar
    window.addEventListener("pageshow", () => {
      const currentParams = new URLSearchParams(window.location.search);
      if (!currentParams.get("case_study_id") && !currentParams.get("case")) {
        this.resetCanvas();
      }
    });

    console.log("✅ DTE VirtualLab — Production Ready with Rotation.");
  }

  /**
   * Mengosongkan total kanvas, komponen, kabel, status simulasi, dan modal misi
   */
  resetCanvas() {
    stateManager.clearWorkspace();

    const compLayer = document.getElementById("components-layer");
    if (compLayer) compLayer.innerHTML = "";
    const wiresGroup = document.getElementById("wires-group");
    if (wiresGroup) wiresGroup.innerHTML = "";

    const state = stateManager.getState();
    state.simulation.running = false;
    state.simulation.status = "STANDBY";
    state.simulation.metrics = { totalVoltage: 0, totalCurrent: 0, totalPower: 0 };
    state.simulation.message = "Rangkaian siap disimulasikan.";

    const existingMission = document.getElementById("case-mission-modal");
    if (existingMission) existingMission.remove();

    stateManager.notify("simulation");
    stateManager.notify("components");
    stateManager.notify("connections");
    stateManager.notify("selection");
    stateManager.notify("workspace");
  }

  bindHeaderControls() {
    const btnUndo = document.getElementById("btn-undo");
    const btnRedo = document.getElementById("btn-redo");
    if (btnUndo) btnUndo.addEventListener("click", () => stateManager.undo());
    if (btnRedo) btnRedo.addEventListener("click", () => stateManager.redo());

    const btnZoomPlus = document.getElementById("btn-zoom-plus");
    const btnZoomMinus = document.getElementById("btn-zoom-minus");
    const btnFitScreen = document.getElementById("btn-fit-screen");

    if (btnZoomPlus) {
      btnZoomPlus.addEventListener("click", () => {
        const ws = stateManager.getState().workspace;
        this.workspaceEngine.applyZoomAt(window.innerWidth / 2, window.innerHeight / 2, ws.zoom * 1.15);
      });
    }

    if (btnZoomMinus) {
      btnZoomMinus.addEventListener("click", () => {
        const ws = stateManager.getState().workspace;
        this.workspaceEngine.applyZoomAt(window.innerWidth / 2, window.innerHeight / 2, ws.zoom / 1.15);
      });
    }

    if (btnFitScreen) {
      btnFitScreen.addEventListener("click", () => {
        stateManager.resetWorkspace();
      });
    }

    const btnResetCircuit = document.getElementById("btn-reset-circuit");
    if (btnResetCircuit) {
      btnResetCircuit.addEventListener("click", () => {
        if (confirm("Kosongkan seluruh rangkaian pada workspace?")) {
          stateManager.clearWorkspace();
        }
      });
    }

    const btnSaveAsCase = document.getElementById("btn-save-as-case");
    if (btnSaveAsCase) {
      btnSaveAsCase.addEventListener("click", () => {
        this.openSaveCaseModal();
      });
    }

    const btnRotateComponent = document.getElementById("btn-rotate-component");
    if (btnRotateComponent) {
      btnRotateComponent.addEventListener("click", () => {
        const state = stateManager.getState();
        if (state.selection?.type === "component" && state.selection?.id) {
          stateManager.rotateComponent(state.selection.id, 90);
        }
      });
    }

    const btnDeleteComponent = document.getElementById("btn-delete-component");
    if (btnDeleteComponent) {
      btnDeleteComponent.addEventListener("click", () => {
        const state = stateManager.getState();
        if (state.selection?.type === "component" && state.selection?.id) {
          stateManager.deleteComponent(state.selection.id);
        } else if (state.selection?.type === "connection" && state.selection?.id) {
          this.connectionEngine.deleteConnection(state.selection.id);
        }
      });
    }

    const updateActionButtons = (selection) => {
      const isCompSelected = selection?.type === "component" && !!selection?.id;
      const isConnSelected = selection?.type === "connection" && !!selection?.id;

      if (btnRotateComponent) {
        btnRotateComponent.disabled = !isCompSelected;
      }
      if (btnDeleteComponent) {
        btnDeleteComponent.disabled = !(isCompSelected || isConnSelected);
      }
      const toolDelete = document.getElementById("tool-delete");
      if (toolDelete) {
        toolDelete.disabled = !(isCompSelected || isConnSelected);
      }
    };

    stateManager.subscribe("selection", (selection) => {
      updateActionButtons(selection);
    });

    stateManager.subscribe("components", () => {
      updateActionButtons(stateManager.getState().selection);
    });

    updateActionButtons(stateManager.getState().selection);

    const btnScreenshot = document.getElementById("btn-screenshot");
    if (btnScreenshot) {
      btnScreenshot.addEventListener("click", () => {
        const state = stateManager.getState();
        const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify({
          version: state.version,
          components: state.components,
          connections: state.connections,
          timestamp: new Date().toISOString()
        }, null, 2));

        const downloadAnchor = document.createElement("a");
        downloadAnchor.setAttribute("href", dataStr);
        downloadAnchor.setAttribute("download", `dte-virtuallab-circuit-${Date.now()}.json`);
        document.body.appendChild(downloadAnchor);
        downloadAnchor.click();
        downloadAnchor.remove();

        alert("💾 Data skematik rangkaian berhasil di-export ke format JSON!");
      });
    }

    const btnHelp = document.getElementById("btn-help");
    if (btnHelp) {
      btnHelp.addEventListener("click", () => {
        alert("💡 Panduan Praktikum Fluxus:\n\n1. Tarik komponen ke workspace.\n2. Klik pin untuk menyambung kabel (Siku 90° Proteus).\n3. Klik di tengah kabel untuk membuat cabang paralel (Junction Dot).\n4. Tekan tombol 'R' atau klik 🔄 Putar untuk memutar komponen 90°.\n5. Klik saklar untuk ON/OFF.");
      });
    }
  }

  bindBottomToolbar() {
    const btnBottomRunSim = document.getElementById("btn-bottom-run-sim");
    if (btnBottomRunSim) {
      btnBottomRunSim.addEventListener("click", () => {
        const state = stateManager.getState();
        const nextState = !state.simulation.running;
        state.simulation.running = nextState;
        stateManager.notify("simulation");
      });

      stateManager.subscribe("simulation", (sim) => {
        const isRunning = sim.running;
        if (isRunning) {
          btnBottomRunSim.classList.add("running");
          btnBottomRunSim.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <rect x="6" y="4" width="4" height="16"></rect>
              <rect x="14" y="4" width="4" height="16"></rect>
            </svg>
          `;
          btnBottomRunSim.title = "Hentikan Simulasi (Sedang Berjalan)";
        } else {
          btnBottomRunSim.classList.remove("running");
          btnBottomRunSim.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
          `;
          btnBottomRunSim.title = "Jalankan Simulasi (Mati / Standby)";
        }
      });
    }

    const toolUndo = document.getElementById("tool-undo");
    const toolRedo = document.getElementById("tool-redo");
    const toolSelect = document.getElementById("tool-select");
    const toolPan = document.getElementById("tool-pan");
    const toolRotate = document.getElementById("tool-rotate");
    const toolZoomIn = document.getElementById("tool-zoom-in");
    const toolZoomOut = document.getElementById("tool-zoom-out");
    const toolFit = document.getElementById("tool-fit");
    const toolDelete = document.getElementById("tool-delete");

    if (toolUndo) toolUndo.addEventListener("click", () => stateManager.undo());
    if (toolRedo) toolRedo.addEventListener("click", () => stateManager.redo());

    const setToolActive = (activeBtn) => {
      [toolSelect, toolPan].forEach(b => b?.classList.remove("active"));
      activeBtn?.classList.add("active");
    };

    if (toolSelect) toolSelect.addEventListener("click", () => setToolActive(toolSelect));
    if (toolPan) toolPan.addEventListener("click", () => setToolActive(toolPan));

    if (toolRotate) {
      toolRotate.addEventListener("click", () => {
        const state = stateManager.getState();
        if (state.selection.type === "component" && state.selection.id) {
          stateManager.rotateComponent(state.selection.id, 90);
        } else {
          alert("Pilih komponen terlebih dahulu lalu klik Putar (atau tekan tombol R di keyboard).");
        }
      });
    }

    if (toolZoomIn) {
      toolZoomIn.addEventListener("click", () => {
        const ws = stateManager.getState().workspace;
        this.workspaceEngine.applyZoomAt(window.innerWidth / 2, window.innerHeight / 2, ws.zoom * 1.2);
      });
    }

    if (toolZoomOut) {
      toolZoomOut.addEventListener("click", () => {
        const ws = stateManager.getState().workspace;
        this.workspaceEngine.applyZoomAt(window.innerWidth / 2, window.innerHeight / 2, ws.zoom / 1.2);
      });
    }

    if (toolFit) {
      toolFit.addEventListener("click", () => {
        stateManager.resetWorkspace();
      });
    }

    if (toolDelete) {
      toolDelete.addEventListener("click", () => {
        const state = stateManager.getState();
        if (state.selection.type === "component" && state.selection.id) {
          stateManager.deleteComponent(state.selection.id);
        } else if (state.selection.type === "connection" && state.selection.id) {
          this.connectionEngine.deleteConnection(state.selection.id);
        }
      });
    }
  }

  bindFloatingMultimeter() {
    const meter = document.getElementById("virtual-multimeter");
    const dragHandle = document.getElementById("multimeter-drag-handle");
    const btnClose = document.getElementById("btn-close-meter");

    if (btnClose && meter) {
      btnClose.addEventListener("click", () => {
        meter.style.display = "none";
      });
    }

    if (dragHandle && meter) {
      let isDragging = false;
      let startX = 0, startY = 0;
      let initLeft = 0, initTop = 0;

      dragHandle.addEventListener("pointerdown", (e) => {
        if (e.target.tagName === "BUTTON") return;
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        const rect = meter.getBoundingClientRect();
        initLeft = rect.left;
        initTop = rect.top;
        dragHandle.setPointerCapture(e.pointerId);
      });

      window.addEventListener("pointermove", (e) => {
        if (!isDragging) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        meter.style.left = `${initLeft + dx}px`;
        meter.style.top = `${initTop + dy}px`;
        meter.style.right = "auto";
      });

      window.addEventListener("pointerup", () => {
        isDragging = false;
      });
    }
  }

  bindCaseStudies() {
    const btnCases = document.getElementById("btn-cases");
    if (!btnCases) return;

    btnCases.addEventListener("click", () => {
      const backdrop = document.createElement("div");
      backdrop.className = "quick-edit-modal-backdrop";

      backdrop.innerHTML = `
        <div class="quick-edit-modal" style="max-width: 500px;">
          <div class="modal-header">
            <div class="modal-title"><span>📖</span> Modul Praktikum Studi Kasus DTE</div>
            <button class="btn-icon" id="case-close-btn">✕</button>
          </div>
          <div class="modal-body" style="gap: 10px;">
            <div class="case-card" style="background: #132238; border: 1px solid #1e3352; border-radius: 8px; padding: 12px; cursor: pointer;" id="case-1-btn">
              <div style="font-weight: 700; color: #38bdf8; font-size: 0.9rem;">Studi Kasus 01: Rangkaian Lampu Sederhana (Seri)</div>
              <p style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Hubungkan Baterai 12V, Saklar SPST, dan Lampu Pijar. Jalankan simulasi untuk menyalakan lampu.</p>
              <button class="btn-primary" style="margin-top: 8px; height: 28px; font-size: 0.75rem;">Load Rangkaian Contoh</button>
            </div>

            <div class="case-card" style="background: #132238; border: 1px solid #1e3352; border-radius: 8px; padding: 12px; cursor: pointer;" id="case-2-btn">
              <div style="font-weight: 700; color: #facc15; font-size: 0.9rem;">Studi Kasus 02: Hukum Ohm & Pengaruh Resistor</div>
              <p style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Pasang Resistor 220Ω secara seri dengan Lampu untuk menganalisis pembagian tegangan.</p>
              <button class="btn-primary" style="margin-top: 8px; height: 28px; font-size: 0.75rem;">Load Rangkaian Contoh</button>
            </div>

            <div class="case-card" style="background: #132238; border: 1px solid #1e3352; border-radius: 8px; padding: 12px; cursor: pointer;" id="case-3-btn">
              <div style="font-weight: 700; color: #34d399; font-size: 0.9rem;">Studi Kasus 03: Rangkaian Paralel 2 Lampu (Cabang Junction)</div>
              <p style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">Hubungkan 2 Lampu secara paralel dengan Titik Simpul Percabangan. Kedua lampu menyala terang 12V penuh.</p>
              <button class="btn-primary" style="margin-top: 8px; height: 28px; font-size: 0.75rem;">Load Rangkaian Contoh</button>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" id="case-cancel-btn">Tutup</button>
          </div>
        </div>
      `;

      document.body.appendChild(backdrop);

      const close = () => backdrop.remove();
      backdrop.querySelector("#case-close-btn").addEventListener("click", close);
      backdrop.querySelector("#case-cancel-btn").addEventListener("click", close);

      backdrop.querySelector("#case-1-btn").addEventListener("click", () => {
        stateManager.clearWorkspace();
        this.loadPrebuiltCase1();
        close();
      });

      backdrop.querySelector("#case-2-btn").addEventListener("click", () => {
        stateManager.clearWorkspace();
        this.loadPrebuiltCase2();
        close();
      });

      backdrop.querySelector("#case-3-btn").addEventListener("click", () => {
        stateManager.clearWorkspace();
        this.loadPrebuiltCase3();
        close();
      });

      backdrop.querySelector("#case-4-btn").addEventListener("click", () => {
        stateManager.clearWorkspace();
        this.loadPrebuiltCase4();
        close();
      });
    });
  }

  showCaseMissionModal(caseNum, title, problem, goal) {
    const existing = document.getElementById("case-mission-modal");
    if (existing) existing.remove();

    const modal = document.createElement("div");
    modal.id = "case-mission-modal";
    modal.style.cssText = `
      position: fixed;
      top: 75px;
      left: 50%;
      transform: translateX(-50%);
      background: #0f172a;
      border: 2px solid #38bdf8;
      border-radius: 12px;
      padding: 16px 20px;
      max-width: 520px;
      width: 90%;
      z-index: 2000;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
      color: #f8fafc;
      font-family: var(--font-sans, sans-serif);
    `;

    modal.innerHTML = `
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
        <span style="font-weight: 700; color: #38bdf8; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
          MISI STUDI KASUS 0${caseNum}
        </span>
        <button style="background: none; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer; padding: 2px;" onclick="document.getElementById('case-mission-modal').remove()">✕</button>
      </div>
      <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 6px; color: #ffffff;">${title}</h3>
      <p style="font-size: 0.84rem; color: #cbd5e1; line-height: 1.5; margin-bottom: 8px;"><strong>Kondisi Awal:</strong> ${problem}</p>
      <div style="background: rgba(56, 189, 248, 0.1); border-left: 3px solid #38bdf8; padding: 8px 12px; border-radius: 4px; font-size: 0.82rem; color: #93c5fd; margin-bottom: 12px; line-height: 1.5;">
        <strong>Tugas Siswa:</strong> ${goal}
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 8px;">
        <button style="min-height: 38px; background: #0284c7; border: none; color: #ffffff; padding: 0 16px; border-radius: 6px; font-size: 0.84rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" onclick="document.getElementById('case-mission-modal').remove()">
          <span>Mulai Praktik</span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
        </button>
      </div>
    `;

    document.body.appendChild(modal);
  }

  loadPrebuiltCase1() {
    this.componentEngine.createComponent("battery", 200, 220);
    this.componentEngine.createComponent("lamp", 440, 140);
    this.componentEngine.createComponent("switch_spst", 440, 320);

    setTimeout(() => {
      const state = stateManager.getState();
      const b = state.components.find(c => c.type === "battery");
      const l = state.components.find(c => c.type === "lamp");
      const s = state.components.find(c => c.type === "switch_spst");

      if (b && l && s) {
        // Explicitly set switch to OFF (open circuit) so the lamp is truly NOT lit!
        s.properties.isClosed = false;
        const switchEl = document.getElementById(`comp-${s.id}`);
        if (switchEl) this.componentEngine.updateComponentVisualProperties(switchEl, s);

        this.connectionEngine.createConnection({ componentId: b.id, terminalId: "term_pos" }, { componentId: l.id, terminalId: "term_pos" });
        this.connectionEngine.createConnection({ componentId: l.id, terminalId: "term_neg" }, { componentId: s.id, terminalId: "term_2" });
        this.connectionEngine.createConnection({ componentId: s.id, terminalId: "term_1" }, { componentId: b.id, terminalId: "term_neg" });
      }

      state.simulation.running = true;
      stateManager.notify("simulation");

      this.showCaseMissionModal(
        1,
        "Lampu Tidak Menyala",
        "Rangkaian lampu telah terhubung ke baterai 12V dan saklar, tetapi lampu saat ini MATI.",
        "Analisis sirkuit. Klik Saklar Rocker untuk mengubah posisinya menjadi ON (tutup sirkuit) agar lampu menyala!"
      );
    }, 60);
  }

  loadPrebuiltCase2() {
    this.componentEngine.createComponent("battery", 180, 220);
    this.componentEngine.createComponent("lamp", 380, 140);
    this.componentEngine.createComponent("resistor", 580, 220);
    this.componentEngine.createComponent("switch_spst", 380, 320);

    setTimeout(() => {
      const state = stateManager.getState();
      const b = state.components.find(c => c.type === "battery");
      const l = state.components.find(c => c.type === "lamp");
      const r = state.components.find(c => c.type === "resistor");
      const s = state.components.find(c => c.type === "switch_spst");

      if (b && l && r && s) {
        s.properties.isClosed = true;
        const switchEl = document.getElementById(`comp-${s.id}`);
        if (switchEl) this.componentEngine.updateComponentVisualProperties(switchEl, s);

        this.connectionEngine.createConnection({ componentId: b.id, terminalId: "term_pos" }, { componentId: l.id, terminalId: "term_pos" });
        this.connectionEngine.createConnection({ componentId: l.id, terminalId: "term_neg" }, { componentId: r.id, terminalId: "term_a" });
        this.connectionEngine.createConnection({ componentId: r.id, terminalId: "term_b" }, { componentId: s.id, terminalId: "term_2" });
        this.connectionEngine.createConnection({ componentId: s.id, terminalId: "term_1" }, { componentId: b.id, terminalId: "term_neg" });
      }

      state.simulation.running = true;
      stateManager.notify("simulation");

      this.showCaseMissionModal(
        2,
        "Menentukan Nilai Resistor & Pembagian Tegangan",
        "Lampu menyala sangat redup karena terhambat oleh Resistor 220Ω yang terpasang seri.",
        "Dobel klik Resistor untuk mengubah nilai hambatannya (misal menjadi 10Ω atau 20Ω), lalu amati perubahan nyala terang lampu!"
      );
    }, 60);
  }

  loadPrebuiltCase3() {
    this.componentEngine.createComponent("battery", 160, 240);
    this.componentEngine.createComponent("switch_spst", 360, 120);
    this.componentEngine.createComponent("lamp", 560, 160);
    this.componentEngine.createComponent("lamp", 560, 320);

    setTimeout(() => {
      const state = stateManager.getState();
      const b = state.components.find(c => c.type === "battery");
      const s = state.components.find(c => c.type === "switch_spst");
      const lamps = state.components.filter(c => c.type === "lamp");

      if (b && s && lamps.length >= 2) {
        s.properties.isClosed = true;
        const switchEl = document.getElementById(`comp-${s.id}`);
        if (switchEl) this.componentEngine.updateComponentVisualProperties(switchEl, s);

        const l1 = lamps[0];
        const l2 = lamps[1];

        this.connectionEngine.createConnection({ componentId: b.id, terminalId: "term_pos" }, { componentId: s.id, terminalId: "term_1" });
        this.connectionEngine.createConnection({ componentId: s.id, terminalId: "term_2" }, { componentId: l1.id, terminalId: "term_pos" });
        this.connectionEngine.createConnection({ componentId: s.id, terminalId: "term_2" }, { componentId: l2.id, terminalId: "term_pos" });
        this.connectionEngine.createConnection({ componentId: l1.id, terminalId: "term_neg" }, { componentId: b.id, terminalId: "term_neg" });
        this.connectionEngine.createConnection({ componentId: l2.id, terminalId: "term_neg" }, { componentId: b.id, terminalId: "term_neg" });
      }

      state.simulation.running = true;
      stateManager.notify("simulation");

      this.showCaseMissionModal(
        3,
        "Rangkaian Paralel 2 Beban Mandiri",
        "Dua lampu terhubung secara paralel pada satu sumber tegangan 12V.",
        "Amati bahwa kedua lampu menyala sama terang pada 12V penuh. Buktikan Hukum Kirchhoff KCL pada simpul percabangan!"
      );
    }, 60);
  }

  loadPrebuiltCase4() {
    this.componentEngine.createComponent("battery", 160, 240);
    this.componentEngine.createComponent("switch_spst", 340, 140);
    this.componentEngine.createComponent("diode", 520, 140);
    this.componentEngine.createComponent("motor_dc", 520, 320);

    setTimeout(() => {
      const state = stateManager.getState();
      const b = state.components.find(c => c.type === "battery");
      const s = state.components.find(c => c.type === "switch_spst");
      const d = state.components.find(c => c.type === "diode");
      const m = state.components.find(c => c.type === "motor_dc");

      if (b && s && d && m) {
        s.properties.isClosed = true;
        const switchEl = document.getElementById(`comp-${s.id}`);
        if (switchEl) this.componentEngine.updateComponentVisualProperties(switchEl, s);

        // Intentionally wire in REVERSE BIAS (Cathode to switch, Anode to motor) so motor is BLOCKED!
        this.connectionEngine.createConnection({ componentId: b.id, terminalId: "term_pos" }, { componentId: s.id, terminalId: "term_1" });
        this.connectionEngine.createConnection({ componentId: s.id, terminalId: "term_2" }, { componentId: d.id, terminalId: "term_cathode" });
        this.connectionEngine.createConnection({ componentId: d.id, terminalId: "term_anode" }, { componentId: m.id, terminalId: "term_pos" });
        this.connectionEngine.createConnection({ componentId: m.id, terminalId: "term_neg" }, { componentId: b.id, terminalId: "term_neg" });
      }

      state.simulation.running = true;
      stateManager.notify("simulation");

      this.showCaseMissionModal(
        4,
        "Dioda Semikonduktor — Motor Listrik Tidak Berputar",
        "Saklar sudah ON dan baterai 12V aktif, namun Motor Listrik DC sama sekali TIDAK berputar (0 RPM).",
        "Periksa polaritas Dioda 1N4007 (saat ini Bias Mundur/Terblokir). Hubungkan secara Bias Maju (Anoda ke +) agar motor berputar kencang!"
      );
    }, 60);
  }

  bindMobileNavigation() {
    const btnComponents = document.getElementById("nav-btn-components");
    const btnWires = document.getElementById("nav-btn-wires");
    const btnMeter = document.getElementById("nav-btn-meter");
    const btnSim = document.getElementById("nav-btn-sim");

    const setActiveNav = (targetBtn) => {
      document.querySelectorAll(".mobile-bottom-nav .nav-item").forEach(b => b.classList.remove("active"));
      targetBtn?.classList.add("active");
    };

    // 1. KOMPONEN: Open Mobile Bottom Sheet
    if (btnComponents) {
      btnComponents.addEventListener("click", () => {
        setActiveNav(btnComponents);
        this.openMobileComponentSheet();
      });
    }

    // 2. KABEL: Toast instruction
    if (btnWires) {
      btnWires.addEventListener("click", () => {
        setActiveNav(btnWires);
        const existing = document.getElementById("mobile-wire-toast");
        if (existing) existing.remove();

        const toast = document.createElement("div");
        toast.id = "mobile-wire-toast";
        toast.style.cssText = `
          position: fixed;
          bottom: 75px;
          left: 50%;
          transform: translateX(-50%);
          background: rgba(15, 23, 42, 0.95);
          color: #38bdf8;
          border: 1px solid #38bdf8;
          border-radius: 20px;
          padding: 8px 18px;
          font-size: 0.8rem;
          font-weight: 600;
          z-index: 2000;
          box-shadow: 0 4px 14px rgba(0,0,0,0.6);
          pointer-events: none;
          white-space: nowrap;
          animation: fadeIn 0.2s ease;
        `;
        toast.textContent = "Sentuh pin terminal komponen untuk menarik kabel!";
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
      });
    }

    // 3. ALAT UKUR: Add or focus Multimeter
    if (btnMeter) {
      btnMeter.addEventListener("click", () => {
        setActiveNav(btnMeter);
        const state = stateManager.getState();
        const existingMeter = state.components.find(c => c.type === "multimeter");
        if (existingMeter) {
          const el = document.getElementById(`comp-${existingMeter.id}`);
          if (el) {
            el.style.transform = "scale(1.15)";
            setTimeout(() => el.style.transform = "", 500);
          }
        } else {
          const ws = state.workspace;
          const centerX = Math.round((-ws.panX + window.innerWidth / 2) / ws.zoom) - 70;
          const centerY = Math.round((-ws.panY + window.innerHeight / 2) / ws.zoom) - 60;
          this.componentEngine.createComponent("multimeter", centerX, centerY);
        }
      });
    }

    // 4. SIMULASI: Toggle simulation with glowing green status
    if (btnSim) {
      btnSim.addEventListener("click", () => {
        const state = stateManager.getState();
        state.simulation.running = !state.simulation.running;
        stateManager.notify("simulation");
      });

      // Synchronize bottom bar button styling with simulation state
      stateManager.subscribe("simulation", (sim) => {
        const isRunning = sim.running;
        const textSpan = btnSim.querySelector("span:last-child");
        if (isRunning) {
          btnSim.classList.add("sim-running");
          if (textSpan) textSpan.textContent = "Simulasi (ON)";
        } else {
          btnSim.classList.remove("sim-running");
          if (textSpan) textSpan.textContent = "Simulasi";
        }
      });
    }
  }

  openMobileComponentSheet() {
    const existing = document.getElementById("mobile-comp-sheet");
    const existingBackdrop = document.getElementById("mobile-comp-backdrop");
    if (existing) {
      existing.remove();
      existingBackdrop?.remove();
      return;
    }

    const backdrop = document.createElement("div");
    backdrop.id = "mobile-comp-backdrop";
    backdrop.className = "mobile-sheet-backdrop";

    const sheet = document.createElement("div");
    sheet.id = "mobile-comp-sheet";
    sheet.className = "mobile-bottom-sheet";

    const categories = [
      {
        title: "Sumber Daya",
        items: [
          { type: "battery", name: "Baterai DC", sub: "12V Sumber", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="16" height="10" x="2" y="7" rx="2"></rect><line x1="22" x2="22" y1="11" y2="13"></line></svg>` }
        ]
      },
      {
        title: "Kontrol & Saklar",
        items: [
          { type: "switch_spst", name: "Saklar Rocker", sub: "ON / OFF", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><circle cx="6" cy="12" r="2"></circle><circle cx="18" cy="12" r="2"></circle></svg>` }
        ]
      },
      {
        title: "Beban & Output",
        items: [
          { type: "lamp", name: "Lampu Pijar", sub: "12V / 20W", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path><path d="M9 18h6"></path><path d="M10 22h4"></path></svg>` },
          { type: "led", name: "LED Merah", sub: "2V / 20mA", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="6"></circle><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line></svg>` },
          { type: "motor_dc", name: "Motor DC", sub: "12V Motor", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>` }
        ]
      },
      {
        title: "Pasif & Alat Ukur",
        items: [
          { type: "resistor", name: "Resistor", sub: "220 Ω", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h3l2-6 4 12 4-12 2 6h5"></path></svg>` },
          { type: "diode", name: "Dioda", sub: "1N4007", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="6 4 18 12 6 20 6 4"></polygon><line x1="18" y1="4" x2="18" y2="20"></line></svg>` },
          { type: "multimeter", name: "Multimeter", sub: "Digital V/A/Ω", svg: `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>` }
        ]
      }
    ];

    let contentHtml = categories.map(cat => `
      <div class="mobile-comp-section">
        <div class="mobile-category-title">${cat.title}</div>
        <div class="mobile-comp-grid">
          ${cat.items.map(item => `
            <button class="mobile-comp-card" data-type="${item.type}" data-name="${item.name.toLowerCase()}">
              <span class="mobile-comp-card-icon">${item.svg}</span>
              <span class="mobile-comp-card-name">${item.name}</span>
              <span class="mobile-comp-card-sub">${item.sub}</span>
            </button>
          `).join("")}
        </div>
      </div>
    `).join("");

    sheet.innerHTML = `
      <div class="mobile-sheet-handle"></div>
      <div class="mobile-sheet-header">
        <span style="font-weight: 700; font-size: 0.9rem; color: #38bdf8; display: inline-flex; align-items: center; gap: 6px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
          TAMBAH KOMPONEN
        </span>
        <button style="background: none; border: none; color: #94a3b8; font-size: 1.3rem; cursor: pointer; padding: 2px 6px;" id="btn-close-comp-sheet">✕</button>
      </div>
      <div class="mobile-sheet-search">
        <input type="text" id="mobile-comp-search-input" placeholder="🔍 Cari komponen (contoh: resistor, lampu)...">
      </div>
      <div class="mobile-sheet-scroll" id="mobile-comp-scroll-body">
        ${contentHtml}
      </div>
    `;

    document.body.appendChild(backdrop);
    document.body.appendChild(sheet);

    const closeAll = () => {
      sheet.remove();
      backdrop.remove();
    };

    backdrop.addEventListener("click", closeAll);
    sheet.querySelector("#btn-close-comp-sheet").addEventListener("click", closeAll);

    // Live search filter
    const searchInput = sheet.querySelector("#mobile-comp-search-input");
    searchInput.addEventListener("input", (e) => {
      const q = e.target.value.toLowerCase().trim();
      sheet.querySelectorAll(".mobile-comp-card").forEach(card => {
        const name = card.getAttribute("data-name") || "";
        const type = card.getAttribute("data-type") || "";
        if (!q || name.includes(q) || type.includes(q)) {
          card.style.display = "flex";
        } else {
          card.style.display = "none";
        }
      });
      sheet.querySelectorAll(".mobile-comp-section").forEach(sec => {
        const visibleCards = sec.querySelectorAll(".mobile-comp-card[style*='display: flex'], .mobile-comp-card:not([style*='display: none'])");
        sec.style.display = visibleCards.length > 0 ? "block" : "none";
      });
    });

    sheet.querySelectorAll(".mobile-comp-card").forEach(btn => {
      btn.addEventListener("click", () => {
        const type = btn.getAttribute("data-type");
        const state = stateManager.getState();
        const ws = state.workspace;
        const centerX = Math.round((-ws.panX + window.innerWidth / 2) / ws.zoom) - 50;
        const centerY = Math.round((-ws.panY + window.innerHeight / 2) / ws.zoom) - 40;
        this.componentEngine.createComponent(type, centerX, centerY);
        closeAll();
      });
    });
  }

  openSaveCaseModal() {
    const existing = document.getElementById("save-case-modal-overlay");
    if (existing) existing.remove();

    const overlay = document.createElement("div");
    overlay.id = "save-case-modal-overlay";
    overlay.style.cssText = `
      position: fixed;
      inset: 0;
      background: rgba(3, 7, 18, 0.78);
      backdrop-filter: blur(6px);
      z-index: 4000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
      animation: fadeIn 0.2s ease-out;
    `;

    overlay.innerHTML = `
      <div style="background: #0f172a; border: 1px solid rgba(168, 85, 247, 0.4); border-radius: 16px; max-width: 520px; width: 100%; padding: 24px; box-shadow: 0 24px 48px rgba(0, 0, 0, 0.85); color: #f8fafc; display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b; padding-bottom: 12px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.4rem;">💾</span>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #c084fc; margin: 0;">Simpan Rangkaian sebagai Studi Kasus</h3>
          </div>
          <button id="btn-close-save-modal" style="background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; line-height: 1; padding: 2px 6px;">✕</button>
        </div>

        <p style="font-size: 0.82rem; color: #94a3b8; margin: 0; line-height: 1.5;">
          Rangkaian yang sedang aktif di canvas akan disimpan ke database agar dapat dibuka dan dipraktikkan langsung oleh mahasiswa pada halaman Studi Kasus.
        </p>

        <form id="form-save-case" style="display: flex; flex-direction: column; gap: 14px;">
          <div>
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #e2e8f0; margin-bottom: 6px;">Judul Studi Kasus <span style="color: #f87171;">*</span></label>
            <input type="text" id="input-case-title" required placeholder="Contoh: Troubleshooting Saklar Rocker & Lampu" style="width: 100%; background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #f8fafc; font-size: 0.88rem; outline: none;">
          </div>

          <div>
            <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #e2e8f0; margin-bottom: 6px;">Deskripsi Misi / Permasalahan <span style="color: #f87171;">*</span></label>
            <textarea id="input-case-desc" required rows="4" placeholder="Jelaskan kondisi permasalahan sirkuit dan instruksi analisis untuk mahasiswa..." style="width: 100%; background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 10px 12px; color: #f8fafc; font-size: 0.88rem; outline: none; resize: vertical;"></textarea>
          </div>

          <div id="save-case-error" style="color: #f87171; font-size: 0.8rem; display: none;"></div>

          <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 6px;">
            <button type="button" id="btn-cancel-save-modal" style="background: #1e293b; border: 1px solid #334155; color: #cbd5e1; border-radius: 8px; padding: 10px 18px; font-weight: 600; font-size: 0.85rem; cursor: pointer;">Batal</button>
            <button type="submit" id="btn-submit-save-modal" style="background: linear-gradient(135deg, #a855f7, #7e22ce); border: none; color: #ffffff; border-radius: 8px; padding: 10px 20px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
              <span>Simpan ke Database</span>
            </button>
          </div>
        </form>
      </div>
    `;

    document.body.appendChild(overlay);

    const closeModal = () => overlay.remove();
    overlay.querySelector("#btn-close-save-modal").addEventListener("click", closeModal);
    overlay.querySelector("#btn-cancel-save-modal").addEventListener("click", closeModal);

    const form = overlay.querySelector("#form-save-case");
    const errorEl = overlay.querySelector("#save-case-error");
    const submitBtn = overlay.querySelector("#btn-submit-save-modal");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      errorEl.style.display = "none";

      const title = overlay.querySelector("#input-case-title").value.trim();
      const description = overlay.querySelector("#input-case-desc").value.trim();

      if (!title || !description) {
        errorEl.textContent = "Judul dan deskripsi wajib diisi!";
        errorEl.style.display = "block";
        return;
      }

      const circuitData = exportCircuitToJSON();
      console.log("💾 [STUDI KASUS EXPORT JSON]:\n" + circuitData);

      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span>Menyimpan...</span>`;

      try {
        const response = await fetch("/api/case-studies", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": csrfToken || ""
          },
          body: JSON.stringify({
            title,
            description,
            circuit_data: circuitData
          })
        });

        const resData = await response.json();

        if (response.ok && resData.success) {
          closeModal();
          this.showCaseExportToast(`Studi Kasus "${title}" Berhasil Disimpan!`, "Tersimpan ke database dan siap diakses mahasiswa.");
        } else {
          errorEl.textContent = resData.message || "Gagal menyimpan studi kasus. Pastikan Anda login sebagai Admin.";
          errorEl.style.display = "block";
          submitBtn.disabled = false;
          submitBtn.innerHTML = `<span>Simpan ke Database</span>`;
        }
      } catch (err) {
        console.error("Save Case Error:", err);
        errorEl.textContent = "Terjadi kesalahan jaringan saat menyimpan data.";
        errorEl.style.display = "block";
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<span>Simpan ke Database</span>`;
      }
    });
  }

  async loadCaseStudyFromAPI(id) {
    try {
      const response = await fetch(`/api/case-studies/${id}`, {
        headers: { "Accept": "application/json" }
      });
      const result = await response.json();

      if (response.ok && result.success && result.data) {
        const caseItem = result.data;
        stateManager.clearWorkspace();

        if (caseItem.circuit_data) {
          loadCircuitFromJSON(caseItem.circuit_data);
        }

        const state = stateManager.getState();
        state.simulation.running = true;
        stateManager.notify("simulation");

        this.showCaseMissionModal(
          caseItem.id,
          caseItem.title,
          caseItem.description,
          "Lakukan analisis pengukuran tegangan/arus pada rangkaian ini atau ubah konfigurasi saklar/komponen untuk menyelesaikan misi!"
        );
      } else {
        console.warn("Studi kasus tidak ditemukan di database:", id);
      }
    } catch (err) {
      console.error("Gagal memuat studi kasus dari server:", err);
    }
  }

  showCaseExportToast(title = "State Rangkaian Berhasil Di-Export!", subtitle = "Buka Browser Console (F12) untuk melihat struktur JSON.") {
    const existing = document.getElementById("case-export-toast");
    if (existing) existing.remove();

    const toast = document.createElement("div");
    toast.id = "case-export-toast";
    toast.style.cssText = `
      position: fixed;
      top: 65px;
      right: 20px;
      background: #0f172a;
      border: 1px solid #a855f7;
      border-radius: 12px;
      padding: 12px 18px;
      color: #f8fafc;
      font-size: 0.85rem;
      font-weight: 500;
      z-index: 3000;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      gap: 12px;
      animation: fadeIn 0.2s ease-out;
    `;
    toast.innerHTML = `
      <span style="font-size: 1.3rem;">💾</span>
      <div>
        <div style="font-weight: 700; color: #c084fc;">${title}</div>
        <div style="font-size: 0.75rem; color: #94a3b8;">${subtitle}</div>
      </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4500);
  }
}

/**
 * 1. exportCircuitToJSON()
 * Mengambil state rangkaian yang SEDANG ADA di canvas (semua komponen, posisi, rotasi,
 * properti internal, dan semua kabel/koneksi), lalu mengembalikan dalam format JSON string.
 * @returns {string}
 */
export function exportCircuitToJSON() {
  const state = stateManager.getState();
  const exportData = {
    version: 1,
    exportedAt: new Date().toISOString(),
    components: state.components.map(c => ({
      id: c.id,
      type: c.type,
      name: c.name,
      x: Math.round(c.x),
      y: Math.round(c.y),
      rotation: c.rotation || 0,
      width: c.width,
      height: c.height,
      properties: JSON.parse(JSON.stringify(c.properties || {})),
      terminals: JSON.parse(JSON.stringify(c.terminals || []))
    })),
    connections: state.connections.map(w => ({
      id: w.id,
      from: {
        componentId: w.from.componentId,
        terminalId: w.from.terminalId,
        ...(w.from.isWireBranch ? { isWireBranch: true, targetWireId: w.from.targetWireId, junctionPoint: w.from.junctionPoint } : {})
      },
      to: {
        componentId: w.to.componentId,
        terminalId: w.to.terminalId,
        ...(w.to.isWireBranch ? { isWireBranch: true, targetWireId: w.to.targetWireId, junctionPoint: w.to.junctionPoint } : {})
      },
      color: w.color || "#f88c00",
      waypoints: w.waypoints ? JSON.parse(JSON.stringify(w.waypoints)) : null
    }))
  };

  return JSON.stringify(exportData, null, 2);
}

/**
 * 2. loadCircuitFromJSON(jsonString)
 * Menerima JSON string lalu me-render ulang semua komponen dan koneksi ke canvas
 * (mengosongkan canvas terlebih dahulu sebelum load).
 * @param {string|object} jsonString 
 * @returns {boolean}
 */
export function loadCircuitFromJSON(jsonString) {
  try {
    const data = typeof jsonString === "string" ? JSON.parse(jsonString) : jsonString;
    if (!data || !Array.isArray(data.components)) {
      console.error("❌ Format JSON rangkaian tidak valid! Properti 'components' harus berupa Array.");
      return false;
    }

    // 1. Kosongkan canvas terlebih dahulu
    stateManager.clearWorkspace();

    // 2. Set komponen dan koneksi baru
    const state = stateManager.getState();
    state.components = JSON.parse(JSON.stringify(data.components || []));
    state.connections = JSON.parse(JSON.stringify(data.connections || []));

    // 3. Trigger render ulang ke DOM dan SVG
    stateManager.notify("components");
    stateManager.notify("connections");
    stateManager.notify("workspace");

    console.log(`✅ Sukses memuat rangkaian dari JSON: ${state.components.length} komponen, ${state.connections.length} koneksi.`);
    return true;
  } catch (err) {
    console.error("❌ Gagal memuat rangkaian dari JSON:", err);
    return false;
  }
}

// Expose fungsi secara global agar bisa dipanggil langsung dari DevTools Console
window.exportCircuitToJSON = exportCircuitToJSON;
window.loadCircuitFromJSON = loadCircuitFromJSON;
window.resetCanvas = () => window.dteApp?.resetCanvas();

// Bootstrap
document.addEventListener("DOMContentLoaded", () => {
  const app = new DTEVirtualLabApp();
  app.init();
  window.dteApp = app;
});
