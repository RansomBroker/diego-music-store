#!/usr/bin/env python3
"""
Diego Music Store - Local Print Agent (v1.0.0)
Lightweight HTTP Server for Direct/Silent Printing on Windows, macOS, and Linux.
Runs on 127.0.0.1:18920
"""

import os
import sys
import json
import subprocess
import platform
from http.server import HTTPServer, BaseHTTPRequestHandler
from urllib.parse import urlparse, parse_qs

PORT = 18920
VERSION = "1.0.0"
AGENT_NAME = "Diego Print Agent"

def get_installed_printers():
    """Detect all installed printer names on the current operating system."""
    system = platform.system()
    printers = []

    if system == "Windows":
        # 1. Try win32print if installed
        try:
            import win32print
            flags = win32print.PRINTER_ENUM_LOCAL | win32print.PRINTER_ENUM_CONNECTIONS
            installed = win32print.EnumPrinters(flags)
            for p in installed:
                # p[2] is the printer name
                if p[2] and p[2] not in printers:
                    printers.append(p[2])
            if printers:
                return printers
        except ImportError:
            pass

        # 2. Fallback to PowerShell Get-Printer
        try:
            cmd = ['powershell', '-NoProfile', '-Command', 'Get-Printer | Select-Object -ExpandProperty Name']
            result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, timeout=5)
            if result.returncode == 0:
                lines = [line.strip() for line in result.stdout.strip().splitlines() if line.strip()]
                for p in lines:
                    if p not in printers:
                        printers.append(p)
            if printers:
                return printers
        except Exception:
            pass

        # 3. Fallback to wmic
        try:
            cmd = ['wmic', 'printer', 'get', 'name']
            result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, timeout=5)
            if result.returncode == 0:
                lines = [line.strip() for line in result.stdout.strip().splitlines() if line.strip() and line.strip().lower() != 'name']
                for p in lines:
                    if p not in printers:
                        printers.append(p)
        except Exception:
            pass

    elif system in ["Linux", "Darwin"]:
        # Try lpstat -p
        try:
            result = subprocess.run(['lpstat', '-p'], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, timeout=5)
            if result.returncode == 0:
                for line in result.stdout.splitlines():
                    # Format: "printer Printer_Name is idle..."
                    parts = line.split()
                    if len(parts) >= 2 and parts[0] == 'printer':
                        printers.append(parts[1])
        except Exception:
            pass

    # If no physical printer detected or in development/container, provide sample mock printers
    if not printers:
        if system == "Windows":
            printers = ["Microsoft Print to PDF", "OneNote for Windows 10"]
        else:
            printers = ["Default_Thermal_Printer (Mock)", "Default_Barcode_Printer (Mock)"]

    return printers

def print_raw_windows(printer_name, raw_bytes, doc_name="Diego Print Job"):
    """Send raw bytes directly to a Windows printer spooler."""
    try:
        import win32print
        hPrinter = win32print.OpenPrinter(printer_name)
        try:
            hJob = win32print.StartDocPrinter(hPrinter, 1, (doc_name, None, "RAW"))
            try:
                win32print.StartPagePrinter(hPrinter)
                win32print.WritePrinter(hPrinter, raw_bytes)
                win32print.EndPagePrinter(hPrinter)
            finally:
                win32print.EndDocPrinter(hPrinter)
        finally:
            win32print.ClosePrinter(hPrinter)
        return True, "Cetak berhasil terkirim ke spooler Windows."
    except ImportError:
        # Fallback to temporary file printing via PowerShell
        import tempfile
        try:
            with tempfile.NamedTemporaryFile(delete=False, suffix=".prn") as tmp:
                tmp.write(raw_bytes)
                tmp_path = tmp.name

            ps_cmd = f'Copy-Item -Path "{tmp_path}" -Destination "\\\\localhost\\{printer_name}"'
            subprocess.run(['powershell', '-NoProfile', '-Command', ps_cmd], check=False, timeout=5)
            try:
                os.remove(tmp_path)
            except Exception:
                pass
            return True, "Cetak diproses via Windows spooler copy."
        except Exception as e:
            return False, f"Gagal mencetak: {str(e)}"
    except Exception as e:
        return False, f"Windows Printer Error: {str(e)}"

