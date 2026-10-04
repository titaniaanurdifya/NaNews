<?php
require_once 'db.php';
// Ambil berita terbaru yang belum expired
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

if ($search_query) {
    $stmt = $conn->prepare("SELECT * FROM media WHERE expired_at > NOW() AND (title LIKE ? OR content LIKE ?) ORDER BY created_at DESC");
    $like_search = "%" . $search_query . "%";
    $stmt->bind_param("ss", $like_search, $like_search);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($category_filter) {
    $stmt = $conn->prepare("SELECT * FROM media WHERE expired_at > NOW() AND category = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $query = "SELECT * FROM media WHERE expired_at > NOW() ORDER BY created_at DESC";
    $result = $conn->query($query);
}
$news_items = [];
while ($row = $result->fetch_assoc()) {
    $news_items[] = $row;
}
$hero_news = count($news_items) > 0 ? $news_items[0] : null;
$other_news = count($news_items) > 1 ? array_slice($news_items, 1) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NaNews — Modern Newspaper</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet" />
</head>
<body>

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
        <a href="index.php" class="nav-link <?php echo empty($category_filter) ? 'active' : ''; ?>">Home</a>
        <a href="index.php?category=Business" class="nav-link <?php echo $category_filter === 'Business' ? 'active' : ''; ?>">Business</a>
        <a href="index.php?category=Sports" class="nav-link <?php echo $category_filter === 'Sports' ? 'active' : ''; ?>">Sports</a>
        <a href="index.php?category=Technology" class="nav-link <?php echo $category_filter === 'Technology' ? 'active' : ''; ?>">Technology</a>
        <a href="index.php?category=Fashion" class="nav-link <?php echo $category_filter === 'Fashion' ? 'active' : ''; ?>">Fashion</a>
        <a href="index.php?category=Entertainment" class="nav-link <?php echo $category_filter === 'Entertainment' ? 'active' : ''; ?>">Entertainment</a>
        <a href="index.php?category=Politics" class="nav-link <?php echo $category_filter === 'Politics' ? 'active' : ''; ?>">Politics</a>
        <a href="index.php?category=World" class="nav-link <?php echo $category_filter === 'World' ? 'active' : ''; ?>">World</a>
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

  <!-- ═══════════════════════════════════════════
       HERO SECTION
  ═══════════════════════════════════════════ -->
  <section class="hero">
    <?php if ($hero_news): ?>
    <div class="hero-main" onclick="window.location.href='detail.php?id=<?php echo $hero_news['id']; ?>'" style="cursor: pointer;">
      <?php if(strpos($hero_news['file_type'], 'image') !== false): ?>
          <div class="hero-bg" style="background-image: url('<?php echo htmlspecialchars($hero_news['file_path']); ?>');"></div>
      <?php else: ?>
          <div class="hero-bg" style="overflow:hidden;">
              <video src="<?php echo htmlspecialchars($hero_news['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top:0; left:0;" autoplay loop muted></video>
          </div>
      <?php endif; ?>
      <div class="hero-overlay"></div>

      <div class="hero-content">
        <div class="hero-meta">
          <span class="tag tag--red"><?php echo htmlspecialchars($hero_news['category'] ?? 'News'); ?></span>
          <span class="hero-date">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="12" rx="1.5"/><path d="M5 1v4M11 1v4M2 7h12"/></svg>
            <?php echo date('F d, Y', strtotime($hero_news['created_at'])); ?>
          </span>
        </div>
        <h1 class="hero-headline"><?php echo htmlspecialchars($hero_news['title']); ?></h1>
        <div class="hero-byline">
          <div class="author-avatar">
            <img src="https://i.pravatar.cc/40?img=3" alt="admin" />
          </div>
          <span class="hero-author">By <strong>admin</strong></span>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div style="padding: 150px 20px; text-align: center; color: #666; background: #f9f9f9; width: 100%;">
        <h2 style="font-family: 'Playfair Display', serif; font-size: 32px;">Belum Ada Berita Aktif</h2>
        <p style="font-family: 'DM Sans', sans-serif;">Silakan tambah berita melalui halaman admin.</p>
    </div>
    <?php endif; ?>
  </section>

  <!-- ═══════════════════════════════════════════
       GRID SECTION
  ═══════════════════════════════════════════ -->
  <?php if(count($other_news) > 0): ?>
  <section class="news-grid" style="padding: 60px 5%; max-width: 1400px; margin: 0 auto;">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #e8192c; padding-bottom: 10px;">
      <h2 style="font-family: 'Playfair Display', serif; font-size: 32px; margin: 0;">Berita Lainnya</h2>
      <div class="filter-tabs" id="trendingTabs" style="display: flex; gap: 10px;">
        <button class="tab active" data-category="all">All</button>
        <button class="tab" data-category="business">Business</button>
        <button class="tab" data-category="technology">Technology</button>
        <button class="tab" data-category="sports">Sports</button>
        <button class="tab" data-category="entertainment">Entertainment</button>
      </div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
        <?php foreach($other_news as $news): ?>
        <a href="detail.php?id=<?php echo $news['id']; ?>" class="trending-item" data-category="<?php echo strtolower($news['category'] ?? 'news'); ?>" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; background: #fff;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="height: 220px; position: relative;">
                <?php if(strpos($news['file_type'], 'image') !== false): ?>
                    <img src="<?php echo htmlspecialchars($news['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="news image">
                <?php else: ?>
                    <video src="<?php echo htmlspecialchars($news['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;"></video>
                <?php endif; ?>
                <span style="position: absolute; top: 15px; left: 15px; background: #e8192c; color: #fff; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 4px; text-transform: uppercase;"><?php echo htmlspecialchars($news['category'] ?? 'News'); ?></span>
            </div>
            <div style="padding: 20px;">
                <div style="font-size: 13px; color: #666; margin-bottom: 10px; display: flex; align-items: center; gap: 5px;">
                    <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="12" rx="1.5"/><path d="M5 1v4M11 1v4M2 7h12"/></svg>
                    <?php echo date('F d, Y', strtotime($news['created_at'])); ?>
                </div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 20px; line-height: 1.3; margin: 0;"><?php echo htmlspecialchars($news['title']); ?></h3>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════════
       FOOTER
  ═══════════════════════════════════════════ -->
  <footer class="footer" style="margin-top: 40px;">
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
      <div class="footer-bottom-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Cookie Settings</a>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
