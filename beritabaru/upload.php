<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $content = $_POST['content'];
    $expired_at = $_POST['expired_at'];
    
    // Convert HTML5 datetime-local format (YYYY-MM-DDTHH:MM) to MySQL datetime (YYYY-MM-DD HH:MM:SS)
    $expired_at = str_replace('T', ' ', $expired_at) . ':00';
    
    $file = $_FILES['file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename to avoid overwriting
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_') . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        
        $file_type = mime_content_type($file['tmp_name']);
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $stmt = $conn->prepare("INSERT INTO media (title, category, content, file_path, file_type, expired_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $title, $category, $content, $target_path, $file_type, $expired_at);
            
            if ($stmt->execute()) {
                header("Location: admin.php?success=1");
                exit;
            } else {
                header("Location: admin.php?error=Database insert failed");
                exit;
            }
        } else {
            header("Location: admin.php?error=Failed to move uploaded file");
            exit;
        }
    } else {
        header("Location: admin.php?error=File upload error code: " . $file['error']);
        exit;
    }
} else {
    header("Location: admin.php");
    exit;
}
?>
