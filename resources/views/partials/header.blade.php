<header class="navbar navbar-expand-lg shadow-sm">
  <div class="container-fluid">
    <!-- Logo -->
    <a href="{{ url('/home') }}" class="navbar-brand text-white fw-bold logo-text" style="word-wrap: break-word;">
      Treasure Bids
    </a>
    
    <!-- Search Bar -->
    <form id="auction-search-form" class="d-flex mx-auto w-50" method="GET" action="{{ route('search.results') }}"
      autocomplete="off">
      <input id="auction-search-box" class="form-control me-2" type="search" name="q"
        placeholder="Search for an auction" aria-label="Search">
      <button class="btn btn-secondary" type="submit"><i class="bi bi-search"></i></button>
      <div id="search-results" class="dropdown-menu w-100"></div>
    </form>

    <!-- Navigation -->
    <div class="d-flex align-items-center">
      @if (Auth::guard('admin')->check())
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a href="{{ route('admin.dashboard') }}"
            class="nav-link text-white px-3 py-2 rounded hover-effect">Dashboard</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.manageMembers') }}" class="nav-link text-white px-3 py-2 rounded hover-effect">Manage
            Members</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.manageAuctions') }}"
            class="nav-link text-white px-3 py-2 rounded hover-effect">Manage Auctions</a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.manageAdmins') }}" class="nav-link text-white px-3 py-2 rounded hover-effect">Manage
            Admins</a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/logout') }}" class="btn btn-danger ms-3 px-3 py-2 rounded">Logout</a>
        </li>
      </ul>
      @elseif (Auth::check())
      <script src="{{ asset('js/notifications.js') }}" defer></script>
      <div class="d-flex align-items-center" style="align-items: center">
        <div class="burger" id="burgerMenu">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <!-- Dropdown menu -->
        <div class="menu" id="dropdownMenu">
          <div class="dropdown">
            <a href="#" class="header-button btn btn-outline-secondary position-relative me-2" id="notification-button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i><span class="header-button-span">Notifications</span>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      id="notification-count" style="display: none;">
                        0
                </span>
            </a>
            <ul class="dropdown-menu" id="notification-list" style="width: 300px;">
                <!-- Notifications will be dynamically populated here -->
            </ul>
          </div>
        <a href="{{ route('profile', ['username' => Auth::user()->username]) }}" class=" header-button btn btn-outline-secondary me-2">
          <i class="bi bi-person-circle"></i> <span class="header-button-span">Profile</span>
        </a>
            <a href="{{ route('auction.create') }}" class="header-button btn btn-secondary me-2">
              <span class="">Create Auction</span>
            </a>
            <a href="{{ url('/logout') }}" class="header-button btn btn-secondary me-2">
              <span class="">Logout</span>
            </a>
        </div>
      </div>
      @else
      <a href="{{ url('/login') }}" class="btn btn-secondary ms-2">
        <i class="bi bi-person-circle"></i>
      </a>
      @endif
    </div>
  </div>
</header>

<script>
    // JavaScript to toggle menu
    const burger = document.getElementById('burgerMenu');
    const menu = document.getElementById('dropdownMenu');

    burger.addEventListener('click', () => {
        burger.classList.toggle('open');
        menu.classList.toggle('open');
    });
</script>