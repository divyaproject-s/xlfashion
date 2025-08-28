<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: admin/admin_index.php");
    exit;
}
?>

<!-- Carousel -->
<div id="homeCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-inner">

        <div class="carousel-item active">
            <div class="carousel-img" style="background-image: url('assets/images/3.png');"></div>
            <div class="carousel-caption">
                <h2 class="display-5 fw-bold animate-title">Welcome to <span class="text-warning">Alankara</span></h2>
                <p class="lead animate-subtitle">Discover handcrafted elegance</p>
            </div>
        </div>

        <div class="carousel-item">
            <div class="carousel-img" style="background-image: url('assets/images/4.png');"></div>
            <div class="carousel-caption">
                <h2 class="display-5 fw-bold animate-title">Latest <span class="text-warning">Collections</span></h2>
                <p class="lead animate-subtitle">Find your perfect piece today</p>
            </div>
        </div>

        <div class="carousel-item">
            <div class="carousel-img" style="background-image: url('assets/images/6.png');"></div>
            <div class="carousel-caption">
                <h2 class="display-5 fw-bold animate-title">Style & <span class="text-warning">Quality</span></h2>
                <p class="lead animate-subtitle">Crafted with passion and care</p>
            </div>
        </div>

    </div>

    <!-- Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon custom-control"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon custom-control"></span>
    </button>
</div>

<!-- Categories Section -->
<!-- Categories Section -->
<div class="container my-5">
    <h2 class="text-center fw-bold gradient-text mb-4">Shop by Categories</h2>

    <div class="row g-4 mt-4">
        <?php
        $categories = [
            ["name" => "Jewellery Set", "image" => "jew.jpg"],
            ["name" => "Earrings & Studs", "image" => "ear.jpg"],
            ["name" => "Bangles", "image" => "bang.jpg"],
            ["name" => "Necklaces", "image" => "neck.jpg"],
            ["name" => "Rings", "image" => "ring.jpeg"],
            ["name" => "Anklets", "image" => "ank.jpeg"],
            ["name" => "Oxidised", "image" => "oxidi.jpeg"]
        ];

        foreach ($categories as $cat) {
            echo '
            <div class="col-6 col-md-3 col-lg-2">
                <a href="category.php?cat='.urlencode($cat["name"]).'" class="text-decoration-none">
                    <div class="card h-100 category-card text-center shadow-sm border-0 overflow-hidden">
                        <img src="assets/category/'.$cat["image"].'" 
                             class="card-img-top" 
                             alt="'.$cat["name"].'" 
                             style="height:160px; object-fit:cover;">
                        <div class="card-body p-2">
                            <h6 class="fw-bold text-dark">'.$cat["name"].'</h6>
                        </div>
                    </div>
                </a>
            </div>
            ';
        }
        ?>
    </div>
</div>

<!-- Latest Products Section -->
<div class="container pb-5">
    <h2 class="text-center gradient-text mb-4">Latest Products</h2>
    <div class="row g-4">
        <?php
        $result = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 8");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 product-card">
                        <div class="position-relative overflow-hidden">
                            <img src="assets/images/'.$row['image'].'" class="card-img-top product-img" alt="'.$row['name'].'">
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2 p-2">New</span>
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title">'.$row['name'].'</h5>
                            <p class="text-success fw-bold fs-5">₹'.$row['price'].'</p>
                            <a href="product.php?id='.$row['id'].'" class="btn btn-gradient btn-sm w-100">View Details</a>
                        </div>
                    </div>
                </div>
                ';
            }
        } else {
            echo "<p class='text-center text-muted'>No products available.</p>";
        }
        ?>
    </div>
</div>

<!-- Custom Styles -->
<style>
/* Gradient Text */
.gradient-text {
    background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Category Card with Image */
.category-card-img {
    height: 200px;
    border-radius: 15px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    transition: transform 0.3s ease;
}
.category-card-img img {
    object-fit: cover;
    height: 100%;
    width: 100%;
    transition: transform 0.4s ease;
}
.category-card-img:hover img {
    transform: scale(1.1);
}
.category-card-img .overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.2), rgba(124,14,32,0.6));
    opacity: 0;
    transition: opacity 0.3s ease;
}
.category-card-img:hover .overlay {
    opacity: 1;
}

/* Product Card */
.product-card {
    border: none;
    border-radius: 15px;
    overflow: hidden;
    transition: transform 0.3s ease;
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0px 8px 20px rgba(0,0,0,0.15);
}
.product-img {
    transition: transform 0.4s ease;
}
.product-card:hover .product-img {
    transform: scale(1.1);
}

/* Gradient Button */
.btn-gradient {
    background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
    color: #fff;
    border: none;
    transition: all 0.3s ease;
}
.btn-gradient:hover {
    background: linear-gradient(90deg, #fc1978ff, #7c0e20ff);
    color: #fff;
}
</style>

<?php include 'includes/footer.php'; ?>
