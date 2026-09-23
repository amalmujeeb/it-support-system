<?php
require_once __DIR__ . '/auth.php';
$user = current_user();
$unread = 0;

if ($user) {
    try {
        $stmt = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);
        $unread = (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        $unread = 0;
    }
}

$script = basename($_SERVER['PHP_SELF'] ?? '');
$path = $_SERVER['PHP_SELF'] ?? '';
$role = $user['role'] ?? '';
$searchAction = $role === 'admin'
    ? '/it-support-system/admin/tickets.php'
    : ($role === 'client' ? '/it-support-system/client/tickets.php' : '/it-support-system/staff/tickets.php');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#071326">
<title><?= htmlspecialchars($pageTitle ?? 'SupportHub') ?></title>
<script>
(() => { document.documentElement.dataset.theme = localStorage.getItem('supporthub.theme') || 'light'; document.documentElement.dataset.accent = localStorage.getItem('supporthub.accent') || 'blue'; })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/it-support-system/assets/css/style.css">
</head>
<body>
<div class="app-shell" id="appShell">
<?php if ($user): ?>
    <div class="sidebar-overlay" data-sidebar-overlay></div>
    <aside class="sidebar" id="appSidebar">
        <div class="brand">
            <a class="brand-mark" href="<?= dashboard_url($user['role']) ?>" aria-label="SupportHub home">
                <span>IT</span>
            </a>
            <div class="brand-copy">
                <strong>SupportHub</strong>
                <span>IT Service Management</span>
            </div>
            <button class="sidebar-close" type="button" data-sidebar-close aria-label="Close menu">×</button>
        </div>

        <div class="sidebar-label">WORKSPACE</div>
        <nav class="nav" aria-label="Primary navigation">
            <a class="<?= $script === 'dashboard.php' ? 'active' : '' ?>" href="<?= dashboard_url($user['role']) ?>" title="Dashboard">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span><span class="nav-text">Dashboard</span>
            </a>
            <?php if ($role === 'client'): ?>
                <a class="<?= str_contains($path, '/client/new-ticket.php') ? 'active' : '' ?>" href="/it-support-system/client/new-ticket.php" title="New Ticket">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg></span><span class="nav-text">New Ticket</span>
                </a>
                <a class="<?= str_contains($path, '/client/tickets.php') || str_contains($path, '/client/ticket.php') ? 'active' : '' ?>" href="/it-support-system/client/tickets.php" title="My Tickets">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v16H5z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span><span class="nav-text">My Tickets</span>
                </a>
            <?php elseif ($role === 'staff'): ?>
                <a class="<?= str_contains($path, '/staff/tickets.php') ? 'active' : '' ?>" href="/it-support-system/staff/tickets.php" title="Assigned Tickets">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg></span><span class="nav-text">Assigned Tickets</span>
                </a>
            <?php else: ?>
                <a class="<?= str_contains($path, '/admin/tickets.php') ? 'active' : '' ?>" href="/it-support-system/admin/tickets.php" title="All Tickets">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h8M8 17h5"/></svg></span><span class="nav-text">All Tickets</span>
                </a>
                <a class="<?= str_contains($path, '/admin/users.php') ? 'active' : '' ?>" href="/it-support-system/admin/users.php" title="Users">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path d="M3.5 19c.7-3 2.5-4.5 5.5-4.5s4.8 1.5 5.5 4.5"/><circle cx="17" cy="10" r="2.3"/><path d="M14.5 19c.3-2 1.3-3.2 3.2-3.2 1.7 0 2.7.8 3 2.2"/></svg></span><span class="nav-text">Users</span>
                </a>
            <?php endif; ?>
            <a class="<?= $script === 'notifications.php' ? 'active' : '' ?>" href="/it-support-system/notifications.php" title="Notifications">
                <span class="nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg></span><span class="nav-text">Notifications</span>
                <?php if ($unread > 0): ?><b class="badge-count"><?= $unread ?></b><?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-spacer"></div>
        <div class="sidebar-bottom">
            <button class="user-mini profile-trigger" type="button" data-settings-open aria-label="Open profile settings">
                <div class="avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <div class="user-copy">
                    <strong><?= htmlspecialchars($user['name']) ?></strong>
                    <span><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
                </div>
                <span class="online-dot" title="Online"></span>
            </button>
            <form method="post" action="/it-support-system/logout.php" class="logout-form">
                <?= csrf_field() ?>
                <button class="logout-link" type="submit" title="Logout">
                    <span class="logout-icon">↪</span><span class="nav-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>
<?php endif; ?>

<main class="<?= $user ? 'main-content' : 'public-content' ?>">
<?php if ($user): ?>
    <header class="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Toggle sidebar">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="topbar-title">
                <span class="eyebrow">IT SUPPORT MANAGEMENT</span>
                <h1><?= htmlspecialchars($pageHeading ?? 'Dashboard') ?></h1>
            </div>
        </div>
        <div class="topbar-actions">
            <form class="global-search" method="get" action="<?= $searchAction ?>" role="search">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg>
                <input name="search" value="<?= htmlspecialchars(trim($_GET['search'] ?? '')) ?>" placeholder="Search tickets..." aria-label="Search tickets">
                <kbd>⌘ K</kbd>
                <button class="global-search-submit" type="submit" aria-label="Run search">Search</button>
            </form>
            <a class="icon-button notification-button" href="/it-support-system/notifications.php" aria-label="Notifications" title="Notifications">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
                <?php if ($unread > 0): ?><span><?= $unread ?></span><?php endif; ?>
            </a>
            <div class="profile-menu">
                <button class="profile-chip" type="button" data-profile-toggle aria-haspopup="menu" aria-expanded="false" aria-controls="profileDropdown">
                <div class="avatar small"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <div class="profile-chip-copy"><strong><?= htmlspecialchars($user['name']) ?></strong><span><?= htmlspecialchars(ucfirst($user['role'])) ?></span></div>
                <svg class="chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                </button>
                <div class="profile-dropdown" id="profileDropdown" hidden>
                    <div class="profile-dropdown-user"><strong><?= htmlspecialchars($user['name']) ?></strong><span><?= htmlspecialchars($user['email']) ?></span></div>
                    <button type="button" class="profile-menu-action" data-settings-open>Profile &amp; appearance</button>
                </div>
            </div>
        </div>
    </header>
    <div class="settings-modal" data-settings-modal hidden role="dialog" aria-modal="true" aria-labelledby="settingsTitle">
        <div class="settings-backdrop" data-settings-close></div>
        <section class="settings-dialog">
            <button class="settings-close" type="button" data-settings-close aria-label="Close settings">×</button>
            <span class="eyebrow">PERSONALIZATION</span><h2 id="settingsTitle">Profile settings</h2>
            <p><?= htmlspecialchars($user['name']) ?> · <?= htmlspecialchars(ucfirst($user['role'])) ?></p>
            <div class="settings-group"><strong>Display mode</strong><div class="theme-options"><button type="button" data-theme-choice="light">Light</button><button type="button" data-theme-choice="dark">Dark</button></div></div>
            <div class="settings-group"><strong>Theme colour</strong><div class="accent-options"><button type="button" data-accent-choice="blue"><i class="accent-blue"></i>Blue</button><button type="button" data-accent-choice="violet"><i class="accent-violet"></i>Violet</button><button type="button" data-accent-choice="green"><i class="accent-green"></i>Green</button><button type="button" data-accent-choice="rose"><i class="accent-rose"></i>Rose</button></div></div>
            <p class="settings-note">Your choices are saved on this device.</p>
        </section>
    </div>
<?php endif; ?>
