<!-- ============================================
    MODERN FOOTER
============================================ -->
<footer class="footer-modern-green">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
            <div class="col-lg-4 mb-4">
                <div class="d-flex align-items-center mb-4">
                    <img src="assets/img/logo.svg" alt="Logo" width="60" class="me-3">
                    <div>
                        <h5 class="text-white mb-0">ALFARUQ TEAM</h5>
                        <small class="text-white opacity-75">Travel Umroh Terpercaya</small>
                    </div>
                </div>
                
                <p class="text-white opacity-75 mb-4">
                    <?php echo htmlspecialchars($tagline1); ?> - 
                    <?php echo htmlspecialchars($tagline2); ?>
                </p>
                
                <div class="social-links-modern">
                    <a href="#" class="social-link-modern" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link-modern" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link-modern" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="https://wa.me/<?php echo $waNumber; ?>" class="social-link-modern" title="WhatsApp" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-4 mb-4">
                <h6 class="text-white mb-3">Navigasi</h6>
                <ul class="footer-links-modern">
                    <li><a href="index.php"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                    <li><a href="about.php"><i class="fas fa-chevron-right me-2"></i>Tentang Kami</a></li>
                    <li><a href="packages.php"><i class="fas fa-chevron-right me-2"></i>Paket Umroh</a></li>
                    <li><a href="gallery.php"><i class="fas fa-chevron-right me-2"></i>Galeri</a></li>
                    <li><a href="contact.php"><i class="fas fa-chevron-right me-2"></i>Kontak</a></li>
                </ul>
            </div>
            
            <!-- Legal -->
            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="text-white mb-3">Legalitas</h6>
                <div class="legal-info-modern">
                    <div class="d-flex align-items-start mb-3">
                        <i class="fas fa-file-certificate text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">PPIU No: SK PPIU NO.24022300153650007</p>
                            <small class="text-white opacity-75">Izin resmi Kementerian Agama</small>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <i class="fas fa-shield-alt text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">Terdaftar & Diawasi</p>
                            <small class="text-white opacity-75">SISKOPATUH & ASPHIRASI</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="text-white mb-3">Kontak Kami</h6>
                <ul class="contact-info-modern">
                    <li class="d-flex align-items-start mb-3">
                        <i class="fas fa-map-marker-alt text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">Ruko Bintan Center No. 56</p>
                            <small class="text-white opacity-75">Tanjungpinang, Kepulauan Riau</small>
                        </div>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="fas fa-phone text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0"><?php echo htmlspecialchars($whatsapp); ?></p>
                            <small class="text-white opacity-75">Admin 1: +62 812-6630-3236</small>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fas fa-envelope text-accent-500 mt-1 me-3"></i>
                        <div>
                            <p class="text-white opacity-90 mb-0">alfaruq5619@gmail.com</p>
                            <small class="text-white opacity-75">Email resmi perusahaan</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="border-white opacity-25 my-4">
        
        <!-- Copyright -->
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <p class="text-white opacity-75 mb-0">
                    &copy; <?php echo date('Y'); ?> PT. ALFARUQ ANUGERAH UTAMA. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-white opacity-50">
                    Made with <i class="fas fa-heart text-red-400"></i> for the ummah
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- ============================================
    FLOATING WHATSAPP BUTTON
============================================ -->
<a href="https://wa.me/<?php echo $waNumber; ?>?text=Halo%20ALFARUQ%20TEAM,%20saya%20ingin%20konsultasi%20paket%20umroh" 
   class="whatsapp-float-modern" target="_blank" title="Chat WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span class="whatsapp-pulse"></span>
</a>

<!-- ============================================
    BACK TO TOP BUTTON
============================================ -->
<button class="back-to-top-modern" id="backToTop" title="Kembali ke atas">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- ============================================
    SCRIPTS
============================================ -->
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (optional for animations) -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script src="js/modern-green.js"></script>
<script src="js/responsive.js"></script>
<script src="js/form-validation.js"></script>
<script src="js/carousel.js"></script>

<script>
// DOM Ready Function
$(document).ready(function() {
    // Back to Top Button
    $('#backToTop').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 500);
    });
    
    // Show/Hide Back to Top
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            $('#backToTop').fadeIn();
        } else {
            $('#backToTop').fadeOut();
        }
    });
    
    // Navbar scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar-modern-green').addClass('scrolled');
        } else {
            $('.navbar-modern-green').removeClass('scrolled');
        }
    });
});

// WhatsApp Tracking
document.querySelectorAll('a[href*="whatsapp"]').forEach(function(link) {
    link.addEventListener('click', function() {
        console.log('WhatsApp clicked:', this.href);
    });
});
</script>
</body>
</html>