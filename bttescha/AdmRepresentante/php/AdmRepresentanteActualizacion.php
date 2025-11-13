<?php

require_once "../../config/database.php";
include "../../helpers/php/functions.php";
$db = conectar_db();
if( !$db ) exit; // Verificar que `$db` no sea `false`

/**
 * Con este código funciona con Postman.
 */
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === "application/json") {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);

    $_POST = $jsonData;
}

$opcion = $_SERVER["REQUEST_METHOD"] === "POST" ? $_POST["case"] : $_GET["case"];

switch( $opcion ) {
    case "obtenerEmpresa":
        obtener_empresa($db);
        break;

    case "listarColonias":
        get_colonias( $_GET["nombreColonia"] );
        break;
    
    case "listarMunicipios":
        get_municipios( $_GET["nombreMunicipio"] );
        break;
    
    case "editarEmpresa":
        actualizar_empresa($db);
        break;
    
    default:
        break;
}

/**
 * Recupera información detallada sobre una empresa específica a partir de un
 * identificador de empleador.
 * 
 * @param mysqli $db    Objeto de conexión a la base de datos utilizando MySQLi.
 * @return void         La función imprime una respuesta JSON con los datos de la empresa
 *                      o mensajes de error.
 */
function obtener_empresa($db) {
    if( $_SERVER["REQUEST_METHOD"] === "GET" ) {
        $idEmpleador = $db->escape_string($_GET["idEmpleador"]);

        $sql = "SELECT
                    e.IdEmpresa,
                    e.Nombre AS NombreEmpresa,
                    e.Correo,
                    e.RazonSocial,
                    e.Domicilio,
                    e.IdColonia,
                    cc.Nombre AS NombreColonia,
                    e.IdMunicipio,
                    cm.Nombre AS NombreMunicipio,
                    ce.Nombre AS NombreEstado,
                    e.TelefonoEmpresa,
                    e.Registro,
                    e.Borrado
                FROM
                    empresa e
                    LEFT JOIN catcolonia cc ON e.IdColonia = cc.IdColonia
                    LEFT JOIN catmunicipio cm ON e.IdMunicipio = cm.IdMunicipio
                    LEFT JOIN catestado ce ON cm.IdEstado = ce.IdEstado
                WHERE
                    e.IdEmpleador = $idEmpleador AND
                    e.Borrado = 1";
    
        try {
            $resultado = $db->query($sql);
    
            if( $resultado->num_rows > 0 ) {
                $response = [
                    "error" => false,
                    "data" => mysqli_fetch_assoc($resultado)
                ];
    
                http_response_code(200);
            } else {
                $response = [
                    "error" => true,
                    "mensaje" => "No se encontró la empresa"
                ];
    
                http_response_code(404);
            }
    
            echo json_encode($response);
        } catch (Exception $e) {
            $response = [
                "error" => true,
                "mensaje" => "Ha ocurrido un error. Código: {$e->getCode()}"
            ];
    
            http_response_code(500);
            echo json_encode($response);
        }
    }
}

/**
 * Actualiza la información de la empresa, colocando `1` en la columna `Registro` cuando se
 * guardan los cambios.
 * 
 * @param mysqli $db    Conexión activa a la base de datos MySQL.
 * @return void         Genera una respuesta JSON con un mensaje de éxito o error.
 */
function actualizar_empresa($db) {
    if( $_SERVER["REQUEST_METHOD"] === "POST" ) {
        $id_empresa = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["IdEmpresa"]);
        $nombre_empresa = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["NombreEmpresa"]);
        $email = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["Correo"]);
        $razon_social = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["RazonSocial"]);
        $domicilio = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["Domicilio"]);
        $id_colonia = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["IdColonia"]);
        $nombre_colonia = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["NombreColonia"]);
        $id_municipio = intval( mysqli_real_escape_string($db, $_POST["data"]["empresa"]["IdMunicipio"]) );
        $telefono = mysqli_real_escape_string($db, $_POST["data"]["empresa"]["TelefonoEmpresa"]);
        $id_empleador = intval( mysqli_real_escape_string($db, $_POST["data"]["idEmpleador"]) );

        /**
         * Si no existe `$id_colonia` es porque esta no está registrada en la base de datos.
         * Valida también si existe `$nombre_colonia`, en caso de existir, reliza el registro.
         */
        if( !$id_colonia && $nombre_colonia ) {
            $id_colonia = registrar_colonia($nombre_colonia, $id_municipio);
            if( $id_colonia < 0 ) exit;
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
                        TelefonoEmpresa = '$telefono',
                        Registro = 1
                    WHERE
                        IdEmpleador = $id_empleador";
            $resultado = mysqli_query($db, $sql);

            if( $resultado ) {
                // Verificar si se actualizaron filas
                // $filas_afectadas = mysqli_affected_rows($db);

                // if( $filas_afectadas === 0 ) {
                //     throw new Exception("No se pudo realizar la actualización.");
                // }
                
                $response = [
                    "error" => false,
                    "mensaje" => "Actualización exitosa"
                ];

                http_response_code(200);
                echo json_encode($response);
            } else {
                throw new Exception("Error en la consulta: " . mysqli_error($db));
            }

        } catch (Exception $e) {
            $response = [
                "error" => true,
                "mensaje" => "Error: " . $e->getMessage()
            ];

            http_response_code(500);
            echo json_encode($response);
        } finally {
            mysqli_close($db);
        }
    }
}


?>