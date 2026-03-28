<section>
    <div class="max-w-screen-xl px-4 py-12 mx-auto sm:px-6 lg:px-8">
        @php
            $breadcrumbItems = [
                ['label' => 'Home', 'url' => url('/')],
                ['label' => 'Search', 'url' => null],
            ];
        @endphp

        <x-breadcrumbs :items="$breadcrumbItems" />

        <h1 class="text-3xl font-bold">
            Search Results
            @if (isset($term))
                for <u>{{ $term }}</u>
            @endif
        </h1>

        <div class="grid grid-cols-1 gap-8 mt-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->results as $result)
                <x-product-card :product="$result" />
            @endforeach
        </div>
    </div>
</section>
