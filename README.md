# Avenue Fashion - E-commerce Website with Virtual Try-On

A modern PHP-based e-commerce platform featuring virtual try-on technology powered by Replicate AI.

## ✨ Features

- **Virtual Try-On**: AI-powered clothing visualization using Replicate API
- **Product Management**: Complete admin panel for managing products, categories, and orders
- **Shopping Cart**: Advanced cart functionality with quantity management
- **User Authentication**: Customer registration and login system
- **Payment Integration**: Stripe payment processing and Cash on Delivery
- **Responsive Design**: Mobile-friendly design with Bootstrap
- **Order Tracking**: Complete order management and tracking system

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+, MySQL
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 4
- **AI Integration**: Replicate API for virtual try-on
- **Payment**: Stripe API
- **Server**: Apache/Nginx

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Replicate API account (for virtual try-on feature)
- Stripe account (for payment processing)

## 🚀 Installation

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/AlizarKhan62/avenuefashion.git
   cd avenuefashion
   ```

2. **Set up database**
   - Import the database from `DATABASE FILE/` directory
   - Update database credentials in `includes/db.php`

3. **Configure API keys**
   ```bash
   # Copy the example config file
   cp config/api_config.example.php config/api_config.php
   
   # Edit the config file and add your API keys
   nano config/api_config.php
   ```

4. **Set file permissions**
   ```bash
   chmod 755 -R .
   chmod 777 -R uploads/
   chmod 777 -R tryon_results/
   ```

5. **Start local server**
   ```bash
   # Using XAMPP/WAMP or
   php -S localhost:8000
   ```

### Production Deployment (AWS EC2)

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed AWS EC2 deployment instructions.

## 🔒 Security

- API keys are stored as environment variables
- Sensitive files are excluded from version control
- Input validation and SQL injection prevention
- Secure file upload handling

## 📁 Project Structure

```
avenuefashion/
├── admin_area/          # Admin panel
├── config/              # Configuration files
├── customer/            # Customer area
├── functions/           # PHP functions
├── includes/            # Include files (header, footer, db)
├── styles/              # CSS files
├── js/                  # JavaScript files
├── uploads/             # Product images
├── tryon_results/       # Virtual try-on results
├── virtual_tryon.php    # Virtual try-on interface
├── tryon_api.php        # API endpoint for try-on
└── index.php            # Homepage
```

## 🔧 Configuration

### Environment Variables (Production)
```bash
export REPLICATE_API_TOKEN="your_replicate_api_key"
export STRIPE_SECRET_KEY="your_stripe_secret_key"
```

### Local Development
Edit `config/api_config.php`:
```php
define('REPLICATE_API_TOKEN', 'your_api_key_here');
```

## 📸 Virtual Try-On Feature

The virtual try-on feature uses Replicate's AI models to overlay clothing items onto user photos:

1. Users upload their photo
2. Select a product to try on
3. AI generates a realistic preview
4. Results are temporarily stored and auto-deleted

## 🛒 Admin Features

- Product management (add, edit, delete)
- Category and manufacturer management
- Order processing and tracking
- Customer management
- Sales analytics
- Coupon management

## 🌐 Live Demo

[Add your live demo URL here when deployed]

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Check the [DEPLOYMENT.md](DEPLOYMENT.md) for common solutions

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- Replicate API for virtual try-on technology
- Bootstrap for responsive design
- Font Awesome for icons
- Stripe for payment processing

---

**Note**: This project is designed for educational and commercial use. Make sure to comply with all applicable laws and API terms of service.
