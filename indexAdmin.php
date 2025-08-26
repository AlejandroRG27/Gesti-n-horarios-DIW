<?php
session_start();
include 'conexion.php';


// Verifica si el usuario es un administrador
if ($_SESSION['user_role'] != 1) {
  header("Location: index.html");
  exit;
}

// Verificar si existe un mensaje de alerta
if (isset($_SESSION['alert_message'])) {
  echo "<script>alert('" . $_SESSION['alert_message'] . "');</script>";
  // Eliminar el mensaje de alerta de la sesión después de mostrarlo
  unset($_SESSION['alert_message']);
}


// Funciones de gestión de datos (crear, editar, eliminar) se agregan aquí

// =========================================================================================
// ===================== FUNCIONES PARA GESTIONAR ALUMNOS ==================================
// =========================================================================================

// Crear alumno
if (isset($_POST['crearAlumno'])) {
  $nombre = trim($_POST['alumnoNombre']);
  $email = trim($_POST['alumnoEmail']);
  $dni = trim($_POST['alumnoDNI']);
  $contraseña = trim($_POST['alumnoContraseña']);
  $is_admin = ($_POST['alumnoAdmin'] == "admin") ? 1 : 0; // Convertir a 1 (admin) o 0 (alumno)

  if (empty($nombre) || empty($email) || empty($dni) || empty($contraseña)) {
    echo "Por favor, rellena todos los campos.";
  } else {
    try {
      // Hashear la contraseña antes de guardarla
      $hashed_password = password_hash($contraseña, PASSWORD_BCRYPT);

      // Consulta SQL asegurando especificar las columnas correctas
      $query = "INSERT INTO usuarios (nombre, email, dni, passworduser, is_admin) VALUES (?, ?, ?, ?, ?)";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$nombre, $email, $dni, $hashed_password, $is_admin]);


      $_SESSION['alert_message'] = 'Alumno creado exitosamente.';
      header("Location: indexAdmin.php");
      exit;
    } catch (PDOException $e) {
      echo "Error al crear alumno: " . $e->getMessage();
    }
  }
}

// Listar todos los alumnos
$query = "SELECT * FROM usuarios where is_admin=0";
$stmt = $pdo->prepare($query);
$stmt->execute();
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Editar alumno
if (isset($_POST['editarAlumno'])) {
  $dni_actual = trim($_POST['alumnoDNIEditar']); // Este es el DNI original que no debe cambiar antes de la consulta
  $nombre = trim($_POST['nuevoAlumnoNombre']);
  $email = trim($_POST['nuevoAlumnoEmail']);
  $dni_nuevo = trim($_POST['nuevoAlumnoDni']); // Nuevo DNI ingresado por el usuario
  $contraseña = trim($_POST['nuevaContraseñaAlumno']);
  $is_admin = ($_POST['nuevoAlumnoRol'] == "admin") ? 1 : 0; // Convertir a 1 (admin) o 0 (alumno)

  if (empty($nombre) || empty($email) || empty($dni_nuevo)) {
    echo "Por favor, rellena todos los campos.";
  } else {
    try {
      // Caso 1: Si no hay nueva contraseña
      $query = "UPDATE usuarios SET nombre = ?, email = ?, dni = ?, is_admin = ? WHERE dni = ?";

      // Caso 2: Si hay nueva contraseña, también la actualizamos
      if (!empty($contraseña)) {
        $query = "UPDATE usuarios SET nombre = ?, email = ?, dni = ?, passworduser = ?, is_admin = ? WHERE dni = ?";
      }

      $stmt = $pdo->prepare($query);

      // Ejecutar con o sin nueva contraseña
      if (!empty($contraseña)) {
        $hashed_password = password_hash($contraseña, PASSWORD_BCRYPT);
        $stmt->execute([$nombre, $email, $dni_nuevo, $hashed_password, $is_admin, $dni_actual]);
      } else {
        $stmt->execute([$nombre, $email, $dni_nuevo, $is_admin, $dni_actual]);
      }
      $_SESSION['alert_message'] = 'Alumno actualizado exitosamente.';
      header("Location: indexAdmin.php");
    } catch (PDOException $e) {
      echo "Error al actualizar alumno: " . $e->getMessage();
    }
  }
}

// Eliminar alumno
if (isset($_POST['eliminarAlumno'])) {
  $dni = trim($_POST['alumnoEliminar']); // Este es el DNI original que no debe cambiar antes de la consulta
  try {
    $query = "DELETE FROM usuarios WHERE dni = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$dni]);
    $_SESSION['alert_message'] = 'Alumno eliminado exitosamente.';
    header("Location: indexAdmin.php");
  } catch (PDOException $e) {
    echo "Error al eliminar ciclo formativo: " . $e->getMessage();
  }
}



// =========================================================================================
// ===================== FUNCIONES PARA GESTIONAR MODULOS ==================================
// =========================================================================================

