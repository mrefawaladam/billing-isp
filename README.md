# Laravel Starter Template

Starter template lengkap untuk project Laravel dengan MatDash Bootstrap Admin theme, dilengkapi dengan berbagai fitur siap pakai.

## 📋 Daftar Isi

- [Fitur](#fitur)
- [Instalasi](#instalasi)
- [Struktur Folder](#struktur-folder)
- [API Starter](#api-starter)
- [CRUD Generator](#crud-generator)
- [Helpers](#helpers)
- [Services Architecture](#services-architecture)
- [Components](#components)
- [JavaScript Helpers](#javascript-helpers)
- [Authentication & Authorization](#authentication--authorization)
- [Usage Examples](#usage-examples)

## ✨ Fitur

### 1. **Layout & UI Components**
- ✅ Responsive layout dengan sidebar
- ✅ Header dengan notifications, language selector, dan profile dropdown
- ✅ Theme customizer (light/dark mode, color themes, layout options)
- ✅ Search modal
- ✅ Reusable Blade components
- ✅ Toast notifications dengan style modern
- ✅ Modal components
- ✅ DataTable components

### 2. **Authentication & User Management**
- ✅ Login/Register dengan throttle protection
- ✅ Forgot/Reset Password
- ✅ Session timeout middleware
- ✅ User CRUD dengan DataTables
- ✅ Roles & Permissions (Spatie Laravel Permission)
- ✅ Modal-based CRUD operations

### 3. **API Starter**
- ✅ API Base Response helper
- ✅ API Auth endpoints (`/api/auth/login`, `/api/auth/me`)
- ✅ Global exception handler untuk API
- ✅ Laravel Sanctum support
- ✅ Standardized error responses

### 4. **CRUD Generator**
- ✅ Command: `php artisan make:crud ModelName`
- ✅ Auto-generate: Model, Migration, Controller, Service, Views, Routes

### 5. **Helpers**
- ✅ `formatDate()` - Format tanggal Indonesia
- ✅ `formatRupiah()` - Format currency Rupiah
- ✅ `slugify()` - Convert string ke URL-friendly slug
- ✅ `randomCode()` - Generate random code
- ✅ `idEncrypt()` & `idDecrypt()` - Encrypt/decrypt ID untuk URL safety
- ✅ `isActiveNav()` - Check active navigation
- ✅ `asset_versioned()` - Asset dengan version untuk cache busting

### 6. **Services Architecture**
- ✅ `UserService` - Business logic untuk user management
- ✅ `AuthService` - Business logic untuk authentication
- ✅ `CRUDService` - Generic CRUD service
- ✅ `NotificationService` - Helper untuk notifications

### 7. **JavaScript Helpers**
- ✅ `Toast` - Toast notification helper
- ✅ `Modal` - Modal helper untuk AJAX loading
- ✅ `Form` - Form submission helper dengan validation

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd starter-aferkit
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 5. Publish Assets (jika diperlukan)
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 6. Run Development Server
```bash
php artisan serve
npm run dev
```

## 📁 Struktur Folder

```
app/
├── Console/
│   └── Commands/
│       ├── MakeCrudCommand.php      # CRUD Generator command
│       └── stubs/                    # Stub files untuk generator
├── Exceptions/
│   └── Handler.php                  # Global exception handler
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── AuthController.php   # API Auth controller
│   │   ├── Auth/                    # Auth controllers
│   │   └── UserController.php       # User CRUD controller
│   ├── Middleware/
│   │   └── SessionTimeout.php       # Session timeout middleware
│   └── Responses/
│       └── ApiResponse.php           # API response helper
├── Models/
│   └── User.php                     # User model dengan Sanctum & Spatie
├── Services/
│   ├── AuthService.php              # Auth business logic
│   ├── CRUDService.php              # Generic CRUD service
│   ├── NotificationService.php      # Notification helper
│   └── UserService.php              # User business logic
└── Support/
    └── helpers.php                  # Helper functions

resources/
└── views/
    ├── components/
    │   ├── layout/                  # Layout components
    │   │   ├── breadcrumb.blade.php
    │   │   ├── customizer.blade.php
    │   │   ├── header.blade.php
    │   │   ├── page-header.blade.php
    │   │   ├── scripts.blade.php
    │   │   └── sidebar.blade.php
    │   └── ui/                      # UI components
    │       ├── datatable.blade.php
    │       ├── form-input.blade.php
    │       ├── modal.blade.php
    │       ├── search-modal.blade.php
    │       └── toast-notification.blade.php
    ├── features/                    # Feature-based views
    │   └── users/
    │       ├── index.blade.php
    │       └── partials/
    │           ├── action-buttons.blade.php
    │           ├── form.blade.php
    │           └── show.blade.php
    └── layouts/
        └── app.blade.php            # Main layout

public/
└── assets/
    └── js/
        └── helpers/                 # JavaScript helpers
            ├── toast.js
            ├── modal.js
            └── form.js
```

## 🔌 API Starter

### Base Response Structure

Semua API response mengikuti struktur konsisten:

**Success Response:**
```json
{
    "success": true,
    "message": "Success message",
    "data": { ... }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error message",
    "errors": { ... }  // Optional, untuk validation errors
}
```

### API Endpoints

#### Authentication

**POST /api/auth/login**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**GET /api/auth/me** (Protected)
```
Headers: Authorization: Bearer {token}
```

**POST /api/auth/logout** (Protected)
```
Headers: Authorization: Bearer {token}
```

**POST /api/auth/refresh** (Protected)
```
Headers: Authorization: Bearer {token}
```

### Usage Example

```javascript
// Login
const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password'
    })
});

const data = await response.json();
const token = data.data.token;

// Use token
const meResponse = await fetch('/api/auth/me', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
```

### Global Exception Handler

Exception handler otomatis menangani:
- **422**: Validation errors
- **401**: Unauthorized
- **403**: Forbidden
- **404**: Not found
- **500**: Server error

## 🛠️ CRUD Generator

Generate CRUD lengkap dengan satu command:

```bash
php artisan make:crud Product
```

**Options:**
- `--api`: Generate API controller
- `--migration`: Generate migration file

**Yang di-generate:**
- ✅ Model (`app/Models/Product.php`)
- ✅ Migration (jika `--migration`)
- ✅ Service (`app/Services/ProductService.php`)
- ✅ Controller (`app/Http/Controllers/ProductController.php`)
- ✅ Views (`resources/views/features/products/`)
- ✅ Routes (ditambahkan ke `routes/web.php`)

**Usage:**
```bash
# Generate CRUD dengan migration
php artisan make:crud Product --migration

# Generate CRUD dengan API controller
php artisan make:crud Product --api

# Generate CRUD lengkap
php artisan make:crud Product --migration --api
```

## 🔧 Helpers

### formatDate()
Format tanggal ke format Indonesia.

```php
formatDate($date); // "10 Nov 2024"
formatDate($date, 'd M Y H:i'); // "10 Nov 2024 14:30"
```

### formatRupiah()
Format number ke currency Rupiah.

```php
formatRupiah(1000000); // "Rp 1.000.000"
formatRupiah(1000000, false); // "1.000.000"
```

### slugify()
Convert string ke URL-friendly slug.

```php
slugify('Hello World'); // "hello-world"
slugify('Product Name 123'); // "product-name-123"
```

### randomCode()
Generate random code.

```php
randomCode(8); // "A1B2C3D4"
randomCode(6, true); // "123456" (numeric only)
```

### idEncrypt() & idDecrypt()
Encrypt/decrypt ID untuk URL safety.

```php
$encrypted = idEncrypt(123); // "eyJpdiI6..."
$decrypted = idDecrypt($encrypted); // 123
```

### isActiveNav()
Check jika navigation active.

```blade
<a href="{{ route('users.index') }}" class="{{ isActiveNav('users.*') }}">
    Users
</a>
```

### asset_versioned()
Asset dengan version untuk cache busting.

```blade
<link href="{{ asset_versioned('css/app.css') }}" rel="stylesheet">
```

## 🏗️ Services Architecture

### UserService
Business logic untuk user management.

```php
use App\Services\UserService;

$userService = new UserService();

// Create user
$user = $userService->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password',
    'roles' => [1, 2]
]);

// Update user
$userService->update($user, [
    'name' => 'Jane Doe'
]);

// Delete user
$userService->delete($user);

// Get validation rules
$rules = UserService::getCreateRules();
```

### AuthService
Business logic untuk authentication.

```php
use App\Services\AuthService;

$authService = new AuthService();

// Login
$result = $authService->login('user@example.com', 'password');
// Returns: ['success' => true, 'data' => ['user' => ..., 'token' => ...]]

// Logout
$authService->logout($user);

// Refresh token
$result = $authService->refreshToken($user);
```

### CRUDService
Generic CRUD service yang bisa digunakan untuk model apapun.

```php
use App\Services\CRUDService;
use App\Models\Product;

$crudService = new CRUDService(new Product());

// Get all with pagination
$products = $crudService->getAll(['status' => 'active'], 15);

// Get by ID
$product = $crudService->getById(1);

// Create
$product = $crudService->create(['name' => 'Product 1']);

// Update
$crudService->update(1, ['name' => 'Product Updated']);

// Delete
$crudService->delete(1);

// Bulk delete
$crudService->bulkDelete([1, 2, 3]);
```

## 🎨 Components

### Toast Notification
Toast notification dengan style modern.

```blade
<x-ui.toast-notification />
```

**JavaScript:**
```javascript
Toast.success('User created successfully!');
Toast.error('Failed to delete user.');
Toast.warning('Please check your input.');
Toast.info('New update available.');
```

### Modal
Reusable modal component.

```blade
<x-ui.modal
    id="userModal"
    title="Create New User"
    size="lg"
    content-id="userModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form">Save</button>
    </x-slot>
</x-ui.modal>
```

**JavaScript:**
```javascript
Modal.load('userModal', '/users/create', 'Create New User');
Modal.show('userModal');
Modal.hide('userModal');
Modal.clear('userModal');
```

### DataTable
DataTable component (tidak digunakan di index.blade.php, tapi tersedia sebagai komponen).

```blade
<x-ui.datatable
    id="users-table"
    title="Users"
    :columns="['ID', 'Name', 'Email']"
    :ajax-url="route('users.index')"
/>
```

### Form Input
Reusable form input component.

```blade
<x-ui.form-input
    name="email"
    label="Email"
    type="email"
    :required="true"
    :errors="$errors"
/>
```

## 📜 JavaScript Helpers

### Toast Helper
File: `public/assets/js/helpers/toast.js`

```javascript
// Show toast
Toast.show('success', 'User created successfully!');
Toast.show('error', 'Failed to delete user.', 'Error');

// Shortcut methods
Toast.success('User created successfully!');
Toast.error('Failed to delete user.');
Toast.warning('Please check your input.');
Toast.info('New update available.');
```

### Modal Helper
File: `public/assets/js/helpers/modal.js`

```javascript
// Load content via AJAX
Modal.load('userModal', '/users/create', 'Create New User');

// Show/Hide
Modal.show('userModal');
Modal.hide('userModal');
Modal.toggle('userModal');

// Clear content
Modal.clear('userModal');

// Set title
Modal.setTitle('userModal', 'Edit User');
```

### Form Helper
File: `public/assets/js/helpers/form.js`

```javascript
// Submit form via AJAX
Form.submit('#user-form', {
    success: function(response) {
        Toast.success(response.message);
        Modal.hide('userModal');
    },
    error: function(xhr) {
        // Handle error
    }
});

// Clear errors
Form.clearErrors('#user-form');

// Reset form
Form.reset('#user-form');
```

## 🔐 Authentication & Authorization

### Features
- ✅ Login/Register dengan throttle protection (max 5 attempts per minute)
- ✅ Forgot/Reset Password
- ✅ Session timeout middleware
- ✅ Roles & Permissions (Spatie Laravel Permission)

### Default Roles
- **Admin**: Full access
- **Manager**: Limited access
- **User**: Basic access

### Default Users
- **Admin**: admin@example.com / password
- **Manager**: manager@example.com / password
- **User**: user@example.com / password

### Usage
```php
// Check role
$user->hasRole('admin');

// Assign role
$user->assignRole('admin');

// Check permission
$user->hasPermissionTo('edit users');

// Give permission
$user->givePermissionTo('edit users');
```

## 📝 Usage Examples

### Membuat CRUD Baru

```bash
# Generate CRUD
php artisan make:crud Product --migration

# Edit migration
# Edit app/Services/ProductService.php untuk validation rules
# Edit resources/views/features/products/partials/form.blade.php untuk form fields
```

### Menggunakan API

```javascript
// Login
const loginResponse = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password'
    })
});

const { data } = await loginResponse.json();
const token = data.token;

// Get user info
const meResponse = await fetch('/api/auth/me', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

### Menggunakan Helpers

```php
// Format date
echo formatDate(now()); // "10 Nov 2024"

// Format currency
echo formatRupiah(1000000); // "Rp 1.000.000"

// Generate slug
echo slugify('Product Name'); // "product-name"

// Encrypt ID
$encrypted = idEncrypt(123);
$decrypted = idDecrypt($encrypted);
```

### Menggunakan Services

```php
use App\Services\UserService;

$userService = new UserService();

// Create user
$user = $userService->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password',
    'roles' => [1]
]);
```

## 📦 Dependencies

- **Laravel Framework**: ^12.0
- **Laravel Sanctum**: ^4.2 (API Authentication)
- **Spatie Laravel Permission**: ^6.23 (Roles & Permissions)
- **Yajra DataTables**: ^12.6 (DataTables integration)

## 🎯 Best Practices

1. **Service Layer**: Gunakan Services untuk business logic, bukan di Controller
2. **Component Reusability**: Gunakan Blade components untuk UI yang reusable
3. **API Consistency**: Gunakan `ApiResponse` helper untuk semua API responses
4. **Error Handling**: Global exception handler menangani semua API errors
5. **Code Generation**: Gunakan CRUD Generator untuk mempercepat development

## 📚 Dokumentasi Tambahan

- [API Documentation](./API-DOCUMENTATION.md)
- [Architecture Guide](./ARCHITECTURE.md)
- [Refactoring Summary](./REFACTORING.md)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Credits

- [Laravel](https://laravel.com)
- [MatDash Bootstrap Admin](https://themewagon.com)
- [Spatie Laravel Permission](https://github.com/spatie/laravel-permission)
- [Yajra DataTables](https://github.com/yajra/laravel-datatables)

