<?php

require_once "../../config/database.php";
include "../../helpers/php/functions.php";
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
    case "crearVacante":
        crear_vacante($db, $datos_post);
        break;

    case "editarVacante":
        editar_vacante($db, $datos_post);
        break;

    case "eliminarVacante":
        eliminar_vacante($db, $datos_post["data"]["idVacante"]);
        break;

    case "listarVacantes":
        listar_vacantes($db, $datos_get["idEmpresa"]);
        break;

    case "obtenerVacante":
        obtener_vacante($db, $datos_get);
        break;

    case "buscarVacantes":
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $termino_busqueda = $db->escape_string($_GET["terminoBusqueda"]);

            /**
             * Valida si $termino_busqueda es un string vacio. Si no lo es,
             * hace la busqueda en la BD, de lo contrario, lista todos los convenios.
             */
            if ($termino_busqueda) {
                buscar_vacantes($db, $datos_get["idEmpresa"], $termino_busqueda);
            } else {
                listar_vacantes($db, $datos_get["idEmpresa"]);
            }
        }
        break;

    case "listarPostulaciones":
        listar_postulaciones($db, $datos_get["idVacante"]);
        break;

    case "listarCarreras":
        listar_carreras($db);
        break;

    case "editarEstatus":
        editar_estatus_vacante($db, $_POST["idPostulacion"], $_POST["idEstatus"]);
        break;

    case "validarRegistro":
        validar_registro($db, $_GET["idEmpresa"]);
        break;

    default:
        break;
}

/**
 * Lista las carreras para llenar el `<select>` en el HTML.
 * Envía una respuesta, en donde cada objeto de la carrera tiene las keys `id_carrera`
 * y `nombre`.
 *
 * @param mysqli $db Conexión a la base de datos.
 */
