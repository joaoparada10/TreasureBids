@extends('layouts.admin')

@section('title', 'Manage Admins')

@section('content')
<div class="manage-section">
    <h1 class="manage-title">Manage Admins</h1>

    {{-- Table of Admins --}}
    @include('partials.admins_table')

    {{-- Create Admin Form --}}
    <button class="manage-button mt-3" id="toggleCreateForm">Create New Admin</button>
    <div id="createAdminForm" style="display: none;" class="create-form mt-3">
        @include('partials.create_admin')
    </div>
</div>


<script>
document.getElementById('toggleCreateForm').addEventListener('click', function() {
  const form = document.getElementById('createAdminForm');
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
</script>
<script>
document.querySelectorAll('.edit-btn').forEach(button => {
  button.addEventListener('click', function () {
    const rowId = this.dataset.id;
    const row = document.getElementById(`row-${rowId}`);

    // Toggle visibility of display and edit fields
    row.querySelectorAll('.display').forEach(el => el.style.display = 'none');
    row.querySelectorAll('.edit').forEach(el => {
      el.style.display = 'block';
      el.removeAttribute('readonly');
      el.removeAttribute('disabled');
    });

    this.style.display = 'none';
    row.querySelector('.save-btn').style.display = 'inline-block';
  });
});

</script>
@endsection