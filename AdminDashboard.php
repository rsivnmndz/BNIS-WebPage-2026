<?php
require_once __DIR__ . '/auth.php';
require_admin_page();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | BNIS Enrollment Analytics</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
        :root{--maroon:#800000;--maroon-dark:#5a0000;--gold:#f0c040;--sidebar-bg:#0d0d0d;--sidebar-border:#1e1e1e;--body-bg:#f4f4f0;--white:#ffffff;--gray-900:#111827;--gray-800:#1f2937;--gray-700:#374151;--gray-600:#4b5563;--gray-500:#6b7280;--gray-400:#9ca3af;--gray-300:#d1d5db;--gray-200:#e5e7eb;--gray-100:#f3f4f6;--gray-50:#f9fafb;--green:#15803d;--green-light:#dcfce7;--blue:#1d4ed8;--blue-light:#dbeafe;--amber:#d97706;--amber-light:#fef3c7;--purple:#6d28d9;--purple-light:#ede9fe;--red:#dc2626;--red-light:#fee2e2;--shadow-sm:0 1px 3px rgba(0,0,0,.07);--shadow-md:0 4px 16px rgba(0,0,0,.09);--shadow-lg:0 10px 40px rgba(0,0,0,.12);--r-sm:8px;--r-md:12px;--r-lg:18px;--r-xl:24px;--r-full:9999px}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}html{scroll-behavior:smooth}
        body{font-family:'DM Sans',sans-serif;background:var(--body-bg);color:var(--gray-800);line-height:1.5;display:flex;min-height:100vh;overflow-x:hidden}
        .sidebar{width:260px;min-height:100vh;background:var(--sidebar-bg);display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:50;border-right:1px solid var(--sidebar-border);transition:transform .3s cubic-bezier(.4,0,.2,1)}
        .sb-logo{padding:1.75rem 1.5rem 1.5rem;border-bottom:1px solid var(--sidebar-border);display:flex;align-items:center;gap:.85rem}
        .sb-logo-icon{width:40px;height:40px;background:var(--maroon);border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .sb-logo-icon svg{width:1.2rem;height:1.2rem;color:#fff}
        .sb-logo-name{font-family:'Playfair Display',serif;font-size:.95rem;font-weight:700;color:#fff;line-height:1.1}
        .sb-logo-sub{font-size:.6rem;color:#555;text-transform:uppercase;letter-spacing:.18em;font-weight:600}
        .sb-section{padding:1.25rem 1rem .5rem}
        .sb-label{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.22em;color:#444;padding:0 .5rem;margin-bottom:.4rem}
        .sb-nav{list-style:none;display:flex;flex-direction:column;gap:2px}
        .sb-nav a{display:flex;align-items:center;gap:.75rem;padding:.65rem .85rem;border-radius:var(--r-sm);color:#777;font-size:.82rem;font-weight:500;transition:all .18s;cursor:pointer;text-decoration:none}
        .sb-nav a:hover{background:#1a1a1a;color:#bbb}.sb-nav a.active{background:var(--maroon);color:#fff}
        .sb-nav a svg{width:1rem;height:1rem;flex-shrink:0}
        .sb-badge{margin-left:auto;background:var(--maroon);color:#fff;font-size:.58rem;font-weight:700;padding:.15rem .45rem;border-radius:var(--r-full);min-width:1.4rem;text-align:center}
        .sb-nav a.active .sb-badge{background:rgba(255,255,255,.25)}
        .sb-user{margin-top:auto;padding:1.25rem;border-top:1px solid var(--sidebar-border);display:flex;align-items:center;gap:.75rem}
        .user-avatar{width:34px;height:34px;background:var(--maroon);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0}
        .user-name{font-size:.8rem;font-weight:600;color:#fff}.user-role{font-size:.62rem;color:#555;text-transform:uppercase;letter-spacing:.1em}
        .main{margin-left:260px;flex:1;display:flex;flex-direction:column;min-height:100vh}
        .topbar{background:#fff;border-bottom:1px solid var(--gray-200);padding:1rem 2rem;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:40;box-shadow:var(--shadow-sm)}
        .topbar-left h1{font-size:1.15rem;font-weight:700;color:var(--gray-900);font-family:'Playfair Display',serif}
        .topbar-left p{font-size:.75rem;color:var(--gray-500);margin-top:.1rem}
        .topbar-right{display:flex;align-items:center;gap:.75rem}
        .tb-btn{display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:var(--r-md);border:1.5px solid var(--gray-200);background:#fff;color:var(--gray-600);font-size:.78rem;font-weight:600;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif;text-decoration:none}
        .tb-btn:hover{border-color:var(--maroon);color:var(--maroon);background:#fff5f5}.tb-btn svg{width:.9rem;height:.9rem}
        .tb-btn.primary{background:var(--maroon);color:#fff;border-color:var(--maroon)}.tb-btn.primary:hover{background:var(--maroon-dark)}
        .sb-toggle{display:none;background:none;border:none;cursor:pointer;color:var(--gray-600)}.sb-toggle svg{width:1.4rem;height:1.4rem}
        .conn-status{display:flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:600;padding:.35rem .8rem;border-radius:var(--r-full);border:1.5px solid var(--gray-200)}
        .conn-dot{width:7px;height:7px;border-radius:50%;animation:pulse 2s infinite}
        .conn-status.ok{color:var(--green);border-color:#bbf7d0;background:var(--green-light)}.conn-status.ok .conn-dot{background:var(--green)}
        .conn-status.err{color:var(--red);border-color:#fecaca;background:var(--red-light)}.conn-status.err .conn-dot{background:var(--red);animation:none}
        .conn-status.loading{color:var(--gray-500);border-color:var(--gray-200)}.conn-status.loading .conn-dot{background:var(--gray-400)}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
        .content{padding:1.75rem 2rem;flex:1}
        .filter-bar{background:#fff;border-radius:var(--r-lg);border:1.5px solid var(--gray-200);padding:1.1rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;box-shadow:var(--shadow-sm)}
        .f-group{display:flex;align-items:center;gap:.6rem}
        .f-label{font-size:.68rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.12em;white-space:nowrap}
        .f-select{padding:.42rem .85rem;border-radius:var(--r-md);border:1.5px solid var(--gray-200);background:var(--body-bg);color:var(--gray-800);font-size:.8rem;font-weight:500;cursor:pointer;font-family:'DM Sans',sans-serif;outline:none;transition:border-color .2s}
        .f-select:focus{border-color:var(--maroon)}.f-pills{display:flex;gap:.35rem;flex-wrap:wrap}
        .f-pill{padding:.32rem .78rem;border-radius:var(--r-full);border:1.5px solid var(--gray-200);background:#fff;color:var(--gray-600);font-size:.7rem;font-weight:600;cursor:pointer;transition:all .18s;font-family:'DM Sans',sans-serif}
        .f-pill:hover{border-color:var(--maroon);color:var(--maroon)}.f-pill.active{background:var(--maroon);color:#fff;border-color:var(--maroon)}
        .f-divider{width:1px;height:1.75rem;background:var(--gray-200);flex-shrink:0}
        .f-search{display:flex;align-items:center;gap:.5rem;flex:1;min-width:160px;border:1.5px solid var(--gray-200);border-radius:var(--r-md);padding:.38rem .8rem;background:var(--body-bg);transition:border-color .2s}
        .f-search:focus-within{border-color:var(--maroon)}.f-search svg{width:.9rem;height:.9rem;color:var(--gray-400);flex-shrink:0}
        .f-search input{border:none;background:transparent;font-size:.8rem;font-family:'DM Sans',sans-serif;color:var(--gray-800);outline:none;width:100%}
        .f-search input::placeholder{color:var(--gray-400)}
        .kpi-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.1rem;margin-bottom:1.5rem}
        .kpi{background:#fff;border-radius:var(--r-lg);padding:1.3rem 1.4rem;border:1.5px solid var(--gray-200);box-shadow:var(--shadow-sm);display:flex;flex-direction:column;gap:.7rem;transition:all .2s;position:relative;overflow:hidden;animation:fadeUp .4s ease both}
        .kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--r-lg) var(--r-lg) 0 0}
        .kpi.c-maroon::before{background:var(--maroon)}.kpi.c-green::before{background:var(--green)}
        .kpi.c-blue::before{background:var(--blue)}.kpi.c-amber::before{background:var(--amber)}
        .kpi:hover{box-shadow:var(--shadow-md);transform:translateY(-2px)}
        .kpi-top{display:flex;justify-content:space-between;align-items:flex-start}
        .kpi-lbl{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--gray-500)}
        .kpi-icon{width:2.1rem;height:2.1rem;border-radius:var(--r-sm);display:flex;align-items:center;justify-content:center}
        .kpi-icon svg{width:1.05rem;height:1.05rem}
        .kpi-icon.red{background:#fee2e2;color:var(--maroon)}.kpi-icon.green{background:var(--green-light);color:var(--green)}
        .kpi-icon.blue{background:var(--blue-light);color:var(--blue)}.kpi-icon.amber{background:var(--amber-light);color:var(--amber)}
        .kpi-val{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gray-900);line-height:1}
        .kpi-skeleton{height:2rem;background:linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);background-size:200%;animation:shimmer 1.2s infinite;border-radius:var(--r-sm);width:60%}
        @keyframes shimmer{0%{background-position:200%}100%{background-position:-200%}}
        .kpi-foot{display:flex;align-items:center;gap:.4rem}
        .kpi-chg{font-size:.68rem;font-weight:700;display:flex;align-items:center;gap:.2rem}
        .kpi-chg.up{color:var(--green)}.kpi-chg svg{width:.75rem;height:.75rem}
        .kpi-period{font-size:.65rem;color:var(--gray-400)}
        .chart-grid-2{display:grid;grid-template-columns:1.6fr 1fr;gap:1.1rem;margin-bottom:1.5rem}
        .chart-grid-eq{display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;margin-bottom:1.5rem}
        .chart-card{background:#fff;border-radius:var(--r-lg);border:1.5px solid var(--gray-200);box-shadow:var(--shadow-sm);overflow:hidden}
        .ch-header{padding:1.2rem 1.5rem 0;display:flex;justify-content:space-between;align-items:flex-start}
        .ch-title{font-size:.88rem;font-weight:700;color:var(--gray-900)}.ch-sub{font-size:.7rem;color:var(--gray-400);margin-top:.12rem}
        .ch-actions{display:flex;gap:.35rem}
        .ch-pill{padding:.22rem .6rem;border-radius:var(--r-full);font-size:.63rem;font-weight:600;border:1.5px solid var(--gray-200);background:#fff;color:var(--gray-500);cursor:pointer;transition:all .18s;font-family:'DM Sans',sans-serif}
        .ch-pill.active{background:var(--maroon);color:#fff;border-color:var(--maroon)}
        .ch-body{padding:.85rem 1.5rem 1.4rem}
        .legend{display:flex;flex-wrap:wrap;gap:.65rem;padding:.65rem 1.5rem;border-top:1px solid var(--gray-100)}
        .legend-item{display:flex;align-items:center;gap:.35rem;font-size:.68rem;color:var(--gray-600);font-weight:500}
        .legend-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .prog-list{display:flex;flex-direction:column;gap:1rem;padding:1.1rem 1.5rem}
        .prog-item{}.prog-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem}
        .prog-name{font-size:.78rem;font-weight:600;color:var(--gray-800)}.prog-val{font-size:.75rem;font-weight:700;color:var(--gray-900)}
        .prog-bar{height:7px;background:var(--gray-100);border-radius:var(--r-full);overflow:hidden}
        .prog-fill{height:100%;border-radius:var(--r-full);transition:width 1s cubic-bezier(.4,0,.2,1)}
        .prog-sub{font-size:.63rem;color:var(--gray-400);margin-top:.18rem}
        .tbl-card{background:#fff;border-radius:var(--r-lg);border:1.5px solid var(--gray-200);box-shadow:var(--shadow-sm);overflow:hidden;margin-bottom:1.5rem}
        .tbl-header{padding:1.1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--gray-200);flex-wrap:wrap;gap:.75rem}
        .tbl-header h2{font-size:.88rem;font-weight:700;color:var(--gray-900)}.tbl-header p{font-size:.7rem;color:var(--gray-400)}
        table{width:100%;border-collapse:collapse}
        thead th{padding:.7rem 1.2rem;text-align:left;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gray-400);background:var(--gray-100);border-bottom:1px solid var(--gray-200);white-space:nowrap;cursor:pointer;user-select:none;transition:color .15s}
        thead th:hover{color:var(--maroon)}
        tbody tr{border-bottom:1px solid var(--gray-100);transition:background .15s}
        tbody tr:last-child{border-bottom:none}tbody tr:hover{background:#fafafa}
        tbody td{padding:.8rem 1.2rem;font-size:.8rem;color:var(--gray-700)}
        .td-rank{font-weight:700;color:var(--gray-400);font-size:.7rem}.td-rank.top{color:var(--maroon)}
        .td-name{font-weight:600;color:var(--gray-900)}
        .strand-chip{display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;font-weight:600;padding:.2rem .55rem;border-radius:var(--r-full)}
        .strand-dot{width:6px;height:6px;border-radius:50%}
        .bar-wrap{display:flex;align-items:center;gap:.65rem;min-width:110px}
        .bar-bg{flex:1;height:5px;background:var(--gray-200);border-radius:var(--r-full);overflow:hidden}
        .bar-fill{height:100%;border-radius:var(--r-full);transition:width .8s cubic-bezier(.4,0,.2,1)}
        .bar-pct{font-size:.68rem;color:var(--gray-400);min-width:2.5rem;text-align:right}
        .status-badge{display:inline-block;padding:.18rem .55rem;border-radius:var(--r-full);font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em}
        .status-badge.high{background:var(--green-light);color:var(--green)}.status-badge.mid{background:var(--blue-light);color:var(--blue)}
        .status-badge.low{background:var(--amber-light);color:var(--amber)}.status-badge.crit{background:var(--red-light);color:var(--red)}
        .bottom-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.1rem;margin-bottom:1.5rem}
        .enrollee-list{display:flex;flex-direction:column}
        .enrollee-row{display:flex;align-items:center;gap:.8rem;padding:.78rem 1.4rem;border-bottom:1px solid var(--gray-100);transition:background .15s}
        .enrollee-row:last-child{border-bottom:none}.enrollee-row:hover{background:#fafafa}
        .e-avatar{width:1.9rem;height:1.9rem;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff}
        .e-name{font-size:.78rem;font-weight:600;color:var(--gray-900)}.e-grade{font-size:.67rem;color:var(--gray-500);margin-top:.05rem}
        .e-time{font-size:.62rem;color:var(--gray-400);white-space:nowrap;margin-left:auto}
        .db-error{background:#fff5f5;border:1.5px solid #fecaca;color:var(--red);border-radius:var(--r-lg);padding:1.4rem 1.75rem;margin-bottom:1.5rem;display:none}
        .db-error strong{display:block;margin-bottom:.5rem;font-size:.9rem}.db-error p{font-size:.82rem;line-height:1.7}
        .db-error code{background:#fee2e2;padding:.1rem .4rem;border-radius:4px;font-family:monospace;font-size:.8rem}
        .toast{position:fixed;bottom:1.5rem;right:1.5rem;background:var(--gray-900);color:#fff;padding:.75rem 1.25rem;border-radius:var(--r-lg);font-size:.8rem;font-weight:500;box-shadow:var(--shadow-lg);transform:translateY(4rem);opacity:0;transition:all .3s;z-index:1000;display:flex;align-items:center;gap:.6rem;max-width:320px}
        .toast.show{transform:translateY(0);opacity:1}.toast.success{border-left:3px solid var(--green)}.toast.error{border-left:3px solid var(--red)}
        @keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
        .kpi:nth-child(1){animation-delay:.05s}.kpi:nth-child(2){animation-delay:.1s}
        @media(max-width:1280px){.chart-grid-2{grid-template-columns:1fr}.bottom-grid{grid-template-columns:1fr 1fr}}
        @media(max-width:900px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.main{margin-left:0}.sb-toggle{display:block}.bottom-grid{grid-template-columns:1fr}.kpi-grid{grid-template-columns:1fr}.content{padding:1.25rem}.topbar{padding:.85rem 1.25rem}.chart-grid-eq{grid-template-columns:1fr}}
        .overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:45}.overlay.open{display:block}
        @media print{.sidebar,.topbar-right,.filter-bar,.sb-toggle{display:none!important}.main{margin-left:0}body{background:#fff}}
    </style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<div class="toast" id="toast"></div>

<aside class="sidebar" id="sidebar">
    <div class="sb-logo">
        <div class="sb-logo-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg></div>
        <div><div class="sb-logo-name">BNIS Admin</div><div class="sb-logo-sub">Enrollment Dashboard</div></div>
    </div>
    <div class="sb-section"><div class="sb-label">Main</div>
        <ul class="sb-nav">
            <li><a href="Admin.html"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>Admin Home</a></li>
            <li><a href="AdminDashboard.php" class="active"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Enrollment Analytics<span class="sb-badge">DB</span></a></li>
            <li><a href="Enrollment.html"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Enrollment Form</a></li>
        </ul>
    </div>
    <div class="sb-section"><div class="sb-label">School</div>
        <ul class="sb-nav">
            <li><a href="SchoolNews.html"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"/></svg>School News</a></li>
            <li><a href="CampusUpdates.html"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Campus Updates</a></li>
            <li><a href="index.html"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>Public Site</a></li>
        </ul>
    </div>
    <div class="sb-user">
        <div class="user-avatar">AD</div>
        <div style="flex:1">
            <div class="user-name">Admin</div>
            <div class="user-role">School Admin</div>
        </div>
        <a href="admin_logout.php" style="color:var(--gray-500); transition:color .2s; display:flex;" title="Logout" onmouseover="this.style.color='var(--red)'" onmouseout="this.style.color='var(--gray-500)'">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:1.2rem;height:1.2rem"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:1rem">
            <button class="sb-toggle" id="sbToggle"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            <div class="topbar-left"><h1>Enrollment Analytics</h1><p id="topbarSub">Connecting to database…</p></div>
        </div>
        <div class="topbar-right">
            <div class="conn-status loading" id="connStatus"><div class="conn-dot"></div><span id="connText">Connecting…</span></div>
            <button class="tb-btn" onclick="loadAll()"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Refresh</button>
            <button class="tb-btn" onclick="window.print()"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Print</button>
            <button class="tb-btn primary" onclick="exportCSV()"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Export CSV</button>
            
            <a href="admin_logout.php" class="tb-btn" style="color:var(--red); border-color:#fecaca; background:#fff5f5; text-decoration:none;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Logout
            </a>
        </div>
    </div>

    <div class="content">
        <div class="db-error" id="dbError">
            <strong>⚠ Database Connection Error</strong>
            <p id="dbErrorMsg">Could not reach the API. Ensure PHP server is running.</p>
        </div>

        <div class="filter-bar">
            <div class="f-group"><span class="f-label">Year</span>
                <select class="f-select" id="filterSY" onchange="loadAll()"><option value="1">SY 2026-2027</option></select>
            </div>
            <div class="f-divider"></div>
            <div class="f-group"><span class="f-label">Level</span>
                <div class="f-pills">
                    <button class="f-pill active" data-level="SHS" onclick="setLevel(this,'SHS')">SHS</button>
                </div>
            </div>
            <div class="f-divider"></div>
            <div class="f-group"><span class="f-label">Strand</span>
                <select class="f-select" id="filterStrand" onchange="loadAll()">
                    <option value="all">All Strands</option>
                    <option value="STEM">STEM</option>
                    <option value="ABM">ABM</option>
                    <option value="HUMSS">HUMSS</option>
                    <option value="TVL">TVL</option>
                </select>
            </div>
            <div class="f-divider"></div>
            <div class="f-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="searchInput" placeholder="Search grade, strand, section…" oninput="debounceSearch()">
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi c-maroon">
                <div class="kpi-top"><span class="kpi-lbl">Total Enrollees</span><div class="kpi-icon red"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div></div>
                <div class="kpi-val" id="kpiTotal"><div class="kpi-skeleton"></div></div>
                <div class="kpi-foot"><span class="kpi-chg up" id="kpiTotalChg"></span><span class="kpi-period">enrolled</span></div>
            </div>
            <div class="kpi c-green">
                <div class="kpi-top"><span class="kpi-lbl">SHS Enrollees</span><div class="kpi-icon green"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg></div></div>
                <div class="kpi-val" id="kpiSHS"><div class="kpi-skeleton"></div></div>
                <div class="kpi-foot"><span class="kpi-period">Senior High School</span></div>
            </div>
            </div>

        <div class="chart-grid-2">
            <div class="chart-card">
                <div class="ch-header"><div><div class="ch-title">Enrollment by Grade Level</div><div class="ch-sub">All enrolled students grouped by grade</div></div>
                    <div class="ch-actions"><button class="ch-pill active" onclick="switchBar(this,'count')">Count</button><button class="ch-pill" onclick="switchBar(this,'pct')">%</button></div>
                </div>
                <div class="ch-body"><div style="height:260px;position:relative"><canvas id="gradeChart"></canvas></div></div>
            </div>
            <div class="chart-card">
                <div class="ch-header"><div><div class="ch-title">SHS Strand Distribution</div><div class="ch-sub">Share per strand</div></div></div>
                <div class="ch-body"><div style="height:220px;position:relative"><canvas id="strandChart"></canvas></div></div>
                <div class="legend" id="strandLegend"></div>
            </div>
        </div>

        <div class="chart-grid-eq">
            <div class="chart-card">
                <div class="ch-header"><div><div class="ch-title">Monthly Enrollment Trend</div><div class="ch-sub">Registrations per month</div></div>
                    <div class="ch-actions"><button class="ch-pill active" onclick="switchTrend(this,'all')">All</button></div>
                </div>
                <div class="ch-body"><div style="height:220px;position:relative"><canvas id="trendChart"></canvas></div></div>
            </div>
            <div class="chart-card">
                <div class="ch-header"><div><div class="ch-title">Gender Breakdown by Strand</div><div class="ch-sub">Male vs Female per SHS strand</div></div></div>
                <div class="ch-body"><div style="height:220px;position:relative"><canvas id="genderChart"></canvas></div></div>
            </div>
        </div>

        <div class="tbl-card">
            <div class="tbl-header">
                <div><h2>Grade &amp; Strand Breakdown</h2><p id="tblSub">Fetching from database…</p></div>
                <button class="tb-btn" onclick="exportCSV()"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>CSV</button>
            </div>
            <div style="overflow-x:auto">
                <table>
                    <thead><tr>
                        <th style="width:32px">#</th>
                        <th onclick="sortBy('grade')">Grade <span id="s-grade"></span></th>
                        <th onclick="sortBy('strand')">Strand <span id="s-strand"></span></th>
                        <th onclick="sortBy('count')">Enrollees <span id="s-count">↓</span></th>
                        <th>Bar</th>
                        <th onclick="sortBy('pct')">Share <span id="s-pct"></span></th>
                        <th onclick="sortBy('male')">Male <span id="s-male"></span></th>
                        <th onclick="sortBy('female')">Female <span id="s-female"></span></th>
                        <th>Status</th>
                    </tr></thead>
                    <tbody id="tableBody"><tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--gray-400)">Loading from database…</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="bottom-grid">
            <div class="chart-card"><div class="ch-header" style="padding:1.1rem 1.5rem"><div><div class="ch-title">Top Strands by Enrollment</div><div class="ch-sub">SHS strands ranked</div></div></div><div class="prog-list" id="strandProg"></div></div>
            <div class="chart-card"><div class="ch-header" style="padding:1.1rem 1.5rem"><div><div class="ch-title">Capacity Fill Rate</div><div class="ch-sub">Actual vs target seats</div></div></div><div class="prog-list" id="capacityProg"></div></div>
            <div class="chart-card"><div class="ch-header" style="padding:1.1rem 1.5rem 0"><div><div class="ch-title">Recent Enrollees</div><div class="ch-sub">Latest from database</div></div></div><div class="enrollee-list" id="recentList"></div></div>
        </div>
    </div>
</div>

<script>
// ─── CONFIG ────────────────────────────────────────────────────────────────
const API_BASE = '.';

// Removed GAS from colors
const SC = { STEM:'#800000', ABM:'#1d4ed8', HUMSS:'#15803d', TVL:'#d97706' };
const LC = { SHS:'#800000' };

let AL='SHS', barMode='count', trendMode='all', sortCol='count', sortDir='desc';
let gradeChart,strandChart,trendChart,genderChart,cache=null,exportRows=[];
let searchTimer;

const $=id=>document.getElementById(id);
const num=n=>Number(n).toLocaleString();

function toast(msg,type='success'){const t=$('toast');t.textContent=msg;t.className=`toast ${type} show`;setTimeout(()=>t.classList.remove('show'),3200);}
function setConn(s,t){$('connStatus').className=`conn-status ${s}`;$('connText').textContent=t;}

async function apiFetch(ep,p={}){
    const url=new URL(`${API_BASE}/${ep}`,location.href);
    Object.entries(p).forEach(([k,v])=>url.searchParams.set(k,v));
    const r=await fetch(url.toString());
    if(!r.ok)throw new Error(`HTTP ${r.status} — Is your PHP server running?`);
    const d=await r.json();
    if(!d.ok)throw new Error(d.error||'API returned error');
    return d;
}

function filters(){return{sy:$('filterSY').value,level:AL,strand:$('filterStrand').value,q:$('searchInput').value.trim()};}

async function loadAll(){
    setConn('loading','Loading…');
    $('dbError').style.display='none';
    try{
        const d=await apiFetch('analytics.php',filters());
        cache=d; setConn('ok',`Live · ${d.generated}`);

        if($('filterSY').options.length<=1&&d.school_years){
            $('filterSY').innerHTML=d.school_years.map(sy=>`<option value="${sy.id}"${sy.is_active?' selected':''}>${sy.label}</option>`).join('');
        }

        $('topbarSub').textContent=`${$('filterSY').selectedOptions[0]?.text||''} · SHS Only · ${num(d.kpi.total)} enrollees`;
        renderKPIs(d.kpi);
        renderGradeChart(d.grades);
        renderStrandChart(d.strands);
        renderTrendChart(d.trend);
        renderGenderChart(d.strands);
        renderTable(d.grades,d.strands);
        renderStrandProg(d.strands);
        renderCapacityProg(d.capacity,d.grades);
        renderRecent(d.recent);
        toast('Data loaded from database');
    }catch(e){
        setConn('err','DB Error');
        $('dbError').style.display='block';
        $('dbErrorMsg').textContent=e.message;
        toast(e.message,'error');
    }
}

function renderKPIs(k){
    $('kpiTotal').textContent=num(k.total);
    $('kpiSHS').textContent=num(k.shs);
    const mp=k.total>0?((k.male/k.total)*100).toFixed(0):0;
    $('kpiTotalChg').innerHTML=`<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:.75rem;height:.75rem"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg> ${mp}% Male`;
}

function renderGradeChart(grades){
    const g={};
    grades.forEach(r=>{const l=r.grade_label;if(!g[l])g[l]={c:0,lv:r.level};g[l].c+=Number(r.count);});
    const labels=Object.keys(g),vals=labels.map(l=>g[l].c),tot=vals.reduce((a,b)=>a+b,0);
    const colors=labels.map(l=>'#800000');
    const data=barMode==='count'?vals:vals.map(v=>+((v/tot*100).toFixed(1)));
    if(gradeChart)gradeChart.destroy();
    gradeChart=new Chart($('gradeChart'),{type:'bar',data:{labels,datasets:[{label:'Enrollees',data,backgroundColor:colors.map(c=>c+'cc'),borderColor:colors,borderWidth:2,borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>barMode==='count'?` ${num(ctx.raw)} students`:` ${ctx.raw}%`}}},scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{grid:{color:'#f0f0f0'},ticks:{font:{size:10}},beginAtZero:true}}}});
}

function renderStrandChart(strands){
    const t={};strands.forEach(s=>{t[s.strand]=(t[s.strand]||0)+Number(s.count);});
    const labels=Object.keys(t),vals=labels.map(l=>t[l]),colors=labels.map(l=>SC[l]||'#999');
    $('strandLegend').innerHTML=labels.map((l,i)=>`<div class="legend-item"><div class="legend-dot" style="background:${colors[i]}"></div>${l}: <strong style="margin-left:3px">${num(vals[i])}</strong></div>`).join('');
    if(strandChart)strandChart.destroy();
    strandChart=new Chart($('strandChart'),{type:'doughnut',data:{labels,datasets:[{data:vals,backgroundColor:colors,borderWidth:3,borderColor:'#fff',hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${num(ctx.raw)}`}}}}});
}

function renderTrendChart(trend){
    if(trendChart)trendChart.destroy();
    trendChart=new Chart($('trendChart'),{type:'line',data:{labels:trend.labels,datasets:[{label:'Enrollees',data:trend[trendMode],borderColor:'#800000',backgroundColor:'rgba(128,0,0,0.08)',fill:true,tension:.4,pointRadius:4,pointBackgroundColor:'#800000',borderWidth:2.5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{mode:'index',intersect:false}},scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{grid:{color:'#f5f5f5'},ticks:{font:{size:10}},beginAtZero:true}}}});
}

function renderGenderChart(strands){
    const gm={};strands.forEach(s=>{if(!gm[s.strand])gm[s.strand]={m:0,f:0};gm[s.strand].m+=Number(s.male);gm[s.strand].f+=Number(s.female);});
    const labels=Object.keys(gm);
    if(genderChart)genderChart.destroy();
    genderChart=new Chart($('genderChart'),{type:'bar',data:{labels,datasets:[{label:'Male',data:labels.map(l=>gm[l].m),backgroundColor:'rgba(128,0,0,0.8)',borderRadius:4,borderSkipped:false},{label:'Female',data:labels.map(l=>gm[l].f),backgroundColor:'rgba(29,78,216,0.75)',borderRadius:4,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{position:'bottom',labels:{font:{size:10},boxWidth:10,padding:10}}},scales:{x:{grid:{color:'#f5f5f5'},ticks:{font:{size:10}}},y:{grid:{display:false},ticks:{font:{size:10}}}}}});
}

function renderTable(grades,strands){
    const rows=[];
    grades.forEach(g=>{
        rows.push({grade:g.grade_label,gradeNum:Number(g.grade),strand:g.strand||'N/A',level:g.level,count:Number(g.count),male:Number(g.male||0),female:Number(g.female||0)});
    });
    strands.forEach(s=>{
        if(!rows.find(r=>r.gradeNum===Number(s.grade)&&r.strand===s.strand))
            rows.push({grade:`Grade ${s.grade}`,gradeNum:Number(s.grade),strand:s.strand,level:'SHS',count:Number(s.count),male:Number(s.male||0),female:Number(s.female||0)});
    });
    rows.sort((a,b)=>{
        let va=a[sortCol],vb=b[sortCol];
        if(sortCol==='grade'){va=a.gradeNum;vb=b.gradeNum;}
        if(sortCol==='pct'){va=a.count;vb=b.count;}
        if(typeof va==='string')return sortDir==='asc'?va.localeCompare(vb):vb.localeCompare(va);
        return sortDir==='asc'?va-vb:vb-va;
    });
    exportRows=rows;
    const tot=rows.reduce((s,r)=>s+r.count,0),max=Math.max(...rows.map(r=>r.count),1);
    $('tblSub').textContent=`${rows.length} entries · ${num(tot)} total enrollees`;
    if(!rows.length){$('tableBody').innerHTML=`<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--gray-400)">No data for selected filters</td></tr>`;return;}
    $('tableBody').innerHTML=rows.map((r,i)=>{
        const pct=tot>0?((r.count/tot)*100).toFixed(1):'0.0';
        const bw=Math.round((r.count/max)*100);
        const dc=SC[r.strand]||'#800000';
        const [sc,sl]=bw>75?['high','High']:bw>50?['mid','Normal']:bw>30?['low','Below Avg']:['crit','Low'];
        return `<tr><td class="td-rank ${i<3?'top':''}">${i+1}</td><td class="td-name">${r.grade}</td><td><span class="strand-chip" style="background:${dc}18;color:${dc}"><span class="strand-dot" style="background:${dc}"></span>${r.strand}</span></td><td style="font-weight:700;color:var(--gray-900)">${num(r.count)}</td><td><div class="bar-wrap"><div class="bar-bg"><div class="bar-fill" style="width:${bw}%;background:${dc}"></div></div><span class="bar-pct">${pct}%</span></div></td><td style="font-size:.75rem;color:var(--gray-500)">${pct}%</td><td style="font-size:.78rem">${num(r.male)}</td><td style="font-size:.78rem">${num(r.female)}</td><td><span class="status-badge ${sc}">${sl}</span></td></tr>`;
    }).join('');
}

function sortBy(col){
    if(sortCol===col)sortDir=sortDir==='asc'?'desc':'asc';else{sortCol=col;sortDir='desc';}
    ['grade','strand','count','pct','male','female'].forEach(c=>{const e=$(`s-${c}`);if(e)e.textContent='';});
    const e=$(`s-${col}`);if(e)e.textContent=sortDir==='asc'?' ↑':' ↓';
    if(cache)renderTable(cache.grades,cache.strands);
}

function renderStrandProg(strands){
    const t={};strands.forEach(s=>{t[s.strand]=(t[s.strand]||0)+Number(s.count);});
    const entries=Object.entries(t).sort((a,b)=>b[1]-a[1]);
    const max=entries[0]?.[1]||1;
    $('strandProg').innerHTML=entries.map(([s,c])=>`<div class="prog-item"><div class="prog-top"><span class="prog-name">${s}</span><span class="prog-val">${num(c)}</span></div><div class="prog-bar"><div class="prog-fill" style="width:${Math.round((c/max)*100)}%;background:${SC[s]||'#800000'}"></div></div><div class="prog-sub">${Math.round((c/max)*100)}% of top strand</div></div>`).join('');
}

function renderCapacityProg(capacity,grades){
    const tgt={SHS:1200};
    const lvl={SHS:0};
    grades.forEach(g=>{lvl[g.level]=(lvl[g.level]||0)+Number(g.count);});
    const lc={SHS:'#800000'};
    $('capacityProg').innerHTML=Object.entries(lvl).map(([level,count])=>{
        const t=tgt[level]||count;const pct=Math.min(100,Math.round((count/t)*100));
        return `<div class="prog-item"><div class="prog-top"><span class="prog-name">${level}</span><span class="prog-val">${num(count)} / ${num(t)}</span></div><div class="prog-bar"><div class="prog-fill" style="width:${pct}%;background:${lc[level]}"></div></div><div class="prog-sub">${pct}% capacity filled</div></div>`;
    }).join('');
}

function renderRecent(recent){
    if(!recent?.length){$('recentList').innerHTML=`<div style="padding:1.5rem;text-align:center;color:var(--gray-400);font-size:.8rem">No recent enrollees</div>`;return;}
    $('recentList').innerHTML=recent.map(r=>{
        const init=r.name.split(' ').map(n=>n[0]).join('').slice(0,2).toUpperCase();
        const col=(SC[r.strand]||'#800000');
        return `<div class="enrollee-row"><div class="e-avatar" style="background:${col}">${init}</div><div style="flex:1;min-width:0"><div class="e-name">${r.name}</div><div class="e-grade">Grade ${r.grade} · ${r.strand}</div></div><div class="e-time">${r.enrolled_at||'–'}</div></div>`;
    }).join('');
}

function setLevel(el,level){
    document.querySelectorAll('.f-pill[data-level]').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');AL=level;
    loadAll();
}
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(loadAll,420);}
function switchBar(el,mode){barMode=mode;el.closest('.ch-actions').querySelectorAll('.ch-pill').forEach(b=>b.classList.remove('active'));el.classList.add('active');if(cache)renderGradeChart(cache.grades);}
function switchTrend(el,mode){trendMode=mode;el.closest('.ch-actions').querySelectorAll('.ch-pill').forEach(b=>b.classList.remove('active'));el.classList.add('active');if(trendChart&&cache){trendChart.data.datasets[0].data=cache.trend[mode];trendChart.update();}}

function exportCSV(){
    const tot=exportRows.reduce((s,r)=>s+r.count,0);
    const rows=[['Grade','Strand','Level','Enrollees','Male','Female','Share%']];
    exportRows.forEach(r=>rows.push([r.grade,r.strand,r.level,r.count,r.male,r.female,((r.count/tot)*100).toFixed(1)+'%']));
    const csv=rows.map(r=>r.map(v=>`"${v}"`).join(',')).join('\n');
    const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
    a.download=`BNIS_Enrollment_${new Date().toISOString().slice(0,10)}.csv`;a.click();
    toast('CSV exported');
}

$('sbToggle').addEventListener('click',()=>{$('sidebar').classList.toggle('open');$('overlay').classList.toggle('open');});
$('overlay').addEventListener('click',()=>{$('sidebar').classList.remove('open');$('overlay').classList.remove('open');});

loadAll();
setInterval(loadAll,5*60*1000);
</script>
</body>
</html>