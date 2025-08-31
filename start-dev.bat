@echo off
echo Starting Laravel Development Environment...
echo ==========================================

echo [1/4] Starting Laravel development server...
start "Laravel Server" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"

echo [2/4] Starting Reverb WebSocket server...
start "Reverb Server" cmd /k "php artisan reverb:start --host=0.0.0.0 --port=8080"

echo [3/4] Starting Queue worker...
start "Queue Worker" cmd /k "php artisan queue:work --tries=3 --timeout=90 --sleep=3"

echo [4/4] All services started!
echo.
echo Application URLs:
echo   Web App:      http://localhost:8000
echo   WebSocket:    ws://localhost:8080
echo   Queue Jobs:   Processing in background
echo.
echo Press any key to close all services...
pause

echo Closing all services...
taskkill /f /im php.exe 2>nul
taskkill /f /im cmd.exe /fi "WINDOWTITLE eq Laravel Server*" 2>nul
taskkill /f /im cmd.exe /fi "WINDOWTITLE eq Reverb Server*" 2>nul
taskkill /f /im cmd.exe /fi "WINDOWTITLE eq Queue Worker*" 2>nul
echo Services closed.
