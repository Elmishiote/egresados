<?php

/**
 * Conecta a la base de datos.
 * En caso de producirse un error, enviará una respuesta en JSON y
 * retornará `false`.
 *
 * @return mysqli|bool Conexión a la base de datos o `false`.
 */
function conectar_db() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "egresadosbolsatrabajo";

    try {
        // Crear una conexión
        $conn = new mysqli($servername, $username, $password, $database);

        // Verificar la conexión
        if( $conn->connect_error ) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }

        return $conn;
    } catch (Exception $e) {
        // Manejar la excepción
        $response = [
            "ok" => false,
            "mensaje" => "Error: " . $e->getMessage()
        ];

        http_response_code(500);
        echo json_encode($response);
        return false;
    }
}

?>