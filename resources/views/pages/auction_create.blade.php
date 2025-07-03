@extends('layouts.app')

@section('content')
<script src="{{ asset('js/auction_create.js') }}" defer></script>
<link href="{{ url('css/edit_auction.css') }}" rel="stylesheet">
<div class="container mt-5">
  <h1 class="text-center text-primary mb-4">Create a New Auction</h1>

  <form action="{{ route('auctions.store') }}" method="POST" enctype="multipart/form-data" class="shadow p-4 bg-white rounded" novalidate id="auction-form">
    @csrf

    <!-- Auction Details -->
    <fieldset class="mb-4">
      <legend class="fw-bold text-primary">Auction Details</legend>
      
      <div class="form-group mb-3">
        <label for="title" class="form-label fw-bold">Title</label>
        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
        @error('title')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Provide a short and descriptive title (max 255 characters).</small>
      </div>

      <div class="form-group mb-3">
        <label for="description" class="form-label fw-bold">Description</label>
        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Minimum 10 characters to explain your auction.</small>
      </div>

      <div class="form-group mb-3">
        <label class="form-label fw-bold">Categories (Select up to 2)</label>
        <div id="categories-container" style="display: flex; flex-wrap: wrap; gap: 20px;">
          @foreach ($categories as $category)
            <div class="form-check">
              <input type="checkbox" name="categories[]" id="category_{{ $category->id }}" value="{{ $category->id }}" class="form-check-input category-checkbox">
              <label for="category_{{ $category->id }}" class="form-check-label">{{ $category->name }}</label>
            </div>
          @endforeach
        </div>
        <small class="text-danger" id="category-error" style="display:none;">You can only select up to 2 categories.</small>
        @error('categories')
          <div class="text-danger">{{ $message }}</div>
        @enderror
      </div>
    </fieldset>

    <!-- Pricing -->
    <fieldset class="mb-4">
      <legend class="fw-bold text-primary">Pricing</legend>
      
      <div class="form-group mb-3">
        <label for="starting_price" class="form-label fw-bold">Starting Price (€)</label>
        <input type="number" class="form-control @error('starting_price') is-invalid @enderror" id="starting_price" name="starting_price" min="0" value="{{ old('starting_price') }}" required>
        @error('starting_price')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Set a reasonable starting price.</small>
      </div>

      <div class="form-group mb-3">
        <label for="buyout_price" class="form-label fw-bold">Buyout Price (€) (Optional)</label>
        <input type="number" class="form-control @error('buyout_price') is-invalid @enderror" id="buyout_price" name="buyout_price" min="0" value="{{ old('buyout_price') }}">
        @error('buyout_price')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Leave empty if there is no buyout price.</small>
      </div>
    </fieldset>

    <!-- Dates -->
    <fieldset class="mb-4">
      <legend class="fw-bold text-primary">Dates</legend>

      <div class="form-group mb-3">
        <label for="starting_date" class="form-label fw-bold">Starting Date</label>
        <input type="datetime-local" class="form-control @error('starting_date') is-invalid @enderror" id="starting_date" name="starting_date" value="{{ old('starting_date') }}" required>
        @error('starting_date')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Auction will start at this time.</small>
      </div>

      <div class="form-group mb-3">
        <label for="end_date" class="form-label fw-bold">End Date</label>
        <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
        @error('end_date')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Auction end time must be after the start time.</small>
      </div>
    </fieldset>

    <!-- Picture -->
    <fieldset class="mb-4">
      <legend class="fw-bold text-primary">Picture</legend>
      
      <div class="form-group mb-3">
        <label for="picture" class="form-label fw-bold">Picture</label>
        <input type="file" class="form-control @error('picture') is-invalid @enderror" id="picture" name="picture" required>
        @error('picture')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div id="picture-preview" class="mt-3 text-center">
          <img id="preview-img" src="{{ asset('auction/no_image.png') }}" alt="Auction Picture Preview" class="img-thumbnail" style="max-width: 150px; max-height: 150px; display: none;">
        </div>
      </div>
    </fieldset>

    <!-- Submit -->
    <div class="d-flex justify-content-center">
      <button type="submit" class="btn btn-primary px-5">Create Auction</button>
    </div>
  </form>
</div>
@endsection
