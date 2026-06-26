<?php
/**
 * index.php — Rockland Rent Masters Homepage
 * Modernized: dark-luxury aesthetic, glassmorphism, bento cities,
 * animated stats, floating property cards, testimonial carousel, modern CTA
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle       = "Rockland Rent Masters — Find Your Dream Home";
$pageDescription = "Discover thousands of verified rental properties in Rockland. Search, compare, and apply instantly.";

require_once __DIR__ . '/includes/header.php';

// ── Fetch featured properties ──────────────────────────────────────────────
$featuredProperties = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.first_name, u.last_name
        FROM   properties p
        LEFT JOIN users u ON p.landlord_id = u.id
        WHERE  p.status = 'active'
        ORDER  BY p.created_at DESC
        LIMIT  6
    ");
    $stmt->execute();
    $featuredProperties = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredProperties = [];
}
?>

<!-- ═══════════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════════ -->
<section class="rrm-hero" id="hero">

    <!-- layered background -->
    <div class="rrm-hero__bg">
        <video class="rrm-hero__video" autoplay loop muted playsinline
               poster="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1920&h=1080&fit=crop">
            <source src="https://assets.mixkit.co/videos/preview/mixkit-house-with-modern-architecture-4070-large.mp4" type="video/mp4">
        </video>
        <div class="rrm-hero__overlay"></div>
        <div class="rrm-hero__grain"></div>
    </div>

    <div class="rrm-container rrm-hero__inner">

        <!-- eyebrow badge -->
        <div class="rrm-eyebrow">
            <span class="rrm-eyebrow__dot"></span>
            Trusted by 10,000+ renters across Rockland
        </div>

        <!-- headline -->
        <h1 class="rrm-hero__title">
            Find the Home<br>
            <em>You Deserve</em>
        </h1>

        <p class="rrm-hero__sub">
            Thousands of verified listings. One seamless experience.
        </p>

        <!-- ── Glassmorphism search bar ────────────────── -->
        <div class="rrm-search">
            <form action="<?= BASE_URL ?>/pages/renter/search.php" method="GET" class="rrm-search__form">

                <div class="rrm-search__field">
                    <svg class="rrm-search__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 21c0-6-8-9-8-14a8 8 0 0 1 16 0c0 5-8 8-8 14z"/><circle cx="12" cy="8" r="3"/>
                    </svg>
                    <input type="text" name="location" placeholder="City or ZIP code" autocomplete="address-level2">
                </div>

                <div class="rrm-search__divider"></div>

                <div class="rrm-search__field">
                    <svg class="rrm-search__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="7" width="20" height="14" rx="2"/>
                        <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                    </svg>
                    <select name="property_type">
                        <option value="">Property Type</option>
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="condo">Condo</option>
                        <option value="studio">Studio</option>
                    </select>
                </div>

                <div class="rrm-search__divider"></div>

                <div class="rrm-search__field">
                    <svg class="rrm-search__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                    <select name="max_price">
                        <option value="">Max Budget</option>
                        <option value="1000">$1,000 / mo</option>
                        <option value="2000">$2,000 / mo</option>
                        <option value="3000">$3,000 / mo</option>
                        <option value="5000">$5,000 / mo</option>
                    </select>
                </div>

                <button type="submit" class="rrm-search__btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                    Search
                </button>
            </form>

            <!-- quick tags -->
            <div class="rrm-search__tags">
                <span class="rrm-tag-label">Popular:</span>
                <a href="<?= BASE_URL ?>/pages/renter/search.php?location=Downtown" class="rrm-tag">Downtown</a>
                <a href="<?= BASE_URL ?>/pages/renter/search.php?property_type=studio" class="rrm-tag">Studio</a>
                <a href="<?= BASE_URL ?>/pages/renter/search.php?max_price=1500" class="rrm-tag">Under $1,500</a>
                <a href="<?= BASE_URL ?>/pages/renter/search.php?pet_friendly=1" class="rrm-tag">Pet Friendly</a>
            </div>
        </div>

        <!-- trust row -->
        <div class="rrm-trust">
            <div class="rrm-trust__item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Verified Listings
            </div>
            <div class="rrm-trust__sep"></div>
            <div class="rrm-trust__item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Secure Payments
            </div>
            <div class="rrm-trust__sep"></div>
            <div class="rrm-trust__item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                Best Price Guarantee
            </div>
        </div>
    </div>

    <!-- scroll cue -->
    <div class="rrm-scroll-cue">
        <div class="rrm-scroll-cue__mouse">
            <div class="rrm-scroll-cue__wheel"></div>
        </div>
        <span>Scroll to explore</span>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     STATISTICS
════════════════════════════════════════════════════════════════ -->
<section class="rrm-stats">
    <div class="rrm-container">
        <div class="rrm-stats__grid">

            <div class="rrm-stat js-stat" data-target="15000" data-suffix="+">
                <div class="rrm-stat__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 9.5L12 4l9 5.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>
                        <polyline points="9 21 9 12 15 12 15 21"/>
                    </svg>
                </div>
                <div class="rrm-stat__number js-counter">0</div>
                <div class="rrm-stat__label">Happy Renters</div>
                <div class="rrm-stat__trend">↑ 23% this year</div>
            </div>

            <div class="rrm-stat js-stat" data-target="2500" data-suffix="+">
                <div class="rrm-stat__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <div class="rrm-stat__number js-counter">0</div>
                <div class="rrm-stat__label">Active Listings</div>
                <div class="rrm-stat__trend">↑ 15% this year</div>
            </div>

            <div class="rrm-stat js-stat" data-target="98" data-suffix="%">
                <div class="rrm-stat__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div class="rrm-stat__number js-counter">0</div>
                <div class="rrm-stat__label">Satisfaction Rate</div>
                <div class="rrm-stat__trend">4.9 / 5 average</div>
            </div>

            <div class="rrm-stat js-stat" data-target="24" data-suffix="h">
                <div class="rrm-stat__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div class="rrm-stat__number js-counter">0</div>
                <div class="rrm-stat__label">Response Time</div>
                <div class="rrm-stat__trend">Always available</div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     FEATURED PROPERTIES
════════════════════════════════════════════════════════════════ -->
<section class="rrm-section rrm-featured">
    <div class="rrm-container">

        <header class="rrm-section__hd">
            <div class="rrm-pill">Featured Listings</div>
            <h2>Hand-picked <span class="rrm-gold">Properties</span></h2>
            <p>Our most popular rentals in prime Rockland locations</p>
        </header>

        <div class="rrm-props__grid">
            <?php if (!empty($featuredProperties)): ?>
                <?php foreach ($featuredProperties as $i => $p): ?>
                <div class="rrm-prop">
                    <div class="rrm-prop__img">
                        <img src="<?= BASE_URL ?>/uploads/properties/<?= htmlspecialchars($p['main_image'] ?? 'placeholder.jpg') ?>"
                             alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy"
                             onerror="this.src='https://placehold.co/600x400/1c2840/d4a842?text=Property'">
                        <div class="rrm-prop__price">
                            $<?= number_format($p['price']) ?><span>/mo</span>
                        </div>
                        <?php if ($p['pet_friendly'] ?? false): ?>
                        <div class="rrm-prop__badge">🐾 Pet Friendly</div>
                        <?php endif; ?>
                        <div class="rrm-prop__fav" aria-label="Save property">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </div>
                    </div>
                    <div class="rrm-prop__body">
                        <h3><?= htmlspecialchars($p['title']) ?></h3>
                        <div class="rrm-prop__loc">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($p['city']) ?>, <?= htmlspecialchars($p['state']) ?>
                        </div>
                        <div class="rrm-prop__feats">
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20v-4a5 5 0 0 1 5-5h10a5 5 0 0 1 5 5v4"/><circle cx="12" cy="7" r="4"/></svg><?= $p['bedrooms'] ?> beds</span>
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16"/><path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6H4z"/><path d="M6 18v2m12-2v2"/></svg><?= $p['bathrooms'] ?> baths</span>
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg><?= number_format($p['sqft']) ?> sqft</span>
                        </div>
                        <div class="rrm-prop__foot">
                            <div class="rrm-prop__host">
                                <div class="rrm-prop__avatar"><?= strtoupper(substr($p['first_name'] ?? 'H', 0, 1)) ?></div>
                                <span><?= htmlspecialchars($p['first_name'] ?? 'Premium Host') ?></span>
                            </div>
                            <a href="<?= BASE_URL ?>/pages/renter/property.php?id=<?= $p['id'] ?>" class="rrm-prop__cta">
                                View Details
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            <?php else: ?>
                <?php
                $demos = [
                    ['Modern Loft · City Center','Rockland','NY','$2,400',2,2,'1,200'],
                    ['Sunny Garden Studio','Westside','NY','$1,150',0,1,'540'],
                    ['Spacious Family Home','Northwood','NY','$3,100',4,2,'2,400'],
                    ['Cozy Condo w/ Gym','Eastside','NY','$1,850',1,1,'780'],
                    ['Riverfront Penthouse','Downtown','NY','$4,500',3,2,'1,950'],
                    ['Classic Brownstone','Rockland','NY','$2,200',2,1,'1,100'],
                ];
                $pImgs = [
                    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=600&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=600&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=600&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&h=400&fit=crop',
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=600&h=400&fit=crop',
                ];
                foreach ($demos as $i => [$title,$city,$state,$price,$beds,$baths,$sqft]):
                ?>
                <div class="rrm-prop">
                    <div class="rrm-prop__img">
                        <img src="<?= $pImgs[$i] ?>" alt="<?= $title ?>" loading="lazy">
                        <div class="rrm-prop__price"><?= $price ?><span>/mo</span></div>
                        <div class="rrm-prop__fav" aria-label="Save property">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </div>
                    </div>
                    <div class="rrm-prop__body">
                        <h3><?= $title ?></h3>
                        <div class="rrm-prop__loc">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= $city ?>, <?= $state ?>
                        </div>
                        <div class="rrm-prop__feats">
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 20v-4a5 5 0 0 1 5-5h10a5 5 0 0 1 5 5v4"/><circle cx="12" cy="7" r="4"/></svg><?= $beds ?> beds</span>
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16"/><path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6H4z"/><path d="M6 18v2m12-2v2"/></svg><?= $baths ?> baths</span>
                            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg><?= $sqft ?> sqft</span>
                        </div>
                        <div class="rrm-prop__foot">
                            <div class="rrm-prop__host">
                                <div class="rrm-prop__avatar">P</div>
                                <span>Premium Host</span>
                            </div>
                            <a href="<?= BASE_URL ?>/pages/renter/search.php" class="rrm-prop__cta">
                                View Details
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="rrm-view-all">
            <a href="<?= BASE_URL ?>/pages/renter/search.php" class="rrm-btn-outline">
                Browse All Properties
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     POPULAR LOCATIONS — Bento Grid
════════════════════════════════════════════════════════════════ -->
<section class="rrm-section rrm-cities">
    <div class="rrm-container">

        <header class="rrm-section__hd">
            <div class="rrm-pill">Popular Locations</div>
            <h2>Browse by <span class="rrm-gold">Neighborhood</span></h2>
            <p>Explore the most sought-after areas in Rockland</p>
        </header>

        <div class="rrm-cities__bento">

            <a href="<?= BASE_URL ?>/pages/renter/search.php?location=Downtown" class="rrm-city rrm-city--lg"
               style="background-image:url('https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=800&h=600&fit=crop')">
                <div class="rrm-city__glass">
                    <h3>Downtown Rockland</h3>
                    <p>245 listings</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>/pages/renter/search.php?location=Westside" class="rrm-city"
               style="background-image:url('https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=600&h=400&fit=crop')">
                <div class="rrm-city__glass">
                    <h3>Westside Village</h3>
                    <p>189 listings</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>/pages/renter/search.php?location=Northwood" class="rrm-city"
               style="background-image:url('https://images.unsplash.com/photo-1519501025264-65ba15a82390?w=600&h=400&fit=crop')">
                <div class="rrm-city__glass">
                    <h3>Northwood Heights</h3>
                    <p>156 listings</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>/pages/renter/search.php?location=Eastside" class="rrm-city"
               style="background-image:url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=600&h=400&fit=crop')">
                <div class="rrm-city__glass">
                    <h3>Eastside Gardens</h3>
                    <p>98 listings</p>
                </div>
            </a>

            <a href="<?= BASE_URL ?>/pages/renter/search.php?location=Riverfront" class="rrm-city"
               style="background-image:url('https://images.unsplash.com/photo-1555636222-cae831e670b3?w=600&h=400&fit=crop')">
                <div class="rrm-city__glass">
                    <h3>Riverfront District</h3>
                    <p>73 listings</p>
                </div>
            </a>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════════════════════════════════ -->
<section class="rrm-section rrm-how">
    <div class="rrm-container">

        <header class="rrm-section__hd">
            <div class="rrm-pill">Simple Process</div>
            <h2>Three Steps to <span class="rrm-gold">Home</span></h2>
            <p>From search to signed lease in record time</p>
        </header>

        <div class="rrm-how__grid">

            <div class="rrm-step">
                <div class="rrm-step__num">01</div>
                <div class="rrm-step__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <h3>Search &amp; Compare</h3>
                <p>Use advanced filters to surface properties that match your budget, size, and neighbourhood preferences.</p>
            </div>

            <div class="rrm-step__connector" aria-hidden="true">
                <svg viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4">
                    <path d="M0 12h40M32 4l8 8-8 8"/>
                </svg>
            </div>

            <div class="rrm-step">
                <div class="rrm-step__num">02</div>
                <div class="rrm-step__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <h3>Apply Instantly</h3>
                <p>Submit a one-click application and schedule viewings directly with landlords — no back-and-forth emails.</p>
            </div>

            <div class="rrm-step__connector" aria-hidden="true">
                <svg viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 4">
                    <path d="M0 12h40M32 4l8 8-8 8"/>
                </svg>
            </div>

            <div class="rrm-step">
                <div class="rrm-step__num">03</div>
                <div class="rrm-step__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 9.5L12 4l9 5.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><polyline points="9 21 9 12 15 12 15 21"/>
                    </svg>
                </div>
                <h3>Move In &amp; Enjoy</h3>
                <p>Sign a digital lease and receive your keys. Our support team is here every step of the way.</p>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     TESTIMONIALS
════════════════════════════════════════════════════════════════ -->
<section class="rrm-section rrm-testi">
    <div class="rrm-container">

        <header class="rrm-section__hd">
            <div class="rrm-pill">Testimonials</div>
            <h2>Loved by <span class="rrm-gold">Real People</span></h2>
            <p>Stories from renters and landlords who found their match</p>
        </header>

        <div class="rrm-testi__track-wrap">
            <div class="rrm-testi__track" id="testiTrack">

                <?php
                $reviews = [
                    ['S','Sarah M.','Renter','Found my dream apartment in 3 days. The search filters are incredible and the application was completely seamless. I will never rent any other way.'],
                    ['J','James D.','Landlord','Managing 4 units used to be a headache. The dashboard and tenant-screening tools changed everything — I save 6 hours a week.'],
                    ['E','Emily R.','Renter','Virtual tours and neighbourhood insights let me decide before I even visited in person. Moved in two weeks after signing up.'],
                    ['M','Marcus T.','Renter','The rent calculator helped me stick to budget. Found a pet-friendly studio under $1,400. Genuinely impressed.'],
                    ['A','Aisha K.','Landlord','Listed my property on a Saturday, had 12 qualified leads by Monday morning. The platform does the heavy lifting.'],
                    ['L','Laura P.','Renter','Customer support answered my question in under an hour. Real humans, real help. 10/10 would recommend.'],
                ];
                foreach ($reviews as [$initial,$name,$role,$text]):
                ?>
                <div class="rrm-testi__card">
                    <div class="rrm-testi__stars">★★★★★</div>
                    <blockquote>"<?= htmlspecialchars($text) ?>"</blockquote>
                    <div class="rrm-testi__author">
                        <div class="rrm-testi__avatar"><?= $initial ?></div>
                        <div>
                            <strong><?= htmlspecialchars($name) ?></strong>
                            <span><?= htmlspecialchars($role) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- nav dots -->
        <div class="rrm-testi__dots" id="testiDots"></div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     RENT CALCULATOR
════════════════════════════════════════════════════════════════ -->
<section class="rrm-section rrm-calc">
    <div class="rrm-container">
        <div class="rrm-calc__box">

            <div class="rrm-calc__copy">
                <div class="rrm-pill rrm-pill--dark">Affordability Tool</div>
                <h2>How much rent<br>can you <span class="rrm-gold">afford?</span></h2>
                <p>Use our free calculator to estimate the right budget based on your income and existing expenses — and discover listings that fit.</p>
                <a href="<?= BASE_URL ?>/pages/renter/search.php" class="rrm-btn-ghost">Browse matching listings →</a>
            </div>

            <div class="rrm-calc__form">
                <div class="rrm-calc__field">
                    <label for="calcIncome">Monthly Income</label>
                    <div class="rrm-calc__input-wrap">
                        <span>$</span>
                        <input type="number" id="calcIncome" value="5000" step="100" min="0">
                    </div>
                </div>
                <div class="rrm-calc__field">
                    <label for="calcExpenses">Monthly Expenses</label>
                    <div class="rrm-calc__input-wrap">
                        <span>$</span>
                        <input type="number" id="calcExpenses" value="1500" step="100" min="0">
                    </div>
                </div>

                <div class="rrm-calc__result">
                    <div class="rrm-calc__result-label">Recommended max rent</div>
                    <div class="rrm-calc__result-value" id="calcResult">$1,050</div>
                    <div class="rrm-calc__result-note">Based on the 30% rule</div>
                    <div class="rrm-calc__bar-wrap">
                        <div class="rrm-calc__bar" id="calcBar" style="width:30%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════════
     CTA BANNER
════════════════════════════════════════════════════════════════ -->
<section class="rrm-cta">
    <div class="rrm-cta__bg"></div>
    <div class="rrm-container rrm-cta__inner">
        <div class="rrm-cta__copy">
            <h2>Ready to Find<br>Your New Home?</h2>
            <p>Join thousands of happy renters who found their perfect place with Rockland Rent Masters.</p>
        </div>
        <div class="rrm-cta__actions">
            <a href="<?= BASE_URL ?>/pages/renter/search.php" class="rrm-btn-solid">
                Start Searching
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="<?= BASE_URL ?>/pages/auth/register.php" class="rrm-btn-border">
                Create Free Account
            </a>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════════════════════
     STYLES
════════════════════════════════════════════════════════════════ -->
<style>
/* ── Tokens ─────────────────────────────────────────────────── */
:root {
    --rrm-navy:   #0b1120;
    --rrm-navy2:  #111827;
    --rrm-navy3:  #1c2840;
    --rrm-gold:   #d4a842;
    --rrm-gold2:  #e8c46a;
    --rrm-text:   #f0f4ff;
    --rrm-muted:  #8899bb;
    --rrm-border: rgba(255,255,255,0.08);
    --rrm-glass:  rgba(255,255,255,0.06);
    --rrm-glass2: rgba(255,255,255,0.12);
    --rrm-r:      16px;
    --rrm-r2:     24px;
    --rrm-sh:     0 24px 48px rgba(0,0,0,0.4);
    --rrm-trans:  0.35s cubic-bezier(.4,0,.2,1);
}

