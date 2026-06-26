<?php
/**
 * update-application-status.php - API endpoint for landlords to update application status
 */

header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Require login and landlord role
if (!isLoggedIn() || $_SESSION['user_role'] !== 'landlord') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$applicationId = $input['id'] ?? 0;
$status = $input['status'] ?? '';

$allowedStatuses = ['pending', 'reviewed', 'approved', 'rejected'];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Verify landlord owns the property for this application
    $stmt = $pdo->prepare("
        SELECT a.id, a.property_id, a.user_id, p.landlord_id, p.title
        FROM applications a
        JOIN properties p ON a.property_id = p.id
        WHERE a.id = :id AND p.landlord_id = :landlord_id
    ");
    $stmt->execute([
        ':id' => $applicationId,
        ':landlord_id' => $_SESSION['user_id']
    ]);
    
    $application = $stmt->fetch();
    
    if (!$application) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit;
    }
    
    // Update status
    $stmt = $pdo->prepare("
        UPDATE applications SET status = :status, updated_at = NOW() 
        WHERE id = :id
    ");
    $stmt->execute([
        ':status' => $status,
        ':id' => $applicationId
    ]);
    
    // If approved, create lease document reference
    if ($status === 'approved') {
        $stmt = $pdo->prepare("
            INSERT INTO leases (application_id, property_id, tenant_id, landlord_id, status, created_at)
            VALUES (:app_id, :prop_id, :tenant_id, :landlord_id, 'pending', NOW())
        ");
        $stmt->execute([
            ':app_id' => $applicationId,
            ':prop_id' => $application['property_id'],
            ':tenant_id' => $application['user_id'],
            ':landlord_id' => $_SESSION['user_id']
        ]);
    }
    
    // Send email notification to applicant
    // sendApplicationStatusNotification($applicantEmail, $status, $application['title']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Application status updated',
        'status' => $status
    ]);
    
} catch (PDOException $e) {
    error_log("Update application status error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>