<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('client');

$pageHeading = 'Create New Ticket';
$pageTitle = 'New Ticket | SupportHub';
$error = '';
$selectedCategory = trim($_POST['category'] ?? '');
$selectedPriority = $_POST['priority'] ?? 'Medium';
$description = trim($_POST['issue_description'] ?? '');
$categories = [
    'Hardware' => '▣',
    'Software' => '◈',
    'Network' => '⌁',
    'Printer' => '▤',
    'Account / Access' => '♙',
    'Other' => '•••'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $priority = $_POST['priority'] ?? '';
    $description = trim($_POST['issue_description'] ?? '');
    $allowedPriority = ['Low','Medium','High','Critical'];

    if ($csrfError = post_error_for_invalid_csrf()) {
        $error = $csrfError;
    } elseif ($category === '' || $description === '' || !array_key_exists($category, $categories) || !in_array($priority, $allowedPriority, true)) {
        $error = 'Please complete all required fields.';
    } elseif (mb_strlen($description) > 5000) {
        $error = 'Issue details must be 5,000 characters or fewer.';
    } else {
        $pdo = db();
        $code = generate_ticket_code();
        $stmt = $pdo->prepare("INSERT INTO tickets(ticket_code,user_id,issue_description,category,priority,status) VALUES(?,?,?,?,?,'Open')");
        $stmt->execute([$code, current_user()['id'], $description, $category, $priority]);
        header('Location: /it-support-system/client/ticket.php?id=' . $pdo->lastInsertId() . '&created=1');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="ticket-wizard">
    <div class="wizard-progress">
        <div class="wizard-step active"><span class="wizard-step-number">1</span><div><strong>Request details</strong><span>Tell us what you need</span></div></div>
        <div class="wizard-step"><span class="wizard-step-number">2</span><div><strong>Review</strong><span>Check your request</span></div></div>
        <div class="wizard-step"><span class="wizard-step-number">3</span><div><strong>Submit</strong><span>Send to IT support</span></div></div>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>
        <div class="new-ticket-grid">
            <section class="card wizard-panel">
                <span class="eyebrow">STEP 1 · DETAILS</span>
                <h2>What can we help you with?</h2>
                <p>Choose the support area that best matches your issue.</p>

                <div class="form-group">
                    <label>Issue Category *</label>
                    <div class="choice-grid" data-choice-group>
                        <?php foreach ($categories as $name => $icon): ?>
                            <label class="choice-card <?= $selectedCategory === $name ? 'selected' : '' ?>">
                                <input type="radio" name="category" value="<?= htmlspecialchars($name) ?>" <?= $selectedCategory === $name ? 'checked' : '' ?> required>
                                <span class="choice-icon"><?= $icon ?></span>
                                <strong><?= htmlspecialchars($name) ?></strong>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-top:20px">
                    <label>Describe the issue *</label>
                    <textarea class="textarea" name="issue_description" maxlength="5000" placeholder="Please provide as much detail as possible. Include error messages, device names or what happened before the issue started..." required><?= htmlspecialchars($description) ?></textarea>
                    <div class="issue-helper"><span>Clear details help the support team resolve requests faster.</span><span>Required</span></div>
                </div>
            </section>

            <aside class="card wizard-panel">
                <span class="eyebrow">STEP 2 · PRIORITY</span>
                <h2>How urgent is it?</h2>
                <p>Select the level that best describes the impact on your work.</p>
                <div class="form-group">
                    <label>Priority Level *</label>
                    <div class="priority-choice-grid" data-choice-group>
                        <?php foreach (['Low','Medium','High','Critical'] as $priority): ?>
                            <label class="choice-card priority-choice priority-<?= strtolower($priority) ?> <?= $selectedPriority === $priority ? 'selected' : '' ?>">
                                <input type="radio" name="priority" value="<?= $priority ?>" <?= $selectedPriority === $priority ? 'checked' : '' ?> required>
                                <span class="choice-icon"><?= $priority === 'Critical' ? '!' : ($priority === 'High' ? '↑' : ($priority === 'Medium' ? '•' : '↓')) ?></span>
                                <strong><?= $priority ?></strong>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="margin-top:22px;padding:14px;border:1px solid #e6edf6;border-radius:13px;background:#f8fafc">
                    <strong style="font-size:10px;display:block">Before you submit</strong>
                    <ul style="margin:8px 0 0;padding-left:17px;color:#75869e;font-size:9px;line-height:1.75">
                        <li>Describe the problem clearly.</li>
                        <li>Choose the correct urgency.</li>
                        <li>Keep your ticket ID for future reference.</li>
                    </ul>
                </div>

                <div class="ticket-form-footer">
                    <a class="btn btn-secondary" href="/it-support-system/client/dashboard.php">Cancel</a>
                    <button class="btn btn-primary" type="submit">Submit Ticket <span>→</span></button>
                </div>
            </aside>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
