<section class="ves-hero">
    <div class="ves-panel ves-hero-copy">
        <span class="ves-kicker">Happy Paws Daily</span>

        <h1 class="ves-serif ves-hero-title">
            Crafted for tails,
            <br>
            designed for joy.
        </h1>

        <p class="ves-hero-text">
            Shop quality dog accessories, engaging toys, and nutritious treats
            designed for active play, training moments, and everyday adventures.
        </p>

        <div class="ves-hero-actions">
            <a class="ves-button ves-button-primary"
               href="{{ route('search.view') }}"
               wire:navigate>
                Shop All Products
            </a>

            <a class="ves-button ves-button-secondary"
               href="#featured">
                Featured Picks
            </a>
        </div>
    </div>

    <div class="ves-hero-grid">
        <div class="ves-hero-tile"
             style="background-image: url('https://picsum.photos/900/600?random=1');">
            <span>Dog Accessories</span>
        </div>

        <div class="ves-hero-tile"
             style="background-image: url('https://picsum.photos/900/600?random=2');">
            <span>Toys & Play</span>
        </div>

        <div class="ves-hero-tile"
             style="background-image: url('https://picsum.photos/900/600?random=3');">
            <span>Treats & Nutrition</span>
        </div>

        <div class="ves-hero-tile"
             style="background-image: url('https://picsum.photos/900/600?random=4');">
            <span>Walking Gear</span>
        </div>
    </div>
</section>
