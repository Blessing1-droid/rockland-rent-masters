<?php
/**
 * property.php - Property Detail Page
 * PRD v1.1: High-quality images, floor plans, video tour embed support, rental application
 */

// Set page metadata
$pageTitle = "Property Details";
$pageDescription = "View detailed information about this rental property including photos, amenities, and location.";

// Include header
require_once __DIR__ . '/../../includes/header.php';

// Get property ID from URL
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($propertyId <= 0) {
    header('Location: ' . BASE_URL . '/pages/renter/search.php');
    exit;
}

// Fetch property details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.id as landlord_id, u.first_name, u.last_name, u.email, u.phone 
        FROM properties p 
        LEFT JOIN users u ON p.landlord_id = u.id 
        WHERE p.id = :id AND p.status = 'active'
    ");
    $stmt->execute([':id' => $propertyId]);
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$property) {
        header('Location: ' . BASE_URL . '/pages/renter/search.php');
        exit;
    }
    
    // Fetch property images
    $stmt = $pdo->prepare("SELECT * FROM property_images WHERE property_id = :property_id ORDER BY sort_order ASC");
    $stmt->execute([':property_id' => $propertyId]);
    $images = $stmt->fetchAll();
    
    // Fetch similar properties
    $stmt = $pdo->prepare("
        SELECT id, title, price, bedrooms, bathrooms, city, main_image 
        FROM properties 
        WHERE city = :city AND id != :id AND status = 'active' 
        LIMIT 3
    ");
    $stmt->execute([':city' => $property['city'], ':id' => $propertyId]);
    $similarProperties = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Property fetch error: " . $e->getMessage());
    header('Location: ' . BASE_URL . '/pages/renter/search.php');
    exit;
}

// Handle rental application submission
$applicationSubmitted = false;
$applicationError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/pages/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    $message = trim($_POST['message'] ?? '');
    $move_in_date = $_POST['move_in_date'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO applications (property_id, user_id, message, move_in_date, status, created_at) 
            VALUES (:property_id, :user_id, :message, :move_in_date, 'pending', NOW())
        ");
        $stmt->execute([
            ':property_id' => $propertyId,
            ':user_id' => $userId,
            ':message' => $message,
            ':move_in_date' => $move_in_date
        ]);
        $applicationSubmitted = true;
        
        // Send email notification to landlord
        sendApplicationNotification($property['email'], $property['title'], $userId);
        
    } catch (PDOException $e) {
        error_log("Application error: " . $e->getMessage());
        $applicationError = "There was an error submitting your application. Please try again.";
    }
}
?>

