<h2 class="text-center text-primary mb-4">Create New Member</h2>
<form method="post" action="{{ route('admin.createMember') }}">
  @csrf
  <div class="form-group mb-3">
    <label for="username" class="form-label">Username:</label>
    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
  </div>
  <div class="form-group mb-3">
    <label for="email" class="form-label">Email:</label>
    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
  </div>
  <div class="form-group mb-3">
    <label for="password" class="form-label">Password:</label>
    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
  </div>
  <div class="form-group mb-3">
    <label for="password_confirmation" class="form-label">Confirm Password:</label>
    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
  </div>
  <div class="form-group mb-3">
    <label for="first_name" class="form-label">First Name:</label>
    <input type="text" name="first_name" class="form-control" placeholder="Enter first name">
  </div>
  <div class="form-group mb-3">
    <label for="last_name" class="form-label">Last Name:</label>
    <input type="text" name="last_name" class="form-control" placeholder="Enter last name">
  </div>
  <div class="form-group mb-3">
    <label for="credit" class="form-label">Credit:</label>
    <input type="number" name="credit" class="form-control" placeholder="Enter credit amount">
  </div>
  <div class="form-check form-check-inline mb-4">
  <label class="form-check-label" for="blocked">Blocked?</label>
  <input type="checkbox" name="blocked"  id="blocked">
  
</div>
  <div class="d-flex justify-content-center">
    <button type="submit" class="btn btn-success px-4">Create Member</button>
  </div>
</form>