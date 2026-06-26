<?php
// footer.php - Rockland Rent Masters Footer
// Dark-luxury theme matching the rest of the site
?>
    </div>
</main>

<footer class="rrm-footer">
    <div class="rrm-container">
        <div class="rrm-footer__grid">
            <div class="rrm-footer__brand">
                <div class="rrm-footer__logo">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="10" fill="#d4a842"/>
                        <path d="M20 10L10 18V30H30V18L20 10Z" stroke="#0b1120" stroke-width="1.5" fill="none"/>
                        <path d="M17 22H23V28H17V22Z" fill="#0b1120" opacity="0.8"/>
                        <circle cx="20" cy="16" r="2" fill="#0b1120"/>
                    </svg>
                    <div>
                        <h3>Rockland Rent Masters</h3>
                        <p>Find your perfect home with ease.</p>
                    </div>
                </div>
                <div class="rrm-footer__social">
                    <a href="#" aria-label="Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <circle cx="12" cy="12" r="4.5"/>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                            <rect x="2" y="9" width="4" height="12"/>
                            <circle cx="4" cy="4" r="2"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="rrm-footer__links">
                <h4>For Renters</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/pages/renter/search.php">Browse Properties</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/renter/saved.php">Saved Properties</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/renter/applications.php">My Applications</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/tools/calculator.php">Rent Calculator</a></li>
                </ul>
            </div>

            <div class="rrm-footer__links">
                <h4>For Landlords</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/pages/landlord/add-property.php">List Your Property</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/landlord/applications.php">Manage Applications</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/landlord/analytics.php">Property Analytics</a></li>
                    <li><a href="<?= BASE_URL ?>/pages/landlord/tenants.php">Tenant Management</a></li>
                </ul>
            </div>

            <div class="rrm-footer__links">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Support</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                </ul>
            </div>

            <div class="rrm-footer__newsletter">
                <h4>Stay Updated</h4>
                <p>Get the latest property listings and market insights.</p>
                <form class="rrm-newsletter-form" action="#" method="POST">
                    <input type="email" placeholder="Your email address" required autocomplete="email">
                    <button type="submit">Subscribe →</button>
                </form>
            </div>
        </div>

        <div class="rrm-footer__bottom">
            <p>&copy; <?= date('Y') ?> Rockland Rent Masters. All rights reserved.</p>
            <div class="rrm-footer__bottom-links">
                <a href="#">Terms</a>
                <a href="#">Privacy</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>

<style>
/* ============================================
   RRM Footer Styles - Dark Luxury Theme
   ============================================ */

.rrm-footer {
    background: #0b1120;
    border-top: 1px solid rgba(212, 168, 66, 0.15);
    padding: 60px 0 30px;
    margin-top: auto;
    position: relative;
    overflow: hidden;
}

.rrm-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a842, transparent);
}

.rrm-footer__grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr 1.5fr;
    gap: 40px;
    margin-bottom: 50px;
}

/* Brand Section */
.rrm-footer__brand {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.rrm-footer__logo {
    display: flex;
    align-items: center;
    gap: 14px;
}

.rrm-footer__logo svg {
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.rrm-footer__logo:hover svg {
    transform: scale(1.05);
}

.rrm-footer__logo h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 4px;
    letter-spacing: -0.3px;
}

.rrm-footer__logo p {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

/* Social Icons */
.rrm-footer__social {
    display: flex;
    gap: 12px;
}

.rrm-footer__social a {
    width: 38px;
    height: 38px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid rgba(212, 168, 66, 0.2);
}

.rrm-footer__social a svg {
    width: 16px;
    height: 16px;
    stroke: rgba(255, 255, 255, 0.7);
    transition: all 0.3s ease;
}

.rrm-footer__social a:hover {
    background: #d4a842;
    border-color: #d4a842;
    transform: translateY(-3px);
}

.rrm-footer__social a:hover svg {
    stroke: #0b1120;
}

/* Links Sections */
.rrm-footer__links h4 {
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #d4a842;
    margin-bottom: 20px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.rrm-footer__links ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.rrm-footer__links li {
    margin-bottom: 12px;
}

.rrm-footer__links a {
    color: rgba(255, 255, 255, 0.55);
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s ease;
    position: relative;
    display: inline-block;
}

.rrm-footer__links a::before {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 1px;
    background: #d4a842;
    transition: width 0.3s ease;
}

.rrm-footer__links a:hover {
    color: #d4a842;
}

.rrm-footer__links a:hover::before {
    width: 100%;
}

/* Newsletter Section */
.rrm-footer__newsletter h4 {
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #d4a842;
    margin-bottom: 12px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.rrm-footer__newsletter p {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 20px;
    line-height: 1.5;
}

.rrm-newsletter-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.rrm-newsletter-form input {
    flex: 1;
    padding: 12px 18px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 40px;
    color: #fff;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.3s ease;
}

.rrm-newsletter-form input:focus {
    outline: none;
    border-color: #d4a842;
    background: rgba(255, 255, 255, 0.1);
}

.rrm-newsletter-form input::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.rrm-newsletter-form button {
    padding: 12px 28px;
    background: linear-gradient(135deg, #d4a842 0%, #b8882e 100%);
    border: none;
    border-radius: 40px;
    color: #0b1120;
    font-weight: 700;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.rrm-newsletter-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(212, 168, 66, 0.3);
}

/* Footer Bottom */
.rrm-footer__bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 30px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    font-size: 12px;
    color: rgba(255, 255, 255, 0.35);
}

.rrm-footer__bottom-links {
    display: flex;
    gap: 24px;
}

.rrm-footer__bottom-links a {
    color: rgba(255, 255, 255, 0.35);
    text-decoration: none;
    transition: color 0.2s ease;
    font-size: 12px;
}

.rrm-footer__bottom-links a:hover {
    color: #d4a842;
}

/* Responsive Design */
@media (max-width: 1100px) {
    .rrm-footer__grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
    }
    
    .rrm-footer__brand {
        grid-column: span 3;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

@media (max-width: 768px) {
    .rrm-footer {
        padding: 50px 0 25px;
    }
    
    .rrm-footer__grid {
        grid-template-columns: 1fr;
        gap: 35px;
    }
    
    .rrm-footer__brand {
        grid-column: span 1;
        flex-direction: column;
        text-align: center;
    }
    
    .rrm-footer__logo {
        justify-content: center;
    }
    
    .rrm-footer__social {
        justify-content: center;
    }
    
    .rrm-footer__links {
        text-align: center;
    }
    
    .rrm-footer__newsletter {
        text-align: center;
    }
    
    .rrm-newsletter-form {
        justify-content: center;
    }
    
    .rrm-footer__bottom {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .rrm-footer__bottom-links {
        justify-content: center;
        gap: 20px;
    }
    
    .rrm-newsletter-form {
        flex-direction: column;
    }
    
    .rrm-newsletter-form button {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .rrm-footer__grid {
        gap: 30px;
    }
    
    .rrm-footer__logo {
        flex-direction: column;
        text-align: center;
    }
    
    .rrm-footer__logo h3 {
        font-size: 18px;
    }
}
</style>