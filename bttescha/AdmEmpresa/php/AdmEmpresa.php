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
    case "registrarEmpresa":
        registrar_empresa($db, $datos_post);
        break;

    case "obtenerEmpresa":
        obtenerEmpresa($db, $_GET);
        break;

    case "editarEmpresa":
        editarEmpresa($db, $datos_post);
        break;

    case "desactivarEmpresa":
        desactivarEmpresa($db, $datos_post);
        break;

    case "listarEmpresas":
        listar_empresas($db);
        break;

    case "buscarEmpresas":
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $termino_busqueda = $db->escape_string($_GET["terminoBusqueda"]);

            /**
             * Valida si $termino_busqueda es un string vacio. Si no lo es,
             * hace la busqueda en la BD, de lo contrario, lista todos los convenios.
             */
            if ($termino_busqueda) {
                buscar_empresas($db, $termino_busqueda);
            } else {
                listar_empresas($db);
            }
        }
        break;

    case "listarColonias":
        get_colonias($_GET["colonia"]);
        break;

    case "listarMunicipios":
        get_municipios($_GET["municipio"]);
        break;

    default:
        break;
}

/**
 * Registra una nueva empresa en la base de datos.
 *
 * @param mysqli $db    Conexión a la base de datos.
 * @param array  $data  Arreglo con los datos ingresados por el usuario.
 */
function registrar_empresa($db, $data)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nombre = $data["data"]["Nombre"];
        $email = $data["data"]["Correo"];
        $razon_social = $data["data"]["RazonSocial"];
        $domicilio = $data["data"]["Domicilio"];
        $id_colonia = $data["data"]["IdColonia"];
        $id_municipio = $data["data"]["IdMunicipio"];
        $telefono = $data["data"]["TelefonoEmpresa"];
        $nombre_colonia = $data["data"]["NombreColonia"];

        try {
            /**
             * Crear una nueva colonia
             */
            if (!$id_colonia) {
                if ($nombre_colonia) {
                    $id_colonia = registrar_colonia($nombre_colonia, $id_municipio);
                    if ($id_colonia < 0) {
                        throw new Exception("No se pudo registrar la colonia");
                    };
                } else {
                    $response = [
                        "ok" => false,
                        "mensaje" => "Todos los campos son obligatorios"
                    ];

                    send_response($response, 422);
                    exit;
                }
            }

            // Verificar que no existan campos vacíos
            if ($nombre === "" || $email === "" || $razon_social === "" || $domicilio === "" || $id_colonia === "" || $id_municipio === "" || $telefono === "") {
                $response = [
                    "ok" => false,
                    "mensaje" => "Todos los campos son obligatorios"
                ];

                send_response($response, 422);
                exit;
            }

            /**
             * Obtener el último ID de la tabla `empresa`.
             */
            $next_id = get_nextid("IdEmpresa", "empresa");
            if ($next_id < 0) throw new Exception("Ha ocurrido un error");

            /**
             * Registrar la nueva empresa
             */
            $query = "INSERT INTO empresa (";
            $query .= "IdEmpresa, Nombre, Correo, RazonSocial, Domicilio, IdColonia, IdMunicipio, TelefonoEmpresa, Registro, Borrado)";
            $query .= " VALUES (";
            $query .= "?, ?, ?, ?, ?, ?, ?, ?, 0, 1";
            $query .= ")";

            $stmt = $db->prepare($query);
            $stmt->bind_param("issssiis", $next_id, $nombre, $email, $razon_social, $domicilio, $id_colonia, $id_municipio, $telefono);
            $result = $stmt->execute();

            if ($result && $stmt->affected_rows > 0) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Empresa registrada correctamente"
                ];

                send_response($response, 201);
            } else {
                throw new Exception("Ocurrió un error al registrar la empresa");
            }
        } catch (mysqli_sql_exception $e) {
            $response = [
                "ok" => false,
                "mensaje" => "Error: " . $e->getMessage()
            ];

            send_response($response, 500);
        }
    }
}

/**
 * Edita una empresa.
 *
 * @param mysqli $db    Conexión a la base de datos.
 * @param array  $post  Datos actualizados.
 */