/* ── Reset helpers ───────────────────────────────────────────── */
.rrm-hero *, .rrm-stats *, .rrm-section *, .rrm-cta * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* ── Container ──────────────────────────────────────────────── */
.rrm-container {
    max-width: 1240px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ── Fonts ──────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500;1,600&family=DM+Sans:wght@300;400;500;600&display=swap');

.rrm-hero, .rrm-stats, .rrm-section, .rrm-cta {
    font-family: 'DM Sans', sans-serif;
    color: var(--rrm-text);
}

h1, h2, h3 { font-family: 'Cormorant Garamond', serif; font-weight: 600; line-height: 1.1; letter-spacing: -0.01em; }

/* ── Shared elements ────────────────────────────────────────── */
.rrm-gold         { color: var(--rrm-gold); }
.rrm-pill         {
    display: inline-block;
    padding: 5px 16px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    background: rgba(212,168,66,.15);
    color: var(--rrm-gold2);
    border: 1px solid rgba(212,168,66,.25);
    margin-bottom: 16px;
}
.rrm-pill--dark { background: rgba(255,255,255,.06); color: var(--rrm-muted); border-color: var(--rrm-border); }

.rrm-section__hd  { text-align: center; margin-bottom: 56px; }
.rrm-section__hd h2 { font-size: 48px; margin-bottom: 12px; }
.rrm-section__hd p  { color: var(--rrm-muted); font-size: 17px; max-width: 520px; margin: 0 auto; }

@media (max-width: 768px) {
    .rrm-section__hd h2 { font-size: 36px; }
}

/* ═══════════════════════════════════════════
   HERO
═══════════════════════════════════════════ */
.rrm-hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    background: var(--rrm-navy);
}

