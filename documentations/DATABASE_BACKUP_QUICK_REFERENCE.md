# Database Backup Quick Reference

## Quick Commands

### Manual Backup
```bash
php artisan backup:database
```

### Manual Backup with Custom Retention
```bash
php artisan backup:database --keep-days=60
```

### Clean Old Backups
```bash
php artisan backup:cleanup --days=30
```

### List Scheduled Tasks
```bash
php artisan schedule:list
```

### Run Scheduler (executes due tasks)
```bash
php artisan schedule:run
```

## Setup Cron (Production - Linux)

**One-time setup:**
```bash
crontab -e
```

**Add this line:**
```
* * * * * cd /var/www/mhrhci-backend && sudo docker compose exec app php artisan schedule:run >> /dev/null 2>&1
```

## Setup Task Scheduler (Windows)

### Quick PowerShell Script

Create `scheduler.ps1`:
```powershell
while($true) {
    cd C:\laragon\www\mhrhci-backend
    php artisan schedule:run
    Start-Sleep -Seconds 60
}
```

Run:
```powershell
powershell -File scheduler.ps1
```

## Current Schedule

| Task | Frequency | Time | Command |
|------|-----------|------|---------|
| Database Backup | Daily | 2:00 AM | `backup:database --keep-days=30` |
| Cleanup Old Backups | Weekly (Sunday) | 3:00 AM | `backup:cleanup --days=30` |

## Backup Location

```
storage/app/backups/database/
```

## Common Schedule Patterns

Edit `routes/console.php`:

```php
// Every hour
->hourly()

// Every 6 hours
->everySixHours()

// Every day at 1:30 AM
->dailyAt('01:30')

// Twice daily (2 AM and 2 PM)
->twiceDaily(2, 14)

// Every weekday at 2 AM
->weekdays()->dailyAt('02:00')

// Weekly on Monday at 2 AM
->weeklyOn(1, '02:00')

// Monthly on 1st at 2 AM
->monthlyOn(1, '02:00')

// Every 30 minutes
->everyThirtyMinutes()
```

## Troubleshooting

### Check if commands exist
```bash
php artisan list backup
```

### Test backup manually
```bash
php artisan backup:database --keep-days=30
```

### View logs
```bash
tail -f storage/logs/laravel.log
```

### Check permissions (Linux)
```bash
chmod -R 775 storage/app/backups
chown -R www-data:www-data storage/app/backups
```

### Verify cron is running (Linux)
```bash
crontab -l
grep CRON /var/log/syslog
```

## Testing

### Test scheduler without waiting
```bash
# Modify schedule in routes/console.php to run every minute
Schedule::command('backup:database')->everyMinute();

# Run scheduler
php artisan schedule:run

# Check if backup was created
ls -la storage/app/backups/database/
```

### View what's scheduled
```bash
php artisan schedule:list
```

## Restore a Backup

1. Login to the application as Admin/System Admin
2. Navigate to Database Backup page
3. Select backup file
4. Click "Restore" button

Or use the web interface at the backup management route.

## File Naming Convention

```
mhrhci_backup_YYMMDD_HHmmss.sql
```

Example: `mhrhci_backup_251008_020000.sql`
- `25` = Year 2025
- `10` = October
- `08` = Day 8
- `020000` = 02:00:00 AM

## Notes

- Backups are automatically cleaned after 30 days (configurable)
- All operations are logged
- Only Admin and System Admin users can manage backups
- Scheduler must be running for automated backups

---

For detailed documentation, see [DATABASE_BACKUP_SCHEDULER.md](./DATABASE_BACKUP_SCHEDULER.md)
