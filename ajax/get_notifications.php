<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications
$query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 15";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Get unread count
$unread_count = get_unread_notifications_count($user_id);

// Format dates and add notification type
foreach ($notifications as &$notif) {
    $notif['created_at'] = format_date($notif['created_at']);
    // Add notification type for icon
    if (strpos($notif['message'], 'commented') !== false) {
        $notif['type'] = 'comment';
    } elseif (strpos($notif['message'], 'liked') !== false) {
        $notif['type'] = 'like';
    } elseif (strpos($notif['message'], 'friend request') !== false) {
        $notif['type'] = 'friend';
    } else {
        $notif['type'] = 'default';
    }
}

echo json_encode([
    'status' => 'success',
    'notifications' => $notifications,
    'unread_count' => $unread_count
]); 