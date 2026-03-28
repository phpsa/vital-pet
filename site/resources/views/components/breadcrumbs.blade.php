@props([
    'items' => [],
])

@if (count($items))
    <nav aria-label="Breadcrumb"
         class="ves-breadcrumbs">
        <ol>
            @foreach ($items as $item)
                <li>
                    @if (! empty($item['url']))
                        <a href="{{ $item['url'] }}"
                           wire:navigate>
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span aria-current="page">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif