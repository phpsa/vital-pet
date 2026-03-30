# Template System Documentation

## Overview

The application supports multiple frontend templates/themes that can be switched via configuration. This allows you to maintain separate frontend implementations for different storefronts while sharing common views.

Each template can have its own styling, branding, images, and other assets.

## Directory Structure

```
resources/views/
├── petstore/          # Pet store specific views (high priority)
├── memberstore/       # Member store specific views (high priority)
├── common/            # Shared views across all templates (fallback)
├── layouts/           # (legacy - base layouts)
├── components/        # (legacy - shared components)
└── ...               # (legacy - other shared views)

public/img/
├── petstore/          # Pet store theme images
├── memberstore/       # Peptide/member store theme images
└── [common images]    # Shared images
```

## How It Works

1. **View Resolution Order** (first match wins):
   - Active template folder: `resources/views/{TEMPLATE}/yourview.blade.php`
   - Common folder: `resources/views/common/yourview.blade.php`
   - Default views: `resources/views/yourview.blade.php`

2. **Theme Configuration** (in `config/template.php`):
   - Each template has a theme config with logo, colors, image paths, etc.
   - Easily access via Blade macros or PHP helpers

3. **Image Loading**:
   - Place theme-specific images in `public/img/{template}/`
   - Reference via `@theme('image_directory')` or `TemplateHelper::imageDir()`

## Configuration

### Switch Templates

Update your `.env` file:
```env
TEMPLATE=petstore   # Current default
TEMPLATE=memberstore
```

Or set dynamically (as currently configured to use domain):
```php
// In config/template.php
'active' => $_SERVER['HTTP_HOST'] === 'store.vital.lndo' ? 'memberstore' : env('TEMPLATE', 'petstore'),
```

### Add New Template

1. Add to `config/template.php`:
```php
'templates' => [
    'petstore',
    'memberstore',
    'newstore',  // Add here
],

'themes' => [
    // ... existing themes
    'newstore' => [
        'name' => 'New Store',
        'description' => 'New store theme',
        'logo' => 'img/newstore-logo.svg',
        'brand_color' => '#CODE',
        'image_directory' => 'img/newstore/',
    ],
],
```

2. Create directories:
```bash
mkdir -p resources/views/newstore
mkdir -p public/img/newstore
```

## Usage in Views

### Blade Directives

Use these convenient directives in your Blade templates:

```blade
<!-- Get active template name -->
@activeTemplate

<!-- Get theme configuration value -->
@theme('brand_color')
@theme('image_directory', 'img/')

<!-- Get specific theme values -->
@themeImageDir
@brandColor
@logo
@heroImage

<!-- Check current template -->
@petstore
    {{-- Pet store specific content --}}
@endpetstore

@memberstore
    {{-- Peptide store specific content --}}
@endmemberstore
```

### Example Usage in Templates

```blade
<!-- Display brand-specific logo -->
<img src="{{ asset(@logo) }}" alt="Logo" class="navbar-logo">

<!-- Display hero image -->
<div style="background-image: url('{{ asset(@heroImage) }}');" class="hero">
    <h1>Welcome!</h1>
</div>

<!-- Use brand color -->
<button style="background-color: @brandColor;">
    Shop Now
</button>

<!-- Display template-specific images -->
<img src="{{ asset(@themeImageDir . 'product-banner.jpg') }}" alt="Products">

<!-- Conditional content -->
@petstore
    <p>🐾 Welcome to our pet store!</p>
@endpetstore

@memberstore
    <p>🧪 Welcome to our peptide store!</p>
@endmemberstore
```

## Usage in PHP

### TemplateHelper Class

Use the `TemplateHelper` in controllers, services, or anywhere in PHP:

