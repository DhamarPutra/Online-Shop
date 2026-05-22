<?php
$userId = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0;

// Fungsi untuk mendapatkan cart ID
function getCartId($userId)
{
    global $conn;
    $cartId = null;
    $stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($cartId);
    $stmt->fetch();
    $stmt->close();
    return $cartId; // Mengembalikan ID cart atau null
}

// Fungsi untuk mendapatkan semua item dalam keranjang
function getCartItems($cartId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT ci.product_id, SUM(ci.quantity) AS quantity, p.product_name, p.platform_price
                         FROM cart_items ci
                         JOIN db_product p ON ci.product_id = p.id
                         WHERE ci.cart_id = ?
                         GROUP BY ci.product_id, p.product_name, p.platform_price");
    $stmt->bind_param('i', $cartId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $cartItems;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $itemId = intval($_POST['item_id']);

    // Hapus item dari cart_items
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE product_id = ?");
    $stmt->bind_param('i', $itemId);
    $stmt->execute();
    $stmt->close();
    exit;
}

// Mendapatkan cart ID untuk pengguna
$cartId = getCartId($userId);
$cartItems = [];

// Jika ada cart, ambil item di dalamnya
if ($cartId !== null) {
    $cartItems = getCartItems($cartId);
}

$totalPrice = 0; // Total harga
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
</head>

<body class="bg-gray-100">
    <div class="h-screen">
        <h1 class="text-2xl font-bold text-center mb-2">Shopping Cart</h1>
        <div class="bg-white shadow-md rounded-lg p-4">
            <table class="min-w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Product Name</th>
                        <th class="px-4 py-2 text-left">Quantity</th>
                        <th class="px-4 py-2 text-left">Price</th>
                        <th class="px-4 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cartItems)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-2">Your cart is empty.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td class="border px-4 py-2">Rp<?php echo number_format($item['platform_price'], 0, ',', '.'); ?></td>
                                <td class="border px-4 py-2">
                                    <form action="/cart" method="POST" class="inline">
                                        <input type="hidden" name="item_id" value="<?php echo $item['product_id']; ?>">
                                        <button type="button" id="confirmRemove" class="text-red-500 hover:underline">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php $totalPrice += $item['platform_price'] * $item['quantity']; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div id="total-price" class="mt-4 text-right font-bold">Total: Rp<?php echo number_format($totalPrice, 0, ',', '.'); ?></div>
            <div class="mt-4 text-center">
                <button id="checkout-btn" class="bg-blue-500 text-white px-4 py-2 rounded">Checkout</button>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('confirmRemove').addEventListener('click', function(event) {
        event.preventDefault(); // Mencegah form submit langsung
        // Tampilkan konfirmasi SweetAlert
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to remove item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna menekan "Yes", lakukan penghapusan dengan AJAX
                const form = document.querySelector('form');
                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .catch(error => {
                    Swal.fire('Success!', 'Item has been removed!', 'success').then(() => {
                            // Arahkan ke halaman keranjang
                            location.reload();
                        });
                });
            }
        });
    });
</script>

</body>

</html>