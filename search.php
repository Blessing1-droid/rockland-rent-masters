<?php
/**
 * search.php - Property Search & Listing Page
 * High priority: Search, filter, pagination
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$pageTitle = "Search Properties - Find Your Perfect Rental";
$pageDescription = "Browse thousands of rental properties in Rockland. Filter by price, bedrooms, location, and more.";

// Get filter parameters
$location = $_GET['location'] ?? '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 10000;
$bedrooms = isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : 0;
$bathrooms = isset($_GET['bathrooms']) ? (float)$_GET['bathrooms'] : 0;
$property_type = $_GET['property_type'] ?? '';
$pet_friendly = isset($_GET['pet_friendly']) ? 1 : 0;
$furnished = isset($_GET['furnished']) ? 1 : 0;
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'DESC';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Build query
try {
    $sql = "SELECT p.*, u.first_name, u.last_name 
            FROM properties p 
            LEFT JOIN users u ON p.landlord_id = u.id 
            WHERE p.status = 'active'";
    $countSql = "SELECT COUNT(*) as total FROM properties p WHERE p.status = 'active'";
    $params = [];
    
    if (!empty($location)) {
        $sql .= " AND (p.address LIKE :location OR p.city LIKE :location OR p.zipcode LIKE :location)";
        $countSql .= " AND (address LIKE :location OR city LIKE :location OR zipcode LIKE :location)";
        $params[':location'] = "%{$location}%";
    }
    
    if ($min_price > 0) {
        $sql .= " AND p.price >= :min_price";
        $countSql .= " AND price >= :min_price";
        $params[':min_price'] = $min_price;
    }
    
    if ($max_price < 10000) {
        $sql .= " AND p.price <= :max_price";
        $countSql .= " AND price <= :max_price";
        $params[':max_price'] = $max_price;
    }
    
    if ($bedrooms > 0) {
        $sql .= " AND p.bedrooms >= :bedrooms";
        $countSql .= " AND bedrooms >= :bedrooms";
        $params[':bedrooms'] = $bedrooms;
    }
    
    if ($bathrooms > 0) {
        $sql .= " AND p.bathrooms >= :bathrooms";
        $countSql .= " AND bathrooms >= :bathrooms";
        $params[':bathrooms'] = $bathrooms;
    }
    
    if (!empty($property_type)) {
        $sql .= " AND p.property_type = :property_type";
        $countSql .= " AND property_type = :property_type";
        $params[':property_type'] = $property_type;
    }
    
    if ($pet_friendly == 1) {
        $sql .= " AND p.pet_friendly = 1";
        $countSql .= " AND pet_friendly = 1";
    }
    
    if ($furnished == 1) {
        $sql .= " AND p.furnished = 1";
        $countSql .= " AND furnished = 1";
    }
    
    // Sorting
    $allowedSorts = ['price', 'bedrooms', 'created_at', 'views'];
    $sort_by = in_array($sort_by, $allowedSorts) ? $sort_by : 'created_at';
    $sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';
    $sql .= " ORDER BY p.{$sort_by} {$sort_order}";
    
    // Pagination
    $sql .= " LIMIT :offset, :per_page";
    
    // Get total count
    $stmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalResults = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalResults / $per_page);
    
    // Get properties
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
    $stmt->execute();
    $properties = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Search error: " . $e->getMessage());
    $properties = [];
    $totalResults = 0;
    $totalPages = 0;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="rrm-search-container">
    <div class="rrm-container">
        <!-- Filters Sidebar -->
        <div class="rrm-search-layout">
            <aside class="rrm-filters-sidebar">
                <h3>🔍 Filter Properties</h3>
                
                <form method="GET" action="" class="rrm-filters-form">
                    <!-- Location -->
                    <div class="rrm-filter-group">
                        <label>📍 Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="City or ZIP">
                    </div>
                    
                    <!-- Price Range -->
                    <div class="rrm-filter-group">
                        <label>💰 Price Range</label>
                        <div class="rrm-price-range">
                            <input type="number" name="min_price" placeholder="Min" value="<?= $min_price ?>">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?= $max_price ?>">
                        </div>
                    </div>
                    
                    <!-- Bedrooms -->
                    <div class="rrm-filter-group">
                        <label>🛏️ Bedrooms</label>
                        <select name="bedrooms">
                            <option value="0">Any</option>
                            <option value="1" <?= $bedrooms == 1 ? 'selected' : '' ?>>1+</option>
                            <option value="2" <?= $bedrooms == 2 ? 'selected' : '' ?>>2+</option>
                            <option value="3" <?= $bedrooms == 3 ? 'selected' : '' ?>>3+</option>
                            <option value="4" <?= $bedrooms == 4 ? 'selected' : '' ?>>4+</option>
                        </select>
                    </div>
                    
                    <!-- Property Type -->
                    <div class="rrm-filter-group">
                        <label>🏠 Property Type</label>
                        <select name="property_type">
                            <option value="">Any</option>
                            <option value="apartment" <?= $property_type == 'apartment' ? 'selected' : '' ?>>Apartment</option>
                            <option value="house" <?= $property_type == 'house' ? 'selected' : '' ?>>House</option>
                            <option value="condo" <?= $property_type == 'condo' ? 'selected' : '' ?>>Condo</option>
                            <option value="studio" <?= $property_type == 'studio' ? 'selected' : '' ?>>Studio</option>
                        </select>
                    </div>
                    
                    <!-- Amenities -->
                    <div class="rrm-filter-group">
                        <label>✨ Amenities</label>
                        <label class="rrm-checkbox-filter">
                            <input type="checkbox" name="pet_friendly" value="1" <?= $pet_friendly ? 'checked' : ?>>
                            🐾 Pet Friendly
                        </label>
                        <label class="rrm-checkbox-filter">
                            <input type="checkbox" name="furnished" value="1" <?= $furnished ? 'checked' : ?>>
                            🛋️ Furnished
                        </label>
                    </div>
                    
                    <!-- Sort -->
                    <div class="rrm-filter-group">
                        <label>📊 Sort By</label>
                        <select name="sort_by">
                            <option value="created_at" <?= $sort_by == 'created_at' ? 'selected' : '' ?>>Newest First</option>
                            <option value="price" <?= $sort_by == 'price' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price&sort_order=DESC" <?= $sort_by == 'price' && $sort_order == 'DESC' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="bedrooms" <?= $sort_by == 'bedrooms' ? 'selected' : '' ?>>Bedrooms</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="rrm-btn-filter">Apply Filters</button>
                    <a href="<?= BASE_URL ?>/pages/renter/search.php" class="rrm-btn-reset">Reset</a>
                </form>
            </aside>
            
            <!-- Results Section -->
            <div class="rrm-results-section">
                <div class="rrm-results-header">
                    <h2><?= number_format($totalResults) ?> Properties Found</h2>
                    <div class="rrm-results-view">
                        <button class="view-btn active" data-view="grid">📱 Grid</button>
                        <button class="view-btn" data-view="list">📋 List</button>
                    </div>
                </div>
                
                <?php if (empty($properties)): ?>
                    <div class="rrm-no-results">
                        <div class="no-results-icon">🔍</div>
                        <h3>No properties found</h3>
                        <p>Try adjusting your filters or search criteria</p>
                        <a href="<?= BASE_URL ?>/pages/renter/search.php" class="rrm-btn-primary">Clear all filters</a>
                    </div>
                <?php else: ?>
                    <div class="rrm-properties-grid" id="propertiesGrid">
                        <?php foreach ($properties as $property): ?>
                            <div class="rrm-property-card">
                                <div class="property-card-image">
                                    <img src="<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($property['main_image'] ?? 'placeholder.jpg') ?>" 
                                         alt="<?= htmlspecialchars($property['title']) ?>"
                                         onerror="this.src='https://placehold.co/400x300/1c2840/d4a842?text=No+Image'">
                                    <div class="property-price">$<?= number_format($property['price']) ?>/mo</div>
                                    <?php if ($property['pet_friendly']): ?>
                                        <div class="property-badge pet">🐾 Pet Friendly</div>
                                    <?php endif; ?>
                                </div>
                                <div class="property-card-content">
                                    <h3><?= htmlspecialchars($property['title']) ?></h3>
                                    <div class="property-location">
                                        📍 <?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['state']) ?>
                                    </div>
                                    <div class="property-features">
                                        <span>🛏️ <?= $property['bedrooms'] ?> beds</span>
                                        <span>🛁 <?= $property['bathrooms'] ?> baths</span>
                                        <span>📐 <?= number_format($property['sqft']) ?> sqft</span>
                                    </div>
                                    <div class="property-footer">
                                        <div class="property-agent">
                                            <div class="agent-avatar">
                                                <?= strtoupper(substr($property['first_name'] ?? 'H', 0, 1)) ?>
                                            </div>
                                            <span><?= htmlspecialchars($property['first_name'] ?? 'Host') ?></span>
                                        </div>
                                        <a href="<?= BASE_URL ?>/pages/renter/property.php?id=<?= $property['id'] ?>" class="property-btn">
                                            View Details →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="rrm-pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-link">← Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="page-link active"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-link"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-link">Next →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Search Page Styles */
