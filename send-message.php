<?php
/**
 * send-message.php - API endpoint for sending messages
 */

header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to send messages']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$propertyId = $input['property_id'] ?? 0;
$message = trim($input['message'] ?? '');
$phone = $input['phone'] ?? '';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

try {
    // Get property owner
    $stmt = $pdo->prepare("
        SELECT landlord_id, title FROM properties WHERE id = :id
    ");
    $stmt->execute([':id' => $propertyId]);
    $property = $stmt->fetch();
    
    if (!$property) {
        echo json_encode(['success' => false, 'message' => 'Property not found']);
        exit;
    }
    
    // Insert message
    $stmt = $pdo->prepare("
        INSERT INTO messages (sender_id, receiver_id, property_id, message, phone, is_read, created_at) 
        VALUES (:sender_id, :receiver_id, :property_id, :message, :phone, 0, NOW())
    ");
    
    $stmt->execute([
        ':sender_id' => $_SESSION['user_id'],
        ':receiver_id' => $property['landlord_id'],
        ':property_id' => $propertyId,
        ':message' => $message,
        ':phone' => $phone
    ]);
    
    // Send email notification (implement with PHPMailer)
    // sendMessageNotification($receiverEmail, $message, $property['title']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Send message error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send message'
    ]);
}
?>