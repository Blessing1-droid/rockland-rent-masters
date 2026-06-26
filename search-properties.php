<?php
/**
 * search-properties.php - API endpoint for property search
 * Returns JSON response for AJAX requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

// Get search parameters
$location = $_GET['location'] ?? '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 1000000;
$bedrooms = isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : 0;
$bathrooms = isset($_GET['bathrooms']) ? (float)$_GET['bathrooms'] : 0;
$property_type = $_GET['property_type'] ?? '';
$pet_friendly = isset($_GET['pet_friendly']) ? (int)$_GET['pet_friendly'] : 0;
$furnished = isset($_GET['furnished']) ? (int)$_GET['furnished'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 12;
$offset = ($page - 1) * $per_page;

try {
    // Build query
    $sql = "SELECT p.*, u.first_name, u.last_name 
            FROM properties p 
            LEFT JOIN users u ON p.landlord_id = u.id 
            WHERE p.status = 'active'";
    $params = [];
    
    if (!empty($location)) {
        $sql .= " AND (p.address LIKE :location OR p.city LIKE :location OR p.zipcode LIKE :location)";
        $params[':location'] = "%{$location}%";
    }
    
    if ($min_price > 0) {
        $sql .= " AND p.price >= :min_price";
        $params[':min_price'] = $min_price;
    }
    
    if ($max_price < 1000000) {
        $sql .= " AND p.price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    if ($bedrooms > 0) {
        $sql .= " AND p.bedrooms >= :bedrooms";
        $params[':bedrooms'] = $bedrooms;
    }
    
    if ($bathrooms > 0) {
        $sql .= " AND p.bathrooms >= :bathrooms";
        $params[':bathrooms'] = $bathrooms;
    }
    
    if (!empty($property_type)) {
        $sql .= " AND p.property_type = :property_type";
        $params[':property_type'] = $property_type;
    }
    
    if ($pet_friendly == 1) {
        $sql .= " AND p.pet_friendly = 1";
    }
    
    if ($furnished == 1) {
        $sql .= " AND p.furnished = 1";
    }
    
    // Get total count
    $countSql = str_replace("SELECT p.*, u.first_name, u.last_name", "SELECT COUNT(*) as total", $sql);
    $stmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Add pagination
    $sql .= " ORDER BY p.created_at DESC LIMIT :offset, :per_page";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format output
    $formattedProperties = array_map(function($property) {
        return [
            'id' => $property['id'],
            'title' => $property['title'],
            'price' => $property['price'],
            'bedrooms' => $property['bedrooms'],
            'bathrooms' => $property['bathrooms'],
            'sqft' => $property['sqft'],
            'city' => $property['city'],
            'state' => $property['state'],
            'main_image' => $property['main_image'],
            'pet_friendly' => (bool)$property['pet_friendly'],
            'furnished' => (bool)$property['furnished'],
            'created_at' => $property['created_at']
        ];
    }, $properties);
    
    echo json_encode([
        'success' => true,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total / $per_page),
        'properties' => $formattedProperties
    ]);
    
} catch (PDOException $e) {
    error_log("Search API error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>