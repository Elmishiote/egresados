<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/helpers/functions.php';

include      "../../helpers/php/functions.php";
// require_once "../../Mailer.php";

$db = conectar_db();
if (!$db) exit; // Verificar que `$db` no sea `false`

/**
 * El siguiente código obtiene los parámetros en caso de enviarse un datos en JSON (funciona
 * para probar con Postman).
 */
$jsonData = [];
$content_type = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : "";
if ($content_type === "application/json") {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
}

/**
 * Identifica si estamos enviando datos en JSON (con Postman, por ej.) o si los estamos
 * recibiendo directamente en el arreglo `$_POST` y asigna los valores a la variable.
 */
$datos_post = count($jsonData) > 0 ? sanitize_array($jsonData) : sanitize_array($_POST);
// Sanitizar datos de `$_GET`
$datos_get = sanitize_array($_GET);

$opcion = $_SERVER["REQUEST_METHOD"] === "POST" ? $datos_post["case"] : $_GET["case"];

switch ($opcion) {
    case "vacantesEstudiante":
        if (!isset($datos_get["idCarrera"]) || empty($datos_get["idCarrera"])) {
            $response = [
                "ok" => false,
                "mensaje" => "Ingresa un ID de carrera válido"
            ];

            send_response($response, 400);
            return;
        }
        vacantes_estudiante($db, $datos_get["idCarrera"]);
        break;

    case "buscarVacantes":
        if (!isset($datos_get["idCarrera"]) || empty($datos_get["idCarrera"])) {
            $response = [
                "ok" => false,
                "mensaje" => "Ingresa un ID de carrera válido"
            ];

            send_response($response, 400);
            return;
        }

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $termino_busqueda = $db->escape_string($_GET["terminoBusqueda"]);

            /**
             * Valida si $termino_busqueda es un string vacio. Si no lo es,
             * hace la busqueda en la BD, de lo contrario, lista todos los convenios.
             */
            if ($termino_busqueda) {
                buscar_vacantes_estudiante($db, $datos_get["idCarrera"], $termino_busqueda);
            } else {
                vacantes_estudiante($db, $datos_get["idCarrera"]);
            }
        }
        break;

    case "obtenerVacante":
        if (
            !isset($datos_get["idVacante"]) || empty($datos_get["idVacante"]) ||
            !isset($datos_get["idCarrera"]) || empty($datos_get["idCarrera"])
        ) {
            $response = [
                "ok" => false,
                "mensaje" => "Ingresa un ID de vacante y carrera válido"
            ];

            send_response($response, 400);
            return;
        }
        get_vacante($db, $datos_get["idVacante"], $datos_get["idCarrera"]);
        break;

    case "validarPostulacion":
        validar_postulacion_response($db, $datos_get);
        break;

    case "postulacion":
        postulacion($db, $datos_post);
        break;

    case "misPostulaciones":
        if (!isset($datos_get["idEstudiante"]) || empty($datos_get["idEstudiante"])) {
            $response = [
                "ok" => false,
                "mensaje" => "Ingresa un ID de estudiante"
            ];

            send_response($response, 400);
            return;
        }
        mis_postulaciones($db, $datos_get["idEstudiante"]);
        break;

    default:
        break;
}

/**
 * Lista las vacantes disponibles para la carrera especificada por su ID.
 *
 * @param mysqli $db           Conexión a la base de datos.
 * @param int    $id_carrera   ID de la carrera a la que pertenece el estudiante.
 */
function vacantes_estudiante($db, $id_carrera)
{
    $carrera = filter_var($id_carrera, FILTER_VALIDATE_INT);

    if (!$carrera) {
        $response = [
            "ok" => false,
            "mensaje" => "Ingresa un ID de carrera válido"
        ];

        send_response($response, 400);
    }

    try {
        $sql = "SELECT
                    v.IdVacante,
                    v.Nombre AS NombreVacante,
                    e.Nombre AS NombreEmpresa,
                    v.InformacionVacante
                FROM
                    vacante v
                    INNER JOIN carrera c ON v.IdCarrera = c.IdCarrera
                    INNER JOIN empresa e ON v.IdEmpresa = e.IdEmpresa
                WHERE
                    v.IdCarrera = $carrera AND
                    v.Borrado = 1
                LIMIT 10";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $vacantes = [];

            while ($vacante = $resultado->fetch_assoc()) {
                $vacantes[] = $vacante;
            }

            $response = [
                "ok" => true,
                "data" => $vacantes
            ];

            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "Aún no hay vacantes disponibles para tu carrera"
            ];

            send_response($response, 404);
        }
    } catch (Exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}

/**
 * Busca vacantes publicadas para mostrar en la pantalla del estudiante.
 *
 * @param mysqli $db                Conexión a la base de datos.
 * @param int $id_carrera           ID de la carrera a la que pertenece el estudiante.
 * @param string $termino_busqueda  Término de búsqueda.
 */
