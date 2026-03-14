<?php
require_once __DIR__ . '/config.php';
$pdo = db();

// Fetch all news from the database, newest first
$stmt = $pdo->query("SELECT * FROM school_news ORDER BY date_posted DESC");
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School News | Bucal National Integrated School</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5a0000;
            --gold: #f0c040;
            --cream: #FFFDF5;
            --white: #ffffff;
            --gray-900: #111827;
            --gray-800: #1f2937;
            --gray-700: #374151;
            --gray-600: #4b5563;
            --gray-500: #6b7280;
            --gray-400: #9ca3af;
            --gray-200: #e5e7eb;
            --gray-100: #f3f4f6;
            --gray-50: #f9fafb;
            --green-dark: #006400;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.10);
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.14);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.18);
            --shadow-maroon: 0 8px 32px rgba(128,0,0,0.25);
            --radius-sm: 8px; --radius-md: 16px; --radius-lg: 24px; --radius-xl: 32px; --radius-full: 9999px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', sans-serif; background: var(--white); color: var(--gray-800); line-height: 1.6; overflow-x: hidden; }
        h1,h2,h3,h4 { font-family: 'Playfair Display', serif; line-height: 1.25; }
        a { text-decoration: none; color: inherit; }
        img { display: block; max-width: 100%; }

        /* NAVBAR */
        .navbar { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.98); backdrop-filter: blur(16px); border-bottom: 3px solid var(--maroon); padding: 0.85rem 3rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 20px rgba(128,0,0,0.08); }
        .nav-brand { display: flex; align-items: center; gap: 0.85rem; }
        .nav-brand img { height: 46px; width: 46px; object-fit: contain; border-radius: 50%; }
        .nav-brand-name { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.02em; color: var(--maroon); line-height: 1.1; }
        .nav-brand-sub { font-size: 0.6rem; font-weight: 500; color: var(--gray-500); letter-spacing: 0.22em; text-transform: uppercase; }
        .nav-links { display: flex; align-items: center; gap: 2rem; font-size: 0.85rem; font-weight: 600; }
        .nav-links a { color: var(--gray-600); transition: color 0.2s; position: relative; padding-bottom: 2px; }
        .nav-links a::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: var(--maroon); transition: width 0.25s; border-radius: 2px; }
        .nav-links a:hover { color: var(--maroon); }
        .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
        .nav-links a.active { color: var(--maroon); }
        .nav-btn { background: var(--maroon) !important; color: var(--white) !important; padding: 0.5rem 1.4rem !important; border-radius: var(--radius-full) !important; transition: all 0.2s !important; box-shadow: var(--shadow-sm); }
        .nav-btn::after { display: none !important; }
        .nav-btn:hover { background: var(--maroon-dark) !important; transform: translateY(-1px); box-shadow: var(--shadow-maroon) !important; }
        .hamburger { display: none; background: none; border: none; cursor: pointer; color: var(--maroon); padding: 0.25rem; }
        .mobile-menu { display: none; position: fixed; inset: 0; z-index: 200; background: var(--white); flex-direction: column; justify-content: center; align-items: center; gap: 2rem; transform: translateX(100%); transition: transform 0.35s cubic-bezier(0.4,0,0.2,1); }
        .mobile-menu.open { transform: translateX(0); }
        .mobile-menu-close { position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; cursor: pointer; color: var(--gray-800); }
        .mobile-menu a { font-size: 1.6rem; font-family: 'Playfair Display', serif; color: var(--maroon); font-weight: 700; }
        .mobile-menu a.secondary { font-size: 1.2rem; font-family: 'DM Sans', sans-serif; color: var(--gray-600); font-weight: 500; }

        /* PAGE HERO */
        .page-hero { background: var(--maroon); color: var(--white); padding: 5rem 3rem 4rem; position: relative; overflow: hidden; }
        .page-hero::before { content: ''; position: absolute; inset: 0; opacity: 0.05; background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); }
        .page-hero::after { content: ''; position: absolute; right: -10rem; top: -10rem; width: 32rem; height: 32rem; background: radial-gradient(circle, rgba(240,192,64,0.15) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
        .page-hero-inner { max-width: 80rem; margin: 0 auto; position: relative; z-index: 1; }
        .page-hero-eyebrow { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; color: var(--gold); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .page-hero-eyebrow::before { content: ''; display: inline-block; width: 2rem; height: 2px; background: var(--gold); }
        .page-hero h1 { font-size: clamp(2.2rem, 4vw, 3.5rem); font-weight: 800; margin-bottom: 1rem; }
        .page-hero p { font-size: 1rem; opacity: 0.8; max-width: 40rem; line-height: 1.75; }
        .page-hero-meta { display: flex; gap: 2rem; margin-top: 2rem; flex-wrap: wrap; }
        .page-hero-meta-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; opacity: 0.7; }
        .page-hero-meta-item svg { width: 1rem; height: 1rem; color: var(--gold); opacity: 1; }

        /* CONTENT */
        .page-content { max-width: 80rem; margin: 0 auto; padding: 4rem 3rem; }

        /* FEATURED NEWS */
        .featured-news { display: grid; grid-template-columns: 1.4fr 1fr; gap: 2rem; margin-bottom: 4rem; }
        .featured-card { background: var(--white); border-radius: var(--radius-xl); overflow: hidden; border: 1.5px solid var(--gray-200); transition: all 0.3s; cursor: pointer; }
        .featured-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-3px); border-color: var(--maroon); }
        .featured-card-img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
        .featured-card-body { padding: 1.75rem; }
        .news-badge { display: inline-block; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; padding: 0.25rem 0.7rem; border-radius: var(--radius-full); margin-bottom: 0.85rem; }
        .news-badge.academic { background: #fee2e2; color: var(--maroon); }
        .news-badge.sports { background: #d1fae5; color: var(--green-dark); }
        .news-badge.community { background: #fef3c7; color: #92400e; }
        .news-badge.admin { background: #ede9fe; color: #5b21b6; }
        .news-badge.culture { background: #fce7f3; color: #9d174d; }
        .featured-card-body h2 { font-size: 1.4rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.6rem; line-height: 1.3; }
        .featured-card-body h3 { font-size: 1.05rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; line-height: 1.35; }
        .card-excerpt { color: var(--gray-500); font-size: 0.875rem; line-height: 1.75; margin-bottom: 1.2rem; }
        .card-meta { display: flex; align-items: center; gap: 1rem; font-size: 0.75rem; color: var(--gray-400); flex-wrap: wrap; }
        .card-meta-item { display: flex; align-items: center; gap: 0.3rem; }
        .card-meta-item svg { width: 0.85rem; height: 0.85rem; }
        .read-more { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; font-weight: 700; color: var(--maroon); text-transform: uppercase; letter-spacing: 0.08em; transition: gap 0.2s; margin-top: 0.5rem; }
        .read-more:hover { gap: 0.7rem; }
        .read-more svg { width: 0.9rem; height: 0.9rem; }

        /* SIDE STACK */
        .featured-side { display: flex; flex-direction: column; gap: 1.25rem; }
        .side-card { background: var(--white); border-radius: var(--radius-lg); overflow: hidden; border: 1.5px solid var(--gray-200); display: flex; gap: 1rem; padding: 1.1rem; transition: all 0.3s; cursor: pointer; }
        .side-card:hover { box-shadow: var(--shadow-md); border-color: var(--maroon); transform: translateX(3px); }
        .side-card-img { width: 90px; height: 90px; border-radius: var(--radius-sm); object-fit: cover; flex-shrink: 0; }
        .side-card-body { flex: 1; min-width: 0; }

        /* FILTER BAR */
        .filter-bar { display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 2.5rem; align-items: center; }
        .filter-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.15em; color: var(--gray-500); margin-right: 0.5rem; }
        .filter-btn { padding: 0.4rem 1rem; border-radius: var(--radius-full); border: 1.5px solid var(--gray-200); background: var(--white); color: var(--gray-600); font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: all 0.2s; font-family: 'DM Sans', sans-serif; }
        .filter-btn:hover, .filter-btn.active { background: var(--maroon); color: var(--white); border-color: var(--maroon); }

        /* NEWS GRID */
        .section-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .section-eyebrow { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; color: var(--maroon); margin-bottom: 0.4rem; display: block; }
        .section-title { font-size: clamp(1.4rem, 2.5vw, 2rem); font-weight: 800; color: var(--gray-900); }
        .news-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.75rem; margin-bottom: 3rem; }
        .news-card { background: var(--white); border-radius: var(--radius-lg); overflow: hidden; border: 1.5px solid var(--gray-200); transition: all 0.3s; cursor: pointer; }
        .news-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--maroon); }
        .news-card-img { width: 100%; aspect-ratio: 16/10; object-fit: cover; }
        .news-card-body { padding: 1.4rem; }
        .news-card-body h3 { font-size: 1.05rem; font-weight: 700; color: var(--gray-900); margin-bottom: 0.5rem; line-height: 1.35; }

        /* ANNOUNCEMENT SIDEBAR */
        .content-with-sidebar { display: grid; grid-template-columns: 1fr 300px; gap: 3rem; align-items: start; }
        .sidebar { position: sticky; top: 5rem; }
        .sidebar-card { background: var(--gray-50); border-radius: var(--radius-lg); border: 1.5px solid var(--gray-200); overflow: hidden; margin-bottom: 1.5rem; }
        .sidebar-header { background: var(--maroon); color: var(--white); padding: 1rem 1.4rem; }
        .sidebar-header h3 { font-size: 0.95rem; font-weight: 700; }
        .sidebar-body { padding: 1.2rem; }
        .announcement-item { padding: 0.85rem 0; border-bottom: 1px solid var(--gray-200); }
        .announcement-item:last-child { border-bottom: none; padding-bottom: 0; }
        .announcement-item .date { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--maroon); margin-bottom: 0.3rem; }
        .announcement-item p { font-size: 0.82rem; color: var(--gray-700); line-height: 1.6; }
        .tag-cloud { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .tag { display: inline-block; padding: 0.3rem 0.75rem; background: var(--gray-100); border-radius: var(--radius-full); font-size: 0.72rem; font-weight: 600; color: var(--gray-600); cursor: pointer; transition: all 0.2s; }
        .tag:hover { background: var(--maroon); color: var(--white); }

        /* MODAL */
        .modal { position: fixed; inset: 0; z-index: 500; background: rgba(0,0,0,0.85); display: flex; align-items: center; justify-content: center; padding: 2rem; opacity: 0; pointer-events: none; transition: opacity 0.3s; backdrop-filter: blur(6px); }
        .modal.open { opacity: 1; pointer-events: all; }
        .modal-inner { max-width: 760px; width: 100%; background: var(--white); border-radius: var(--radius-xl); overflow: hidden; max-height: 90vh; overflow-y: auto; transform: scale(0.93) translateY(16px); transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1); }
        .modal.open .modal-inner { transform: scale(1) translateY(0); }
        .modal-img { width: 100%; aspect-ratio: 16/9; object-fit: cover; }
        .modal-body { padding: 2rem 2.2rem; }
        .modal-badge-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
        .modal-title { font-size: 1.6rem; font-weight: 800; color: var(--gray-900); margin-bottom: 0.75rem; line-height: 1.25; }
        .modal-meta { display: flex; gap: 1.2rem; font-size: 0.78rem; color: var(--gray-400); margin-bottom: 1.4rem; flex-wrap: wrap; }
        .modal-meta-item { display: flex; align-items: center; gap: 0.3rem; }
        .modal-meta-item svg { width: 0.9rem; height: 0.9rem; }
        .modal-content p { color: var(--gray-600); font-size: 0.9rem; line-height: 1.85; margin-bottom: 1rem; }
        .modal-close { position: absolute; top: 1rem; right: 1rem; background: rgba(0,0,0,0.5); color: #fff; border: none; width: 2.5rem; height: 2.5rem; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; z-index: 10; }
        .modal-close:hover { background: var(--maroon); }
        .modal-close svg { width: 1.2rem; height: 1.2rem; }
        .modal-img-wrap { position: relative; }

        /* FOOTER */
        .footer { background: #0f0f0f; color: var(--white); padding: 4rem 3rem 2rem; border-top: 6px solid var(--maroon); }
        .footer-inner { max-width: 80rem; margin: 0 auto; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 2rem; margin-bottom: 2.5rem; }
        .footer-logo-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .footer-logo-wrap { background: var(--white); padding: 0.4rem; border-radius: 50%; }
        .footer-logo-wrap img { height: 3rem; width: 3rem; object-fit: contain; }
        .footer-logo-name { font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 700; }
        .footer-logo-loc { font-size: 0.68rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.22em; }
        .footer-bottom { max-width: 80rem; margin: 0 auto; padding-top: 1.5rem; border-top: 1px solid #222; display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--gray-500); flex-wrap: wrap; gap: 0.5rem; }

        @media (max-width: 1024px) {
            .navbar { padding: 0.85rem 1.5rem; }
            .featured-news { grid-template-columns: 1fr; }
            .featured-side { display: grid; grid-template-columns: repeat(2,1fr); }
            .content-with-sidebar { grid-template-columns: 1fr; }
            .sidebar { position: static; }
            .news-grid { grid-template-columns: repeat(2,1fr); }
            .page-content { padding: 3rem 1.5rem; }
        }
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hamburger { display: block; }
            .mobile-menu { display: flex; }
            .page-hero { padding: 3rem 1.5rem; }
            .featured-side { grid-template-columns: 1fr; }
            .news-grid { grid-template-columns: 1fr; }
            .footer { padding: 3rem 1.25rem 1.5rem; }
            .footer-inner { flex-direction: column; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <img src="BNIS_logo-removebg-preview.png" alt="BNIS Logo">
        <div>
            <div class="nav-brand-name">Bucal National</div>
            <div class="nav-brand-sub">Integrated School</div>
        </div>
    </div>
    <div class="nav-links">
        <a href="index.html">Home</a>
        <a href="Enrollment.html">Enrollment</a>
        <a href="SchoolNews.php" class="active">School News</a>
        <a href="CampusUpdates.html">Campus Updates</a>
        <a href="Admin.html" class="nav-btn">Our Admin</a>
    </div>
    <button class="hamburger" id="mobileMenuBtn" aria-label="Open menu">
        <svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <button class="mobile-menu-close" id="closeMenuBtn"><svg style="width:2rem;height:2rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
    <a href="index.html">Home</a>
    <a href="Enrollment.html" class="secondary">Enrollment</a>
    <a href="SchoolNews.php" class="secondary">School News</a>
    <a href="CampusUpdates.html" class="secondary">Campus Updates</a>
    <a href="Admin.html" class="secondary">Our Admin</a>
</div>

<section class="page-hero">
    <div class="page-hero-inner">
        <div class="page-hero-eyebrow">BNIS Community</div>
        <h1>School News</h1>
        <p>Stay informed with the latest stories, achievements, announcements, and updates from Bucal National Integrated School.</p>
        <div class="page-hero-meta">
            <div class="page-hero-meta-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                School Year 2025–2026
            </div>
            <div class="page-hero-meta-item">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"/></svg>
                Latest stories &amp; highlights
            </div>
        </div>
    </div>
</section>

<div class="page-content">

    <div style="margin-bottom:1rem;">
        <span class="section-eyebrow">Top Stories</span>
        <h2 class="section-title">Featured News</h2>
    </div>

    <?php if (count($news) > 0): ?>
    <div class="featured-news" style="margin-bottom:3.5rem;">
        <?php $n0 = $news[0]; ?>
        <div class="featured-card" onclick="openModal(0)">
            <img class="featured-card-img" src="<?= htmlspecialchars($n0['image_path']) ?>" alt="<?= htmlspecialchars($n0['title']) ?>">
            <div class="featured-card-body">
                <span class="news-badge <?= htmlspecialchars($n0['category']) ?>"><?= ucfirst(htmlspecialchars($n0['category'])) ?></span>
                <h2><?= htmlspecialchars($n0['title']) ?></h2>
                <p class="card-excerpt"><?= htmlspecialchars($n0['excerpt']) ?></p>
                <div class="card-meta">
                    <span class="card-meta-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><?= date('M j, Y', strtotime($n0['date_posted'])) ?></span>
                    <span class="card-meta-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><?= htmlspecialchars($n0['author']) ?></span>
                </div>
                <span class="read-more">Read Full Story <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg></span>
            </div>
        </div>
        <div class="featured-side">
            <?php for($i = 1; $i <= 3; $i++): if(isset($news[$i])): $n = $news[$i]; ?>
            <div class="side-card" onclick="openModal(<?= $i ?>)">
                <img class="side-card-img" src="<?= htmlspecialchars($n['image_path']) ?>" alt="<?= htmlspecialchars($n['title']) ?>">
                <div class="side-card-body">
                    <span class="news-badge <?= htmlspecialchars($n['category']) ?>"><?= ucfirst(htmlspecialchars($n['category'])) ?></span>
                    <h3 style="font-size:0.9rem;font-weight:700;color:var(--gray-900);margin-bottom:0.3rem;line-height:1.3;"><?= htmlspecialchars($n['title']) ?></h3>
                    <div class="card-meta"><span class="card-meta-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><?= date('M j, Y', strtotime($n['date_posted'])) ?></span></div>
                </div>
            </div>
            <?php endif; endfor; ?>
        </div>
    </div>
    <?php else: ?>
        <p style="margin-bottom: 4rem; color: var(--gray-500);">No news articles have been published yet. Please add them in the Admin Dashboard.</p>
    <?php endif; ?>

    <div class="content-with-sidebar">
        <div>
            <div class="filter-bar">
                <span class="filter-label">Filter:</span>
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="academic">Academic</button>
                <button class="filter-btn" data-filter="sports">Sports</button>
                <button class="filter-btn" data-filter="community">Community</button>
                <button class="filter-btn" data-filter="culture">Culture</button>
                <button class="filter-btn" data-filter="admin">Admin</button>
            </div>

            <div class="section-header">
                <div>
                    <span class="section-eyebrow">All Stories</span>
                    <h2 class="section-title">Latest from BNIS</h2>
                </div>
            </div>

            <div class="news-grid" id="newsGrid">
                </div>
        </div>

        <aside class="sidebar">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3>📢 Announcements</h3>
                </div>
                <div class="sidebar-body">
                    <div class="announcement-item">
                        <div class="date">Mar 11, 2026</div>
                        <p>NAT Review classes begin for Grade 6 & Grade 10 starting March 15.</p>
                    </div>
                    <div class="announcement-item">
                        <div class="date">Mar 5, 2026</div>
                        <p>Card-giving ceremony on March 20. Parents are encouraged to attend.</p>
                    </div>
                    <div class="announcement-item">
                        <div class="date">Feb 28, 2026</div>
                        <p>No classes on March 4 in observance of Teacher Wellness Day.</p>
                    </div>
                    <div class="announcement-item">
                        <div class="date">Feb 18, 2026</div>
                        <p>Enrollment for SY 2026–2027 opens April 1 for continuing students.</p>
                    </div>
                </div>
            </div>

            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h3>🏷️ Browse by Topic</h3>
                </div>
                <div class="sidebar-body">
                    <div class="tag-cloud">
                        <span class="tag">Academics</span>
                        <span class="tag">Sports</span>
                        <span class="tag">Community</span>
                        <span class="tag">Honors</span>
                        <span class="tag">Events</span>
                        <span class="tag">Faculty</span>
                        <span class="tag">Enrollment</span>
                        <span class="tag">Arts</span>
                        <span class="tag">Health</span>
                        <span class="tag">Technology</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="modal" id="modal">
    <div class="modal-inner">
        <div class="modal-img-wrap">
            <img class="modal-img" id="modalImg" src="" alt="">
            <button class="modal-close" id="modalClose"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="modal-body">
            <div class="modal-badge-row">
                <span class="news-badge" id="modalBadge"></span>
            </div>
            <div class="modal-title" id="modalTitle"></div>
            <div class="modal-meta">
                <span class="modal-meta-item" id="modalDate"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span id="modalDateText"></span></span>
                <span class="modal-meta-item" id="modalAuthor"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span id="modalAuthorText"></span></span>
            </div>
            <div class="modal-content" id="modalContent"></div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-inner">
        <div>
            <div class="footer-logo-row">
                <div class="footer-logo-wrap"><img src="BNIS_logo-removebg-preview.png" alt="BNIS Logo"></div>
                <div>
                    <div class="footer-logo-name">Bucal 2</div>
                    <div class="footer-logo-loc">Maragondon, Cavite</div>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:3rem;flex-wrap:wrap;">
            <div>
                <p style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.2em;color:var(--gray-500);margin-bottom:0.75rem;font-weight:600;">Navigation</p>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <a href="index.html" style="color:var(--gray-400);font-size:0.85rem;">Home</a>
                    <a href="Enrollment.html" style="color:var(--gray-400);font-size:0.85rem;">Enrollment</a>
                    <a href="SchoolNews.php" style="color:var(--gold);font-size:0.85rem;">School News</a>
                    <a href="CampusUpdates.html" style="color:var(--gray-400);font-size:0.85rem;">Campus Updates</a>
                    <a href="Admin.html" style="color:var(--gray-400);font-size:0.85rem;">Our Admin</a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Developed by: Roise Ivan M. Mendoza | BNIS Alumni</p>
        <p>&copy; 2026 Bucal National Integrated School</p>
    </div>
</footer>

<script>
// Load dynamic PHP data directly into the JavaScript array!
const allNews = <?php 
    $jsNews = [];
    foreach($news as $i => $n) {
        $jsNews[] = [
            'id' => $i,
            'category' => $n['category'],
            'badge' => ucfirst($n['category']),
            'badgeClass' => $n['category'],
            'img' => $n['image_path'],
            'title' => $n['title'],
            'date' => date('F j, Y', strtotime($n['date_posted'])),
            'author' => $n['author'],
            'excerpt' => $n['excerpt'],
            'content' => $n['content']
        ];
    }
    echo json_encode($jsNews);
?>;

let activeFilter = 'all';

function renderGrid() {
    const grid = document.getElementById('newsGrid');
    const filtered = activeFilter === 'all' ? allNews : allNews.filter(n => n.category === activeFilter);
    
    if(filtered.length === 0) {
        grid.innerHTML = '<p style="color:var(--gray-500); grid-column: span 3;">No news articles match this filter.</p>';
        return;
    }

    grid.innerHTML = filtered.map(n => `
        <div class="news-card" onclick="openModal(${n.id})">
            <img class="news-card-img" src="${n.img}" alt="${n.title}">
            <div class="news-card-body">
                <span class="news-badge ${n.badgeClass}">${n.badge}</span>
                <h3>${n.title}</h3>
                <p class="card-excerpt" style="margin-bottom:0.8rem;">${n.excerpt}</p>
                <div class="card-meta">
                    <span class="card-meta-item"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>${n.date}</span>
                </div>
            </div>
        </div>
    `).join('');
}

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        activeFilter = btn.dataset.filter;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderGrid();
    });
});

