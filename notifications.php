<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    header('Location: /it-support-system/notifications.php'); exit;
}

$stmt = db()->prepare("SELECT n.*, t.ticket_code FROM notifications n LEFT JOIN tickets t ON t.id=n.ticket_id WHERE n.user_id=? ORDER BY n.created_at DESC");
$stmt->execute([$uid]);
$notifications = $stmt->fetchAll();
$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));
$notificationError = $_SESSION['notification_error'] ?? '';
unset($_SESSION['notification_error']);

$pageHeading = 'Notifications';
$pageTitle = 'Notifications | SupportHub';
include __DIR__ . '/includes/header.php';
?>

<div class="dashboard-hero" style="margin-bottom:14px">
    <div><span class="eyebrow">ACTIVITY CENTER</span><h2>Notifications</h2><p>Stay updated with ticket assignments, status changes and support notes.</p></div>
<?php if ($notifications): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="mark_all"><button class="btn btn-secondary">Mark All as Read</button></form><?php endif; ?>
</div>

<div class="card notifications-card">
    <?php if ($notificationError): ?><div class="alert alert-error"><?= htmlspecialchars($notificationError) ?></div><?php endif; ?>
    <div class="notifications-head">
        <div><h2>Your Notifications</h2><p><?= $unreadCount ?> unread update<?= $unreadCount === 1 ? '' : 's' ?> · <?= count($notifications) ?> total</p></div>
    </div>
    <div class="notification-tabs"><span class="active">All</span><span>Unread <?= $unreadCount ?></span><span>Assignments</span><span>Status Updates</span></div>

    <?php foreach($notifications as $n): ?>
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
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="mark_read"><input type="hidden" name="id" value="<?= (int)$n['id'] ?>"><button class="btn btn-primary">Mark Read</button></form>
                <?php else: ?><span class="read-label">READ</span><?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (!$notifications): ?><div class="empty-state"><div class="empty-state-icon">✓</div><strong style="display:block;font-size:12px;color:#334155">You're all caught up</strong><span style="display:block;margin-top:4px;font-size:9px">New ticket activity will appear here.</span></div><?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
