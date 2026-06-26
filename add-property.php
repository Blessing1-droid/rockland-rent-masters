<?php
/**
 * add-property.php - Add New Property Listing
 * PRD v1.1: Easy property listing creation with multi-photo upload
 */

$pageTitle = "Add New Property - List Your Rental";
$pageDescription = "List your property on Rockland Rent Masters. Reach thousands of potential tenants.";

require_once __DIR__ . '/../../includes/auth-check.php';
requireRole('landlord');

require_once __DIR__ . '/../../includes/header.php';

$error = '';
$success = '';
$propertyId = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
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
    $latitude = (float)($_POST['latitude'] ?? 0);
    $longitude = (float)($_POST['longitude'] ?? 0);
    $pet_friendly = isset($_POST['pet_friendly']) ? 1 : 0;
    $furnished = isset($_POST['furnished']) ? 1 : 0;
    $laundry = isset($_POST['laundry']) ? 1 : 0;
    $parking = isset($_POST['parking']) ? 1 : 0;
    $amenities = $_POST['amenities'] ?? [];
    
    // Validation
    $errors = [];
    if (empty($title)) $errors[] = "Property title is required";
    if (empty($description)) $errors[] = "Description is required";
    if ($price <= 0) $errors[] = "Valid price is required";
    if ($bedrooms <= 0) $errors[] = "Number of bedrooms is required";
    if ($bathrooms <= 0) $errors[] = "Number of bathrooms is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($zipcode)) $errors[] = "ZIP code is required";
    
    // Geocode address if lat/lng not provided
    if ($latitude == 0 && $longitude == 0 && !empty($address)) {
        $geoResult = geocodeAddress($address, $city, $state, $zipcode);
        if ($geoResult) {
            $latitude = $geoResult['lat'];
            $longitude = $geoResult['lng'];
        }
    }
    
    if (empty($errors)) {
        try {
            // Insert property
            $stmt = $pdo->prepare("
                INSERT INTO properties (
                    landlord_id, title, description, price, bedrooms, bathrooms, sqft,
                    property_type, address, city, state, zipcode, latitude, longitude,
                    pet_friendly, furnished, laundry, parking, amenities, status, created_at
                ) VALUES (
                    :landlord_id, :title, :description, :price, :bedrooms, :bathrooms, :sqft,
                    :property_type, :address, :city, :state, :zipcode, :latitude, :longitude,
                    :pet_friendly, :furnished, :laundry, :parking, :amenities, 'active', NOW()
                )
            ");
            
            $stmt->execute([
                ':landlord_id' => $_SESSION['user_id'],
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
                ':latitude' => $latitude,
                ':longitude' => $longitude,
                ':pet_friendly' => $pet_friendly,
                ':furnished' => $furnished,
                ':laundry' => $laundry,
                ':parking' => $parking,
                ':amenities' => json_encode($amenities)
            ]);
            
            $propertyId = $pdo->lastInsertId();
            
            // Handle image uploads
            if ($propertyId && isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
                uploadPropertyImages($propertyId, $_FILES['images']);
            }
            
            $success = "Property listed successfully! <a href='" . BASE_URL . "/pages/renter/property.php?id=$propertyId'>View your listing</a>";
            
        } catch (PDOException $e) {
            error_log("Add property error: " . $e->getMessage());
            $error = "Failed to add property. Please try again.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Helper function to geocode address
function geocodeAddress($address, $city, $state, $zipcode) {
    $fullAddress = urlencode("$address, $city, $state $zipcode");
    $url = "https://nominatim.openstreetmap.org/search?format=json&q=$fullAddress&limit=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'RocklandRentMasters/1.0');
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if (!empty($data)) {
        return [
            'lat' => (float)$data[0]['lat'],
            'lng' => (float)$data[0]['lon']
        ];
    }
    return null;
}

// Helper function to upload images
function uploadPropertyImages($propertyId, $files) {
    $uploadDir = __DIR__ . '/../../uploads/properties/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $sortOrder = 0;
    foreach ($files['name'] as $index => $name) {
        if ($files['error'][$index] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$index];
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($extension, $allowedExtensions)) {
                $filename = $propertyId . '_' . time() . '_' . $index . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                // Compress image
                compressImage($tmpName, $destination, $extension);
                
                // Insert into database
                global $pdo;
                $stmt = $pdo->prepare("
                    INSERT INTO property_images (property_id, image_path, sort_order) 
                    VALUES (:property_id, :image_path, :sort_order)
                ");
                $stmt->execute([
                    ':property_id' => $propertyId,
                    ':image_path' => $filename,
                    ':sort_order' => $sortOrder++
                ]);
                
                // Set first image as main image
                if ($sortOrder === 1) {
                    $stmt = $pdo->prepare("UPDATE properties SET main_image = :image WHERE id = :id");
                    $stmt->execute([':image' => $filename, ':id' => $propertyId]);
                }
            }
        }
    }
}

function compressImage($source, $destination, $extension) {
    $maxWidth = 1200;
    $quality = 80;
    
    list($width, $height) = getimagesize($source);
    $ratio = $width / $height;
    
    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $ratio;
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }
    
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
            $sourceImage = imagecreatefromjpeg($source);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagejpeg($newImage, $destination, $quality);
            break;
        case 'png':
            $sourceImage = imagecreatefrompng($source);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagepng($newImage, $destination, 9);
            break;
        case 'webp':
            $sourceImage = imagecreatefromwebp($source);
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagewebp($newImage, $destination, $quality);
            break;
    }
    
    imagedestroy($sourceImage);
    imagedestroy($newImage);
}
?>

