# Production Migration Guide - inv.kenzastore.my.id

## 🎯 **Overview**

Successfully migrated API configuration from development (`localhost:8080`) to production (`inv.kenzastore.my.id`).

## ✅ **Changes Applied**

### **1. API Endpoint Updates**

**Before (Development)**:

```javascript
const API_BASE = 'http://localhost:8080/kenzastore.my.id/api/index.php';
```

**After (Production)**:

```javascript
const API_BASE = 'https://inv.kenzastore.my.id/api/index.php';
```

**Files Updated:**

- ✅ `api/api_tester.html`
- ✅ `api/api_tester_fixed.html`
- ✅ `api/test_login.html`

### **2. PDF URL Configuration**

**Before**: Relative paths (`/invoices/filename.pdf`)  
**After**: Full URLs (`https://inv.kenzastore.my.id/invoices/filename.pdf`)

**Files Updated:**

- ✅ `api/controllers/InvoiceController.php` - PDF generation
- ✅ `api/api_tester.html` - JavaScript URL handling

### **3. Environment-Aware Configuration**

Created **`api/config/config.php`** - Smart configuration that:

- **Detects environment** automatically (development vs production)
- **Production**: Uses `https://inv.kenzastore.my.id`
- **Development**: Uses `http://localhost:8080` (or your local setup)

### **4. CORS Security**

**Development**:

- ✅ CORS enabled (`Access-Control-Allow-Origin: *`)

**Production**:

- ✅ CORS restricted to production domain only
- ✅ Security improved for public API

### **5. Database Configuration**

**Invoice PDF Path Storage**:

- **Before**: `'invoices/filename.pdf'`
- **After**: `'https://inv.kenzastore.my.id/invoices/filename.pdf'`

## 🚀 **Production URLs**

### **API Endpoints**

- **Base URL**: `https://inv.kenzastore.my.id/api/index.php`
- **Login**: `POST /auth/login`
- **Invoices**: `GET /invoices?page=1&limit=10`
- **Create Invoice**: `POST /invoices`
- **Generate PDF**: `POST /invoices/{id}/generate`

### **PDF Access**

- **Direct PDF URLs**: `https://inv.kenzastore.my.id/invoices/{filename}.pdf`

## 🔧 **Environment Configuration**

### **Production Mode**

Automatically triggered when:

1. Domain contains `inv.kenzastore.my.id`
2. Environment variable: `ENVIRONMENT=production`

### **Development Mode**

Used when:

1. Domain is `localhost` or other local domains
2. No `ENVIRONMENT=production` variable set

## 🛠️ **API Usage Examples**

### **JavaScript (Production)**

```javascript
// API calls now use production domain
const API_BASE = 'https://inv.kenzastore.my.id/api/index.php';

fetch(API_BASE + '/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
});

// PDF URLs are returned as full URLs
// Response: { pdf_url: "https://inv.kenzastore.my.id/invoices/INV-123.pdf" }
```

### **PHP (Environment-Aware)**

```php
require_once __DIR__ . '/config/config.php';

// URLs automatically configured based on environment
$apiUrl = Config::getApiBaseUrl();        // Production or development
$invoiceUrl = Config::getInvoiceBaseUrl(); // Production or development

if (Config::isProduction()) {
    echo "Running in production mode";
}
```

## 📁 **Files Created/Modified**

### **New Files**

1. **`api/config/config.php`** - Environment configuration system
2. **`api/PRODUCTION_MIGRATION_GUIDE.md`** - This guide

### **Modified Files**

1. **`api/api_tester.html`** - Production API URLs + PDF fixes
2. **`api/api_tester_fixed.html`** - Production API URLs
3. **`api/test_login.html`** - Production API URLs
4. **`api/controllers/InvoiceController.php`** - Environment-aware PDF URLs
5. **`api/index.php`** - Production CORS configuration

## 🔍 **Testing**

### **Development Testing**

```bash
# Start local server
php -S localhost:8080

# Test API
curl "http://localhost:8080/kenzastore.my.id/api/index.php"

# Test PDF URL generation
curl -X POST "http://localhost:8080/kenzastore.my.id/api/index.php/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### **Production Testing**

```bash
# Test production API
curl "https://inv.kenzastore.my.id/api/index.php"

# Test production PDF access
curl "https://inv.kenzastore.my.id/invoices/test.pdf"
```

## ✅ **Verification Checklist**

- [x] API endpoints use production domain
- [x] PDF URLs are full URLs (not relative)
- [x] CORS properly configured for security
- [x] Environment detection working
- [x] Database stores full PDF URLs
- [x] JavaScript PDF handling simplified
- [x] All API testers updated

## 🎉 **Benefits**

1. **Automatic Environment Detection** - No manual configuration needed
2. **Production-Ready URLs** - Full URLs work from anywhere
3. **Security Improved** - CORS restrictions in production
4. **Easy Migration** - Same codebase works in both environments
5. **Future-Proof** - Easy to add more environments

## 📞 **Support**

The API is now fully configured for production use at:
**`https://inv.kenzastore.my.id`**

All PDF generation, authentication, and invoice management features work seamlessly with the new domain!
