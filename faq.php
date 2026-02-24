<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5 gradient-text">Frequently Asked Questions</h2>
        <p class="text-muted">Everything you need to know about XL Fashion. Can't find the answer? Contact us!</p>
        <div class="divider mx-auto"></div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="accordion accordion-flush shadow-sm rounded-4 overflow-hidden border" id="faqAccordion">
                
                <!-- General -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            How do I place an order?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Simply browse our collections, select your size, and click "Buy Now" or "Add to Cart". Follow the checkout process to enter your details and complete the payment.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            What payment methods do you accept?
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            We accept all major credit cards (Visa, MasterCard, Amex), PayPal, and Apple Pay for a secure and seamless checkout experience.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Can I cancel my order?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Orders can be canceled within 1 hour of placement. After that, they enter the processing phase and cannot be modified.
                        </div>
                    </div>
                </div>

                <!-- Shipping -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#c4">
                            How long does shipping take?
                        </button>
                    </h2>
                    <div id="c4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Standard shipping typically takes 3-7 business days depending on your location. You will receive a tracking ID once your order is shipped.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold py-3" type="button" data-bs-toggle="collapse" data-bs-target="#c5">
                            Do you ship internationally?
                        </button>
                    </h2>
                    <div id="c5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted">
                            Currently, we ship to most major countries. Shipping rates and delivery times vary based on destination. Check our Shipping Info page for details.
                        </div>
                    </div>
                </div>

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
.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #ff1493;
    box-shadow: none;
}
.accordion-item { border: none; }
</style>

<?php include 'includes/footer.php'; ?>
