<?php
/**
 * favorites.php - API endpoint for managing saved properties
 * Supports GET (list favorites), POST (add), DELETE (remove)
 */

header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

// Require login
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to save favorites']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'];

try {
    switch ($method) {
        case 'GET':
            // Get user's favorites
            $stmt = $pdo->prepare("
                SELECT p.*, f.created_at as favorited_at
                FROM favorites f
                JOIN properties p ON f.property_id = p.id
                WHERE f.user_id = :user_id
                ORDER BY f.created_at DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'favorites' => $favorites
            ]);
            break;
            
        case 'POST':
            // Add to favorites
            $input = json_decode(file_get_contents('php://input'), true);
            $propertyId = $input['property_id'] ?? 0;
            
            if (!$propertyId) {
                echo json_encode(['success' => false, 'message' => 'Property ID required']);
                exit;
            }
            
            // Check if already exists
            $stmt = $pdo->prepare("
                SELECT id FROM favorites WHERE user_id = :user_id AND property_id = :property_id
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':property_id' => $propertyId
            ]);
            
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Already in favorites']);
                exit;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO favorites (user_id, property_id, created_at) 
                VALUES (:user_id, :property_id, NOW())
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':property_id' => $propertyId
            ]);
            
            echo json_encode([
                'success' => true,
                'action' => 'added',
                'message' => 'Added to favorites'
            ]);
            break;
            
        case 'DELETE':
            // Remove from favorites
            $input = json_decode(file_get_contents('php://input'), true);
            $propertyId = $input['property_id'] ?? 0;
            
            if (!$propertyId) {
                echo json_encode(['success' => false, 'message' => 'Property ID required']);
                exit;
            }
            
            $stmt = $pdo->prepare("
                DELETE FROM favorites WHERE user_id = :user_id AND property_id = :property_id
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':property_id' => $propertyId
            ]);
            
            echo json_encode([
                'success' => true,
                'action' => 'removed',
                'message' => 'Removed from favorites'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Favorites API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>