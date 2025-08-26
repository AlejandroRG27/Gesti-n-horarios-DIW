<?php
session_start(); // Inicia la sesión si aún no está iniciada

// Destruir todas las variables de la sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al usuario a la página de inicio (index.html)
header("Location: index.html");
exit;
?>
