<?php
$csrf_token = generateCSRFToken();

if (isset($_SESSION['user']['id']) && isset($_SESSION['user']['role']) === 'Admin') {
    header("Location: /dashboard"); // Arahkan ke dashboard jika sudah login dan admin
    exit(); // Hentikan eksekusi skrip
} elseif (isset($_SESSION['user']['id'])) {
    header("Location: /shop"); // Arahkan ke home jika sudah login
    exit(); // Hentikan eksekusi skrip
}

$loginMessage = "";

// Proses ketika form login disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    resetCSRFToken();

    // Ambil data dari form
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Cek apakah username dan password valid
    $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    // Mengikat parameter
    $stmt->bind_param('s', $username);

    // Menjalankan pernyataan
    $stmt->execute();

    // Mendapatkan hasil
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Login sukses
            // Misalkan $user hanya berisi informasi yang aman
            $_SESSION['user'] = [
                'id' => $user['id'],          // Hanya menyimpan ID
                'username' => $user['username'], // Hanya menyimpan username
                'role' => $user['role']       // Jika level admin
            ];
            header("Location: /dashboard"); // Arahkan ke halaman dashboard
            exit();
        } else {
            $loginMessage = "Username atau password salah.";
        }
    } else {
        $loginMessage = "Username tidak ada.";
    }
    $stmt->close();
}

// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body class="bg-gray-100">
    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

            <form action="/login" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Input Username -->
                <div class="mb-4">
                    <label class="block text-gray-700">Username</label>
                    <input autocomplete="off" type="text" name="username" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                </div>

                <!-- Input Password -->
                <div class="mb-4">
                    <label class="block text-gray-700">Password</label>
                    <input autocomplete="off" type="password" name="password" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                </div>

                <!-- Tombol Login -->
                <div class="mb-6">
                    <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Login</button>
                </div>

                <!-- Menampilkan Pesan Error Jika Ada -->
                <?php if (!empty($loginMessage)) : ?>
                    <div class="text-center text-red-500">
                        <?php echo $loginMessage; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>

</html>