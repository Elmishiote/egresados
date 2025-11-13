<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/php/functions.php";

$db = conectar_db();
if (!$db) exit; // Verificar que `$db` no sea `false`

$jsonData = [];
$content_type = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : "";
if ($content_type === "application/json") {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
}

$datos_post = count($jsonData) > 0 ? sanitize_array($jsonData) : sanitize_array($_POST);
$datos_get = sanitize_array($_GET);

$opcion = $_SERVER["REQUEST_METHOD"] === "POST" ? $datos_post["case"] : $_GET["case"];

switch ($opcion) {
    case "login":
        login($datos_post);
        break;

    default:
        $response = ["ok" => false, "mensaje" => "Operación no soportada"];
        send_response($response, 400);
        break;
}

function login($post)
{
    global $db;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (
            !isset($post["data"]["user"]) || empty($post["data"]["user"]) ||
            !isset($post["data"]["password"]) || empty($post["data"]["password"])
        ) {
            $response = ["ok" => false, "mensaje" => "Ingresa tu usuario y contraseña"];
            send_response($response, 400);
            exit;
        }

        $usuario = $post["data"]["user"];
        $password = $post["data"]["password"];

        try {
            $sql = "SELECT * FROM usuario WHERE Nick = '$usuario'";
            $resultado = $db->query($sql);

            if ($resultado->num_rows > 0) {
                $usuario_bd = $resultado->fetch_assoc();
                $pass_coincide = password_verify($password, $usuario_bd["Contrasena"]);

                if (!$pass_coincide) {
                    $response = ["ok" => false, "mensaje" => "Contraseña incorrecta"];
                    send_response($response, 401);
                    exit;
                }

                $user_info = get_user_info($usuario_bd["IdUsuario"], $usuario_bd["tipo_usuario"]);
                $redirect_url = null;

                // Verificar si es un estudiante y redirigir según el estado de los datos
                if ($usuario_bd["tipo_usuario"] == 3) {
                    $datosLlenos = check_estudiante_datos($usuario_bd["IdUsuario"]);
                    // Si los datos no están completos, redirigir a cambio_pass.php
                    $redirect_url = $datosLlenos === "N"
                        ? "http://localhost/seybt/bttescha/AdmEstudiante/cambio_pass.php"
                        : "http://localhost/seybt/bttescha/AdmEstudiante/";
                }

                // Respuesta JSON con la información del usuario y redirección
                $response = [
                    "ok" => true,
                    "mensaje" => "Inicio de sesión exitoso",
                    "data" => [
                        "logged_in"     => true,
                        "tipo_usuario"  => $usuario_bd["tipo_usuario"],
                        "user_info"     => $user_info,
                        "redirect"      => $redirect_url // Aquí se pasa la URL de redirección
                    ]
                ];

                send_response($response);

                // Iniciar la sesión en el servidor
                session_start();
                $_SESSION["logged_in"]      = true;
                $_SESSION["tipo_usuario"]   = $usuario_bd["tipo_usuario"];
                $_SESSION["user_info"]      = $user_info;
                $_SESSION["id_usuario"]     = $usuario_bd["IdUsuario"];
            } else {
                $response = ["ok" => false, "mensaje" => "Este usuario no existe"];
                send_response($response, 401);
            }
        } catch (Exception $e) {
            $response = ["ok" => false, "mensaje" => $e->getMessage()];
            send_response($response, 500);
        }
    }
}

function check_estudiante_datos($id_usuario)
{
    global $db;

    try {
        $sql = "SELECT DatosLlenos FROM estudiante WHERE IdUsuario = '$id_usuario'";
        $resultado = $db->query($sql);

        if ($resultado->num_rows === 0) {
            return false;
        }

        $datos_estudiante = $resultado->fetch_assoc();
        return $datos_estudiante['DatosLlenos'];
    } catch (Exception $e) {
        return false;
    }
}

function get_user_info($id_usuario, $tipo)
{
    global $db;

    $tipos_usuario = [
        1 => "administrador",
        2 => "empleador",
        3 => "estudiante"
    ];

    $tipo_usuario = $tipos_usuario[$tipo];

    try {
        $sql = "SELECT * FROM $tipo_usuario WHERE IdUsuario = '$id_usuario'";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();

            if ($tipo_usuario === "empleador") {
                $sql = "SELECT IdEmpresa FROM empresa WHERE IdEmpleador = '{$usuario["IdEmpleador"]}'";
                $resultado_empresa = $db->query($sql);
                if ($resultado_empresa->num_rows === 0) return false;
                $usuario["IdEmpresa"] = $resultado_empresa->fetch_column(0);
            }

            return $usuario;
        } else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}

?>
