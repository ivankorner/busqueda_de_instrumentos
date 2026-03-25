     <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <span class="navbar-brand d-flex align-items-center">
                <img src="./inclusiones/logocde-b.png" alt="Logo CDE" width="120" height="80" class="me-2">    
            </span>
            <?php
            //session_start();
            $isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
            ?>
            <?php if ($isLoggedIn): ?>
                <span class="text-dark ms-auto">Bienvenido, <?php echo htmlspecialchars(strtoupper($_SESSION['username'])); ?>!</span>
            <?php endif; ?>
        </div>
    </nav>