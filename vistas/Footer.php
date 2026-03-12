
<?php
// footer assumes session already started by including page
$isLoggedIn = isset($_SESSION) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>
<style>
    /* ensure footer stays at bottom of viewport when content is short */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    footer {
        margin-top: auto;
    }
</style>
<footer style="background-color: #343a40; color: #ffffff; padding: 1rem 0; text-align: center;">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
        <p class="mb-2 mb-md-0">Concejo Deliberante Eldorado &copy; <?php echo date('Y'); ?>. Todos los derechos reservados.</p>
        <?php if ($isLoggedIn): ?>
            <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
        <?php endif; ?>
    </div>
</footer>



