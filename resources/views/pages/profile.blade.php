@extends('layouts.app')

@section('title', $user->username . "'s Profile")

@section('content')


@php
    use Carbon\Carbon;
@endphp



<div class="profile-container d-flex">
  <script src="{{ asset('js/profile.js') }}" defer></script>

  <!-- Sidebar -->
  <aside class="sidebar bg-primary text-white shadow p-3">
    <ul class="profileMenu nav flex-column">
      <li class="menu-item nav-item active">
        <a href="#" class="nav-link text-white" data-content="user-details">
          <i class="bi bi-person"></i> <span class="menu-item-text">User Details</span>
        </a>
      </li>
      <li class="menu-item nav-item">
        <a href="#" class="nav-link text-white" data-content="auctions">
          <i class="bi bi-newspaper"></i> <span class="menu-item-text">Auctions</span>
        </a>
      </li>
      <li class="menu-item nav-item">
        <a href="#" class="nav-link text-white" data-content="faq">
          <i class="bi bi-question-circle"></i> <span class="menu-item-text">FAQ</span>
        </a>
      </li>
      <li class="menu-item nav-item">
        <a href="#" class="nav-link text-white" data-content="about-us">
          <i class="bi bi-info-circle"></i> <span class="menu-item-text">About Us</span>
        </a>
      </li>
    </ul>
  </aside>

  <!-- Profile Content -->
  <div class="profile-content flex-grow-1 bg-light p-4">
    <div id="user-details" class="content-section">

      <div class="profile-picture-container my-4 text-center" style="flex-direction: column">
        <img src="{{ $user->getProfileImage() }}" alt="Profile Picture" class="profile-picture rounded-circle shadow">
        <h1 class="text-primary">{{ $user->username }}</h1>
        @if ($averageRating)
        <div id="star-display" class="star-rating justify-content-center" rating={{$averageRating}}></div>
        @else
        @endif
      </div>

      <div class="profile-details">
        <p><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        @if(auth()->user() && auth()->user()->id === $user->id)
        <p><strong>Address:</strong> {{ $user->address }}</p>
        <p><strong>Credit:</strong> <span class="credit">€{{ $user->credit }}</span></p>

        <div class="profile-own-buttons">
          <button class="btn btn-warning mt-3 edit-profile-btn">Edit Profile</button>
          <button class="btn btn-warning mt-3 edit-profile-btn popup">Add Credit</button>
          <form method="POST" action="{{ route('account.delete') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" style="margin-top: 16px"
              onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
              Delete My Account
            </button>
          </form>

        </div>
        @endif
      </div>
    </div>

    <!-- Edit Profile Section -->
    <div id="edit-profile" class="content-section d-none">
      <h1 class="text-primary">Edit Profile</h1>
      <form id="edit-profile-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" name="username" id="username" class="form-control" value="{{ $user->username }}">
        </div>

        <div class="mb-3">
          <label for="first_name" class="form-label">First Name</label>
          <input type="text" name="first_name" id="first_name" class="form-control" value="{{ $user->first_name }}">
        </div>

        <div class="mb-3">
          <label for="last_name" class="form-label">Last Name</label>
          <input type="text" name="last_name" id="last_name" class="form-control" value="{{ $user->last_name }}">
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
        </div>

        <div class="mb-3">
          <label for="address" class="form-label">Address</label>
          <input type="text" name="address" id="address" class="form-control" value="{{ $user->address }}">
        </div>

        <div class="mb-3">
          <label for="current_password" class="form-label">Current Password</label>
          <input type="password" name="current_password" id="current_password" class="form-control"
            placeholder="Enter current password">
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">New Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password">
        </div>

        <div class="mb-3">
          <label for="password_confirmation" class="form-label">Confirm Password</label>
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
            placeholder="Confirm new password">
        </div>

        <div class="mb-3">
          <label for="picture" class="form-label">Profile Picture</label>
          <input type="file" name="picture" id="picture" class="form-control">
          <input name="type" type="text" value="profile_type" hidden>
          <input name="id" type="number" value="{{ $user->id }}" hidden>

          <!-- Image preview -->
          <div id="profile-picture-preview" class="mt-3 text-center">
            <img id="preview-img" src="{{ $user->getProfileImage() }}" alt="Profile Picture Preview"
              class="rounded-circle shadow" style="max-width: 150px; max-height: 150px; object-fit: cover;">
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <button type="button" class="btn btn-secondary cancel-edit-btn">Cancel</button>
      </form>
    </div>

    <div id="auctions" class="content-section d-none">
      <h1 class="text-primary border-bottom pb-2 mb-4">Auctions</h1>

      <div class="auction-choice">
        <button type="button" class="choice your-auctions-btn {{ request('createdAuctionsPage') || !request('biddedAuctionsPage') && !request('followedAuctionsPage') ? 'selected' : '' }}">Created Auctions</button>
        <button type="button" class="choice bidded-auctions-btn {{ request('biddedAuctionsPage') ? 'selected' : '' }}">Bidded Auctions</button>
        <button type="button" class="choice followed-auctions-btn {{ request('followedAuctionsPage') ? 'selected' : '' }}">Followed Auctions</button>
      </div>

      <div class="mb-5">

        @include('partials.profile_your_auctions')
        @include('partials.profile_bidded_auctions')
        @include('partials.profile_followed_auctions')
      
      </div>

    </div>

    
  
    <div id="faq" class="content-section d-none">
    <h1 class="faq-title">FAQ</h1>

  <div class="faq-category">
    <h2>General Questions</h2>
    <div class="faq-item">
      <h3>1. How do online auctions work?</h3>
      <p>Online auctions allow people to buy and sell items through a bidding process hosted on our platform. 
        Users can create listings where they describe the item, upload photos, set a starting price, and specify the auction's start and end dates. Sellers also have the option to set a "Buy Now" price, which ends the auction immediately if a bidder offers that amount.
        Buyers can place bids manually, but each bid must be higher than the starting price or the current highest bid. If a bidder is outbid, they will receive a notification, allowing them to place another bid.
        To prevent last-minute "sniping," the auction deadline is extended by 30 minutes if any bids are placed within the final 15 minutes.
        Once the auction ends, the winning bidder and the seller finalize the transaction, and the item is shipped to the buyer.
      </p>
    </div>
    <div class="faq-item">
      <h3>2. Is registration required to participate?</h3>
      <p>Yes! Only registered users can participate in auctions.</p>
    </div>
    <div class="faq-item">
      <h3>3. What should I do if I encounter a problem?</h3>
      <p>You can contact our support at treasurebidsfeup@gmail.com!</p>
    </div>
    <div class="faq-item">
      <h3>4. Can I access the platform on mobile devices?</h3>
      <p>Of course you can! The website supports small and large screen devices.</p>
    </div>
  </div>

  <div class="faq-category">
    <h2>Bidding</h2>
    <div class="faq-item">
      <h3>1. How do I place a bid?</h3>
      <p>First, browse the auctions and find one that catches your eye. Once you've decided, click on the auction. Below the Starting Price/Highest Bid, you'll find a field where you can enter the amount of credits you'd like to bid. Place your bid and confirm. It's that simple!</p>
    </div>
    <div class="faq-item">
      <h3>2. I got outbid, what do I do now?</h3>
      <p>There's no problem if you get outbid. When this happens, the credits you used for your last bid are automatically returned to your account. You can choose to place a higher bid to outbid the current highest bidder, if you wish.</p>
    </div>
    <div class="faq-item">
      <h3>3. How will I know that I got outbid?</h3>
      <p>If someone outbids you, you will get a notification telling you so!</p>
    </div>
    <div class="faq-item">
      <h3>4. How do I know if I've won an auction?</h3>
      <p>You will also receive a notification when this happens. After this, the auction owner will ship the item to you.</p>
    </div>
  </div>

  <div class="faq-category">
    <h2>Selling</h2>
    <div class="faq-item">
      <h3>1. How do I list an item?</h3>
      <p>To list an item, click the "Create Auction" button located in the website's header. This will take you to a form where you can provide detailed information about your item, including its description, photos, and starting price. Once the form is complete, submit it to create your auction.</p>
    </div>
    <div class="faq-item">
      <h3>2. What happens if my item doesn't sell?</h3>
      <p>If your item doesn't sell, don't worry—you can try again! Consider adjusting the starting price, improving your photos, or writing a more captivating description to attract potential bidders. With a few tweaks, your item might catch someone's eye next time.</p>
    </div>
    <div class="faq-item">
      <h3>3. Can I edit or cancel an auction after it starts?</h3>
      <p>Yes, you can. However, only if there are no bids placed on your auction.</p>
    </div>
    <div class="faq-item">
      <h3>4. What is a reserved price, and how do I set one?</h3>
      <p>A reserved price is basically a minimum acceptable price for your auction, where no user can bid below that value. You set one of these when creating your auction, on the "Starting price" field.</p>
    </div>
  </div>
