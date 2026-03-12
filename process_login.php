<?php
session_start();

require_once 'config/database.php';

// Verifica si se enviaron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_username = $_POST['username'] ?? '';
    $input_password = $_POST['password'] ?? '';

    // Conexión a la base de datos usando las credenciales correctas
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        $_SESSION['error'] = 'Error de conexión con la base de datos.';
        header('Location: login.php');
        exit;
    }

    // Busca el usuario en la tabla usuarios
    $stmt = $conn->prepare("SELECT password_hash FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($hash);
        $stmt->fetch();

        // Verifica la contraseña usando password_verify
        if (password_verify($input_password, $hash)) {
            // Credenciales válidas, inicia sesión
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $input_username;

            header('Location: index.php');
            exit;
        }
    }

    // Credenciales inválidas
    $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
    header('Location: login.php');
    exit;
} else {
    // Si se accede directamente al archivo, redirige al login
    header('Location: login.php');
    exit;
}
?>