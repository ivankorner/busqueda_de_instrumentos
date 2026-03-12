<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica que el usuario esté logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verifica que se haya enviado el id por POST y que sea numérico
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error'] = "ID de registro no válido.";
    header('Location: resultados.php');
    exit;
}

// Datos de conexión
require_once 'config/database.php';

try {
    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara y ejecuta el borrado
    $stmt = $pdo->prepare("DELETE FROM datos WHERE id = :id");
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->execute();

    // Verifica si realmente se borró algún registro
    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Registro eliminado correctamente.";
    } else {
        $_SESSION['error'] = "No se encontró el registro para eliminar.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al eliminar el registro: " . $e->getMessage();
}

// Redirige a resultados.php
header('Location: resultados.php');
exit;
?>