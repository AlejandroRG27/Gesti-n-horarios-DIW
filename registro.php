<?php
include 'conexion.php'; // Archivo para la conexión a la base de datos.

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password2 = trim($_POST['password2']);
    $dni = trim($_POST['dni']);
    $is_admin = intval($_POST['rol']);

    // Validación de campos vacíos
    if (empty($name) || empty($email) || empty($password) || empty($password2) || empty($dni)) {
        echo "Por favor, rellena todos los campos.";
        exit;
    }

    // Validación de contraseñas
    if ($password !== $password2) {
        echo "Las contraseñas no coinciden.";
        exit;
    }

    // Validación de formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Correo electrónico no válido.";
        exit;
    }

    // Validación de DNI (puedes añadir una expresión regular para verificar el formato si es necesario)
    if (strlen($dni) < 8) { // Ajusta según las reglas de tu DNI
        echo "DNI inválido.";
        exit;
    }

    try {
        // Verifica si el correo ya está registrado.
        $query = "SELECT * FROM Usuarios WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            echo "El correo ya está registrado.";
            exit;
        }

        // Hashea la contraseña.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Inserta el nuevo usuario en la base de datos.
        $query = "INSERT INTO Usuarios (nombre, email, passworduser, dni, is_admin) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$name, $email, $hashed_password, $dni, $is_admin]);

        echo "Registro exitoso. Ahora puedes iniciar sesión.";
        
        // Redirige al login con un "exit" después de la redirección para evitar ejecución adicional.
        header("Location: index.html");
        exit;
    } catch (PDOException $e) {
        echo "Error al registrar el usuario: " . $e->getMessage();
    }
}
?>