.rrm-hero__bg {
    position: absolute;
    inset: 0;
}

.rrm-hero__video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: .55;
}

.rrm-hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, rgba(11,17,32,.85) 0%, rgba(11,17,32,.4) 60%, rgba(11,17,32,.7) 100%);
}

.rrm-hero__grain {
    position: absolute;
    inset: 0;
    opacity: .035;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    background-size: 200px;
}

.rrm-hero__inner {
    position: relative;
    z-index: 2;
    padding: 120px 0 100px;
}

/* eyebrow */
.rrm-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--rrm-glass);
    backdrop-filter: blur(12px);
    border: 1px solid var(--rrm-border);
    border-radius: 100px;
    padding: 7px 18px;
    font-size: 13px;
    color: var(--rrm-gold2);
    font-weight: 500;
    margin-bottom: 28px;
}
.rrm-eyebrow__dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--rrm-gold);
    animation: rrm-blink 2s ease-in-out infinite;
}
@keyframes rrm-blink { 0%,100%{opacity:1;} 50%{opacity:.3;} }

/* title */
.rrm-hero__title {
    font-size: 72px;
    font-weight: 600;
    color: #fff;
    max-width: 760px;
    margin-bottom: 20px;
}
.rrm-hero__title em { color: var(--rrm-gold2); font-style: italic; }

