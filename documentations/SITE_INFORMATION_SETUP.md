# Site Information Setup Documentation

## Overview
The Site Information feature allows you to configure and manage contact information for your website. **Only one record is maintained** - the system automatically updates the existing record if it exists, or creates a new one if it doesn't.

## Features

### Backend Components

#### 1. Model (`app/Models/SiteInformation.php`)
- Manages site information data
- **Fillable fields:**
  - `email_address` (required)
  - `tel_no` (required)
  - `phone_no` (required)
  - `telegram` (nullable)
  - `facebook` (required)
  - `viber` (nullable)
  - `whatsapp` (nullable)

#### 2. Migration (`database/migrations/2025_10_01_000000_create_site_informations_table.php`)
- Creates `site_informations` table with all necessary fields
- Includes timestamps for tracking

#### 3. Request Validation (`app/Http/Requests/SiteInformationRequest.php`)
**Production-ready validation with regex patterns:**

- **Email Address**
  - Required
  - Valid email format with DNS validation
  - Regex: `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/`

- **Telephone & Phone Numbers**
  - Required
  - International format support
  - Regex: `/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/`
  - Examples: +1-234-567-8900, (123) 456-7890, +44 20 1234 5678

- **Telegram** (Optional)
  - Username format: `@username` or `username`
  - URL format: `https://t.me/username` or `https://telegram.me/username`
  - Regex: `/^@?[a-zA-Z0-9_]{5,32}$|^https?:\/\/(t\.me|telegram\.me)\/[a-zA-Z0-9_]{5,32}$/`

- **Facebook** (Required)
  - Full URL: `https://facebook.com/yourpage`
  - Short URL: `fb.com/yourpage`
  - Username only: `yourpage`
  - Regex: `/^(https?:\/\/)?(www\.)?(facebook|fb)\.com\/[a-zA-Z0-9.]+\/?$|^[a-zA-Z0-9.]+$/`

- **Viber** (Optional)
  - Phone number format
  - Same regex as telephone numbers

- **WhatsApp** (Optional)
  - Phone number format
  - Same regex as telephone numbers

#### 4. Controller (`app/Http/Controllers/SiteInformationController.php`)
**Single-record management logic:**

- `index()` - Display setup page with existing data
- `store()` - Create or update the single record
- `destroy()` - Reset/delete site information (admin only)

### Frontend Components

#### Vue Component (`resources/js/pages/SiteInformation/Setup.vue`)
**Responsive, modern UI/UX matching Products pages:**

- **Status Badge**: Shows if information is configured or not
- **Form Fields**: All contact information fields with validation
- **Real-time Validation**: Client-side and server-side validation
- **Toast Notifications**: Success/error messages
- **Reset Functionality**: Admin can reset the information
- **Responsive Design**: Mobile-first, adapts to all screen sizes
- **Dark Mode Support**: Full dark mode compatibility

#### Toast Component (`resources/js/pages/SiteInformation/Toast.vue`)
- Reusable notification system
- Auto-dismiss after 3.5 seconds
- Success/error/info/warning types

### Routes (`routes/web.php`)
```php
// Protected by auth and verified middleware
Route::get('site-information', [SiteInformationController::class, 'index'])
    ->name('site-information.index');
    
Route::post('site-information', [SiteInformationController::class, 'store'])
    ->name('site-information.store');
    
Route::delete('site-information', [SiteInformationController::class, 'destroy'])
    ->name('site-information.destroy');
```

## Installation & Setup

### Step 1: Run Migration
```bash
php artisan migrate --path=database/migrations/2025_10_01_000000_create_site_informations_table.php
```

Or run all pending migrations:
```bash
php artisan migrate
```

### Step 2: Access the Setup Page
Navigate to: `http://yourapp.test/site-information`

### Step 3: Fill in the Information
1. **Required Fields:**
   - Email Address
   - Telephone Number
   - Phone Number
   - Facebook Profile

2. **Optional Fields:**
   - Telegram
   - Viber
   - WhatsApp

### Step 4: Save
Click "Save Information" or "Update Information" button.

## Usage Examples

### Valid Input Examples

**Email:**
```
contact@company.com
support@yourdomain.org
info@business.co.uk
```

**Phone Numbers (Tel, Phone, Viber, WhatsApp):**
```
+1-234-567-8900
(123) 456-7890
+44 20 1234 5678
123.456.7890
1234567890
```

**Telegram:**
```
@johndoe
johndoe
https://t.me/johndoe
https://telegram.me/johndoe
```

**Facebook:**
```
facebook.com/yourpage
https://facebook.com/yourpage
https://www.fb.com/yourpage
yourpage
yourpage.business
```

## API Endpoints

### GET `/site-information`
Returns the setup page with existing data (if any).

