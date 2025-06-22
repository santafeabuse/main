# NeuraNest Installation Guide for OpenServer

## 📋 Prerequisites
- OpenServer installed and running
- PHP 7.4+ enabled
- MySQL/MariaDB enabled
- PHPMailer library

## 🗄️ Database Setup

### Method 1: Using phpMyAdmin (Recommended)
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Login with your MySQL credentials (usually `root` with no password in OpenServer)
3. Click on "SQL" tab
4. Copy and paste the contents of `database_schema_simple.sql`
5. Click "Go" to execute

### Method 2: Using MySQL Command Line
1. Open Command Prompt as Administrator
2. Navigate to your OpenServer MySQL bin directory:
   ```bash
   cd "C:\OpenServer\modules\database\MySQL-8.0\bin"
   ```
3. Connect to MySQL:
   ```bash
   mysql -u root -p
   ```
4. Execute the schema file:
   ```sql
   source "d:\OSPanel\domains\neuranest_Test\neuranest\database_schema_simple.sql"
   ```

## 📧 PHPMailer Setup

### Download PHPMailer
1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/releases
2. Extract the files to `neuranest/vendor/PHPMailer/`
3. Make sure you have these files:
   - `vendor/PHPMailer/src/PHPMailer.php`
   - `vendor/PHPMailer/src/SMTP.php`
   - `vendor/PHPMailer/src/Exception.php`

### Alternative: Using Composer (if available)
```bash
cd neuranest
composer require phpmailer/phpmailer
```

## ⚙️ Configuration

### 1. Database Configuration
Edit `includes/config.php` if needed:
```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'neuranest');
define('DB_USER', 'root');
define('DB_PASS', ''); // Usually empty in OpenServer
```

### 2. Email Configuration
The email settings in `includes/config.php` are already configured for Yandex SMTP:
```php
// Email configuration
define('SMTP_HOST', 'smtp.yandex.ru');
define('SMTP_USER', 'neuranest@yandex.com');
define('SMTP_PASS', 'dpciontyiyrjhrhk');
define('SMTP_PORT', 587);
```

### 3. Site URL Configuration
Update the site URL in `includes/config.php`:
```php
define('SITE_URL', 'http://localhost/neuranest');
```

## 📁 File Permissions
Make sure the following directories are writable:
- `assets/images/avatars/` (for user avatar uploads)
- `temp/` (for temporary files, create if needed)

## 🧪 Testing the Installation

### 1. Test Database Connection
Create a test file `test_db.php`:
```php
<?php
require_once 'includes/database.php';
try {
    $db = new Database();
    echo "Database connection successful!";
    
    // Test query
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<br>Users in database: " . $result['count'];
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
```

### 2. Test Email Functionality
Create a test file `test_email.php`:
```php
<?php
require_once 'includes/mail_config.php';

$result = send_email(
    'your-email@example.com', 
    'Test Email', 
    'This is a test email from NeuraNest'
);

if ($result['success']) {
    echo "Email sent successfully!";
} else {
    echo "Email failed: " . $result['message'];
}
?>
```

## 🚀 Accessing the Application

1. **Main Page**: `http://localhost/neuranest/`
2. **Registration**: `http://localhost/neuranest/auth/register.php`
3. **Login**: `http://localhost/neuranest/auth/login.php` (when created)
4. **Admin Login**: 
   - Email: `admin@neuranest.com`
   - Password: `admin123`

## 🔧 Troubleshooting

### Common Issues:

#### 1. Database Connection Error
- Check if MySQL service is running in OpenServer
- Verify database credentials in `config.php`
- Make sure the `neuranest` database exists

#### 2. Email Not Sending
- Check internet connection
- Verify SMTP credentials
- Check if port 587 is not blocked by firewall
- Try using a different email service (Gmail, etc.)

#### 3. File Upload Issues
- Check if `assets/images/avatars/` directory exists and is writable
- Verify PHP upload settings in `php.ini`:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

#### 4. Session Issues
- Make sure session directory is writable
- Check PHP session settings in `php.ini`

#### 5. PHPMailer Not Found
- Verify PHPMailer files are in the correct location
- Check file paths in `includes/mail_config.php`

### Error Logs
Check OpenServer logs for detailed error information:
- PHP errors: `OpenServer/userdata/logs/PHP_*.log`
- MySQL errors: `OpenServer/userdata/logs/MySQL_*.log`
- Apache errors: `OpenServer/userdata/logs/Apache_*.log`

## 📝 Default Login Credentials

After installation, you can use these credentials to test:
- **Email**: `admin@neuranest.com`
- **Password**: `admin123`

## 🔄 Next Steps

After successful installation:
1. Test user registration process
2. Verify email sending functionality
3. Test chat interface (when implemented)
4. Configure premium payment integration
5. Set up SSL certificate for production

## 🆘 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Verify all file paths and permissions
3. Check OpenServer logs for detailed errors
4. Ensure all required PHP extensions are enabled

## 🔒 Security Notes

For production deployment:
1. Change default admin password
2. Use strong database passwords
3. Enable HTTPS
4. Configure proper file permissions
5. Update SMTP credentials
6. Enable PHP error logging (disable display_errors)

---

**Note**: This installation guide is specifically for OpenServer development environment. For production deployment, additional security measures and configurations will be required.