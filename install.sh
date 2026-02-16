#!/bin/bash

echo "================================================"
echo "WolfCore CTF Bank - Installation Script"
echo "================================================"
echo ""

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo "ERROR: MySQL is not installed. Please install MySQL first."
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "ERROR: PHP is not installed. Please install PHP first."
    exit 1
fi

echo "PHP Version:"
php -v | head -n 1
echo ""

# Prompt for MySQL credentials
echo "Enter MySQL root password (leave empty if no password):"
read -s MYSQL_PASSWORD

if [ -z "$MYSQL_PASSWORD" ]; then
    MYSQL_CMD="mysql -u root"
else
    MYSQL_CMD="mysql -u root -p$MYSQL_PASSWORD"
fi

# Test MySQL connection
echo "Testing MySQL connection..."
if ! $MYSQL_CMD -e "SELECT 1;" &> /dev/null; then
    echo "ERROR: Could not connect to MySQL. Please check your credentials."
    exit 1
fi

echo "MySQL connection successful!"
echo ""

# Create database and tables
echo "Creating database and tables..."
$MYSQL_CMD < setup.sql

if [ $? -eq 0 ]; then
    echo "Database setup completed successfully!"
else
    echo "ERROR: Database setup failed."
    exit 1
fi

# Create uploads directory
echo ""
echo "Creating uploads directory..."
mkdir -p uploads
chmod 755 uploads

echo ""
echo "Setting file permissions..."
chmod 644 *.php
chmod 644 style.css
chmod 644 setup.sql
chmod 644 README.md
chmod 600 config.php
chmod 600 flag.txt

echo ""
echo "================================================"
echo "Installation Complete!"
echo "================================================"
echo ""
echo "Default Users:"
echo "  Username: admin      Password: password  (VIP)"
echo "  Username: john_doe   Password: password"
echo "  Username: sarah_smith Password: password"
echo "  Username: vip_user   Password: password  (VIP)"
echo ""
echo "To start the application:"
echo "  1. Using Apache/Nginx: Place files in web root"
echo "  2. Using PHP built-in server: php -S localhost:8000"
echo ""
echo "CTF Flags:"
echo "  - SQL Injection: Check 'flags' table"
echo "  - Race Condition: Check 'flags' table"
echo "  - File Upload: Check flag.txt file"
echo ""
echo "Happy Hacking!"