if (isset($_POST['crearModulo'])) {
  // Obtener y limpiar los datos del formulario
  $nombre = trim($_POST['moduloNombre']);
  $descripcion = trim($_POST['moduloDescripcion']);
  $idCiclo = trim($_POST['cicloIdModulo']);
  $profesor = trim($_POST['moduloProfesor']);
  $creditos = trim($_POST['moduloCreditos']);

  // Verificar que los campos no estén vacíos
  if (empty($nombre) || empty($descripcion) || empty($idCiclo) || empty($profesor) || empty($creditos)) {
    echo "Por favor, rellena todos los campos.";
  } else {
    // Asegurarse de que idCiclo sea un número entero
    $idCiclo = intval($idCiclo); // Convierte a número entero. Si no devuelve cero.

    // Verificar que idCiclo sea un valor válido
    if ($idCiclo <= 0) {
      echo "Por favor, selecciona un ciclo válido.";
    } else {
      try {
        // Consulta SQL con los nuevos campos profesor y créditos
        $query = "INSERT INTO modulos (nombre, descripcion, ciclo_formativo_id, profesor, creditos) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);

        // Ejecutar la consulta
        $stmt->execute([$nombre, $descripcion, $idCiclo, $profesor, $creditos]);

        // Mensaje de éxito
        echo "Módulo creado exitosamente.";

        // Redirigir a indexAdmin.php después de la creación
        $_SESSION['alert_message'] = 'Modulo creado exitosamente.';
        header("Location: indexAdmin.php");
        exit;
      } catch (PDOException $e) {
        // Manejar errores
        echo "Error al crear módulo: " . $e->getMessage();
      }
    }
  }
}


// Listar todos los modulos
$query = "SELECT modulos.*, ciclos.nombre AS ciclo_nombre 
          FROM modulos 
          JOIN ciclosformativos AS ciclos ON modulos.ciclo_formativo_id = ciclos.id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Editar modulo
if (isset($_POST['editarModulo'])) {
  $modulo_actual = trim($_POST['moduloId']); // Este es el id del modulo original que no debe cambiar antes de la consulta
  $nombre = trim($_POST['nuevoNombreModulo']);
  $descripcion = trim($_POST['nuevaDescripcionModulo']);
  $cicloModulo = trim($_POST['nuevoCicloModulo']);
  $profesor = trim($_POST['nuevoProfesorModulo']);
  $creditos = trim($_POST['nuevosCreditosModulo']);

  // Verificar que los campos no estén vacíos
  if (empty($nombre) || empty($descripcion) || empty($cicloModulo) || empty($profesor) || empty($creditos)) {
    echo "Por favor, rellena todos los campos.";
  } else {
    try {
      // Consulta SQL para actualizar el módulo con los nuevos campos
      $query = "UPDATE modulos SET nombre = ?, descripcion = ?, ciclo_formativo_id = ?, profesor = ?, creditos = ? WHERE id = ?";

      $stmt = $pdo->prepare($query);

      // Ejecutar la consulta con los nuevos valores
      $stmt->execute([$nombre, $descripcion, $cicloModulo, $profesor, $creditos, $modulo_actual]);

      $_SESSION['alert_message'] = 'Modulo actualizado exitosamente.';
      header("Location: indexAdmin.php");
    } catch (PDOException $e) {
      echo "Error al actualizar módulo: " . $e->getMessage();
    }
  }
}


// Eliminar modulo
if (isset($_POST['eliminarModulo'])) {
  $modulo = trim($_POST['moduloEliminar']);
  try {
    $query = "DELETE FROM modulos WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$modulo]);
    $_SESSION['alert_message'] = 'Modulo eliminados exitosamente.';
    header("Location: indexAdmin.php");
  } catch (PDOException $e) {
    echo "Error al eliminar el modulo: " . $e->getMessage();
  }
}


// =========================================================================================
// ===================== FUNCIONES PARA GESTIONAR CICLOS FORMATIVOS ========================
// =========================================================================================

// Crear ciclo formativo
if (isset($_POST['crearCiclo'])) {
  $cod = trim($_POST['cicloCod']);
  $nombre = trim($_POST['cicloNombre']);
  $descripcion = trim($_POST['cicloDescripcion']);
  $duracion = trim($_POST['cicloDuracion']);

  if (empty($cod) || empty($nombre) || empty($descripcion) || empty($duracion)) {
    echo "Por favor, rellena todos los campos.";
  } else {
    try {
      $query = "INSERT INTO CiclosFormativos (cod, nombre, descripcion, duracion) VALUES (?, ?, ?, ?)";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$cod, $nombre, $descripcion, $duracion]);
      $_SESSION['alert_message'] = 'Ciclo formativo creado exitosamente.';
      header("Location: indexAdmin.php");
      exit;
    } catch (PDOException $e) {
      echo "Error al crear ciclo formativo: " . $e->getMessage();
    }
  }
}


