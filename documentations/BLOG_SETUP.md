# Blog CRUD Setup

This document provides a complete overview of the Blog CRUD system that has been implemented.

## Files Created

### Backend (Laravel)

1. **Migration**: `database/migrations/2025_09_30_075531_create_blogs_table.php`
   - Fields: `id`, `title`, `content`, `images`, `timestamps`

2. **Model**: `app/Models/Blog.php`
   - Fillable: `title`, `content`, `images`
   - Casts: `images` as `array`

3. **Controller**: `app/Http/Controllers/BlogController.php`
   - Full CRUD operations (index, create, store, show, edit, update, destroy)
   - Image upload handling (max 5 images)
   - Search functionality
   - Pagination support

4. **Factory**: `database/factories/BlogFactory.php`
   - Generates fake blog data for testing

5. **Seeder**: `database/seeders/BlogSeeder.php`
   - Seeds 20 sample blogs

### Frontend (Vue 3 + TypeScript)

#### Navigation
**AppSidebar.vue** - Updated with Blogs navigation item
- Icon: `FileText` from lucide-vue-next
- Active state automatically detected using `urlIsActive` utility
- Works for all blog routes: `/blogs`, `/blogs/create`, `/blogs/{id}`, `/blogs/{id}/edit`

#### Pages
Located in `resources/js/pages/Blogs/`:

1. **Index.vue** - Blog listing page
   - Search functionality with debouncing
   - Per-page pagination controls
   - Responsive table/card layout
   - Delete confirmation modal
   - Toast notifications

2. **Create.vue** - Create new blog page
   - Form validation
   - Multiple image upload (max 5)
   - Image preview functionality

3. **Edit.vue** - Edit existing blog page
   - Pre-populated form data
   - Option to keep or replace existing images
   - Image preview for new uploads

4. **Show.vue** - View blog details page
   - Display blog content and images
   - Edit/Delete actions
   - Delete confirmation modal

5. **Toast.vue** - Reusable toast notification component
   - Success/Error/Info/Warning types
   - Auto-dismiss functionality

## Routes

All blog routes are protected by `auth` and `verified` middleware:

- `GET /blogs` - List all blogs (blogs.index)
- `GET /blogs/create` - Show create form (blogs.create)
- `POST /blogs` - Store new blog (blogs.store)
- `GET /blogs/{blog}` - Show blog details (blogs.show)
- `GET /blogs/{blog}/edit` - Show edit form (blogs.edit)
- `PUT/PATCH /blogs/{blog}` - Update blog (blogs.update)
- `DELETE /blogs/{blog}` - Delete blog (blogs.destroy)

## Features

### Backend Features
- ✅ Image storage in `storage/app/public/blogs`
- ✅ Automatic image deletion when blog is deleted
- ✅ Image replacement options on update
- ✅ Search functionality (title and content)
- ✅ Configurable pagination (10, 25, 50, 100 per page)
- ✅ Form validation
- ✅ Flash messages for success/error feedback

### Frontend Features
- ✅ Responsive design (mobile + desktop)
- ✅ Dark mode support
- ✅ Real-time search with debouncing
- ✅ Image preview before upload
- ✅ Drag-and-drop file upload support
- ✅ Delete confirmation dialogs
- ✅ Toast notifications for user feedback
- ✅ Loading states and form validation
- ✅ Same UX/UI as Products pages

## Database Schema

```sql
CREATE TABLE blogs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NULL,
    images JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Usage

### Access the Blog pages
1. Navigate to `/blogs` to view all blogs
2. Click "New Blog" to create a new blog entry
3. Use search and pagination controls to filter blogs
4. Click on any blog to view details or edit/delete

### Seeding Sample Data
```bash
php artisan db:seed --class=BlogSeeder
```

### Storage Link
If images are not displaying, ensure the storage link is created:
```bash
php artisan storage:link
```

## Validation Rules

### Create/Update
- **title**: required, string, max 255 characters
- **content**: nullable, string (text area)
- **images**: nullable, array, max 5 images
- **images.***: file, image (jpg, jpeg, png, webp, avif), max 5MB per image

## API Response Format

Blog data returned from the API includes:
```typescript
{
  id: number;
  title: string;
  content?: string | null;
  images?: string[] | null;
  created_at?: string | null;
}
```

## Production Ready Checklist

- ✅ Database migration created and run
- ✅ Model with proper fillables and casts
- ✅ Controller with full CRUD operations
- ✅ Form validation
- ✅ Image upload handling
- ✅ Image cleanup on delete
- ✅ Factory and seeder for testing
- ✅ Routes registered with authentication
- ✅ Vue components with TypeScript
- ✅ Responsive UI matching Products design
- ✅ Toast notifications
- ✅ Error handling
- ✅ Frontend compiled successfully

## Notes

- Images are stored in `storage/app/public/blogs/`
- Maximum 5 images per blog post
- Images are accessible via `/storage/blogs/{filename}`
- All routes require authentication
- Search works on both title and content fields
- Pagination preserves search and filter state
