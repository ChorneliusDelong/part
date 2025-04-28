<?php
// order_status_api.php
header('Content-Type: application/json');

$orderId = $_GET['id'] ?? '';
if (!$orderId) {
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

$orders_file = 'orders.json';
if (!file_exists($orders_file)) {
    echo json_encode(['error' => 'Orders file not found']);
    exit;
}

$orders = json_decode(file_get_contents($orders_file), true) ?? [];
$order = null;
foreach ($orders as $o) {
    if ($o['id'] === $orderId) {
        $order = $o;
        break;
    }
}

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}

echo json_encode(['status' => $order['status']]);
exit;
?>
