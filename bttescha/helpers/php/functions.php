<?php

use FFI\Exception;

require_once "../../config/database.php";
$db = conectar_db();

/**
 * Sanitiza los datos de un arreglo.
 *
 * @param  array $arr   Arreglo a sanitizar.
 * @return array        Arreglo con los datos sanitizados.
 */
function sanitize_array($arr) {
    global $db;
    $sanitized_array = [];

    foreach($arr as $key => $value) {
        if( is_array($value) ) {
            $sanitized_array[$key] = sanitize_array($value);
        } else {
            $sanitized_array[$key] = $db->escape_string($value);
        }
    }

    return $sanitized_array;
}

/**
 * Envía una respuesta en JSON con el código de status correspondiente.
 *
 * @param array $arr_response   Arreglo con la respuesta.
 * @param int   $status_code    (Opcional) Código de estado HTTP que se enviará
 *                              en la respuesta. Por defecto, es 200 (OK).
 */
function send_response($arr_response, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($arr_response);
}

/**
 * Inserta una nueva colonia en la base de datos. Usa sentencias preparadas.
 *
 * Esta función realiza dos consultas: una para obtener el último `IdColonia` y otra para
 * insertar una nueva fila en la tabla `catcolonia`. Retorna el nuevo `IdColonia` si la
 * inserción es exitosa, o `-1` si hay algún error.
 *
 * @param string $nombre_colonia    El nombre de la nueva colonia.
 * @param int    $id_municipio      El `IdMunicipio` al que pertenece la colonia.
 *
 * @return int                      El nuevo `IdColonia` si la inserción es exitosa, o `-1` si hay un error.
 */
function registrar_colonia($nombre_colonia, $id_municipio)
{
    global $db;
    if( !$nombre_colonia || !$id_municipio ) return -1;

    try {
        /**
         * Verificar si existe algun registro con el mismo nombre y municipio. Si existe,
         * la colonia ya está registrada y retornará su ID.
         */
        $sql = "SELECT * FROM catcolonia WHERE LOWER(Nombre) = LOWER(?) AND IdMunicipio = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("si", $nombre_colonia, $id_municipio);
        $stmt->execute();
        $result = $stmt->get_result();

        if( $result->num_rows > 0 ) {
            $colonia = $result->fetch_assoc();
            return intval( $colonia["IdColonia"] );
        }

        /**
         * Obtener el último ID de la tabla `catcolonia`.
         */
        $nextId = get_nextid("IdColonia", "catcolonia");

        if( $nextId < 0 ) {
            throw new Exception("Error en la consulta para obtener el último IdColonia: " . $db->error);
        }

        /**
         * Crear nueva colonia.
         */
        $sql_insert = "INSERT INTO catcolonia (IdColonia, Nombre, IdMunicipio, Borrado) VALUES (?, ?, ?, 1)";
        $stmt = $db->prepare($sql_insert);
        $stmt->bind_param("isi", $nextId, $nombre_colonia, $id_municipio);
        $result = $stmt->execute();

        if( $result && $stmt->affected_rows > 0 ) {
            return $nextId;
        } else {
            throw new Exception("Error en la consulta de inserción: " . mysqli_error($db));
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return -1;
    }
}

/**
 * Lista las colonias para el cuadro de sugerencias. En caso de que existan
 * coincidencias con el texto ingresado devuelve 3 resultados.
 *
 * @param string $nombre_colonia Texto ingresado por el usuario.
 */
function get_colonias($nombre_colonia)
{
    global $db;
    if( !$nombre_colonia ) exit;
    $nombre_colonia_escapado = $db->escape_string($nombre_colonia);

    try {
        $sql = "SELECT 
                    cc.IdColonia,
                    cc.Nombre AS NombreColonia,
                    cc.IdMunicipio,
                    cm.Nombre AS NombreMunicipio,
                    cm.IdEstado,
                    ce.Nombre AS NombreEstado
                FROM 
                    catcolonia cc
                JOIN 
                    catmunicipio cm ON cc.IdMunicipio = cm.IdMunicipio
                JOIN 
                    catestado ce ON cm.IdEstado = ce.IdEstado
                WHERE 
                    cc.Borrado = 1 AND
                    cm.Borrado = 1 AND
                    cc.Nombre LIKE '%$nombre_colonia_escapado%'
                LIMIT 3";
        $resultado = $db->query($sql);

        if( $resultado->num_rows > 0 ) {
            while( $coloniaBD = $resultado->fetch_assoc() ) {
                $colonias[] = $coloniaBD;
            }

            $response = [
                "ok" => true,
                "data" => $colonias
            ];
            send_response($response);
        } else {
            $response = [ "ok" => false ];

            send_response($response, 404);
        }

    } catch(mysqli_sql_exception $e) {
        $response = [
            "ok" => false,
            "mensaje" => $e->getMessage()
        ];
        send_response($response, 500);
    }
}

/**
 * Lista los municipios para el cuadro de sugerencias. En caso de que existan
 * coincidencias con el texto ingresado devuelve 3 resultados.
 *
 * @param string $nombre_municipio Texto ingresado por el usuario.
 */
function get_municipios($nombre_municipio)
{
    global $db;
    if( !$nombre_municipio ) exit;
    $nombre_municipio_escapado = $db->escape_string($nombre_municipio);

    try {
        $sql = "SELECT 
                    cm.IdMunicipio,
                    cm.Nombre AS NombreMunicipio,
                    cm.IdEstado,
                    ce.Nombre AS NombreEstado
                FROM 
                    catmunicipio cm
                JOIN 
                    catestado ce ON cm.IdEstado = ce.IdEstado
                WHERE 
                    cm.Borrado = 1 AND
                    cm.Nombre LIKE '%$nombre_municipio_escapado%'
                LIMIT 3";

        $resultado = $db->query($sql);

        if( $resultado->num_rows > 0 ) {
            while( $municipioBD = $resultado->fetch_assoc() ) {
                $municipios[] = $municipioBD;
            }

            $response = [
                "ok" => true,
                "data" => $municipios
            ];
            send_response($response);
        } else {
            $response = [ "ok" => false ];

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
 * Obtiene el último ID de una tabla especificada y le suma 1.
 *
 * @param  string $nombre_columna   Nombre de la columna que almacena el ID del registro.
 * @param  string $tabla            Nombre de la tabla.
 * @return int                      El siguiente ID a insertar o `-1` en caso de error.
 */
function get_nextid($nombre_columna, $tabla)
{
    global $db;

    try {
        $sql = "SELECT MAX($nombre_columna) AS lastId FROM $tabla";
        $result = $db->query($sql);

        $row = $result->fetch_assoc();
        $last_id = intval( $row["lastId"] );
        
        return $last_id + 1;

    } catch (mysqli_sql_exception $e) {
        return -1;
    }
}

?>