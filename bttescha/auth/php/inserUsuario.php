<?php
// Incluir archivo de conexión a la base de datos
require_once __DIR__ . "/../../config/database.php";

// Obtener la conexión a la base de datos
$db = conectar_db();
if (!$db) exit;  // Verificar que la conexión fue exitosa

// Definir la contraseña proporcionada por el usuario
$password = 'xiu';  // La contraseña proporcionada por el usuario

// Encriptar la contraseña usando password_hash
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Realizar la inserción en la base de datos
$sql = "INSERT INTO Usuario (Nick, CorreoElectronico, Contraseña, tipo_usuario, Borrado)
        VALUES ('pablo', 'pablo@tescha.com', '$password_hash', 3, 1)";

// Ejecutar la consulta
if ($db->query($sql)) {
    echo "Usuario 'pablo' insertado exitosamente.";
} else {
    echo "Error al insertar el usuario: " . $db->error;
}
?>