.rrm-hero__sub {
    font-size: 18px;
    color: rgba(240,244,255,.65);
    max-width: 540px;
    margin-bottom: 44px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .rrm-hero__title { font-size: 48px; }
    .rrm-hero__sub { font-size: 16px; }
}

/* ── Glassmorphism search ───────────────────────────────────── */
.rrm-search {
    max-width: 820px;
    margin-bottom: 36px;
}

.rrm-search__form {
    display: flex;
    align-items: center;
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 100px;
    padding: 8px;
    gap: 0;
    flex-wrap: wrap;
}

.rrm-search__field {
    flex: 1;
    min-width: 160px;
    position: relative;
    display: flex;
    align-items: center;
}

.rrm-search__ico {
    position: absolute;
    left: 16px;
    width: 17px;
    height: 17px;
    stroke: rgba(255,255,255,.55);
    flex-shrink: 0;
}

.rrm-search__field input,
.rrm-search__field select {
    width: 100%;
    padding: 14px 14px 14px 44px;
    background: transparent;
    border: none;
    color: #fff;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    appearance: none;
    outline: none;
}
.rrm-search__field input::placeholder { color: rgba(255,255,255,.5); }
.rrm-search__field select option { color: #111; background: #fff; }

.rrm-search__divider {
    width: 1px;
    height: 28px;
    background: rgba(255,255,255,.2);
    flex-shrink: 0;
}

.rrm-search__btn {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 28px;
    background: linear-gradient(135deg, var(--rrm-gold) 0%, #b8882e 100%);
    color: var(--rrm-navy);
    font-size: 14px;
    font-weight: 700;
    border: none;
    border-radius: 100px;
    cursor: pointer;
    transition: var(--rrm-trans);
    letter-spacing: .03em;
    text-transform: uppercase;
}
.rrm-search__btn svg { width: 16px; height: 16px; stroke: var(--rrm-navy); }
.rrm-search__btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(212,168,66,.4); }

/* quick tags */
.rrm-search__tags {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
    padding-left: 8px;
}
.rrm-tag-label { font-size: 12px; color: rgba(255,255,255,.45); }
.rrm-tag {
    padding: 4px 14px;
    border-radius: 100px;
    border: 1px solid rgba(255,255,255,.2);
    font-size: 13px;
    color: rgba(255,255,255,.75);
    text-decoration: none;
    transition: var(--rrm-trans);
}
.rrm-tag:hover { background: rgba(255,255,255,.12); color: #fff; border-color: rgba(255,255,255,.4); }

/* trust row */
.rrm-trust {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px 20px;
}
.rrm-trust__item {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    color: rgba(240,244,255,.6);
    font-weight: 500;
}
.rrm-trust__item svg { width: 15px; height: 15px; stroke: var(--rrm-gold); }
.rrm-trust__sep { width: 1px; height: 16px; background: rgba(255,255,255,.15); }

/* scroll cue */
.rrm-scroll-cue {
    position: absolute;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: rgba(255,255,255,.4);
    font-size: 11px;
    letter-spacing: .06em;
    text-transform: uppercase;
    z-index: 3;
}
.rrm-scroll-cue__mouse {
    width: 24px; height: 38px;
    border: 1.5px solid rgba(255,255,255,.3);
    border-radius: 20px;
    position: relative;
}
.rrm-scroll-cue__wheel {
    width: 3px; height: 7px;
    background: rgba(255,255,255,.5);
    border-radius: 2px;
    position: absolute;
    top: 7px; left: 50%;
    transform: translateX(-50%);
    animation: rrm-scroll 1.6s ease-in-out infinite;
}
@keyframes rrm-scroll { 0%{transform:translateX(-50%) translateY(0);opacity:1} 100%{transform:translateX(-50%) translateY(14px);opacity:0} }

/* ═══════════════════════════════════════════
   STATS
═══════════════════════════════════════════ */
.rrm-stats {
    background: var(--rrm-navy2);
    padding: 0 0 64px;
    margin-top: -1px;
}

.rrm-stats__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    transform: translateY(-48px);
}

.rrm-stat {
    background: var(--rrm-navy3);
    border: 1px solid var(--rrm-border);
    border-radius: var(--rrm-r2);
    padding: 32px 24px;
    text-align: center;
    transition: var(--rrm-trans);
    position: relative;
    overflow: hidden;
}
.rrm-stat::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 50% 0%, rgba(212,168,66,.07), transparent 70%);
    pointer-events: none;
}
.rrm-stat:hover { transform: translateY(-6px); border-color: rgba(212,168,66,.3); box-shadow: 0 20px 40px rgba(0,0,0,.4); }

