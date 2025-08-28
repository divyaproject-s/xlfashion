</main>

<!-- Footer -->
<footer class="footer-gradient text-white pt-4">
    <div class="container">
        <div class="row">
            <!-- Brand Info -->
            <div class="col-md-4 mb-3 text-center text-md-start">
                <h4 class="fw-bold">💎 Alankara</h4>
                <p style="font-size:14px;">Elegant jewellery for every occasion.  
                Crafted with love & tradition.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-4 mb-3 text-center">
                <h5 class="fw-bold">Quick Links</h5>
                <a href="index.php" class="footer-link d-block">🏠 Home</a>
                <a href="products.php" class="footer-link d-block">🛍️ Products</a>
                <a href="cart.php" class="footer-link d-block">🛒 Cart</a>
                <a href="contact.php" class="footer-link d-block">📞 Contact</a>
            </div>

            <!-- Social Media -->
            <div class="col-md-4 mb-3 text-center text-md-end">
                <h5 class="fw-bold">Follow Us</h5>
                <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                <a href="#" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
            </div>
        </div>

        <!-- Divider -->
        <hr class="border-light">

        <!-- Copyright -->
        <div class="text-center pb-2">
            <p class="mb-0">&copy; <?= date('Y') ?> <strong>Alankara</strong>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Styles -->
<style>
    /* Gradient Footer */
    .footer-gradient {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
    }
    .footer-link {
        color: #fff;
        text-decoration: none;
        font-size: 14px;
        margin: 4px 0;
        display: inline-block;
        transition: 0.3s;
    }
    .footer-link:hover {
        color: #ffd700; /* gold effect */
    }
    .social-icon {
        font-size: 20px;
        color: #fff;
        margin: 0 8px;
        transition: transform 0.3s, color 0.3s;
    }
    .social-icon:hover {
        color: #ffd700;
        transform: scale(1.2);
    }
</style>

<!-- Bootstrap JS + Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
