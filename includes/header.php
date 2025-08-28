<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        padding-top: 80px; /* prevent content hiding */
    }

    /* Gradient Navbar */
    .navbar-gradient {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
        backdrop-filter: blur(8px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        position: fixed;
        width: 100%;
        top: 0;
        left: 0;
        z-index: 1030;
    }

    /* Brand */
    .navbar-brand {
        font-weight: 700;
        color: white !important;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .navbar-brand i {
        color: gold;
        font-size: 1.3rem;
        transition: transform 0.3s ease, color 0.3s ease;
    }
    .navbar-brand:hover i {
        transform: rotate(15deg) scale(1.2);
        color: #fff176;
    }

    /* Navbar Links */
    .navbar-gradient .nav-link {
        color: white !important;
        font-weight: 500;
        position: relative;
        padding-bottom: 5px;
        margin: 0 6px;
        transition: all 0.3s ease;
    }
    .navbar-gradient .nav-link::after {
        content: "";
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: #fff;
        transition: width 0.3s ease;
    }
    .navbar-gradient .nav-link:hover::after,
    .navbar-gradient .nav-link.active::after {
        width: 100%;
    }

    /* Gradient Button */
    .btn-gradient {
        background: linear-gradient(90deg, #ff758c, #ff7eb3);
        border: none;
        color: white !important;
        padding: 6px 16px;
        font-weight: 500;
        border-radius: 50px;
        box-shadow: 0 4px 10px rgba(255, 105, 135, 0.4);
        transition: all 0.3s ease;
    }
    .btn-gradient:hover {
        background: linear-gradient(90deg, #ff4d79, #ff0066);
        box-shadow: 0 6px 14px rgba(255, 105, 135, 0.6);
        transform: translateY(-2px);
    }

    /* Outline Gradient Button */
    .btn-gradient-outline {
        border: 2px solid #fff;
        background: transparent;
        color: white !important;
        padding: 6px 16px;
        font-weight: 500;
        border-radius: 50px;
        transition: all 0.3s ease;
    }
    .btn-gradient-outline:hover {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
        color: white !important;
        box-shadow: 0 4px 10px rgba(86, 41, 50, 0.4);
    }

    /* Fix for white toggle icon */
    .navbar-toggler {
        border: none;
    }
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' 
        xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255,255,255,1%29' 
        stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' 
        d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
    }
   
    .dropdown-menu {
        border: none;
        border-radius: 15px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        background: #fff;
        left: 0;
        right: 0;
        top: 100%;
    }

   /* Mega menu items with gradient hover */
.dropdown-item {
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.dropdown-item:hover {
    background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
    color: #fff !important;
    transform: translateX(6px);
}

/* Column headers */
.dropdown-menu h6 {
    font-size: 13px;
    letter-spacing: 1px;
    font-weight: 600;
    margin-bottom: 12px;
    color: #7c0e20;
}

</style>

<header>
    <nav class="navbar navbar-expand-lg navbar-gradient py-2">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-gem"></i> Alankara
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Center Menu -->
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                    </li>

                    <!-- Products with Mega Menu -->
                    <li class="nav-item dropdown position-static">
                        <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="bi bi-bag-fill me-1"></i> Products
                        </a>

                        <!-- Mega Menu -->
                        <!-- Mega Menu -->
<div class="dropdown-menu w-100 p-4" aria-labelledby="productsDropdown">
    <div class="row">
        <!-- Column 1 -->
        <div class="col-md-4">
            <h6 class="text-uppercase fw-bold text-danger mb-3">Women’s Accessories</h6>
            <ul class="list-unstyled">
                <li><a class="dropdown-item" href="category.php?cat=jewellery-set">💎 Jewellery Set</a></li>
                <li><a class="dropdown-item" href="category.php?cat=earrings-and-studs">✨ Earrings & Studs</a></li>
                <li><a class="dropdown-item" href="category.php?cat=bangles">🪬 Bangles</a></li>
                <li><a class="dropdown-item" href="category.php?cat=necklaces">📿 Necklaces</a></li>
                <li><a class="dropdown-item" href="category.php?cat=rings">💍 Rings</a></li>
                <li><a class="dropdown-item" href="category.php?cat=anklets">👣 Anklets</a></li>
                <li><a class="dropdown-item" href="category.php?cat=oxidised">🖤 Oxidised</a></li>
            </ul>
        </div>

        <!-- Column 2 -->
        <div class="col-md-4">
            <h6 class="text-uppercase fw-bold text-danger mb-3">Trending Collections</h6>
            <ul class="list-unstyled">
                <li><a class="dropdown-item" href="category.php?cat=bridal">👰 Bridal Jewellery</a></li>
                <li><a class="dropdown-item" href="category.php?cat=party-wear">🎉 Party Wear</a></li>
                <li><a class="dropdown-item" href="category.php?cat=casual">🌸 Daily Wear</a></li>
                <li><a class="dropdown-item" href="category.php?cat=temple">🛕 Temple Collection</a></li>
                <li><a class="dropdown-item" href="category.php?cat=office">💼 Office Wear</a></li>
            </ul>
        </div>

        <!-- Column 3: Promo Banner -->
        <div class="col-md-4 text-center">
            <div class="p-3 rounded-4 shadow-sm" 
                 style="background:linear-gradient(135deg,#7c0e20,#fc1978); color:white;">
                <h5 class="fw-bold">✨ Festive Sale ✨</h5>
                <p class="small mb-3">Up to <strong>50% OFF</strong> on selected jewellery.</p>
                <a href="sale.php" class="btn btn-light btn-sm rounded-pill px-3">Shop Now</a>
            </div>
        </div>
    </div>
</div>

                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="cart.php"><i class="bi bi-cart-fill me-1"></i> Cart</a>
                    </li>

                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my_orders.php"><i class="bi bi-box-seam me-1"></i> My Orders</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Right Side -->
                <ul class="navbar-nav ms-auto">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item d-flex align-items-center text-white me-3">
                            <i class="bi bi-person-circle me-1"></i> Welcome, 
                            <strong class="ms-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-gradient-outline btn-sm" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item me-2">
                            <a class="btn btn-gradient-outline btn-sm" href="login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-gradient btn-sm" href="register.php">
                                <i class="bi bi-pencil-square"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Mega Menu Hover Fix -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (window.innerWidth > 991) {
        document.querySelectorAll('.navbar .dropdown').forEach(function (dropdown) {
            dropdown.addEventListener('mouseenter', function () {
                const menu = this.querySelector('.dropdown-menu');
                if (menu) menu.classList.add('show');
            });
            dropdown.addEventListener('mouseleave', function () {
                const menu = this.querySelector('.dropdown-menu');
                if (menu) menu.classList.remove('show');
            });
        });
    }
});
</script>
