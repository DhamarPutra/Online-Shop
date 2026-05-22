<?php
$csrf_token = generateCSRFToken();

$user_id = $_SESSION['user']['id'];

// Query untuk mengambil informasi pengguna
$sql = "SELECT first_name, last_name, email, no_tlp, username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$checkPassword = "";
$saveMessage = "";

// Proses pembaruan profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF token
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
    $password = $_POST['password'];
    $retype_password = $_POST['retype_password'];

    // Validasi data
    if (!empty($password) && $password !== $retype_password) {
        $checkPassword = "<p class='text-red-500 text-center'>Password tidak cocok!</p>";
    } else {
        // Update data pengguna
        $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, no_tlp = ?, username = ?";

        // Jika password diisi, hash password dan tambahkan ke query
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query .= ", password = ?";
        }
        $update_query .= " WHERE id = ?";

        $stmt = $conn->prepare($update_query);

        // Bind parameter
        if (!empty($password)) {
            $stmt->bind_param('ssssssi', $first_name, $last_name, $email, $no_tlp, $username, $hashed_password, $user_id);
        } else {
            $stmt->bind_param('sssssi', $first_name, $last_name, $email, $no_tlp, $username, $user_id);
        }

        // Eksekusi query
        if ($stmt->execute()) {
            header("Location: /profile?updated=true");
        }
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-4xl">
            <!-- Form Edit Profil -->
            <h2 class="text-2xl font-bold mb-6 text-center">Edit Profile</h2>
            <form action="/profile" method="POST" class="flex flex-wrap -mx-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Kolom Kiri -->
                <div class="w-full lg:w-1/2 px-4 mb-4">
                    <div class="mb-4">
                        <label class="block text-gray-700">First Name</label>
                        <input autocomplete="off" type="text" name="first_name"
                            value="<?php echo htmlspecialchars($user['first_name']); ?>"
                            class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Last Name (optional)</label>
                        <input autocomplete="off" type="text" name="last_name"
                            value="<?php echo htmlspecialchars($user['last_name']); ?>"
                            class="w-full p-2 border border-gray-300 rounded mt-1">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Email</label>
                        <input autocomplete="off" type="email" name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">No. Telepon</label>
                        <input autocomplete="off" type="text" name="no_tlp"
                            value="<?php echo htmlspecialchars($user['no_tlp']); ?>"
                            class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="w-full lg:w-1/2 px-4 mb-4">
                    <div class="mb-4">
                        <label class="block text-gray-700">Username</label>
                        <input autocomplete="off" type="text" name="username"
                            value="<?php echo htmlspecialchars($user['username']); ?>"
                            class="w-full p-2 border border-gray-300 rounded mt-1" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">New Password (leave blank to keep current)</label>
                        <input autocomplete="off" type="password" name="password" class="w-full p-2 border border-gray-300 rounded mt-1">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Retype New Password</label>
                        <input autocomplete="off" type="password" name="retype_password" class="w-full p-2 border border-gray-300 rounded mt-1">
                        <span class="text-red-500"><?php echo $checkPassword; ?></span>
                    </div>

                    <div class="mb-6">
                        <button type="button" id="updateProfileButton" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('updateProfileButton').addEventListener('click', function(event) {
            // Tampilkan konfirmasi SweetAlert
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save the changes?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna menekan "Yes", submit form
                    Swal.fire('Success!', 'User details have been changed!', 'success').then(() => {
                        location.reload();
                        document.querySelector('form').submit();
                    });
                }
            });
        });
    </script>
</body>

</html>