<div class="container">
    <!-- Property Images Gallery -->
    <div class="property-gallery" style="margin-bottom: 2rem;">
        <div class="main-image" style="position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 1rem;">
            <img id="main-image" src="<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($property['main_image'] ?? 'placeholder.jpg') ?>" 
                 alt="<?= htmlspecialchars($property['title']) ?>"
                 style="width: 100%; height: 500px; object-fit: cover;">
            <div class="property-price-tag" style="position: absolute; bottom: 20px; right: 20px; background: var(--primary-color); color: white; padding: 12px 24px; border-radius: 8px; font-size: 1.5rem; font-weight: bold;">
                $<?= number_format($property['price']) ?>/mo
            </div>
        </div>
        
        <?php if (count($images) > 0): ?>
            <div class="thumbnail-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 1rem;">
                <div class="thumbnail" onclick="document.getElementById('main-image').src='<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($property['main_image']) ?>'">
                    <img src="<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($property['main_image']) ?>" 
                         style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer;">
                </div>
                <?php foreach ($images as $image): ?>
                    <div class="thumbnail" onclick="document.getElementById('main-image').src='<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($image['image_path']) ?>'">
                        <img src="<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($image['image_path']) ?>" 
                             style="width: 100%; height: 80px; object-fit: cover; border-radius: 8px; cursor: pointer;">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="grid grid-cols-3" style="gap: 2rem;">
        <!-- Main Content (2/3 width) -->
        <div style="grid-column: span 2;">
            <h1><?= htmlspecialchars($property['title']) ?></h1>
            
            <div class="property-location" style="margin-bottom: 1rem;">
                📍 <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['state']) ?> <?= htmlspecialchars($property['zipcode']) ?>
            </div>
            
            <div class="property-stats" style="display: flex; gap: 2rem; padding: 1rem; background: var(--bg-light); border-radius: 8px; margin-bottom: 2rem;">
                <div><strong>🛏️ <?= $property['bedrooms'] ?></strong> Bedrooms</div>
                <div><strong>🛁 <?= $property['bathrooms'] ?></strong> Bathrooms</div>
                <div><strong>📐 <?= number_format($property['sqft']) ?></strong> Sq Ft</div>
                <div><strong>🏠 <?= ucfirst($property['property_type']) ?></strong></div>
            </div>
            
            <!-- Description -->
            <div class="property-description" style="margin-bottom: 2rem;">
                <h3>Description</h3>
                <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
            </div>
            
            <!-- Amenities -->
            <?php if (!empty($property['amenities'])): 
                $amenities = json_decode($property['amenities'], true);
                if ($amenities && count($amenities) > 0):
            ?>
                <div class="property-amenities" style="margin-bottom: 2rem;">
                    <h3>Amenities</h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                        <?php foreach ($amenities as $amenity): ?>
                            <div>✓ <?= htmlspecialchars($amenity) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; endif; ?>
            
            <!-- Map Section -->
            <div class="property-map" style="margin-bottom: 2rem;">
                <h3>Location</h3>
                <div id="property-map" style="height: 300px; border-radius: 8px; overflow: hidden;"></div>
            </div>
            
            <!-- Similar Properties -->
            <?php if (count($similarProperties) > 0): ?>
                <div class="similar-properties">
                    <h3>Similar Properties in <?= htmlspecialchars($property['city']) ?></h3>
                    <div class="property-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <?php foreach ($similarProperties as $similar): ?>
                            <div class="property-card">
                                <div class="property-card-image">
                                    <img src="<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($similar['main_image'] ?? 'placeholder.jpg') ?>" 
                                         alt="<?= htmlspecialchars($similar['title']) ?>"
                                         style="height: 150px; object-fit: cover;">
                                    <div class="property-card-price">$<?= number_format($similar['price']) ?>/mo</div>
                                </div>
                                <div class="property-card-content">
                                    <h4 style="font-size: 1rem;"><?= htmlspecialchars($similar['title']) ?></h4>
                                    <div style="font-size: 0.875rem; color: #666;">
                                        🛏️ <?= $similar['bedrooms'] ?> beds • 🛁 <?= $similar['bathrooms'] ?> baths
                                    </div>
                                    <a href="<?= BASE_URL ?>/pages/renter/property.php?id=<?= $similar['id'] ?>" class="btn btn-outline btn-small" style="margin-top: 0.5rem; width: 100%;">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar (1/3 width) -->
        <div>
            <!-- Contact Landlord Card -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3>Contact Landlord</h3>
                </div>
                <div class="card-body">
                    <p><strong><?= htmlspecialchars($property['first_name'] ?? 'Property Manager') ?></strong></p>
                    <?php if (!empty($property['email'])): ?>
                        <p>📧 <?= htmlspecialchars($property['email']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($property['phone'])): ?>
                        <p>📞 <?= htmlspecialchars($property['phone']) ?></p>
                    <?php endif; ?>
                    
                    <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="openContactModal()">
                        📩 Send Message
                    </button>
                </div>
            </div>
            
            <!-- Apply Now Card -->
            <div class="card" style="margin-bottom: 1.5rem; border: 2px solid var(--primary-color);">
                <div class="card-header" style="background: var(--primary-color); color: white;">
                    <h3 style="color: white;">Ready to Apply?</h3>
                </div>
                <div class="card-body">
                    <p>Submit your rental application instantly. Landlords review applications within 24-48 hours.</p>
                    <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="openApplicationModal()">
                        📝 Apply Now
                    </button>
                </div>
            </div>
            
            <!-- Property Features -->
            <div class="card">
                <div class="card-header">
                    <h3>Property Features</h3>
                </div>
                <div class="card-body">
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;">✓ Available: Immediately</li>
                        <?php if ($property['pet_friendly']): ?>
                            <li style="margin-bottom: 0.5rem;">✓ Pet Friendly</li>
                        <?php endif; ?>
                        <?php if ($property['furnished']): ?>
                            <li style="margin-bottom: 0.5rem;">✓ Furnished</li>
                        <?php endif; ?>
                        <?php if ($property['laundry']): ?>
                            <li style="margin-bottom: 0.5rem;">✓ In-Unit Laundry</li>
                        <?php endif; ?>
                        <?php if ($property['parking']): ?>
                            <li style="margin-bottom: 0.5rem;">✓ Parking Included</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div id="contact-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Send Message to Landlord</h3>
            <button class="modal-close" onclick="closeContactModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="contact-form" method="POST">
                <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-textarea" name="message" rows="5" required placeholder="I'm interested in this property..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Your Phone (optional)</label>
                    <input type="tel" class="form-input" name="phone" placeholder="Best number to reach you">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeContactModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitContactForm(<?= $propertyId ?>)">Send Message</button>
        </div>
    </div>
