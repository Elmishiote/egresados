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

$opcion = $_SERVER["REQUEST_METHOD"] === "POST" ? $datos_post["case"] : $_GET["case"];

switch ($opcion) {
    case "registrarConvenio":
        registrar_convenio($db, $datos_post);
        break;

    case "editarConvenio":
        editar_convenio($db, $datos_post);
        break;

    case "eliminarConvenio":
        eliminarConvenioPorId($db, $datos_post);
        break;

    case "listarConvenios":
        listar_convenios($db);
        break;

    case "obtenerConvenio":
        obtener_convenio($db);
        break;

    case "buscarConvenios":
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $termino_busqueda = $db->escape_string($_GET["terminoBusqueda"]);

            /**
             * Valida si $termino_busqueda es un string vacio. Si no lo es,
             * hace la busqueda en la BD, de lo contrario, lista todos los convenios.
             */
            if ($termino_busqueda) {
                buscar_convenios($db, $termino_busqueda);
            } else {
                listar_convenios($db);
            }
        }
        break;

    case "listarEmpresas":
        listar_empresas($db);
        break;

    default:
        break;
}

/**
 * Lista todos los convenios.
 * @param mysqli $db Conexión a la base de datos.
 */
function listar_convenios($db)
{
    try {
        $sql = "SELECT
                    convenio.IdConvenio,
                    convenio.NumeroConvenio,
                    empresa.IdEmpresa,
                    empresa.Nombre AS nombreEmpresa
                FROM convenio
                JOIN empresa
                ON convenio.IdEmpresa = empresa.IdEmpresa
                WHERE convenio.Borrado = 1";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            while ($convenio = $resultado->fetch_assoc()) {
                $convenios[] = $convenio;
            }

            $response = [
                "ok" => true,
                "data" => $convenios
            ];

            http_response_code(200);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "No hay convenios registrados"
            ];

            http_response_code(404);
        }
        echo json_encode($response);
    } catch (Exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        http_response_code(500);
        echo json_encode($response);
    }
}

/**
 * Buscar convenios por número de convenio o empresa.
 *
 * @param mysqli $db                Conexión a la base de datos.
 * @param string $termino_busqueda  Número de convenio o nombre de la empresa.
 */
function buscar_convenios($db, $termino_busqueda)
{
    $sql = "SELECT c.IdConvenio, c.NumeroConvenio, e.Nombre AS nombreEmpresa
            FROM convenio AS c
            LEFT JOIN empresa AS e
            ON c.IdEmpresa = e.IdEmpresa
            WHERE (c.NumeroConvenio LIKE ? OR e.Nombre LIKE ?)
            AND c.Borrado = 1";

    try {
        $termino_busqueda_like = "%{$termino_busqueda}%";

        // Preparar sentencia SQL y enlazar parámetros
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $termino_busqueda_like, $termino_busqueda_like);

        // Ejecutar consulta
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $resultados_busqueda = [];

            while ($convenio = $result->fetch_assoc()) {
                $resultados_busqueda[] = $convenio;
            }

            $response = [
                "ok" => true,
                "data" => $resultados_busqueda
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
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}

/**
 * Obtener un convenio dado por su ID.
 * @param mysqli $db Conexión a la base de datos.
 */
function obtener_convenio($db)
{
    // Sanitizar entrada
    $id_convenio = filter_var($_GET["idConvenio"], FILTER_SANITIZE_NUMBER_INT);

    try {
        $sql = "SELECT * FROM convenio WHERE IdConvenio = $id_convenio";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $response = [
                "ok" => true,
                "data" => $resultado->fetch_assoc()
            ];

            http_response_code(200);
            echo json_encode($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "Convenio no encontrado"
            ];

            http_response_code(404);
            echo json_encode($response);
        }
    } catch (Exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        http_response_code(500);
        echo json_encode($response);
    }
}

/**
 * Lista todas las empresas para llenar el select del formulario.
 * @param mysqli $db Conexión mysqli a la base de datos.
 */
