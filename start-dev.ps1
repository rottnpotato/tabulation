# Laravel Development Server with Real-Time Features
# This script starts all necessary services for the pageant tabulation system

Write-Host "Starting Laravel Development Environment..." -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green

# Function to handle cleanup on script exit
function Stop-Services {
    Write-Host "`nStopping all services..." -ForegroundColor Yellow
    Get-Job | Stop-Job
    Get-Job | Remove-Job
    Write-Host "All services stopped." -ForegroundColor Red
}

# Register cleanup function for Ctrl+C
Register-EngineEvent PowerShell.Exiting -Action { Stop-Services }

try {
    # Start Laravel development server
    Write-Host "[1/4] Starting Laravel development server on http://localhost:8000..." -ForegroundColor Cyan
    Start-Job -Name "LaravelServer" -ScriptBlock {
        Set-Location $using:PWD
        php artisan serve --host=0.0.0.0 --port=8000
    } | Out-Null

    # Wait a moment for Laravel server to start
    Start-Sleep -Seconds 2

    # Start Reverb WebSocket server
    Write-Host "[2/4] Starting Reverb WebSocket server on port 8080..." -ForegroundColor Cyan
    Start-Job -Name "ReverbServer" -ScriptBlock {
        Set-Location $using:PWD
        php artisan reverb:start --host=0.0.0.0 --port=8080
    } | Out-Null

    # Start Queue worker for broadcasting
    Write-Host "[3/4] Starting Queue worker for real-time broadcasting..." -ForegroundColor Cyan
    Start-Job -Name "QueueWorker" -ScriptBlock {
        Set-Location $using:PWD
        php artisan queue:work --tries=3 --timeout=90 --sleep=3
    } | Out-Null

    # Optional: Start Vite dev server for hot reloading (uncomment if needed)
    # Write-Host "[4/4] Starting Vite dev server for hot reloading..." -ForegroundColor Cyan
    # Start-Job -Name "ViteServer" -ScriptBlock {
    #     Set-Location $using:PWD
    #     npm run dev
    # } | Out-Null

    Write-Host "[4/4] All services started successfully!" -ForegroundColor Green
    Write-Host "" -ForegroundColor White
    Write-Host "🚀 Application URLs:" -ForegroundColor Yellow
    Write-Host "   📱 Web App:      http://localhost:8000" -ForegroundColor White
    Write-Host "   🔌 WebSocket:    ws://localhost:8080" -ForegroundColor White
    Write-Host "   📊 Queue Jobs:   Processing in background" -ForegroundColor White
    Write-Host "" -ForegroundColor White
    Write-Host "📋 Service Status:" -ForegroundColor Yellow
    
    # Monitor services and show status
    while ($true) {
        $jobs = Get-Job
        $runningCount = ($jobs | Where-Object { $_.State -eq "Running" }).Count
        $failedCount = ($jobs | Where-Object { $_.State -eq "Failed" }).Count
        
        if ($failedCount -gt 0) {
            Write-Host "❌ $failedCount service(s) failed! Check logs below:" -ForegroundColor Red
            Get-Job | Where-Object { $_.State -eq "Failed" } | Receive-Job
            break
        }
        
        Write-Host "`r✅ $runningCount services running... (Press Ctrl+C to stop all)" -NoNewline -ForegroundColor Green
        Start-Sleep -Seconds 5
    }

} catch {
    Write-Host "Error occurred: $($_.Exception.Message)" -ForegroundColor Red
} finally {
    Stop-Services
}
