<?php
require_once 'db.php';

if(!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM media WHERE id = ? AND expired_at > NOW()");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h2>Berita tidak ditemukan atau sudah kedaluwarsa.</h2><a href='index.php'>Kembali ke Beranda</a></div>");
}

$news = $result->fetch_assoc();

// Ambil berita terbaru untuk ticker
$ticker_query = "SELECT id, title FROM media WHERE expired_at > NOW() ORDER BY created_at DESC LIMIT 5";
$ticker_result = $conn->query($ticker_query);
$news_items = [];
while ($row = $ticker_result->fetch_assoc()) {
    $news_items[] = $row;
}

// Proses penambahan komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $name = !empty($_POST['name']) ? $_POST['name'] : 'Guest';
    $comment_text = $_POST['comment'];
    $stmt_comment = $conn->prepare("INSERT INTO comments (media_id, name, comment) VALUES (?, ?, ?)");
    $stmt_comment->bind_param("iss", $id, $name, $comment_text);
    $stmt_comment->execute();
    header("Location: detail.php?id=" . $id);
    exit;
}

// Ambil komentar untuk berita ini
$stmt_comments = $conn->prepare("SELECT * FROM comments WHERE media_id = ? ORDER BY created_at DESC");
$stmt_comments->bind_param("i", $id);
$stmt_comments->execute();
$comments_result = $stmt_comments->get_result();
$comments = [];
while ($row = $comments_result->fetch_assoc()) {
    $comments[] = $row;
}
$comment_count = count($comments);

