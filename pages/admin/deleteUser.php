<?php
if (isset($_GET['id'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    resetCSRFToken();

    $user_id = intval($_GET['id']);

    // Query untuk menghapus pengguna
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    // Eksekusi query
    if ($stmt->execute()) {
        // Redirect ke userList.php dengan pesan sukses
        header("Location: /userList?deleted=true");
    } else {
        // Gagal, redirect dengan pesan error
        header("Location: /userList?error=" . urlencode($stmt->error));
    }

    $stmt->close();
} else {
    // Tidak ada ID pengguna
    header("Location: /userList?error=" . urlencode('No user ID provided.'));
}

$conn->close();
?>