.rrm-search-container {
    padding: 100px 0 60px;
    background: linear-gradient(135deg, #0b1120 0%, #111827 100%);
    min-height: 100vh;
}

.rrm-search-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 40px;
}

/* Filters Sidebar */
.rrm-filters-sidebar {
    background: #1c2840;
    border-radius: 20px;
    padding: 24px;
    border: 1px solid rgba(212, 168, 66, 0.15);
    position: sticky;
    top: 100px;
    height: fit-content;
}

.rrm-filters-sidebar h3 {
    font-size: 18px;
    margin-bottom: 20px;
    color: #d4a842;
}

.rrm-filter-group {
    margin-bottom: 20px;
}

.rrm-filter-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
}

.rrm-filter-group input,
.rrm-filter-group select {
    width: 100%;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: white;
    font-size: 14px;
}

.rrm-price-range {
    display: flex;
    gap: 10px;
    align-items: center;
}

.rrm-price-range input {
    flex: 1;
}

.rrm-price-range span {
    color: rgba(255, 255, 255, 0.5);
}

.rrm-checkbox-filter {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    margin-bottom: 10px;
    font-size: 13px;
}

.rrm-checkbox-filter input {
    width: 16px;
    height: 16px;
    accent-color: #d4a842;
}

.rrm-btn-filter {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #d4a842, #b8882e);
    color: #0b1120;
    border: none;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 10px;
}

