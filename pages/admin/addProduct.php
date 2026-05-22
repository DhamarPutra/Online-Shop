<?php
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    resetCSRFToken();

    // Ambil data dari form
    $product_code = mysqli_real_escape_string($conn, $_POST['product_code']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $seller_price = mysqli_real_escape_string($conn, $_POST['seller_price']);
    $platform_price = $seller_price + 2500;
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['product_image']['tmp_name'];
        $imageName = basename($_FILES['product_image']['name']);
        $imageSize = $_FILES['product_image']['size'];
        $imageType = $_FILES['product_image']['type'];

        // Tentukan lokasi penyimpanan gambar
        $uploadDir = __DIR__ . '/../../assets/uploads/product/';
        $uploadFileDir = $uploadDir . $imageName;

        // Pindahkan file yang diunggah ke direktori yang ditentukan
        if (move_uploaded_file($imageTmpPath, $uploadFileDir)) {
            // Jika berhasil memindahkan gambar, simpan data produk ke database
            $sql = "INSERT INTO db_product (product_code, product_name, seller_price, platform_price, stock, product_image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssiiis', $product_code, $product_name, $seller_price, $platform_price, $stock, $imageName);

            if ($stmt->execute()) {
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
</head>

<body class="bg-gray-100">

    <div class="flex h-screen">
        <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-lg">
            <h2 class="text-2xl font-bold text-center text-gray-700 mb-6">Add New Product</h2>

            <form action="/addProduct" method="POST" id="productForm" class="space-y-6" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Product Code -->
                <div>
                    <label for="product_code" class="block text-gray-700 font-semibold">Product Code</label>
                    <input type="text" name="product_code" id="product_code" required maxlength="6"
                        class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Product Name -->
                <div>
                    <label for="product_name" class="block text-gray-700 font-semibold">Product Name</label>
                    <input type="text" name="product_name" id="product_name" required
                        class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Seller Price -->
                <div>
                    <label for="seller_price" class="block text-gray-700 font-semibold">Price</label>
                    <div class="flex items-center w-full p-2 mt-1 border border-gray-300 rounded-md focus-within:ring-2 focus-within:ring-blue-500">
                        <span class="text-gray-500 mr-2">Rp</span>
                        <input type="number" name="seller_price" id="seller_price" required step="0.01"
                            class="w-full focus:outline-none">
                    </div>
                    <span class="font-bold text-red-500">*Platform tax: Rp2.500.</span>
                </div>

                <!-- Stock -->
                <div>
                    <label for="stock" class="block text-gray-700 font-semibold">Stock</label>
                    <input type="number" name="stock" id="stock" required
                        class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Product Image -->
                <div class="mb-4">
                    <label class="block text-gray-700">Product Image</label>
                    <input type="file" name="product_image" id="product_image" class="w-full p-2 border border-gray-300 rounded mt-1" accept="image/*" required>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="button" id="addProduct"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition-colors">
                        Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addProduct').addEventListener('click', function(event) {
            // Tampilkan konfirmasi SweetAlert
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to add new product?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, add it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna menekan "Yes", submit form
                    Swal.fire('Success!', 'Product has been added!', 'success').then(() => {
                        location.reload();
                        document.querySelector('form').submit();
                    });
                }
            });
        });
    </script>
</body>

</html>