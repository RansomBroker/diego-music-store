@echo off
title Diego Music Store - Print Agent
echo ======================================================
echo    Diego Music Store - Local Print Agent (v1.0.0)
echo ======================================================
echo Menjalankan agent di port 18920...

if exist "%~dp0diego-print-agent.exe" (
    "%~dp0diego-print-agent.exe" %*
) else (
    python "%~dp0agent.py" %*
)
pause
