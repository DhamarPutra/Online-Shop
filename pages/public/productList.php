<?php
// Ambil produk dari database
$sql = "SELECT * FROM db_product";
$result = mysqli_query($conn, $sql);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Cek jika pengguna sudah login
if (isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];

    // Mengambil data POST jika ada
    $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = 1; // Anda bisa menyesuaikan ini jika ingin menambahkan opsi kuantitas

    // Fungsi untuk menambahkan produk ke keranjang
    function addToCart($userId, $productId, $quantity)
    {
        global $conn;

        // Periksa jika cart untuk user sudah ada
        $cartId = getCartId($userId);
        if ($cartId === null) {
            // Buat keranjang baru jika belum ada
            $sql = "INSERT INTO carts (user_id) VALUES (?)";
            $stmt = $conn->prepare($sql);
            mysqli_stmt_bind_param($stmt, 'i', $userId);
            mysqli_stmt_execute($stmt);
            $cartId = mysqli_insert_id($conn); // Ambil ID keranjang yang baru dibuat
            mysqli_stmt_close($stmt);
        }

        // Tambahkan item ke cart_items
        $stmt = mysqli_prepare($conn, "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iii', $cartId, $productId, $quantity);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }

    // Mendapatkan ID cart untuk user
    function getCartId($userId)
    {
        global $conn;
        $stmt = mysqli_prepare($conn, "SELECT id FROM carts WHERE user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $cartId);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $cartId; // Mengembalikan ID cart atau null
    }

    // Cek apakah produk berhasil ditambahkan
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (addToCart($userId, $productId, $quantity)) {
            echo json_encode(['success' => true, 'message' => 'Product added to cart']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
        exit; // Hentikan eksekusi script setelah menangani permintaan
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
</head>

<body class="bg-gray-100 product-list">
    <div class="h-screen">
        <!-- <div>
            <span class="text-2xl font-bold mb-2 text-center">Product List</span>
            <span class="text-2xl font-bold mb-2 text-right">Product List</span>
        </div> -->
        <h1 class="text-2xl font-bold mb-2 text-center">Product List</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow-md p-2">
                    <img src="../../assets/uploads/product/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="w-full object-cover aspect-square rounded-t-lg">
                    <h2 class="text-xl font-semibold mt-2"><?php echo htmlspecialchars($product['product_name']); ?></h2>
                    <p class="<?php echo ($product['stock'] <= 5) ? 'text-red-500' : 'text-gray-700'; ?>">
                        Stock: <?php echo htmlspecialchars($product['stock']); ?>
                    </p>
                    <p class="text-lg font-bold mt-2">Rp<?php echo number_format($product['platform_price'], 0, ',', '.'); ?></p>
                    <button class="add-to-cart bg-blue-500 text-white py-2 px-4 rounded mt-4" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script>
        // Add to cart functionality
        const addToCartButtons = document.querySelectorAll('.add-to-cart');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');

                // Show SweetAlert confirmation
                Swal.fire({
                    title: 'Add to Cart',
                    text: 'Are you sure you want to add this product to the cart?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, add it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim permintaan AJAX untuk menambahkan produk ke keranjang
                        fetch('/productList', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'product_id=' + productId,
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Added!',
                                        'Your product has been added to the cart.',
                                        'success'
                                    ).then((result) => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        data.message,
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });
                    }
                });
            });
        });
    </script>
</body>

</html>