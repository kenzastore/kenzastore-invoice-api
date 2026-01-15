# Kenzastore Invoice Management API

A comprehensive PHP-based invoice management system with PDF generation, user authentication, and RESTful API endpoints. Originally developed for Kenzastore, now open source.

## 🚀 Features

- **User Authentication** - JWT-based secure login system
- **Invoice Management** - Create, read, update, delete invoices
- **PDF Generation** - Automatic PDF generation using Dompdf
- **RESTful API** - Complete API for frontend integration
- **Environment Detection** - Automatic development/production configuration
- **CORS Support** - Cross-origin request handling
- **Database Driven** - MySQL/MariaDB backend
- **Responsive** - Works with web and mobile applications

## 📦 Project Structure

```
kenzastore-invoice-api/
├── api/                    # API endpoints and controllers
│   ├── controllers/        # Business logic controllers
│   ├── middleware/         # Authentication & validation
│   ├── config/            # Configuration files
│   └── utils/             # Utility classes
├── assets/                # Static assets (CSS, JS, images)
├── includes/              # Common PHP includes
├── invoices/              # Generated PDF invoices
├── db/                    # Database schema files
├── vendor/                # Composer dependencies
└── api_tester.html        # API testing interface
```

## 🛠 Installation

### Prerequisites

- PHP 8.1+ with extensions:
  - PDO MySQL
  - mbstring
  - intl
  - gd
  - zip
- MySQL 8.x or MariaDB 10.5+
- Composer 2.x
- Web server (Apache/Nginx) or PHP built-in server

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/kenzastore-invoice-api.git
cd kenzastore-invoice-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Database Setup

1. Create a MySQL database:

```sql
CREATE DATABASE invoicing_system;
```

2. Import the database schema:

```bash
mysql -u root -p invoicing_system < db/blog_cms.sql
```

Or import the full system from `zip/invoicing_system.sql`

### 4. Environment Configuration

1. Copy the environment file:

```bash
cp .env.example .env
```

2. Edit `.env` with your database credentials:

```env
DATABASE_HOST=localhost
DATABASE_NAME=invoicing_system
DATABASE_USER=your_username
DATABASE_PASS=your_password
ENVIRONMENT=production
```

### 5. Set Permissions

```bash
chmod 755 invoices/
chmod 755 uploads/
```

### 6. Start Development Server

```bash
php -S localhost:8080
```

Visit: `http://localhost:8080/kenzastore.my.id/`

## 🔧 Production Deployment

### Web Server Configuration

#### Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/index.php [QSA,L]
```

#### Nginx

```nginx
location /api/ {
    try_files $uri $uri/ /api/index.php?$query_string;
}
```

### Environment Variables

Ensure these are set in your production environment:

- `ENVIRONMENT=production`
- `DATABASE_*` - Production database credentials
- `JWT_SECRET` - Strong secret key for JWT signing
- `CORS_ORIGIN` - Your production domain

## 📚 API Documentation

### Base URL

- **Development**: `http://localhost:8080/kenzastore.my.id/api/index.php`
- **Production**: `https://inv.kenzastore.my.id/api/index.php`

### Authentication

All API endpoints (except login) require a JWT token in the Authorization header:

```http
Authorization: Bearer <your-jwt-token>
```

### Endpoints

#### 1. User Authentication

**POST** `/auth/login`

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**

