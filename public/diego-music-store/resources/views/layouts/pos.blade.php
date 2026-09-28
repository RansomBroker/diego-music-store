<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'POS Kasir Modern' }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS compiled via Vite -->
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Leaflet.js Map Library -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        body {
            font-family: 'Inter', sans-serif;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @livewireStyles
</head>
<body class="bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100 h-screen min-h-dvh max-h-dvh w-full overflow-hidden flex transition-colors duration-200">
    
    <!-- Custom Top-Right Toast Container -->
    <x-pos.toast />

    {{ $slot }}

    @livewireScripts
    <script>
        // Check for saved dark mode preference or system preference
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        window.addEventListener('print-receipt', async event => {
            const saleId = event.detail.saleId;
            const url = `{{ url('/pos/receipt') }}/${saleId}`;

            const savedDirect = localStorage.getItem('diego_pos_direct_print_enabled');
            const directPrint = savedDirect !== null ? savedDirect === 'true' : true;
            let targetPrinter = localStorage.getItem('diego_pos_receipt_printer');
            const agentUrl = (localStorage.getItem('diego_pos_agent_url') || 'http://127.0.0.1:18920').replace(/\/+$/, '');

            if (directPrint) {
                try {
                    // Quick check if agent is alive
                    const checkRes = await fetch(`${agentUrl}/api/status`, { signal: AbortSignal.timeout(1200) });
                    if (checkRes.ok) {
                        // Auto-select detected printer if none explicitly chosen yet
                        if (!targetPrinter) {
                            try {
                                const pRes = await fetch(`${agentUrl}/api/printers`);
                                const pData = await pRes.json();
                                if (pData.status === 'success' && pData.printers.length > 0) {
                                    targetPrinter = pData.printers.find(p => /pos|thermal|receipt|epson|58|80/i.test(p)) || pData.printers[0];
                                    localStorage.setItem('diego_pos_receipt_printer', targetPrinter);
                                }
                            } catch (_) {}
                        }

                        if (targetPrinter) {
                            // Fetch the receipt text representation
                            const receiptRes = await fetch(url);
                            const htmlContent = await receiptRes.text();

                            // Extract text lines from DOM
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(htmlContent, 'text/html');
                            const bodyText = doc.body.innerText || '';

                            const printRes = await fetch(`${agentUrl}/api/print/receipt`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    printer: targetPrinter,
                                    raw_text: bodyText.trim(),
                                    title: 'Struk #' + saleId,
                                    cut: true
                                })
                            });

                            if (printRes.ok) {
                                if (window.dispatchEvent) {
                                    window.dispatchEvent(new CustomEvent('toast', {
                                        detail: {
                                            type: 'success',
                                            title: 'Struk Tercetak Langsung',
                                            message: `Struk transaksi berhasil dicetak ke [${targetPrinter}].`
                                        }
                                    }));
                                }
                                return; // Silent print completed successfully!
                            }
                        }
                    }
                } catch (e) {
                    console.warn('Direct print agent unreachable, falling back to browser print:', e);
                }
            }

            // Fallback to standard browser window.open
            window.open(url, '_blank');
        });

        window.addEventListener('open-draft-bill', event => {
            window.open(event.detail.url, '_blank');
        });
    </script>
</body>
</html>
