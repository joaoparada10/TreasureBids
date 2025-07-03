<a class="category" href="{{ route('category.show', $category->id) }}" style="border-color: {{$category->color}}; background-color: {{$category->color.'55'}}">
    {{$category->name}}
</a>