.rrm-stat__icon {
    width: 48px; height: 48px;
    border-radius: 14px;
    background: rgba(212,168,66,.1);
    border: 1px solid rgba(212,168,66,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}
.rrm-stat__icon svg { width: 22px; height: 22px; stroke: var(--rrm-gold); }

.rrm-stat__number {
    font-family: 'Cormorant Garamond', serif;
    font-size: 50px;
    font-weight: 600;
    color: var(--rrm-gold2);
    line-height: 1;
    margin-bottom: 6px;
}
.rrm-stat__label { font-weight: 600; font-size: 15px; color: var(--rrm-text); margin-bottom: 4px; }
.rrm-stat__trend { font-size: 12px; color: var(--rrm-muted); }

@media (max-width: 768px) {
    .rrm-stats__grid { grid-template-columns: repeat(2, 1fr); transform: translateY(-24px); }
    .rrm-stat { padding: 24px 16px; }
    .rrm-stat__number { font-size: 36px; }
}

/* ═══════════════════════════════════════════
   SECTIONS — shared bg
═══════════════════════════════════════════ */
.rrm-section {
    background: var(--rrm-navy2);
    padding: 80px 0;
}
.rrm-section:nth-child(odd) { background: var(--rrm-navy); }

@media (max-width: 768px) {
    .rrm-section { padding: 60px 0; }
}

/* ═══════════════════════════════════════════
   PROPERTY CARDS
═══════════════════════════════════════════ */
.rrm-props__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    margin-bottom: 48px;
}

.rrm-prop {
    background: var(--rrm-navy3);
    border: 1px solid var(--rrm-border);
    border-radius: var(--rrm-r2);
    overflow: hidden;
    transition: var(--rrm-trans);
}
.rrm-prop:hover { transform: translateY(-8px); box-shadow: var(--rrm-sh); border-color: rgba(212,168,66,.2); }

.rrm-prop__img {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
}
.rrm-prop__img img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform .6s ease;
    display: block;
}
.rrm-prop:hover .rrm-prop__img img { transform: scale(1.06); }

.rrm-prop__price {
    position: absolute;
    bottom: 14px; left: 14px;
    background: var(--rrm-gold);
    color: var(--rrm-navy);
    font-family: 'Cormorant Garamond', serif;
    font-size: 22px;
    font-weight: 600;
    padding: 4px 14px;
    border-radius: 10px;
}
.rrm-prop__price span { font-size: 13px; font-family: 'DM Sans', sans-serif; font-weight: 500; }

.rrm-prop__badge {
    position: absolute;
    top: 14px; left: 14px;
    background: rgba(11,17,32,.8);
    backdrop-filter: blur(8px);
    border: 1px solid var(--rrm-border);
    color: var(--rrm-gold2);
    font-size: 12px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 100px;
}

