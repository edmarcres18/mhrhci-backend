# Testing the Database Backup System

## 🧪 Quick Test Guide

### Prerequisites
- Server running (`php artisan serve`)
- Logged in as Admin or System Admin
- Database has some test data

---

## Test 1: Access the Backup Page

**Desktop:**
1. Navigate to: `http://localhost:8000/database-backup`
2. ✅ Page loads without errors
3. ✅ See database information panel (driver, database name, table count)
4. ✅ See "Create Backup" and "Upload & Restore" buttons
5. ✅ See backups list (empty or with existing backups)

**Mobile (Chrome DevTools):**
1. Open DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select "iPhone SE" or "iPhone 12"
4. Navigate to backup page
5. ✅ Layout switches to card view
6. ✅ All buttons are touch-friendly
7. ✅ No horizontal scrolling

---

## Test 2: Create a Backup

**Steps:**
1. Click "Create Backup" button
2. Button shows "Creating Backup..."
3. Wait 1-5 seconds

**Expected Results:**
✅ Success toast appears: "Database backup created successfully."  
✅ New backup appears in the list  
✅ Filename format: `mhrhci_backup_25_10_02_HHMMSS.sql`  
✅ File size is displayed  
✅ Created timestamp is shown  

**Verify on Disk:**
```bash
# Windows
dir storage\app\backups\database

# Linux/Mac
ls -lh storage/app/backups/database/
```

---

## Test 3: Download a Backup

**Steps:**
1. Find a backup in the list
2. Click "Download" button

**Expected Results:**
✅ Browser downloads the .sql file  
✅ File opens in text editor showing SQL content  
✅ File size matches what's shown in UI  
✅ SQL structure looks correct  

**Verify SQL Content:**
Open the downloaded file and check:
- Contains `CREATE TABLE` statements
- Contains `INSERT INTO` statements (if data exists)
- No obvious corruption or errors

---

## Test 4: Restore from Backup

⚠️ **WARNING**: This will replace your current database!

**Preparation:**
1. Create a fresh backup first (safety backup)
2. Make a small change to the database (add/delete a record)
3. Note the change

**Steps:**
1. Click "Restore" on an older backup
2. Read the warning dialog
3. Click "Restore" to confirm
4. Wait for completion

**Expected Results:**
✅ Warning dialog appears with red warning text  
✅ Shows backup filename  
✅ "Restoring..." shown during process  
✅ Success toast: "Database restored successfully from backup."  
✅ Your test change is reverted (database is back to backup state)  

---

## Test 5: Delete a Backup

**Steps:**
1. Click "Delete" on a backup
2. Confirm in the dialog

**Expected Results:**
✅ Confirmation dialog appears  
✅ Shows backup filename  
✅ Success toast: "Backup deleted successfully."  
✅ Backup removed from list  
✅ File deleted from disk  

**Verify:**
```bash
# Check file is gone
dir storage\app\backups\database  # Windows
ls storage/app/backups/database/  # Linux/Mac
```

---

## Test 6: Upload & Restore

**Preparation:**
1. Download an existing backup (from Test 3)
2. Or use any valid .sql file

**Steps:**
1. Click "Upload & Restore" button
2. Read the warning
3. Click "Select SQL File"
4. Choose the .sql file
5. Click "Upload & Restore"
6. Wait for upload and restore

**Expected Results:**
✅ Warning dialog appears  
✅ File input shows selected file name and size  
✅ "Uploading..." shown during process  
✅ Success toast: "Database restored successfully from uploaded file."  
✅ Database matches the uploaded backup  

**Error Cases to Test:**
- Upload a .txt file → Should reject
- Upload a file > 100MB → Should reject
- Upload corrupt SQL → Should show error

---

## Test 7: Mobile Responsiveness

**Device Sizes to Test:**

### iPhone SE (375px)
1. Open page in DevTools mobile view
2. ✅ Database info cards stack vertically
3. ✅ Buttons full width
4. ✅ Backup list shows cards (not table)
5. ✅ All text readable
6. ✅ Touch targets ≥ 44px

### iPad (768px)
1. Switch to iPad view
2. ✅ Table layout appears
3. ✅ Action buttons visible with icons
4. ✅ No horizontal scroll

### Desktop (1920px)
1. Full screen browser
2. ✅ Full table with all columns
3. ✅ Action buttons show full labels
4. ✅ Maximum width constraint (max-w-7xl)

---

## Test 8: Different Database Drivers

### SQLite (Default)
```env
DB_CONNECTION=sqlite
```
✅ Backup creates .sql file  
✅ Restore works  
✅ No external tools needed  

