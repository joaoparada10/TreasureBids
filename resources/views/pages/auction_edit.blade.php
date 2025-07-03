@extends('layouts.app')

@section('title', 'Edit Auction - '.$auction->title)

@section('content')
<script src="{{ asset('js/auction_create.js') }}"></script>
<link href="{{ url('css/edit_auction.css') }}" rel="stylesheet">
<div class="container py-4">
    <h1 class="text-primary text-center mb-4">Edit Auction</h1>

    <form method="POST" action="{{ route('auctions.update', $auction->id) }}" enctype="multipart/form-data" class="shadow p-4 bg-white rounded">
        @csrf
        @method('PUT')

        <!-- Auction Details -->
        <fieldset class="mb-4">
            <legend class="fw-bold text-primary">Auction Details</legend>
            <!-- Title -->
        <div class="form-group mb-3">
            <label for="title" class="form-label fw-bold">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $auction->title) }}" required>
        </div>

        <!-- Description -->
        <div class="form-group mb-3">
            <label for="description" class="form-label fw-bold">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description', $auction->description) }}</textarea>
        </div>

        <!-- Categories Selection -->
        <div class="form-group mb-3">
            <label class="form-label fw-bold">Categories (Select up to 2)</label>
            <div id="categories-container" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <div class="column" style="flex: 1;">
                    @foreach ($categories->slice(0, ceil($categories->count() / 3)) as $category)
                        <div class="form-check">
                            <input type="checkbox" 
                                name="categories[]" 
                                id="category_{{ $category->id }}" 
                                value="{{ $category->id }}" 
                                class="form-check-input category-checkbox"
                                {{ $auction->category->contains($category->id) ? 'checked' : '' }}>
                            <label for="category_{{ $category->id }}" 
                                class="form-check-label" 
                                style="color: #333; font-weight: 500;">
                                {{ $category->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="column" style="flex: 1;">
                    @foreach ($categories->slice(ceil($categories->count() / 3), ceil($categories->count() / 3)) as $category)
                        <div class="form-check">
                            <input type="checkbox" 
                                name="categories[]" 
                                id="category_{{ $category->id }}" 
                                value="{{ $category->id }}" 
                                class="form-check-input category-checkbox"
                                {{ $auction->category->contains($category->id) ? 'checked' : '' }}>
                            <label for="category_{{ $category->id }}" 
                                class="form-check-label" 
                                style="color: #333; font-weight: 500;">
                                {{ $category->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="column" style="flex: 1;">
                    @foreach ($categories->slice(2 * ceil($categories->count() / 3)) as $category)
                        <div class="form-check">
                            <input type="checkbox" 
                                name="categories[]" 
                                id="category_{{ $category->id }}" 
                                value="{{ $category->id }}" 
                                class="form-check-input category-checkbox"
                                {{ $auction->category->contains($category->id) ? 'checked' : '' }}>
                            <label for="category_{{ $category->id }}" 
                                class="form-check-label" 
                                style="color: #333; font-weight: 500;">
                                {{ $category->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        </fieldset>

        <!-- Pricing -->
        <fieldset class="mb-4">
            <legend class="fw-bold text-primary">Pricing</legend>
            <!-- Starting Price -->
            <div class="form-group mb-3">
                <label for="starting_price" class="form-label fw-bold">Starting Price (€)</label>
                <input type="number" id="starting_price" name="starting_price" class="form-control" value="{{ old('starting_price', $auction->starting_price) }}" required>
            </div>

            <!-- Buyout Price -->
            <div class="form-group mb-3">
                <label for="buyout_price" class="form-label fw-bold">Buyout Price (€)</label>
                <input type="number" id="buyout_price" name="buyout_price" class="form-control" value="{{ old('buyout_price', $auction->buyout_price) }}">
            </div>
        </fieldset>

        <!-- Dates -->
        <fieldset class="mb-4">
            <legend class="fw-bold text-primary">Dates</legend>
            <!-- Starting Date -->
            <div class="form-group mb-3">
                <label for="starting_date" class="form-label fw-bold">Starting Date</label>
                <input type="datetime-local" id="starting_date" name="starting_date" class="form-control" 
                    value="{{ old('starting_date', \Carbon\Carbon::parse($auction->starting_date)->format('Y-m-d\TH:i')) }}" required>
            </div>

            <!-- End Date -->
            <div class="form-group mb-3">
                <label for="end_date" class="form-label fw-bold">End Date</label>
                <input type="datetime-local" id="end_date" name="end_date" class="form-control" 
                    value="{{ old('end_date', $auction->end_date->format('Y-m-d\TH:i')) }}" required>
            </div>
        </fieldset>

        <fieldset class="mb-4">
            <legend class="fw-bold text-primary">Picture</legend>
            <!-- Picture -->
            <div class="form-group mb-4">
                <label for="picture" class="form-label fw-bold">Picture</label>
                <input type="file" id="picture" name="picture" class="form-control">
                <input name="type" type="text" value="auction_type" hidden>
                <input name="id" type="number" value="{{ $auction->id }}" hidden>
                <div id="picture-preview" class="mt-3">
                    <img id="preview-img" 
                        src="{{ $auction->getAuctionImage() }}" 
                        alt="Picture Preview" 
                        class="img-thumbnail"
                        style="max-width: 150px; max-height: 150px; display: {{ $auction->picture ? 'block' : 'none' }};">
                </div>
            </div>
        </fieldset>

        <!-- Buttons -->
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary me-2">Save Changes</button>
            <a href="{{ route('auction.show', $auction->id) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.category-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checked = document.querySelectorAll('.category-checkbox:checked');
                if (checked.length > 2) {
                    alert('You can only select up to 2 categories.');
                    checkbox.checked = false;
                }
            });
        });
    });
</script>