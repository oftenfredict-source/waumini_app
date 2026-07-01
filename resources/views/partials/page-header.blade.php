@php
    $icon = $icon ?? null;
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $breadcrumb = $breadcrumb ?? [];
@endphp

<div class="app-title">
    <div>
        <h1>
            @if($icon)
                <i class="fa {{ $icon }}"></i>
            @endif
            {{ $title }}
        </h1>
        @if($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    @if(! empty($breadcrumb))
        <ul class="app-breadcrumb breadcrumb">
            @foreach($breadcrumb as $item)
                <li class="breadcrumb-item">
                    @if(! empty($item['route']))
                        <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                    @else
                        {{ $item['label'] }}
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
