@echo off

if "%1"=="" goto :help

if "%1"=="start" (
  if "%2"=="debug" (
    START /b "" "%cd%/php.exe" -S localhost:80 -t ../../ ../../index.php > debug.log 2>&1
    echo Debug mode activated
  ) else (
    START /b "" "%cd%/php.exe" -S localhost:80 -t ../../ ../../index.php > nul 2>&1
  )
  echo [%date% %time%]
  echo App running. . .
  goto :eof
) else if "%1"=="stop" (
	echo [%date% %time%]
	taskkill /IM php.exe /F 2>nul >nul && echo App stopped successfully || echo App is already shut down
  goto :eof
) else (
  goto :help
)

:help
echo SNiP Command Line Interface for Clients
echo Usage: app [actions...]
echo.
echo Actions:
echo   start  Starts the application engine.
echo          Alternatively, you can start in debug mode.
echo          Usage: app start debug
echo   stop   Stops the application engine