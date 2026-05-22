<?php
$csrf_token = generateCSRFToken();

// Ambil semua data pengguna dari database
$sql = "SELECT id, first_name, last_name, email, username, role FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h2 class="text-2xl font-bold mb-6 text-center">User List</h2>

        <table class="min-w-full bg-white border-collapse">
            <thead>
                <tr>
                    <th class="py-2 px-4 border">#</th>
                    <th class="py-2 px-4 border">Full Name</th>
                    <th class="py-2 px-4 border">Email</th>
                    <th class="py-2 px-4 border">Username</th>
                    <th class="py-2 px-4 border">Role</th>
                    <th class="py-2 px-4 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)) : ?>
                    <?php foreach ($users as $key => $user) : ?>
                        <tr>
                            <td class="py-2 px-4 border"><?php echo $key + 1; ?></td>
                            <td class="py-2 px-4 border">
                                <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?>
                            </td>
                            <td class="py-2 px-4 border"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="py-2 px-4 border"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="py-2 px-4 border text-center">
                                <?php echo htmlspecialchars($user['role']);?>
                            </td>
                            <td class="py-2 px-4 border text-center">
                                <!-- Button Edit -->
                                <button onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo $user['first_name']; ?>', '<?php echo $user['last_name']; ?>', '<?php echo $user['email']; ?>', '<?php echo $user['username']; ?>')" class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Edit</button>

                                <!-- Button Delete -->
                                <button onclick="confirmDelete(<?php echo $user['id']; ?>)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div id="editUserModal" class="fixed inset-0 flex items-center justify-center hidden bg-gray-500 bg-opacity-75">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center">Edit User</h2>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id" name="id">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="mb-4">
                    <label class="block text-gray-700">First Name</label>
                    <input type="text" id="edit_first_name" name="first_name" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Last Name</label>
                    <input type="text" id="edit_last_name" name="last_name" class="w-full p-2 border border-gray-300 rounded mt-1">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Email</label>
                    <input type="email" id="edit_email" name="email" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Username</label>
                    <input type="text" id="edit_username" name="username" class="w-full p-2 border border-gray-300 rounded mt-1" required>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600 mr-2">Cancel</button>
                    <button type="button" onclick="confirmEdit(<?php echo $user['id']; ?>)" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk membuka modal dan mengisi data user
        function openEditModal(id, first_name, last_name, email, username) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_first_name').value = first_name;
            document.getElementById('edit_last_name').value = last_name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_username').value = username;

            // Tampilkan modal
            document.getElementById('editUserModal').classList.remove('hidden');
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }

        // Konfirmasi hapus
        function confirmDelete(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this user?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect ke halaman delete_user.php
                    Swal.fire('Success!', 'User has been deleted.', 'success').then(() => {
                        window.location.href = '/deleteUser?id=' + userId;
                    });
                }
            });
        }

        function confirmEdit() {
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
                    // Ambil data dari form
                    const formData = new FormData(document.getElementById('editUserForm'));

                    // Kirim data via AJAX
                    fetch('/editUser', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            Swal.fire('Success!', 'User details have been updated!', 'success').then(() => {
                                location.reload();
                            });
                            return response.json(); // Parse JSON
                        })
                }
            });
        }
    </script>
</body>

</html>