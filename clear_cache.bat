@echo off
REM Clear Cache Script
REM This script clears all cache to fix Super Admin menu issue

echo ========================================
echo Clear Cache for Super Admin Fix
echo ========================================
echo.

echo Clearing cache files...

REM Delete cache directory if exists
if exist "storage\cache" (
    echo Deleting storage\cache...
    rmdir /s /q "storage\cache"
    mkdir "storage\cache"
    echo ✅ Cache cleared!
) else (
    echo ℹ️ No cache directory found
)

REM Also clear session if needed
if exist "storage\sessions" (
    echo Clearing sessions...
    del /q "storage\sessions\*.*"
    echo ✅ Sessions cleared!
)

echo.
echo ========================================
echo Cache cleared successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Logout from admin panel
echo 2. Login again
echo 3. Menu should now display correctly
echo.
pause