function listar_carreras($db)
{
    try {
        $sql = "SELECT * FROM carrera";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            while ($carrera = $resultado->fetch_assoc()) {
                $carreras[] = [
                    "id_carrera" => $carrera["IdCarrera"],
                    "nombre" => $carrera["Nombre"]
                ];
            }

            $response = [
                "ok" => true,
                "data" => $carreras
            ];
            send_response($response);
        } else {
            $response = [
                "ok" => true,
                "mensaje" => "No hay carreras registradas"
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
 * Registra una nueva vacante en la base de datos.
 *
 * @param mysqli $db   Conexión a la base de datos.
 * @param array  $data Datos introducidos por el usuario (arreglo `$_POST` sanitizado).
 */
function crear_vacante($db, $data)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $info_vacante = $data["data"];

        try {
            /**
             * Obtener el ID de la vacante
             */
            $id_vacante = get_nextid("IdVacante", "vacante");
            if ($id_vacante === -1) throw new Exception("Ha ocurrido un error");

            $sql = "INSERT INTO vacante (
                        IdVacante, 
                        Nombre, 
                        InformacionVacante, 
                        FechaSolicitud, 
                        IdCarrera, 
                        Habilidades, 
                        Funciones, 
                        Genero, 
                        NumeroVacantes,
                        Experiencia,
                        DiasHorarioLaboral,
                        Prestaciones,
                        OfertaEconomica,
                        CondicionLaboral,
                        Capacitacion,
                        PrestacionesLey,
                        IdEmpresa,
                        Borrado
                    ) VALUES (
                        $id_vacante, 
                        '{$info_vacante["Nombre"]}', 
                        '{$info_vacante["InformacionVacante"]}', 
                        CURRENT_DATE, 
                        '{$info_vacante["IdCarrera"]}', 
                        '{$info_vacante["Habilidades"]}', 
                        '{$info_vacante["Funciones"]}',
                        '{$info_vacante["Genero"]}', 
                        '{$info_vacante["NumeroVacantes"]}',
                        '{$info_vacante["Experiencia"]}',
                        '{$info_vacante["DiasHorarioLaboral"]}',
                        '{$info_vacante["Prestaciones"]}',
                        '{$info_vacante["OfertaEconomica"]}',
                        '{$info_vacante["CondicionLaboral"]}',
                        '{$info_vacante["Capacitacion"]}',
                        '{$info_vacante["PrestacionesLey"]}',
                        '{$info_vacante["IdEmpresa"]}',
                        '1'
                    )";
            $resultado = $db->query($sql);

            if ($resultado) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Vacante registrada correctamente"
                ];

                send_response($response, 201);
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
 * Edita una vacante dada por su ID.
 *
 * @param mysqli $db         Conexión a la base de datos.
 * @param array  $datos_post Datos introducidos por el usuario (arreglo `$_POST` sanitizado).
 */
function editar_vacante($db, $datos_post)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        try {
            $sql = "UPDATE vacante
                    SET
                        Nombre = '{$datos_post["data"]["Nombre"]}',
                        InformacionVacante = '{$datos_post["data"]["InformacionVacante"]}',
                        IdCarrera = '{$datos_post["data"]["IdCarrera"]}',
                        Habilidades = '{$datos_post["data"]["Habilidades"]}',
                        Funciones = '{$datos_post["data"]["Funciones"]}',
                        Genero = '{$datos_post["data"]["Genero"]}',
                        NumeroVacantes = '{$datos_post["data"]["NumeroVacantes"]}',
                        Experiencia = '{$datos_post["data"]["Experiencia"]}',
                        DiasHorarioLaboral = '{$datos_post["data"]["DiasHorarioLaboral"]}',
                        Prestaciones = '{$datos_post["data"]["Prestaciones"]}',
                        OfertaEconomica = '{$datos_post["data"]["OfertaEconomica"]}',
                        CondicionLaboral = '{$datos_post["data"]["CondicionLaboral"]}',
                        Capacitacion = '{$datos_post["data"]["Capacitacion"]}',
                        PrestacionesLey = '{$datos_post["data"]["PrestacionesLey"]}'
                    WHERE IdVacante = {$datos_post["data"]["IdVacante"]}";

            $resultado = $db->query($sql);

            if ($resultado) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Vacante actualizada correctamente"
                ];

                send_response($response);
            } else {
                throw new Exception("Ha ocurrido un error: " . $db->error);
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
 * Actualiza el campo `Borrado` a 0 para que este registro ya no se muestre.
 *
 * @param mysqli $db            Conexión a la base de datos.
 * @param int    $id_vacante    ID de la vacante a eliminar.
 */
function eliminar_vacante($db, $id_vacante)
{
    $id_vacante = filter_var($id_vacante, FILTER_VALIDATE_INT);

    try {
        $sql = "UPDATE vacante SET Borrado = 0 WHERE IdVacante = $id_vacante";
        $resultado = $db->query($sql);

        if ($resultado && $db->affected_rows > 0) {
            $response = [
                "ok" => true,
                "mensaje" => "Vacante eliminada"
            ];
            send_response($response, 200);
        } else {
            throw new Exception("Ha ocurrido un error");
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
 * Lista las vacantes dadas de alta tomando como referencia el ID de la empresa.
 *
 * @param mysqli $db            Conexión a la base de datos.
 * @param int    $id_empresa    ID de la empresa a la cual pertenecen las vacantes que deseamos ver.
 */
function listar_vacantes($db, $id_empresa)
{
    $id_empresa = filter_var($id_empresa, FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT
                    vacante.*,
                    carrera.Nombre AS NombreCarrera,
                    (SELECT COUNT(*) AS total_postulaciones
                    FROM postulacion
                    WHERE IdVacante = vacante.IdVacante) AS NumPostulaciones
                FROM vacante
                INNER JOIN
                    carrera
                ON
                    vacante.IdCarrera = carrera.IdCarrera
                WHERE vacante.IdEmpresa = {$id_empresa} 
                AND vacante.Borrado = 1";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            while ($vacante = $resultado->fetch_assoc()) {
                $vacantes[] = [
                    "id_vacante" => $vacante["IdVacante"],
                    "nombre" => $vacante["Nombre"],
                    "carrera" => $vacante["NombreCarrera"],
                    "fecha_publicacion" => $vacante["FechaSolicitud"],
                    "num_postulaciones" => $vacante["NumPostulaciones"]
                ];
            }

            $response = [
                "ok" => true,
                "data" => $vacantes
            ];
            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "No hay vacantes registradas"
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
 * Obtiene la información de una vacante por su ID de vacante y su ID de empresa..
 *
 * @param mysqli $db        Conexión a la base de datos.
 * @param array  $datos_get Arreglo con los datos de `GET`, que contenga la key `idVacante` y `idEmpresa`.
 */
function obtener_vacante($db, $datos_get)
{
    /**
     * Validar que `idVacante` y `idEmpresa` esté definido y no sea un string vacío.
     */
    if (
        !isset($datos_get["idVacante"]) || empty($datos_get["idVacante"])
        || !isset($datos_get["idEmpresa"]) || empty($datos_get["idEmpresa"])
    ) {
        $response = [
            "ok" => false,
            "mensaje" => "Ingresa el ID de la vacante y de la empresa"
        ];
        send_response($response, 400);
        exit;
    }

    // Obtener el ID de la vacante a consultar
    $id_vacante = filter_var($datos_get["idVacante"], FILTER_VALIDATE_INT);
    $id_empresa = filter_var($datos_get["idEmpresa"], FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT
                    v.*,
                    c.Nombre AS NombreCarrera
                FROM vacante v
                JOIN carrera c
                ON v.IdCarrera = c.IdCarrera
                WHERE v.IdVacante = {$id_vacante} 
                AND v.IdEmpresa = {$id_empresa} AND v.Borrado = 1";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $response = [
                "ok" => true,
                "data" => $resultado->fetch_assoc()
            ];

            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "No se encontró ninguna vacante con el ID {$id_vacante} o no tienes acceso a esta vacante"
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
 * Busca vacantes publicada por cierta empresa dada por su ID.
 * La búsqueda puede realizarse por el nombre de la vacante o nombre de
 * la carrera a la cual está dirigida.
 *
 * @param mysqli $db                Conexión a la base de datos.
 * @param int    $id_empresa        ID de la empresa.
 * @param string $termino_busqueda  Término de búsqueda ingresado por el usuario.
 */
function buscar_vacantes($db, $id_empresa, $termino_busqueda)
{
    $id_empresa = filter_var($id_empresa, FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT
                    v.*,
                    c.Nombre AS NombreCarrera
                FROM vacante v
                INNER JOIN
                    carrera c
                ON
                    v.IdCarrera = c.IdCarrera
                WHERE 
                    v.IdEmpresa = {$id_empresa}
                AND (v.Nombre LIKE '%$termino_busqueda%' OR c.Nombre LIKE '%$termino_busqueda%')
                AND v.Borrado = 1";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            while ($vacante = $resultado->fetch_assoc()) {
                $vacantes[] = [
                    "id_vacante" => $vacante["IdVacante"],
                    "nombre" => $vacante["Nombre"],
                    "carrera" => $vacante["NombreCarrera"],
                    "fecha_publicacion" => $vacante["FechaSolicitud"]
                ];
            }

            $response = [
                "ok" => true,
                "data" => $vacantes
            ];

            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "No se han encontrado resultados con el término de búsqueda ingresado"
            ];

            send_response($response, 404);
        }
    } catch (Exception $e) {
        $response = [
            "ok"      => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}

/**
 * Lista las postulaciones existentes para una vacantante dada por su ID.
 *
 * @param mysqli $db         Conexión a la base de datos.
 * @param int    $id_vacante ID de la vacante para consultar.
 */
function listar_postulaciones($db, $id_vacante)
{
    $vacante = filter_var($id_vacante, FILTER_VALIDATE_INT);

    try {
        $sqlPostulaciones = "SELECT
                    p.IdPostulacion,
                    e.Nombre AS NombreEstudiante,
                    e.PrimerApellido,
                    e.SegundoApellido,
                    p.FechaPostulacion,
                    p.cv,
                    p.estatus,
                    ep.Nombre AS EstatusNombre
                FROM 
                    postulacion p
                JOIN
	                vacante v
                ON
	                p.IdVacante = v.IdVacante
                JOIN 
                    estatus_postulacion ep
                ON
                    p.estatus = ep.IdEstatus
                JOIN 
                    estudiante e
                ON
                    p.IdEstudiante = e.IdEstudiante
                WHERE p.IdVacante = $vacante";

        $resultadoPostulaciones = $db->query($sqlPostulaciones);

        $sqlVacante = "SELECT 
                            v.IdVacante,
                            v.Nombre AS NombreVacante,
                            v.FechaSolicitud AS FechaPublicacion,
                            c.Nombre AS NombreCarrera 
                        FROM vacante v 
                        JOIN 
                            carrera c
                        ON
                            v.IdCarrera = c.IdCarrera
                        WHERE v.IdVacante = $vacante";
        $resultadoVacante = $db->query($sqlVacante)->fetch_assoc();

        $vacante = [
            "IdVacante"         => $resultadoVacante["IdVacante"],
            "Nombre"            => $resultadoVacante["NombreVacante"],
            "FechaPublicacion"  => $resultadoVacante["FechaPublicacion"],
            "NombreCarrera"     => $resultadoVacante["NombreCarrera"]
        ];

        if ($resultadoVacante) {
            if ($resultadoPostulaciones->num_rows > 0) {
                $postulaciones = [];

                while ($postulacion = $resultadoPostulaciones->fetch_assoc()) {
                    $postulaciones[] = [
                        "IdPostulacion"     => $postulacion["IdPostulacion"],
                        "NombreEstudiante"  => $postulacion["NombreEstudiante"] . " " . $postulacion["PrimerApellido"] . " " . $postulacion["SegundoApellido"],
                        "FechaPostulacion"  => $postulacion["FechaPostulacion"],
                        "cv"                => "uploads/cv/" . $postulacion["cv"],
                        "estatus"           => $postulacion["estatus"],
                        "EstatusNombre"     => $postulacion["EstatusNombre"]
                    ];
                }

                $response = [
                    "ok" => true,
                    "data" => [
                        "vacante" => $vacante,
                        "postulaciones" => $postulaciones
                    ]
                ];

                send_response($response);
            } else {
                $response = [
                    "ok" => false,
                    "data" => [
                        "vacante" => $vacante,
                        "mensaje" => "Aún no hay postulantes para esta vacante"
                    ]
                ];

                send_response($response, 404);
            }
        }
    } catch (Exception $e) {
        $response = [
            "ok"      => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}

/**
 * Edita el estatus de una postulación.
 *
 * @param mysqli $db             Conexión a la base de datos.
 * @param int    $id_postulacion ID de la postulacion.
 * @param int    $id_estatus     Número de estatus: `2` para 'En espera' o `3` para 'Rechazado'.
 */
function editar_estatus_vacante($db, $id_postulacion, $id_estatus)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $postulacion = filter_var($id_postulacion, FILTER_VALIDATE_INT);
        $estatus = filter_var($id_estatus, FILTER_VALIDATE_INT);

        try {
            $sql = "UPDATE postulacion SET estatus = $estatus WHERE IdPostulacion = $postulacion";
            $result = $db->query($sql);

            if ($result) {
                $respuesta = [
                    "ok" => true,
                    "mensaje" => "Estatus actualizado correctamente"
                ];

                send_response($respuesta);
            } else {
                throw new Exception('Ha ocurrido un error al actualizar el estatus');
            }
        } catch (Exception $e) {
            $response = [
                "ok"      => false,
                "mensaje" => $e->getMessage()
            ];

            send_response($response, 500);
        }
    }
}

function validar_registro($db, $id_empresa)
{
    $empresa = filter_var($id_empresa, FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT Registro FROM empresa WHERE IdEmpresa = $empresa";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $response = [
                "ok" => true,
                "validado" => $resultado->fetch_assoc()["Registro"] == 1 ? true : false
            ];

            send_response($response);
        }
    } catch (Exception $e) {
        $response = [
            "ok"      => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}