```json
{
    "status": "success",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

#### 2. Invoice Management

**GET** `/invoices` - List invoices

```
Query Parameters:
- page: Page number (default: 1)
- limit: Items per page (default: 10)
```

**POST** `/invoices` - Create invoice

```json
{
    "invoice_number": "INV-001",
    "invoice_date": "2025-12-02",
    "payment_terms": 14,
    "due_date": "2025-12-16",
    "bill_to": "Customer Name",
    "ship_to": "Shipping Address",
    "notes": "Invoice notes",
    "items": [
        {
            "description": "Product A",
            "quantity": 2,
            "rate": 100.00
        }
    ],
    "tax": 10.0,
    "discount": 0.0,
    "shipping": 0.0
}
```

**GET** `/invoices/{id}` - Get specific invoice

**PUT** `/invoices/{id}` - Update invoice

**DELETE** `/invoices/{id}` - Delete invoice

**POST** `/invoices/{id}/generate` - Generate PDF

```json
{
    "status": "success",
    "pdf_url": "https://inv.kenzastore.my.id/invoices/INV-001.pdf",
    "invoice_number": "INV-001"
}
```

#### 3. Recurring Invoices

**GET** `/recurring-invoices` - List recurring schedules

**POST** `/recurring-invoices` - Create a recurring schedule

```json
{
    "invoice_template_id": 123,
    "frequency": "monthly",
    "start_date": "2026-01-15",
    "status": "active"
}
```

**PUT** `/recurring-invoices/{id}` - Update a schedule

```json
{
    "status": "paused",
    "frequency": "annually"
}
```

**DELETE** `/recurring-invoices/{id}` - Delete a schedule

### 🤖 Automation

To process recurring invoices automatically, set up a CRON job to run:

```bash
php api/utils/process_recurring.php
```

## 🧪 Testing

### API Testing Interface

Open `api/api_tester.html` in your browser for a comprehensive testing interface.

### Using cURL

```bash
# Login
curl -X POST "https://inv.kenzastore.my.id/api/index.php/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Get invoices (replace TOKEN with actual token)
curl -H "Authorization: Bearer TOKEN" \
  "https://inv.kenzastore.my.id/api/index.php/invoices"
```

## 🔒 Security Features

- **JWT Authentication** - Secure token-based authentication
- **Password Hashing** - bcrypt password storage
- **Input Validation** - SQL injection prevention
- **CORS Configuration** - Cross-origin request security
- **Environment Detection** - Different security levels for dev/prod
- **SQL Injection Prevention** - Prepared statements

## 🗄️ Database Schema

### Core Tables

- `users` - User accounts and authentication
- `invoices` - Invoice master data
- `invoice_items` - Individual line items
- `logs` - Activity logging

### Sample Data

The database includes sample users:

- `test@example.com` / `password`
- `karel.it@ydsf.org` / `password`
- `kenzastoreid@gmail.com` / `password`

## 🛠 Development

### Adding New Features

1. **New API Endpoint**: Add to `api/index.php` router
2. **Controller Logic**: Add to `api/controllers/`
3. **Database Changes**: Update schema and migrations
4. **Tests**: Add API tests

### Environment Configuration

The system automatically detects environment:

- **Production**: Domain contains `inv.kenzastore.my.id`
- **Development**: All other domains

### PDF Customization

Modify `api/controllers/InvoiceController::generateInvoiceHtml()` to customize PDF template.

## 🚨 Troubleshooting

### Common Issues

1. **404 on API endpoints**
   - Check web server rewrite rules
   - Verify .htaccess configuration

2. **PDF not generating**
   - Check file permissions on `invoices/` directory
   - Ensure Dompdf is installed: `composer require dompdf/dompdf`

3. **Database connection errors**
   - Verify credentials in `.env`
   - Check MySQL service is running

4. **CORS errors**
   - Check `api/index.php` CORS headers
   - Verify domain configuration

### Debug Mode

Enable debug mode in `.env`:

```env
DEBUG_MODE=true
LOG_ERRORS=true
```

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📞 Support

For issues and questions:

- Check the troubleshooting section
- Review API documentation
- Submit an issue on GitHub

## 🔄 Changelog

### v1.0.0 (2025-12-02)

- Initial release
- Invoice CRUD operations
- PDF generation
- JWT authentication
- RESTful API
- Environment detection
- Production-ready configuration

---

**Kenzastore Invoice API** - Professional invoice management solution built with ❤️
