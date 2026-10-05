<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid = current_user()['id'];
$filter = $_GET['filter'] ?? 'all';
$validFilters = ['all', 'unread', 'assignments', 'status_updates'];
if (!in_array($filter, $validFilters, true)) $filter = 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedFilter = $_POST['filter'] ?? 'all';
    if (in_array($postedFilter, $validFilters, true)) $filter = $postedFilter;
    if (!verify_csrf()) {
        $_SESSION['notification_error'] = 'Your form session expired. Please try again.';
    } elseif (($_POST['action'] ?? '') === 'mark_read') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
        $stmt->execute([$id, $uid]);
    } elseif (($_POST['action'] ?? '') === 'mark_all') {
        $stmt = db()->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
        $stmt->execute([$uid]);
    }
    header('Location: /it-support-system/notifications.php?filter=' . urlencode($filter)); exit;
}

$stmt = db()->prepare("SELECT n.*, t.ticket_code FROM notifications n LEFT JOIN tickets t ON t.id=n.ticket_id WHERE n.user_id=? ORDER BY n.created_at DESC");
$stmt->execute([$uid]);
$notifications = $stmt->fetchAll();
$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
$assignmentCount = count(array_filter($notifications, fn($n) => $n['type'] === 'Ticket Assigned'));
$statusUpdateCount = count(array_filter($notifications, fn($n) => in_array($n['type'], ['Status Changed', 'Resolution Note Added'], true)));
$visibleNotifications = array_values(array_filter($notifications, function ($n) use ($filter) {
    return match ($filter) {
        'unread' => !(bool)$n['is_read'],
        'assignments' => $n['type'] === 'Ticket Assigned',
        'status_updates' => in_array($n['type'], ['Status Changed', 'Resolution Note Added'], true),
        default => true,
    };
}));
$notificationError = $_SESSION['notification_error'] ?? '';
unset($_SESSION['notification_error']);

$pageHeading = 'Notifications';
$pageTitle = 'Notifications | SupportHub';
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-hero" style="margin-bottom:14px">
    <div><span class="eyebrow">ACTIVITY CENTER</span><h2>Notifications</h2><p>Stay updated with ticket assignments, status changes and support notes.</p></div>
<?php if ($unreadCount): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"><input type="hidden" name="action" value="mark_all"><button class="btn btn-secondary">Mark All as Read</button></form><?php endif; ?>
</div>

<div class="card notifications-card">
    <?php if ($notificationError): ?><div class="alert alert-error"><?= htmlspecialchars($notificationError) ?></div><?php endif; ?>
    <div class="notifications-head">
        <div><h2>Your Notifications</h2><p><?= $unreadCount ?> unread update<?= $unreadCount === 1 ? '' : 's' ?> · <?= count($notifications) ?> total</p></div>
    </div>
    <nav class="notification-tabs" aria-label="Filter notifications">
        <a class="<?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all" <?= $filter === 'all' ? 'aria-current="page"' : '' ?>>All <span><?= count($notifications) ?></span></a>
        <a class="<?= $filter === 'unread' ? 'active' : '' ?>" href="?filter=unread" <?= $filter === 'unread' ? 'aria-current="page"' : '' ?>>Unread <span><?= $unreadCount ?></span></a>
        <a class="<?= $filter === 'assignments' ? 'active' : '' ?>" href="?filter=assignments" <?= $filter === 'assignments' ? 'aria-current="page"' : '' ?>>Assignments <span><?= $assignmentCount ?></span></a>
        <a class="<?= $filter === 'status_updates' ? 'active' : '' ?>" href="?filter=status_updates" <?= $filter === 'status_updates' ? 'aria-current="page"' : '' ?>>Status Updates <span><?= $statusUpdateCount ?></span></a>
    </nav>

    <?php foreach($visibleNotifications as $n): ?>
        <?php
            $icon = $n['type'] === 'Ticket Assigned' ? '↗' : ($n['type'] === 'Resolution Note Added' ? '✓' : '↻');
        ?>
        <div class="notification-item <?= !$n['is_read'] ? 'unread' : '' ?>">
            <div class="notification-symbol"><?= $icon ?></div>
            <div class="notification-copy">
                <strong><?= htmlspecialchars($n['type']) ?></strong>
                <p><?= htmlspecialchars($n['message']) ?></p>
                <time><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></time>
            </div>
            <div class="notification-action">
                <?php if (!$n['is_read']): ?>
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"><input type="hidden" name="action" value="mark_read"><input type="hidden" name="id" value="<?= (int)$n['id'] ?>"><button class="btn btn-primary">Mark Read</button></form>
                <?php else: ?><span class="read-label">READ</span><?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!$visibleNotifications): ?><div class="empty-state"><div class="empty-state-icon">✓</div><strong style="display:block;font-size:12px;color:#334155"><?= $notifications ? 'No notifications in this section' : "You're all caught up" ?></strong><span style="display:block;margin-top:4px;font-size:9px"><?= $notifications ? 'Try another notification filter.' : 'New ticket activity will appear here.' ?></span></div><?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