.rrm-btn-reset {
    display: block;
    text-align: center;
    padding: 10px;
    background: transparent;
    border: 1px solid rgba(212, 168, 66, 0.3);
    color: #d4a842;
    border-radius: 40px;
    text-decoration: none;
    font-size: 13px;
}

/* Results Section */
.rrm-results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.rrm-results-header h2 {
    font-size: 24px;
    color: white;
}

.rrm-results-view {
    display: flex;
    gap: 10px;
}

.view-btn {
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
}

.view-btn.active {
    background: #d4a842;
    color: #0b1120;
    border-color: #d4a842;
}

/* Properties Grid */
.rrm-properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.rrm-properties-grid.list-view {
    grid-template-columns: 1fr;
}

.rrm-property-card {
    background: #1c2840;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(212, 168, 66, 0.1);
    transition: transform 0.3s;
}

.rrm-property-card:hover {
    transform: translateY(-4px);
    border-color: rgba(212, 168, 66, 0.3);
}

.property-card-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.property-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.property-price {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: #d4a842;
    color: #0b1120;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.property-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    color: white;
}

.property-badge.pet {
    background: #10b981;
}

.property-card-content {
    padding: 16px;
}

.property-card-content h3 {
    font-size: 18px;
    margin-bottom: 8px;
    color: white;
}

.property-location {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 12px;
}

.property-features {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
    padding: 12px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    margin-bottom: 12px;
}

.property-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.property-agent {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
}

.agent-avatar {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #d4a842, #b8882e);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: #0b1120;
}

.property-btn {
    background: transparent;
    border: 1px solid #d4a842;
    color: #d4a842;
    padding: 6px 14px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 12px;
    transition: all 0.3s;
}

.property-btn:hover {
    background: #d4a842;
    color: #0b1120;
}

/* No Results */
.rrm-no-results {
    text-align: center;
    padding: 60px 20px;
    background: #1c2840;
    border-radius: 20px;
}

.no-results-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.rrm-no-results h3 {
    font-size: 24px;
    margin-bottom: 10px;
}

.rrm-no-results p {
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 20px;
}

/* Pagination */
.rrm-pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.page-link {
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    transition: all 0.3s;
}

.page-link:hover,
.page-link.active {
    background: #d4a842;
    color: #0b1120;
    border-color: #d4a842;
}

/* Responsive */
@media (max-width: 992px) {
    .rrm-search-layout {
        grid-template-columns: 1fr;
    }
    
    .rrm-filters-sidebar {
        position: static;
    }
}

@media (max-width: 768px) {
    .rrm-search-container {
        padding: 80px 0 40px;
    }
    
    .rrm-properties-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .rrm-results-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
// Grid/List view toggle
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const view = this.dataset.view;
        const grid = document.getElementById('propertiesGrid');
        
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        if (view === 'list') {
            grid.classList.add('list-view');
        } else {
            grid.classList.remove('list-view');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>