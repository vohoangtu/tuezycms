@echo off
REM Setup Configurations Table
REM Run this to create configurations table and seed data

echo ========================================
echo Setup Configurations Table
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
echo Creating configurations table...
echo.

REM Run the migration
mysql -h %DB_HOST% -u %DB_USER% -p%DB_PASS% %DB_NAME% < database\migrations\create_configurations_table.sql

if %ERRORLEVEL% NEQ 0 (
    echo ❌ Migration failed!
    pause
    exit /b 1
)

echo ✅ Configurations table created!
echo.
echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Go to /admin/settings
echo 2. You will see configuration items
echo 3. Toggle them on/off and configure settings
echo.
pause
