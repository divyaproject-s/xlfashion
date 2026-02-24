<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5 gradient-text">Contact Us</h2>
        <p class="text-muted">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        <div class="divider mx-auto"></div>
    </div>

    <div class="row g-5">
        <!-- Contact Info -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-light">
                <div class="mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-primary me-2"></i> Our Location</h5>
                    <p class="text-muted mb-0">123 Fashion Street, Style City,<br>XL 56789, Country</p>
                </div>
                <div class="mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-envelope-fill text-primary me-2"></i> Email Us</h5>
                    <p class="text-muted mb-0">support@xlfashion.com<br>info@xlfashion.com</p>
                </div>
                <div class="mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-telephone-fill text-primary me-2"></i> Call Us</h5>
                    <p class="text-muted mb-0">+1 234 567 890<br>+1 987 654 321</p>
                </div>
                <div class="mt-auto">
                    <h5 class="fw-bold mb-3">Follow Us</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow rounded-4 p-4">
                <form action="#" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Your Name</label>
                            <input type="text" name="name" class="form-control border-0 bg-light py-2 px-3" placeholder="Full Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                            <input type="email" name="email" class="form-control border-0 bg-light py-2 px-3" placeholder="email@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Subject</label>
                            <input type="text" name="subject" class="form-control border-0 bg-light py-2 px-3" placeholder="What is this regarding?" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase">Message</label>
                            <textarea name="message" class="form-control border-0 bg-light py-2 px-3" rows="5" placeholder="How can we help you?" required></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold py-2 shadow-sm">Send Message <i class="bi bi-send-fill ms-1"></i></button>
                        </div>
                    </div>
                </form>
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
.text-primary {
    color: #ff1493 !important;
}
.btn-primary {
    background-color: #ff1493 !important;
    border-color: #ff1493 !important;
}
.btn-primary:hover {
    background-color: #e91e63 !important;
    border-color: #e91e63 !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 20, 147, 0.3);
}
.btn-outline-primary {
    color: #ff1493 !important;
    border-color: #ff1493 !important;
}
.btn-outline-primary:hover {
    background-color: #ff1493 !important;
    color: #fff !important;
}
</style>

<?php include 'includes/footer.php'; ?>
