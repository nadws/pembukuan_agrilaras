<x-theme.app title="{{ $title }}" sizeCard="12">
    <x-slot name="cardHeader">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="mb-0 fw-bold" style="color: #0f172a !important;"><i
                        class="fas fa-book-reader text-primary me-2"></i>Dokumentasi & Panduan Pembukuan</h4>
                <small class="text-muted" style="color: #64748b !important;">Pusat informasi alur transaksi, aset tetap,
                    aktiva gantung, dan laporan keuangan.</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="cardBody">
        {{-- Load Mermaid.js for Visual Diagram --}}
        <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>

        <style>
            /* Reset & Contrast Fixes */
            .doc-container {
                font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
                color: #0f172a !important;
            }

            .doc-container p,
            .doc-container li,
            .doc-container div,
            .doc-container span,
            .doc-container td,
            .doc-container th {
                color: #0f172a !important;
            }

            .doc-container .text-muted {
                color: #475569 !important;
            }

            /* Sidebar Styling */
            .doc-sidebar {
                position: sticky;
                top: 20px;
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 12px;
                padding: 16px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            }

            .doc-nav-category {
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #2563eb !important;
                margin: 16px 0 6px 8px;
            }

            .doc-nav-category:first-child {
                margin-top: 0;
            }

            .doc-nav-link {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 9px 12px;
                border-radius: 8px;
                color: #334155 !important;
                font-weight: 600;
                font-size: 13px;
                text-decoration: none;
                margin-bottom: 3px;
                transition: all 0.15s ease-in-out;
            }

            .doc-nav-link:hover,
            .doc-nav-link.active {
                background: #eff6ff !important;
                color: #1d4ed8 !important;
                font-weight: 700;
            }

            /* Top Search Wrapper */
            .doc-search-wrapper {
                background: #f8fafc !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 12px;
                padding: 16px 20px;
                margin-bottom: 24px;
            }

            .doc-search-input {
                border: 2px solid #94a3b8 !important;
                font-size: 15px;
                padding: 12px 16px;
                border-radius: 10px;
                background: #ffffff !important;
                color: #0f172a !important;
                font-weight: 600;
            }

            .doc-search-input:focus {
                border-color: #2563eb !important;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15) !important;
            }

            /* Section Cards */
            .doc-section {
                background: #ffffff !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 16px;
                padding: 28px;
                margin-bottom: 28px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            }

            .doc-title {
                font-size: 21px;
                font-weight: 800;
                color: #0f172a !important;
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .doc-subtitle {
                font-size: 14px;
                color: #475569 !important;
                margin-bottom: 20px;
                line-height: 1.6;
            }

            /* Pathway Cards */
            .path-card {
                border-radius: 12px;
                padding: 20px;
                height: 100%;
                border: 1px solid #cbd5e1;
            }

            .path-card-blue {
                background: #f0f7ff !important;
                border-color: #93c5fd !important;
            }

            .path-card-emerald {
                background: #f0fdf4 !important;
                border-color: #86efac !important;
            }

            /* Step Badge Circles */
            .step-number {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: 14px;
                flex-shrink: 0;
            }

            .bg-step-blue {
                background: #2563eb !important;
                color: #ffffff !important;
            }

            .bg-step-emerald {
                background: #059669 !important;
                color: #ffffff !important;
            }

            /* Mermaid Diagram Fix Text Visibility */
            .mermaid-box {
                background: #ffffff !important;
                border: 2px solid #cbd5e1 !important;
                border-radius: 14px;
                padding: 24px;
                overflow-x: auto;
                text-align: center;
            }

            .mermaid text {
                fill: #0f172a !important;
                font-weight: 700 !important;
                font-size: 13.5px !important;
            }

            .mermaid .node rect,
            .mermaid .node polygon {
                fill: #ffffff !important;
                stroke: #2563eb !important;
                stroke-width: 2px !important;
            }

            .mermaid .edgeLabel {
                background-color: #ffffff !important;
                color: #0f172a !important;
                font-weight: 700 !important;
            }

            /* Timeline Styling */
            .timeline-item {
                position: relative;
                padding-left: 32px;
                padding-bottom: 20px;
                border-left: 2px dashed #94a3b8;
            }

            .timeline-item:last-child {
                border-left: 2px transparent;
                padding-bottom: 0;
            }

            .timeline-dot {
                position: absolute;
                left: -9px;
                top: 2px;
                width: 16px;
                height: 16px;
                border-radius: 50%;
                background: #2563eb;
                border: 3px solid #ffffff;
                box-shadow: 0 0 0 2px #2563eb;
            }

            .comparison-table th {
                background: #1e293b !important;
                color: #ffffff !important;
                font-size: 13px;
                padding: 10px 14px;
            }

            .comparison-table td {
                padding: 10px 14px;
                font-size: 13px;
                color: #0f172a !important;
            }
        </style>

        <div class="doc-container">
            {{-- Top Search Bar --}}
            <div class="doc-search-wrapper">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                    <div>
                        <strong class="fs-6" style="color: #0f172a !important;"><i
                                class="fas fa-search me-1 text-primary"></i> Pencarian Cepat Dokumentasi</strong>
                        <div class="small" style="color: #475569 !important;">Cari topik (misal: <em>aktiva</em>,
                            <em>genset</em>, <em>aktiva gantung</em>, <em>pembalik</em>, <em>penyusutan</em>,
                            <em>neraca</em>)
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-white text-dark border px-3 py-2 fw-bold">Tekan <code>Ctrl + K</code>
                            untuk mencari</span>
                    </div>
                </div>
                <input type="text" id="docSearchInput" class="form-control doc-search-input"
                    placeholder="Ketik kata kunci pencarian...">
            </div>

            <div class="row g-4">
                {{-- Left Navigation Sidebar --}}
                <div class="col-lg-3">
                    <div class="doc-sidebar">
                        {{-- Kategori 1: AKTIVA & ASET TETAP --}}
                        <div class="doc-nav-category"><i class="fas fa-boxes me-1"></i> Modul Aktiva & Aset</div>
                        <a href="#flowchart-aktiva" class="doc-nav-link active">
                            <i class="fas fa-sitemap text-primary"></i>
                            <span>1. Alur Aktiva Gantung vs Direct</span>
                        </a>

                    </div>
                </div>

                {{-- Right Main Documentation Content --}}
                <div class="col-lg-9" id="docMainContent">

                    {{-- SECTION 1: FLOWCHART VISUAL ALUR PENGADAAN AKTIVA --}}
                    <section id="flowchart-aktiva" class="doc-section doc-card-item"
                        data-search="alur pengadaan aktiva gantung pembalik aset tetap direct flowchart">
                        <div class="doc-title">
                            <i class="fas fa-sitemap text-primary"></i>
                            1. Flowchart Visual: Aktiva Gantung vs Master Aktiva Direct
                        </div>
                        <p class="doc-subtitle">
                            Gunakan alur visual di bawah untuk menentukan apakah pengadaan barang/alat Anda masuk lewat
                            <strong>Aktiva Gantung</strong> (pengadaan bertahap/ada tukang/DP) atau <strong>Direct
                                Master Aktiva</strong> (1x beli utuh).
                        </p>

                        {{-- Mermaid Flowchart Diagram --}}
                        <div class="mermaid-box mb-4">
                            <pre class="mermaid">
flowchart TD
    Start(["Beli / Pengadaan Barang / Aset"]) --> Q1{"Apakah Pengadaan Bertahap?\n(Ada DP, Cicilan, Ongkir, Tukang/Rakit)"}
    
    Q1 -- "YA (Bertahap / Ada DP)" --> PathA["1. Input di 'Aktiva Gantung'\n(Tiap Kali Bayar/DP/Nota)"]
    PathA --> PathA2["2. Input di 'Pembalik Aktiva Gantung'\n+ Centang 'Daftarkan ke Master Aset'"]
    PathA2 --> DoneA(["Selesai: Saldo Gantung 0\nAset Tetap Bertambah & Disusutkan"])

    Q1 -- "TIDAK (1x Beli Utuh)" --> PathB["1. Daftarkan di '/aktiva.add'\n(Masuk Master Aset & Umur)"]
    PathB --> PathB2["2. Jurnal Pembayaran Kas/Bank\ndi 'Jurnal Umum / Pembelian'"]
    PathB2 --> DoneB(["Selesai: Kas Berkurang\nAset Tetap Bertambah & Disusutkan"])
                            </pre>
                        </div>

                        {{-- Visual Comparison Cards --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="path-card path-card-blue">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="step-number bg-step-blue">A</span>
                                        <h6 class="fw-bold text-primary mb-0 fs-6">Jalur Aktiva Gantung + Pembalik</h6>
                                    </div>
                                    <p class="small text-muted mb-3"><strong>Cocok Untuk:</strong> Pembelian bertahap,
                                        pembangunan kandang/gudang, atau perakitan mesin yang butuh banyak
                                        nota/DP/ongkir.</p>
                                    <ul class="list-unstyled small mb-0">
                                        <li class="mb-2"><i
                                                class="fas fa-check-circle text-primary me-2"></i><strong>Langkah
                                                1:</strong> Setiap bayar DP/nota $\rightarrow$ Input di menu
                                            <code>Aktiva Gantung</code>.
                                        </li>
                                        <li class="mb-2"><i
                                                class="fas fa-check-circle text-primary me-2"></i><strong>Langkah
                                                2:</strong> Saat barang selesai & siap pakai $\rightarrow$ Input di menu
                                            <code>Pembalik Aktiva Gantung</code>.
                                        </li>
                                        <li><i class="fas fa-star text-primary me-2"></i><strong>Keuntungan:</strong>
                                            Jurnal kas & master aktiva <u>otomatis terbuat oleh sistem</u>.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="path-card path-card-emerald">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="step-number bg-step-emerald">B</span>
                                        <h6 class="fw-bold text-success mb-0 fs-6">Jalur Master Aktiva Direct
                                            (`/aktiva.add`)</h6>
                                    </div>
                                    <p class="small text-muted mb-3"><strong>Cocok Untuk:</strong> Barang yang dibeli
                                        utuh 1 kali transaksi dan langsung siap pakai hari itu juga (laptop, genset
                                        portable, dll).</p>
                                    <ul class="list-unstyled small mb-0">
                                        <li class="mb-2"><i
                                                class="fas fa-check-circle text-success me-2"></i><strong>Langkah
                                                1:</strong> Daftarkan barang di menu <code>/aktiva.add</code> (Master
                                            Aktiva).</li>
                                        <li class="mb-2"><i
                                                class="fas fa-check-circle text-success me-2"></i><strong>Langkah
                                                2:</strong> Input jurnal kas keluar di menu
                                            <code>Jurnal Umum / Pembelian Umum</code>.
                                        </li>
                                        <li><i class="fas fa-star text-success me-2"></i><strong>Hasil:</strong> Kas
                                            berkurang, Aset Tetap naik, penyusutan otomatis berjalan.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>



                    {{-- SECTION 3: PENGECEKAN NERACA & LABA RUGI --}}
                    <section id="cek-laporan" class="doc-section doc-card-item"
                        data-search="neraca laba rugi laporan penyusutan depresiasi pemeriksaan">
                        <div class="doc-title">
                            <i class="fas fa-chart-line text-success"></i>
                            3. Cara Pengecekan di Neraca & Laba Rugi
                        </div>
                        <p class="doc-subtitle">
                            Bagaimana cara memastikan data yang diinput sudah benar dan tidak merusak laporan keuangan?
                        </p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-white h-100">
                                    <h6 class="fw-bold text-primary mb-2 fs-6"><i class="fas fa-balance-scale me-1"></i>
                                        Tampilan di NERACA (`/pembukuan-baru/neraca`)</h6>
                                    <ul class="small text-muted mb-0 ps-3">
                                        <li class="mb-2"><strong>Akun Peralatan Kandang:</strong> Bertambah <strong>+ Rp
                                                90.000.000</strong> (Tercatat sebagai Kekayaan Perusahaan).</li>
                                        <li class="mb-2"><strong>Akumulasi Penyusutan:</strong> Bertambah minus
                                            <strong>- Rp 1.875.000</strong> per bulan.
                                        </li>
                                        <li><strong>Nilai Buku Bersih (Bulan ke-1):</strong> Rp 88.125.000.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-white h-100">
                                    <h6 class="fw-bold text-danger mb-2 fs-6"><i
                                            class="fas fa-file-invoice-dollar me-1"></i> Tampilan di LABA RUGI
                                        (`/laporan/laba-rugi`)</h6>
                                    <ul class="small text-muted mb-0 ps-3">
                                        <li class="mb-2"><strong>Beban Depresiasi Peralatan:</strong> Hanya terpotong
                                            <strong>Rp 1.875.000</strong> di bulan tersebut.
                                        </li>
                                        <li class="mb-2"><strong>Mengapa Tidak Langsung 90 Juta?</strong> Karena jika
                                            langsung dibiayakan 90jt sekaligus, Laporan Laba Rugi akan rugi hancur
                                            secara tidak wajar.</li>
                                        <li><strong>Hasil:</strong> Laporan Keuangan Perusahaan akurat, rapi, dan sesuai
                                            SAK.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>
        </div>

        @section('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Initialize Mermaid Visual Diagram with High Contrast Theme
                    if (window.mermaid) {
                        mermaid.initialize({
                            startOnLoad: true,
                            theme: 'neutral',
                            themeVariables: {
                                fontFamily: 'system-ui, sans-serif',
                                fontSize: '13.5px',
                                primaryTextColor: '#0f172a',
                                secondaryTextColor: '#0f172a',
                                lineColor: '#2563eb',
                                nodeBorder: '#2563eb'
                            }
                        });
                    }

                    const searchInput = document.getElementById('docSearchInput');
                    const items = document.querySelectorAll('.doc-card-item');
                    const navLinks = document.querySelectorAll('.doc-nav-link');

                    // Filter search logic
                    function filterDocs() {
                        const q = searchInput.value.toLowerCase().trim();
                        items.forEach(function (item) {
                            const keywords = item.dataset.search ? item.dataset.search.toLowerCase() : '';
                            const text = item.textContent.toLowerCase();

                            if (!q || keywords.includes(q) || text.includes(q)) {
                                item.style.display = 'block';
                            } else {
                                item.style.display = 'none';
                            }
                        });
                    }

                    searchInput.addEventListener('input', filterDocs);

                    // Shortcut Ctrl + K for quick search
                    document.addEventListener('keydown', function (e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                            e.preventDefault();
                            searchInput.focus();
                        }
                    });

                    // Smooth Scroll Nav Links
                    navLinks.forEach(function (link) {
                        link.addEventListener('click', function (e) {
                            const targetId = this.getAttribute('href');
                            if (targetId.startsWith('#')) {
                                navLinks.forEach(l => l.classList.remove('active'));
                                this.classList.add('active');
                            }
                        });
                    });
                });
            </script>
        @endsection
    </x-slot>
</x-theme.app>