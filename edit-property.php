<?php
/**
 * edit-property.php - Edit Property Listing
 * PRD v1.1: Manage existing property listings
 */

$pageTitle = "Edit Property - Update Your Listing";
$pageDescription = "Update your property details, photos, and pricing.";

require_once __DIR__ . '/../../includes/auth-check.php';
requireRole('landlord');

$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($propertyId <= 0) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

// Fetch property data
try {
    $stmt = $pdo->prepare("
        SELECT * FROM properties 
        WHERE id = :id AND landlord_id = :user_id
    ");
    $stmt->execute([
        ':id' => $propertyId,
        ':user_id' => $_SESSION['user_id']
    ]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$property) {
        header('Location: ' . BASE_URL . '/pages/dashboard.php');
        exit;
    }
    
    // Fetch property images
    $stmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = :property_id ORDER BY sort_order ASC");
    $stmt->execute([':property_id' => $propertyId]);
    $images = $stmt->fetchAll();
    
    $amenities = json_decode($property['amenities'], true) ?? [];
    
} catch (PDOException $e) {
    error_log("Edit property error: " . $e->getMessage());
    $error = "Failed to load property data.";
}

require_once __DIR__ . '/../../includes/header.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $bedrooms = (int)($_POST['bedrooms'] ?? 0);
    $bathrooms = (float)($_POST['bathrooms'] ?? 0);
    $sqft = (int)($_POST['sqft'] ?? 0);
    $property_type = $_POST['property_type'] ?? 'apartment';
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $pet_friendly = isset($_POST['pet_friendly']) ? 1 : 0;
    $furnished = isset($_POST['furnished']) ? 1 : 0;
    $laundry = isset($_POST['laundry']) ? 1 : 0;
    $parking = isset($_POST['parking']) ? 1 : 0;
    $amenitiesList = $_POST['amenities'] ?? [];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE properties SET
                title = :title,
                description = :description,
                price = :price,
                bedrooms = :bedrooms,
                bathrooms = :bathrooms,
                sqft = :sqft,
                property_type = :property_type,
                address = :address,
                city = :city,
                state = :state,
                zipcode = :zipcode,
                pet_friendly = :pet_friendly,
                furnished = :furnished,
                laundry = :laundry,
                parking = :parking,
                amenities = :amenities,
                updated_at = NOW()
            WHERE id = :id AND landlord_id = :user_id
        ");
        
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':price' => $price,
            ':bedrooms' => $bedrooms,
            ':bathrooms' => $bathrooms,
            ':sqft' => $sqft,
            ':property_type' => $property_type,
            ':address' => $address,
            ':city' => $city,
            ':state' => $state,
            ':zipcode' => $zipcode,
            ':pet_friendly' => $pet_friendly,
            ':furnished' => $furnished,
            ':laundry' => $laundry,
            ':parking' => $parking,
            ':amenities' => json_encode($amenitiesList),
            ':id' => $propertyId,
            ':user_id' => $_SESSION['user_id']
        ]);
        
        // Handle new image uploads
        if (isset($_FILES['new_images']) && count($_FILES['new_images']['name']) > 0 && $_FILES['new_images']['name'][0] != '') {
            uploadNewImages($propertyId, $_FILES['new_images']);
        }
        
        $success = "Property updated successfully!";
        
        // Refresh property data
        header("Refresh:0");
        
    } catch (PDOException $e) {
        error_log("Update property error: " . $e->getMessage());
        $error = "Failed to update property. Please try again.";
    }
}

function uploadNewImages($propertyId, $files) {
    global $pdo;
    
    $uploadDir = __DIR__ . '/../../uploads/properties/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Get current max sort order
    $stmt = $pdo->prepare("SELECT MAX(sort_order) as max_order FROM property_images WHERE property_id = :id");
    $stmt->execute([':id' => $propertyId]);
    $result = $stmt->fetch();
    $sortOrder = ($result['max_order'] ?? -1) + 1;
    
    foreach ($files['name'] as $index => $name) {
        if ($files['error'][$index] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$index];
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($extension, $allowedExtensions)) {
                $filename = $propertyId . '_' . time() . '_' . $index . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                move_uploaded_file($tmpName, $destination);
                
                $stmt = $pdo->prepare("
                    INSERT INTO property_images (property_id, image_path, sort_order) 
                    VALUES (:property_id, :image_path, :sort_order)
                ");
                $stmt->execute([
                    ':property_id' => $propertyId,
                    ':image_path' => $filename,
                    ':sort_order' => $sortOrder++
                ]);
            }
        }
    }
}

function deleteImage($imageId, $propertyId) {
    global $pdo;
    
    // Get image path
    $stmt = $pdo->prepare("SELECT image_path FROM property_images WHERE id = :id AND property_id = :property_id");
    $stmt->execute([':id' => $imageId, ':property_id' => $propertyId]);
    $image = $stmt->fetch();
    
    if ($image) {
        $filePath = __DIR__ . '/../../uploads/properties/' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $stmt = $pdo->prepare("DELETE FROM property_images WHERE id = :id");
        $stmt->execute([':id' => $imageId]);
        return true;
    }
    return false;
}

// Handle image deletion
if (isset($_GET['delete_image'])) {
    $imageId = (int)$_GET['delete_image'];
    if (deleteImage($imageId, $propertyId)) {
        header("Location: " . BASE_URL . "/pages/landlord/edit-property.php?id=$propertyId&deleted=1");
        exit;
    }
}
?>

