@extends('layouts.admin')

@section('title', 'Manage Members')

@section('content')
<div class="manage-section">
    <h1 class="manage-title">Manage Members</h1>

    {{-- Table of Members --}}
    @include('partials.admin_member_table')

    <div class="pagination-container">
    {{ $members->links() }}
</div>

    {{-- Create Member Form --}}
    <button class="manage-button mt-3" id="toggleCreateForm">Create New Member</button>
    <div id="createMemberForm" style="display: none;" class="create-form mt-3">
        @include('partials.create_member')
    </div>
    
</div>



<script>
document.getElementById('toggleCreateForm').addEventListener('click', function() {
  const form = document.getElementById('createMemberForm');
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
</script>
<script>
document.querySelectorAll('.edit-btn').forEach(button => {
  button.addEventListener('click', function () {
    const rowId = this.dataset.id;
    const row = document.getElementById(`row-${rowId}`);

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