<div class="container" style="max-width: 800px; margin: 0 auto;">
    <h1>List Your Property</h1>
    <p>Fill out the form below to list your property on Rockland Rent Masters.</p>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" id="property-form">
        <!-- Basic Information -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Basic Information</legend>
            
            <div class="form-group">
                <label class="form-label">Property Title *</label>
                <input type="text" class="form-input" name="title" required placeholder="e.g., Modern Downtown Apartment">
            </div>
            
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea class="form-textarea" name="description" rows="5" required placeholder="Describe your property..."></textarea>
            </div>
            
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Price (per month) *</label>
                    <input type="number" class="form-input" name="price" required placeholder="e.g., 1500">
                </div>
                <div class="form-group">
                    <label class="form-label">Property Type</label>
                    <select class="form-select" name="property_type">
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="condo">Condo</option>
                        <option value="studio">Studio</option>
                        <option value="townhouse">Townhouse</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-3">
                <div class="form-group">
                    <label class="form-label">Bedrooms *</label>
                    <input type="number" class="form-input" name="bedrooms" required min="0" step="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Bathrooms *</label>
                    <input type="number" class="form-input" name="bathrooms" required min="0" step="0.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Square Feet</label>
                    <input type="number" class="form-input" name="sqft" placeholder="e.g., 1200">
                </div>
            </div>
        </fieldset>
        
        <!-- Location -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Location</legend>
            
            <div class="form-group">
                <label class="form-label">Street Address *</label>
                <input type="text" class="form-input" name="address" required>
            </div>
            
            <div class="grid grid-cols-3">
                <div class="form-group">
                    <label class="form-label">City *</label>
                    <input type="text" class="form-input" name="city" required>
                </div>
                <div class="form-group">
                    <label class="form-label">State *</label>
                    <select class="form-select" name="state" required>
                        <option value="">Select</option>
                        <option value="NY">New York</option>
                        <option value="CA">California</option>
                        <option value="IL">Illinois</option>
                        <option value="TX">Texas</option>
                        <option value="FL">Florida</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ZIP Code *</label>
                    <input type="text" class="form-input" name="zipcode" required>
                </div>
            </div>
            
            <div class="grid grid-cols-2">
                <div class="form-group">
                    <label class="form-label">Latitude (auto-detected)</label>
                    <input type="text" class="form-input" name="latitude" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude (auto-detected)</label>
                    <input type="text" class="form-input" name="longitude" readonly>
                </div>
            </div>
        </fieldset>
        
        <!-- Amenities -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Amenities & Features</legend>
            
            <div class="grid grid-cols-2">
                <label class="form-label">
                    <input type="checkbox" name="pet_friendly" value="1"> 🐾 Pet Friendly
                </label>
                <label class="form-label">
                    <input type="checkbox" name="furnished" value="1"> 🛋️ Furnished
                </label>
                <label class="form-label">
                    <input type="checkbox" name="laundry" value="1"> 🧺 In-Unit Laundry
                </label>
                <label class="form-label">
                    <input type="checkbox" name="parking" value="1"> 🚗 Parking Included
                </label>
            </div>
            
            <div class="form-group" style="margin-top: 1rem;">
                <label class="form-label">Additional Amenities</label>
                <div class="grid grid-cols-2">
                    <label><input type="checkbox" name="amenities[]" value="Air Conditioning"> ❄️ Air Conditioning</label>
                    <label><input type="checkbox" name="amenities[]" value="Heating"> 🔥 Heating</label>
                    <label><input type="checkbox" name="amenities[]" value="Dishwasher"> 🍽️ Dishwasher</label>
                    <label><input type="checkbox" name="amenities[]" value="Refrigerator"> 🧊 Refrigerator</label>
                    <label><input type="checkbox" name="amenities[]" value="Stove/Oven"> 🔥 Stove/Oven</label>
                    <label><input type="checkbox" name="amenities[]" value="Microwave"> 📱 Microwave</label>
                    <label><input type="checkbox" name="amenities[]" value="Gym"> 💪 Gym Access</label>
                    <label><input type="checkbox" name="amenities[]" value="Pool"> 🏊 Pool</label>
                    <label><input type="checkbox" name="amenities[]" value="Security"> 🔒 Security System</label>
                    <label><input type="checkbox" name="amenities[]" value="Balcony"> 🌅 Balcony/Patio</label>
                </div>
            </div>
        </fieldset>
        
        <!-- Photos -->
        <fieldset style="border: 1px solid var(--border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <legend style="padding: 0 0.5rem; font-weight: bold;">Photos</legend>
            
            <div class="form-group">
                <label class="form-label">Upload Property Photos (Max 10 photos, 5MB each)</label>
                <input type="file" class="form-input" name="images[]" accept="image/*" multiple onchange="previewImages(this)">
                <div id="image-preview" style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;"></div>
            </div>
        </fieldset>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Listing</button>
    </form>
</div>

<script>
function previewImages(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>