# Database Backup & Restore Guide

Complete documentation for the database backup and restore functionality in the MHRHCI Backend system.

## Overview

This system provides a comprehensive, production-ready database backup and restore solution that works in both local and production environments. It supports multiple database drivers and provides a mobile-responsive Vue.js interface.

## Features

✅ **Multi-Database Support**: SQLite, MySQL, and PostgreSQL  
✅ **Auto-Detection**: Automatically detects and uses the appropriate backup method based on your database configuration  
✅ **Download Backups**: Download backup files as .sql files  
✅ **Restore from Backup**: Restore your database from any previously created backup  
✅ **Upload & Restore**: Upload external .sql files and restore from them  
✅ **Mobile-Responsive**: Fully responsive UI optimized for mobile-first design  
✅ **Security**: Admin-only access with proper authorization checks  
✅ **Production-Ready**: Works in both local and production environments  
✅ **Automatic Fallback**: Uses PHP-based backup methods if CLI tools are unavailable  

## Database Driver Support

### SQLite
- **Backup Method**: Pure PHP implementation using SQL dump
- **Restore Method**: Direct SQL execution via Laravel DB
- **Requirements**: None (works out of the box)
- **Location**: Database file specified in `.env` (usually `database/database.sqlite`)

### MySQL
- **Backup Method**: 
  - Primary: `mysqldump` CLI tool
  - Fallback: PHP-based dump using Laravel DB queries
- **Restore Method**: Direct SQL execution via Laravel DB
- **Requirements**: 
  - `mysqldump` (recommended but not required)
  - Database credentials in `.env`

### PostgreSQL
- **Backup Method**: `pg_dump` CLI tool
- **Restore Method**: Direct SQL execution via Laravel DB
- **Requirements**: 
  - `pg_dump` must be installed and accessible
  - Database credentials in `.env`

## File Structure

```
app/Http/Controllers/
└── DatabaseBackupController.php      # Main backup controller

resources/js/
├── pages/Database/
│   ├── Backup.vue                    # Main backup management page
│   └── Toast.vue                     # Toast notification component
└── components/
    └── AppSidebar.vue                # Navigation (includes backup link)

routes/
└── web.php                           # Backup routes

storage/app/backups/database/         # Backup storage directory (auto-created)
```

## Routes

All routes require authentication and admin privileges:

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/database-backup` | Display backup management page |
| POST | `/database-backup/create` | Create a new backup |
| GET | `/database-backup/download/{filename}` | Download a backup file |
| DELETE | `/database-backup/{filename}` | Delete a backup file |
| POST | `/database-backup/restore` | Restore from an existing backup |
| POST | `/database-backup/upload-restore` | Upload and restore from a .sql file |

## Usage

### Accessing the Backup Manager

1. Log in as an **Admin** or **System Admin**
2. Navigate to **Settings → Database Backup** in the sidebar
3. The page displays:
   - Database information (driver, name, size, table count)
   - List of available backups
   - Action buttons for backup operations

### Creating a Backup

**Desktop:**
1. Click the **"Create Backup"** button
2. Wait for the backup to complete (usually 1-5 seconds)
3. Success notification will appear
4. New backup will be listed in the backups table

**Mobile:**
1. Tap **"Create Backup"** button
2. Wait for completion
3. New backup appears in the list

Backup files are named: `mhrhci_backup_yy_mm_dd_HHmmss.sql`

Example: `mhrhci_backup_25_10_02_125630.sql`

### Downloading a Backup

**Desktop:**
1. Find the backup in the table
2. Click **"Download"** button in the Actions column
3. File downloads to your browser's download folder

**Mobile:**
1. Find the backup in the list
2. Tap **"Download"** button
3. File downloads to your device

### Restoring from a Backup

⚠️ **WARNING**: Restoring will **replace all current data** with the backup data!

**Desktop:**
1. Click **"Restore"** button for the desired backup
2. Read the warning in the confirmation dialog
3. Click **"Restore"** to confirm
4. Wait for restoration to complete
5. Success notification appears

**Mobile:**
1. Tap **"Restore"** button for the desired backup
2. Review the warning dialog
3. Tap **"Restore"** to confirm
4. Wait for completion

**Best Practice**: Always create a fresh backup before restoring an old one!

### Uploading & Restoring External Backups

**Desktop:**
1. Click **"Upload & Restore"** button
2. Read the warning
3. Click **"Select SQL File"**
4. Choose your `.sql` file
5. Click **"Upload & Restore"**
6. Wait for completion

**Mobile:**
1. Tap **"Upload & Restore"**
2. Review warning
3. Tap file input to select file
4. Choose your `.sql` file
5. Tap **"Upload & Restore"**

**Supported File Types**: `.sql` files only  
**Maximum File Size**: 100 MB

### Deleting a Backup

**Desktop:**
1. Click **"Delete"** button for the backup
2. Confirm deletion in the dialog
3. Backup is permanently removed

**Mobile:**
1. Tap **"Delete"** button
2. Confirm in the dialog
3. Backup is removed

## Local Development Setup

### SQLite (Default)

No additional setup required. Backups work out of the box.

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### MySQL

**Option 1: With mysqldump (Recommended)**

Ensure `mysqldump` is in your PATH:

```bash
# Windows (Laragon/XAMPP/WAMP)
# Usually already available in PATH

# Linux/Mac
which mysqldump
```

**Option 2: Without mysqldump**

The system will automatically use the PHP fallback method.

**.env Configuration:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=your_password
```

### PostgreSQL

Install PostgreSQL and ensure `pg_dump` is available:

