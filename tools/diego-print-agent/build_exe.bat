@echo off
setlocal
title Build Diego Print Agent (.exe)
cd /d "%~dp0"

echo ======================================================
echo    Build Diego Print Agent -> diego-print-agent.exe
echo ======================================================

python --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Python tidak ditemukan di PATH. Install Python 3.10+ terlebih dahulu.
    pause
    exit /b 1
)

echo [1/3] Menginstall dependensi build (pywin32, pyinstaller)...
python -m pip install --upgrade pip
python -m pip install -r requirements-build.txt
if errorlevel 1 (
    echo [ERROR] Gagal menginstall dependensi build.
    pause
    exit /b 1
)

echo [2/3] Compile agent.py menjadi standalone .exe...
python -m PyInstaller --onefile --console --clean --noconfirm ^
    --name diego-print-agent ^
    --hidden-import=win32print ^
    --hidden-import=win32api ^
    --hidden-import=pywintypes ^
    agent.py
if errorlevel 1 (
    echo [ERROR] Gagal compile .exe dengan PyInstaller.
    pause
    exit /b 1
)

echo [3/3] Selesai!
echo File hasil build: "%~dp0dist\diego-print-agent.exe"
pause

