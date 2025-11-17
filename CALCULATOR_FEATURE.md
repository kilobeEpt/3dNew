# Cost Calculator Feature

## Overview

The Cost Calculator is an interactive 3D printing cost estimation tool that allows users to:
- Configure print parameters (material, quality, dimensions, infill)
- Upload 3D model files (STL, OBJ, 3MF, STEP)
- Get real-time price calculations
- Submit estimates for review
- Download/print summaries

## Features

### Interactive UI
- Material selection from database
- Print quality presets (draft, standard, high)
- Dimension inputs with unit conversion (mm/cm)
- Infill density slider (0-100%)
- Finishing options (sanding, painting, premium)
- Quantity input with bulk discounts

### Real-time Pricing
- Calculates material cost based on volume and density
- Estimates print time based on quality and infill
- Applies finishing costs
- Adds quantity discounts (10+ items: 10% off, 50+ items: 20% off)
- Includes tax calculations

### File Upload
- Drag & drop or click to upload
- Validates file type (STL, OBJ, 3MF, STEP, STP)
- 5MB file size limit
- Secure storage with unique filenames
- Base64 encoding for API transfer

### Data Persistence
- Saves form inputs to localStorage
- Restores on page reload
- Clears on successful submission

### Analytics
- Tracks page views
- Logs calculation attempts
- Records file uploads
- Monitors submission success/errors
- Stores session data for analysis

### Offline Support
- Detects offline status
- Shows warning message
- Prevents submission when offline
- Recovers when back online

## Technical Implementation

### Frontend
- **HTML**: `/public_html/calculator.html`
- **CSS**: `/public_html/assets/css/calculator.css`
- **JavaScript**: `/public_html/assets/js/pages/calculator.js`

### Backend
- **Controller**: `/src/Controllers/Api/CostEstimatesController.php`
- **Model**: `/src/Models/CostEstimate.php`
- **Analytics**: `/src/Controllers/Api/AnalyticsController.php`

### Database
- **Migrations**:
  - `016_add_file_upload_to_cost_estimates.sql` - File upload fields
  - `017_create_analytics_events_table.sql` - Analytics tracking
  
- **Tables**:
  - `cost_estimates` - Stores submitted estimates
  - `cost_estimate_items` - Line items for each estimate
  - `analytics_events` - Usage analytics

### API Endpoints
- `GET /api/materials` - Fetch available materials
- `GET /api/pricing-rules` - Fetch pricing rules
- `GET /api/settings` - Get tax rate and other settings
- `POST /api/cost-estimates` - Submit estimate
- `POST /api/analytics/events` - Log analytics events
- `GET /api/csrf-token` - Get CSRF token for form submission

## Pricing Calculation Logic

### Material Cost
```
volume_cm3 = (width_mm × height_mm × length_mm) / 1000
material_volume = volume_cm3 × (infill / 100)
material_weight = material_volume × 1.24 g/cm³  // PLA density
material_cost = (material_weight / 1000) × material_price_per_kg
```

### Print Time Cost
```
quality_multiplier = {draft: 1.0, standard: 1.3, high: 1.8}
print_time_hours = (volume_cm3 / 10) × quality_multiplier × (infill / 50)
time_cost = print_time_hours × $15/hour
```

### Total Calculation
```
subtotal_per_unit = material_cost + time_cost + finishing_cost
subtotal = subtotal_per_unit × quantity

// Apply bulk discounts
if (quantity >= 50) discount = subtotal × 0.20
else if (quantity >= 10) discount = subtotal × 0.10
else discount = 0

subtotal_after_discount = subtotal - discount
tax_amount = subtotal_after_discount × (tax_rate / 100)
total = subtotal_after_discount + tax_amount
```

## File Upload Security

- Only allowed extensions: STL, OBJ, 3MF, STEP, STP
- Maximum file size: 5MB
- Files sanitized and renamed on server
- Stored outside web root in `/uploads/models/`
- Original filename preserved in database
- MIME type validation

## Usage

### For Users
1. Visit `/calculator.html`
2. Select material from dropdown
3. Configure print settings
4. Enter dimensions
5. Adjust infill density
6. Select finishing options
7. Set quantity
8. Optionally upload 3D model file
9. Fill contact information
10. Submit estimate

### For Developers

#### Add New Material
```sql
INSERT INTO materials (name, slug, unit_price, unit, category)
VALUES ('PLA White', 'pla-white', 25.00, 'kg', 'Filament');
```

#### Add Pricing Rule
```sql
INSERT INTO pricing_rules (name, rule_type, condition_type, discount_type, discount_value, priority)
VALUES ('Holiday Sale', 'global', 'date_range', 'percentage', 15.00, 10);
```

#### Track Custom Event
```javascript
api.post('/analytics/events', {
    event_type: 'custom_event',
    event_category: 'calculator',
    event_data: { key: 'value' }
});
```

## Configuration

### Environment Variables
```env
# Tax rate for calculations
TAX_RATE=8.5

# File upload settings
MAX_UPLOAD_SIZE=5242880  # 5MB in bytes
UPLOAD_DIR=/uploads/models

# CAPTCHA bypass for calculator (development only)
CALCULATOR_BYPASS_CAPTCHA=true
```

### Database Configuration
Run migrations to add required tables:
```bash
php database/migrate.php
```

## Testing

### Manual Testing
1. Test with different materials
2. Verify unit conversions (mm ↔ cm)
3. Test file upload with valid/invalid files
4. Check offline detection
5. Verify localStorage persistence
6. Test form validation
7. Submit estimate and check database
8. Verify email notification

### API Testing
```bash
# Get materials
curl http://localhost:8000/api/materials

# Submit estimate
curl -X POST http://localhost:8000/api/cost-estimates \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "Test User",
    "customer_email": "test@example.com",
    "title": "Test Print",
    "items": [...],
    "captcha_token": "bypass_for_calculator",
    "csrf_token": "..."
  }'
```

## Future Enhancements

- [ ] Real PDF generation (currently placeholder)
- [ ] 3D model preview/viewer
- [ ] Save quotes for later
- [ ] Email quote to customer
- [ ] Integration with payment gateway
- [ ] Advanced pricing rules engine
- [ ] Material comparison tool
- [ ] Print time estimation improvements
- [ ] Multi-file uploads
- [ ] Project templates/presets

## Troubleshooting

### Calculator not loading materials
- Check `/api/materials` endpoint returns data
- Verify database has materials seeded
- Check browser console for errors

### File upload fails
- Ensure `/uploads/models/` directory exists and is writable
- Check file size is under 5MB
- Verify file extension is allowed
- Check server PHP upload limits

### Prices showing $0.00
- Verify materials have `unit_price` set
- Check dimensions are entered correctly
- Ensure units are converted properly
- Review browser console for calculation errors

### Form submission fails
- Check network connection
- Verify CSRF token is valid
- Check required fields are filled
- Review API error messages
- Check server logs

## Support

For issues or questions:
- Check browser console for JavaScript errors
- Review `/logs/app.log` for server errors
- Verify database migrations are applied
- Test with sample data