</div>

<!-- Application Modal -->
<div id="application-modal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Rental Application</h3>
            <button class="modal-close" onclick="closeApplicationModal()">&times;</button>
        </div>
        <div class="modal-body">
            <?php if ($applicationSubmitted): ?>
                <div class="alert alert-success">
                    ✅ Application submitted successfully! The landlord will contact you within 24-48 hours.
                </div>
            <?php elseif ($applicationError): ?>
                <div class="alert alert-error">
                    ❌ <?= htmlspecialchars($applicationError) ?>
                </div>
            <?php endif; ?>
            
            <form id="application-form" method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" class="form-input" name="full_name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" class="form-input" name="email" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone *</label>
                    <input type="tel" class="form-input" name="phone" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Current Address</label>
                    <input type="text" class="form-input" name="current_address">
                </div>
                <div class="form-group">
                    <label class="form-label">Employment Status</label>
                    <select class="form-select" name="employment_status">
                        <option value="employed">Employed Full-time</option>
                        <option value="self-employed">Self-Employed</option>
                        <option value="student">Student</option>
                        <option value="unemployed">Unemployed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Income ($)</label>
                    <input type="number" class="form-input" name="monthly_income" placeholder="e.g., 5000">
                </div>
                <div class="form-group">
                    <label class="form-label">Move-in Date</label>
                    <input type="date" class="form-input" name="move_in_date">
                </div>
                <div class="form-group">
                    <label class="form-label">Additional Notes</label>
                    <textarea class="form-textarea" name="notes" rows="3" placeholder="Any additional information you'd like to share..."></textarea>
                </div>
                <input type="hidden" name="property_id" value="<?= $propertyId ?>">
                <input type="hidden" name="submit_application" value="1">
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeApplicationModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitApplicationForm()">Submit Application</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize map
function initMap() {
    const lat = <?= $property['latitude'] ?? 41.8781 ?>;
    const lng = <?= $property['longitude'] ?? -87.6298 ?>;
    
    const map = L.map('property-map').setView([lat, lng], 13);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    
    L.marker([lat, lng]).addTo(map)
        .bindPopup('<?= htmlspecialchars($property['title']) ?>')
        .openPopup();
}

// Modal functions
function openContactModal() {
    document.getElementById('contact-modal').classList.add('active');
}

function closeContactModal() {
    document.getElementById('contact-modal').classList.remove('active');
}

function openApplicationModal() {
    document.getElementById('application-modal').classList.add('active');
}

function closeApplicationModal() {
    document.getElementById('application-modal').classList.remove('active');
}

function submitContactForm(propertyId) {
    const form = document.getElementById('contact-form');
    const formData = new FormData(form);
    formData.append('property_id', propertyId);
    formData.append('action', 'contact');
    
    fetch('<?= BASE_URL ?>/api/send-message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Message sent successfully!', 'success');
            closeContactModal();
        } else {
            showToast(data.message || 'Error sending message', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error sending message', 'error');
    });
}

function submitApplicationForm() {
    const form = document.getElementById('application-form');
    form.submit();
}

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', initMap);
</script>

<?php
// Helper function to send email notification
function sendApplicationNotification($landlordEmail, $propertyTitle, $userId) {
    // Implement email notification using PHPMailer
    // This is a placeholder - implement based on your email setup
    error_log("Application submitted for property: {$propertyTitle}");
}

require_once __DIR__ . '/../../includes/footer.php';
?>