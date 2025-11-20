# Quick Setup Guide - Database Backup System

## 🚀 Quick Start (5 Minutes)

### Step 1: Verify Installation

All necessary files have been created. Verify these files exist:

```
✓ app/Http/Controllers/DatabaseBackupController.php
✓ resources/js/pages/Database/Backup.vue
✓ resources/js/pages/Database/Toast.vue
✓ routes/web.php (updated)
✓ resources/js/components/AppSidebar.vue (updated)
✓ .gitignore (updated)
```

### Step 2: Install Dependencies (if not already done)

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 3: Build Frontend Assets

```bash
# Development build
npm run dev

# OR Production build
npm run build
```

### Step 4: Create Storage Directory

The backup directory will be auto-created, but you can create it manually:

```bash
# Windows (PowerShell)
New-Item -ItemType Directory -Force -Path "storage\app\backups\database"

# Linux/Mac
mkdir -p storage/app/backups/database
chmod -R 755 storage/app/backups
```

### Step 5: Verify Database Configuration

Check your `.env` file:

```env
# For SQLite (Default - Works out of the box)
DB_CONNECTION=sqlite
DB_DATABASE=C:\laragon\www\mhrhci-backend\database\database.sqlite

# For MySQL (Laragon/Local)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mhrhci_db
DB_USERNAME=root
DB_PASSWORD=

# For PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mhrhci_db
DB_USERNAME=postgres
DB_PASSWORD=yourpassword
```

### Step 6: Access the Backup System

1. Start your server:
   ```bash
   php artisan serve
   ```

2. Log in as an **Admin** or **System Admin** user

3. Navigate to: **Settings → Database Backup**

4. Create your first backup!

## 📱 Mobile Testing

Test on mobile by accessing your local server:

```bash
# Find your local IP
ipconfig  # Windows
ifconfig  # Linux/Mac

# Access from mobile on same network
http://YOUR_IP:8000/database-backup
```
## ✅ Verification Checklist

- [ ] Frontend builds without errors (`npm run dev`)
- [ ] Can access `/database-backup` route (as admin)
- [ ] Can create a backup
- [ ] Backup file appears in `storage/app/backups/database/`
- [ ] Can download the backup file
- [ ] Can restore from backup
- [ ] Can delete a backup
- [ ] Can upload and restore external .sql file
- [ ] Page is responsive on mobile (< 640px width)

## 🔧 Troubleshooting

### Error: "Class DatabaseBackupController not found"

```bash
composer dump-autoload
php artisan config:clear
php artisan route:clear
```

### Error: Inertia page not found

```bash
npm run build
php artisan cache:clear
```

### Error: Permission denied (storage)

```bash
# Windows (Run as Administrator)
icacls storage /grant Users:F /T

# Linux/Mac
chmod -R 755 storage
chown -R www-data:www-data storage
```

### Database backup fails

1. Check database connection: `php artisan tinker` then `DB::connection()->getPdo()`
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database driver is supported (sqlite, mysql, pgsql)

## 🎨 Customization

### Change Backup Storage Location

Edit `DatabaseBackupController.php`:

```php
private function getBackupStoragePath(): string
{
    // Change this path
    return storage_path('app/backups/database');
}
```

### Adjust Upload File Size Limit

Edit `php.ini`:

```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

Then restart your web server.

### Add Automated Cleanup

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Delete backups older than 30 days
    $schedule->call(function () {
        $backupPath = storage_path('app/backups/database');
        if (!is_dir($backupPath)) return;
        
        $files = glob($backupPath . '/*.sql');
        $threshold = time() - (30 * 24 * 60 * 60); // 30 days
        
        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
            }
        }
    })->daily()->at('02:00');
}
```

## 📊 Production Deployment

### Pre-Production Checklist

1. **Test in staging first**
   - Create backups
   - Restore backups
   - Verify data integrity

2. **Set up automated backups**
   - Schedule daily/weekly backups
   - Consider offsite backup storage

3. **Configure proper permissions**
   ```bash
   chmod -R 755 storage/app/backups
   chown -R www-data:www-data storage/app/backups
   ```

4. **Monitor backup size**
   - Large databases may need `mysqldump` optimizations
   - Consider compression for backups

5. **Security hardening**
   - Ensure backup routes are protected
   - Monitor backup access logs
   - Encrypt sensitive backups

### Production Environment Variables

```env
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host.com
DB_PORT=3306
DB_DATABASE=production_db
DB_USERNAME=prod_user
DB_PASSWORD=secure_password
```

### Cloud Storage Integration (Optional)

For critical production systems, consider storing backups in cloud storage:

```php
// Example: Upload to AWS S3 after backup
$backupFile = $this->createBackup();
Storage::disk('s3')->put('backups/' . basename($backupFile), file_get_contents($backupFile));
```

## 🔐 Security Notes

- Backup files contain **ALL your database data**
- Keep backups secure and encrypted
- Don't commit backups to version control (already in .gitignore)
- Regularly test restore procedures
- Monitor who accesses backup features

## 📞 Support

For detailed information, see `DATABASE_BACKUP_GUIDE.md`

For issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for Vue errors
3. Verify database connection works
4. Test with a fresh backup

## 🎉 You're All Set!

The database backup system is now fully functional and production-ready!

**Next Steps:**
1. Create a test backup
2. Test restore functionality in a staging environment
3. Set up automated backup schedules
4. Document your backup/restore procedures

---

**Need Help?** Refer to `DATABASE_BACKUP_GUIDE.md` for comprehensive documentation.
