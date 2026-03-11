</main>

<!-- Footer -->
<!-- Footer -->
<footer class="footer-modern">
    <div class="container">
        <div class="row gy-5">

            <!-- Brand Info -->
            <div class="col-lg-4 col-md-12 text-center text-lg-start">
                <div class="footer-brand mb-4 d-flex justify-content-center justify-content-lg-start">
                    <h2 class="fw-bold d-flex align-items-center text-white"
                        style="text-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                        <i class="bi bi-bag-heart-fill me-3"></i> XL FASHION
                    </h2>
                </div>
                <p class="mb-4 pe-lg-5" style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; line-height: 1.6;">
                    Defining your style with premium quality and trending designs. Join our fashion journey and shine in
                    every moment.
                </p>
                <div class="social-icons d-flex justify-content-center justify-content-lg-start">
                    <a href="#" class="social-btn" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-btn" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-btn" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-btn" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>

            <!-- Shop Info -->
            <div class="col-lg-2 col-md-4 col-6">
                <h5 class="footer-heading">Collections</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="footer-link">Home</a></li>
                    <li><a href="index.php#collection" class="footer-link">New Arrivals</a></li>
                    <li><a href="index.php#collection" class="footer-link">Best Sellers</a></li>
                    <li><a href="catalog.php" class="footer-link">Shop All</a></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-4 col-6">
                <h5 class="footer-heading">Support</h5>
                <ul class="list-unstyled">
                    <li><a href="my_orders.php" class="footer-link">Track Order</a></li>
                    <li><a href="returns.php" class="footer-link">Easy Returns</a></li>
                    <li><a href="shipping.php" class="footer-link">Shipping Status</a></li>
                    <li><a href="contact.php" class="footer-link">Help Center</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-lg-4 col-md-4">
                <h5 class="footer-heading">Subscribe & Save</h5>
                <p class="mb-4 small" style="color: rgba(255, 255, 255, 0.9);">Get 10% off your first order and stay
                    updated with our latest drops!</p>
                <div class="newsletter-box">
                    <form class="newsletter-group" id="newsletterForm">
                        <input type="email" id="subscriberEmail" class="newsletter-input" placeholder="Enter your email"
                            required>
                        <button class="newsletter-btn" id="subscribeBtn" type="submit">Join</button>
                    </form>
                    <div id="subscriptionMessage" class="mt-2 small" style="display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Bottom Line -->
        <div class="footer-bottom mt-5 pt-4 border-top border-white-10">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-white-50 small">&copy; <?= date('Y') ?> <span class="text-white">XL Fashion
                            Trends</span>. Elevating your style journey.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div
                        class="payment-methods d-flex align-items-center justify-content-center justify-content-md-end mt-3 mt-md-0">
                        <i class="bi bi-credit-card-2-front" title="Visa"></i>
                        <i class="bi bi-paypal" title="PayPal"></i>
                        <i class="bi bi-apple" title="Apple Pay"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Styles -->
<style>

</style>

<link rel="stylesheet" href="./CSS/footer.css?v=<?= time(); ?>">
<!-- Bootstrap JS + Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<!-- Newsletter Subscription Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('newsletterForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const emailInput = document.getElementById('subscriberEmail');
                const subscribeBtn = document.getElementById('subscribeBtn');
                const msgDiv = document.getElementById('subscriptionMessage');
                const email = emailInput.value;

                // Disable button and show loading state
                subscribeBtn.disabled = true;
                const originalBtnText = subscribeBtn.innerText;
                subscribeBtn.innerText = '...';

                const formData = new FormData();
                formData.append('email', email);

                fetch('ajax_subscribe.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        msgDiv.innerText = data.message;
                        msgDiv.style.display = 'block';
                        msgDiv.style.color = data.status === 'success' ? '#fff' : '#ffd700';

                        if (data.status === 'success') {
                            emailInput.value = '';
                        }
                    })
                    .catch(error => {
                        msgDiv.innerText = 'An error occurred. Please try again.';
                        msgDiv.style.display = 'block';
                        msgDiv.style.color = '#ffd700';
                    })
                    .finally(() => {
                        subscribeBtn.disabled = false;
                        subscribeBtn.innerText = originalBtnText;
                    });
            });
        }
    });
</script>

</body>

</html>