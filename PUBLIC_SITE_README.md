# Public Site Documentation

## Overview

The public-facing website is a modern, responsive, and accessible frontend built with vanilla JavaScript, semantic HTML5, and modular CSS. It features light/dark theme support, smooth animations, and full integration with the backend API.

## Architecture

### Technology Stack
- **HTML5**: Semantic markup with ARIA labels for accessibility
- **CSS**: Modular CSS with CSS variables for theming
- **JavaScript**: Vanilla ES6+ modules with no framework dependencies
- **Build Tools**: Node.js with clean-css and terser for minification

### File Structure
```
public_html/
├── assets/
│   ├── css/
│   │   ├── variables.css      # CSS custom properties (theme variables)
│   │   ├── reset.css           # CSS reset and base styles
│   │   ├── components.css      # Reusable component styles
│   │   ├── header.css          # Header and navigation styles
│   │   ├── hero.css            # Hero section styles
│   │   ├── gallery.css         # Gallery and lightbox styles
│   │   ├── footer.css          # Footer styles
│   │   └── main.css            # Main CSS file (imports all modules)
│   ├── js/
│   │   ├── api.js              # API client with caching
│   │   ├── theme.js            # Theme manager (light/dark)
│   │   ├── navigation.js       # Navigation component
│   │   ├── lightbox.js         # Gallery lightbox component
│   │   ├── slider.js           # Testimonial slider component
│   │   ├── utils.js            # Utility functions
│   │   ├── main.js             # Main app initialization
│   │   └── pages/              # Page-specific scripts
│   │       ├── home.js
│   │       ├── services.js
│   │       ├── gallery.js
│   │       ├── materials.js
│   │       ├── news.js
│   │       └── contact.js
│   ├── dist/                   # Minified CSS/JS (generated)
│   └── images/                 # Image assets
├── index.html                  # Home page
├── services.html               # Services listing page
├── materials.html              # Materials catalog page
├── gallery.html                # Project gallery page
├── news.html                   # News/blog listing page
├── contact.html                # Contact form page
└── about.html                  # About company page
```

## Features

### Theme Support
- Light and dark themes using CSS variables
- Theme preference persisted in localStorage
- Respects system preference (prefers-color-scheme)
- Smooth theme transitions

### Responsive Design
- Mobile-first approach
- Breakpoints: 640px (sm), 768px (md), 1024px (lg)
- Mobile navigation drawer
- Touch-friendly UI elements

### Accessibility (WCAG AA)
- Semantic HTML5 elements
- ARIA labels and roles
- Keyboard navigation support
- Focus states for interactive elements
- Skip links for screen readers
- Alt text for images
- Form validation with clear error messages

### Animations
- CSS transitions for smooth interactions
- IntersectionObserver for fade-in animations
- Reduced motion support (prefers-reduced-motion)
- Performance-optimized animations

### API Integration
- RESTful API client with caching (5-minute default)
- Loading states and error handling
- Pagination support
- Search and filtering
- Form submissions with validation

### Components

#### Navigation
- Sticky header with scroll shadow
- Mobile drawer menu
- Active page highlighting
- Responsive breakpoints

#### Hero Section
- Animated entrance
- Call-to-action buttons
- Statistics display
- Background shapes

#### Gallery
- Grid layout with responsive columns
- Lightbox with keyboard navigation
- Image lazy loading
- Category filtering

#### Testimonial Slider
- Auto-play with pause on hover
- Navigation controls
- Progress indicators
- Keyboard accessible

#### Contact Form
- Client-side validation
- Error message display
- Success feedback
- CSRF protection ready

## Development

### Build Process
```bash
# Install dependencies
npm install

# Build minified CSS and JS
npm run build

# Build CSS only
npm run build:css

# Build JS only
npm run build:js
```

### Local Development
```bash
# Start PHP development server
php -S localhost:8000 -t public_html

# Or use the dev server script
./dev-server.sh
```

## API Endpoints Used

The public site integrates with the following API endpoints:

- `GET /api/services` - List services (with filtering)
- `GET /api/services/{id}` - Get service details
- `GET /api/materials` - List materials (with categories)
- `GET /api/materials/categories` - Get material categories
- `GET /api/gallery` - List gallery items (with filtering)
- `GET /api/news` - List news posts (with pagination)
- `GET /api/settings` - Get public site settings
- `POST /api/contact` - Submit contact form
- `POST /api/cost-estimates` - Submit cost estimate request

## Customization

### Theming
Edit `/public_html/assets/css/variables.css` to customize:
- Colors (primary, secondary, accent)
- Typography (fonts, sizes, weights)
- Spacing scale
- Border radius
- Transitions timing

### Content
Dynamic content is fetched from the API. To customize:
1. Update content through the admin panel
2. Modify site settings (contact info, company details)
3. Add/edit services, materials, gallery items, news posts

### Styling
Each CSS module can be independently customized:
- `variables.css` - Design tokens
- `components.css` - Reusable components (buttons, cards, forms)
- `header.css` - Navigation and header
- `footer.css` - Footer layout
- `hero.css` - Hero section styles
- `gallery.css` - Gallery and lightbox

## Performance

### Optimizations
- CSS/JS minification and bundling
- Image lazy loading
- API response caching
- Debounced scroll and resize handlers
- IntersectionObserver for animations

### Lighthouse Targets
- Performance: 90+
- Accessibility: 95+
- Best Practices: 90+
- SEO: 90+

## Browser Support
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Android)

## Future Enhancements
- Add service worker for offline support
- Implement Progressive Web App (PWA) features
- Add image optimization pipeline
- Implement lazy loading for below-the-fold content
- Add analytics integration
- Add search functionality
- Add multi-language support
