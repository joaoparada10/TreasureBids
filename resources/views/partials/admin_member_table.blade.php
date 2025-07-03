<div class="mb-3">
    <input type="text" id="filter-input" class="form-control" placeholder="Search for members...">
</div>
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Address</th>
                <th>Credit</th>
                <th>Blocked</th>
                <th>Actions</th>
            </tr>
        </thead>
    <tbody>
        @foreach ($members as $member)
            <tr id="row-{{ $member->id }}">
                <form method="post" action="{{ route('members.update', $member->id) }}">
                    @csrf
                    @method('PUT')
                    <td>{{ $member->id }}</td>
                    <td>
                        <span class="display display-username">{{ $member->username }}</span>
                        <input type="text" name="username" class="edit form-control" value="{{ $member->username }}" style="display: none;" readonly>
                    </td>
                    <td>
                        <span class="display display-email">{{ $member->email }}</span>
                        <input type="email" name="email" class="edit form-control" value="{{ $member->email }}" style="display: none;" readonly>
                    </td>
                    <td>
                        <span class="display display-first_name">{{ $member->first_name }}</span>
                        <input type="text" name="first_name" class="edit form-control" value="{{ $member->first_name }}" style="display: none;" readonly>
                    </td>
                    <td>
                        <span class="display display-last_name">{{ $member->last_name }}</span>
                        <input type="text" name="last_name" class="edit form-control" value="{{ $member->last_name }}" style="display: none;" readonly>
                    </td>
                    <td>
                        <span class="display display-address">{{ $member->address }}</span>
                        <input type="text" name="address" class="edit form-control" value="{{ $member->address }}" style="display: none;" readonly>
                    </td>
                    <td>
                        <span class="display display-credit">{{ $member->credit }}</span>
                        <input type="number" name="credit" class="edit form-control" value="{{ $member->credit }}" style="display: none;" readonly>
                    </td>
                    <td>
                        <span class="display display-blocked">{{ $member->blocked ? 'Yes' : 'No' }}</span>
                        <select name="blocked" class="edit form-control" style="display: none;" disabled>
                            <option value="1" {{ $member->blocked ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ !$member->blocked ? 'selected' : '' }}>No</option>
                        </select>
                    </td>
                    
                    <td>
                        <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $member->id }}">Edit</button>
                        <button type="submit" class="btn btn-sm btn-success save-btn" data-id="{{ $member->id }}" style="display: none;">Save</button>
                        </form>
                        <form method="post" action="{{ route('admin.removeMember', $member->id) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this member?')">Remove</button>
                        </form>
                    </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

<script>
    document.getElementById('filter-input').addEventListener('input', function () {
    const query = this.value.trim(); // Get the filter query

    // Fetch filtered results via AJAX
    fetch(`/api/members/filter?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            updateTable(data); // Update table rows with filtered results
        })
        .catch(error => console.error('Error:', error));
});

function updateTable(members) {
    const tableBody = document.querySelector('table tbody');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    tableBody.innerHTML = ''; 

    if (members.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8">No members found.</td></tr>';
        return;
    }

    members.forEach(member => {
        const row = `
            <tr id="row-${member.id}">
                <td>${member.id}</td>
                <td>
                    <span class="display display-username">${member.username}</span>
                </td>
                <td>
                    <span class="display display-email">${member.email}</span>
                </td>
                <td>
                    <span class="display display-first_name">${member.first_name}</span>
                </td>
                <td>
                    <span class="display display-last_name">${member.last_name}</span>
                </td>
                <td>
                    <span class="display display-address">${member.address}</span>
                </td>
                <td>
                    <span class="display display-credit">${member.credit}</span>
                </td>
                <td>
                    <span class="display display-blocked">${member.blocked ? 'Yes' : 'No'}</span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="{{ $member->id }}">Edit</button>
                    <button type="submit" class="btn btn-sm btn-success save-btn" data-id="{{ $member->id }}" style="display: none;">Save</button>
                    <form method="post" action="{{ route('admin.removeMember', $member->id) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this member?')">Remove</button>
                        </form>
                </td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', row);
    });
}

</script>
