<?php

namespace App\Livewire;

use App\Settings\ContentSettings;
use Illuminate\View\View;
use Livewire\Component;

class LegalPage extends Component
{
    public string $page;

    public string $title;

    public string $content;

    public function mount(string $page): void
    {
        $settings = app(ContentSettings::class);

        match ($page) {
            'terms'    => [$this->title, $this->content] = ['Terms & Conditions', $settings->terms_conditions],
            'privacy'  => [$this->title, $this->content] = ['Privacy Policy', $settings->privacy_policy],
            'returns'  => [$this->title, $this->content] = ['Return Policy', $settings->return_policy],
            'shipping' => [$this->title, $this->content] = ['Shipping Policy', $settings->shipping_policy],
            default    => abort(404),
        };

        $this->page = $page;
    }

    public function render(): View
    {
        return view('livewire.legal-page')
            ->title($this->title);
    }
}
