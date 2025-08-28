<?php
session_start();
include 'includes/config.php';

/**
 * Allowed categories (must match what's stored in your DB)
 */
$ALLOWED_CATEGORIES = [
    'Jewellery Set',
    'Earrings & Studs',
    'Bangles',
    'Necklaces',
    'Rings',
    'Anklets',
    'Oxidised',
];

/** Normalize to a slug for matching */
function cat_slug($s) {
    $s = strtolower(trim($s));
    $s = preg_replace('/&/','and',$s);          // earrings & studs -> earrings and studs
    $s = preg_replace('/[^a-z0-9]+/','-',$s);   // spaces/others -> dash
    return trim($s, '-');
}

/** Build slug -> display map */
$slugToDisplay = [];
foreach ($ALLOWED_CATEGORIES as $display) {
    $slugToDisplay[cat_slug($display)] = $display;
}

/** Read incoming cat and resolve to a valid display name */
$rawCat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$categoryDisplay = '';
if ($rawCat !== '') {
    // exact match first
    if (in_array($rawCat, $ALLOWED_CATEGORIES, true)) {
        $categoryDisplay = $rawCat;
    } else {
        // try slug match (supports ?cat=jewellery-set or ?cat=earrings-and-studs)
        $slug = cat_slug($rawCat);
        if (isset($slugToDisplay[$slug])) {
            $categoryDisplay = $slugToDisplay[$slug];
        }
    }
}

/**
 * Define $category for header.php
 */
$category = $categoryDisplay;

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="text-center fw-bold gradient-text mb-4">
        <?= $category ? htmlspecialchars($category) : 'Shop by Category'; ?>
    </h2>


    <div class="row g-4 mt-4">
        <?php
        if ($category) {
            $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE category = ? ORDER BY id DESC");
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $pid   = (int)$row['id'];
                    $name  = htmlspecialchars($row['name']);
                    $price = number_format((float)$row['price'], 2);
                    $img   = htmlspecialchars($row['image']);
                    echo '
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100 product-card border-0 shadow-sm">
                            <div class="position-relative overflow-hidden">
                                <img src="assets/images/'.$img.'" class="card-img-top product-img" alt="'.$name.'" style="height:250px;object-fit:cover;transition:transform .35s ease;">
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2 p-2">New</span>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title">'.$name.'</h5>
                                <p class="text-success fw-bold fs-5">₹'.$price.'</p>
                                <a href="product.php?id='.$pid.'" class="btn btn-gradient btn-sm w-100">View Details</a>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo "<p class='text-center text-muted'>No products found in this category.</p>";
            }
            $stmt->close();
        } else {
            echo "<p class='text-center text-muted'>Please select a category from the menu above.</p>";
        }
        ?>
    </div>
</div>

<style>
/* subtle hover zoom for category cards */
.product-card:hover .card-img-top { transform: scale(1.05); }
</style>

<?php include 'includes/footer.php'; ?>