### MySQL (If available)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test_db
DB_USERNAME=root
DB_PASSWORD=
```
✅ Backup works (with mysqldump or PHP fallback)  
✅ Restore works  
✅ Large databases handled  

### PostgreSQL (If available)
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=test_db
DB_USERNAME=postgres
DB_PASSWORD=password
```
✅ Backup works (requires pg_dump)  
✅ Restore works  

---

## Test 9: Error Handling

### Test Invalid Access
1. Log out or use a non-admin account
2. Try to access `/database-backup`
3. ✅ Should get 403 Forbidden error

### Test File Not Found
1. Try to download a non-existent backup (manually change URL)
2. ✅ Should get 404 error

### Test Invalid Filename
1. Try directory traversal: `/database-backup/download/../../.env`
2. ✅ Should get 403 error (filename validation)

### Test Large File Upload
1. Try uploading a file > 100MB
2. ✅ Should show error about file size

---

## Test 10: Performance

### Small Database (< 1MB)
- Backup time: < 1 second
- Restore time: < 1 second
- Download: Instant

### Medium Database (1-10MB)
- Backup time: 1-5 seconds
- Restore time: 1-5 seconds
- Download: < 1 second

### Large Database (> 10MB)
- Backup time: 5-30 seconds
- Restore time: 5-30 seconds
- Download: 1-3 seconds

---

## Test 11: Concurrent Operations

1. Open two browser tabs
2. Create backup in Tab 1
3. ✅ Refresh Tab 2, new backup appears
4. Delete backup in Tab 2
5. ✅ Refresh Tab 1, backup is gone

---

## Test 12: Toast Notifications

All operations should show appropriate toasts:

✅ **Success (Green):**
- Backup created
- Backup deleted
- Database restored
- Upload successful

✅ **Error (Red):**
- Backup creation failed
- Delete failed
- Restore failed
- Upload failed
- Invalid file type

✅ **Toast Behavior:**
- Auto-dismisses after 3.5 seconds
- Can be manually closed with X button
- Transitions smoothly in/out
- Positioned top-right on desktop
- Positioned top-center on mobile

---

## Test 13: Database Info Panel

Verify the info panel shows correct data:

✅ **Driver**: sqlite, mysql, or pgsql  
✅ **Database**: Correct database name  
✅ **Tables**: Correct count of tables  
✅ **Size**: File size (SQLite) or N/A (MySQL/PostgreSQL)  

All cards should:
- Display properly on mobile (2x2 grid)
- Display properly on desktop (4 columns)
- Have proper icons
- Show "N/A" for unavailable data

---

## Automated Test Script

Create this file for quick testing: `tests/Feature/DatabaseBackupTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_backup_page()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $response = $this->actingAs($admin)->get('/database-backup');
        
        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_backup_page()
    {
        $user = User::factory()->create(['role' => UserRole::STAFF]);
        
        $response = $this->actingAs($user)->get('/database-backup');
        
        $response->assertStatus(403);
    }

    public function test_admin_can_create_backup()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $response = $this->actingAs($admin)->post('/database-backup/create');
        
        $response->assertRedirect('/database-backup');
        $response->assertSessionHas('success');
    }
}
```

Run tests:
```bash
php artisan test --filter DatabaseBackupTest
```

---

## ✅ Final Verification Checklist

After all tests, verify:

- [ ] All features work on desktop
- [ ] All features work on mobile
- [ ] No console errors in browser
- [ ] No PHP errors in Laravel logs
- [ ] Backup files created in correct location
- [ ] Backup files excluded from git
- [ ] Downloaded backups are valid SQL
- [ ] Restore actually restores data
- [ ] Upload & restore works with external files
- [ ] Toasts appear for all actions
- [ ] Dialogs show proper warnings
- [ ] Loading states show during operations
- [ ] Admin-only access enforced
- [ ] File validation works
- [ ] Database info panel accurate

---

## 🐛 Common Issues & Solutions

### Issue: "Class not found" error
```bash
composer dump-autoload
php artisan config:clear
php artisan route:clear
```

### Issue: Backup page shows 404
```bash
npm run build
php artisan optimize
```

### Issue: Permission denied
```bash
# Windows
icacls storage /grant Users:F /T

# Linux
chmod -R 755 storage
```

### Issue: Backup file is empty
- Check database has tables and data
- Check Laravel logs for errors
- Verify database connection works

---

## 📊 Success Criteria

✅ **All core features work**  
✅ **Mobile responsive on all sizes**  
✅ **No errors in console or logs**  
✅ **Security measures in place**  
✅ **Documentation complete**  
✅ **Production ready**  

---

## 🎉 Ready for Production!

Once all tests pass, the system is ready for production deployment.

See `BACKUP_SETUP.md` for deployment instructions.
