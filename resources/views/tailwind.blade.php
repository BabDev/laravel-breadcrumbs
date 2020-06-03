@php /** @var \Illuminate\Support\Collection $breadcrumbs **/ @endphp
@unless($breadcrumbs->isEmpty())
    <nav class="text-black font-bold my-8" aria-label="breadcrumb">
        <ol class="list-none p-0 inline-flex">
            @foreach($breadcrumbs as $breadcrumb)
                @if($breadcrumb->url)
                    @if(!$loop->last)
                        <li class="flex items-center">
                            <a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a>
                            <span class="mx-3">/</span>
                        </li>
                    @else
                        <li class="flex items-center">
                            <a href="#" class="text-gray-500" aria-current="page">{{ $breadcrumb->title }}</a>
                        </li>
                    @endif
                @else
                    <li class="flex items-center">
                        <a href="#" class="text-gray-500" aria-current="page">{{ $breadcrumb->title }}</a>
                        @if(!$loop->last)
                            <span class="mx-3">/</span>
                        @endif
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endunless
