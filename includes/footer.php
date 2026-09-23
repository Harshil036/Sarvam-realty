<!-- Footer -->
<footer class="sarvam-footer mt-5">
    <div class="container">
        <div class="row g-4 pt-5 pb-3">
            <!-- Col 1: Brand & Social -->
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="<?= SITE_URL ?>" class="d-flex align-items-center gap-2 text-decoration-none mb-3">
                    <div style="width:36px;height:36px;background:#FF893B;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-house-door-fill text-white" style="font-size:1rem;"></i>
                    </div>
                    <span class="footer-brand">Sarvam Real Estate</span>
                </a>
                <p style="color:rgba(255,255,255,0.5);font-size:0.875rem;line-height:1.7;margin-bottom:1.5rem;">
                    Your trusted partner in finding the perfect home. Premium real estate services across India with a commitment to excellence.
                </p>
                <div class="footer-social">
                    <a href="#" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="#" title="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <!-- Col 2: Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="footer-heading">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?= SITE_URL ?>" class="footer-link"><i class="bi bi-chevron-right small me-1"></i>Home</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/about.php" class="footer-link"><i class="bi bi-chevron-right small me-1"></i>About Us</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/properties.php" class="footer-link"><i class="bi bi-chevron-right small me-1"></i>Properties</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/contact.php" class="footer-link"><i class="bi bi-chevron-right small me-1"></i>Contact</a></li>
                    <?php if (!isLoggedIn()): ?>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/login.php" class="footer-link"><i class="bi bi-chevron-right small me-1"></i>Login</a></li>
                    <li class="mb-2"><a href="<?= SITE_URL ?>/register.php" class="footer-link"><i class="bi bi-chevron-right small me-1"></i>Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Col 3: Property Types -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="footer-heading">Property Types</h6>
                <ul class="list-unstyled">
                    <?php
                    if (isset($conn)) {
                        $types = getPropertyTypes($conn);
                        $count = 0;
                        foreach($types as $type) {
                            if($count >= 6) break;
                            echo '<li class="mb-2"><a href="'.SITE_URL.'/properties.php?type_id='.$type['id'].'" class="footer-link"><i class="bi '.$type['icon'].' me-2"></i>'.$type['type_name'].'</a></li>';
                            $count++;
                        }
                    } else {
                        echo '<li class="mb-2"><a href="#" class="footer-link"><i class="bi bi-building me-2"></i>Apartments</a></li>';
                        echo '<li class="mb-2"><a href="#" class="footer-link"><i class="bi bi-house-door me-2"></i>Villas</a></li>';
                        echo '<li class="mb-2"><a href="#" class="footer-link"><i class="bi bi-shop me-2"></i>Commercial</a></li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Col 4: Contact & Newsletter -->
            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="footer-heading">Contact Us</h6>
                <ul class="list-unstyled mb-4" style="color:rgba(255,255,255,0.5);font-size:0.875rem;">
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-geo-alt-fill mt-1" style="color:#729CA2;flex-shrink:0;"></i>
                        <span>123 Business Avenue, Nariman Point, Mumbai 400021</span>
                    </li>
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-telephone-fill mt-1" style="color:#729CA2;flex-shrink:0;"></i>
                        <span>+91 98765 43210</span>
                    </li>
                    <li class="mb-3 d-flex gap-2">
                        <i class="bi bi-envelope-fill mt-1" style="color:#729CA2;flex-shrink:0;"></i>
                        <span>info@sarvamrealestate.com</span>
                    </li>
                </ul>
                <h6 class="footer-heading" style="font-size:0.85rem;">Newsletter</h6>
                <form action="#" method="POST" class="d-flex">
                    <input type="email" class="form-control" placeholder="Your email address" required
                           style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);color:rgba(255,255,255,0.8);border-radius:8px 0 0 8px;font-size:0.85rem;">
                    <button class="btn btn-sarvam" type="submit" style="border-radius:0 8px 8px 0;padding:0.5rem 0.9rem;">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="footer-divider"></div>

        <!-- Copyright Bar -->
        <div class="footer-bottom row align-items-center pb-3">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="footer-link me-3" style="font-size:0.8rem;">Privacy Policy</a>
                <a href="#" class="footer-link me-3" style="font-size:0.8rem;">Terms of Service</a>
                <a href="#" class="footer-link" style="font-size:0.8rem;">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<button type="button" id="btn-back-to-top" title="Back to top" aria-label="Back to top">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>

<?php if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false): ?>
<!-- Admin JS -->
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
<?php endif; ?>

</body>
</html>
