<?php

require_once "../../config/database.php";
include "../../helpers/php/functions.php"; 
$db = conectar_db();
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
        // Usamos $datos_post porque el Front-end envía los datos con POST
        validar_acceso_cuestionario($db, $datos_post);
        break;
    
    // Aquí se añadirán los casos para guardar las respuestas de los cuestionarios (ej. case "guardarCuestionario1")
    
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
    // Asegurar que usamos sentencias preparadas para la matrícula para prevenir SQL Injection
    $matricula = $data["matricula"];
    $tipo_cuestionario = $data["tipo"]; // 1, 2, o 3
    
    if (empty($matricula)) {
        send_response(["ok" => false, "mensaje" => "La matrícula es obligatoria."], 422);
        return;
    }

    try {
        // PASO 1: Obtener el estudiante y su estatus actual
        // ASUMIMOS que la tabla 'estudiante' tiene una columna 'Matricula' y una columna 'Estatus_Periodo'
        $query = "SELECT IdEstudiante, Estatus_Periodo FROM estudiante WHERE Matricula = ? AND Borrado = 1";
        $stmt = $db->prepare($query);
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 0) {
            send_response(["ok" => false, "mensaje" => "Matrícula no encontrada o inactiva."], 404);
            return;
        }

        $estudiante = $resultado->fetch_assoc();
        $id_estudiante = $estudiante['IdEstudiante'];
        $estatus_actual = $estudiante['Estatus_Periodo']; // Columna que define el estado académico

        // PASO 2: Lógica para determinar elegibilidad según el caso:
        $acceso_permitido = false;
        $mensaje_acceso = "";

        switch ($tipo_cuestionario) {
            case '1': // Actualización de datos (termiando octavo)
                if ($estatus_actual === 'TerminoOctavo') { 
                    $acceso_permitido = true;
                } else {
                    $mensaje_acceso = "Este cuestionario aplica solo para estudiantes que terminaron el octavo semestre.";
                }
                break;
            case '2': // Oportunidad Laboral (Termino de Residencias)
                if ($estatus_actual === 'ResidenciaTerminada') {
                    $acceso_permitido = true;
                } else {
                    $mensaje_acceso = "Este cuestionario aplica solo para estudiantes que terminaron la residencia.";
                }
                break;
            case '3': // Satisfacción (antes de titulación)
                if ($estatus_actual === 'PendienteTitulacion') { 
                    $acceso_permitido = true;
                } else {
                    $mensaje_acceso = "Este cuestionario aplica solo para egresados próximos a titulación.";
                }
                break;
            default:
                send_response(["ok" => false, "mensaje" => "Tipo de cuestionario no válido."], 400);
                return;
        }
        $stmt->close();

        // PASO 3: Respuesta final
        if ($acceso_permitido) {
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

    } catch (Exception $e) {
        send_response(["ok" => false, "mensaje" => "Error del servidor: " . $e->getMessage()], 500);
    }
}