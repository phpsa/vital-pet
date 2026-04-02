<?php

declare(strict_types=1);

namespace App\Filament\Lunar\Pages;

use App\Settings\ContentSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Storage;

class ContentSettingsPage extends SettingsPage
{
    protected static string $settings = ContentSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Content Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 95;

    protected static ?string $title = 'Content Settings';

    protected static string $view = 'filament-spatie-laravel-settings-plugin::pages.settings-page';

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Content Settings')
                ->tabs([
                    Tab::make('Page Sections')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Section::make('Site Logo')
                                ->description('Upload your logo. It will be displayed in the header, footer, and emails. Recommended: PNG or SVG with transparent background, min 400px wide.')
                                ->schema([
                                    FileUpload::make('logo_path')
                                        ->label('Logo Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('logos')
                                        ->visibility('public')
                                        ->imagePreviewHeight('80')
                                        ->maxSize(2048)
                                        ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/jpeg', 'image/webp'])
                                        ->helperText('Max 2 MB. Accepted: PNG, SVG, JPG, WebP.')
                                        ->deleteUploadedFileUsing(function ($file) {
                                            Storage::disk('public')->delete($file);
                                        }),
                                ]),

                            Section::make('Site Banner')
                                ->description('If set, this text will appear in a prominent banner across the top of the site.')
                                ->schema([
                                    TextInput::make('banner_text')
                                        ->label('Banner Text')
                                        ->placeholder('Leave empty to hide the banner')
                                        ->helperText('Displayed in a full-width bar at the top of every page. Leave blank to disable.')
                                        ->maxLength(500),
                                ]),

                            Section::make('Footer')
                                ->schema([
                                    TextInput::make('footer_text')
                                        ->label('Footer Description')
                                        ->placeholder('A short tagline shown beneath the logo in the footer')
                                        ->maxLength(500),

                                    TextInput::make('contact_email')
                                        ->label('Contact Email')
                                        ->email()
                                        ->placeholder('contact@example.com')
                                        ->helperText('Displayed in the footer and used for contact links.'),
                                ]),
                        ]),

                    Tab::make('Terms & Conditions')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            RichEditor::make('terms_conditions')
                                ->label('Terms & Conditions')
                                ->helperText('This content will be shown on the /terms page.')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'strike',
                                    'h2', 'h3',
                                    'bulletList', 'orderedList',
                                    'link',
                                    'undo', 'redo',
                                ])
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Privacy Policy')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            RichEditor::make('privacy_policy')
                                ->label('Privacy Policy')
                                ->helperText('This content will be shown on the /privacy page.')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'strike',
                                    'h2', 'h3',
                                    'bulletList', 'orderedList',
                                    'link',
                                    'undo', 'redo',
                                ])
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Return Policy')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->schema([
                            RichEditor::make('return_policy')
                                ->label('Return Policy')
                                ->helperText('This content will be shown on the /returns page.')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'strike',
                                    'h2', 'h3',
                                    'bulletList', 'orderedList',
                                    'link',
                                    'undo', 'redo',
                                ])
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Shipping Policy')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            RichEditor::make('shipping_policy')
                                ->label('Shipping Policy')
                                ->helperText('This content will be shown on the /shipping page.')
                                ->toolbarButtons([
                                    'bold', 'italic', 'underline', 'strike',
                                    'h2', 'h3',
                                    'bulletList', 'orderedList',
                                    'link',
                                    'undo', 'redo',
                                ])
                                ->columnSpanFull(),
                        ]),

                    Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Section::make('Site Identity')
                                ->description('Used as the default title and meta tags across the storefront.')
                                ->schema([
                                    TextInput::make('site_title')
                                        ->label('Site Title')
                                        ->placeholder('My Store')
                                        ->helperText('Shown as the browser tab title on the home page, and appended to other page titles (e.g. "Product Name — My Store").')
                                        ->maxLength(100),

                                    Textarea::make('meta_description')
                                        ->label('Meta Description')
                                        ->placeholder('A short description of your store for search engines.')
                                        ->helperText('Recommended: 120–160 characters.')
                                        ->rows(3)
                                        ->maxLength(300),

                                    TextInput::make('meta_keywords')
                                        ->label('Meta Keywords')
                                        ->placeholder('peptides, supplements, health')
                                        ->helperText('Comma-separated keywords. Modern search engines largely ignore this, but it does no harm.'),
                                ]),
                        ]),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }

    /**
     * Wrap the stored string path into an array so Filament's FileUpload
     * component can hydrate its internal state correctly.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['logo_path'])) {
            $data['logo_path'] = [$data['logo_path']];
        } else {
            $data['logo_path'] = [];
        }

        return $data;
    }

    /**
     * Unwrap the FileUpload array back to a single string for storage.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (is_array($data['logo_path'])) {
            $data['logo_path'] = array_values($data['logo_path'])[0] ?? null;
        }

        return $data;
    }
}