.rrm-prop__fav {
    position: absolute;
    top: 12px; right: 12px;
    width: 36px; height: 36px;
    background: rgba(11,17,32,.6);
    backdrop-filter: blur(8px);
    border: 1px solid var(--rrm-border);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: var(--rrm-trans);
}
.rrm-prop__fav svg { width: 16px; height: 16px; stroke: rgba(255,255,255,.7); transition: var(--rrm-trans); }
.rrm-prop__fav:hover svg { stroke: #f87171; fill: #f87171; }

.rrm-prop__body { padding: 22px; }

.rrm-prop__body h3 {
    font-size: 20px;
    font-weight: 600;
    color: var(--rrm-text);
    margin-bottom: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rrm-prop__loc {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--rrm-muted);
    margin-bottom: 16px;
}
.rrm-prop__loc svg { width: 14px; height: 14px; stroke: var(--rrm-gold); flex-shrink: 0; }

.rrm-prop__feats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--rrm-border);
    margin-bottom: 18px;
}
.rrm-prop__feats span {
    display: flex; align-items: center; gap: 5px;
    font-size: 13px; color: var(--rrm-muted);
}
.rrm-prop__feats svg { width: 14px; height: 14px; stroke: var(--rrm-muted); }

.rrm-prop__foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.rrm-prop__host { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--rrm-muted); }

.rrm-prop__avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--rrm-gold), #b8882e);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700;
    color: var(--rrm-navy);
    flex-shrink: 0;
}

.rrm-prop__cta {
    display: flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600;
    color: var(--rrm-gold2);
    text-decoration: none;
    transition: var(--rrm-trans);
}
.rrm-prop__cta svg { width: 15px; height: 15px; stroke: var(--rrm-gold2); transition: var(--rrm-trans); }
.rrm-prop__cta:hover { color: #fff; }
.rrm-prop__cta:hover svg { transform: translateX(4px); stroke: #fff; }

/* view all */
.rrm-view-all { text-align: center; }
.rrm-btn-outline {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 36px;
    border: 1.5px solid rgba(212,168,66,.5);
    border-radius: 100px;
    color: var(--rrm-gold2);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: .03em;
    text-transform: uppercase;
    transition: var(--rrm-trans);
}
.rrm-btn-outline svg { width: 16px; height: 16px; stroke: var(--rrm-gold2); transition: var(--rrm-trans); }
.rrm-btn-outline:hover {
    background: rgba(212,168,66,.1);
    border-color: var(--rrm-gold);
    box-shadow: 0 0 0 4px rgba(212,168,66,.08);
}
.rrm-btn-outline:hover svg { transform: translateX(4px); }

@media (max-width: 1024px) {
    .rrm-props__grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .rrm-props__grid { grid-template-columns: 1fr; }
}

/* ═══════════════════════════════════════════
   CITIES — Bento Grid
═══════════════════════════════════════════ */
.rrm-cities__bento {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-auto-rows: auto;
    gap: 16px;
}

.rrm-city {
    position: relative;
    border-radius: var(--rrm-r2);
    overflow: hidden;
    background-size: cover;
    background-position: center;
    aspect-ratio: 4/3;
    text-decoration: none;
    display: block;
    transition: var(--rrm-trans);
}
.rrm-city::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(0deg, rgba(0,0,0,.7) 0%, transparent 55%);
    transition: var(--rrm-trans);
}
.rrm-city:hover::after { background: linear-gradient(0deg, rgba(0,0,0,.75) 0%, rgba(0,0,0,.2) 100%); }
.rrm-city:hover { transform: scale(1.02); }

.rrm-city--lg {
    grid-column: 1;
    grid-row: 1 / 3;
    aspect-ratio: unset;
}

.rrm-city__glass {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    z-index: 2;
    padding: 20px 22px;
}
.rrm-city__glass h3 {
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 4px;
}
.rrm-city__glass p { font-size: 13px; color: rgba(255,255,255,.65); }

@media (max-width: 1024px) {
    .rrm-cities__bento {
        grid-template-columns: repeat(2, 1fr);
    }
    .rrm-city--lg { grid-column: auto; grid-row: auto; aspect-ratio: 4/3; }
}
@media (max-width: 768px) {
    .rrm-cities__bento { grid-template-columns: 1fr; }
}

/* ═══════════════════════════════════════════
   HOW IT WORKS
═══════════════════════════════════════════ */
.rrm-how__grid {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 0;
}

.rrm-step {
    flex: 1;
    max-width: 320px;
    text-align: center;
    padding: 40px 24px;
    border-radius: var(--rrm-r2);
    background: var(--rrm-glass);
    border: 1px solid var(--rrm-border);
    transition: var(--rrm-trans);
    position: relative;
}
.rrm-step:hover { background: var(--rrm-glass2); transform: translateY(-6px); }

.rrm-step__num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 72px;
    font-weight: 600;
    color: rgba(212,168,66,.12);
    line-height: 1;
    position: absolute;
    top: 16px; left: 50%;
    transform: translateX(-50%);
    pointer-events: none;
}

.rrm-step__ico {
    width: 64px; height: 64px;
    border-radius: 18px;
    background: rgba(212,168,66,.1);
    border: 1px solid rgba(212,168,66,.25);
    display: flex; align-items: center; justify-content: center;
    margin: 36px auto 20px;
}
.rrm-step__ico svg { width: 28px; height: 28px; stroke: var(--rrm-gold); }

.rrm-step h3 { font-size: 24px; margin-bottom: 12px; color: var(--rrm-text); }
.rrm-step p  { font-size: 14px; line-height: 1.65; color: var(--rrm-muted); }

.rrm-step__connector {
    width: 60px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    padding-top: 100px;
    opacity: .35;
}
.rrm-step__connector svg { width: 48px; height: 24px; stroke: var(--rrm-gold); }

