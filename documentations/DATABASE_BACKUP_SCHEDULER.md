# Database Backup Scheduler Documentation

## Overview

The MHRHCI Backend application includes an automated database backup system with scheduled tasks for creating backups and cleaning up old files.

## Features

- **Automated Backups**: Schedule regular database backups
- **Automatic Cleanup**: Remove old backups to save disk space
- **Multi-Database Support**: Works with MySQL, PostgreSQL, and SQLite
- **Flexible Scheduling**: Customize backup frequency
- **Logging**: Track backup success and failures

## Console Commands

### 1. Create Backup

```bash
php artisan backup:database
```

**Options:**
- `--keep-days=30` - Number of days to keep backups (default: 30)

**Example:**
```bash
# Create backup and keep last 60 days
php artisan backup:database --keep-days=60
```

### 2. Cleanup Old Backups

```bash
php artisan backup:cleanup
```

**Options:**
- `--days=30` - Delete backups older than this many days (default: 30)

**Example:**
```bash
# Remove backups older than 7 days
php artisan backup:cleanup --days=7
```

## Scheduled Tasks

The application is configured with the following automated schedules:

### Daily Backup
- **Schedule**: Every day at 2:00 AM
- **Command**: `backup:database --keep-days=30`
- **Purpose**: Creates daily database backups and removes backups older than 30 days

### Weekly Cleanup
- **Schedule**: Every Sunday at 3:00 AM
- **Command**: `backup:cleanup --days=30`
- **Purpose**: Additional cleanup to ensure old backups are removed

## Setup Instructions

### For Production (Linux/Unix)

1. **Configure Cron Job**

   Edit the crontab for your web server user:
   ```bash
   crontab -e
   ```

2. **Add Laravel Scheduler Entry**

   Add this single line to run Laravel's scheduler every minute:
   ```
   * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
   ```

   Replace `/path/to/your/project` with your actual project path, e.g.:
   ```
   * * * * * cd /var/www/mhrhci-backend && php artisan schedule:run >> /dev/null 2>&1
   ```

3. **Verify Cron is Active**
   ```bash
   crontab -l
   ```

### For Development (Windows with Laragon)

**Option 1: Task Scheduler (Recommended)**

1. Open **Task Scheduler** (Press Win + R, type `taskschd.msc`)

2. Click **Create Basic Task**
   - Name: Laravel Scheduler
   - Description: Run Laravel scheduled tasks

3. **Trigger**: Select "Daily"
   - Start: Choose tomorrow's date
   - Recur every: 1 day

4. **Action**: Select "Start a program"
   - Program/script: `C:\laragon\bin\php\php-8.x.x\php.exe` (adjust to your PHP version)
   - Add arguments: `artisan schedule:run`
   - Start in: `C:\laragon\www\mhrhci-backend`

5. **Settings**:
   - Check "Run task as soon as possible after a scheduled start is missed"
   - Set to repeat every 1 minute for a duration of 1 day

**Option 2: PowerShell Script**

Create a file `run-scheduler.ps1`:
```powershell
while($true) {
    cd C:\laragon\www\mhrhci-backend
    php artisan schedule:run
    Start-Sleep -Seconds 60
}
```

Run it in the background:
```powershell
powershell -File run-scheduler.ps1
```

**Option 3: Manual Testing**

For development, you can manually trigger scheduled tasks:
```bash
# Run all scheduled tasks that are due
php artisan schedule:run

# List all scheduled tasks
php artisan schedule:list

# Run a specific command
php artisan backup:database
```

### For Docker Environments

Add to your `docker-compose.yml`:

```yaml
services:
  scheduler:
    image: your-app-image
    command: sh -c "while true; do php artisan schedule:run; sleep 60; done"
    volumes:
      - ./:/var/www/html
    depends_on:
      - app
```

Or create a separate container with cron:

```dockerfile
# Add to your Dockerfile
RUN apt-get update && apt-get install -y cron

# Copy cron file
COPY docker/cron/laravel-scheduler /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler
RUN crontab /etc/cron.d/laravel-scheduler
```

Create `docker/cron/laravel-scheduler`:
```
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

## Customizing the Schedule

Edit `routes/console.php` to customize backup schedules:

### Common Schedule Examples

```php
// Every hour
Schedule::command('backup:database')->hourly();

// Every 6 hours
Schedule::command('backup:database')->everySixHours();

// Every day at specific time
Schedule::command('backup:database')->dailyAt('01:30');

// Twice daily
Schedule::command('backup:database')->twiceDaily(1, 13);

// Weekly on Monday at 2 AM
Schedule::command('backup:database')->weeklyOn(1, '02:00');

