<div class="sidebar">
    <h3>Admin Panel</h3>

    <a href="add_product.php">➕ Add Product</a>
    <a href="manage_products.php">📦 Manage Products</a>

    <!-- New Category System -->
    <a href="add_category.php">📁 Add Category</a>
    <a href="manage_categories.php">🗂 Manage Categories</a>
    
    <a href="carousel.php">🎠 Manage Carousel</a>

    <!-- Curated Looks Section -->
    <div class="sidebar-divider mx-3 my-2" style="border-top: 1px solid #444;"></div>
    <div class="px-3 small text-uppercase text-muted fw-bold mb-1">✨ Curated Looks</div>
    <a href="add_curated_look.php">➕ Add Curated Look</a>
    <a href="manage_curated_looks.php">📋 Manage Curated Looks</a>
    <div class="sidebar-divider mx-3 my-2" style="border-top: 1px solid #444;"></div>

    <a href="manage_offers.php">🎁 Manage Offers</a>
    <a href="manage_orders.php">🛒 Manage Orders</a>
    <a href="manage_users.php">👥 Manage Users</a>

    <a href="../index.php" class="mt-4" style="background:#0d6efd; text-align:center;">🏠 Go to Home</a>
    <a href="logout.php" class="mt-auto" style="background:#dc3545; text-align:center;">🔒 Logout</a>
</div>

<style>
    .sidebar {
        width: 240px;
        background: #1c1e21;
        color: #fff;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0;
    }

    .sidebar a {
        color: #fff;
        text-decoration: none;
        display: block;
        padding: 12px 20px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .sidebar a:hover {
        background: #343a40;
        padding-left: 25px;
    }

    .sidebar h3 {
        padding: 25px 20px;
        font-weight: bold;
        text-align: center;
        background: #15171a;
        margin-bottom: 10px;
        font-size: 1.5rem;
    }

    .sidebar a.mt-auto {
        margin-top: auto !important;
    }
</style>
