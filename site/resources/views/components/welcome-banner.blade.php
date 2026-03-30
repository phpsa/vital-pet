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
             style="background-image: url('{{ asset('img/dog-acess.jpg') }}');">
            <span>Dog Accessories</span>
        </div>

        <div class="ves-hero-tile"
             style="background-image: url('{{ asset('img/dog-play.jpg') }}');">
            <span>Toys & Play</span>
        </div>

        <div class="ves-hero-tile"
             style="background-image: url('{{ asset('img/dog-treats.jpg') }}');">
            <span>Treats & Nutrition</span>
        </div>

        <div class="ves-hero-tile"
             style="background-image: url('{{ asset('img/dog-fun.jpg') }}');">
            <span>Walking Gear</span>
        </div>
    </div>
</section>