```bash
# Check if pg_dump is available
pg_dump --version
```

**.env Configuration:**

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

## Production Deployment

### Pre-Deployment Checklist

- [ ] Verify database connection works
- [ ] Test backup creation in staging
- [ ] Test restore functionality in staging
- [ ] Ensure backup storage directory is writable
- [ ] Configure proper file permissions (755 for directories)
- [ ] Set up automated cleanup for old backups (optional)

### Server Requirements

**For All Databases:**
- PHP 8.2 or higher
- Laravel 12.x
- Write permissions on `storage/app/backups/database/`

**For MySQL (Production):**
- `mysqldump` utility (recommended)
- Or PHP fallback method will be used

**For PostgreSQL:**
- `pg_dump` utility (required)

### File Permissions

```bash
# Set correct permissions on production
chmod -R 755 storage/app/backups
chown -R www-data:www-data storage/app/backups  # Linux
```

### Environment Configuration

Ensure your production `.env` has correct database credentials:

```env
APP_ENV=production
DB_CONNECTION=mysql  # or pgsql, sqlite
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password
```

### Storage Considerations

**Backup File Size:**
- SQLite: Similar to database file size
- MySQL/PostgreSQL: Varies based on data

**Storage Location:**
- Local: `storage/app/backups/database/`
- Production: Same directory structure
- Consider cloud storage integration for critical systems

**Cleanup Strategy:**

You may want to add a scheduled task to delete old backups:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Delete backups older than 30 days
    $schedule->call(function () {
        $backupPath = storage_path('app/backups/database');
        $files = glob($backupPath . '/*.sql');
        $now = time();
        foreach ($files as $file) {
            if ($now - filemtime($file) >= 30 * 24 * 60 * 60) {
                unlink($file);
            }
        }
    })->daily();
}
```

## Security

### Access Control

- Only users with **Admin** or **System Admin** roles can access backup features
- Authorization checked on every route
- Filename validation prevents directory traversal attacks
- File type validation ensures only .sql files are processed

### Best Practices

1. **Regular Backups**: Create backups before major updates
2. **Test Restores**: Periodically test restore functionality in staging
3. **Secure Storage**: Keep backup files secure and encrypted if possible
4. **Access Logs**: Monitor who creates/restores backups
5. **Offsite Backups**: Consider copying critical backups to cloud storage

## Troubleshooting

### "Failed to create backup: Unsupported database driver"

**Solution**: Check your `DB_CONNECTION` in `.env`. Only `sqlite`, `mysql`, and `pgsql` are supported.

### "Failed to create PostgreSQL backup. Make sure pg_dump is installed"

**Solution**: Install PostgreSQL client tools or add `pg_dump` to your PATH.

```bash
# Ubuntu/Debian
sudo apt-get install postgresql-client

# Mac
brew install postgresql
```

### "Permission denied" when creating backup

**Solution**: Ensure the storage directory is writable:

```bash
chmod -R 755 storage/app/backups
```

### Backup file is empty or corrupt

**Solution**: 
1. Check database connection
2. Verify database has tables and data
3. Check PHP error logs for SQL errors
4. Try the backup operation again

### Restore fails with SQL errors

**Solution**:
1. Ensure backup file is compatible with your database version
2. Check for syntax differences if restoring across different database types
3. Verify backup file is not corrupted (open in text editor)

### Upload file size limit exceeded

**Solution**: Increase PHP upload limits in `php.ini`:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

## Mobile Responsiveness

The interface is fully optimized for mobile devices:

### Mobile (< 640px)
- Stacked card layout for backups
- Touch-friendly buttons with adequate spacing
- Simplified information display
- Full-screen dialogs

### Tablet (640px - 1024px)
- Table layout with responsive columns
- Condensed action buttons
- Optimized spacing

### Desktop (> 1024px)
- Full table layout
- All action buttons visible with labels
- Maximum information density

## Testing

### Manual Testing Checklist

**Basic Functionality:**
- [ ] Create a backup
- [ ] Download a backup
- [ ] Restore from a backup
- [ ] Upload and restore external backup
- [ ] Delete a backup

**Edge Cases:**
- [ ] Empty database backup
- [ ] Large database backup (> 10 MB)
- [ ] Rapid multiple backups
- [ ] Restore during active database operations

**Mobile Testing:**
- [ ] Create backup on mobile
- [ ] Download backup on mobile
- [ ] Upload file on mobile
- [ ] All dialogs display correctly

### Automated Testing

Consider adding feature tests:

```php
// tests/Feature/DatabaseBackupTest.php
public function test_admin_can_create_backup()
{
    $admin = User::factory()->create(['role' => UserRole::ADMIN]);
    
    $response = $this->actingAs($admin)
        ->post('/database-backup/create');
    
    $response->assertRedirect('/database-backup');
    $response->assertSessionHas('success');
}
```

## API Integration (Future Enhancement)

If you need API access for automated backups:

```php
// Example: Add API routes in routes/api.php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/api/backup/create', [DatabaseBackupController::class, 'apiBackup']);
    Route::get('/api/backup/list', [DatabaseBackupController::class, 'apiList']);
});
```

## Support

For issues or questions:
1. Check this documentation
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check browser console for Vue.js errors
4. Verify database connection works outside backup system

## License

This backup system is part of the MHRHCI Backend and follows the same license as the main project.

---

**Version**: 1.0.0  
**Last Updated**: 2025-10-02  
**Tested With**: Laravel 12.x, PHP 8.2+, SQLite/MySQL/PostgreSQL