function listar_empresas($db)
{
    try {
        $sql = "SELECT * FROM empresa";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            while ($empresa = $resultado->fetch_assoc()) {
                $empresas[] = [
                    "idEmpresa" => $empresa["IdEmpresa"],
                    "nombre" => $empresa["Nombre"]
                ];
            }

            $response = [
                "ok" => true,
                "data" => $empresas
            ];

            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "No hay empresas registradas"
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
 * Registra un nuevo convenio en la base de datos.
 *
 * @param mysqli $db    Conexión a la base de datos.
 * @param array  $data  Arreglo con los datos ingresados por el usuario.
 */
function registrar_convenio($db, $data)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $num_convenio = filter_var($data["data"]["numConvenio"], FILTER_VALIDATE_INT);
        $id_empresa = filter_var($data["data"]["idEmpresa"], FILTER_VALIDATE_INT);

        try {
            /**
             * Verificar que el ID de la empresa de `$id_empresa` esté registrado.
             */
            $query_empresa_exist = "SELECT COUNT(*) AS count FROM empresa WHERE IdEmpresa = ?";
            $stmt_empresa_exist = $db->prepare($query_empresa_exist);
            $stmt_empresa_exist->bind_param("s", $id_empresa);
            $stmt_empresa_exist->execute();
            $result_empresa_exist = $stmt_empresa_exist->get_result();
            $row_empresa_exist = $result_empresa_exist->fetch_assoc();

            if ($row_empresa_exist["count"] === 0) {
                $response = [
                    "ok" => false,
                    "mensaje" => "El ID de la empresa no existe en la base de datos"
                ];

                send_response($response, 404);
                $stmt_empresa_exist->close();
                exit;
            }

            /**
             * Obtener último ID registrado.
             */
            $query = "SELECT MAX(IdConvenio) AS lastId FROM convenio";
            $resultado = mysqli_fetch_assoc(mysqli_query($db, $query));
            $nextId = intval($resultado['lastId']) + 1;

            $sql_insert = "INSERT INTO convenio (IdConvenio, NumeroConvenio, IdEmpresa, Borrado) VALUES (?, ?, ?, 1)";
            $stmt = $db->prepare($sql_insert);
            $stmt->bind_param("sss", $nextId, $num_convenio, $id_empresa);

            // Ejecutar la consulta.
            $result = $stmt->execute();

            if ($result && $stmt->affected_rows > 0) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Convenio registrado"
                ];

                send_response($response);
            } else {
                throw new Exception("Error al insertar el convenio");
            }

            $stmt->close();
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
 * Edita un nuevo convenio en la base de datos dado por su ID.
 *
 * @param mysqli $db                Conexión a la base de datos.
 * @param array  $datos_editados    Arreglo con los datos ingresados por el usuario.
 */
function editar_convenio($db, $datos_editados)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_convenio = filter_var($datos_editados["data"]["idConvenio"], FILTER_VALIDATE_INT);
        $num_convenio = filter_var($datos_editados["data"]["numConvenio"], FILTER_VALIDATE_INT);
        $id_empresa = filter_var($datos_editados["data"]["idEmpresa"], FILTER_VALIDATE_INT);

        $query = "UPDATE convenio 
                    SET NumeroConvenio = ?, IdEmpresa = ? 
                    WHERE IdConvenio = ?";

        try {

            $stmt = $db->prepare($query);
            $stmt->bind_param("iii", $num_convenio, $id_empresa, $id_convenio);

            // Ejecutar la consulta.
            $result = $stmt->execute();

            if ($result) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Convenio actualizado correctamente"
                ];

                send_response($response);
            } else {
                throw new Exception("Error al actualizar el convenio");
            }

            $stmt->close();
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
 * Elimina un convenio dado por su ID.
 * No elmina el registro de la base de datos realmente, sino que edita la
 * columna `Borrado` colocando el valor `0` en el registro dado.
 *
 * @param mysqli $db    Conexión a la base de datos.
 * @param array  $data  Arreglo con los datos ingresados por el usuario.
 */
function eliminarConvenioPorId($db, $data)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_convenio = filter_var($data["data"]["idConvenio"], FILTER_VALIDATE_INT);

        try {
            $sql = "UPDATE convenio SET Borrado = 0 WHERE IdConvenio = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $id_convenio);

            $result = $stmt->execute();

            if ($result && $stmt->affected_rows > 0) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Convenio desactivado correctamente"
                ];

                send_response($response);
            } else {
                throw new Exception("No se pudo eliminar el convenio");
            }

            $stmt->close();
        } catch (Exception $e) {
            $response = [
                "ok" => false,
                "mensaje" => $e->getMessage()
            ];

            send_response($response, 500);
        }
    }
}