const modal = document.getElementById('modal');
function openModal(id) {
    const n = allNews[id];
    document.getElementById('modalImg').src = n.img;
    document.getElementById('modalImg').alt = n.title;
    const badge = document.getElementById('modalBadge');
    badge.textContent = n.badge;
    badge.className = 'news-badge ' + n.badgeClass;
    document.getElementById('modalTitle').textContent = n.title;
    document.getElementById('modalDateText').textContent = n.date;
    document.getElementById('modalAuthorText').textContent = n.author;
    document.getElementById('modalContent').innerHTML = n.content.replace(/\n/g, '<br>');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}
document.getElementById('modalClose').addEventListener('click', () => { modal.classList.remove('open'); document.body.style.overflow = ''; });
modal.addEventListener('click', (e) => { if (e.target === modal) { modal.classList.remove('open'); document.body.style.overflow = ''; } });
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { modal.classList.remove('open'); document.body.style.overflow = ''; } });

const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const closeMenuBtn = document.getElementById('closeMenuBtn');
const mobileMenu = document.getElementById('mobileMenu');
mobileMenuBtn.addEventListener('click', () => mobileMenu.classList.add('open'));
closeMenuBtn.addEventListener('click', () => mobileMenu.classList.remove('open'));

renderGrid();
</script>
</body>
</html>