document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Navigation (Hamburger Menu)
    const hamburger = document.getElementById('hamburger');
    const navLinks = document.getElementById('navLinks');

    if (hamburger && navLinks) {
        hamburger.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('open');
            hamburger.classList.toggle('active');
            
            // Animasi untuk hamburger line agar menjadi tanda silang (X)
            const spans = hamburger.querySelectorAll('span');
            if (spans.length === 3) {
                if (isOpen) {
                    spans[0].style.transform = 'translateY(8px) rotate(45deg)';
                    spans[1].style.opacity = '0';
                    spans[2].style.transform = 'translateY(-8px) rotate(-45deg)';
                } else {
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            }
        });

        // Menutup menu jika klik di luar navigasi
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('open');
                hamburger.classList.remove('active');
                const spans = hamburger.querySelectorAll('span');
                if (spans.length === 3) {
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            }
        });
    }

    // 2. Search Overlay
    const searchBtn = document.getElementById('searchBtn');
    const searchOverlay = document.getElementById('searchOverlay');
    const searchClose = document.getElementById('searchClose');
    const searchInput = document.getElementById('searchInput');

    if (searchBtn && searchOverlay && searchClose) {
        searchBtn.addEventListener('click', () => {
            searchOverlay.classList.add('open');
            document.body.style.overflow = 'hidden'; // Mencegah scrolling pada background
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        });

        const closeSearch = () => {
            searchOverlay.classList.remove('open');
            document.body.style.overflow = '';
            if (searchInput) searchInput.value = '';
        };

        searchClose.addEventListener('click', closeSearch);

        // Menutup overlay jika area luar search box diklik
        searchOverlay.addEventListener('click', (e) => {
            if (e.target === searchOverlay) {
                closeSearch();
            }
        });

        // Menutup menggunakan tombol Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && searchOverlay.classList.contains('open')) {
                closeSearch();
            }
        });
    }

    // 3. Trending News Tabs
    // Asumsi: tombol tab memiliki class '.tab' dan kontainer '#trendingTabs'
    // Item konten memiliki class '.trending-item' dan atribut 'data-category'
    const trendingTabs = document.querySelectorAll('#trendingTabs .tab, .trending-tab');
    const trendingItems = document.querySelectorAll('.trending-item');

    if (trendingTabs.length > 0 && trendingItems.length > 0) {
        trendingTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Hapus class active dari semua tab
                trendingTabs.forEach(t => t.classList.remove('active'));
                // Tambahkan class active ke tab yang diklik
                tab.classList.add('active');

                // Ambil kategori dari data-category atau teks di dalamnya
                const category = tab.getAttribute('data-category') || tab.textContent.toLowerCase().trim();

                // Filter konten
                trendingItems.forEach(item => {
                    const itemCategory = item.getAttribute('data-category') || 'all';
                    
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    
                    if (category === 'all' || itemCategory === category) {
                        item.style.display = 'flex'; // atau 'block' tergantung CSS Anda
                        // Sedikit jeda untuk memberikan efek animasi masuk
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(10px)';
                        // Tunggu animasi selesai baru di-hide
                        setTimeout(() => {
                            if (item.style.opacity === '0') {
                                item.style.display = 'none';
                            }
                        }, 300);
                    }
                });
            });
        });
    }

    // 4. Back to Top Button
    // Asumsi: elemen button memiliki id="backToTop" atau class="back-to-top"
    const backToTopBtn = document.getElementById('backToTop') || document.querySelector('.back-to-top');

    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('visible'); // Tambahkan CSS class visible (opacity 1)
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
