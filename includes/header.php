<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "includes/config.php";

// Fetch all categories for menu
$cats = $conn->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="CSS/header.css?v=<?= time(); ?>_5">

<header>
    <!-- Top Bar -->
    <div class="top-bar bg-dark py-1 text-white-50">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="small">
                <i class="bi bi-envelope-fill me-1"></i> support@xlfashion.com
                <span class="mx-2">|</span>
                <i class="bi bi-telephone-fill me-1"></i> +1 234 567 890
            </div>
            <div class="small d-none d-md-block">
                Free Shipping on Orders Over $50!
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
            <div class="navbar-inner">

                <!-- 1. LEFT: Brand & Desktop Menu -->
                <div class="nav-left">
                    <!-- Hamburger Menu (Mobile Only) -->
                    <button class="navbar-toggler d-lg-none p-0 border-0 me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                        <i class="bi bi-list text-white fs-1"></i>
                    </button>

                    <!-- Brand -->
                    <a class="navbar-brand" href="index.php">
                        <div class="brand-icon">
                            <i class="bi bi-bag-heart-fill"></i>
                        </div>
                        <span class="brand-text">XL<span class="text-highlight">Fashion</span></span>
                    </a>
                </div>

                <!-- 2. CENTER: Search Bar -->
                <div class="nav-center d-none d-lg-block">
                    <form action="index.php" method="GET" class="search-form">
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search for clothes, bags, accessories..." 
                               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button type="submit" class="search-btn">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>

                <!-- 3. RIGHT: Icons & Actions -->
                <div class="nav-right">

                    <!-- Desktop Links (Moved here) -->
                    <div class="desktop-menu d-none d-lg-flex me-3">
                         <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">Products</a>
                            <ul class="dropdown-menu shadow-lg border-0 mega-menu p-3">
                                <div class="row g-2">
                                    <?php foreach ($cats as $c): ?>
                                        <div class="col-md-4">
                                            <li><a class="dropdown-item py-2 px-3 rounded-2" href="category.php?cat=<?= htmlspecialchars($c['slug']) ?>"><?= $c['name'] ?></a></li>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Search Icon (Mobile Only) -->
                    <a href="#mobileSearch" class="nav-icon-link d-lg-none" data-bs-toggle="collapse">
                        <i class="bi bi-search"></i>
                        <span>Search</span>
                    </a>

                    <!-- Wishlist Icon -->
                    <a href="wishlist.php" class="nav-icon-link me-2" title="Wishlist">
                        <i class="bi bi-heart"></i>
                        <span>Wishlist</span>
                    </a>

                    <!-- Cart Icon -->
                     <a href="cart.php" class="nav-icon-link" title="Cart">
                        <i class="bi bi-cart3"></i>
                        <span>Cart</span>
                     </a>

                <?php if (!empty($_SESSION['user_id'])): ?>
                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="nav-icon-link" data-bs-toggle="dropdown">
                                <i class="bi bi-person"></i>
                                <span>Profile</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2">
                                <li>
                                    <div class="px-3 py-2">
                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Member') ?></p>
                                        <small class="text-muted"><?= ucfirst($_SESSION['user_role'] ?? 'Member') ?></small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                    <li><a class="dropdown-item rounded-2" href="admin/admin_index.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item rounded-2" href="my_profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                                <li><a class="dropdown-item rounded-2" href="wishlist.php"><i class="bi bi-heart me-2"></i> My Wishlist</a></li>
                                <li><a class="dropdown-item rounded-2" href="my_orders.php"><i class="bi bi-box-seam me-2"></i> My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item rounded-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Login Modal Trigger -->
                        <a href="#" class="nav-icon-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="bi bi-person"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
             <!-- Mobile Search Bar (Collapsible) -->
             <div class="collapse w-100 mt-2 d-lg-none" id="mobileSearch">
                <form action="index.php" method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Search..." 
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button type="submit" class="search-btn">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>
</header>

