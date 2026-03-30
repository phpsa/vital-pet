<section class="member-hero"
         style="--member-hero-bg: url('{{ asset('img/memberstore/hero-performance.svg') }}');">
    <div class="member-hero-content">
        <span class="member-hero-rule" aria-hidden="true"></span>

        <div class="member-hero-copy">
            <h1 class="member-hero-title">Peak Performance</h1>

            <p class="member-hero-text">
                Enhance your athletic performance and
                recovery today
            </p>
        </div>

        <a class="member-hero-button"
           href="{{ route('search.view') }}"
           wire:navigate>
            Shop Now
        </a>

        <span class="member-hero-rule member-hero-rule-bottom" aria-hidden="true"></span>
    </div>
</section>