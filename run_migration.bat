@echo off
REM Migration Script for Module Permissions
REM Run this script to add module and settings permissions to database

echo ========================================
echo Module Permissions Migration
echo ========================================
echo.

REM Set default values
set DB_HOST=localhost
set DB_NAME=tuzycms
set DB_USER=root

REM Prompt for database credentials
set /p DB_HOST="Database Host (default: localhost): " || set DB_HOST=localhost
set /p DB_NAME="Database Name (default: tuzycms): " || set DB_NAME=tuzycms
set /p DB_USER="Database User (default: root): " || set DB_USER=root
set /p DB_PASS="Database Password: "

echo.
echo Running migration...
echo.

REM Run the migration
mysql -h %DB_HOST% -u %DB_USER% -p%DB_PASS% %DB_NAME% < database\migrations\add_module_permissions.sql

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Migration completed successfully!
    echo ========================================
) else (
    echo.
    echo ========================================
    echo Migration failed! Please check your database credentials.
    echo ========================================
)

echo.
pause
