<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';
?>

<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5 gradient-text">Shipping Information</h2>
        <p class="text-muted">Fast, reliable, and trackable shipping worldwide.</p>
        <div class="divider mx-auto"></div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <section class="mb-5">
                    <h4 class="fw-bold mb-3">Delivery Times</h4>
                    <p class="text-muted">We process orders within 24-48 hours. Once shipped, delivery times are as follows:</p>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Region</th>
                                    <th>Estimated Delivery</th>
                                    <th>Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Local (Style City)</td>
                                    <td>1-2 Business Days</td>
                                    <td>FREE</td>
                                </tr>
                                <tr>
                                    <td>National</td>
                                    <td>3-5 Business Days</td>
                                    <td>SGD 5.00</td>
                                </tr>
                                <tr>
                                    <td>International</td>
                                    <td>7-14 Business Days</td>
                                    <td>SGD 15.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted mt-2">Note: Free national shipping on orders over SGD 50!</p>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold mb-3">Order Tracking</h4>
                    <p class="text-muted">As soon as your package leaves our warehouse, we will send you a shipment confirmation email with a link to track your order. You can also use our <a href="track_order.php">Track Order</a> page.</p>
                </section>

                <section>
                    <h4 class="fw-bold mb-3">Taxes & Duties</h4>
                    <p class="text-muted mb-0">For international orders, please note that customs duties, taxes, and fees may be charged by your local customs office. These charges are the responsibility of the customer.</p>
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
