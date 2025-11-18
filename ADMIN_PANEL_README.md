# Admin Panel Documentation

## Overview

The Admin Panel is a standalone Single Page Application (SPA) built with vanilla JavaScript that provides a comprehensive interface for managing all aspects of the 3D printing services platform.

## Features

### 1. Authentication
- JWT-based authentication with automatic token refresh
- Secure login/logout functionality
- Password reset capability
- Session management with automatic logout on token expiration

### 2. Dashboard
- Real-time analytics and metrics
- Submission volume trends (Chart.js)
- Conversion rate tracking
- Revenue statistics
- Top services visualization
- Request status distribution
- Recent activity log

### 3. Management Views

#### Services
- List all services with search and pagination
- Create, edit, and delete services
- Toggle service visibility
- Role-based access controls

#### Materials
- Manage material inventory
- Track stock levels
- Set unit prices and categories
- SKU management

#### Pricing Rules
- Configure dynamic pricing rules
- Discount management
- Conditional pricing logic

#### Gallery
- Upload and manage images
- Automatic image compression before upload (max 1920x1080)
- Drag-and-drop support
- Image reordering and deletion
- Support for JPEG, PNG, GIF, WebP

#### News/Blog
- Rich text editor (Quill.js) for content creation
- Draft and publish workflow
- SEO metadata management
- Automatic slug generation

#### Site Settings
- Grouped settings management
- Bulk update capability
- Type-aware form controls (text, number, boolean, etc.)
- Role-restricted access (super_admin only)

#### Customer Requests
- View and filter customer requests
- Update request status (pending, in_progress, completed, cancelled)
- Real-time status updates
- Filter by status

#### Cost Estimates
- View all cost estimates
- Detailed estimate information
- File attachment tracking

#### Audit Logs
- Complete activity trail
- Filter by action type and resource
- Admin user tracking
- IP address logging

### 4. UI Components

#### Toast Notifications
- Success, error, warning, and info messages
- Auto-dismiss with configurable duration
- Smooth slide-in/out animations

#### Modal Dialogs
- Confirm dialogs for destructive actions
- Alert messages
- Form modals with validation
- Customizable size (default, large)

#### Tables
- Responsive design
- Sortable columns
- Pagination
- Search functionality

### 5. Technical Features

#### API Integration
- Centralized API service
- Automatic token refresh
- Error handling and retry logic
- Optimistic UI updates

#### State Management
- Centralized state manager
- User authentication state
- Route management
- Permission checking

#### Routing
- Client-side hash-based routing
- Active navigation highlighting
- Dynamic page title updates

#### Permissions
- Role-based UI controls
- super_admin: Full access
- admin: Read, write, delete
- editor: Read, write
- viewer: Read only

#### Image Optimization
- Automatic compression before upload
- Maximum dimensions: 1920x1080px
- Quality: 80%
- Maintains aspect ratio

#### Build Pipeline
- CSS minification
- JavaScript minification
- Module bundling
- Source file preservation

## File Structure

```
/admin
├── index.html              # Main SPA shell
├── assets/
│   ├── css/
│   │   └── admin.css       # Admin-specific styles
│   ├── js/
│   │   ├── app.js          # Main application entry point
│   │   ├── api.js          # API service
│   │   ├── auth.js         # Authentication service
│   │   ├── router.js       # Client-side router
│   │   ├── state.js        # State management
│   │   ├── components/     # Reusable UI components
│   │   │   ├── toast.js
│   │   │   └── modal.js
│   │   ├── views/          # Page views
│   │   │   ├── dashboard.js
│   │   │   ├── services.js
│   │   │   ├── materials.js
│   │   │   ├── pricing-rules.js
│   │   │   ├── gallery.js
│   │   │   ├── news.js
│   │   │   ├── settings.js
│   │   │   ├── requests.js
│   │   │   ├── estimates.js
│   │   │   └── audit-logs.js
│   │   └── utils/          # Helper utilities
│   │       └── helpers.js
│   └── dist/               # Minified production files
└── routes.php              # Server-side routes (legacy)
```

## Usage

### Accessing the Admin Panel

Navigate to: `https://yourdomain.com/admin/index.html`

### Default Credentials

After running database seeds:
- Username: `admin`
- Password: `admin123` (change immediately in production)

### Building for Production

```bash
# Install dependencies
npm install

# Build minified assets
npm run build

# Watch for changes during development
npm run watch
```

### Development Mode

For development, you can directly use the non-minified files by accessing:
- `/admin/index.html`

The app will automatically load development assets.

### Production Mode

Update `admin/index.html` to use minified assets:

```html
<link rel="stylesheet" href="/admin/assets/dist/admin.min.css">
<script type="module" src="/admin/assets/dist/app.min.js"></script>
```

## Security Considerations

1. **Authentication**: All API requests include JWT Bearer token
2. **Token Refresh**: Automatic token renewal before expiration
3. **HTTPS**: Always use HTTPS in production
4. **CORS**: Properly configured for admin domain
5. **Role-Based Access**: UI elements hidden/disabled based on permissions
6. **Audit Logging**: All admin actions are logged
7. **Session Timeout**: Automatic logout after token expiration

## Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

Modern browsers with ES6 module support required.

## Third-Party Libraries

- **Chart.js** (v4.4.0): Analytics visualizations
- **Quill** (v2.0.0): Rich text editor for news posts

Loaded via CDN for ease of deployment.

## Responsive Design

The admin panel is fully responsive and optimized for:
- Desktop: 1024px and above
- Tablet: 768px - 1023px
- Mobile: Below 768px (sidebar collapses to toggle menu)

## Customization

### Brand Colors

Edit CSS variables in `/admin/assets/css/admin.css`:

```css
:root {
    --color-primary: #2563eb;
    --color-secondary: #7c3aed;
    /* ... other variables */
}
```

### Adding New Views

1. Create view file in `/admin/assets/js/views/`
2. Register route in `/admin/assets/js/app.js`
3. Add navigation item in `/admin/index.html`

Example:

```javascript
// In app.js
import { MyNewView } from './views/my-new-view.js';

this.router.add('/my-route', () => new MyNewView().render());
```

## API Endpoints

All admin API endpoints are documented in:
- `ADMIN_API.md`: Complete API reference
- `ADMIN_SECURITY.md`: Security best practices

## Support

For issues or questions:
1. Check the API documentation
2. Review audit logs for errors
3. Check browser console for JavaScript errors
4. Verify JWT token validity

## License

This admin panel is part of the PHP API Platform project.
