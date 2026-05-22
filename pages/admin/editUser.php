<?php
// Cek jika request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF token jika perlu
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }
    resetCSRFToken();

    // Ambil dan sanitasi data
    $user_id = intval($_POST['id']);
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    // Update query
    $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, username = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Error preparing statement.']);
        exit();
    }
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $username, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating user.']);
    }

    $stmt->close();
}
$conn->close();