<!-- MOBILE MENU (OFFCANVAS) -->
<div class="offcanvas offcanvas-start mobile-menu-drawer" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">
            <span class="text-primary-color">XL</span>Fashion
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="menu-section">
            <h6 class="section-title">Categories</h6>
            <ul class="list-unstyled">
                <?php foreach ($cats as $c): ?>
                    <li>
                        <a href="category.php?cat=<?= htmlspecialchars($c['slug']) ?>" class="menu-item">
                            <?= $c['name'] ?>
                            <i class="bi bi-chevron-right float-end"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="menu-section mt-3">
            <h6 class="section-title">Quick Links</h6>
            <ul class="list-unstyled">
                <li><a href="index.php" class="menu-item"><i class="bi bi-house me-2"></i> Home</a></li>
                <li><a href="wishlist.php" class="menu-item"><i class="bi bi-heart me-2"></i> Wishlist</a></li>
                <li><a href="cart.php" class="menu-item"><i class="bi bi-cart3 me-2"></i> My Cart</a></li>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <li><a href="my_orders.php" class="menu-item"><i class="bi bi-box-seam me-2"></i> My Orders</a></li>
                    <li><a href="logout.php" class="menu-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="#" class="menu-item" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="offcanvas"><i class="bi bi-person me-2"></i> Login / Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- BOTTOM NAVIGATION (MOBILE ONLY) -->