// Monthly on first day
Schedule::command('backup:database')->monthlyOn(1, '02:00');

// Every 30 minutes
Schedule::command('backup:database')->everyThirtyMinutes();

// Multiple times per day
Schedule::command('backup:database')
    ->twiceDaily(2, 14)  // 2 AM and 2 PM
    ->timezone('America/New_York');

// Only on weekdays
Schedule::command('backup:database')
    ->weekdays()
    ->dailyAt('02:00');

// Only on weekends
Schedule::command('backup:database')
    ->weekends()
    ->dailyAt('02:00');
```

### Advanced Conditions

```php
// Only in production
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->when(fn () => app()->environment('production'));

// Skip if another process is running
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// Send notification on failure
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->emailOutputOnFailure('admin@example.com');

// Run in background
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->runInBackground();

// Maintenance mode check
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->unless(fn () => app()->isDownForMaintenance());
```

## Backup Storage

Backups are stored in:
```
storage/app/backups/database/
```

### Backup File Naming

Files follow the pattern:
```
mhrhci_backup_YYMMDD_HHmmss.sql
```

Example:
```
mhrhci_backup_251008_020000.sql
```

### Storage Considerations

- Ensure sufficient disk space for backups
- Consider external storage for production (AWS S3, Google Cloud Storage)
- Monitor backup file sizes regularly
- Set appropriate retention periods based on your needs

### Adding .gitignore

The backup directory should be excluded from version control. Verify in `.gitignore`:
```
/storage/app/backups/
```

## Monitoring and Logs

### View Scheduled Tasks

```bash
# List all scheduled tasks with next run time
php artisan schedule:list
```

### Check Logs

Laravel logs scheduled task execution in `storage/logs/laravel.log`:

```bash
# View recent logs
tail -f storage/logs/laravel.log

# Search for backup logs
grep "Database backup" storage/logs/laravel.log
```

### Test Scheduled Tasks

```bash
# Run the scheduler (will only execute tasks that are due)
php artisan schedule:run

# Force run a specific command for testing
php artisan backup:database --keep-days=30
```

### Debugging

```bash
# Test the scheduler (shows what would run)
php artisan schedule:test

# Run scheduler with verbose output
php artisan schedule:work --verbose
```

## Best Practices

1. **Test First**: Always test backup and restore on development before production
2. **Monitor Disk Space**: Set up alerts for low disk space
3. **Verify Backups**: Periodically test restore functionality
4. **Keep Multiple Copies**: Consider offsite backup storage
5. **Document Restore Process**: Ensure team knows how to restore from backup
6. **Set Appropriate Retention**: Balance storage costs with compliance needs
7. **Use Maintenance Windows**: Schedule backups during low-traffic periods
8. **Monitor Success/Failure**: Set up notifications for failed backups

## Troubleshooting

### Scheduler Not Running

**Check cron:**
```bash
# View cron logs (Linux)
grep CRON /var/log/syslog

# Check if cron is active
systemctl status cron
```

**Check permissions:**
```bash
# Ensure storage directory is writable
chmod -R 775 storage/app/backups
chown -R www-data:www-data storage/app/backups
```

### Backup Command Fails

**Check database connection:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

**Check for mysqldump/pg_dump:**
```bash
# MySQL
which mysqldump

# PostgreSQL
which pg_dump
```

**Check storage permissions:**
```bash
ls -la storage/app/
```

### Large Database Issues

For large databases:

1. **Increase timeout** in `app/Console/Commands/DatabaseBackup.php`:
   ```php
   $process->setTimeout(900); // 15 minutes
   ```

2. **Use compression**:
   ```bash
   # Manually compress backups
   gzip storage/app/backups/database/*.sql
   ```

3. **Consider incremental backups** or **database-specific tools**

## Security Considerations

1. **Restrict Access**: Only admin users can access backup management
2. **Secure Storage**: Backups contain sensitive data - protect them
3. **Encrypt Backups**: Consider encrypting backup files
4. **Secure Transfer**: Use SFTP/SCP for transferring backups
5. **Access Logs**: Monitor who accesses/downloads backups
6. **Environment Variables**: Never commit database credentials

## Related Documentation

- [Database Backup Feature Summary](./BACKUP_FEATURE_SUMMARY.md)
- [Database Backup Setup](./BACKUP_SETUP.md)
- [Laravel Task Scheduling](https://laravel.com/docs/11.x/scheduling)
- [Laravel Artisan Console](https://laravel.com/docs/11.x/artisan)

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review this documentation
3. Contact system administrator
4. Check Laravel documentation

---

**Last Updated**: 2025-10-08
**Laravel Version**: 11.x
**PHP Version**: 8.2+