// Listar todos los ciclos formativos
$query = "SELECT * FROM CiclosFormativos";
$stmt = $pdo->prepare($query);
$stmt->execute();
$ciclos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Editar ciclo formativo
if (isset($_POST['editarCiclo'])) {
  $id_ciclo = intval($_POST['cicloId']);
  $cod = trim($_POST['nuevoCicloCod']);
  $nombre = trim($_POST['nuevoCicloNombre']);
  $descripcion = trim($_POST['cicloDescripcion']);
  $duracion = trim($_POST['cicloDuracion']);

  if (empty($cod) || empty($nombre) || empty($descripcion) || empty($duracion)) {
    echo "Por favor, rellena todos los campos.";
  } else {
    try {
      $query = "UPDATE CiclosFormativos SET cod = ?, nombre = ?, descripcion = ?, duracion = ? WHERE id = ?";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$cod, $nombre, $descripcion, $duracion, $id_ciclo]);
      $_SESSION['alert_message'] = 'Ciclo formativo actualizado exitosamente.';
      header("Location: indexAdmin.php");
    } catch (PDOException $e) {
      echo "Error al actualizar ciclo formativo: " . $e->getMessage();
    }
  }
}



// Eliminar ciclo formativo
if (isset($_POST['eliminarCiclo'])) {
  $id_ciclo = intval($_POST['cicloEliminar']);

  try {
    $query = "DELETE FROM CiclosFormativos WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_ciclo]);
    $_SESSION['alert_message'] = 'Ciclo formativo eliminado exitosamente.';
    header("Location: indexAdmin.php");
  } catch (PDOException $e) {
    echo "Error al eliminar ciclo formativo: " . $e->getMessage();
  }
}


// =========================================================================================
// ===================== FUNCIONES PARA GESTIONAR HORARIOS =================================
// =========================================================================================

// Crear Horario
if (isset($_POST['crearHorario'])) {
  $modulo_id = intval($_POST['horarioModulo']);
  $dia_semana = trim($_POST['horarioDia']);
  $hora_inicio = trim($_POST['horarioHoraInicio']);
  $hora_fin = trim($_POST['horarioHoraFin']);

  if (empty($modulo_id) || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
    echo "Por favor, rellena todos los campos para el horario.";
  } else {
    try {
      $query = "INSERT INTO Horarios (modulo_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$modulo_id, $dia_semana, $hora_inicio, $hora_fin]);
      $_SESSION['alert_message'] = 'Horario creado exitosamente.';
      header("Location: indexAdmin.php");
      exit;
    } catch (PDOException $e) {
      echo "Error al crear horario: " . $e->getMessage();
    }
  }
}

// Listar todos los horarios
$query = "SELECT h.id, m.nombre AS modulo, h.dia_semana, h.hora_inicio, h.hora_fin 
          FROM Horarios h 
          JOIN modulos m ON h.modulo_id = m.id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Editar Horario
if (isset($_POST['editarHorario'])) {
  $horario_id = intval($_POST['horarioId']);
  $modulo_id = intval($_POST['nuevoHorarioModulo']);
  $dia_semana = trim($_POST['nuevoHorarioDia']);
  $hora_inicio = trim($_POST['nuevoHorarioHoraInicio']);
  $hora_fin = trim($_POST['nuevoHorarioHoraFin']);

  if (empty($modulo_id) || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
    echo "Por favor, rellena todos los campos para editar el horario.";
  } else {
    try {
      $query = "UPDATE Horarios SET modulo_id = ?, dia_semana = ?, hora_inicio = ?, hora_fin = ? WHERE id = ?";
      $stmt = $pdo->prepare($query);
      $stmt->execute([$modulo_id, $dia_semana, $hora_inicio, $hora_fin, $horario_id]);
      $_SESSION['alert_message'] = 'Horario actualizado exitosamente.';
      header("Location: indexAdmin.php");
    } catch (PDOException $e) {
      echo "Error al actualizar horario: " . $e->getMessage();
    }
  }
}

// Eliminar Horario
if (isset($_POST['eliminarHorario'])) {
  $horario_id = intval($_POST['horarioEliminar']);
  try {
    $query = "DELETE FROM Horarios WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$horario_id]);
    $_SESSION['alert_message'] = 'Horario eliminado exitosamente.';
    header("Location: indexAdmin.php");
  } catch (PDOException $e) {
    echo "Error al eliminar horario: " . $e->getMessage();
  }
}




// =========================================================================================
// ===================== FUNCIONES PARA GESTIONAR MATRICULAS ===============================
// =========================================================================================

// Matricular alumno en un módulo
if (isset($_POST['matricularAlumno'])) {
  $idAlumno = trim($_POST['alumnoIdMatricular']);
  $idModulo = trim($_POST['moduloIdMatricular']);

  if (empty($idAlumno) || empty($idModulo)) {
    echo "Por favor, selecciona un alumno y un módulo.";
  } else {
    try {
      // Verificar si el alumno ya está matriculado en ese módulo
      $queryVerificar = "SELECT COUNT(*) FROM AlumnosModulos WHERE usuario_id = ? AND modulo_id = ?";
      $stmtVerificar = $pdo->prepare($queryVerificar);
      $stmtVerificar->execute([$idAlumno, $idModulo]);
      $existe = $stmtVerificar->fetchColumn();

      if ($existe > 0) {
        $_SESSION['alert_message'] = 'El alumno ya está matriculado en este módulo.';
        header("Location: indexAdmin.php");
        exit;
      }

      // Insertar la matrícula
      $queryInsert = "INSERT INTO AlumnosModulos (usuario_id, modulo_id) VALUES (?, ?)";
      $stmtInsert = $pdo->prepare($queryInsert);
      $stmtInsert->execute([$idAlumno, $idModulo]);

      $_SESSION['alert_message'] = 'Alumno matriculado exitosamente.';
      header("Location: indexAdmin.php");
      exit;
    } catch (PDOException $e) {
      echo "Error al matricular alumno: " . $e->getMessage();
    }
  }
}