<div class="container" style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Edit Property</h1>
        <a href="<?= BASE_URL ?>/pages/renter/property.php?id=<?= $propertyId ?>" class="btn btn-outline" target="_blank">Preview Listing →</a>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Image deleted successfully!</div>
    <?php endif; ?>
    
    <!-- Current Images -->
    <?php if (count($images) > 0): ?>
        <div style="margin-bottom: 1.5rem;">
            <h3>Current Photos</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <?php foreach ($images as $image): ?>
                    <div style="position: relative;">
                        <img src="<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($image['image_path']) ?>" 
                             style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                        <a href="?id=<?= $propertyId ?>&delete_image=<?= $image['id'] ?>" 
                           class="delete-image" 
                           style="position: absolute; top: -8px; right: -8px; background: red; color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; text-decoration: none;"
                           onclick="return confirm('Delete this image?')">×</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <!-- Basic Information -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Basic Information</legend>
            
            <div class="form-group">
                <label class="form-label">Property Title *</label>
                <input type="text" class="form-input" name="title" required value="<?= htmlspecialchars($property['title']) ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea class="form-textarea" name="description" rows="5" required><?= htmlspecialchars($property['description']) ?></textarea>
            </div>
            
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Price (per month) *</label>
                    <input type="number" class="form-input" name="price" required value="<?= $property['price'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Property Type</label>
                    <select class="form-select" name="property_type">
                        <option value="apartment" <?= $property['property_type'] == 'apartment' ? 'selected' : '' ?>>Apartment</option>
                        <option value="house" <?= $property['property_type'] == 'house' ? 'selected' : '' ?>>House</option>
                        <option value="condo" <?= $property['property_type'] == 'condo' ? 'selected' : '' ?>>Condo</option>
                        <option value="studio" <?= $property['property_type'] == 'studio' ? 'selected' : '' ?>>Studio</option>
                        <option value="townhouse" <?= $property['property_type'] == 'townhouse' ? 'selected' : '' ?>>Townhouse</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-3">
                <div class="form-group">
                    <label class="form-label">Bedrooms *</label>
                    <input type="number" class="form-input" name="bedrooms" required value="<?= $property['bedrooms'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Bathrooms *</label>
                    <input type="number" class="form-input" name="bathrooms" required step="0.5" value="<?= $property['bathrooms'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Square Feet</label>
                    <input type="number" class="form-input" name="sqft" value="<?= $property['sqft'] ?>">
                </div>
            </div>
        </fieldset>
        
        <!-- Location -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Location</legend>
            
            <div class="form-group">
                <label class="form-label">Street Address *</label>
                <input type="text" class="form-input" name="address" required value="<?= htmlspecialchars($property['address']) ?>">
            </div>
            
            <div class="grid grid-cols-3">
                <div class="form-group">
                    <label class="form-label">City *</label>
                    <input type="text" class="form-input" name="city" required value="<?= htmlspecialchars($property['city']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">State *</label>
                    <select class="form-select" name="state" required>
                        <option value="">Select</option>
                        <option value="NY" <?= $property['state'] == 'NY' ? 'selected' : '' ?>>New York</option>
                        <option value="CA" <?= $property['state'] == 'CA' ? 'selected' : '' ?>>California</option>
                        <option value="IL" <?= $property['state'] == 'IL' ? 'selected' : '' ?>>Illinois</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ZIP Code *</label>
                    <input type="text" class="form-input" name="zipcode" required value="<?= htmlspecialchars($property['zipcode']) ?>">
                </div>
            </div>
        </fieldset>
        
        <!-- Amenities -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Amenities & Features</legend>
            
            <div class="grid grid-cols-2">
                <label>
                    <input type="checkbox" name="pet_friendly" value="1" <?= $property['pet_friendly'] ? 'checked' : '' ?>> 🐾 Pet Friendly
                </label>
                <label>
                    <input type="checkbox" name="furnished" value="1" <?= $property['furnished'] ? 'checked' : '' ?>> 🛋️ Furnished
                </label>
                <label>
                    <input type="checkbox" name="laundry" value="1" <?= $property['laundry'] ? 'checked' : '' ?>> 🧺 In-Unit Laundry
                </label>
                <label>
                    <input type="checkbox" name="parking" value="1" <?= $property['parking'] ? 'checked' : '' ?>> 🚗 Parking Included
                </label>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">Additional Amenities</label>
                <div class="grid grid-cols-2">
                    <label><input type="checkbox" name="amenities[]" value="Air Conditioning" <?= in_array('Air Conditioning', $amenities) ? 'checked' : '' ?>> ❄️ Air Conditioning</label>
                    <label><input type="checkbox" name="amenities[]" value="Heating" <?= in_array('Heating', $amenities) ? 'checked' : '' ?>> 🔥 Heating</label>
                    <label><input type="checkbox" name="amenities[]" value="Dishwasher" <?= in_array('Dishwasher', $amenities) ? 'checked' : '' ?>> 🍽️ Dishwasher</label>
                    <label><input type="checkbox" name="amenities[]" value="Refrigerator" <?= in_array('Refrigerator', $amenities) ? 'checked' : '' ?>> 🧊 Refrigerator</label>
                    <label><input type="checkbox" name="amenities[]" value="Gym" <?= in_array('Gym', $amenities) ? 'checked' : '' ?>> 💪 Gym Access</label>
                    <label><input type="checkbox" name="amenities[]" value="Pool" <?= in_array('Pool', $amenities) ? 'checked' : '' ?>> 🏊 Pool</label>
                </div>
            </div>
        </fieldset>
        
        <!-- Add New Photos -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Add More Photos</legend>
            
            <div class="form-group">
                <label class="form-label">Upload Additional Photos</label>
                <input type="file" class="form-input" name="new_images[]" accept="image/*" multiple>
            </div>
        </fieldset>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>