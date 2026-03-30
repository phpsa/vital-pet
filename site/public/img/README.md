# Theme-Specific Images

## Directory Structure

```
public/img/
├── petstore/           # Pet store images (animals, pets, pet-related)
├── memberstore/        # Peptide/member store images (scientific, peptide molecules)
└── [common images]     # Shared images used by all themes
```

## Usage

### In Blade Templates

Use the Blade directives to get theme-specific configuration:

```blade
<!-- Get theme image directory -->
<img src="{{ asset(@themeImageDir . 'banner.jpg') }}" alt="Banner">

<!-- Get specific theme config -->
<img src="{{ asset(@heroImage) }}" alt="Hero">

<!-- Get logo -->
<img src="{{ asset(@logo) }}" alt="Logo">

<!-- Get brand color -->
<div style="background-color: @brandColor;">...</div>
```

### In PHP/Controllers

Use the `TemplateHelper` class:

```php
use App\Support\TemplateHelper;

// Get image directory
$imageDir = TemplateHelper::imageDir();

// Get specific theme value
$logo = TemplateHelper::logo();
$brandColor = TemplateHelper::brandColor();

// Check current template
if (TemplateHelper::isPetstore()) {
    // Pet store specific logic
}

if (TemplateHelper::ismemberstore()) {
    // Peptide store specific logic
}
```

## Theme Specific Content

### Petstore (petstore/)
- Pet-themed images
- Animal icons and illustrations
- Pet product photography
- Fun, colorful branding

### Memberstore (memberstore/)
- Peptide/molecular structures
- Scientific imagery
- Professional/corporate branding
- Molecular diagrams and compounds
- Medical/scientific themed graphics

## Adding Theme-Specific Images

1. Determine which template the image is for
2. Place it in the appropriate directory:
   - `public/img/petstore/` for pet store
   - `public/img/memberstore/` for peptide/member store
3. Reference it in your views using the template helpers
4. The image will automatically load for the active template

## Example: Hero Image

#### Petstore Version
`public/img/petstore/hero-pets.jpg` - Cute pets, playful imagery

#### Memberstore Version  
`public/img/memberstore/hero-peptide.jpg` - Molecular structures, scientific look

#### In Template
```blade
<img src="{{ asset(@heroImage()) }}" alt="Hero" class="hero-banner">
```

The correct image will load based on the active template!
