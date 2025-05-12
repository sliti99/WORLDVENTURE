@echo off
echo Starting WorldVenture Chat Server...
echo.

:: Check if Node.js is installed
where node >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Node.js is not installed or not in PATH.
    echo Please install Node.js from https://nodejs.org/
    echo.
    pause
    exit /b 1
)

:: Check if npm is installed
where npm >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: npm is not installed or not in PATH.
    echo Please repair your Node.js installation.
    echo.
    pause
    exit /b 1
)

:: Check if dependencies are installed
if not exist node_modules (
    echo Installing dependencies...
    npm install
)

:: Check if .env file exists
if not exist .env (
    echo WARNING: .env file not found. Creating a template...
    echo GEMINI_API_KEY=YOUR_GEMINI_API_KEY_HERE > .env
    echo PORT=3000 >> .env
    echo.
    echo Please edit the .env file and add your Gemini API key.
    echo.
)

:: Start the chat server
echo Starting chat server...
npm start

pause