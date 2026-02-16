</div>
        <!-- This container is closed by footer.php -->
    </main>
    <!-- Main Content End -->

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav shadow-4">
        <a href="<?php echo APP_URL; ?>/public/index.php" class="bottom-nav-item active" id="nav-home">
            <span class="bottom-nav-icon"><i class="fas fa-home"></i></span>
            <span>Home</span>
        </a>
        <a href="<?php echo APP_URL; ?>/public/complaints.php" class="bottom-nav-item" id="nav-complaints">
            <span class="bottom-nav-icon"><i class="fas fa-list-ul"></i></span>
            <span>Complaints</span>
        </a>
        <?php if ($isLoggedIn): ?>
            <a href="<?php echo APP_URL; ?>/public/submit-complaint.php" class="bottom-nav-item" id="nav-submit">
                <span class="bottom-nav-icon"><i class="fas fa-plus-circle"></i></span>
                <span>Report</span>
            </a>
            <a href="<?php echo APP_URL; ?>/public/dashboard.php" class="bottom-nav-item" id="nav-profile">
                <span class="bottom-nav-icon"><i class="fas fa-user-circle"></i></span>
                <span>Profile</span>
            </a>
        <?php else: ?>
            <a href="<?php echo APP_URL; ?>/public/login.php" class="bottom-nav-item" id="nav-login">
                <span class="bottom-nav-icon"><i class="fas fa-sign-in-alt"></i></span>
                <span>Login</span>
            </a>
        <?php endif; ?>
    </nav>
    
    <!-- Desktop Footer (Simplified) -->
    <footer class="bg-body-tertiary text-center text-lg-start d-none d-lg-block mt-5">
      <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
        © 2025 CivicVoice
      </div>
    </footer>

    <!-- MDBOOTSTRAP "VIEW LIBRARY" (JAVASCRIPT) -->
    <script
      type="text/javascript"
      src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.3.2/mdb.umd.min.js"
    ></script>
    
    <!-- Your Custom Scripts -->
    <script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
</body>
</html>