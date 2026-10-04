<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action']) || !isset($data['media_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$action = $data['action'];
$media_id = intval($data['media_id']);

if ($action === 'like') {
    $stmt = $conn->prepare("UPDATE media SET likes_count = likes_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $media_id);
    if ($stmt->execute()) {
        $res = $conn->query("SELECT likes_count FROM media WHERE id = $media_id");
        $row = $res->fetch_assoc();
        echo json_encode(['success' => true, 'new_count' => $row['likes_count']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} elseif ($action === 'share') {
    $stmt = $conn->prepare("UPDATE media SET shares_count = shares_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $media_id);
    if ($stmt->execute()) {
        $res = $conn->query("SELECT shares_count FROM media WHERE id = $media_id");
        $row = $res->fetch_assoc();
        echo json_encode(['success' => true, 'new_count' => $row['shares_count']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
