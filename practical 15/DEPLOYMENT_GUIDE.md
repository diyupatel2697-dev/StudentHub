# Local Deployment Guide - XAMPP Virtual Host Setup

This guide provides step-by-step instructions for deploying the StudentHub portal locally using XAMPP with a virtual host configuration.

## Prerequisites

- XAMPP installed (https://www.apachefriends.org/)
- Administrator privileges on Windows
- Basic understanding of configuration files

## Step 1: Install XAMPP

1. Download XAMPP from https://www.apachefriends.org/
2. Run the installer with default settings
3. Install to: `C:\xampp`
4. Complete the installation

## Step 2: Copy Project Files

1. Navigate to: `C:\xampp\htdocs`
2. Create a new folder named `studenthub`
3. Copy all files from `practical 15` to `C:\xampp\htdocs\studenthub`

## Step 3: Configure Database

### 3.1 Start MySQL and Apache

1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Click "Start" next to MySQL
4. Wait for both to show green status

### 3.2 Create Database

1. Open browser and go to: http://localhost/phpmyadmin
2. Click "New" in left sidebar
3. Enter database name: `studenthub`
4. Select collation: `utf8mb4_unicode_ci`
5. Click "Create"

### 3.3 Import Schema

1. Click on the `studenthub` database
2. Click "Import" tab
3. Click "Choose File"
4. Navigate to: `C:\xampp\htdocs\studenthub\studenthub_schema.sql`
5. Click "Go" at the bottom
6. Wait for import to complete
7. You should see "Import has been successfully finished"

### 3.4 Verify Tables

1. Click on `studenthub` database
2. You should see tables: students, events, registrations
3. Click on each table to verify data

## Step 4: Configure Virtual Host

### 4.1 Edit httpd-vhosts.conf

1. Navigate to: `C:\xampp\apache\conf\extra`
2. Open `httpd-vhosts.conf` in a text editor
3. Add the following at the end:

```apache
<VirtualHost *:80>
    ServerName studenthub.local
    DocumentRoot "C:/xampp/htdocs/studenthub"
    
    <Directory "C:/xampp/htdocs/studenthub">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/studenthub-error.log"
    CustomLog "logs/studenthub-access.log" common
</VirtualHost>
```

4. Save the file

### 4.2 Edit httpd.conf

1. Navigate to: `C:\xampp\apache\conf`
2. Open `httpd.conf`
3. Search for: `#Include conf/extra/httpd-vhosts.conf`
4. Remove the `#` to uncomment
5. Save the file

## Step 5: Edit Hosts File

1. Press `Windows + R`
2. Type: `notepad C:\Windows\System32\drivers\etc\hosts`
3. Click "Yes" for administrator privileges
4. Add at the end: `127.0.0.1    studenthub.local`
5. Save the file

## Step 6: Restart Apache

1. Go to XAMPP Control Panel
2. Stop Apache
3. Start Apache
4. Wait for green status

## Step 7: Access the Portal

Open browser: `http://studenthub.local/portal.php`

## Step 8: Run Tests

- Database test: `http://studenthub.local/test_connection.php`
- Registration test: `http://studenthub.local/test_registration.php`
- AJAX test: `http://studenthub.local/test_practical10.php`
- Integration test: `http://studenthub.local/integration_test.php`

## Alternative Without Virtual Host

Access directly: `http://localhost/studenthub/portal.php`
