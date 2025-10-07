<?php
require dirname(__DIR__) . '/config.php'; 
$conn = getConnection(); // Ensure this returns a valid MySQLi connection

if (isset($_GET['id'])) {
    $task_id = (int) $_GET['id'];

    // Prepared statement to mark task as done
    $stmt = $conn->prepare("UPDATE task SET status = 1 WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to task list
header("Location: ../task.php");
exit;
?>
