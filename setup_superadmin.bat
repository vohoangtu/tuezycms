@echo off
REM Quick Setup Script for Super Admin
REM This script will:
REM 1. Run the migration to add permissions
REM 2. Assign super_admin role to your user

echo ========================================
echo Super Admin Quick Setup
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
set /p USER_EMAIL="Your Email (e.g., admin@tuzycms.com): "

echo.
echo Step 1: Running migration to add permissions...
echo.

REM Run the migration
mysql -h %DB_HOST% -u %DB_USER% -p%DB_PASS% %DB_NAME% < database\migrations\add_module_permissions.sql

if %ERRORLEVEL% NEQ 0 (
    echo ❌ Migration failed!
    pause
    exit /b 1
)

echo ✅ Migration completed!
echo.
echo Step 2: Assigning super_admin role to user %USER_EMAIL%...
echo.

REM Create temporary SQL file
echo INSERT INTO user_roles (user_id, role_id) > temp_assign_role.sql
echo SELECT u.id, r.id >> temp_assign_role.sql
echo FROM users u >> temp_assign_role.sql
echo CROSS JOIN roles r >> temp_assign_role.sql
echo WHERE u.email = '%USER_EMAIL%' >> temp_assign_role.sql
echo AND r.name = 'super_admin' >> temp_assign_role.sql
echo ON DUPLICATE KEY UPDATE user_id=VALUES(user_id); >> temp_assign_role.sql

REM Run the role assignment
mysql -h %DB_HOST% -u %DB_USER% -p%DB_PASS% %DB_NAME% < temp_assign_role.sql

if %ERRORLEVEL% NEQ 0 (
    echo ❌ Role assignment failed!
    del temp_assign_role.sql
    pause
    exit /b 1
)

REM Clean up
del temp_assign_role.sql

echo ✅ Super Admin role assigned!
echo.
echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Logout from admin panel
echo 2. Login again
echo 3. You should now see Modules and Settings menu
echo.
pause