</div>

  <div id="about-us" class="content-section d-none">
    <h1>About Us</h1>
    <h2>Who are we?</h2>
    <p>Welcome to <strong>TreasureBids</strong> your ultimate online auction platform for finding and acquiring rare,luxurious and valuable items.
  Our main mission is to connect buyers and sellers from anywhere in the world who share a passion for findding hidden gems. Whether you're a collector hunting for antiques or a seller with
unique items to share, Treasure Bids is the perfect place to make it happen.</p>

    <h2>Why choose TreasureBids?</h2>
    <p>Here at TreasureBids every auction is unique and an opportunity to find something extraordinary. We strive for
      secure transactions as your security is our main priority. Every item on Treasure Bids is carefully reviewed to ensure authenticity, uniqueness, and quality.
      We are community-driven. We value our users more than anything and we built our whole platform around them. 
    </p>

    <h2>Our Vision</h2>
    <p>We envision a world where the excitement of discovering treasures is accessible to everyone. TreasureBids aims to
      become the premier online auction platform, through innovation, community and security. We want our users to be ready to embark on their next
      treasure-hunting journey with us, whether they are a bidder, seller or simply just exploring what we have to offer. 
      Dive into the world of Treasure Bids and discover the many treasures that are waiting for you!
    </p>
    <br>

    <h1>Main features</h1>
    <h2>Bidding in real time</h2>
    <p>Real-time auction updates to ensure an engaging and competitive experience.Also, the countdown timers and bid notifications to keep users informed and involved.</p>
    <h2>Secure Transactions</h2>
    <p>Advanced payment processing ensures secure transactions for buyers and sellers.</p>
    <h2>Notifications</h2>
    <p>There are notification for almost everything to keep the users up-to-date with information.</p>
    <h2>Advanced Search and Filtering</h2>
    <p>Powerful search capabilities to help users find specific items quickly. Filters for price range, category, auction status, start and end dates.</p>
    <h1> Contact Us</h1>
    <p>If you need any kind of support, don't hesitate to contact us at treasurebidsfeup@gmail.com.</p>
  </div>

  
  </div>
