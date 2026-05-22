<?php
$csrf_token = generateCSRFToken();

$checkPassword = "";
$saveMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    resetCSRFToken();

    // Ambil data dari form
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_tlp = mysqli_real_escape_string($conn, $_POST['no_tlp']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $retype_password = mysqli_real_escape_string($conn, $_POST['retype_password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Validasi data
    if ($password !== $retype_password) {
        $checkPassword = "<p class='text-red-500 text-center'>Password tidak cocok!</p>";
    } else {
        // Encrypt password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert data into database
        $sql = "INSERT INTO users (first_name, last_name, email, no_tlp, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Mengikat parameter
        $stmt->bind_param('sssssss', $first_name, $last_name, $email, $no_tlp, $username, $hashed_password, $role);

        // Menjalankan pernyataan
        if ($stmt->execute()) {
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
            <!-- Form Registrasi -->
            <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>
            <form action="/register" method="POST" class="flex flex-wrap -mx-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Kolom Kiri -->
                <div class="w-full lg:w-1/2 px-4 mb-4">
                    <div class="mb-4">
                        <label class="block text-gray-700">First Name</label>
                        <input autocomplete="off" type="text" name="first_name" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Last Name (optional)</label>
                        <input autocomplete="off" type="text" name="last_name" class="w-full p-2 border border-gray-300 rounded mt-1">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Email</label>
                        <input autocomplete="off" type="email" name="email" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">No. Telepon</label>
                        <input autocomplete="off" type="number" name="no_tlp" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="w-full lg:w-1/2 px-4 mb-4">
                    <div class="mb-4">
                        <label class="block text-gray-700">Username</label>
                        <input autocomplete="off" type="text" name="username" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Password</label>
                        <input autocomplete="off" type="password" name="password" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Retype Password</label>
                        <input autocomplete="off" type="password" name="retype_password" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                        <span class="text-red-500"><?php echo $checkPassword; ?></span>
                    </div>

                    <?php
                    if ($_SESSION['user']['id'] && $_SESSION['user']['role'] === 'Admin') {
                        echo '
                            <div class="mb-4">
                                <label for="role" class="block text-gray-700 font-semibold">Is Admin</label>
                                <select name="role" id="role" class="w-full p-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Buyer">Buyer</option>
                                    <option value="Seller">Seller</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            ';
                    }
                    ?>

                    <div class="mb-4">
                        <button type="button" id="addUserButton" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Register</button>
                    </div>

                    <!-- Menampilkan Pesan Simpan -->
                    <?php if (!empty($saveMessage)) : ?>
                        <?php echo $saveMessage; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('addUserButton').addEventListener('click', function(event) {
            // Tampilkan konfirmasi SweetAlert
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to add new user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, add it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna menekan "Yes", submit form
                    Swal.fire('Success!', 'User has been added!', 'success').then(() => {
                        location.reload();
                        document.querySelector('form').submit();
                    });
                }
            });
        });
    </script>
</body>

</html>