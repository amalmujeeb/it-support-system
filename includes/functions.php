<?php
require_once __DIR__ . '/auth.php';

function create_notification(int $userId, ?int $ticketId, string $type, string $message): void
{
    $stmt = db()->prepare(
        "INSERT INTO notifications(user_id,ticket_id,type,message) VALUES(?,?,?,?)"
    );
    $stmt->execute([$userId, $ticketId, $type, $message]);
}

function generate_ticket_code(): string
{
    do {
        $code = 'TKT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $stmt = db()->prepare("SELECT id FROM tickets WHERE ticket_code=?");
        $stmt->execute([$code]);
    } while ($stmt->fetch());

    return $code;
}

function status_class(string $status): string
{
    return 'status-' . strtolower(str_replace(' ', '-', $status));
}
