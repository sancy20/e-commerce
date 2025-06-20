<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- Frontend Controllers ---
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\VendorApplicationController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\StripeWebhookController;

// --- Admin Controllers (Aliased to avoid conflicts where necessary) ---
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeValueController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\VendorPayoutController;
use App\Http\Controllers\Admin\AdminInquiryController;

// --- Vendor Controllers ---
use App\Http\Controllers\VendorOrderController;
use App\Http\Controllers\VendorProductController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\VendorReportController;
use App\Http\Controllers\VendorUpgradeController;
use App\Http\Controllers\VendorReviewController;
use App\Http\Controllers\VendorConnectController;
use App\Http\Controllers\VendorInquiryController;

// --- Shared Notification Controller ---
use App\Http\Controllers\NotificationController;

// --- Frontend Public Routes ---

// Default home page - now points to products index
Route::get('/', [ProductController::class, 'index'])->name('home');

// Product Browse
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Shopping Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/update', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{itemIdentifier}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
});

// Review Submission (requires authentication)
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('reviews.store');

// Stripe Webhook (excluded from CSRF in VerifyCsrfToken.php)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');


// --- Authenticated Frontend Routes ---

// Checkout Process
Route::middleware(['auth'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
});

// User Dashboard (Customer Panel)
Route::get('/dashboard', function () {
    return redirect()->route('dashboard.index');
})->name('dashboard')->middleware(['auth']);

// User Dashboard Group (all specific dashboard pages for customers)
Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [UserDashboardController::class, 'index'])->name('index');
    Route::get('/orders', [UserDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [UserDashboardController::class, 'showOrder'])->name('orders.show');
    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
});

// Vendor Application Routes (for customers to apply to be vendors)
Route::middleware(['auth'])->prefix('vendor-application')->name('vendor_application.')->group(function () {
    Route::get('/apply', [VendorApplicationController::class, 'showApplicationForm'])->name('apply');
    Route::post('/submit', [VendorApplicationController::class, 'submitApplication'])->name('submit');
});

// Wishlist Routes
Route::middleware(['auth'])->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/add', [WishlistController::class, 'add'])->name('add');
    Route::delete('/remove/{wishlist}', [WishlistController::class, 'remove'])->name('remove');
    Route::post('/move-to-cart/{wishlist}', [WishlistController::class, 'moveToCart'])->name('move_to_cart');
});

// Customer Inquiry Routes
Route::middleware(['auth'])->prefix('inquiries')->name('inquiries.')->group(function () {
    Route::get('/create/{product?}', [InquiryController::class, 'create'])->name('create');
    Route::post('/store', [InquiryController::class, 'store'])->name('store');
    // Note: Admin will manage inquiries via admin.inquiries.* routes
});


// --- Notification AJAX Routes (for bell & mail icons) ---
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/unread', [NotificationController::class, 'getUnreadNotifications'])->name('unread');
    Route::post('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark_as_read');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark_all_as_read');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
});


// --- Admin Panel Routes (Protected by 'auth' and 'admin' middleware) ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard (default for /admin)
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User/Customer Management (for admin to manage vendor/admin flags, tiers)
    Route::resource('users', UserController::class)->except(['create', 'store', 'show']);

    // Admin Inquiry Management (for admin to review messages from customers)
    Route::resource('inquiries', AdminInquiryController::class)->except(['create', 'store', 'edit']);
    Route::post('inquiries/{inquiry}/mark-as-read', [AdminInquiryController::class, 'markAsRead'])->name('inquiries.mark_as_read');

    // Orders Management
    Route::resource('orders', OrderController::class)->except(['create', 'store']);

    // Products & Product Variants Management
    Route::resource('products', AdminProductController::class);
    Route::resource('products.variants', ProductVariantController::class);
    Route::get('/attributes/{attribute}/values', [ProductVariantController::class, 'getValuesByAttribute'])->name('attributes.values'); 

    // Categories Management
    Route::resource('categories', CategoryController::class);

    // Attributes & Attribute Values Management
    Route::resource('attributes', AttributeController::class);
    Route::resource('attribute-values', AttributeValueController::class);

    // Shipping Methods Management
    Route::resource('shipping-methods', ShippingMethodController::class);

    // Reviews Management
    Route::resource('reviews', AdminReviewController::class)->except(['create', 'store']);

    // Reports (grouped under /admin/reports)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales-by-date', [ReportController::class, 'salesByDate'])->name('sales-by-date');
        Route::get('/product-sales', [ReportController::class, 'productSales'])->name('product-sales');
        Route::get('/category-sales', [ReportController::class, 'categorySales'])->name('category-sales');
    });

    // Vendor Payouts Management
    Route::resource('vendor-payouts', VendorPayoutController::class)->except(['create', 'edit', 'show']);
});


// --- Vendor Panel Routes (Protected by 'auth' and 'is_vendor' middleware) ---
Route::middleware(['auth', 'is_vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard'); 

    // Vendor Product Management
    Route::resource('products', VendorProductController::class);

    // Vendor Order Management
    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [VendorOrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [VendorOrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [VendorOrderController::class, 'destroy'])->name('orders.destroy');

    // Vendor Reports
    Route::get('/reports', [VendorReportController::class, 'index'])->name('reports.index');

    // Vendor Tier Upgrade Request
    Route::prefix('upgrade-request')->name('upgrade_request.')->group(function () {
        Route::get('/', [VendorUpgradeController::class, 'showRequestForm'])->name('form');
        Route::post('/', [VendorUpgradeController::class, 'submitRequest'])->name('submit');
    });

    // Vendor Stripe Connect Onboarding
    Route::prefix('stripe-connect')->name('stripe_connect.')->group(function () {
        Route::get('/onboard', [VendorConnectController::class, 'onboard'])->name('onboard');
        Route::get('/return', [VendorConnectController::class, 'returnFromStripe'])->name('return');
        Route::get('/refresh', [VendorConnectController::class, 'refreshFromStripe'])->name('refresh');
    });

    // Vendor Reviews
    Route::get('/reviews', [VendorReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [VendorReviewController::class, 'show'])->name('reviews.show');
    Route::put('/reviews/{review}', [VendorReviewController::class, 'update'])->name('reviews.update'); 

    Route::get('/inquiries', [VendorInquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [VendorInquiryController::class, 'show'])->name('inquiries.show');
    Route::put('/inquiries/{inquiry}', [VendorInquiryController::class, 'update'])->name('inquiries.update');
    Route::post('/inquiries/{inquiry}/mark-as-read', [VendorInquiryController::class, 'markAsRead'])->name('inquiries.mark_as_read');
    Route::delete('/inquiries/{inquiry}', [VendorInquiryController::class, 'destroy'])->name('inquiries.destroy');
});

require __DIR__.'/auth.php';