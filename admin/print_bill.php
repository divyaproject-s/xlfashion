<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include '../includes/config.php';

/* ===========================
   1. Validate Order ID
=========================== */
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    die("Invalid Order ID");
}

/* ===========================
   2. Fetch Order + Customer
=========================== */
$sql = "SELECT o.id, o.total_amount, o.shipping_address, o.created_at,
               u.name AS customer_name, u.mobile
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found");
}

/* ===========================
   3. Fetch Order Items
=========================== */
$sql = "SELECT oi.quantity, oi.price,
               p.name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if (!$result || $result->num_rows === 0) {
    die("No items found for this order");
}

/* ===========================
   4. Build Invoice HTML
=========================== */
$html = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
 
body {
        font-family: "DejaVu Sans", Arial, sans-serif;
        background: #f4f4f4;
        font-size: 14px;
    }

    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 30px;
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
    }

    .header h1 {
        margin: 0;
        font-size: 28px;
        color: #333;
    }

    .header p {
        margin: 5px 0;
        color: #777;
    }

    .section {
        margin-bottom: 25px;
    }

    .section h2 {
        font-size: 18px;
        color: #6a1b9a;
        border-bottom: 2px solid #eee;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }

    table th {
        background: #6a1b9a;
        color: #fff;
        padding: 10px;
        text-align: left;
    }

    table td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .total-row td {
        font-weight: bold;
        border-top: 2px solid #6a1b9a;
    }

    .footer {
        text-align: center;
        margin-top: 30px;
        color: #555;
    }
</style>

 
</head>

<body>

<div class="invoice-box">

    <div class="header">
        <h1>XL Fashion Trends</h1>
        <p>Invoice</p>
    </div>

    <div class="section">
        <h2>Order Details</h2>
        <p><strong>Order ID:</strong> #' . (int)$order['id'] . '</p>
        <p><strong>Order Date:</strong> ' . date('d-m-Y H:i', strtotime($order['created_at'])) . '</p>
    </div>

    <div class="section">
        <h2>Customer Details</h2>
        <p><strong>Name:</strong> ' . htmlspecialchars($order['customer_name']) . '</p>
        <p><strong>Mobile:</strong> ' . htmlspecialchars($order['mobile']) . '</p>
        <p><strong>Address:</strong><br>' . nl2br(htmlspecialchars($order['shipping_address'])) . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
';

$grand_total = 0;

while ($row = $result->fetch_assoc()) {
    $total = $row['quantity'] * $row['price'];
    $grand_total += $total;

    $html .= '
        <tr>
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . (int)$row['quantity'] . '</td>
            <td>₹ ' . number_format($row['price'], 2) . '</td>
            <td>₹ ' . number_format($total, 2) . '</td>
        </tr>
    ';
}

$html .= '
        <tr class="total-row">
            <td colspan="3" style="text-align:right;">Grand Total</td>
            <td>₹ ' . number_format($grand_total, 2) . '</td>
        </tr>
        </tbody>
    </table>

    <div class="footer">
        Thank you for shopping at <strong>XL Fashion Trends</strong><br>
        Visit us again!
    </div>

</div>

</body>
</html>
';

/* ===========================
   5. Generate PDF
=========================== */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream(
    "XL_Fashion_Invoice_" . $order_id . ".pdf",
    ["Attachment" => true]
);
exit;