@media (max-width: 1024px) {
    .rrm-how__grid { flex-direction: column; align-items: center; gap: 24px; }
    .rrm-step__connector { display: none; }
    .rrm-step { max-width: 100%; width: 100%; }
}

/* ═══════════════════════════════════════════
   TESTIMONIALS
═══════════════════════════════════════════ */
.rrm-testi { background: var(--rrm-navy); }

.rrm-testi__track-wrap {
    overflow: hidden;
    border-radius: var(--rrm-r2);
    margin-bottom: 28px;
}

.rrm-testi__track {
    display: flex;
    gap: 24px;
    transition: transform .5s cubic-bezier(.4,0,.2,1);
}

.rrm-testi__card {
    flex-shrink: 0;
    width: calc(33.333% - 16px);
    background: var(--rrm-navy3);
    border: 1px solid var(--rrm-border);
    border-radius: var(--rrm-r2);
    padding: 36px;
    transition: var(--rrm-trans);
    position: relative;
    overflow: hidden;
}
.rrm-testi__card::before {
    content: '\201C';
    position: absolute;
    top: -10px; right: 24px;
    font-family: 'Cormorant Garamond', serif;
    font-size: 120px;
    color: rgba(212,168,66,.08);
    line-height: 1;
    pointer-events: none;
}

.rrm-testi__stars {
    color: var(--rrm-gold);
    font-size: 14px;
    letter-spacing: 2px;
    margin-bottom: 16px;
}

.rrm-testi__card blockquote {
    font-size: 15px;
    line-height: 1.7;
    color: rgba(240,244,255,.75);
    margin-bottom: 28px;
}

.rrm-testi__author { display: flex; align-items: center; gap: 12px; }

.rrm-testi__avatar {
    width: 42px; height: 42px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--rrm-gold), #8a5a00);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700;
    color: var(--rrm-navy);
    flex-shrink: 0;
}

.rrm-testi__author strong { display: block; font-size: 14px; font-weight: 600; color: var(--rrm-text); }
.rrm-testi__author span  { font-size: 12px; color: var(--rrm-muted); }

/* dots */
.rrm-testi__dots {
    display: flex;
    justify-content: center;
    gap: 8px;
}
.rrm-testi__dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--rrm-border);
    cursor: pointer;
    transition: var(--rrm-trans);
}
.rrm-testi__dot.active {
    background: var(--rrm-gold);
    transform: scale(1.3);
}

@media (max-width: 1024px) {
    .rrm-testi__card { width: calc(50% - 12px); }
}
@media (max-width: 768px) {
    .rrm-testi__card { width: calc(100% - 0px); }
}

/* ═══════════════════════════════════════════
   CALCULATOR
═══════════════════════════════════════════ */
.rrm-calc__box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border-radius: 28px;
    overflow: hidden;
    border: 1px solid var(--rrm-border);
    background: var(--rrm-navy3);
}

.rrm-calc__copy {
    padding: 48px;
    background: radial-gradient(ellipse at 30% 60%, rgba(212,168,66,.07), transparent 70%), var(--rrm-navy3);
}
.rrm-calc__copy h2 { font-size: 40px; }
.rrm-calc__copy p  { font-size: 15px; color: var(--rrm-muted); line-height: 1.65; margin: 16px 0; }

.rrm-btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--rrm-gold2);
    text-decoration: none;
    transition: var(--rrm-trans);
}
.rrm-btn-ghost:hover { color: #fff; gap: 10px; }

.rrm-calc__form {
    padding: 48px;
    background: rgba(0,0,0,.25);
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.rrm-calc__field label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--rrm-muted);
    margin-bottom: 8px;
}
.rrm-calc__input-wrap {
    display: flex;
    align-items: center;
    background: var(--rrm-glass);
    border: 1px solid var(--rrm-border);
    border-radius: 12px;
    overflow: hidden;
}
.rrm-calc__input-wrap span {
    padding: 0 16px;
    font-size: 16px;
    color: var(--rrm-gold);
    font-weight: 600;
    border-right: 1px solid var(--rrm-border);
}
.rrm-calc__input-wrap input {
    flex: 1;
    padding: 14px 16px;
    background: transparent;
    border: none;
    outline: none;
    color: var(--rrm-text);
    font-size: 16px;
    font-family: 'DM Sans', sans-serif;
}

.rrm-calc__result {
    background: rgba(212,168,66,.07);
    border: 1px solid rgba(212,168,66,.2);
    border-radius: 16px;
    padding: 24px;
    text-align: center;
}
.rrm-calc__result-label { font-size: 12px; letter-spacing: .06em; text-transform: uppercase; color: var(--rrm-muted); margin-bottom: 8px; }
.rrm-calc__result-value { font-family: 'Cormorant Garamond', serif; font-size: 52px; font-weight: 600; color: var(--rrm-gold2); line-height: 1; margin-bottom: 4px; }
.rrm-calc__result-note  { font-size: 12px; color: var(--rrm-muted); margin-bottom: 16px; }
.rrm-calc__bar-wrap { height: 4px; background: rgba(255,255,255,.08); border-radius: 2px; overflow: hidden; }
.rrm-calc__bar { height: 100%; background: linear-gradient(90deg, var(--rrm-gold), var(--rrm-gold2)); border-radius: 2px; transition: width .6s cubic-bezier(.4,0,.2,1); }

@media (max-width: 1024px) {
    .rrm-calc__box { grid-template-columns: 1fr; }
    .rrm-calc__copy { padding: 40px; }
    .rrm-calc__form { padding: 40px; }
}
@media (max-width: 768px) {
    .rrm-calc__copy h2 { font-size: 32px; }
}

/* ═══════════════════════════════════════════
   CTA BANNER
═══════════════════════════════════════════ */
.rrm-cta {
    position: relative;
    overflow: hidden;
    padding: 80px 0;
    background: var(--rrm-navy);
}
.rrm-cta__bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 70% 50%, rgba(212,168,66,.12) 0%, transparent 60%),
                radial-gradient(ellipse at 20% 70%, rgba(11,17,32,.8) 0%, transparent 60%);
    pointer-events: none;
}