function editarEmpresa($db, $post)
{
    $id_empresa = $post["data"]["IdEmpresa"];
    $nombre_empresa = $post["data"]["Nombre"];
    $email = $post["data"]["Correo"];
    $razon_social = $post["data"]["RazonSocial"];
    $domicilio = $post["data"]["Domicilio"];
    $telefono = $post["data"]["TelefonoEmpresa"];

    $id_colonia = $post["data"]["IdColonia"];
    $nombre_colonia = $post["data"]["NombreColonia"];
    $id_municipio = $post["data"]["IdMunicipio"];
    /**
     * Si no existe `$id_colonia` es porque no está registrada en la base de datos.
     * Valida también si existe `$nombre_colonia`, en caso de existir, reliza el registro.
     */
    if (!$id_colonia && $nombre_colonia) {
        $id_colonia = registrar_colonia($nombre_colonia, $id_municipio);
        if ($id_colonia < 0) exit;
    }

    try {
        $sql = "UPDATE empresa
                SET
                    Nombre = '$nombre_empresa',
                    Correo = '$email',
                    RazonSocial = '$razon_social',
                    Domicilio = '$domicilio',
                    IdColonia = '$id_colonia',
                    IdMunicipio = $id_municipio,
                    TelefonoEmpresa = '$telefono'
                WHERE
                    IdEmpresa = $id_empresa";

        $resultado = mysqli_query($db, $sql);

        if ($resultado) {
            $response = [
                "error" => false,
                "mensaje" => "Actualización exitosa"
            ];

            http_response_code(200);
            echo json_encode($response);
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
 * Obtiene la información de una empresa dada por su ID.
 *
 * @param mysqli $db    Conexión a la base de datos.
 * @param array  $data  Arreglo asociativo con la key `idEmpresa`, de tipo `int`.
 */
function obtenerEmpresa($db, $data)
{
    $id_empresa = filter_var($data["idEmpresa"], FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT 
                    e.IdEmpresa,
                    e.Nombre,
                    e.Correo,
                    e.RazonSocial,
                    e.Domicilio,
                    e.IdColonia,
                    cc.Nombre AS NombreColonia,
                    e.IdMunicipio,
                    cm.Nombre AS NombreMunicipio,
                    ce.Nombre AS NombreEstado,
                    e.TelefonoEmpresa
                FROM 
                    empresa e
                JOIN 
                    catcolonia cc ON e.IdColonia = cc.IdColonia
                JOIN 
                    catmunicipio cm ON e.IdMunicipio = cm.IdMunicipio
                JOIN 
                    catestado ce ON cm.IdEstado = ce.IdEstado
                WHERE 
                    e.IdEmpresa = $id_empresa AND e.Borrado = 1";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $response = [
                "ok"   => true,
                "data" => $resultado->fetch_assoc()
            ];

            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "Esta empresa no existe"
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
 * Lista todas las empresas.
 *
 * @param mysqli $db Conexión a la base de datos.
 */
function listar_empresas($db)
{
    try {
        $sql = "SELECT 
                    e.IdEmpresa,
                    e.Nombre,
                    e.Correo,
                    e.RazonSocial,
                    e.Domicilio,
                    cc.Nombre AS NombreColonia,
                    cm.Nombre AS NombreMunicipio,
                    ce.Nombre AS NombreEstado,
                    e.TelefonoEmpresa
                FROM 
                    empresa e
                JOIN 
                    catcolonia cc ON e.IdColonia = cc.IdColonia
                JOIN 
                    catmunicipio cm ON e.IdMunicipio = cm.IdMunicipio
                JOIN 
                    catestado ce ON cm.IdEstado = ce.IdEstado
                WHERE 
                    e.Borrado = 1
                ORDER BY IdEmpresa ASC";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            while ($empresa = $resultado->fetch_assoc()) {
                $direccion = "{$empresa["NombreMunicipio"]}, {$empresa["NombreEstado"]}";

                $empresas[] = [
                    "IdEmpresa" => $empresa["IdEmpresa"],
                    "Nombre" => $empresa["Nombre"],
                    "Correo" => $empresa["Correo"],
                    "Direccion" => $direccion
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
    } catch (mysqli_sql_exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];

        send_response($response, 500);
    }
}

/**
 * Buscar empresas de acuerdo a un término de búsqueda ingresado.
 * Este término de búsqueda puede ser el nombre, razón social, correo o
 * domicilio de la empresa; o en su defecto nombre de la colonia, municipio
 * o estado donde radica la empresa.
 *
 * @param mysqli $db                Conexión a la base de datos.
 * @param string $termino_busqueda  Término de búsqueda ingresado por el usuario.
 */
function buscar_empresas($db, $termino_busqueda)
{
    try {
        $sql = "SELECT 
                    e.IdEmpresa,
                    e.Nombre,
                    e.Correo,
                    e.RazonSocial,
                    e.Domicilio,
                    cc.Nombre AS NombreColonia,
                    cm.Nombre AS NombreMunicipio,
                    ce.Nombre AS NombreEstado,
                    e.TelefonoEmpresa
                FROM 
                    empresa e
                JOIN 
                    catcolonia cc ON e.IdColonia = cc.IdColonia
                JOIN 
                    catmunicipio cm ON e.IdMunicipio = cm.IdMunicipio
                JOIN 
                    catestado ce ON cm.IdEstado = ce.IdEstado
                WHERE
                    (e.Nombre LIKE '%$termino_busqueda%' 
                    OR e.RazonSocial LIKE '%$termino_busqueda%'
                    OR e.Correo LIKE '%$termino_busqueda%'
                    OR e.Domicilio LIKE '%$termino_busqueda%'
                    OR cc.Nombre LIKE '%$termino_busqueda%'
                    OR cm.Nombre LIKE '%$termino_busqueda%'
                    OR ce.Nombre LIKE '%$termino_busqueda%')
                AND e.Borrado = 1
                ORDER BY IdEmpresa ASC";

        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $resultados_busqueda = [];

            while ($empresa = $resultado->fetch_assoc()) {
                $direccion = "{$empresa["NombreMunicipio"]}, {$empresa["NombreEstado"]}";

                $resultados_busqueda[] = [
                    "IdEmpresa" => $empresa["IdEmpresa"],
                    "Nombre" => $empresa["Nombre"],
                    "Correo" => $empresa["Correo"],
                    "Direccion" => $direccion
                ];
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
 * Desactiva una empresa, colocando el valor `0` en el campo `Borrado` de su registro.
 *
 * @param mysqli $db  Conexión a la base de datos.
 * @param array $post Arreglo sociativo con la key `data.IdEmpresa`.
 */
function desactivarEmpresa($db, $post)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_empresa = filter_var($post["data"]["idEmpresa"], FILTER_VALIDATE_INT);

        try {
            $sql = "UPDATE empresa
                    SET Borrado = 0
                    WHERE  IdEmpresa = $id_empresa";

            $resultado = $db->query($sql);

            if ($resultado) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Empresa desactivada"
                ];

                send_response($response, 200);
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
