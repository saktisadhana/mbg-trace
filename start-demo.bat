@echo off
title MBG Trace - Demo Launcher

echo ============================================
echo   Starting MBG Trace demo...
echo ============================================

REM 1) Backend (Laravel API) -> http://localhost:8000  (uses cloud Aiven + Atlas)
start "MBG Backend" /d "C:\Users\Sakti Sadhana\Project\SBD\mbg-trace" cmd /k "C:\xampp\php\php.exe artisan serve --port=8000"

REM 2) Frontend (Vite) -> http://localhost:5173
start "MBG Frontend" /d "C:\Users\Sakti Sadhana\Project\SBD\mbg-trace\frontend" cmd /k "npm run dev -- --port 5173 --strictPort"

echo Waiting ~10s for the servers to boot...
timeout /t 10 /nobreak >nul

echo.
echo === Your PUBLIC LINK appears below (look for  https://...trycloudflare.com ) ===
echo === Keep this window open during your demo. Close it / Ctrl+C to stop.      ===
echo.
"C:\Program Files (x86)\cloudflared\cloudflared.exe" tunnel --url http://localhost:5173