<div class="mobile-bottom-nav d-lg-none">
    <div class="d-flex justify-content-around align-items-center h-100">
        <a href="index.php" class="bottom-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
            <i class="bi bi-house"></i>
            <span>Home</span>
        </a>
        <a href="#" class="bottom-nav-link" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
            <i class="bi bi-grid"></i>
            <span>Menu</span>
        </a>
        <a href="cart.php" class="bottom-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'cart.php') ? 'active' : '' ?>">
            <div class="nav-icon-wrapper">
                <i class="bi bi-cart3"></i>
            </div>
            <span>Cart</span>
        </a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="my_profile.php" class="bottom-nav-link <?= (basename($_SERVER['PHP_SELF']) == 'my_profile.php') ? 'active' : '' ?>">
                <i class="bi bi-person"></i>
                <span>Profile</span>
            </a>
        <?php else: ?>
            <a href="#" class="bottom-nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="bi bi-person"></i>
                <span>Login</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="loginModalTitle">Login to Your Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="loginAlert" class="alert alert-danger d-none"></div>

                <!-- 1. LOGIN FORM -->
                <div id="loginView">
                    <form id="ajaxLoginForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="example@email.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="******" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <a href="javascript:void(0)" onclick="showLoginSubView('forgotEmailView')" class="small text-secondary text-decoration-none">Forgot password?</a>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2" style="background: var(--primary-color); border: none;">Login Now</button>
                        </div>
                    </form>
                </div>

                <!-- 2. LOGIN OTP (FOR UNVERIFIED ACCOUNTS) -->
                <div id="loginOtpView" class="d-none text-center">
                    <div class="verify-icon mb-3" style="font-size: 3rem; color: var(--primary-color);">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h6 class="fw-bold">Verify Your Email</h6>
                    <p class="small text-muted">Enter the 6-digit code sent to your email.</p>
                    <form id="loginVerifyOtpForm">
                        <div class="mb-4">
                            <input type="text" name="otp" class="form-control text-center fw-bold fs-4 py-3 rounded-4" 
                                   placeholder="000000" maxlength="6" pattern="[0-9]{6}" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2">Verify & Login</button>
                            <button type="button" onclick="showLoginSubView('loginView')" class="btn btn-link btn-sm text-decoration-none text-muted">Back to Login</button>
                        </div>
                    </form>
                </div>

                <!-- 3. FORGOT PASSWORD - EMAIL -->
                <div id="forgotEmailView" class="d-none">
                    <p class="small text-muted mb-4">Enter your registered email to receive a password reset OTP.</p>
                    <form id="forgotPassEmailForm">
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="your@email.com" required>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2" style="background: var(--primary-color); border: none;">Send OTP</button>
                            <button type="button" onclick="showLoginSubView('loginView')" class="btn btn-link btn-sm text-decoration-none text-muted">Back to Login</button>
                        </div>
                    </form>
                </div>

                <!-- 4. FORGOT PASSWORD - OTP -->
                <div id="forgotOtpView" class="d-none text-center">
                    <div class="verify-icon mb-3" style="font-size: 3rem; color: var(--primary-color);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h6>Enter Reset Code</h6>
                    <p class="small text-muted">Enter the 6-digit code sent for password reset.</p>
                    <form id="forgotPassOtpForm">
                        <div class="mb-4">
                            <input type="text" name="otp" class="form-control text-center fw-bold fs-4 py-3 rounded-4" 
                                   placeholder="000000" maxlength="6" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2">Verify OTP</button>
                            <button type="button" onclick="showLoginSubView('forgotEmailView')" class="btn btn-link btn-sm text-decoration-none text-muted">Resend OTP</button>
                        </div>
                    </form>
                </div>

                <!-- 5. FORGOT PASSWORD - RESET -->
                <div id="forgotResetView" class="d-none">
                    <p class="small text-muted mb-4">Set a strong new password for your account.</p>
                    <form id="forgotPassResetForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">New Password</label>
                            <input type="password" name="new_password" id="resetPassword" class="form-control rounded-pill" placeholder="******" required minlength="8">
                            
                            <!-- Password Requirements Checklist (Reset) -->
                            <div class="password-checklist mt-2 p-2 bg-light rounded-3 small">
                                <div id="reset-min8" class="text-danger"><i class="bi bi-x-circle me-1"></i> Min 8 characters</div>
                                <div id="reset-upper" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one uppercase</div>
                                <div id="reset-lower" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one lowercase</div>
                                <div id="reset-number" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one number</div>
                                <div id="reset-special" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one special character</div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control rounded-pill" placeholder="******" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" id="resetPassBtn" class="btn btn-dark rounded-pill fw-bold py-2" disabled>Reset Password</button>
                        </div>
                    </form>
                </div>

            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <p class="small text-muted mb-0">Don't have an account? <a href="javascript:void(0)" class="text-decoration-none fw-bold" style="color: var(--primary-color);" data-bs-toggle="modal" data-bs-target="#registerModal">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<!-- REGISTER MODAL -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="registerModalTitle">Create Your Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="registerAlert" class="alert alert-danger d-none"></div>
                <div id="registerSuccess" class="alert alert-success d-none"></div>

                <!-- 1. REGISTRATION FORM -->
                <div id="registerView">
                    <form id="ajaxRegisterForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 ps-0" placeholder="John Doe" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="example@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-phone text-muted"></i></span>
                                <input type="tel" name="mobile" class="form-control border-start-0 ps-0" pattern="[0-9]{10}" maxlength="10" placeholder="10-digit number" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Address Details</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="text" name="door_no" class="form-control" placeholder="Door No" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="pincode" class="form-control" pattern="[0-9]{6}" maxlength="6" placeholder="Pincode" required>
                                </div>
                                <div class="col-12">
                                    <input type="text" name="street_name" class="form-control" placeholder="Street Name" required>
                                </div>
                                <div class="col-12">
                                    <input type="text" name="city" class="form-control" placeholder="City" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Password</label>
                            <input type="password" name="password" id="registerPassword" class="form-control rounded-pill" placeholder="******" required minlength="8">
                            
                            <!-- Password Requirements Checklist -->
                            <div class="password-checklist mt-2 p-2 bg-light rounded-3 small">
                                <div id="reg-min8" class="text-danger"><i class="bi bi-x-circle me-1"></i> Min 8 characters</div>
                                <div id="reg-upper" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one uppercase</div>
                                <div id="reg-lower" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one lowercase</div>
                                <div id="reg-number" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one number</div>
                                <div id="reg-special" class="text-danger"><i class="bi bi-x-circle me-1"></i> At least one special character</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control rounded-pill" placeholder="******" required>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2" style="background: var(--primary-color); border: none;">Sign Up</button>
                        </div>
                    </form>
                </div>

                <!-- 2. REGISTRATION OTP -->
                <div id="registerOtpView" class="d-none text-center">
                    <div class="verify-icon mb-3" style="font-size: 3rem; color: var(--primary-color);">
                        <i class="bi bi-envelope-check"></i>
                    </div>
                    <h6 class="fw-bold">Check Your Email</h6>
                    <p class="small text-muted">We sent a 6-digit verification code to your email.</p>
                    <form id="registerVerifyOtpForm">
                        <div class="mb-4">
                            <input type="text" name="otp" class="form-control text-center fw-bold fs-4 py-3 rounded-4" 
                                   placeholder="000000" maxlength="6" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2">Verify Account</button>
                            <button type="button" onclick="showRegisterSubView('registerView')" class="btn btn-link btn-sm text-decoration-none text-muted">Back to Sign Up</button>
                        </div>
                    </form>
                </div>

            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <p class="small text-muted mb-0">Already have an account? <a href="javascript:void(0)" class="text-decoration-none fw-bold" style="color: var(--primary-color);" data-bs-toggle="modal" data-bs-target="#loginModal">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<script>
let isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
let pendingAction = null;

