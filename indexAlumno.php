<?php
session_start();
include 'conexion.php';

// Verifica si el usuario es un administrador
if ($_SESSION['user_role'] != 0) {
    header("Location: index.html");
    exit;
  }

  // Verificar si existe un mensaje de alerta
if (isset($_SESSION['alert_message'])) {
    echo "<script>alert('" . $_SESSION['alert_message'] . "');</script>";
    // Eliminar el mensaje de alerta de la sesión después de mostrarlo
    unset($_SESSION['alert_message']);
  }


// Obtener módulos disponibles
$query = "SELECT * FROM modulos";
$stmt = $pdo->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener módulos del alumno si ya ha seleccionado
$alumno_id = $_SESSION['user_id'];
$querySeleccionados = "SELECT modulo_id FROM alumnosmodulos WHERE usuario_id = :usuario_id";
$stmtSeleccionados = $pdo->prepare($querySeleccionados);
$stmtSeleccionados->execute(['usuario_id' => $alumno_id]);
$modulosSeleccionados = [];
while ($row = $stmtSeleccionados->fetch(PDO::FETCH_ASSOC)) {
    $modulosSeleccionados[] = $row['modulo_id'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Alumno</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css-index/index-alumno.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Seleccionar Módulos</h2>
        <form action="guardar_modulos.php" method="POST">
        <?php foreach ($result as $row) { ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="modulos[]" value="<?php echo $row['id']; ?>" 
                        <?php echo in_array($row['id'], $modulosSeleccionados) ? 'checked' : ''; ?> >
                    <label class="form-check-label"> <?php echo $row['nombre']; ?> </label>
                </div>
            <?php } ?>
            <button type="submit" class="btn btn-primary mt-2">Guardar Selección</button>
        </form>

        <h2 class="mt-4">Horario Personalizado</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Módulo</th>
                </tr>
            </thead>
            <tbody>
            <?php
                // Obtener horario personalizado con PDO
                $queryHorario = "SELECT h.dia_semana, h.hora_inicio, h.hora_fin, m.nombre 
                    FROM horarios h
                    INNER JOIN modulos m ON h.modulo_id = m.id 
                    INNER JOIN alumnosmodulos am ON am.modulo_id = h.modulo_id
                     WHERE am.usuario_id = :usuario_id";
                $stmtHorario = $pdo->prepare($queryHorario);
                $stmtHorario->execute(['usuario_id' => $alumno_id]);

                while ($row = $stmtHorario->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr><td>{$row['dia_semana']}</td><td>{$row['hora_inicio']}</td><td>{$row['hora_fin']}</td><td>{$row['nombre']}</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="exportar_pdf.php" class="btn btn-success">Exportar a PDF</a>
    </div>
    <!-- Validación del formulario -->
    <script>
document.querySelector("form").onsubmit = function() {
    const selectedModules = document.querySelectorAll('input[name="modulos[]"]:checked');
    if (selectedModules.length === 0) {
        alert("Por favor, selecciona al menos un módulo.");
        return false;
    }
};
</script>
</body>
</html>