function buscar_vacantes_estudiante($db, $id_carrera, $termino_busqueda)
{
    $carrera = filter_var($id_carrera, FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT
                    v.IdVacante,
                    v.Nombre AS NombreVacante,
                    e.Nombre AS NombreEmpresa,
                    v.InformacionVacante
                FROM
                    vacante v
                    INNER JOIN carrera c ON v.IdCarrera = c.IdCarrera
                    INNER JOIN empresa e ON v.IdEmpresa = e.IdEmpresa
                WHERE
                    v.IdCarrera = $carrera
                AND
                    (v.Nombre LIKE '%$termino_busqueda%'
                        OR e.Nombre LIKE '%$termino_busqueda%'
                        OR v.InformacionVacante LIKE '%$termino_busqueda%'
                        OR v.Habilidades LIKE '%$termino_busqueda%')
                AND
                    v.Borrado = 1
                LIMIT 10";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $vacantes = [];

            while ($vacante = $resultado->fetch_assoc()) {
                $vacantes[] = $vacante;
            }

            $response = [
                "ok" => true,
                "data" => $vacantes
            ];

            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "No hay vacantes que coincidan con el término de búsqueda ingresado"
            ];

            send_response($response, 404);
        }
    } catch (Exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}

/**
 * Obtiene la información de una vacante dada por su ID.
 *
 * @param mysqli $db            Conexión a la base de datos.
 * @param int    $id_vacante    ID de la vacante.
 * @param int    $id_carrera    ID de la carrera a la que pertenece el estudiante.
 */
function get_vacante($db, $id_vacante, $id_carrera)
{
    $vacante = filter_var($id_vacante, FILTER_VALIDATE_INT);
    $carrera = filter_var($id_carrera, FILTER_VALIDATE_INT);

    if (!$vacante || !$carrera) {
        $response = [
            "ok" => false,
            "mensaje" => "Ingresa una vacante válida"
        ];
        send_response($response, 400);
        exit;
    }

    try {
        $sql = "SELECT
                    v.*,
                    c.Nombre AS NombreCarrera,
                    e.Nombre AS NombreEmpresa
                FROM
                    vacante v
                    INNER JOIN carrera c ON v.IdCarrera = c.IdCarrera
                    INNER JOIN empresa e ON v.IdEmpresa = e.IdEmpresa
                WHERE
                    v.IdVacante = $vacante AND
                    v.Borrado = 1";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $vacante_db = $resultado->fetch_assoc();

            /**
             * Valida si el estudiante tiene acceso a la vacante, verificando si el
             * ID de la carrera recibido en la función es igual al del registro en la base
             * de datos.
             */
            if (intval($vacante_db["IdCarrera"]) !== $carrera) {
                $response = [
                    "ok" => false,
                    "mensaje" => "Esta vacante no pertenece a tu carrera."
                ];
                send_response($response, 403);
                exit;
            }

            $response = [
                "ok" => true,
                "data" => $vacante_db
            ];

            send_response($response, 200);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "Esta vacante no existe"
            ];
            send_response($response, 404);
        }
    } catch (Exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}

/**
 * Guarda el CV del estudiante en `uploads/cv`. Si ocurrió un error al subir
 * el archivo, retorna `false`, de lo contrario, retorna un `string` con el
 * nombre del archivo.
 *
 * @return false|string
 */
function guardar_cv()
{
    // Carpeta donde se guardarán los CVs
    $targetDir = "../../uploads/cv/";

    /**
     * Verificar si el path de `$targetDir` existe. Si no existe, se
     * crea esa carpeta.
     */
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Obtener información del archivo
    $infoArchivo = pathinfo($_FILES["cv"]["name"]);
    // Obtener la extensión del archivo
    $extension = strtolower($infoArchivo["extension"]);

    // Generar un nombre unico para el archivo
    $newFileName = md5(uniqid(rand(), true)) . "." . $extension;

    // Crear el path del archivo completo
    $targetFile = $targetDir . $newFileName;
    // Obtener extensión
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validar que sea PDF
    if ($fileType != "pdf") {
        return false;
    }

    // Mover el archivo a la carpeta `uploads/cv`
    $archivo_subido = move_uploaded_file($_FILES["cv"]["tmp_name"], $targetFile);

    // Si el archivo no se subió, retorna `false`
    if (!$archivo_subido) return false;

    // Retornar el nombre del archivo
    return $newFileName;
}

/**
 * Crea un nuevo registro en la tabla `postulacion`.
 *
 * @param mysqli $db         Conexión a la base de datos.
 * @param array  $datos_post Array asociativo con las keys `IdEstudiante` y `IdVacante`.
 */
