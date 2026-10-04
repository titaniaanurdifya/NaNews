<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM media WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: admin.php?error=Media not found");
    exit;
}

$media = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $content = $_POST['content'];
    $expired_at = $_POST['expired_at'];
    
    // Convert HTML5 datetime-local format (YYYY-MM-DDTHH:MM) to MySQL datetime (YYYY-MM-DD HH:MM:SS)
    $expired_at = str_replace('T', ' ', $expired_at);
    if (strlen($expired_at) == 16) {
        $expired_at .= ':00';
    }

    $update_query = "UPDATE media SET title = ?, category = ?, content = ?, expired_at = ? WHERE id = ?";
    $update_params = [$title, $category, $content, $expired_at, $id];
    $update_types = "ssssi";

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_') . '.' . $file_extension;
        $target_path = $upload_dir . $filename;
        $file_type = mime_content_type($file['tmp_name']);
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            // Delete old file
            if (file_exists($media['file_path'])) {
                unlink($media['file_path']);
            }
            $update_query = "UPDATE media SET title = ?, category = ?, content = ?, expired_at = ?, file_path = ?, file_type = ? WHERE id = ?";
            $update_params = [$title, $category, $content, $expired_at, $target_path, $file_type, $id];
            $update_types = "ssssssi";
        } else {
            $error = "Gagal mengupload file baru.";
        }
    } else if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
         $error = "Terjadi kesalahan saat mengupload file. Error code: " . $_FILES['file']['error'];
    }

    if (!isset($error)) {
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param($update_types, ...$update_params);
        if ($stmt->execute()) {
            header("Location: admin.php?success=1");
            exit;
        } else {
            $error = "Gagal menyimpan perubahan ke database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Media | NaNews Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #f4f7f6; margin: 0; padding: 0; color: #333; }
        .sidebar { width: 250px; background: #111; color: #fff; height: 100vh; position: fixed; top: 0; left: 0; padding: 20px; box-sizing: border-box;}
        .sidebar h2 { font-size: 24px; color: #e8192c; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }
        .sidebar a { display: block; color: #aaa; text-decoration: none; padding: 12px 15px; margin-bottom: 10px; border-radius: 5px; transition: 0.3s; font-weight: 500;}
        .sidebar a:hover, .sidebar a.active { background: #e8192c; color: #fff; }
        
        .main-content { margin-left: 250px; padding: 40px; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { margin: 0; font-size: 28px; }
        
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 30px; max-width: 600px; }
        .card h3 { margin-top: 0; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px;}
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; box-sizing: border-box; font-family: 'DM Sans', sans-serif;}
        .form-group input:focus, .form-group textarea:focus { border-color: #e8192c; }
        
        .btn { background: #e8192c; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif; transition: 0.2s; display: inline-block; text-decoration: none;}
        .btn:hover { background: #c00; transform: translateY(-1px); }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        
        .media-preview { max-width: 100%; height: auto; max-height: 300px; border-radius: 8px; margin-top: 10px; object-fit: cover;}
        
        .alert { padding: 15px; background: #f8d7da; color: #721c24; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #dc3545;}
    </style>
</head>
<body>

<div class="sidebar">
    <h2><span style="background: #e8192c; color: #fff; padding: 0px 9px; border-radius: 4px;">N</span> NaNews</h2>
    <a href="admin.php">Dashboard Media</a>
    <a href="index.php" target="_blank">Lihat Website</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Edit Media</h1>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>Ubah Data Media</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul / Nama Media</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($media['title']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Kategori Berita</label>
                <?php $cat = $media['category'] ?? ''; ?>
                <select id="category" name="category" required>
                    <option value="" disabled <?php echo $cat === '' ? 'selected' : ''; ?>>Pilih Kategori</option>
                    <option value="Business" <?php echo $cat === 'Business' ? 'selected' : ''; ?>>Business</option>
                    <option value="Sports" <?php echo $cat === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                    <option value="Technology" <?php echo $cat === 'Technology' ? 'selected' : ''; ?>>Technology</option>
                    <option value="Fashion" <?php echo $cat === 'Fashion' ? 'selected' : ''; ?>>Fashion</option>
                    <option value="Entertainment" <?php echo $cat === 'Entertainment' ? 'selected' : ''; ?>>Entertainment</option>
                    <option value="Politics" <?php echo $cat === 'Politics' ? 'selected' : ''; ?>>Politics</option>
                    <option value="World" <?php echo $cat === 'World' ? 'selected' : ''; ?>>World</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content">Isi Berita</label>
                <textarea id="content" name="content" rows="4" required><?php echo htmlspecialchars($media['content']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Media Saat Ini</label>
                <?php if(strpos($media['file_type'], 'image') !== false): ?>
                    <img src="<?php echo htmlspecialchars($media['file_path']); ?>" class="media-preview" alt="preview">
                <?php else: ?>
                    <video src="<?php echo htmlspecialchars($media['file_path']); ?>" class="media-preview" controls></video>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="file">Ganti File (Foto / Video)</label>
                <input type="file" id="file" name="file" accept="image/*,video/*">
                <small style="color: #666; display: block; margin-top: 5px;">*Opsional. Biarkan kosong jika tidak ingin mengubah file media.</small>
            </div>
            
            <div class="form-group">
                <label for="expired_at">Tanggal & Waktu Berakhir (Expired)</label>
                <?php 
                    $dt = new DateTime($media['expired_at']);
                    $dt_string = $dt->format('Y-m-d\TH:i');
                ?>
                <input type="datetime-local" id="expired_at" name="expired_at" value="<?php echo $dt_string; ?>" required>
            </div>
            <button type="submit" class="btn">Simpan Perubahan</button>
            <a href="admin.php" class="btn btn-secondary" style="margin-left: 10px;">Batal</a>
        </form>
    </div>
</div>

</body>
</html>
