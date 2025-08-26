<?php
session_start();
include 'conexion.php'; // Archivo para la conexión a la base de datos.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Verifica si los campos no están vacíos.
    if (empty($email) || empty($password)) {
        echo "Por favor, rellena todos los campos.";
        exit;
    }

    // Valida el correo electrónico.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Correo electrónico no válido.";
        exit;
    }

    // Consulta para verificar el usuario.
    $query = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verifica la contraseña.
        if (password_verify($password, $user['passworduser'])) {
            // Establece las variables de sesión.
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['is_admin'];


            // Redirige al usuario según su rol.
            if ($user['is_admin'] == 1) {
                header("Location: indexAdmin.php"); // Redirige a la página del administrador.
            } else {
                header("Location: indexAlumno.php"); // Redirige a la página del alumno.
            }
            exit;
        } else {
            // Si la contraseña no es correcta.
            echo "Contraseña incorrecta.";
        }
    } else {
        // Si el usuario no existe.
        echo "No existe un usuario registrado con este correo.";
    }
}
?>
