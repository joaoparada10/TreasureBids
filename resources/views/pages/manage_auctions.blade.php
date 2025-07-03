@extends('layouts.admin')

@section('title', 'Manage Auctions')

@section('content')
<div class="manage-section">
    <h1 class="manage-title">Manage Auctions</h1>

    {{-- Table of Auctions --}}
    @include('partials.admin_auction_table')
    <div class="pagination-container">
    {{ $auctions->links() }}
</div>
</div>


<script>
document.querySelectorAll('.edit-btn').forEach(button => {
  button.addEventListener('click', function(event) {
    event.preventDefault(); 

    const rowId = this.dataset.id;
    const row = document.getElementById(`auction-row-${rowId}`);

    row.querySelectorAll('.display').forEach(el => el.style.display = 'none');
    row.querySelectorAll('.edit').forEach(el => el.style.display = 'block');

    this.style.display = 'none';
    row.querySelector('.save-btn').style.display = 'inline-block'; 
    row.querySelector('.cancel-btn').style.display = 'inline-block'; 
  });
});

document.querySelectorAll('.cancel-btn').forEach(button => {
  button.addEventListener('click', function(event) {
    event.preventDefault(); 

    const rowId = this.dataset.id;
    const row = document.getElementById(`auction-row-${rowId}`);

    row.querySelectorAll('.display').forEach(el => el.style.display = 'block');
    row.querySelectorAll('.edit').forEach(el => {
      el.style.display = 'none';
      el.value = el.defaultValue; 
    });


    row.querySelector('.edit-btn').style.display = 'inline-block';
    row.querySelector('.save-btn').style.display = 'none';
    this.style.display = 'none'; 
  });
});

document.querySelectorAll('.save-btn').forEach(button => {
  button.addEventListener('click', function(event) {
    event.preventDefault(); 

    const rowId = this.dataset.id;
    const row = document.getElementById(`auction-row-${rowId}`);

    const startingDateInput = row.querySelector('input[name="starting_date"]');
    const endDateInput = row.querySelector('input[name="end_date"]');
    const startingDate = new Date(startingDateInput.value);
    const endDate = new Date(endDateInput.value);

    if (startingDate >= endDate) {
      alert('Starting Date must be before End Date.');
      return; 
    }


    row.querySelector('form').submit();
  });
});
</script>

@endsection