// Ambil berita rekomendasi (Related News) kategori sama
$related_query = $conn->prepare("SELECT * FROM media WHERE expired_at > NOW() AND category = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
$related_query->bind_param("si", $news['category'], $id);
$related_query->execute();
$related_result = $related_query->get_result();
$related_news = [];
while ($row = $related_result->fetch_assoc()) {
    $related_news[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($news['title']); ?> | NaNews</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />
</head>
<body style="background: #fdfdfd;">

  <!-- ═══════════════════════════════════════════
       TOP BAR
  ═══════════════════════════════════════════ -->
  <div class="top-bar">
    <div class="top-bar-inner">
      <div class="top-bar-left">
        <span class="date-label"><?php echo date('l, F d, Y'); ?></span>
      </div>
      <div class="ticker-wrap">
        <span class="ticker-label">
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><polygon points="0,0 12,6 0,12" fill="#fff"/></svg>
          Trending
        </span>
        <div class="ticker-track" id="tickerTrack">
          <?php foreach($news_items as $n): ?>
            <a href="detail.php?id=<?php echo $n['id']; ?>" style="color: inherit; text-decoration: none; transition: 0.2s;" onmouseover="this.style.color='#e8192c'" onmouseout="this.style.color='inherit'"><?php echo htmlspecialchars($n['title']); ?></a> &nbsp;&nbsp;•&nbsp;&nbsp;
          <?php endforeach; ?>
        </div>
      </div>
      <div class="top-bar-right">
        <div class="social-icons">
          <a href="javascript:void(0)" onclick="alert('Menuju ke halaman Facebook')" aria-label="Facebook" class="soc-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          </a>
          <a href="javascript:void(0)" onclick="alert('Menuju ke halaman Twitter')" aria-label="Twitter" class="soc-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
          </a>
          <a href="javascript:void(0)" onclick="alert('Menuju ke halaman YouTube')" aria-label="YouTube" class="soc-icon">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75,15.02 15.5,12 9.75,8.98 9.75,15.02" fill="#c00"/></svg>
          </a>
        </div>
        <button class="subscribe-btn" onclick="alert('Terima kasih! Fitur berlangganan akan segera hadir.')">Subscribe</button>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════
       NAVBAR
  ═══════════════════════════════════════════ -->
  <header class="navbar" id="navbar">
    <div class="navbar-inner">
      <a href="index.php" class="logo">
        <span class="logo-mark">N</span>
        <div class="logo-text">
          <span class="logo-name">NaNews</span>
          <span class="logo-sub">NEWSPAPER</span>
        </div>
      </a>

      <nav class="nav-links" id="navLinks">
        <a href="index.php" class="nav-link">Home</a>
        <a href="index.php?category=Business" class="nav-link">Business</a>
        <a href="index.php?category=Sports" class="nav-link">Sports</a>
        <a href="index.php?category=Technology" class="nav-link">Technology</a>
        <a href="index.php?category=Fashion" class="nav-link">Fashion</a>
        <a href="index.php?category=Entertainment" class="nav-link">Entertainment</a>
        <a href="index.php?category=Politics" class="nav-link">Politics</a>
        <a href="index.php?category=World" class="nav-link">World</a>
      </nav>

      <div class="nav-actions">
        <button class="search-btn" id="searchBtn" aria-label="Search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </button>
        <button class="hamburger" id="hamburger" aria-label="Menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>

    <!-- Search Overlay -->
    <div class="search-overlay" id="searchOverlay">
      <form action="index.php" method="GET" class="search-box">
        <input type="text" name="search" placeholder="Cari judul atau isi berita..." id="searchInput" required />
        <button type="button" class="search-close" id="searchClose">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </form>
    </div>
  </header>

  <!-- ARTICLE DETAIL -->
  <div style="max-width: 900px; margin: 40px auto; padding: 0 20px;">
      <!-- Back to Homepage -->
      <a href="index.php" style="display: inline-flex; align-items: center; gap: 8px; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #111; text-decoration: none; margin-bottom: 30px; font-weight: 500; transition: 0.2s;" onmouseover="this.style.color='#e8192c'" onmouseout="this.style.color='#111'">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Homepage
      </a>

      <!-- Header Article -->
      <div style="margin-bottom: 25px; text-align: left;">
          <h1 style="font-family: 'DM Sans', sans-serif; font-size: 38px; line-height: 1.25; margin-bottom: 20px; color: #111; font-weight: 700; letter-spacing: -0.5px;"><?php echo htmlspecialchars($news['title']); ?></h1>
          
          <div style="color: #555; font-size: 14px; display: flex; align-items: center; gap: 10px; font-family: 'DM Sans', sans-serif;">
              <img src="https://ui-avatars.com/api/?name=Admin&background=111&color=fff" style="width: 28px; height: 28px; border-radius: 50%;" alt="Author">
              <span style="font-weight: 500; color: #111;">Admin</span>
              <span>•</span>
              <span><?php echo htmlspecialchars($news['category'] ?? 'News'); ?></span>
              <span>•</span>
              <span><?php echo date('M d, Y', strtotime($news['created_at'])); ?></span>
          </div>
      </div>
      
      <!-- Media -->
      <div style="border-radius: 16px; overflow: hidden; margin-bottom: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
          <?php if(strpos($news['file_type'], 'image') !== false): ?>
              <img src="<?php echo htmlspecialchars($news['file_path']); ?>" style="width: 100%; height: auto; max-height: 550px; object-fit: cover; display: block;" alt="news image">
          <?php else: ?>
              <video src="<?php echo htmlspecialchars($news['file_path']); ?>" style="width: 100%; display: block;" controls autoplay></video>
          <?php endif; ?>
      </div>
      
      <!-- Content -->
      <div style="font-family: 'DM Sans', sans-serif; font-size: 18px; line-height: 1.8; color: #333; margin-bottom: 60px;">
          <?php echo nl2br(htmlspecialchars($news['content'])); ?>
      </div>

      <!-- Interaction Bar (Comments & Likes UI) -->
      <div style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0; display: flex; align-items: center; justify-content: space-between; font-family: 'DM Sans', sans-serif; margin-bottom: 40px; flex-wrap: wrap; gap: 15px;">
          <div style="display: flex; gap: 20px; color: #444; font-size: 15px; font-weight: 500;">
              <div id="likeBtn" style="display: flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.color='#e8192c'" onmouseout="this.style.color='#444'" onclick="handleLike(<?php echo $id; ?>)">
                  <svg id="likeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                  <span id="likeCountText"><?php echo number_format($news['likes_count'] ?? 0); ?></span> Likes
              </div>
              <div style="display: flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.color='#e8192c'" onmouseout="this.style.color='#444'" onclick="document.getElementById('commentInput').focus();">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <?php echo $comment_count; ?> Comments
              </div>
              <div id="shareBtn" style="display: flex; align-items: center; gap: 6px; cursor: pointer; transition: 0.2s;" onmouseover="this.style.color='#e8192c'" onmouseout="this.style.color='#444'" onclick="handleShare(<?php echo $id; ?>)">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                  <span id="shareCountText"><?php echo number_format($news['shares_count'] ?? 0); ?></span> Shares
              </div>
          </div>
          
          <!-- Comment Form Input in Bar -->
          <form action="" method="POST" style="flex: 1; min-width: 250px; display: flex; align-items: center; background: #f5f5f5; border-radius: 30px; padding: 5px 15px;">
              <input type="text" name="name" placeholder="Name" required style="width: 80px; background: transparent; border: none; outline: none; font-family: 'DM Sans', sans-serif; font-size: 14px; border-right: 1px solid #ddd; padding-right: 10px; margin-right: 10px;">
              <input type="text" name="comment" id="commentInput" placeholder="Write a comment..." required style="flex: 1; background: transparent; border: none; outline: none; font-family: 'DM Sans', sans-serif; font-size: 14px;">
              <button type="submit" style="background: none; border: none; cursor: pointer; color: #111; padding: 5px; display: flex; align-items: center; justify-content: center; transition: 0.2s;" onmouseover="this.style.color='#e8192c'" onmouseout="this.style.color='#111'">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              </button>
          </form>
      </div>

      <!-- Comments List -->
      <?php if($comment_count > 0): ?>
      <div style="margin-bottom: 60px; font-family: 'DM Sans', sans-serif;">
          <h3 style="font-size: 20px; margin-bottom: 20px; font-weight: 700; color: #111;">Comments (<?php echo $comment_count; ?>)</h3>
          <div style="display: flex; flex-direction: column; gap: 20px;">
              <?php foreach($comments as $c): ?>
              <div style="display: flex; gap: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
                  <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($c['name']); ?>&background=random" style="width: 40px; height: 40px; border-radius: 50%;" alt="Avatar">
                  <div>
                      <div style="display: flex; align-items: baseline; gap: 10px; margin-bottom: 5px;">
                          <strong style="font-size: 15px; color: #111;"><?php echo htmlspecialchars($c['name']); ?></strong>
                          <span style="font-size: 12px; color: #888;"><?php echo date('M d, Y • h:i A', strtotime($c['created_at'])); ?></span>
                      </div>
                      <p style="margin: 0; font-size: 15px; color: #444; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>
                  </div>
              </div>
              <?php endforeach; ?>
          </div>
      </div>
      <?php endif; ?>

      <!-- Recommended News -->
      <?php if(count($related_news) > 0): ?>
      <div style="margin-top: 60px; padding-top: 40px; border-top: 2px solid #111;">
          <h2 style="font-family: 'DM Sans', sans-serif; font-size: 24px; font-weight: 700; color: #111; margin-bottom: 25px;">Related News</h2>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
              <?php foreach($related_news as $rn): ?>
              <a href="detail.php?id=<?php echo $rn['id']; ?>" style="text-decoration: none; color: inherit; display: block;">
                  <div style="border-radius: 12px; overflow: hidden; margin-bottom: 15px; aspect-ratio: 16/9;">
                      <?php if(strpos($rn['file_type'], 'image') !== false): ?>
                          <img src="<?php echo htmlspecialchars($rn['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                      <?php else: ?>
                          <video src="<?php echo htmlspecialchars($rn['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;"></video>
                      <?php endif; ?>
                  </div>
                  <h3 style="font-family: 'DM Sans', sans-serif; font-size: 18px; font-weight: 600; line-height: 1.4; margin-bottom: 10px; color: #111; transition: 0.2s;" onmouseover="this.style.color='#e8192c'" onmouseout="this.style.color='#111'"><?php echo htmlspecialchars($rn['title']); ?></h3>
                  <div style="font-size: 13px; color: #666; font-family: 'DM Sans', sans-serif;">
                      <?php echo date('M d, Y', strtotime($rn['created_at'])); ?>
                  </div>
              </a>
              <?php endforeach; ?>
          </div>
      </div>
      <?php endif; ?>
  </div>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <a href="index.php" class="logo logo--light">
          <span class="logo-mark logo-mark--light">N</span>
          <div class="logo-text">
            <span class="logo-name">NaNews</span>
            <span class="logo-sub">NEWSPAPER</span>
          </div>
        </a>
        <p>Delivering truth, depth, and clarity to readers around the world since 2010.</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© <?php echo date('Y'); ?> NaNews Newspaper. All rights reserved.</p>
    </div>
  </footer>

  <script src="script.js"></script>
  <script>
    // Memastikan Like/Share status dari localStorage
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const mediaId = urlParams.get('id');
        
        if (localStorage.getItem('liked_' + mediaId)) {
            document.getElementById('likeIcon').setAttribute('fill', '#e8192c');
            document.getElementById('likeIcon').setAttribute('stroke', '#e8192c');
            document.getElementById('likeBtn').style.color = '#e8192c';
        }
    });

    function handleLike(mediaId) {
        if (localStorage.getItem('liked_' + mediaId)) {
            alert('Anda sudah menyukai artikel ini!');
            return;
        }
        
        fetch('action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'like', media_id: mediaId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('likeCountText').innerText = data.new_count;
                localStorage.setItem('liked_' + mediaId, 'true');
                document.getElementById('likeIcon').setAttribute('fill', '#e8192c');
                document.getElementById('likeIcon').setAttribute('stroke', '#e8192c');
                document.getElementById('likeBtn').style.color = '#e8192c';
            }
        });
    }

    function handleShare(mediaId) {
        // Copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Link artikel berhasil disalin!');
        });

        if (localStorage.getItem('shared_' + mediaId)) {
            return; // Already shared, don't increment again but still copy link
        }

        fetch('action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'share', media_id: mediaId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('shareCountText').innerText = data.new_count;
                localStorage.setItem('shared_' + mediaId, 'true');
            }
        });
    }
  </script>
</body>
</html>
