# Site Settings Implementation Guide

## Overview
A comprehensive Site Name and Site Logo configuration system has been implemented for your Laravel application. This feature is **exclusively accessible to System Admin users** and applies the configured settings to both the authentication pages and the application sidebar.

---

## Features

### ✅ System Admin Only Access
- Only users with the **System Admin** role can access and configure site settings
- Protected by `EnsureUserIsSystemAdmin` middleware

### ✅ Configurable Fields
1. **Site Name**: Text field for application name (max 255 characters)
2. **Site Logo**: Image upload (JPEG, PNG, GIF, SVG, WEBP, max 2MB)

### ✅ Dynamic Display
- Site name and logo are dynamically displayed in:
  - **Authentication pages** (Login, Register, Password Reset, etc.)
  - **Application sidebar** (main navigation)
  
### ✅ Caching
- Settings are cached for 1 hour for optimal performance
- Cache automatically clears when settings are updated

### ✅ API Endpoint
- Public API endpoint: `/api/site-settings`
- Rate limited to 200 requests per minute

---

## Files Created/Modified

### Backend Files Created
1. **Migration**: `database/migrations/2025_10_02_000001_add_site_name_and_logo_to_site_informations_table.php`
2. **Middleware**: `app/Http/Middleware/EnsureUserIsSystemAdmin.php`
3. **Controller**: `app/Http/Controllers/SiteSettingsController.php`

### Frontend Files Created
1. **Page**: `resources/js/pages/SiteSettings/Index.vue`
2. **Toast Component**: `resources/js/pages/SiteSettings/Toast.vue`
3. **Composable**: `resources/js/composables/useSiteSettings.ts`

### Files Modified
1. **Model**: `app/Models/SiteInformation.php` - Added `site_name` and `site_logo` fields
2. **Routes**: `routes/web.php` - Added site settings routes
3. **API Routes**: `routes/api.php` - Added public API endpoint
4. **Middleware**: `app/Http/Middleware/HandleInertiaRequests.php` - Added `isSystemAdmin` flag
5. **Sidebar**: `resources/js/components/AppSidebar.vue` - Added System section with Site Settings menu
6. **Logo Component**: `resources/js/components/AppLogo.vue` - Made dynamic with settings
7. **Auth Layout**: `resources/js/layouts/auth/AuthSimpleLayout.vue` - Made dynamic with settings

---

## Database Schema

```sql
-- Added to site_informations table
site_name VARCHAR(255) DEFAULT 'Laravel Starter Kit'
site_logo VARCHAR(255) NULLABLE
```

---

## Routes

### Web Routes (System Admin Only)
```php
GET  /site-settings                    // View settings page
POST /site-settings                    // Update settings
POST /site-settings/remove-logo        // Remove logo
POST /site-settings/reset              // Reset to defaults
```

### API Routes (Public)
```php
GET /api/site-settings                 // Fetch current settings (cached)
```

---

## Usage Instructions

### For System Admin

#### Step 1: Access Site Settings
1. Login as a **System Admin** user
2. Navigate to the sidebar
3. Look for the **"System"** section at the bottom
4. Click on **"Site Settings"**

#### Step 2: Configure Settings
1. **Site Name**: Enter your desired application name
2. **Site Logo**: 
   - Click "Upload Logo" button
   - Select an image file (JPEG, PNG, GIF, SVG, WEBP)
   - Maximum file size: 2MB
   - Preview will be shown immediately
3. Click **"Save Settings"** to apply changes

#### Step 3: Manage Logo
- **Change Logo**: Upload a new image to replace the current one
- **Remove Logo**: Click the X button on the logo preview, then confirm
- **Reset to Defaults**: Click "Reset to Default" button to restore default settings

---

## How It Works

### 1. Permission Check
The system checks if the logged-in user has the System Admin role:
```php
// In HandleInertiaRequests.php
'isSystemAdmin' => $user ? $user->isSystemAdmin() : false
```

### 2. Frontend Composable
The `useSiteSettings` composable fetches settings from the API:
```typescript
const { siteSettings } = useSiteSettings();
// Returns: { site_name: string, site_logo: string | null }
```

### 3. Dynamic Components
Both `AppLogo` and `AuthSimpleLayout` use the composable to display:
- **Custom logo** if uploaded, or **default icon** if not
- **Custom site name** or "Laravel Starter Kit" as default

### 4. Caching Strategy
- Settings are cached for 1 hour
- Cache key: `site_info_settings`
- Automatically cleared on create/update/delete

---

## File Upload Details

### Storage Location
Uploaded logos are stored in: `storage/app/public/site-assets/`

### Supported Formats
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- SVG (.svg)
- WEBP (.webp)

### Size Limit
Maximum file size: **2MB (2048KB)**

---

## Security

### Access Control
- **Page Access**: Protected by `EnsureUserIsSystemAdmin` middleware
- **API Endpoint**: Public (read-only, cached)
- **File Upload**: Validated on server-side for type and size

### Middleware
```php
// EnsureUserIsSystemAdmin.php
if (!$user || !$user->isSystemAdmin()) {
    abort(403, 'Access denied. Only System Administrators can access this resource.');
}
```

---

## API Response Example

### Success Response
```json
{
  "success": true,
  "data": {
    "site_name": "My Custom App Name",
    "site_logo": "/storage/site-assets/logo123.png"
  },
  "meta": {
    "cached": true
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "An error occurred while fetching site settings",
  "data": {
    "site_name": "Laravel Starter Kit",
    "site_logo": null
  }
}
```

---

## Testing the Feature

### Step 1: Run Migration
```bash
php artisan migrate
```

### Step 2: Ensure Storage Link
```bash
php artisan storage:link
```

### Step 3: Login as System Admin
Ensure you have a user with role `system_admin` in the database.

### Step 4: Test the Feature
1. Navigate to `/site-settings`
2. Upload a logo and change the site name
3. Check the sidebar logo/name updates
4. Logout and check the login page logo/name updates
5. Test the API endpoint: `GET /api/site-settings`

---

## Troubleshooting

### Issue: Logo not displaying
**Solution**: Ensure storage link is created
```bash
php artisan storage:link
```

### Issue: Access denied error
**Solution**: Verify user has `system_admin` role in database
```sql
UPDATE users SET role = 'system_admin' WHERE id = YOUR_USER_ID;
```

### Issue: Changes not reflecting
**Solution**: Clear application cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Issue: File upload error
**Check**:
- File size is under 2MB
- File format is supported (JPEG, PNG, GIF, SVG, WEBP)
- `storage/app/public` directory exists and is writable

---

## Default Values

- **Site Name**: "Laravel Starter Kit"
- **Site Logo**: `null` (uses default app icon)

---

## Additional Notes

### Performance
- Settings are cached for 1 hour to minimize database queries
- Logo images should be optimized before upload for best performance
- Recommended logo size: 128x128px to 512x512px

### Best Practices
1. Use transparent PNG for logos with complex shapes
2. Use SVG for scalable, crisp logos
3. Keep site name concise (under 30 characters for best display)
4. Test logo appearance in both light and dark modes

---

## Support

If you encounter any issues or need modifications, please refer to the following files:
- Backend Logic: `app/Http/Controllers/SiteSettingsController.php`
- Frontend Page: `resources/js/pages/SiteSettings/Index.vue`
- API Endpoint: `routes/api.php`
- Sidebar Integration: `resources/js/components/AppSidebar.vue`

---

**Implementation Complete** ✅

All features have been successfully implemented and integrated into your application. The Site Settings configuration is now fully functional and ready for use by System Administrators.
