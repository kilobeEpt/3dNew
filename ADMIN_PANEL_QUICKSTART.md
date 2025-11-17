# Admin Panel Quick Start Guide

## Prerequisites

- PHP 7.4+
- MySQL/MariaDB database
- Node.js and npm (for building assets)
- Web server (Apache/Nginx) or PHP built-in server

## Installation

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Configure Environment

Copy `.env.example` to `.env` and configure:

```env
# Database
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_user
DB_PASS=your_password

# JWT Secret (generate a random string)
JWT_SECRET=your_secret_key_here

# Admin Settings
ADMIN_EMAIL=admin@example.com
```

### 3. Set Up Database

```bash
# Run migrations
php database/migrate.php

# Seed database (creates admin user)
php database/seed.php
```

### 4. Build Assets

```bash
# Build minified CSS and JS
npm run build
```

### 5. Start Development Server

```bash
# Option 1: PHP built-in server
php -S localhost:8000 -t public_html

# Option 2: Use the dev-server script
./dev-server.sh
```

## Accessing the Admin Panel

1. Open your browser to: `http://localhost:8000/admin/index.html`
2. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
3. **IMPORTANT**: Change the default password immediately!

## First Steps

### Change Default Password

1. Click on the user menu (top right)
2. Select "Profile"
3. Update your password
4. Save changes

### Configure Site Settings

1. Navigate to "Settings" in the sidebar
2. Review and update:
   - General settings (site name, contact email)
   - Billing settings (tax rate)
   - Email settings (SMTP configuration)
3. Click "Save Changes"

### Add Content

#### Services
1. Go to "Services"
2. Click "Add Service"
3. Fill in service details
4. Toggle visibility as needed
5. Save

#### Materials
1. Go to "Materials"
2. Click "Add Material"
3. Set pricing and stock levels
4. Save

#### Gallery
1. Go to "Gallery"
2. Click "Upload Image"
3. Select one or multiple images
4. Images are automatically compressed
5. Upload

#### News/Blog
1. Go to "News"
2. Click "Add Post"
3. Use the rich text editor for content
4. Set status (Draft/Published)
5. Save

## Features Overview

### Dashboard
- View key metrics and statistics
- Monitor submission trends
- Track conversion rates
- See recent activity

### Analytics
- Charts powered by Chart.js
- Submission volume over time
- Top services breakdown
- Request status distribution

### Management
- **Services**: Manage service offerings
- **Materials**: Track inventory and pricing
- **Pricing Rules**: Configure dynamic pricing
- **Gallery**: Upload and organize images
- **News**: Create blog posts and announcements
- **Settings**: Configure site-wide settings
- **Requests**: Manage customer inquiries
- **Estimates**: View cost estimate submissions
- **Audit Logs**: Track all admin actions

## User Roles

### Super Admin
- Full access to all features
- Can manage users and settings
- Can perform all CRUD operations

### Admin
- Read, write, and delete access
- Cannot manage users or critical settings

### Editor
- Read and write access
- Cannot delete items

### Viewer
- Read-only access
- View reports and data

## Security

### Best Practices

1. **Change default credentials immediately**
2. **Use strong passwords** (minimum 8 characters, mix of letters, numbers, symbols)
3. **Enable HTTPS** in production
4. **Keep JWT_SECRET secure** and never commit to version control
5. **Review audit logs** regularly
6. **Set appropriate permissions** for uploaded files
7. **Keep dependencies updated**

### Token Management

- Access tokens expire after 1 hour
- Refresh tokens expire after 7 days
- Tokens are automatically refreshed
- Manual logout clears all tokens

## Development

### File Structure

```
/admin
├── index.html              # Main SPA entry point
├── assets/
│   ├── css/
│   │   └── admin.css       # Admin styles
│   ├── js/
│   │   ├── app.js          # Main app
│   │   ├── api.js          # API service
│   │   ├── auth.js         # Auth service
│   │   ├── router.js       # Router
│   │   ├── state.js        # State management
│   │   ├── components/     # UI components
│   │   ├── views/          # Page views
│   │   └── utils/          # Utilities
│   └── dist/               # Minified files (production)
```

### Development Workflow

1. **Make changes** to source files in `/admin/assets/`
2. **Test locally** with development server
3. **Build for production**: `npm run build`
4. **Deploy** minified files from `/admin/assets/dist/`

### Adding a New View

1. Create view file: `/admin/assets/js/views/my-view.js`

```javascript
import { API } from '../api.js';
import { Toast } from '../components/toast.js';

export class MyView {
    constructor() {
        this.container = document.getElementById('admin-content');
    }

    async render() {
        this.container.innerHTML = '<h1>My View</h1>';
    }
}
```

2. Register route in `/admin/assets/js/app.js`:

```javascript
import { MyView } from './views/my-view.js';

this.router.add('/my-route', () => new MyView().render());
```

3. Add navigation item in `/admin/index.html`:

```html
<a href="#/my-route" class="nav-item" data-route="my-route">
    <svg><!-- icon --></svg>
    <span>My View</span>
</a>
```

## Production Deployment

### 1. Build Assets

```bash
npm run build
```

### 2. Update HTML

Change `/admin/index.html` to use minified assets:

```html
<link rel="stylesheet" href="/admin/assets/dist/admin.min.css">
<script type="module" src="/admin/assets/dist/app.min.js"></script>
```

### 3. Configure Web Server

**Apache (.htaccess already configured)**

Ensure mod_rewrite is enabled.

**Nginx**

```nginx
location /admin {
    try_files $uri $uri/ /admin/index.html;
}
```

### 4. Set Permissions

```bash
chmod 755 /path/to/admin
chmod 644 /path/to/admin/index.html
chmod -R 755 /path/to/admin/assets
chmod -R 775 /path/to/uploads
```

### 5. Enable HTTPS

Use Let's Encrypt or your SSL certificate provider.

## Troubleshooting

### Login Issues

- **Check credentials**: Verify username and password
- **Check database**: Ensure users table has admin user
- **Check JWT_SECRET**: Must be set in .env
- **Check browser console**: Look for JavaScript errors
- **Clear browser cache**: Old tokens may cause issues

### API Errors

- **401 Unauthorized**: Token expired or invalid (should auto-refresh)
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Check API endpoint path
- **500 Server Error**: Check PHP error logs

### Build Issues

- **npm install fails**: Check Node.js version (14+)
- **Minification errors**: Check for syntax errors in source files
- **Missing dist files**: Run `npm run build`

### Performance

- **Slow dashboard**: Check analytics query performance
- **Large file uploads**: Increase PHP upload limits
- **Memory issues**: Increase PHP memory_limit

## Support

For detailed documentation, see:
- **ADMIN_API.md**: Complete API reference
- **ADMIN_SECURITY.md**: Security best practices
- **ADMIN_PANEL_README.md**: Full feature documentation

## Changelog

### Version 1.0.0 (Initial Release)
- Complete admin panel SPA
- Dashboard with analytics
- CRUD operations for all entities
- Role-based access control
- Image upload and optimization
- Rich text editor for news
- Audit logging
- Toast notifications
- Modal dialogs
- Responsive design
- Build pipeline
