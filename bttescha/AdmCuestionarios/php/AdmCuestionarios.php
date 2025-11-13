<?php
require_once "../../config/database.php";
include "../../helpers/php/functions.php";
session_start();

db = conectar_db();
if (!$db) exit;

/**
 * Lógica de recepción de datos (JSON/POST/GET) de otros controladores
 */
$jsonData = [];
$content_type = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : "";
if ($content_type === "application/json") {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
}
$datos_post = count($jsonData) > 0 ? sanitize_array($jsonData) : sanitize_array($_POST);

$opcion = $_SERVER["REQUEST_METHOD"] === "POST" ? $datos_post["case"] : $_GET["case"]; 

switch ($opcion) {
    case "validarAcceso":
        validar_acceso_cuestionario($db, $datos_post);
        break;

    default:
        send_response(["ok" => false, "mensaje" => "Operación no soportada."], 400);
        break;
}

/**
 * Valida la matrícula del estudiante y determina si es elegible para el cuestionario.
 *
 * @param mysqli $db    Conexión a la base de datos.
 * @param array  $data  Datos de la petición (matricula, tipo).
 */
function validar_acceso_cuestionario($db, $data) {
    $matricula_raw = $data["matricula"] ?? '';
    $tipo_cuestionario = (string)($data["tipo"] ?? '');

    $matricula = trim($matricula_raw);
    if ($matricula === '') {
        send_response(["ok" => false, "mensaje" => "La matrícula es requerida."], 400);
        return;
    }

    // Normalizar entrada: quitar espacios y pasar a minúsculas para comparar
    $matricula_norm = mb_strtolower(preg_replace('/\\s+/', '', $matricula), 'UTF-8');

    // Buscar estudiante por Matricula o NumeroControl (si existe)
    $sql = "SELECT IdEstudiante, IdUsuario, Matricula, Estatus_Periodo\n            FROM estudiante\n            WHERE REPLACE(LOWER(Matricula), ' ', '') = ?\n               OR (COALESCE(NumeroControl, '') != '' AND REPLACE(LOWER(NumeroControl), ' ', '') = ?)\n            LIMIT 1";

    if (!($stmt = $db->prepare($sql))) {
        send_response(["ok" => false, "mensaje" => "Error del servidor (prepare)."], 500);
        return;
    }
    $stmt->bind_param("ss", $matricula_norm, $matricula_norm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        send_response(["ok" => false, "mensaje" => "Matrícula no encontrada."], 404);
        return;
    }

    $estudiante = $result->fetch_assoc();
    $id_estudiante = $estudiante['IdEstudiante'];
    $id_usuario = $estudiante['IdUsuario'];
    $estatus_actual = $estudiante['Estatus_Periodo'] ?? '';

    // Normalizar estatus para comparación (quitamos espacios y forzamos minúsculas)
    $estatus_normalizado = mb_strtolower(preg_replace('/\\s+/', '', $estatus_actual), 'UTF-8');

    // Definir mapa de valores aceptados (puedes ampliar equivalencias aquí)
    $mapa_estatus = [
        'terminooctavo' => '1',
        'residenciaterminada' => '2',
        'pendientetitulacion' => '3',
        // equivalencias comunes
        'termino_octavo' => '1',
        'residencia_terminada' => '2',
        'pendiente_titulación' => '3',
    ];

    $acceso_permitido = false;
    $mensaje_acceso = '';

    switch ($tipo_cuestionario) {
        case '1':
            if (isset($mapa_estatus[$estatus_normalizado]) && $mapa_estatus[$estatus_normalizado] === '1') {
                $acceso_permitido = true;
            } else {
                $mensaje_acceso = "Este cuestionario aplica solo para estudiantes que terminaron el octavo semestre.";
            }
            break;
        case '2':
            if (isset($mapa_estatus[$estatus_normalizado]) && $mapa_estatus[$estatus_normalizado] === '2') {
                $acceso_permitido = true;
            } else {
                $mensaje_acceso = "Este cuestionario aplica solo para estudiantes que terminaron la residencia.";
            }
            break;
        case '3':
            if (isset($mapa_estatus[$estatus_normalizado]) && $mapa_estatus[$estatus_normalizado] === '3') {
                $acceso_permitido = true;
            } else {
                $mensaje_acceso = "Este cuestionario aplica solo para egresados próximos a titulación.";
            }
            break;
        default:
            send_response(["ok" => false, "mensaje" => "Tipo de cuestionario no válido."], 400);
            return;
    }

    if ($acceso_permitido) {
        // Crear sesión para que la parte de estudiante reconozca al usuario
        // Si no hay IdUsuario asociado, se crea una sesión temporal con id_usuario = 0
        $_SESSION['id_usuario'] = $id_usuario ? (int)$id_usuario : 0;
        $_SESSION['id_estudiante'] = (int)$id_estudiante;
        $_SESSION['role'] = 'estudiante';

        send_response([
            "ok" => true,
            "mensaje" => "Acceso permitido. Verificación exitosa.",
            "id_estudiante" => $id_estudiante,
            "cuestionario" => $tipo_cuestionario,
        ]);
    } else {
        send_response([
            "ok" => false,
            "mensaje" => "No es posible acceder al cuestionario. $mensaje_acceso",
        ], 403);
    }
}