function showLoginModal(action) {
    pendingAction = action;
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    showLoginSubView('loginView'); // Reset to login view
    loginModal.show();
}

function showLoginSubView(viewId) {
    const views = ['loginView', 'loginOtpView', 'forgotEmailView', 'forgotOtpView', 'forgotResetView'];
    const titles = {
        'loginView': 'Login to Your Account',
        'loginOtpView': 'Verify OTP',
        'forgotEmailView': 'Forgot Password',
        'forgotOtpView': 'Verify Code',
        'forgotResetView': 'Reset Password'
    };
    
    views.forEach(v => {
        const el = document.getElementById(v);
        if (el) el.classList.add('d-none');
    });
    
    const target = document.getElementById(viewId);
    if (target) target.classList.remove('d-none');
    
    document.getElementById('loginModalTitle').textContent = titles[viewId] || 'Login';
    document.getElementById('loginAlert').classList.add('d-none');
}

function showRegisterSubView(viewId) {
    const views = ['registerView', 'registerOtpView'];
    const titles = {
        'registerView': 'Create Your Account',
        'registerOtpView': 'Verify Your Email'
    };
    
    views.forEach(v => {
        const el = document.getElementById(v);
        if (el) el.classList.add('d-none');
    });
    
    const target = document.getElementById(viewId);
    if (target) target.classList.remove('d-none');
    
    document.getElementById('registerModalTitle').textContent = titles[viewId] || 'Register';
    document.getElementById('registerAlert').classList.add('d-none');
}

function toggleWishlist(event, productId, element) {
    if (event) event.stopPropagation();
    
    if (!isLoggedIn) {
        showLoginModal(() => toggleWishlist(null, productId, element));
        return;
    }

    const btn = element;
    fetch('ajax_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            btn.classList.toggle('active', data.action === 'added');
        } else {
            alert(data.message);
        }
    })
    .catch(() => alert('Something went wrong.'));
}

