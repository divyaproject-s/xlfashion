<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/admin_index.php");
    exit;
}
?>

<link rel="stylesheet" href="CSS/index.css?v=<?= time(); ?>_5">
<link rel="stylesheet" href="CSS/curated_looks.css?v=<?= time(); ?>">



<!-- ================================
      HERO CAROUSEL
================================ -->
<?php if (empty($_GET['search'])): ?>
<div class="container-fluid p-0 mb-5">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $carousel_res = $conn->query("SELECT * FROM carousel_images ORDER BY id DESC");
            if ($carousel_res->num_rows > 0) {
                $idx = 0;
                while ($row = $carousel_res->fetch_assoc()) {
                    $active_class = ($idx === 0) ? 'active' : '';
                    $img_path = $row['image_path'];
                    $img_url = $row['image_url'] ?? '';
                    $img_src = !empty($img_url) ? $img_url : (!empty($img_path) ? 'assets/carousel/' . $img_path : '');
                    ?>
                    <div class="carousel-item <?= $active_class ?>" data-bs-interval="5000">
                        <div class="hero-overlay"></div>
                        <img src="<?= $img_src ?>" class="d-block w-100 hero-img" alt="<?= htmlspecialchars($row['title']) ?>">
                        <div class="carousel-caption hero-caption text-start">
                            <?php if (!empty($row['title'])): ?>
                                <h1 class="display-3 fw-bold text-white mb-3 animate__animated animate__fadeInDown text-uppercase"><?= htmlspecialchars($row['title']) ?></h1>
                            <?php endif; ?>
                            <?php if (!empty($row['link'])): ?>
                                <a href="<?= htmlspecialchars($row['link']) ?>" class="btn btn-light btn-lg rounded-pill px-5 fw-bold animate__animated animate__fadeInUp">Shop Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    $idx++;
                }
            } else {
                echo '
                <div class="carousel-item active">
                    <div class="hero-overlay"></div>
                    <img src="assets/carousel/slider1.png" class="d-block w-100 hero-img" alt="Default Hero">
                    <div class="carousel-caption hero-caption text-start">
                        <h1 class="display-3 fw-bold text-white mb-3 animate__animated animate__fadeInDown text-uppercase">New Season Arrivals</h1>
                        <a href="category.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold animate__animated animate__fadeInUp">Shop Now</a>
                    </div>
                </div>';
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon p-4" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon p-4" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- ================================
      CATEGORIES
================================ -->
<?php if (empty($_GET['search'])): ?>
<div class="container my-5 pb-4">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5 mb-2 gradient-text">Shop by Category</h2>
        <div class="divider mx-auto"></div>
    </div>

    <div class="row g-4 justify-content-center">
        <?php
        // Fetch Categories
        $cat_res = $conn->query("SELECT * FROM categories ORDER BY id DESC");
        while ($cat = $cat_res->fetch_assoc()) {
            $cat_img = $cat["image"];
            $cat_url = $cat["image_url"] ?? '';
            $cat_src = !empty($cat_url) ? $cat_url : (!empty($cat_img) ? 'assets/category/' . $cat_img : '');
            echo '
            <div class="col-6 col-md-4 col-lg-2">
                <a href="category.php?cat=' . urlencode($cat["slug"]) . '" class="text-decoration-none text-dark">
                    <div class="cat-card text-center p-3 rounded-4 h-100">
                        <div class="img-wrapper mb-3 shadow-lg rounded-circle mx-auto" style="width: 130px; height: 130px; overflow: hidden; border: 4px solid #fff;">
                            <img src="' . htmlspecialchars($cat_src) . '" class="w-100 h-100 object-fit-cover transition-transform hover-scale" alt="' . htmlspecialchars($cat["name"]) . '">
                        </div>
                        <h6 class="fw-bold mb-0 text-uppercase letter-spacing-1">' . $cat["name"] . '</h6>
                    </div>
                </a>
            </div>';
        }
        ?>
    </div>
</div>
<?php endif; ?>

<!-- ================================
      CURATED LOOKS FOR YOU
================================ -->
<?php if (empty($_GET['search'])): ?>
<section class="curated-looks-section">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="fw-bold display-5 mb-2 gradient-text">Fashion Picks For You</h2>
            <div class="divider mx-auto"></div>
        </div>

        <div class="slider-outer-wrapper">
            <button class="slider-arrow prev" id="prevLook" title="Previous">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="slider-arrow next" id="nextLook" title="Next">
                <i class="bi bi-chevron-right"></i>
            </button>

            <div class="looks-slider-container">
                <?php
                // Fetch Active Looks
                $curated_result = $conn->query("SELECT * FROM curated_looks WHERE status = 'active' ORDER BY display_order ASC, id DESC");
                if ($curated_result->num_rows > 0):
                    while ($row = $curated_result->fetch_assoc()):
                        $img_path = $row['image_path'];
                        $img_url = $row['image_url'] ?? '';
                        $img_src = !empty($img_url) ? $img_url : (!empty($img_path) ? 'assets/curated/' . $img_path : '');
                        ?>
                        <div class="look-card" onclick="window.location.href='<?= htmlspecialchars($row['link']) ?>'">
                            <div class="look-img-wrapper">
                                <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                            </div>
                            <div class="look-info p-3 text-center">
                                <h5 class="fw-bold mb-0"><?= htmlspecialchars($row['title']) ?></h5>
                            </div>
                            <div class="shop-all-overlay">
                                <i class="bi bi-bag"></i> Shop All
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center w-100 py-5 text-muted">No looks available at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================================
      LATEST PRODUCTS
================================ -->
<?php 
$is_search_or_cat = !empty($_GET['search']) || !empty($_GET['category']);
?>
<div class="container pb-5 <?= $is_search_or_cat ? 'pt-md-5 mt-4' : '' ?>" id="collection">
    <div class="text-center mb-5 <?= $is_search_or_cat ? 'mt-md-2' : '' ?>">
        <?php if (!empty($_GET['search'])): ?>
            <h2 class="fw-bold display-5 mb-3 gradient-text border-bottom pb-3 px-2 text-wrap">Search Results for "<?= htmlspecialchars($_GET['search']) ?>"</h2>
        <?php else: ?>
            <h2 class="fw-bold display-5 mb-2 gradient-text">Featured Collection</h2>
        <?php endif; ?>
        <div class="divider mx-auto"></div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar p-4 mb-5 rounded-4 shadow-sm bg-white border">
        <form method="GET" action="index.php#collection">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold text-uppercase text-secondary">Category</label>
                    <div class="select-wrapper">
                        <select name="category" id="categorySelect" class="form-select border-0 bg-light">
                            <option value="">All Categories</option>
                            <?php
                            $cat_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
                            while ($c = $cat_res->fetch_assoc()) {
                                $selected = (isset($_GET['category']) && $_GET['category'] == $c['slug']) ? 'selected' : '';
                                echo "<option value='{$c['slug']}' $selected>{$c['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label small fw-bold text-uppercase text-secondary">Fabric</label>
                    <div class="select-wrapper">
                        <select name="fabric" class="form-select border-0 bg-light">
                            <option value="">All Fabrics</option>
                            <?php
                            $fab_res = $conn->query("SELECT DISTINCT fabric FROM products WHERE fabric IS NOT NULL AND fabric != '' ORDER BY fabric ASC");
                            while ($f = $fab_res->fetch_assoc()) {
                                $selected = (isset($_GET['fabric']) && $_GET['fabric'] == $f['fabric']) ? 'selected' : '';
                                echo "<option value='{$f['fabric']}' $selected>{$f['fabric']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <?php
                $current_cat = isset($_GET['category']) ? strtolower($_GET['category']) : '';
                $hide_size_cats = ['sarees', 'saree', 'bags', 'bag'];
                $hide_size = in_array($current_cat, $hide_size_cats);
                ?>
                <div class="col-md-2 col-6" id="sizeFilterCol" style="display: <?= $hide_size ? 'none' : 'block' ?>;">
                    <label class="form-label small fw-bold text-uppercase text-secondary">Size</label>
                    <div class="select-wrapper">
                        <select name="size" id="sizeSelect" class="form-select border-0 bg-light">
                            <option value="">Any Size</option>
                            <?php
                            $sizes = ['S', 'M', 'L', 'XL', 'XXL', 'Free Size'];
                            foreach ($sizes as $sz) {
                                $selected = (isset($_GET['size']) && $_GET['size'] == $sz) ? 'selected' : '';
                                echo "<option value='$sz' $selected>$sz</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <script>
                document.getElementById('categorySelect').addEventListener('change', function() {
                    const sizeCol = document.getElementById('sizeFilterCol');
                    const sizeSelect = document.getElementById('sizeSelect');
                    const val = this.value.toLowerCase();
                    const hideSizeCats = ['sarees', 'saree', 'bags', 'bag'];
                    
                    if (hideSizeCats.includes(val)) {
                        sizeCol.style.display = 'none';
                        sizeSelect.value = ''; // Reset to Any Size
                    } else {
                        sizeCol.style.display = 'block';
                    }
                });
                </script>

                <div class="col-md-3 col-6">
                    <label class="form-label small fw-bold text-uppercase text-secondary">Price (SGD)</label>
                    <div class="d-flex gap-2">
                        <input type="number" name="min_price" class="form-control border-0 bg-light" placeholder="Min"
                            min="0"
                            value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                        <input type="number" name="max_price" class="form-control border-0 bg-light" placeholder="Max"
                            min="0"
                            value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                    </div>
                </div>

                <div class="col-md-2 col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-bold">Filter</button>
                    <a href="index.php#collection" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- ================================
     OFFERS SECTION LOGIC
================================ -->
<?php
// 1. Create Offers Table if not exists (Self-setup)
$conn->query("CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_text VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    background_color VARCHAR(50) DEFAULT '#ff1493',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 2. Seed Data if empty
$chk_off = $conn->query("SELECT id FROM offers LIMIT 1");
if ($chk_off->num_rows == 0) {
    $conn->query("INSERT INTO offers (title, description, discount_text, status, background_color) VALUES 
    ('Flash Sale', 'Get amazing discounts on our latest arrival of Kurtas and Sarees.', 'Flat 20% OFF', 'active', '#ff1493'),
    ('Weekend Bonanza', 'Buy 2 Get 1 Free on all Accessories.', 'Limited Time', 'active', '#ffd700')");
}

// 3. Fetch Active Offers
$offers_res = $conn->query("SELECT * FROM offers WHERE status = 'active' ORDER BY id DESC");
?>

<!-- ================================
     OFFERS SECTION DISPLAY
================================ -->
<?php if ($offers_res->num_rows > 0 && empty($_GET['search']) && empty($_GET['category'])): ?>
    <div class="container my-4">
        <div class="row g-3">
            <?php while ($offer = $offers_res->fetch_assoc()): 
                // Dynamic styling based on offered background color
                $bg_color = $offer['background_color'];
                $text_color = ($bg_color == '#ffd700') ? '#000' : '#fff'; 
            ?>
                <div class="col-md-6">
                    <div class="offer-card p-4 rounded-4 d-flex justify-content-between align-items-center position-relative overflow-hidden" 
                         style="background: <?= $bg_color ?>; color: <?= $text_color ?>; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                        
                        <!-- Decorative Circle -->
                        <div class="offer-circle"></div>

                        <div class="position-relative z-1">
                            <span class="badge bg-white text-dark mb-2 animate__animated animate__fadeIn"><?= htmlspecialchars($offer['discount_text']) ?></span>
                            <h3 class="fw-bold mb-1 animate__animated animate__fadeInUp"><?= htmlspecialchars($offer['title']) ?></h3>
                            <p class="mb-0 opacity-75 small animate__animated animate__fadeInUp"><?= htmlspecialchars($offer['description']) ?></p>
                        </div>
                        <div class="z-1 ms-3">
                            <?php if (!empty($offer['link_url'])): ?>
                                <a href="<?= htmlspecialchars($offer['link_url']) ?>" class="btn btn-light rounded-pill fw-bold text-uppercase" style="color: <?= $bg_color ?>">Shop</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Products Grid -->
    <div class="row g-4" id="product-list">
        <?php
        $sql = "SELECT p.*, 
                       IFNULL(AVG(r.rating), 0) as avg_rating, 
                       COUNT(r.id) as review_count 
                FROM products p 
                LEFT JOIN product_reviews r ON p.id = r.product_id";
        $params = [];
        $types = "";
        $where = [];

        if (!empty($_GET['category'])) {
            $sql .= " JOIN categories c ON p.category_id = c.id";
            $where[] = "c.slug = ?";
            $params[] = $_GET['category'];
            $types .= "s";
        }
        if (!empty($_GET['fabric'])) {
            $where[] = "p.fabric = ?";
            $params[] = $_GET['fabric'];
            $types .= "s";
        }
        if (!empty($_GET['min_price'])) {
            $where[] = "p.sgd_price >= ?";
            $params[] = $_GET['min_price'];
            $types .= "d";
        }
        if (!empty($_GET['max_price'])) {
            $where[] = "p.sgd_price <= ?";
            $params[] = $_GET['max_price'];
            $types .= "d";
        }
        if (!empty($_GET['size'])) {
            $sz = $_GET['size'];
            $where[] = "(p.sizes LIKE CONCAT('%', ?, '%') OR JSON_UNQUOTE(JSON_EXTRACT(p.size_stock, CONCAT('$.\"', ?, '\"'))) IS NOT NULL)";
            $params[] = $sz;
            $params[] = $sz;
            $types .= "ss";
        }
        
        if (!empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY p.id ORDER BY p.id DESC LIMIT 12";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $u_price = floatval($row['usual_price']);
                $s_price = floatval($row['sgd_price']);
                $usual_price_html = ($u_price > $s_price) ? '<small class="text-muted text-decoration-line-through me-2">SGD ' . number_format($u_price, 2) . '</small>' : '';
                
                // Rating Stars Calculation
                $avg_r = round(floatval($row['avg_rating']), 1);
                $rev_c = intval($row['review_count']);
                $stars_html = '';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= floor($avg_r)) {
                        $stars_html .= '<i class="bi bi-star-fill text-warning"></i>';
                    } elseif ($i - 0.5 <= $avg_r) {
                        $stars_html .= '<i class="bi bi-star-half text-warning"></i>';
                    } else {
                        $stars_html .= '<i class="bi bi-star text-warning"></i>';
                    }
                }

                // Calculate if product is "New" (within 3 days)
                $created_date = strtotime($row['created_at']);
                $three_days_ago = strtotime('-2 days');
                $is_new = ($created_date >= $three_days_ago);
                $new_badge_html = $is_new ? '<span class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-1 rounded-pill z-2">New</span>' : '';

                // Fetch user wishlist if logged in - Optimized to do once
                static $user_wishlist = null;
                if ($user_wishlist === null) {
                    $user_wishlist = [];
                    if (isset($_SESSION['user_id'])) {
                        $uid = $_SESSION['user_id'];
                        $wish_res = $conn->query("SELECT product_id FROM wishlist WHERE user_id = $uid");
                        while ($w = $wish_res->fetch_assoc()) {
                            $user_wishlist[] = $w['product_id'];
                        }
                    }
                }

                $prod_img = $row['image'];
                $prod_url = $row['image_url'] ?? '';
                $prod_src = !empty($prod_url) ? $prod_url : (!empty($prod_img) ? 'assets/images/' . $prod_img : '');

                $is_wishlisted = in_array($row['id'], $user_wishlist) ? 'active' : '';
                $has_sizes_bool = !empty($row['sizes']);
                // Buy Now → Checkout (direct purchase) or Product page (if sizes needed)
                $buy_url = $has_sizes_bool ? "product.php?id=" . $row['id'] : "checkout.php?buy_now=" . $row['id'] . "&size=N/A";
                // Add to Cart → Add item and go to cart page
                $cart_url = $has_sizes_bool ? "product.php?id=" . $row['id'] : "cart.php?action=add&id=" . $row['id'] . "&size=N/A";

                echo '
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card product-card h-100 border-0 shadow-sm" onclick="window.location.href=\'product.php?id=' . $row['id'] . '\'">
                        <div class="position-relative overflow-hidden product-img-wrapper">
                            ' . $new_badge_html . '
                            <img src="' . htmlspecialchars($prod_src) . '" class="card-img-top" alt="' . htmlspecialchars($row['name']) . '">
                            <button class="wishlist-btn ' . $is_wishlisted . '" onclick="toggleWishlist(event, ' . $row['id'] . ', this)" title="Add to Wishlist">
                                <i class="bi bi-heart"></i>
                            </button>
                        </div>
                        <div class="card-body p-3 d-flex flex-column">
                            <h6 class="card-title fw-bold text-truncate mb-1">' . $row['name'] . '</h6>
                            <div class="price-block mb-3">
                                ' . $usual_price_html . '
                                <span class="text-danger fw-bold fs-5">SGD ' . number_format($s_price, 2) . '</span>
                            </div>
                            
                            <div class="mt-auto d-grid gap-2">
                                <button onclick="handleAction(event, \'' . $buy_url . '\')" class="btn btn-dark btn-sm rounded-pill fw-bold">Buy Now</button>
                                <button onclick="handleAction(event, \'' . $cart_url . '\')" class="btn btn-outline-dark btn-sm rounded-pill">Add to Cart</button>
                            </div>

                            <!-- Rating Section (Meesho Style) -->
                            <div class="rating-display mt-3 d-flex align-items-center justify-content-center gap-2">
                                <span class="fw-bold text-dark small">' . ($rev_c > 0 ? $avg_r : '0.0') . '</span>
                                <div class="stars">' . $stars_html . '</div>
                                <span class="text-muted small">(' . $rev_c . ')</span>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo "
            <div class='col-12 text-center py-5'>
                <div class='py-5 bg-light rounded-4'>
                    <i class='bi bi-search display-1 text-muted mb-3'></i>
                    <h4 class='text-secondary'>No products found.</h4>
                    <p class='text-muted'>Try adjusting your filters.</p>
                    <a href='index.php#collection' class='btn btn-dark mt-2'>Clear All Filters</a>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

<script>
function handleAction(event, url) {
    event.stopPropagation();
    if (!isLoggedIn) {
        showLoginModal(() => {
            window.location.href = url;
        });
        return;
    }
    window.location.href = url;
}
</script>

<script src="JS/curated_looks.js"></script>

<?php include 'includes/footer.php'; ?>