function postulacion($db, $datos_post)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_estudiante = filter_var($datos_post["IdEstudiante"], FILTER_VALIDATE_INT);
        $id_vacante = filter_var($datos_post["IdVacante"], FILTER_VALIDATE_INT);

        if (!$id_estudiante || !$id_vacante) {
            $response = [
                "ok" => false,
                "mensaje" => "Proporciona un ID de estudiante y de vacante válidos"
            ];

            send_response($response, 400);
        }

        /**
         * Validar si el estudiante ya se postuló a la vacante mediante la función
         * `validar_postulacion`. Si devuelve un `string`, significa que ya está postulado.
         */
        $postulacion = validar_postulacion($db, $datos_post);
        if (is_string($postulacion)) {
            $response = [
                "ok" => false,
                "mensaje" => "Ya te has postulado a esta vacante"
            ];
            send_response($response, 400);
            exit;
        }

        try {
            // Obtener el último ID de postulaciones
            $next_id = get_nextid("IdPostulacion", "postulacion");
            if ($next_id === -1) throw new Exception("Ha ocurrido un error");

            // Subir CV
            $cv = guardar_cv();
            if (!$cv) throw new Exception("Ha ocurrido un error al subir tu CV. Verifica que esté en formato PDF");

            $sql = "INSERT INTO postulacion 
                    (IdPostulacion, IdEstudiante, IdVacante, FechaPostulacion, cv, estatus, Borrado) 
                    VALUES ($next_id, $id_estudiante, $id_vacante, CURRENT_DATE, '$cv', 1, 1)";

            // Insertar registro
            $resultado = $db->query($sql);

            // Obtener la fecha actual en formato YYYY-MM-DD
            $fechaActual = date('Y-m-d');

            if ($resultado) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Tu postulación ha sido enviada",
                    "fecha_postulacion" => $fechaActual
                ];

                send_response($response, 201);

                /**
                 * Enviar correos electrónicos al estudiante y al empleador.
                 */
                enviar_email_estudiante($id_estudiante, $id_vacante);
                enviar_email_empleador($id_vacante);
            }
        } catch (Exception $e) {
            $response = [
                "ok" => false,
                "mensaje" => $e->getMessage()
            ];

            send_response($response, 500);
        }
    }
}

/**
 * Valida si un estudiante ya está postulado a una vacante.
 * Función para uso interno.
 *
 * @param  mysqli $db    Conexión a la base de datos.
 * @param  array  $arr   Array asociativo con las keys `IdEstudiante` y `IdVacante`.
 * @return false|string `false` si la postulación no existe. Si sí existe, retorna la fecha
 *                              de postulación.
 */
function validar_postulacion($db, $arr)
{
    $id_estudiante = filter_var($arr["IdEstudiante"], FILTER_VALIDATE_INT);
    $id_vacante = filter_var($arr["IdVacante"], FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT * FROM postulacion WHERE IdEstudiante = $id_estudiante AND IdVacante = $id_vacante";
        $resultado = $db->query($sql);

        /**
         * Validar si la postulación no existe. En ese caso, retornar `false`.
         */
        if (!$resultado->num_rows > 0) return false;

        /**
         * Si la postulación sí existe, retorna la fecha de postulación.
         */
        $postulacion = $resultado->fetch_assoc();
        $fecha_postulacion = $postulacion["FechaPostulacion"];

        return $fecha_postulacion;
    } catch (Exception $e) {
        /**
         * En caso de existir algún error, la función retornará `false`.
         */
        return false;
    }
}

/**
 * Valida si un estudiante ya está postulado a una vacante y envía una respuesta.
 *
 * @param  mysqli $db  Conexión a la base de datos.
 * @param  array  $arr Array asociativo con las keys `IdEstudiante` y `IdVacante`.
 */
function validar_postulacion_response($db, $arr)
{
    $validar = validar_postulacion($db, $arr);

    if (!$validar) {
        $response = [
            "ok" => true,
            "postulacion" => false
        ];
        send_response($response);
        exit;
    }

    $response = [
        "ok" => true,
        "postulacion" => true,
        "fecha_postulacion" => $validar
    ];
    send_response($response);
}

/**
 * Ver las postulaciones del estudiante para la view `mis-postulaciones.php`.
 *
 * @param mysqli $db            Conexión a la base de datos.
 * @param int $id_estudiante    ID del estudiante.
 */
function mis_postulaciones($db, $id_estudiante)
{
    $estudiante = filter_var($id_estudiante, FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT
                    p.IdPostulacion,
                    v.IdVacante,
                    v.Nombre AS NombreVacante,
                    e.Nombre AS NombreEmpresa,
                    p.FechaPostulacion,
                    p.estatus,
                    ep.Nombre AS NombreEstatus
                FROM
                    postulacion p
                JOIN
                    vacante v
                ON p.IdVacante = v.IdVacante
                JOIN
                    empresa e
                ON
                    v.IdEmpresa = e.IdEmpresa
                JOIN
                    estatus_postulacion ep
                ON
                    p.estatus = ep.IdEstatus
                WHERE
                    p.IdEstudiante = $estudiante";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $vacantes = [];

            while ($vacante = $resultado->fetch_assoc()) {
                $vacantes[] = $vacante;
            }

            $response = [
                "ok" => true,
                "data" => $vacantes
            ];

            send_response($response, 200);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "Aún no te has postulado a ninguna vacante"
            ];
            send_response($response, 404);
        }
    } catch (Exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}
