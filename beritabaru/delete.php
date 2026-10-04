<?php
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // First, get the file path to delete it from the server
    $stmt = $conn->prepare("SELECT file_path FROM media WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $file_path = $row['file_path'];
        
        // Delete from database
        $del_stmt = $conn->prepare("DELETE FROM media WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        
        if ($del_stmt->execute()) {
            // Delete actual file if exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            header("Location: admin.php?success=1");
            exit;
        } else {
            header("Location: admin.php?error=Failed to delete from database");
            exit;
        }
    } else {
        header("Location: admin.php?error=Media not found");
        exit;
    }
} else {
    header("Location: admin.php");
    exit;
}
?>
