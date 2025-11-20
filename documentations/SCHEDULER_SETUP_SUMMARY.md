# Database Backup Scheduler - Setup Summary

## ✅ What Has Been Installed

### 1. Console Commands Created

**File**: `app/Console/Commands/DatabaseBackup.php`
- Command: `backup:database`
- Purpose: Creates database backups automatically
- Options: `--keep-days=30` (configurable retention period)

**File**: `app/Console/Commands/DatabaseCleanup.php`
- Command: `backup:cleanup`
- Purpose: Removes old backup files
- Options: `--days=30` (delete backups older than specified days)

### 2. Scheduler Configuration

**File**: `routes/console.php`

**Scheduled Tasks:**

| Task | Schedule | Time | Purpose |
|------|----------|------|---------|
| Database Backup | Daily | 2:00 AM | Creates daily backups, keeps 30 days |
| Backup Cleanup | Weekly | Sunday 3:00 AM | Removes old backups |

### 3. Documentation Created

- **DATABASE_BACKUP_SCHEDULER.md** - Complete documentation
- **DATABASE_BACKUP_QUICK_REFERENCE.md** - Quick command reference
- **SCHEDULER_SETUP_SUMMARY.md** - This file

## 🚀 Next Steps to Enable Automated Backups

### Option 1: Production Server (Linux) - **RECOMMENDED**

1. **Login to your server via SSH**

2. **Edit crontab:**
   ```bash
   crontab -e
   ```

3. **Add this line** (replace path with your actual project path):
   ```bash
   * * * * * cd /var/www/mhrhci-backend && php artisan schedule:run >> /dev/null 2>&1
   ```

4. **Save and exit** (Ctrl+X, Y, Enter for nano)

5. **Verify it's added:**
   ```bash
   crontab -l
   ```

**That's it!** Backups will now run automatically.

### Option 2: Development (Windows with Laragon)

**Quick Start:**

1. **Open PowerShell** as Administrator

2. **Navigate to project:**
   ```powershell
   cd C:\laragon\www\mhrhci-backend
   ```

3. **Run scheduler in background:**
   ```powershell
   while($true) { php artisan schedule:run; Start-Sleep -Seconds 60 }
   ```

Or use Task Scheduler (see detailed docs).

### Option 3: Docker

Add to your `docker-compose.yml`:

```yaml
scheduler:
  image: your-app-image
  command: sh -c "while true; do php artisan schedule:run; sleep 60; done"
  volumes:
    - ./:/var/www/html
```

## 📋 Testing the Setup

### 1. Test Commands Manually

```bash
# Create a backup
php artisan backup:database

# Check if backup was created
ls storage/app/backups/database/

# List scheduled tasks
php artisan schedule:list
```

### 2. Test Automated Schedule

**For testing, modify** `routes/console.php` **temporarily:**

```php
// Change from daily to every minute for testing
Schedule::command('backup:database --keep-days=30')
    ->everyMinute()  // Changed from ->dailyAt('02:00')
    ->timezone(config('app.timezone'));
```

**Run scheduler:**
```bash
php artisan schedule:run
```

**Wait 1 minute and run again:**
```bash
php artisan schedule:run
```

**Check backups folder:**
```bash
ls storage/app/backups/database/
```

**Restore the original schedule after testing!**

## 🔍 Verify Everything is Working

### Check Commands Exist
```bash
php artisan list backup
```

**Expected output:**
```
backup:cleanup   Clean up old database backup files
backup:database  Create a database backup and optionally clean old backups
```

### Check Scheduled Tasks
```bash
php artisan schedule:list
```

**Expected output:**
```
0 2 * * *  php artisan backup:database --keep-days=30
0 3 * * 0  php artisan backup:cleanup --days=30
```

### Check Backup Storage
```bash
ls -la storage/app/backups/database/
```

### Monitor Logs
```bash
tail -f storage/logs/laravel.log
```

## 📁 File Locations

### Backups Stored In:
```
storage/app/backups/database/
```

### File Naming Pattern:
```
mhrhci_backup_251008_020000.sql
               │ │ │ │
               │ │ │ └─ Time (02:00:00)
               │ │ └─── Day (08)
               │ └───── Month (10 = October)
               └─────── Year (25 = 2025)
```

### Logs Location:
```
storage/logs/laravel.log
```

## 🔧 Customization

### Change Backup Schedule

Edit `routes/console.php`:

```php
// Examples:
->everyHour()           // Every hour
->everySixHours()       // Every 6 hours
->dailyAt('03:00')      // Daily at 3 AM
->twiceDaily(1, 13)     // 1 AM and 1 PM
->weeklyOn(1, '02:00')  // Every Monday at 2 AM
->monthlyOn(1, '02:00') // 1st of month at 2 AM
```

### Change Retention Period

```php
// Keep backups for 60 days
Schedule::command('backup:database --keep-days=60')
```

### Add Email Notifications (requires mail setup)

```php
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->emailOutputOnFailure('admin@example.com');
```

## 🛡️ Security Notes

1. ✅ Backup directory should be in `.gitignore` (already configured at line 9)
2. ✅ Only Admin/System Admin can access backup management
3. ✅ Backups contain sensitive data - protect them
4. ⚠️ Consider encrypting backups for production
5. ⚠️ Consider offsite backup storage (AWS S3, etc.)

## 📊 Monitoring

### Success Indicators:
- New backup files appear in `storage/app/backups/database/`
- Log entries show "Database backup completed successfully"
- Old backups are automatically removed

### Failure Indicators:
- No new backup files
- Error messages in `storage/logs/laravel.log`
- Disk space warnings

## 🆘 Troubleshooting

### Scheduler Not Running

**Check if cron is active (Linux):**
```bash
systemctl status cron
```

**View cron logs:**
```bash
grep CRON /var/log/syslog
```

### Permission Issues

**Fix storage permissions (Linux):**
```bash
sudo chmod -R 775 storage/app/backups
sudo chown -R www-data:www-data storage/app/backups
```

**Create directory manually if needed:**
```bash
mkdir -p storage/app/backups/database
chmod 775 storage/app/backups/database
```

### Database Connection Issues

**Test database connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Command Not Found

**Clear cache:**
```bash
php artisan cache:clear
php artisan config:clear
composer dump-autoload
```

## 📚 Additional Resources

- **Full Documentation**: `documentations/DATABASE_BACKUP_SCHEDULER.md`
- **Quick Reference**: `documentations/DATABASE_BACKUP_QUICK_REFERENCE.md`
- **Laravel Scheduling**: https://laravel.com/docs/11.x/scheduling
- **Backup Feature**: `documentations/BACKUP_FEATURE_SUMMARY.md`

## ✅ Checklist

Before going to production, ensure:

- [ ] Cron job is configured and running
- [ ] Test backup command runs successfully
- [ ] Test restore functionality works
- [ ] Backup retention period is appropriate
- [ ] Disk space is sufficient for backups
- [ ] Logs are being written correctly
- [ ] Backup directory is in `.gitignore`
- [ ] Permissions are correct on backup directory
- [ ] Email notifications configured (optional)
- [ ] Offsite backup strategy in place (recommended)

## 🎉 You're All Set!

The automated database backup system is ready to use. Just enable the cron job on your server and backups will run automatically according to the schedule.

---

**Created**: 2025-10-08  
**Laravel Version**: 11.x  
**Status**: Ready for Production
