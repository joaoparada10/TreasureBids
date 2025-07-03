<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home
Route::redirect('/', '/home');

// If logged out /profile -> /login
Route::get('/profile/{username}', [ProfileController::class, 'showAuctionsProfile'])->name('profile');

Route::get('/profile', function () {
    if (Auth::check()) {
        return redirect()->route('profile', ['username' => Auth::user()->username]);
    }
    return redirect()->route('login');
})->name('profile.redirect');

// Authentication
Route::get('/test-auth', function () {
    $admin = Auth::guard('admin')->user();
    return response()->json(['admin' => $admin]);
});
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});
Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// Auction getters
Route::get('/home', [AuctionController::class, 'home'])->name('auctions.home');
Route::get('/auctions', [AuctionController::class, 'index'])->name('auctions.index');
Route::get('/auctions/{id}', [AuctionController::class, 'show'])->name('auction.show');

// Auction create
Route::middleware(['auth', 'blocked'])->group(function () {
    Route::get('/auction/create', [AuctionController::class, 'create'])->name('auction.create');    
    Route::post('/auctions', [AuctionController::class, 'store'])->name('auctions.store');
});

// Category
Route::get('/category/{category}', [CategoryController::class, 'show'])->name('category.show');

// Auction edit/delete(admin)/cancel
Route::put('/auctions/update/{id}', [AuctionController::class, 'admin_update'])
    ->middleware('auth:admin')
    ->name('auctions.admin_update');
Route::delete('/auctions/delete/{id}', [AuctionController::class, 'destroy'])
    ->middleware('auth:admin')
    ->name('auctions.delete');
Route::middleware(['auth', 'blocked'])->group(function () {
    Route::delete('/auctions/{auction}/cancel', [AuctionController::class, 'cancel'])->name('auctions.cancel');
});

// Auction edit/follow
Route::middleware(['auth', 'blocked'])->group(function () {
    Route::get('/auctions/{id}/edit', [AuctionController::class, 'edit'])->name('auctions.edit');
    Route::put('/auctions/{id}', [AuctionController::class, 'update'])->name('auctions.update');
    Route::post('/auctions/{id}/follow', [AuctionController::class, 'toggleFollowStatus']);
});

// Bids post
Route::middleware(['auth', 'blocked'])->post('/auction/{auction}/bid', [BidController::class, 'store'])->name('auction.bid');

// Profiles edit
Route::middleware(['auth', 'blocked'])->put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

// Admin pages
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->middleware('auth:admin')->name('admin.dashboard');
Route::get('/admins', [AdminController::class, 'manageAdmins'])->middleware('auth:admin')->name('admin.manageAdmins');
Route::get('/admin/auctions', [AdminController::class, 'manageAuctions'])->middleware('auth:admin')->name('admin.manageAuctions');

// Admins get/edit/delete
Route::put('/admins/{id}', [AdminController::class, 'update'])->middleware('auth:admin')->name('admins.update');
Route::delete('/admins/{id}', [AdminController::class, 'destroy'])->middleware('auth:admin')->name('admins.destroy');
Route::post('/admins/create', [AdminController::class, 'store'])->middleware('auth:admin')->name('admins.store');

// Members get/edit/delete
Route::get('/members', [AdminController::class, 'manageMembers'])->middleware('auth:admin')->name('admin.manageMembers');
Route::put('/members/{id}', [MemberController::class, 'update'])->middleware('auth:admin')->name('members.update');
Route::post('/members/create', [AdminController::class, 'createMember'])->middleware('auth:admin')->name('admin.createMember');
Route::delete('/members/remove/{id}', [AdminController::class, 'removeMember'])->middleware('auth:admin')->name('admin.removeMember');

// Search
Route::get('/search_results', [AuctionController::class, 'showSearchResults'])->name('search.results');

Route::get('/payment', [PaymentController::class, 'showPaymentPage'])->name('payment.page');
Route::post('/process-payment', [PaymentController::class, 'processPayment'])->name('payment.process');

// Notifications
Route::middleware(['auth', 'blocked'])->group(function () {
    Route::get('/notifications/unseen', function () {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $unseenCount = $user->notifications()->where('seen', false)->count();
        return response()->json(['unseenCount' => $unseenCount]);
    });

    Route::get('/notifications/unseen-list', function () {
        $unseenNotifications = Auth::user()->notifications()->where('seen', false)->get();
        return response()->json($unseenNotifications);
    });

    Route::post('/notifications/mark-as-seen', function (Illuminate\Http\Request $request) {
        $notificationId = $request->input('id');
        $notification = Auth::user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->update(['seen' => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    });
});

// Ratings
Route::middleware(['auth', 'blocked'])->post('/auctions/{auction}/rate', [RatingController::class, 'store'])->name('ratings.store');

// Transactions
Route::middleware(['auth', 'blocked'])->group(function () {
    Route::get('/auctions/{auction}/transaction', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/auctions/{auction}/transaction', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
});

// Forgot password
Route::post('/forgot-password', [MailController::class, 'forgotPassword'])->name('forgot-password');

Route::middleware(['auth', 'blocked'])->group(function () {
    Route::delete('/account/delete', [ProfileController::class, 'deleteAccount'])->name('account.delete');
});