```php
use App\Support\TemplateHelper;

// Get active template
$template = TemplateHelper::active();           // 'petstore'

// Get theme configuration
$theme = TemplateHelper::theme();               // Full array
$value = TemplateHelper::themeConfig('logo');   // Specific value

// Get commonly used values
$imageDir = TemplateHelper::imageDir();         // 'img/petstore/'
$brandColor = TemplateHelper::brandColor();     // '#FF6B35'
$logo = TemplateHelper::logo();                 // 'img/petstore-logo.svg'
$heroImage = TemplateHelper::heroImage();       // 'img/petstore/hero-pets.jpg'

// Check current template
if (TemplateHelper::isPetstore()) {
    // Pet-specific logic
}

if (TemplateHelper::ismemberstore()) {
    // Peptide-specific logic
}

if (TemplateHelper::is('petstore')) {
    // Alternative syntax
}
```

### Example Controller Usage

```php
namespace App\Http\Controllers;

use App\Support\TemplateHelper;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index', [
            'theme' => TemplateHelper::theme(),
            'imageDir' => TemplateHelper::imageDir(),
            'isPetstore' => TemplateHelper::isPetstore(),
        ]);
    }
}
```

## Theme Configuration (config/template.php)

```php
'themes' => [
    'petstore' => [
        'name' => 'Pet Store',
        'description' => 'Pet-themed storefront',
        'logo' => 'img/petstore-logo.svg',
        'favicon' => 'favicon-pet.ico',
        'brand_color' => '#FF6B35',
        'image_directory' => 'img/petstore/',
        'hero_image' => 'img/petstore/hero-pets.jpg',
    ],
    'memberstore' => [
        'name' => 'Peptide Store',
        'description' => 'Scientifically-themed peptide storefront',
        'logo' => 'img/memberstore-logo.svg',
        'favicon' => 'favicon-peptide.ico',
        'brand_color' => '#0066CC',
        'image_directory' => 'img/memberstore/',
        'hero_image' => 'img/memberstore/hero-peptide.jpg',
    ],
],
```

Feel free to add more theme-specific configuration as needed!

## Best Practices

### Views
- **Shared Logic**: Keep reusable views in `common/`
- **Template-Specific**: Only override in template folder when different layout/styling is needed
- **Use Macros**: Always use `@theme()` and related macros for consistency
- **Naming**: Use clear, hierarchical folder structure (e.g., `pages/`, `components/`, `partials/`)

### Images
- Place theme-specific images in `public/img/{template}/`
- Reference using `@theme('image_directory')` for easy switching
- Use meaningful names: `hero-pets.jpg`, not just `hero.jpg`

### Configuration
- Add new theme config values to `config/template.php` as needed
- Use `TemplateHelper::themeConfig('key')` to access custom values
- Document custom config values in comments

## Example File Structure

```
resources/views/
├── common/
│   ├── layouts/
│   │   ├── app.blade.php
│   │   └── auth.blade.php
│   ├── components/
│   │   ├── navbar.blade.php
│   │   └── footer.blade.php
│   └── partials/
│       └── loading.blade.php
│
├── petstore/
│   ├── landing/
│   │   └── special.blade.php
│   │       (Pet-themed special landing page)
│   └── products/
│       └── index.blade.php
│           (Pet store specific product listing)
│
└── memberstore/
    ├── landing/
    │   └── special.blade.php
    │       (Peptide-themed special landing page)
    └── products/
        └── index.blade.php
            (Peptide store specific product listing)

public/img/
├── petstore/
│   ├── hero-pets.jpg
│   ├── pet-icons/
│   └── ...
│
└── memberstore/
    ├── hero-peptide.jpg
    ├── molecular-structures/
    └── ...
```

## Artisan Commands

Clear view cache when making changes:
```bash
php artisan view:clear
```

Or use Docker:
```bash
lando artisan view:clear
```

## Themes

### Petstore Theme 🐾
- **Brand Color**: Orange (#FF6B35)
- **Imagery**: Animals, pets, playful graphics
- **Tone**: Fun, friendly, approachable
- **Target**: Pet lovers and owners

### Memberstore Theme 🧪
- **Brand Color**: Blue (#0066CC)
- **Imagery**: Peptides, molecular structures, scientific diagrams
- **Tone**: Professional, scientific, technical
- **Target**: Scientific community, researchers, professionals
