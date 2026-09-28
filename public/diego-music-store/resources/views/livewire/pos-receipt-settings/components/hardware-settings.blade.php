<div x-data="{
    agentUrl: 'http://127.0.0.1:18920',
    agentStatus: 'checking',
    agentInfo: null,
    printers: [],
    selectedReceiptPrinter: '',
    selectedBarcodePrinter: '',
    directPrintEnabled: true,
    logs: [],
    isTestingReceipt: false,
    isTestingBarcode: false,

    init() {
        this.agentUrl = localStorage.getItem('diego_pos_agent_url') || 'http://127.0.0.1:18920';
        this.selectedReceiptPrinter = localStorage.getItem('diego_pos_receipt_printer') || '';
        this.selectedBarcodePrinter = localStorage.getItem('diego_pos_barcode_printer') || '';
        const savedDirect = localStorage.getItem('diego_pos_direct_print_enabled');
        this.directPrintEnabled = savedDirect !== null ? savedDirect === 'true' : true;
        if (savedDirect === null) {
            localStorage.setItem('diego_pos_direct_print_enabled', 'true');
        }
        this.checkAgentConnection();
    },

    getBaseUrl() {
        return (this.agentUrl || 'http://127.0.0.1:18920').replace(/\/+$/, '');
    },

    async checkAgentConnection() {
        this.agentStatus = 'checking';
        const url = this.getBaseUrl();
        this.addLog(`Memeriksa koneksi ke Diego Print Agent (${url})...`);
        try {
            const res = await fetch(`${url}/api/status`, { signal: AbortSignal.timeout(2500) });
            if (res.ok) {
                const data = await res.json();
                this.agentStatus = 'online';
                this.agentInfo = data;
                this.addLog(`🟢 Terhubung ke ${data.name} v${data.version} (${data.platform})`);
                await this.fetchPrinters();
            } else {
                this.agentStatus = 'offline';
                this.addLog('🔴 Gagal: Agent merespon dengan status error ' + res.status);
            }
        } catch (e) {
            this.agentStatus = 'offline';
            this.addLog(`🔴 Diego Print Agent belum aktif pada ${url}.`);
        }
    },

    async fetchPrinters() {
        try {
            const res = await fetch(`${this.getBaseUrl()}/api/printers`);
            const data = await res.json();
            if (data.status === 'success') {
                this.printers = data.printers || [];
                this.addLog(`Mendeteksi ${this.printers.length} printer di sistem: ` + this.printers.join(', '));

                if (!this.selectedReceiptPrinter && this.printers.length > 0) {
                    const thermal = this.printers.find(p => /pos|thermal|receipt|epson|58|80/i.test(p)) || this.printers[0];
                    this.selectedReceiptPrinter = thermal;
                }
                if (!this.selectedBarcodePrinter && this.printers.length > 0) {
                    const barcodeP = this.printers.find(p => /xprinter|zebra|barcode|tsc|label|panda/i.test(p)) || this.printers[0];
                    this.selectedBarcodePrinter = barcodeP;
                }
            }
        } catch (e) {
            this.addLog('Gagal membaca daftar printer: ' + e.message);
        }
    },

    saveSettings() {
        localStorage.setItem('diego_pos_agent_url', this.getBaseUrl());
        localStorage.setItem('diego_pos_receipt_printer', this.selectedReceiptPrinter);
        localStorage.setItem('diego_pos_barcode_printer', this.selectedBarcodePrinter);
        localStorage.setItem('diego_pos_direct_print_enabled', this.directPrintEnabled ? 'true' : 'false');
        this.addLog('💾 Konfigurasi printer & agent berhasil disimpan di browser komputer ini.');
        
        $dispatch('toast', { 
            type: 'success', 
            title: 'Pengaturan Tersimpan', 
            message: 'Konfigurasi Direct Print telah diperbarui di browser komputer ini.' 
        });
    },

    async testPrintReceipt() {
        if (!this.selectedReceiptPrinter) {
            alert('Silakan pilih printer struk kasir terlebih dahulu.');
            return;
        }
        this.isTestingReceipt = true;
        this.addLog(`Mengirim sample struk uji coba ke printer [${this.selectedReceiptPrinter}]...`);
        try {
            const res = await fetch(`${this.getBaseUrl()}/api/test/receipt`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ printer: this.selectedReceiptPrinter })
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                this.addLog(`✅ SUKSES: Struk uji coba tercetak di [${this.selectedReceiptPrinter}].`);
            } else {
                this.addLog(`❌ GAGAL: ${data.message || 'Terjadi kesalahan'}`);
            }
        } catch (e) {
            this.addLog(`❌ Error koneksi: ${e.message}`);
        } finally {
            this.isTestingReceipt = false;
        }
    },

    async testPrintBarcode() {
        if (!this.selectedBarcodePrinter) {
            alert('Silakan pilih printer label barcode terlebih dahulu.');
            return;
        }
        this.isTestingBarcode = true;
        this.addLog(`Mengirim sample 1 label barcode uji coba ke printer [${this.selectedBarcodePrinter}]...`);
        try {
            const res = await fetch(`${this.getBaseUrl()}/api/test/barcode`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ printer: this.selectedBarcodePrinter })
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                this.addLog(`✅ SUKSES: Label barcode uji coba tercetak di [${this.selectedBarcodePrinter}].`);
            } else {
                this.addLog(`❌ GAGAL: ${data.message || 'Terjadi kesalahan'}`);
            }
        } catch (e) {
            this.addLog(`❌ Error koneksi: ${e.message}`);
        } finally {
            this.isTestingBarcode = false;
        }
    },

    addLog(msg) {
        const time = new Date().toLocaleTimeString('id-ID');
        this.logs.unshift(`[${time}] ${msg}`);
        if (this.logs.length > 50) this.logs.pop();
    }
}" class="space-y-6">

    <!-- Agent Connection Status Card -->
    <div class="p-5 rounded-2xl border transition-all duration-200"
         :class="{
             'bg-emerald-50/70 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/50': agentStatus === 'online',
             'bg-amber-50/70 border-amber-200 dark:bg-amber-950/20 dark:border-amber-800/50': agentStatus === 'offline',
             'bg-slate-50 border-slate-200 dark:bg-slate-800/40 dark:border-slate-700': agentStatus === 'checking'
         }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                     :class="{
                         'bg-emerald-500/20 text-emerald-600 dark:text-emerald-400': agentStatus === 'online',
                         'bg-amber-500/20 text-amber-600 dark:text-amber-400': agentStatus === 'offline',
                         'bg-slate-500/20 text-slate-500': agentStatus === 'checking'
                     }">
                    <i class="ph ph-printer text-2xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Diego Print Agent (Desktop Service)</h3>
                        <div x-show="agentStatus === 'online'">
                            <x-pos.utility.pill variant="success" size="xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                                Online (v<span x-text="agentInfo?.version || '1.0.0'"></span>)
                            </x-pos.utility.pill>
                        </div>
                        <div x-show="agentStatus === 'offline'">
                            <x-pos.utility.pill variant="warning" size="xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                Offline / Belum Aktif
                            </x-pos.utility.pill>
                        </div>
                        <div x-show="agentStatus === 'checking'">
                            <x-pos.utility.pill variant="default" size="xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 animate-ping mr-1.5"></span>
                                Memeriksa...
                            </x-pos.utility.pill>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-show="agentStatus === 'online'">
                        Terhubung pada <code class="font-mono text-emerald-700 dark:text-emerald-300">127.0.0.1:18920</code> di komputer kasir ini. Siap melakukan cetak langsung tanpa preview dialog.
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" x-show="agentStatus === 'offline'">
                        Agent belum berjalan di latar belakang komputer kasir ini. Cetak struk & barcode akan otomatis menggunakan dialog printer browser bawaan.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="ph-arrows-clockwise"
                    @click="checkAgentConnection()"
                >
                    Cek Ulang
                </x-pos.utility.button>

                <x-pos.utility.button
                    variant="primary"
                    size="sm"
                    icon="ph-download-simple"
                    href="/downloads/diego-print-agent/agent.py"
                    download="diego-print-agent.py"
                >
                    Unduh Agent (.py)
                </x-pos.utility.button>

                <x-pos.utility.button
                    variant="secondary"
                    size="sm"
                    icon="ph-file-code"
                    href="/downloads/diego-print-agent/run_agent.bat"
                    download="run_agent.bat"
                >
                    Unduh Runner (.bat)
                </x-pos.utility.button>
            </div>
        </div>
    </div>

    <!-- Main Grid: Printer Mapping & Test Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Left Column: Printer Selection & Options -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm space-y-5">
            <!-- URL Endpoint Agent -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        URL Endpoint Print Agent
                    </label>
                    <span class="text-[10px] text-slate-400">Default: http://127.0.0.1:18920</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text"
                           x-model="agentUrl"
                           @change="checkAgentConnection()"
                           placeholder="http://127.0.0.1:18920"
                           class="flex-1 text-xs font-mono rounded-xl border-slate-350 dark:border-slate-650 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white p-2.5 focus:ring-primary focus:border-primary">
                    <x-pos.utility.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        icon="ph-plugs-connected"
                        @click="checkAgentConnection()"
                    >
                        Hubungkan
                    </x-pos.utility.button>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Gunakan <code>http://127.0.0.1:18920</code> untuk PC kasir ini, atau IP lokal (misal: <code>http://192.168.1.50:18920</code>) jika berbagi printer kasir via LAN toko.
                </p>
            </div>

            <!-- Printer Struk Kasir -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Printer Struk Belanja Kasir (Thermal Paper Roll)
                </label>
                <div class="relative">
                    <select x-model="selectedReceiptPrinter"
                            class="w-full text-xs font-semibold rounded-xl border-slate-350 dark:border-slate-650 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white p-2.5 focus:ring-primary focus:border-primary">
                        <option value="">-- Pilih Printer Struk Kasir --</option>
                        <template x-for="p in printers" :key="p">
                            <option :value="p" x-text="p" :selected="p === selectedReceiptPrinter"></option>
                        </template>
                    </select>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Pilih printer thermal kasir (misal: EPSON TM-T82, POS-58, Xprinter XP-58/80).
                </p>
            </div>

            <!-- Printer Barcode Produk -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Printer Label Barcode Produk (Sticker Roll)
                </label>
                <div class="relative">
                    <select x-model="selectedBarcodePrinter"
                            class="w-full text-xs font-semibold rounded-xl border-slate-350 dark:border-slate-650 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white p-2.5 focus:ring-primary focus:border-primary">
                        <option value="">-- Pilih Printer Label Barcode --</option>
                        <template x-for="p in printers" :key="p">
                            <option :value="p" x-text="p" :selected="p === selectedBarcodePrinter"></option>
                        </template>
                    </select>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Pilih printer barcode label khusus (misal: Xprinter XP-365B, TSC, Zebra ZD220).
                </p>
            </div>

            <!-- Direct Print Toggle Checkbox -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox"
                           x-model="directPrintEnabled"
                           class="mt-0.5 rounded text-primary focus:ring-primary h-4 w-4">
                    <div>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <span>Mode Direct / Silent Print Otomatis</span>
                            <x-pos.utility.pill variant="success" size="xs">Aktif Otomatis</x-pos.utility.pill>
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5 leading-relaxed">
                            Mode ini aktif secara otomatis. Saat kasir menekan bayar atau cetak barcode, struk langsung dicetak ke printer tanpa memunculkan jendela preview printer browser sama sekali.
                        </span>
                    </div>
                </label>
            </div>

            <!-- Save Button -->
            <div class="pt-2 flex justify-end">
                <x-pos.utility.button
                    type="button"
                    variant="primary"
                    size="sm"
                    icon="ph-floppy-disk"
                    @click="saveSettings()"
                >
                    Simpan Pengaturan Hardware
                </x-pos.utility.button>
            </div>
        </div>

        <!-- Right Column: Diagnostic & Test Page (Uji Coba Cetak) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm space-y-5">
            <div class="border-b border-slate-100 dark:border-slate-700 pb-3">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="ph ph-flask text-base text-emerald-600"></i>
                    Halaman Uji Coba (Test Print Page)
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Lakukan pengujian cetak sampel secara langsung untuk memastikan driver & ukuran kertas sudah pas.
                </p>
            </div>

            <!-- Test Action Cards -->
            <div class="space-y-3">
                <!-- Test Card 1: Struk Kasir -->
                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Uji Coba Printer Struk Thermal</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block">
                            Target: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="selectedReceiptPrinter || '(Belum dipilih)'"></span>
                        </span>
                    </div>
                    <x-pos.utility.button
                        type="button"
                        variant="success"
                        size="sm"
                        icon="ph-receipt"
                        @click="testPrintReceipt()"
                        ::disabled="!selectedReceiptPrinter || agentStatus !== 'online' || isTestingReceipt"
                    >
                        <span x-text="isTestingReceipt ? 'Mencetak...' : 'Test Cetak Struk'"></span>
                    </x-pos.utility.button>
                </div>

                <!-- Test Card 2: Barcode Label -->
                <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Uji Coba Printer Label Barcode</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block">
                            Target: <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="selectedBarcodePrinter || '(Belum dipilih)'"></span>
                        </span>
                    </div>
                    <x-pos.utility.button
                        type="button"
                        variant="info"
                        size="sm"
                        icon="ph-barcode"
                        @click="testPrintBarcode()"
                        ::disabled="!selectedBarcodePrinter || agentStatus !== 'online' || isTestingBarcode"
                    >
                        <span x-text="isTestingBarcode ? 'Mencetak...' : 'Test Cetak Barcode'"></span>
                    </x-pos.utility.button>
                </div>
            </div>

            <!-- Terminal Live Logs -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400">
                    <span>Log Aktivitas & Respon Print Agent:</span>
                    <button type="button" @click="logs = []" class="text-primary hover:underline text-[10px]">Bersihkan</button>
                </div>
                <div class="bg-slate-950 text-emerald-400 p-3 rounded-xl font-mono text-[11px] h-36 overflow-y-auto border border-slate-800 space-y-1">
                    <template x-for="(log, idx) in logs" :key="idx">
                        <div x-text="log" class="leading-relaxed"></div>
                    </template>
                    <div x-show="logs.length === 0" class="text-slate-600 italic">Belum ada riwayat aktivitas.</div>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Setup Guide Box -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/50 dark:bg-blue-950/20 text-xs text-slate-700 dark:text-slate-300 space-y-2">
            <h5 class="font-bold text-blue-900 dark:text-blue-300 flex items-center gap-2">
                <i class="ph ph-info text-base"></i>
                Cara Memasang Diego Print Agent di Komputer Kasir:
            </h5>
            <ol class="list-decimal list-inside space-y-1 text-slate-600 dark:text-slate-400 pl-1 leading-relaxed">
                <li>Klik tombol <strong>"Unduh Agent (.py)"</strong> dan <strong>"Unduh Runner (.bat)"</strong> di atas, lalu simpan dalam satu folder di komputer kasir (contoh: <code>C:\DiegoPrintAgent\</code>).</li>
                <li>Klik ganda file <code>run_agent.bat</code>. Jendela hitam agent akan terbuka dan mendengarkan di port <code>18920</code>.</li>
                <li>Kembali ke halaman ini dan klik <strong>"Cek Ulang"</strong>. Status akan berubah hijau 🟢 <strong>Online</strong>, pilih printer kasir Anda, lalu klik <strong>"Test Cetak Struk"</strong>!</li>
            </ol>
        </div>

        <div class="p-4 rounded-xl border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50/50 dark:bg-emerald-950/20 text-xs text-slate-700 dark:text-slate-300 space-y-2">
            <h5 class="font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-2">
                <i class="ph ph-cloud text-base"></i>
                Bagaimana Jika Web POS Di-hosting di Cloud / VPS (HTTPS)?
            </h5>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                <strong>Tetap menggunakan localhost (127.0.0.1)!</strong> Mengapa? Karena JavaScript berjalan di <em>browser PC Kasir toko fisik</em> yang tersambung kabel USB ke printer. Server cloud di internet tidak memiliki colokan fisik ke printer Anda.
            </p>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                <strong>Catatan Browser (Chrome/Edge):</strong> Jika domain web Anda menggunakan <code>https://</code>, cukup izinkan akses lokal sekali saja: Klik ikon gembok / setting situs di samping address bar &rarr; <em>Site Settings</em> &rarr; ubah <em>Insecure content</em> menjadi <strong>"Allow"</strong>.
            </p>
        </div>
    </div>

</div>
