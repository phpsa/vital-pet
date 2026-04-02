<div>
    <div class="max-w-screen-lg px-4 py-16 mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ $title }}</h1>

        @if ($content)
            <div class="prose prose-gray max-w-none">
                {!! $content !!}
            </div>
        @else
            <p class="text-gray-500">This page has not been populated yet. Please check back soon.</p>
        @endif
    </div>
</div>
