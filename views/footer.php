<?php
// views/footer.php
?>
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> ALFARUQ TEAM. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle + Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Tambahkan di bagian bawah index.php atau footer.php -->
<script>
    // Event listener untuk scroll window
    window.addEventListener('scroll', function() {
        // Ambil elemen navbar berdasarkan ID
        const navbar = document.getElementById('mainNavbar');
        // Ambil tinggi hero/slider untuk titik trigger
        const heroHeight = document.querySelector('header').offsetHeight;

        // Jika scroll melewati hero (dengan buffer 50px), ubah ke hijau solid
        if (window.scrollY > heroHeight - 50) {
            navbar.classList.add('navbar-transparent'); // Tambah class hijau solid
        } else {
            // Jika kembali ke atas, hapus class hijau (kembali ke putih)
            navbar.classList.remove('navbar-transparent');
        }
    });
</script>