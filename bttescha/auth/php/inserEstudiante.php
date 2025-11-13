<?php
// Script local para crear Usuario + Estudiante de prueba.
// Uso web: /bttescha/auth/php/inserEstudiante.php?matricula=S2025001&nick=amy&pass=amy123&nombre=Amy&estatus=TerminoOctavo
// O CLI: php inserEstudiante.php S2025001 amy amy123 "Amy Ramos" TerminoOctavo

require_once __DIR__ . "/../../config/database.php";
$db = conectar_db();
if (!$db) exit("No se pudo conectar a la BD");

// Obtener parámetros (GET/POST/argv)
$matricula = $_GET['matricula'] ?? ($argv[1] ?? null);
$nick = $_GET['nick'] ?? ($argv[2] ?? null);
$pass = $_GET['pass'] ?? ($argv[3] ?? null);
$nombre = $_GET['nombre'] ?? ($argv[4] ?? 'Estudiante Prueba');
$estatus = $_GET['estatus'] ?? ($argv[5] ?? 'TerminoOctavo');

if (!$matricula || !$nick || !$pass) {
    echo "Faltan parámetros. Ejemplo: ?matricula=S2025001&nick=amy&pass=amy123\n";
    exit;
}

// 1) Crear Usuario (asegurar que no exista)
$check = $db->prepare("SELECT IdUsuario FROM Usuario WHERE Nick = ?");
$check->bind_param("s", $nick);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->bind_result($existingId);
    $check->fetch();
    $id_usuario = $existingId;
    echo "El usuario $nick ya existe (IdUsuario={$id_usuario}). Se usará ese Id.\n";
    $check->close();
} else {
    $password_hash = password_hash($pass, PASSWORD_DEFAULT);
    $tipo_usuario = 3; // ajustar si tu esquema tiene otro valor
    $email = "{$nick}@example.local";
    $stmt = $db->prepare("INSERT INTO Usuario (Nick, CorreoElectronico, Contraseña, tipo_usuario, Borrado) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sssi", $nick, $email, $password_hash, $tipo_usuario);
    if (!$stmt->execute()) {
        echo "Error creando Usuario: " . $db->error . "\n";
        exit;
    }
    $id_usuario = $db->insert_id;
    $stmt->close();
    echo "Usuario creado: IdUsuario={$id_usuario}\n";
}

// 2) Crear o actualizar registro en estudiante enlazado a IdUsuario
// Comprobar si ya existe matrícula
$check2 = $db->prepare("SELECT IdEstudiante FROM estudiante WHERE Matricula = ? LIMIT 1");
$check2->bind_param("s", $matricula);
$check2->execute();
$check2->store_result();
if ($check2->num_rows > 0) {
    $check2->bind_result($existingEstId);
    $check2->fetch();
    // Actualizar registro para asegurar Estatus_Periodo e IdUsuario
    $stmtUpd = $db->prepare("UPDATE estudiante SET IdUsuario = ?, Estatus_Periodo = ? WHERE IdEstudiante = ?");
    $stmtUpd->bind_param("isi", $id_usuario, $estatus, $existingEstId);
    if ($stmtUpd->execute()) {
        echo "Estudiante existente actualizado: IdEstudiante={$existingEstId}\n";
    } else {
        echo "Error actualizando estudiante: " . $db->error . "\n";
    }
    $check2->close();
} else {
    // Insertar nuevo estudiante (ajusta columnas si tu tabla requiere más campos)
    $stmt2 = $db->prepare("INSERT INTO estudiante (IdUsuario, Matricula, Nombre, PrimerApellido, Estatus_Periodo, DatosLlenos, Borrado) VALUES (?, ?, ?, '', ?, 'N', 1)");
    $stmt2->bind_param("isss", $id_usuario, $matricula, $nombre, $estatus);
    if (!$stmt2->execute()) {
        echo "Error creando estudiante: " . $db->error . "\n";
        exit;
    }
    $id_estudiante = $db->insert_id;
    $stmt2->close();
    echo "Estudiante creado: IdEstudiante={$id_estudiante}\n";
}

echo "Operación completada.\n";
?>