</div>



    </div>


    <div id="about-us" class="content-section d-none">
      <h1>About Us</h1>
      <p>Placeholder content for About Us.</p>
    </div>
    <div id="settings" class="content-section d-none">
      <h1>Settings</h1>
      <p>Placeholder content for Settings.</p>
    </div>
  </div>
</div>

@include('partials.bank_card')

<script>
  // JavaScript to handle the popup card
const overlay = document.getElementById('overlay');
const showCardBtn = document.querySelector('.popup');
const closeCard = document.getElementById('closeCard');

// Show the overlay and card when the button is clicked
showCardBtn.addEventListener('click', () => {
  overlay.style.display = 'flex';
});

// Hide the overlay and card when the close button is clicked
closeCard.addEventListener('click', () => {
  overlay.style.display = 'none';
});
</script>

<script>
  starRating()
</script>

<script>

yourAuctionsBtn = document.querySelector('.your-auctions-btn')
biddedAuctionsBtn = document.querySelector('.bidded-auctions-btn')
followedAuctionsBtn = document.querySelector('.followed-auctions-btn')

yourAuctionsDiv = document.querySelector('.created-auctions')
biddedAuctionsDiv = document.querySelector('.bidded-auctions')
followedAuctionsDiv = document.querySelector('.followed-auctions')

yourAuctionsBtn.addEventListener('click', function(){
  yourAuctionsBtn.classList.add('selected');
  biddedAuctionsBtn.classList.remove('selected');
  followedAuctionsBtn.classList.remove('selected');

  yourAuctionsDiv.classList.add('selected');
  biddedAuctionsDiv.classList.remove('selected');
  followedAuctionsDiv.classList.remove('selected');

});
biddedAuctionsBtn.addEventListener('click', function(){
  yourAuctionsBtn.classList.remove('selected');
  biddedAuctionsBtn.classList.add('selected');
  followedAuctionsBtn.classList.remove('selected');

  yourAuctionsDiv.classList.remove('selected');
  biddedAuctionsDiv.classList.add('selected');
  followedAuctionsDiv.classList.remove('selected');
});
followedAuctionsBtn.addEventListener('click', function(){
  yourAuctionsBtn.classList.remove('selected');
  biddedAuctionsBtn.classList.remove('selected');
  followedAuctionsBtn.classList.add('selected');

  yourAuctionsDiv.classList.remove('selected');
  biddedAuctionsDiv.classList.remove('selected');
  followedAuctionsDiv.classList.add('selected');
});

</script>

@endsection