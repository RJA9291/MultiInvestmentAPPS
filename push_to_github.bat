@echo off
setlocal
cd /d "%~dp0"

echo ===================================================
echo   Pushing AIOS Platform code to GitHub (go-live branch)
echo   Folder: %cd%
echo ===================================================
echo.

where git >nul 2>nul
if errorlevel 1 (
    echo Git is not installed on this computer.
    echo.
    echo Download it here first, then double-click this file again:
    echo https://git-scm.com/download/win
    echo.
    pause
    exit /b 1
)

if not exist ".git" (
    echo Initializing git repository...
    git init
    git remote add origin https://github.com/RJA9291/MultiInvestmentAPPS.git
) else (
    echo Existing git repository detected, skipping init.
    git remote get-url origin >nul 2>nul
    if errorlevel 1 git remote add origin https://github.com/RJA9291/MultiInvestmentAPPS.git
)

git checkout -b go-live 2>nul
if errorlevel 1 git checkout go-live

echo.
echo Setting commit identity for this repo (local only, not global)...
git config user.email "abdulrahmanbinjapry92@gmail.com"
git config user.name "Rahman Japry"

echo.
echo Staging all files...
git add .

echo.
echo Committing...
git commit -m "Add working Laravel platform (Sprint 12-15: Identity, Project, Compliance, AI, DataRoom, Document, Investor, Notification, Analytics)"

echo.
echo Pushing to GitHub (go-live branch)...
echo If a browser window pops up asking you to log in to GitHub, that is
echo normal Git authentication - just approve it there.
echo.
git push -u origin go-live

echo.
echo ===================================================
echo DONE. Scroll up to check for any red "error" lines.
echo If it says everything is up to date or shows a push summary,
echo the code is now on GitHub, branch "go-live".
echo ===================================================
pause
