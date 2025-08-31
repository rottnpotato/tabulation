# Development Scripts for Real-Time Features

This document explains how to run all necessary services for the Pageant Tabulation System with real-time functionality.

## Required Services

The application requires these services to run simultaneously:

1. **Laravel Development Server** (`php artisan serve`) - Main web application
2. **Reverb WebSocket Server** (`php artisan reverb:start`) - Real-time communication
3. **Queue Worker** (`php artisan queue:work`) - Broadcasting events processing

## Available Scripts

### Option 1: PowerShell Script (Recommended for Windows)

```powershell
# Run this in PowerShell
.\start-dev.ps1
```

**Features:**
- ✅ Monitors service health
- ✅ Graceful shutdown with Ctrl+C
- ✅ Color-coded status messages
- ✅ Automatic cleanup on exit

### Option 2: Batch File (Simple Windows)

```cmd
# Run this in Command Prompt
start-dev.bat
```

**Features:**
- ✅ Opens each service in separate windows
- ✅ Simple start/stop with any key press
- ✅ Easy to see individual service logs

### Option 3: Composer Script (Laravel Standard)

```bash
# Full development environment with logs
composer run dev
```

**Features:**
- ✅ Laravel's built-in development script
- ✅ Color-coded output for each service
- ✅ Integrated with Composer workflow
- ✅ Optimized for performance (Pail removed)

### Option 4: NPM Scripts (Cross-platform)

```bash
# Production mode (no Vite hot reload)
npm run serve

# Development mode (includes Vite hot reload)
npm run serve:dev
```

**Features:**
- ✅ Uses concurrently for better output
- ✅ Color-coded service names
- ✅ Cross-platform compatibility
- ✅ Integrated with npm workflow

## Service URLs

Once all services are running:

- **🌐 Web Application:** http://localhost:8000
- **🔌 WebSocket Server:** ws://localhost:8080
- **📊 Queue Processing:** Background (no URL)

## Real-Time Features Enabled

With all services running, these features work in real-time:

### Judge → Tabulator
- Score submissions instantly appear in Tabulator Scores page
- Live score updates with notifications

### Tabulator → Judge  
- Round changes instantly notify judges
- Lock/unlock status updates immediately
- Automatic page refreshes when needed

## Troubleshooting

### Services Won't Start
1. Check if ports 8000 and 8080 are available
2. Ensure `.env` file has correct Reverb configuration
3. Run `php artisan config:clear` if needed

### Real-Time Not Working
1. Verify all three services are running
2. Check browser console for WebSocket errors
3. Ensure queue jobs are being processed: `php artisan queue:failed`

### Stop All Services
- **PowerShell:** Press `Ctrl+C`
- **Batch File:** Press any key
- **Composer:** Press `Ctrl+C`
- **NPM:** Press `Ctrl+C`

## Manual Commands (Alternative)

If you prefer to run services manually in separate terminals:

```bash
# Terminal 1: Laravel Server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: WebSocket Server  
php artisan reverb:start --host=0.0.0.0 --port=8080

# Terminal 3: Queue Worker
php artisan queue:work --tries=3 --timeout=90 --sleep=3

# Terminal 4: Frontend Dev (optional)
npm run dev
```

## Production Deployment

For production, use a process manager like **Supervisor** to manage these services properly.
