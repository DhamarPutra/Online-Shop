<?php
// Cek apakah user sudah login
if (!isset($_SESSION['user']['id']) || (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] !== 'Admin')) {
    header("Location: /shop"); // Arahkan ke awal jika belum login
    exit(); // Hentikan eksekusi skrip
} elseif (!isset($_SESSION['user']['id'])) {
    header("Location: /"); // Arahkan ke awal jika belum login
    exit(); // Hentikan eksekusi skrip
};
$username = $_SESSION['user']['username'];

function getCartItemCount($userId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT product_id) as item_count 
                            FROM cart_items 
                            WHERE cart_id = (SELECT id FROM carts WHERE user_id = ?)");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['item_count'] ? $row['item_count'] : 0;
}

// Misalnya, kita memiliki ID pengguna dari sesi
$userId = $_SESSION['user']['id'];
$cartItemCount = getCartItemCount($userId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>

<body class="flex bg-gray-100">
    <!-- Sidebar -->
    <div class="bg-white w-64 h-screen shadow-md">
        <div class="p-4">
            <h2 class="text-xl font-bold text-center">Dashboard</h2>
            <p class="text-center">Hello, <?php echo htmlspecialchars($username); ?>!</p>
        </div>
        <nav class="mt-6">
            <ul>
                <li>
                    <a href="#" class="block p-4 text-gray-700 hover:bg-blue-500 hover:text-white" onclick="loadContent('productList')">Product</a>
                </li>
                <li>
                    <a href="#" class="block p-4 text-gray-700 hover:bg-blue-500 hover:text-white" onclick="loadContent('cart')">Cart (<?php echo $cartItemCount; ?>)</a>
                </li>
                <li>
                    <a href="#" class="block p-4 text-gray-700 hover:bg-blue-500 hover:text-white" onclick="loadContent('addProduct')">Add Product</a>
                </li>
                <li>
                    <a href="#" class="block p-4 text-gray-700 hover:bg-blue-500 hover:text-white" onclick="loadContent('add-user')">Add User</a>
                </li>
                <li>
                    <a href="#" class="block p-4 text-gray-700 hover:bg-blue-500 hover:text-white" onclick="loadContent('profile')">Profile</a>
                </li>
                <li>
                    <a href="#" class="block p-4 text-gray-700 hover:bg-blue-500 hover:text-white" onclick="loadContent('userList')">User List</a>
                </li>
                <li>
                    <button type="button" id="confirmLogout" class="w-full text-left p-4 text-gray-700 hover:bg-red-500 hover:text-white">Logout</button>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Konten Utama -->
    <div class="flex-1 p-4">
        <div id="content">
            <iframe src="/productList" width="100%" height="100%"></iframe>
        </div>
    </div>

    <script>
        // Fungsi untuk memuat konten sesuai pilihan menu
        function loadContent(page) {
            const content = document.getElementById('content');
            let html = '';

            switch (page) {
                case 'productList':
                    html = `
                        <iframe src="/productList" width="100%" height="100%"></iframe>
                    `;
                    break;
                case 'cart':
                    html = `
                        <iframe src="/cart" width="100%" height="100%"></iframe>
                    `;
                    break;
                case 'addProduct':
                    html = `
                        <iframe src="/addProduct" width="100%" height="100%"></iframe>
                    `;
                    break;
                case 'add-user':
                    html = `
                        <iframe src="/register" width="100%" height="100%"></iframe>
                    `;
                    break;
                case 'profile':
                    html = `
                        <iframe src="/profile" width="100%" height="100%"></iframe>
                    `;
                    break;
                case 'userList':
                    html = `
                        <iframe src="/userList" width="100%" height="100%"></iframe>
                    `;
                    break;
                default:
                    html = `
                        <h1 class="text-2xl font-bold">Welcome to Your Dashboard</h1>
                        <p class="mt-4">Please select an option from the menu.</p>
                    `;
            }

            content.innerHTML = html;
        }

        document.getElementById('confirmLogout').addEventListener('click', function(event) {
            // Tampilkan konfirmasi SweetAlert
            Swal.fire({
                title: 'Ready to Log Out?',
                text: "Are you sure you want to log out? We'll miss you!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Log Me Out!',
                cancelButtonText: 'No, Stay Logged In!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect ke halaman delete_user.php
                    Swal.fire('Goodbye!', 'You have successfully logged out. See you next time!', 'success').then(() => {
                        window.location.href = '/logout';
                    });
                }
            });
        });
    </script>
</body>

</html>