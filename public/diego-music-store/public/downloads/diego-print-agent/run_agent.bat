@echo off
title Diego Music Store - Print Agent
echo ======================================================
echo    Diego Music Store - Local Print Agent (v1.0.0)
echo ======================================================
echo Menjalankan agent di background pada port 18920...

python "%~dp0agent.py"
pause
