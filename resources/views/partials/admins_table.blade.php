<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Actions</th>
    </thead>
    <tbody>
        @foreach ($admins as $admin)
            <tr id="row-{{ $admin->id }}">
                <form method="post" action="{{ route('admins.update', $admin->id) }}">
                    @csrf
                    @method('PUT')
                    <td>{{ $admin->id }}</td>
                    <td>
                        <span class="display display-username">{{ $admin->username }}</span>
                        <input type="text" name="username" class="edit form-control" value="{{ $admin->username }}" style="display: none;" readonly>
                    </td>
                   
                    <td>
                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $admin->id }}">Edit</button>
                        <button type="submit" class="btn btn-sm btn-success save-btn" data-id="{{ $admin->id }}" style="display: none;">Save</button>
                        </form>
                        <form method="post" action="{{ route('admins.destroy', $admin->id) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this admin?')">Remove</button>
                        </form>
                    </td>
            </tr>
        @endforeach
    </tbody>
</table>