.rrm-cta__inner {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 48px;
    flex-wrap: wrap;
}

.rrm-cta__copy h2 { font-size: 48px; }
.rrm-cta__copy p  { font-size: 16px; color: var(--rrm-muted); margin-top: 14px; max-width: 400px; line-height: 1.65; }

.rrm-cta__actions { display: flex; flex-direction: column; gap: 14px; align-items: flex-start; flex-shrink: 0; }

.rrm-btn-solid {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 16px 40px;
    background: linear-gradient(135deg, var(--rrm-gold) 0%, #b8882e 100%);
    color: var(--rrm-navy);
    font-weight: 700;
    font-size: 14px;
    letter-spacing: .04em;
    text-transform: uppercase;
    text-decoration: none;
    border-radius: 100px;
    transition: var(--rrm-trans);
}
.rrm-btn-solid svg { width: 16px; height: 16px; stroke: var(--rrm-navy); transition: var(--rrm-trans); }
.rrm-btn-solid:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(212,168,66,.35); }
.rrm-btn-solid:hover svg { transform: translateX(4px); }

.rrm-btn-border {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 16px 40px;
    border: 1.5px solid rgba(212,168,66,.4);
    color: var(--rrm-gold2);
    font-weight: 600;
    font-size: 14px;
    letter-spacing: .04em;
    text-transform: uppercase;
    text-decoration: none;
    border-radius: 100px;
    transition: var(--rrm-trans);
}
.rrm-btn-border:hover {
    background: rgba(212,168,66,.08);
    border-color: var(--rrm-gold2);
    color: #fff;
}

@media (max-width: 768px) {
    .rrm-cta { padding: 60px 0; }
    .rrm-cta__inner { flex-direction: column; text-align: center; }
    .rrm-cta__copy h2 { font-size: 36px; }
    .rrm-cta__copy p { max-width: 100%; }
    .rrm-cta__actions { align-items: center; width: 100%; }
    .rrm-btn-solid, .rrm-btn-border { width: 100%; justify-content: center; }
    .rrm-trust { justify-content: center; }
    .rrm-trust__sep { display: none; }
}
</style>

<!-- ════════════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════════ -->
<script>
/* ── Counter animation ──────────────────────────────────────── */
const countObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (!e.isIntersecting) return;
        const stat    = e.target;
        const counter = stat.querySelector('.js-counter');
        const target  = +stat.dataset.target;
        const suffix  = stat.dataset.suffix || '';
        const dur     = 1800;
        const step    = target / (dur / 16);
        let cur = 0;

        const tick = () => {
            cur = Math.min(cur + step, target);
            counter.textContent = Math.floor(cur).toLocaleString() + (cur >= target ? suffix : '');
            if (cur < target) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
        countObs.unobserve(stat);
    });
}, { threshold: 0.5 });

document.querySelectorAll('.js-stat').forEach(el => countObs.observe(el));

/* ── Testimonial carousel ───────────────────────────────────── */
(function () {
    const track     = document.getElementById('testiTrack');
    const dotsWrap  = document.getElementById('testiDots');
    if (!track || !dotsWrap) return;

    const cards     = Array.from(track.querySelectorAll('.rrm-testi__card'));
    const perPage   = () => window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1;
    let current     = 0;
    let timer;

    function pageCount() { return Math.ceil(cards.length / perPage()); }

    function buildDots() {
        dotsWrap.innerHTML = '';
        for (let i = 0; i < pageCount(); i++) {
            const d = document.createElement('div');
            d.className = 'rrm-testi__dot' + (i === current ? ' active' : '');
            d.addEventListener('click', () => goTo(i));
            dotsWrap.appendChild(d);
        }
    }

    function updateDots() {
        dotsWrap.querySelectorAll('.rrm-testi__dot').forEach((d, i) =>
            d.classList.toggle('active', i === current));
    }

    function getOffset() {
        const pp    = perPage();
        const gap   = 24;
        const w     = track.parentElement.clientWidth;
        const cardW = (w - gap * (pp - 1)) / pp;
        return current * (cardW + gap) * pp;
    }

    function goTo(idx) {
        current = Math.min(Math.max(0, idx), pageCount() - 1);
        track.style.transform = `translateX(-${getOffset()}px)`;
        updateDots();
        restart();
    }

    function next() { goTo((current + 1) % pageCount()); }
    function restart() { clearInterval(timer); timer = setInterval(next, 5000); }

    buildDots();
    restart();
    window.addEventListener('resize', () => { buildDots(); goTo(0); });

    /* swipe support */
    let touchX = 0;
    track.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 50) goTo(dx < 0 ? (current + 1) % pageCount() : (current - 1 + pageCount()) % pageCount());
    });
})();

/* ── Rent calculator ────────────────────────────────────────── */
(function () {
    const income   = document.getElementById('calcIncome');
    const expenses = document.getElementById('calcExpenses');
    const result   = document.getElementById('calcResult');
    const bar      = document.getElementById('calcBar');
    if (!income || !expenses) return;

    function calc() {
        const inc  = Math.max(0, parseFloat(income.value)   || 0);
        const exp  = Math.max(0, parseFloat(expenses.value) || 0);
        const max  = Math.max(0, (inc - exp) * 0.30);
        const pct  = inc > 0 ? Math.min(100, (max / inc) * 100) : 0;

        if (result) result.textContent = '$' + Math.round(max).toLocaleString();
        if (bar)    bar.style.width    = pct + '%';
    }

    income.addEventListener('input',   calc);
    expenses.addEventListener('input', calc);
    calc();
})();

/* ── Favourite heart toggle ─────────────────────────────────── */
document.querySelectorAll('.rrm-prop__fav').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const svg = this.querySelector('svg');
        const on  = svg.style.fill === 'rgb(248, 113, 113)';
        svg.style.fill   = on ? 'none'        : '#f87171';
        svg.style.stroke = on ? 'rgba(255,255,255,.7)' : '#f87171';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>