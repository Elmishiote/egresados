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
    case "validarConvenio":
        validar_convenio($db, $datos_get);
        break;

    case "registrarEmpleador":
        registrar_empleador($db, $datos_post);
        break;

    default:
        break;
}

/**
 * Consulta en la base de datos si el número de convenio ingresado existe. Si existe,
 * envia una respuesta en JSON con la información del convenio, de lo contrario, envía un
 * mensaje de error con el status 404.
 *
 * @param mysqli    $db          Conexión a la base de datos.
 * @param array     $datos_get   Datos ingresados por el usuario (arreglo `$_GET` sanitizado).
 */
function validar_convenio($db, $datos_get)
{
    // Validar que esté definidio
    if (!isset($datos_get["numConvenio"])) {
        $response = [
            "ok" => false,
            "mensaje" => "Debes ingresar un número de convenio"
        ];
        send_response($response, 422);
        exit;
    }

    $numero_convenio = filter_var($datos_get["numConvenio"], FILTER_VALIDATE_INT);

    $sql = "SELECT
                c.IdConvenio,
                c.NumeroConvenio,
                c.IdEmpresa,
                e.Nombre AS NombreEmpresa,
                e.RazonSocial
            FROM
                convenio c
            JOIN
                empresa e ON c.IdEmpresa = e.IdEmpresa
            WHERE
                c.Borrado = 1 AND
                e.Borrado = 1 AND
                c.NumeroConvenio = ?";

    try {
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $numero_convenio);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response = [
                "ok" => true,
                "data" => $result->fetch_assoc()
            ];

            send_response($response);
        } else {
            $response = [
                "ok" => false,
                "mensaje" => "El convenio ingresado no existe"
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
 * Registra un nuevo usuario en la tabla `usuario`.
 *
 * @param  mysqli $db           Conexión a la base de datos.
 * @param  array  $user_info    Arreglo con las claves `nick` y `password`, ambos de tipo `string`.
 * @return int                  En caso de éxito, retorna el `IdUsuario` insertado, si el nombre de
 *                              usuario (`nick`) ya está registrado en la base de datos, retorna `0`.
 *                              Si ocurre un error al insertar el usuario, retorna `-1`.
 */
function registrar_usuario($db, $user_info)
{
    $nick = strtolower($user_info["nick"]);
    $password = password_hash($user_info["password"], PASSWORD_DEFAULT);

    try {
        // Obtener el último ID
        $next_id = get_nextid("IdUsuario", "usuario");
        if ($next_id < 0) throw new Exception("Ha ocurrido un error");

        // Revisar si el `$nick` si ya está registrado
        $sql = "SELECT * FROM usuario WHERE NICK = '$nick'";
        $resultado = $db->query($sql);
        if ($resultado->num_rows > 0) return 0;

        // Insertar registro
        $sql = "INSERT INTO usuario (IdUsuario, Nick, Contraseña, tipo_usuario, Borrado) VALUES (?, ?, ?, 2, 1)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("iss", $next_id, $nick, $password);
        $stmt->execute();

        if ($stmt->affected_rows > 0) return $next_id;
        else throw new Exception("Ha ocurrido un error");
    } catch (Exception $e) {
        // echo $e->getMessage();
        return -1;
    }
}

/**
 * Actualiza el campo `IdEmpleador` en el registro con el ID `$id_empresa`
 * en la tabla `empresa`.
 *
 * @param  mysqli        $db             Conexión a la base de datos.
 * @param  int|string    $id_empresa     ID de la empresa a actualizar.
 * @param  int|string    $id_empleador   ID del empleador.
 * @return int                           `1` si la actualización es correcta o `-1` en caso de error.
 */
function actualizar_empresa_idempleador($db, $id_empresa, $id_empleador)
{
    $id_empresa = filter_var($id_empresa, FILTER_VALIDATE_INT);
    $id_empleador = filter_var($id_empleador, FILTER_VALIDATE_INT);

    try {
        $sql = "UPDATE empresa SET IdEmpleador = ? WHERE idEmpresa = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $id_empleador, $id_empresa);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return 1;
        } else {
            throw new Exception("No se pudo actualizar la información de la empresa");
        }
    } catch (Exception $e) {
        // echo $e->getMessage();
        return -1;
    }
}

/**
 * Registra un nuevo empleador.
 * Primero crea un nuevo usuario usando la función `registrar_usuario()`, posteriormente
 * crea el nuevo registro en la tabla `empleador`. Finalmente, actualiza la tabla `empresa`
 * añadiendo el `IdEmpleador` al registro de la empresa al que pertenece el empleador.
 *
 * @param mysqli $db    Conexión a la base de datos.
 * @param array  $datos Información recibida en `$_POST`.
 */
function registrar_empleador($db, $datos)
{
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $arr_usuario = $datos["data"]["usuario"];
        $arr_empleador = $datos["data"]["empleador"];
        $id_empresa = $datos["data"]["idEmpresa"];

        // Registrar usuario
        $id_usuario = registrar_usuario($db, $arr_usuario);

        // El usuario ya existe
        if ($id_usuario === 0) {
            $response = [
                "ok" => false,
                "mensaje" => "Este usuario ya está registrado"
            ];
            send_response($response, 409);
            exit;
        } else if ($id_usuario === -1) {
            // No se pudo registrar el usuario
            $response = [
                "ok" => false,
                "mensaje" => "Ha ocurrido un error al crear el usuario"
            ];
            send_response($response, 500);
            exit;
        }


        try {
            // Obtener el útlimo ID de `empleador` y sumarle 1
            $next_id = get_nextid("IdEmpleador", "empleador");
            if ($next_id < 0) throw new Exception("Ha ocurrido un error");

            $nombre = $arr_empleador["nombre"];
            $primer_apellido = $arr_empleador["primerApellido"];
            $segundo_apellido = $arr_empleador["segundoApellido"];
            $telefono = $arr_empleador["telefono"];
            $puesto = $arr_empleador["puesto"];

            $sql = "INSERT INTO empleador (IdEmpleador, Nombre, PrimerApellido, SegundoApellido, Telefono, Whatsapp, Puesto, Registro, IdUsuario, Borrado) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, 1)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("issssssi", $next_id, $nombre, $primer_apellido, $segundo_apellido, $telefono, $telefono, $puesto, $id_usuario);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception($db->error);
            }

            /**
             * Actualizar la tabla `empresa` para añadir a `IdEmpleador` el ID del empleador
             * recien registrado (este ID está almacenado en `$next_id`).
             */
            $actualizacion_empresa = actualizar_empresa_idempleador($db, $id_empresa, $next_id);

            if ($actualizacion_empresa > 0) {
                $response = [
                    "ok" => true,
                    "mensaje" => "Registro realizado exitosamemente"
                ];

                send_response($response, 201);
            } else {
                throw new Exception("Se registró al empleador, pero no se pudo vincular con la empresa correspondiente");
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