def print_raw_cups(printer_name, raw_bytes):
    """Send raw data to CUPS on Linux/macOS."""
    try:
        proc = subprocess.Popen(['lp', '-d', printer_name, '-o', 'raw'], stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        out, err = proc.communicate(input=raw_bytes, timeout=5)
        if proc.returncode == 0:
            return True, "Cetak berhasil dikirim ke CUPS."
        return False, f"CUPS Error: {err.decode('utf-8', errors='ignore')}"
    except Exception as e:
        return False, f"Linux/macOS Print Error: {str(e)}"

def send_to_printer(printer_name, raw_bytes, doc_name="Diego Print"):
    """Dispatch raw print job to the appropriate OS handler."""
    system = platform.system()
    if system == "Windows":
        return print_raw_windows(printer_name, raw_bytes, doc_name)
    elif system in ["Linux", "Darwin"]:
        return print_raw_cups(printer_name, raw_bytes)
    else:
        return True, f"Mock print simulated for {printer_name} ({len(raw_bytes)} bytes)."

class PrintAgentHandler(BaseHTTPRequestHandler):
    def _send_cors_headers(self):
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Access-Control-Request-Private-Network')
        self.send_header('Access-Control-Allow-Private-Network', 'true')

    def do_OPTIONS(self):
        self.send_response(200)
        self._send_cors_headers()
        self.end_headers()

    def _respond_json(self, status_code, data):
        self.send_response(status_code)
        self._send_cors_headers()
        self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.end_headers()
        self.wfile.write(json.dumps(data).encode('utf-8'))

    def do_GET(self):
        parsed = urlparse(self.path)
        path = parsed.path

        if path == "/" or path == "/api/status":
            self._respond_json(200, {
                "status": "online",
                "name": AGENT_NAME,
                "version": VERSION,
                "platform": platform.system(),
                "node": platform.node(),
                "port": PORT
            })
        elif path == "/api/printers":
            printers = get_installed_printers()
            self._respond_json(200, {
                "status": "success",
                "count": len(printers),
                "printers": printers
            })
        else:
            self._respond_json(404, {"status": "error", "message": "Endpoint not found."})

    def do_POST(self):
        parsed = urlparse(self.path)
        path = parsed.path

        # Read JSON body
        content_length = int(self.headers.get('Content-Length', 0))
        body_bytes = self.rfile.read(content_length)
        try:
            payload = json.loads(body_bytes.decode('utf-8')) if body_bytes else {}
        except Exception:
            payload = {}

        if path == "/api/test/receipt":
            printer_name = payload.get("printer")
            if not printer_name:
                self._respond_json(400, {"status": "error", "message": "Printer name is required."})
                return

            # Construct ESC/POS sample test receipt
            # ESC @ (Initialize), ESC a 1 (Center), etc.
            lines = [
                b'\x1b\x40', # Init
                b'\x1b\x61\x01', # Center align
                b'\x1b\x21\x30', # Double height & width
                b'DIEGO MUSIC STORE\n',
                b'\x1b\x21\x00', # Normal font
                b'*** TEST CETAK STRUK BERHASIL ***\n',
                b'--------------------------------\n',
                b'\x1b\x61\x00', # Left align
                f"Waktu Cetak : {os.popen('date /t 2>nul || date').read().strip() or 'Live Test'}\n".encode('utf-8', errors='ignore'),
                f"Target Driver: {printer_name}\n".encode('utf-8', errors='ignore'),
                b"Status Agent: Terhubung (v1.0.0)\n",
                b"--------------------------------\n",
                b"Item Sample 1       x1   Rp  50.000\n",
                b"Item Sample 2       x2   Rp 150.000\n",
                b"--------------------------------\n",
                b"TOTAL BELANJA            Rp 200.000\n",
                b"================================\n",
                b'\x1b\x61\x01', # Center align
                b'Direct Print Agent Berfungsi Sempurna!\n\n',
                b'\x1d\x56\x41\x10', # Paper Cut
                b'\x1b\x70\x00\x19\xfa' # Cash drawer kick pulse
            ]
            raw_data = b''.join(lines)
            success, msg = send_to_printer(printer_name, raw_data, "Test Print Struk")
            self._respond_json(200 if success else 500, {
                "status": "success" if success else "error",
                "message": msg,
                "printer": printer_name
            })

        elif path == "/api/test/barcode":
            printer_name = payload.get("printer")
            if not printer_name:
                self._respond_json(400, {"status": "error", "message": "Printer name is required."})
                return

            # Sample TSPL / ESC-POS / Plain text test for Barcode
            # TSPL format (Standard for Xprinter / TSC / Zebra label thermal printers)
            tspl_commands = [
                b"SIZE 40 mm, 30 mm\n",
                b"GAP 2 mm, 0 mm\n",
                b"CLS\n",
                b'TEXT 20,20,"3",0,1,1,"DIEGO MUSIC STORE"\n',
                b'TEXT 20,50,"2",0,1,1,"Gitar Yamaha F310"\n',
                b'BARCODE 20,80,"128",50,1,0,2,2,"899123456789"\n',
                b'TEXT 20,150,"2",0,1,1,"Rp 1.500.000"\n',
                b"PRINT 1,1\n"
            ]
            raw_data = b''.join(tspl_commands)
            success, msg = send_to_printer(printer_name, raw_data, "Test Print Barcode")
            self._respond_json(200 if success else 500, {
                "status": "success" if success else "error",
                "message": msg,
                "printer": printer_name
            })

        elif path == "/api/print/receipt":
            printer_name = payload.get("printer")
            raw_text = payload.get("raw_text", "")
            base64_data = payload.get("base64_data", "")

            if not printer_name:
                self._respond_json(400, {"status": "error", "message": "Printer name is required."})
                return

            if base64_data:
                import base64
                raw_bytes = base64.b64decode(base64_data)
            else:
                # Text string with automatic cut
                raw_bytes = raw_text.encode('utf-8', errors='ignore')
                if payload.get("cut", True):
                    raw_bytes += b"\n\n\n\x1d\x56\x41\x10"

            success, msg = send_to_printer(printer_name, raw_bytes, payload.get("title", "Struk Transaksi"))
            self._respond_json(200 if success else 500, {
                "status": "success" if success else "error",
                "message": msg,
                "printer": printer_name
            })

        elif path == "/api/print/barcode":
            printer_name = payload.get("printer")
            raw_commands = payload.get("raw_commands", "")
            base64_data = payload.get("base64_data", "")

            if not printer_name:
                self._respond_json(400, {"status": "error", "message": "Printer name is required."})
                return

            if base64_data:
                import base64
                raw_bytes = base64.b64decode(base64_data)
            else:
                raw_bytes = raw_commands.encode('utf-8', errors='ignore')

            success, msg = send_to_printer(printer_name, raw_bytes, payload.get("title", "Cetak Barcode Label"))
            self._respond_json(200 if success else 500, {
                "status": "success" if success else "error",
                "message": msg,
                "printer": printer_name
            })

        else:
            self._respond_json(404, {"status": "error", "message": "Endpoint not found."})

def run():
    import argparse
    parser = argparse.ArgumentParser(description="Diego Music Store Print Agent")
    parser.add_argument("--host", default="127.0.0.1", help="Host address to bind (use 0.0.0.0 for LAN sharing)")
    parser.add_argument("--port", type=int, default=PORT, help="Port to listen on (default: 18920)")
    parser.add_argument("--ssl", action="store_true", help="Enable SSL/HTTPS mode")
    parser.add_argument("--cert", default="cert.pem", help="SSL certificate file path")
    parser.add_argument("--key", default="key.pem", help="SSL private key file path")
    args = parser.parse_args()

    server_address = (args.host, args.port)
    httpd = HTTPServer(server_address, PrintAgentHandler)
    proto = "http"

    if args.ssl:
        import ssl
        if os.path.exists(args.cert) and os.path.exists(args.key):
            context = ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER)
            context.load_cert_chain(certfile=args.cert, keyfile=args.key)
            httpd.socket = context.wrap_socket(httpd.socket, server_side=True)
            proto = "https"
        else:
            print(f"[Peringatan] File sertifikat {args.cert} atau {args.key} tidak ditemukan. Berjalan dalam mode HTTP.")

    print(f"======================================================")
    print(f"   {AGENT_NAME} v{VERSION} Aktif")
    print(f"   Listening on: {proto}://{args.host}:{args.port}")
    if args.host == "0.0.0.0":
        print(f"   Mode Jaringan LAN: Printer dapat diakses oleh PC kasir lain di WiFi/LAN toko")
    print(f"   Status: Menunggu perintah cetak dari Diego POS...")
    print(f"======================================================")
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nAgent dihentikan oleh pengguna.")
        httpd.server_close()

if __name__ == '__main__':
    run()
