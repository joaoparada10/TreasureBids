@foreach ($categories as $category)
    @include('partials.tag', ['category' => $category])
@endforeach