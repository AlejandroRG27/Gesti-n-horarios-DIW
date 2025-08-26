<?php
require_once 'TCPDF-main\tcpdf.php';
include 'conexion.php';

session_start();
if (!isset($_SESSION['alumno_id'])) {
    die('Acceso denegado.');
}

$alumno_id = $_SESSION['alumno_id'];

// Obtener módulos del alumno
$query = "SELECT horarios.dia_semana, horarios.hora_inicio, horarios.hora_fin, modulos.nombre AS modulo 
          FROM horarios 
          INNER JOIN modulos ON horarios.modulo_id = modulos.id 
          INNER JOIN alumnosmodulos ON modulos.id = alumnosmodulos.modulo_id 
          WHERE alumnosmodulos.usuario_id = ? 
          ORDER BY FIELD(horarios.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), horarios.hora_inicio";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $alumno_id);
$stmt->execute();
$result = $stmt->get_result();

// Crear PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Gestión');
$pdf->SetTitle('Horario Alumno');
$pdf->SetMargins(15, 10, 15);  // Márgenes: izquierda, arriba, derecha
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16); // Establecer fuente
$pdf->Cell(0, 10, 'Horario Personalizado', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(50, 10, 'Día', 1, 0, 'C');
$pdf->Cell(50, 10, 'Hora Inicio', 1, 0, 'C');
$pdf->Cell(50, 10, 'Hora Fin', 1, 0, 'C');
$pdf->Cell(50, 10, 'Módulo', 1, 1, 'C');
$pdf->SetFont('helvetica', '', 12);

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(50, 10, $row['dia_semana'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['hora_inicio'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['hora_fin'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['modulo'], 1, 1, 'C');
}

$pdf->Output('horario.pdf', 'D');
?>
