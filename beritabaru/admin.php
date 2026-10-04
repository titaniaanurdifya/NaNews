<?php
require_once 'db.php';
$result = $conn->query("SELECT * FROM media ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | NaNews Admin</title>
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
        
        .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); margin-bottom: 30px; }
        .card h3 { margin-top: 0; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px;}
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; box-sizing: border-box; font-family: 'DM Sans', sans-serif;}
        .form-group input:focus, .form-group textarea:focus { border-color: #e8192c; }
        
        .btn { background: #e8192c; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: 'DM Sans', sans-serif; transition: 0.2s;}
        .btn:hover { background: #c00; transform: translateY(-1px); }
        .btn-danger { background: #dc3545; padding: 8px 15px; font-size: 13px; text-decoration: none; }
        .btn-danger:hover { background: #a71d2a; }
        
        .btn-edit { background: #0d6efd; padding: 8px 15px; font-size: 13px; text-decoration: none; margin-right: 5px; }
        .btn-edit:hover { background: #0b5ed7; }
        
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        table th { background: #fcfcfc; font-weight: 600; color: #555; }
        table tr:hover td { background: #fafafa; }
        .media-preview { width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;}
        
        .alert { padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #28a745;}
        .alert.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545;}
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge.active { background: #e6f4ea; color: #1e8e3e; }
        .badge.expired { background: #fce8e6; color: #d93025; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><span style="background: #e8192c; color: #fff; padding: 0px 9px; border-radius: 4px;">N</span> NaNews</h2>
    <a href="admin.php" class="active">Dashboard Media</a>
    <a href="index.php" target="_blank">Lihat Website</a>
</div>

<div class="main-content">
    <div class="header">
        <h1>Dashboard Media</h1>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert">Operasi berhasil dilakukan!</div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert error">Terjadi kesalahan: <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>Upload Media Baru</h3>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul / Nama Media</label>
                <input type="text" id="title" name="title" placeholder="Contoh: Banner Promo Lebaran" required>
            </div>
            <div class="form-group">
                <label for="category">Kategori Berita</label>
                <select id="category" name="category" required>
                    <option value="" disabled selected>Pilih Kategori</option>
                    <option value="Business">Business</option>
                    <option value="Sports">Sports</option>
                    <option value="Technology">Technology</option>
                    <option value="Fashion">Fashion</option>
                    <option value="Entertainment">Entertainment</option>
                    <option value="Politics">Politics</option>
                    <option value="World">World</option>
                </select>
            </div>
            <div class="form-group">
                <label for="content">Isi Berita</label>
                <textarea id="content" name="content" placeholder="Masukkan isi berita di sini..." rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="file">File (Foto / Video)</label>
                <input type="file" id="file" name="file" accept="image/*,video/*" required>
            </div>
            <div class="form-group">
                <label for="expired_at">Tanggal & Waktu Berakhir (Expired)</label>
                <input type="datetime-local" id="expired_at" name="expired_at" required>
            </div>
            <button type="submit" class="btn">Upload Media</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Media</h3>
        <table>
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Tipe</th>
                    <th>Kedaluwarsa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $is_expired = strtotime($row['expired_at']) < time();
                    ?>
                    <tr>
                        <td>
                            <?php if(strpos($row['file_type'], 'image') !== false): ?>
                                <img src="<?php echo htmlspecialchars($row['file_path']); ?>" class="media-preview" alt="preview">
                            <?php else: ?>
                                <video src="<?php echo htmlspecialchars($row['file_path']); ?>" class="media-preview" muted></video>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><span class="badge" style="background:#eee;color:#333;"><?php echo htmlspecialchars($row['category'] ?? '-'); ?></span></td>
                        <td><?php echo htmlspecialchars($row['file_type']); ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($row['expired_at'])); ?></td>
                        <td>
                            <?php if($is_expired): ?>
                                <span class="badge expired">Expired</span>
                            <?php else: ?>
                                <span class="badge active">Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus media ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #888;">Belum ada media yang diunggah.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
