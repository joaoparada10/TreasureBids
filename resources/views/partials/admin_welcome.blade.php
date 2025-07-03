<div class="admin-dashboard-container bg-light p-4 shadow rounded">
    <!-- Title -->
    <h1 class="text-center text-primary mb-4">Welcome to the Admin Dashboard</h1>
    <p class="text-center text-muted">Manage members, auctions, categories, and other administrative tasks.</p>

    <!-- Navigation Links -->
    <div class="admin-links d-flex justify-content-center mt-4">
        <a href="{{ route('admin.manageMembers') }}" class="btn btn-primary mx-2">Manage Members</a>
        <a href="{{ route('admin.manageAuctions') }}" class="btn btn-primary mx-2">Manage Auctions</a>
        <a href="{{ route('admin.manageAdmins') }}" class="btn btn-primary mx-2">Manage Admins</a>
    </div>
</div>