// Listar todos los alumnos con sus módulos
$query = "SELECT am.id AS id_alumnomodulo, u.nombre AS nombre_alumno, m.nombre AS nombre_modulo
          FROM AlumnosModulos am
          INNER JOIN Usuarios u ON am.usuario_id = u.id
          INNER JOIN Modulos m ON am.modulo_id = m.id";

$stmt = $pdo->prepare($query);
$stmt->execute();
$alumnosModulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión Administrativa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css-index/index-css.css" rel="stylesheet" type="text/css">
</head>

<body>
<form action="cerrarSesion.php" method="port">
      <button class="btn btn-primary fixed-top" type="submit" class="fixed-button">Cerrar Sesión</button>
    </form>
  <div class="container mt-5">
    
    <h1 class="section-title">Gestión Administrativa</h1>

    <!-- Menú de navegación para la selección de las funciones -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link active" id="gestion-alumnos-tab" data-bs-toggle="tab" href="#gestion-alumnos" role="tab"
          aria-controls="gestion-alumnos" aria-selected="true">Gestionar Alumnos</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="gestion-ciclos-tab" data-bs-toggle="tab" href="#gestion-ciclos" role="tab"
          aria-controls="gestion-ciclos" aria-selected="false">Gestionar Ciclos Formativos</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="gestion-modulos-tab" data-bs-toggle="tab" href="#gestion-modulos" role="tab"
          aria-controls="gestion-modulos" aria-selected="false">Gestionar Módulos</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="gestion-horarios-tab" data-bs-toggle="tab" href="#gestion-horarios" role="tab"
          aria-controls="gestion-horarios" aria-selected="false">Gestionar Horarios</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="gestion-matriculas-tab" data-bs-toggle="tab" href="#gestion-matriculas" role="tab"
          aria-controls="gestion-matriculas" aria-selected="false">Gestionar matriculas</a>
      </li>
    </ul>

    <div class="tab-content" id="myTabContent">
      <!-- GESTIÓN DE ALUMNOS -->
      <div class="tab-pane fade show active" id="gestion-alumnos" role="tabpanel" aria-labelledby="gestion-alumnos-tab">
        <h2>Gestionar alumnos</h2>
        <div class="mb-3">
          <!-- Botones para gestión de alumnos -->
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#ver-alumnos-form"
            aria-expanded="true" aria-controls="ver-alumnos-form">Ver alumnos</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#crear-alumnos-form"
            aria-expanded="false" aria-controls="crear-alumnos-form">Crear alumnos</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#editar-alumnos-form"
            aria-expanded="false" aria-controls="editar-alumnos-form">Editar alumnos</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#eliminar-alumnos-form"
            aria-expanded="false" aria-controls="eliminar-alumnos-form">Eliminar alumnos</button>
        </div>

        <!-- Contenedor de acordeón para los formularios de alumnos -->
        <div id="accordion-alumnos">
          <!-- Crear alumno -->
          <div class="collapse" id="crear-alumnos-form" data-bs-parent="#accordion-alumnos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="alumnoNombre" class="form-label">Nombre del alumno</label>
                <input type="text" class="form-control" id="alumnoNombre" name="alumnoNombre" required>
              </div>
              <div class="mb-3">
                <label for="alumnoEmail" class="form-label">Email</label>
                <textarea class="form-control" id="alumnoEmail" name="alumnoEmail"></textarea>
              </div>
              <div class="mb-3">
                <label for="alumnoDNI" class="form-label">DNI</label>
                <textarea class="form-control" id="alumnoDNI" name="alumnoDNI"></textarea>
              </div>
              <div class="mb-3">
                <label for="alumnoContraseña" class="form-label">Contraseña</label>
                <textarea class="form-control" id="alumnoContraseña" name="alumnoContraseña"></textarea>
              </div>
              <div class="mb-3">
                <label for="alumnoAdmin" class="form-label">Rol</label>
                <br>
                <select name="alumnoAdmin" id="alumnoAdmin">
                  <option value="admin">Administrador</option>
                  <option value="alumno">Alumno</option>
                </select>
              </div>
              <button type="submit" name="crearAlumno" class="btn btn-success">Crear alumno</button>
            </form>
          </div>

          <!-- Ver alumnos (con la clase show para que esté abierto por defecto)-->
          <div class="collapse" id="ver-alumnos-form" data-bs-parent="#accordion-alumnos">
            <h3>Alumnos Existentes</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>Identificador</th>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>DNI</th>
                  <th>Contraseña</th>
                  <th>Rol</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($alumnos as $alumno) {
                  echo "<tr>
                      <td>{$alumno['id']}</td>
                      <td>{$alumno['nombre']}</td>
                      <td>{$alumno['email']}</td>
                      <td>{$alumno['dni']}</td>
                      <td>{$alumno['passworduser']}</td>
                      <td>{$alumno['is_admin']}</td>
                    </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

          <!-- Editar alumnos -->
          <div class="collapse" id="editar-alumnos-form" data-bs-parent="#accordion-alumnos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="alumnoDNIEditar" class="form-label">Seleccionar alumno</label>
                <select class="form-select" id="alumnoDNIEditar" name="alumnoDNIEditar" required>
                  <?php
                  foreach ($alumnos as $alumno) {
                    echo "<option value='{$alumno['dni']}'>{$alumno['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="nuevoAlumnoNombre" class="form-label">Nuevo nombre</label>
                <input type="text" class="form-control" id="nuevoAlumnoNombre" name="nuevoAlumnoNombre">
              </div>
              <div class="mb-3">
                <label for="nuevoAlumnoEmail" class="form-label">Nuevo email</label>
                <input type="text" class="form-control" id="nuevoAlumnoEmail" name="nuevoAlumnoEmail">
              </div>
              <div class="mb-3">
                <label for="nuevoAlumnoDni" class="form-label">Nuevo DNI</label>
                <textarea class="form-control" id="nuevoAlumnoDni" name="nuevoAlumnoDni"></textarea>
              </div>
              <div class="mb-3">
                <label for="nuevaContraseñaAlumno" class="form-label">Nueva contraseña</label>
                <textarea class="form-control" id="nuevaContraseñaAlumno" name="nuevaContraseñaAlumno"></textarea>
              </div>
              <select name="nuevoAlumnoRol" id="nuevoAlumnoRol">
                <option value="admin">Administrador</option>
                <option value="alumno">Alumno</option>
              </select>
              <br>
              <button type="submit" name="editarAlumno" class="btn btn-warning">Editar alumno</button>
            </form>
          </div>

          <!-- Eliminar alumno -->
          <div class="collapse" id="eliminar-alumnos-form" data-bs-parent="#accordion-alumnos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="alumnoEliminar" class="form-label">Seleccionar alumno a eliminar</label>
                <select class="form-select" id="alumnoEliminar" name="alumnoEliminar" required>
                  <?php
                  foreach ($alumnos as $alumno) {
                    echo "<option value='{$alumno['dni']}'>{$alumno['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <button type="submit" name="eliminarAlumno" class="btn btn-danger">Eliminar alumno</button>
            </form>
          </div>
        </div>
        <!-- Fin contenedor accordion-alumnos -->
      </div>
    </div>



    <div class="tab-content" id="myTabContent">
      <!-- GESTIÓN DE CICLOS -->
      <div class="tab-pane fade" id="gestion-ciclos" role="tabpanel" aria-labelledby="gestion-ciclos-tab">
        <h2>Gestionar ciclos formativos</h2>
        <div class="mb-3">
          <!-- Botones para gestión de ciclos -->
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#ver-ciclos-form"
            aria-expanded="false" aria-controls="ver-ciclos-form">Ver Ciclos Formativos</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#crear-ciclo-form"
            aria-expanded="false" aria-controls="crear-ciclo-form">Crear Ciclo Formativo</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#editar-ciclo-form"
            aria-expanded="false" aria-controls="editar-ciclo-form">Editar Ciclo Formativo</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#eliminar-ciclo-form"
            aria-expanded="false" aria-controls="eliminar-ciclo-form">Eliminar Ciclo Formativo</button>
        </div>

        <!-- Contenedor de acordeón para los formularios de ciclos -->
        <div id="accordion-ciclos">
          <!-- Crear Ciclo Formativo -->
          <div class="collapse" id="crear-ciclo-form" data-bs-parent="#accordion-ciclos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="cicloCod" class="form-label">Código del Ciclo</label>
                <input type="text" class="form-control" id="cicloCod" name="cicloCod" required>
              </div>
              <div class="mb-3">
                <label for="cicloNombre" class="form-label">Nombre del Ciclo</label>
                <input type="text" class="form-control" id="cicloNombre" name="cicloNombre" required>
              </div>
              <div class="mb-3">
                <label for="cicloDescripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="cicloDescripcion" name="cicloDescripcion"></textarea>
              </div>
              <div class="mb-3">
                <label for="cicloDuracion" class="form-label">Duración (años)</label>
                <select class="form-control" id="cicloDuracion" name="cicloDuracion" required>
                  <option value="1">1 Año</option>
                  <option value="2">2 Años</option>
                </select>
              </div>
              <button type="submit" name="crearCiclo" class="btn btn-success">Crear Ciclo</button>
            </form>
          </div>

          <!-- Ver Ciclos Formativos -->
          <div class="collapse" id="ver-ciclos-form" data-bs-parent="#accordion-ciclos">
            <h3>Ciclos Formativos Existentes</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Nombre</th>
                  <th>Descripción</th>
                  <th>Duración</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($ciclos as $ciclo) {
                  echo "<tr>
                      <td>{$ciclo['cod']}</td>
                      <td>{$ciclo['nombre']}</td>
                      <td>{$ciclo['descripcion']}</td>
                      <td>{$ciclo['duracion']}</td>
                    </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

          <!-- Editar Ciclo Formativo -->
          <div class="collapse" id="editar-ciclo-form" data-bs-parent="#accordion-ciclos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="cicloId" class="form-label">Seleccionar Ciclo</label>
                <select class="form-select" id="cicloId" name="cicloId" required>
                  <?php
                  foreach ($ciclos as $ciclo) {
                    echo "<option value='{$ciclo['id']}'>{$ciclo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="nuevoCicloCod" class="form-label">Nuevo Código</label>
                <input type="text" class="form-control" id="nuevoCicloCod" name="nuevoCicloCod">
              </div>
              <div class="mb-3">
                <label for="nuevoCicloNombre" class="form-label">Nuevo Nombre</label>
                <input type="text" class="form-control" id="nuevoCicloNombre" name="nuevoCicloNombre">
              </div>
              <div class="mb-3">
                <label for="cicloDescripcion" class="form-label">Nueva Descripción</label>
                <textarea class="form-control" id="cicloDescripcion" name="cicloDescripcion"></textarea>
              </div>
              <div class="mb-3">
                <label for="cicloDuracion" class="form-label">Nueva Duración</label>
                <select class="form-select" id="cicloDuracion" name="cicloDuracion" required>
                  <option value="1">1 Año</option>
                  <option value="2">2 Años</option>
                </select>
              </div>
              <button type="submit" name="editarCiclo" class="btn btn-warning">Editar Ciclo</button>
            </form>
          </div>


          <!-- Eliminar Ciclo Formativo -->
          <div class="collapse" id="eliminar-ciclo-form" data-bs-parent="#accordion-ciclos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="cicloEliminar" class="form-label">Seleccionar Ciclo a Eliminar</label>
                <select class="form-select" id="cicloEliminar" name="cicloEliminar" required>
                  <?php
                  foreach ($ciclos as $ciclo) {
                    echo "<option value='{$ciclo['id']}'>{$ciclo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <button type="submit" name="eliminarCiclo" class="btn btn-danger">Eliminar Ciclo</button>
            </form>
          </div>
        </div>
        <!-- Fin del contenedor accordion-ciclos -->
      </div>
    </div>


    <div class="tab-content" id="myTabContent">
      <!-- GESTIÓN DE MÓDULOS -->
      <div class="tab-pane fade" id="gestion-modulos" role="tabpanel" aria-labelledby="gestion-modulos-tab">
        <h2>Gestionar módulos</h2>
        <div class="mb-3">
          <!-- Botones para gestión de módulos -->
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#ver-modulos-form"
            aria-expanded="false" aria-controls="ver-modulos-form">Ver módulos</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#crear-modulos-form"
            aria-expanded="false" aria-controls="crear-modulos-form">Crear módulos</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#editar-modulos-form"
            aria-expanded="false" aria-controls="editar-modulos-form">Editar módulos</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#eliminar-modulos-form"
            aria-expanded="false" aria-controls="eliminar-modulos-form">Eliminar módulos</button>
        </div>

        <!-- Contenedor de acordeón para los formularios de módulos -->
        <div id="accordion-modulos">
          <!-- Crear módulo -->
          <div class="collapse" id="crear-modulos-form" data-bs-parent="#accordion-modulos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="moduloNombre" class="form-label">Nombre del módulo</label>
                <input type="text" class="form-control" id="moduloNombre" name="moduloNombre" required>
              </div>
              <div class="mb-3">
                <label for="moduloDescripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="moduloDescripcion" name="moduloDescripcion"></textarea>
              </div>
              <div class="mb-3">
                <label for="cicloIdModulo" class="form-label">Seleccionar Ciclo Formativo</label>
                <select class="form-select" id="cicloIdModulo" name="cicloIdModulo" required>
                  <!-- Opciones de ciclos dinámicamente desde DB -->
                  <?php
                  foreach ($ciclos as $ciclo) {
                    echo "<option value='{$ciclo['id']}'>{$ciclo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="moduloProfesor" class="form-label">Profesor</label>
                <input type="text" class="form-control" id="moduloProfesor" name="moduloProfesor" required>
              </div>
              <div class="mb-3">
                <label for="moduloCreditos" class="form-label">Créditos</label>
                <input type="number" class="form-control" id="moduloCreditos" name="moduloCreditos" required>
              </div>
              <button type="submit" name="crearModulo" class="btn btn-success mt-2">Crear módulo</button>
            </form>
          </div>


          <!-- Ver módulos -->
          <div class="collapse" id="ver-modulos-form" data-bs-parent="#accordion-modulos">
            <h3>Módulos Existentes</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Descripción</th>
                  <th>Profesor</th>
                  <th>Créditos</th>
                  <th>Id_Ciclo</th>
                  <th>Nombre_Ciclo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($modulos as $modulo) {
                  echo "<tr>
                <td>{$modulo['nombre']}</td>
                <td>{$modulo['descripcion']}</td>
                <td>{$modulo['profesor']}</td>
                <td>{$modulo['creditos']}</td>
                <td>{$modulo['ciclo_formativo_id']}</td>
                <td>{$modulo['ciclo_nombre']}</td>
              </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>


          <!-- Editar módulos -->
          <div class="collapse" id="editar-modulos-form" data-bs-parent="#accordion-modulos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="moduloId" class="form-label">Seleccionar módulo</label>
                <select class="form-select" id="moduloId" name="moduloId" required>
                  <?php
                  foreach ($modulos as $modulo) {
                    echo "<option value='{$modulo['id']}'>{$modulo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="nuevoNombreModulo" class="form-label">Nuevo nombre</label>
                <input type="text" class="form-control" id="nuevoNombreModulo" name="nuevoNombreModulo">
              </div>
              <div class="mb-3">
                <label for="nuevaDescripcionModulo" class="form-label">Nueva descripción</label>
                <input type="text" class="form-control" id="nuevaDescripcionModulo" name="nuevaDescripcionModulo">
              </div>
              <div class="mb-3">
                <label for="nuevoCicloModulo" class="form-label">Seleccionar Ciclo Formativo</label>
                <select class="form-select" id="nuevoCicloModulo" name="nuevoCicloModulo" required>
                  <?php
                  foreach ($ciclos as $ciclo) {
                    echo "<option value='{$ciclo['id']}'>{$ciclo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="nuevoProfesorModulo" class="form-label">Nuevo Profesor</label>
                <input type="text" class="form-control" id="nuevoProfesorModulo" name="nuevoProfesorModulo">
              </div>
              <div class="mb-3">
                <label for="nuevosCreditosModulo" class="form-label">Nuevos Créditos</label>
                <input type="number" class="form-control" id="nuevosCreditosModulo" name="nuevosCreditosModulo">
              </div>
              <button type="submit" name="editarModulo" class="btn btn-warning mt-2">Editar módulo</button>
            </form>
          </div>


          <!-- Eliminar módulos -->
          <div class="collapse" id="eliminar-modulos-form" data-bs-parent="#accordion-modulos">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="moduloEliminar" class="form-label">Seleccionar módulo a eliminar</label>
                <select class="form-select" id="moduloEliminar" name="moduloEliminar" required>
                  <?php
                  foreach ($modulos as $modulo) {
                    echo "<option value='{$modulo['id']}'>{$modulo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <button type="submit" name="eliminarModulo" class="btn btn-danger mt-2">Eliminar módulo</button>
            </form>
          </div>
        </div>
        <!-- Fin del contenedor accordion-modulos -->
      </div>
    </div>


    <div class="tab-content" id="myTabContent">
      <!-- GESTIÓN DE HORARIOS -->
      <div class="tab-pane fade" id="gestion-horarios" role="tabpanel" aria-labelledby="gestion-horarios-tab">
        <h2>Gestionar horarios</h2>
        <div class="mb-3">
          <!-- Botones para gestión de horarios -->
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#ver-horarios-form"
            aria-expanded="false" aria-controls="ver-horarios-form">Ver Horarios</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#crear-horarios-form"
            aria-expanded="false" aria-controls="crear-horarios-form">Crear Horario</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#editar-horarios-form"
            aria-expanded="false" aria-controls="editar-horarios-form">Editar Horario</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#eliminar-horarios-form"
            aria-expanded="false" aria-controls="eliminar-horarios-form">Eliminar Horario</button>
        </div>

        <!-- Contenedor de acordeón para los formularios de horarios -->
        <div id="accordion-horarios">
          <!-- Crear Horario -->
          <div class="collapse" id="crear-horarios-form" data-bs-parent="#accordion-horarios">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="horarioModulo" class="form-label">Módulo</label>
                <select class="form-select" id="horarioModulo" name="horarioModulo" required>
                  <?php
                  // Se utiliza la variable $modulos que ya tienes definida
                  foreach ($modulos as $modulo) {
                    echo "<option value='{$modulo['id']}'>{$modulo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="horarioDia" class="form-label">Día de la Semana</label>
                <select class="form-select" id="horarioDia" name="horarioDia" required>
                  <option value="Lunes">Lunes</option>
                  <option value="Martes">Martes</option>
                  <option value="Miércoles">Miércoles</option>
                  <option value="Jueves">Jueves</option>
                  <option value="Viernes">Viernes</option>
                  <option value="Sábado">Sábado</option>
                  <option value="Domingo">Domingo</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="horarioHoraInicio" class="form-label">Hora de Inicio</label>
                <input type="time" class="form-control" id="horarioHoraInicio" name="horarioHoraInicio" required>
              </div>
              <div class="mb-3">
                <label for="horarioHoraFin" class="form-label">Hora de Fin</label>
                <input type="time" class="form-control" id="horarioHoraFin" name="horarioHoraFin" required>
              </div>
              <button type="submit" name="crearHorario" class="btn btn-success">Crear Horario</button>
            </form>
          </div>

          <!-- Ver Horarios -->
          <div class="collapse" id="ver-horarios-form" data-bs-parent="#accordion-horarios">
            <h3>Horarios Existentes</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Módulo</th>
                  <th>Día</th>
                  <th>Hora Inicio</th>
                  <th>Hora Fin</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($horarios as $horario) {
                  echo "<tr>
                      <td>{$horario['id']}</td>
                      <td>{$horario['modulo']}</td>
                      <td>{$horario['dia_semana']}</td>
                      <td>{$horario['hora_inicio']}</td>
                      <td>{$horario['hora_fin']}</td>
                    </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

          <!-- Editar Horario -->
          <div class="collapse" id="editar-horarios-form" data-bs-parent="#accordion-horarios">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="horarioId" class="form-label">Seleccionar Horario a Editar</label>
                <select class="form-select" id="horarioId" name="horarioId" required>
                  <?php
                  foreach ($horarios as $horario) {
                    echo "<option value='{$horario['id']}'>
                        ID: {$horario['id']} - Módulo: {$horario['modulo']} - Día: {$horario['dia_semana']}
                      </option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="nuevoHorarioModulo" class="form-label">Nuevo Módulo</label>
                <select class="form-select" id="nuevoHorarioModulo" name="nuevoHorarioModulo" required>
                  <?php
                  foreach ($modulos as $modulo) {
                    echo "<option value='{$modulo['id']}'>{$modulo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="nuevoHorarioDia" class="form-label">Nuevo Día de la Semana</label>
                <select class="form-select" id="nuevoHorarioDia" name="nuevoHorarioDia" required>
                  <option value="Lunes">Lunes</option>
                  <option value="Martes">Martes</option>
                  <option value="Miércoles">Miércoles</option>
                  <option value="Jueves">Jueves</option>
                  <option value="Viernes">Viernes</option>
                  <option value="Sábado">Sábado</option>
                  <option value="Domingo">Domingo</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="nuevoHorarioHoraInicio" class="form-label">Nueva Hora de Inicio</label>
                <input type="time" class="form-control" id="nuevoHorarioHoraInicio" name="nuevoHorarioHoraInicio"
                  required>
              </div>
              <div class="mb-3">
                <label for="nuevoHorarioHoraFin" class="form-label">Nueva Hora de Fin</label>
                <input type="time" class="form-control" id="nuevoHorarioHoraFin" name="nuevoHorarioHoraFin" required>
              </div>
              <button type="submit" name="editarHorario" class="btn btn-warning">Editar Horario</button>
            </form>
          </div>

          <!-- Eliminar Horario -->
          <div class="collapse" id="eliminar-horarios-form" data-bs-parent="#accordion-horarios">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="horarioEliminar" class="form-label">Seleccionar Horario a Eliminar</label>
                <select class="form-select" id="horarioEliminar" name="horarioEliminar" required>
                  <?php
                  foreach ($horarios as $horario) {
                    echo "<option value='{$horario['id']}'>
                        ID: {$horario['id']} - Módulo: {$horario['modulo']} - Día: {$horario['dia_semana']}
                      </option>";
                  }
                  ?>
                </select>
              </div>
              <button type="submit" name="eliminarHorario" class="btn btn-danger">Eliminar Horario</button>
            </form>
          </div>
        </div>
        <!-- Fin del contenedor accordion-horarios -->
      </div>
    </div>

    <div class="tab-content" id="myTabContent">
      <!-- GESTIÓN DE MATRICULAS -->
      <div class="tab-pane fade" id="gestion-matriculas" role="tabpanel" aria-labelledby="gestion-matriculas-tab">
        <h2>Gestionar matrículas</h2>
        <div class="mb-3">
          <!-- Botones para gestión de alumnos -->
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#ver-matriculas-form"
            aria-expanded="false" aria-controls="ver-matriculas-form">Ver matriculas</button>
          <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#matricular-alumnos-form"
            aria-expanded="false" aria-controls="matricular-alumnos-form">Matricular alumnos en modulos</button>
        </div>

        <!-- Contenedor de acordeón para los formularios de matriculas -->
        <div id="accordion-matriculas">
          <!-- Ver matriculas -->
          <div class="collapse" id="ver-matriculas-form" data-bs-parent="#accordion-matriculas">
            <h3>Matrículas Existentes</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>Identificador</th>
                  <th>Alumno</th>
                  <th>Módulo</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($alumnosModulos as $alumnoModulo) {
                  echo "<tr>
                      <td>{$alumnoModulo['id_alumnomodulo']}</td>
                      <td>{$alumnoModulo['nombre_alumno']}</td>
                      <td>{$alumnoModulo['nombre_modulo']}</td>
                    </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>

          <!-- Matricular Alumnos-->
          <div class="collapse" id="matricular-alumnos-form" data-bs-parent="#accordion-matriculas">
            <form action="indexAdmin.php" method="POST">
              <div class="mb-3">
                <label for="alumnoIdMatricular" class="form-label">Seleccionar alumno</label>
                <select class="form-select" id="alumnoIdMatricular" name="alumnoIdMatricular" required>
                  <?php
                  foreach ($alumnos as $alumno) {
                    echo "<option value='{$alumno['id']}'>{$alumno['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="moduloIdMatricular" class="form-label">Seleccionar Modulo</label>
                <select class="form-select" id="moduloIdMatricular" name="moduloIdMatricular" required>
                  <?php
                  foreach ($modulos as $modulo) {
                    echo "<option value='{$modulo['id']}'>{$modulo['nombre']}</option>";
                  }
                  ?>
                </select>
              </div>
              <button type="submit" name="matricularAlumno" class="btn btn-success">Matricular alumno</button>
            </form>
          </div>
        </div>
        <!-- Fin contenedor accordion-matriculas -->
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>