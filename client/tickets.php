<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('client');

$pageHeading = 'My Tickets';
$pageTitle = 'My Tickets | SupportHub';
$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM tickets WHERE user_id=?";
$params = [current_user()['id']];
if ($search !== '') {
    $sql .= " AND (ticket_code LIKE ? OR category LIKE ? OR issue_description LIKE ?)";
    $term = "%{$search}%";
    array_push($params, $term, $term, $term);
}
if ($status !== '') { $sql .= " AND status=?"; $params[] = $status; }
$sql .= " ORDER BY created_at DESC";
$stmt = db()->prepare($sql); $stmt->execute($params); $tickets = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-hero" style="margin-bottom:14px">
    <div><span class="eyebrow">TICKET CENTER</span><h2>My Support Tickets</h2><p>Search, filter and open any support request to view its full timeline.</p></div>
    <a class="btn btn-primary" href="/it-support-system/client/new-ticket.php">＋ New Ticket</a>
</div>

<div class="card filter-card">
    <form method="get" class="filter-toolbar">
        <div class="filter-search"><input class="input" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search ticket ID, category or issue..."></div>
        <select class="select" name="status"><option value="">All Statuses</option><?php foreach(['Open','Assigned','In Progress','Resolved','Closed'] as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
        <button class="btn btn-primary">Search</button>
        <?php if ($search !== '' || $status !== ''): ?><a class="btn btn-secondary" href="/it-support-system/client/tickets.php">Reset</a><?php endif; ?>
    </form>
    <div class="filter-meta"><span>Showing <strong><?= count($tickets) ?></strong> matching ticket<?= count($tickets) === 1 ? '' : 's' ?></span><span>Latest first</span></div>
</div>

<div class="card" style="margin-top:13px">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Ticket ID</th><th>Category</th><th>Issue</th><th>Priority</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($tickets as $t): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['ticket_code']) ?></strong></td>
                    <td><?= htmlspecialchars($t['category']) ?></td>
                    <td><?= htmlspecialchars(mb_strimwidth($t['issue_description'],0,45,'...')) ?></td>
                    <td class="priority-<?= strtolower($t['priority']) ?>"><?= htmlspecialchars($t['priority']) ?></td>
                    <td><span class="status <?= status_class($t['status']) ?>"><?= htmlspecialchars($t['status']) ?></span></td>
                    <td><?= date('d M Y', strtotime($t['created_at'])) ?></td>
                    <td><a class="btn btn-secondary" href="/it-support-system/client/ticket.php?id=<?= (int)$t['id'] ?>">View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$tickets): ?><tr><td colspan="7"><div class="empty-state"><div class="empty-state-icon">⌕</div><strong style="display:block;font-size:12px;color:#334155">No matching tickets</strong><span style="display:block;margin-top:4px;font-size:9px">Try another search or clear the filters.</span></div></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
