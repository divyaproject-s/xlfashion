<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5 gradient-text">Returns & Refund Policy</h2>
        <p class="text-muted">Shop with confidence with our 30-day return policy.</p>
        <div class="divider mx-auto"></div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <section class="mb-5">
                    <h4 class="fw-bold mb-3">30-Day Money Back Guarantee</h4>
                    <p class="text-muted">If you are not 100% satisfied with your purchase, you can return your item(s) for a full refund or exchange within 30 days of the delivery date.</p>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold mb-3">Return Conditions</h4>
                    <ul class="text-muted">
                        <li>Items must be in original, unused, and unwashed condition.</li>
                        <li>Original tags and packaging must be attached.</li>
                        <li>Final sale items are not eligible for returns unless defective.</li>
                        <li>Hygiene products (e.g., earrings) cannot be returned for health reasons.</li>
                    </ul>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold mb-3">How to Start a Return</h4>
                    <p class="text-muted">To initiate a return, please email us at <strong>returns@xlfashion.com</strong> with your Order ID and reason for return. Our team will provide you with a return label and further instructions.</p>
                </section>

                <section>
                    <h4 class="fw-bold mb-3">Refunds</h4>
                    <p class="text-muted mb-0">Once we receive and inspect your return, we will notify you and process your refund. The amount will be credited back to your original payment method within 5-10 business days.</p>
                </section>
            </div>
        </div>
    </div>
</div>

<style>
.gradient-text {
    background: linear-gradient(45deg, #2c3e50, #000000);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.divider {
    width: 60px;
    height: 4px;
    background: #ff1493;
    border-radius: 2px;
}
</style>

<?php include 'includes/footer.php'; ?>
