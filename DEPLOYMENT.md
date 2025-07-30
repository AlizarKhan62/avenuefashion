# AWS EC2 Deployment Guide for Avenue Fashion E-commerce

## Prerequisites
- AWS EC2 instance with PHP 7.4+ and MySQL
- Apache/Nginx web server
- Replicate API account and new API key

## 🚀 Deployment Steps

### 1. Clone Repository on EC2
```bash
cd /var/www/html
sudo git clone https://github.com/AlizarKhan62/avenuefashion.git
sudo chown -R www-data:www-data avenuefashion/
sudo chmod -R 755 avenuefashion/
```

### 2. Set Environment Variables (SECURE METHOD)
```bash
# Edit Apache environment file
sudo nano /etc/apache2/envvars

# Add this line at the end:
export REPLICATE_API_TOKEN="your_new_replicate_api_key_here"

# Restart Apache
sudo systemctl restart apache2
```

### 3. Alternative: Using .htaccess (if environment variables don't work)
Create a `.htaccess` file in your project root:
```apache
SetEnv REPLICATE_API_TOKEN your_new_replicate_api_key_here
```

### 4. Database Configuration
```bash
# Import database
mysql -u your_username -p your_database_name < DATABASE_FILE/your_database.sql

# Update database connection in includes/db.php
```

### 5. File Permissions
```bash
sudo chown -R www-data:www-data /var/www/html/avenuefashion/
sudo chmod -R 755 /var/www/html/avenuefashion/
sudo chmod -R 777 /var/www/html/avenuefashion/uploads/
sudo chmod -R 777 /var/www/html/avenuefashion/tryon_results/
```

### 6. Test the Application
- Visit your EC2 public IP
- Test the virtual try-on feature
- Check error logs: `sudo tail -f /var/log/apache2/error.log`

## 🔒 Security Best Practices

### For GitHub (Already Implemented):
- ✅ API key removed from code
- ✅ .gitignore file created
- ✅ Environment variable approach implemented
- ✅ Config files excluded from version control

### For AWS EC2:
1. **Use IAM roles** instead of hardcoded credentials
2. **Set up SSL/HTTPS** with Let's Encrypt
3. **Configure firewall** (Security Groups)
4. **Regular security updates**
5. **Database security** (strong passwords, limited access)

## 📁 Project Structure After Deployment
```
/var/www/html/avenuefashion/
├── admin_area/
├── config/
│   ├── api_config.example.php  (tracked in git)
│   └── api_config.php          (NOT tracked in git)
├── includes/
├── styles/
├── tryon_api.php               (now secure)
├── .gitignore                  (protects sensitive files)
└── README.md
```

## 🔧 Troubleshooting

### If Virtual Try-On Doesn't Work:
1. Check environment variable: `echo $REPLICATE_API_TOKEN`
2. Check PHP can access it: `<?php echo getenv('REPLICATE_API_TOKEN'); ?>`
3. Check Apache error logs
4. Verify API key is valid in Replicate dashboard

### Common Issues:
- **Permission denied**: Fix file permissions
- **API key not found**: Verify environment variable setup
- **Database connection**: Update db.php with EC2 MySQL credentials

## 📞 Support
If you encounter issues during deployment, check:
1. Apache error logs: `/var/log/apache2/error.log`
2. PHP error logs: `/var/log/php/error.log`
3. Application logs in your project directory