document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. AJAX LOGIN ---
    const loginForm = document.getElementById('ajaxLoginForm');
    if(loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('loginAlert');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
            alertBox.classList.add('d-none');

            fetch('ajax_login.php', { method: 'POST', body: new FormData(this) })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    isLoggedIn = true;
                    if (pendingAction) {
                        bootstrap.Modal.getInstance(document.getElementById('loginModal')).hide();
                        pendingAction();
                        pendingAction = null;
                    } else {
                        window.location.reload(); 
                    }
                } else if (data.status === 'unverified') {
                    showLoginSubView('loginOtpView');
                    alertBox.textContent = data.message;
                    alertBox.className = 'alert alert-info py-2 small';
                    alertBox.classList.remove('d-none');
                } else {
                    alertBox.textContent = data.message;
                    alertBox.className = 'alert alert-danger py-2 small';
                    alertBox.classList.remove('d-none');
                }
            })
            .finally(() => { btn.disabled = false; btn.innerHTML = originalText; });
        });
    }

    // --- 2. LOGIN OTP VERIFY (UNVERIFIED USERS) ---
    const loginOtpForm = document.getElementById('loginVerifyOtpForm');
    if (loginOtpForm) {
        loginOtpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('loginAlert');
            btn.disabled = true;

            const formData = new FormData(this);
            formData.append('auto_login', '1');

            fetch('ajax_verify_otp.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    alertBox.className = 'alert alert-success py-2';
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    btn.disabled = false;
                }
            });
        });
    }

    // --- 3. AJAX REGISTER ---
    const registerForm = document.getElementById('ajaxRegisterForm');
    const registerPassInput = document.getElementById('registerPassword');
    const registerSubmitBtn = registerForm ? registerForm.querySelector('button[type="submit"]') : null;

    if(registerPassInput) {
        registerPassInput.addEventListener('input', function() {
            const val = this.value;
            const requirements = {
                'reg-min8': val.length >= 8,
                'reg-upper': /[A-Z]/.test(val),
                'reg-lower': /[a-z]/.test(val),
                'reg-number': /[0-9]/.test(val),
                'reg-special': /[^A-Za-z0-9]/.test(val)
            };
            
            let allPassed = true;
            for(const [id, passed] of Object.entries(requirements)) {
                const el = document.getElementById(id);
                if(passed) {
                    el.classList.remove('text-danger');
                    el.classList.add('text-success');
                    el.querySelector('i').className = 'bi bi-check-circle-fill me-1';
                } else {
                    el.classList.remove('text-success');
                    el.classList.add('text-danger');
                    el.querySelector('i').className = 'bi bi-x-circle me-1';
                    allPassed = false;
                }
            }
            if(registerSubmitBtn) registerSubmitBtn.disabled = !allPassed;
        });
    }

    if(registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('registerAlert');
            const successBox = document.getElementById('registerSuccess');
            btn.disabled = true;

            fetch('ajax_register.php', { method: 'POST', body: new FormData(this) })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    showRegisterSubView('registerOtpView');
                    successBox.textContent = data.message;
                    successBox.classList.remove('d-none');
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    btn.disabled = false;
                }
            });
        });
    }

    // --- 4. REGISTER OTP VERIFY ---
    const regOtpForm = document.getElementById('registerVerifyOtpForm');
    if (regOtpForm) {
        regOtpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('registerAlert');
            btn.disabled = true;

            const formData = new FormData(this);
            formData.append('auto_login', '0');

            fetch('ajax_verify_otp.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('registerModal')).hide();
                    showLoginModal();
                    document.getElementById('loginAlert').textContent = "Verification successful! Please login.";
                    document.getElementById('loginAlert').className = "alert alert-success py-2 small";
                    document.getElementById('loginAlert').classList.remove('d-none');
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    btn.disabled = false;
                }
            });
        });
    }

    // --- 5. FORGOT PASS - EMAIL SUBMIT ---
    const forgotEmailForm = document.getElementById('forgotPassEmailForm');
    if (forgotEmailForm) {
        forgotEmailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('loginAlert');
            btn.disabled = true;
            
            const formData = new FormData(this);
            formData.append('action', 'send_otp');

            fetch('ajax_forgot_password.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    showLoginSubView('forgotOtpView');
                    alertBox.textContent = data.message;
                    alertBox.className = 'alert alert-info py-2 small';
                    alertBox.classList.remove('d-none');
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    btn.disabled = false;
                }
            });
        });
    }

    // --- 6. FORGOT PASS - OTP VERIFY ---
    const forgotOtpForm = document.getElementById('forgotPassOtpForm');
    if (forgotOtpForm) {
        forgotOtpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('loginAlert');
            btn.disabled = true;
            
            const formData = new FormData(this);
            formData.append('action', 'verify_otp');

            fetch('ajax_forgot_password.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    showLoginSubView('forgotResetView');
                    alertBox.textContent = data.message;
                    alertBox.className = 'alert alert-success py-2 small';
                    alertBox.classList.remove('d-none');
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    btn.disabled = false;
                }
            });
        });
    }

    // --- 7. FORGOT PASS - RESET SUBMIT ---
    const forgotResetForm = document.getElementById('forgotPassResetForm');
    const resetPassInput = document.getElementById('resetPassword');
    const resetPassBtn = document.getElementById('resetPassBtn');

    if(resetPassInput) {
        resetPassInput.addEventListener('input', function() {
            const val = this.value;
            const requirements = {
                'reset-min8': val.length >= 8,
                'reset-upper': /[A-Z]/.test(val),
                'reset-lower': /[a-z]/.test(val),
                'reset-number': /[0-9]/.test(val),
                'reset-special': /[^A-Za-z0-9]/.test(val)
            };
            
            let allPassed = true;
            for(const [id, passed] of Object.entries(requirements)) {
                const el = document.getElementById(id);
                if(passed) {
                    el.classList.remove('text-danger');
                    el.classList.add('text-success');
                    el.querySelector('i').className = 'bi bi-check-circle-fill me-1';
                } else {
                    el.classList.remove('text-success');
                    el.classList.add('text-danger');
                    el.querySelector('i').className = 'bi bi-x-circle me-1';
                    allPassed = false;
                }
            }
            if(resetPassBtn) resetPassBtn.disabled = !allPassed;
        });
    }

    if (forgotResetForm) {
        forgotResetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const alertBox = document.getElementById('loginAlert');
            btn.disabled = true;
            
            const formData = new FormData(this);
            formData.append('action', 'reset_password');

            fetch('ajax_forgot_password.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    showLoginSubView('loginView');
                    alertBox.textContent = data.message;
                    alertBox.className = 'alert alert-success py-2';
                    alertBox.classList.remove('d-none');
                } else {
                    alertBox.textContent = data.message;
                    alertBox.classList.remove('d-none');
                    btn.disabled = false;
                }
            });
        });
    }
});
</script>