**Response Props:**
```typescript
{
  siteInformation: {
    id: number,
    email_address: string,
    tel_no: string,
    phone_no: string,
    telegram: string | null,
    facebook: string,
    viber: string | null,
    whatsapp: string | null,
    created_at: string,
    updated_at: string
  } | null
}
```

### POST `/site-information`
Creates or updates the site information.

**Request Body:**
```json
{
  "email_address": "contact@company.com",
  "tel_no": "+1-234-567-8900",
  "phone_no": "+1-234-567-8900",
  "telegram": "@company",
  "facebook": "facebook.com/company",
  "viber": "+1-234-567-8900",
  "whatsapp": "+1-234-567-8900"
}
```

**Success Response:**
- Redirects to `site-information.index`
- Flash message: "Site information created/updated successfully."

**Error Response:**
- Returns validation errors with specific messages

### DELETE `/site-information`
Resets/deletes the site information (admin only).

**Success Response:**
- Redirects to `site-information.index`
- Flash message: "Site information has been reset successfully."

**Error Response:**
- 403 Forbidden if not admin

## UI/UX Features

### Responsive Design
- **Mobile (< 640px)**: Single column layout
- **Tablet (640px - 1024px)**: Two-column grid for some fields
- **Desktop (> 1024px)**: Optimized two-column layout

### Visual Feedback
- **Loading States**: Spinner animation during submission
- **Disabled States**: Prevents multiple submissions
- **Error States**: Red borders and error messages
- **Success States**: Green badges and toast notifications

### Accessibility
- **ARIA Labels**: Proper labels for screen readers
- **Keyboard Navigation**: Full keyboard support
- **Focus Indicators**: Clear focus states
- **Color Contrast**: WCAG AA compliant

### Dark Mode
- Automatic theme switching
- All components support dark mode
- Proper contrast in both themes

## Security Features

1. **Authentication Required**: All routes protected by auth middleware
2. **Email Verification**: Routes require verified users
3. **Admin-Only Reset**: Only admins can delete site information
4. **Input Validation**: Both client-side and server-side validation
5. **SQL Injection Protection**: Laravel's query builder and Eloquent
6. **XSS Protection**: Vue's automatic escaping
7. **CSRF Protection**: Laravel's CSRF token on all POST/DELETE requests

## Single-Record Logic

The system enforces a **single-record constraint**:

```php
// In SiteInformationController@store
$siteInfo = SiteInformation::first();

if ($siteInfo) {
    // Update existing record
    $siteInfo->update($validated);
} else {
    // Create new record
    SiteInformation::create($validated);
}
```

This ensures:
- Only one set of contact information exists
- No duplicate records
- Simple data management
- Clear single source of truth

## Testing

### Manual Testing Checklist

- [ ] Create new site information
- [ ] Update existing site information
- [ ] Validate required fields
- [ ] Validate optional fields
- [ ] Test email format validation
- [ ] Test phone number format validation
- [ ] Test Telegram format validation
- [ ] Test Facebook format validation
- [ ] Test toast notifications
- [ ] Test responsive design (mobile, tablet, desktop)
- [ ] Test dark mode
- [ ] Test admin reset functionality
- [ ] Test non-admin access (should not see reset button)

### Example Test Data

```json
{
  "email_address": "contact@example.com",
  "tel_no": "+1-555-123-4567",
  "phone_no": "+1-555-987-6543",
  "telegram": "@examplecompany",
  "facebook": "facebook.com/examplecompany",
  "viber": "+1-555-111-2222",
  "whatsapp": "+1-555-333-4444"
}
```

## Troubleshooting

### Issue: Validation errors not displaying
**Solution**: Check browser console for JavaScript errors. Ensure Inertia.js is properly configured.

### Issue: Migration fails
**Solution**: 
1. Check if table already exists: `php artisan migrate:status`
2. Rollback if needed: `php artisan migrate:rollback --step=1`
3. Re-run migration

### Issue: Form not submitting
**Solution**:
1. Check browser network tab for request details
2. Verify CSRF token is present
3. Check Laravel logs: `storage/logs/laravel.log`

### Issue: Reset button not showing
**Solution**: Ensure user has admin privileges. Check `canDeleteProducts` prop in controller.

## Future Enhancements

Potential improvements:
- [ ] Add more social media platforms (Twitter, Instagram, LinkedIn)
- [ ] Add business hours configuration
- [ ] Add physical address fields
- [ ] Add map integration
- [ ] Add API endpoint for frontend consumption
- [ ] Add export/import functionality
- [ ] Add change history tracking
- [ ] Add multiple language support

## Maintenance

### Database Backup
Before making changes, backup the site information:
```bash
php artisan db:backup
```

### Updating Validation Rules
To modify validation rules, edit:
`app/Http/Requests/SiteInformationRequest.php`

### Styling Changes
UI styles can be modified in:
`resources/js/pages/SiteInformation/Setup.vue`

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify database connection
4. Ensure all migrations are run

## License

This feature is part of the MHRHCI Backend application.
