<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . "/../../../Mailer.php";

$db = conectar_db();

/**
 * Obtiene el email del estudiante o del empleador.
 * En caso de error, retorna `false`.
 *
 * @param  int      $id      ID del estudiante o del empleador.
 * @param  string   $tabla   Tabla de la base de datos: `estudiante` o `empleador`.
 * 
 * @return string|false      Email del estudiante o `false` en caso de error.
 */
function get_email($id, $tabla)
{
    global $db;
    $id_registro = filter_var($id, FILTER_VALIDATE_INT);

    $pk = $tabla === "estudiante" ? "IdEstudiante" : "IdEmpleador";

    try {
        $sql = "SELECT CorreoElectronico FROM $tabla WHERE $pk = $id_registro";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc()["CorreoElectronico"];
        }

        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Obtiene el nombre de una vacante y el nombre de la empresa.
 *
 * @param  int $id      ID de la vacante.
 * @return array|false  Arreglo asociativo con las keys `nombre_vacante`,
 *                      `nombre_empresa` y `id_empeador` o `false` en caso de error.
 */
function get_info_vacante($id)
{
    global $db;
    $id_vacante = filter_var($id, FILTER_VALIDATE_INT);

    try {
        $sql = "SELECT 
                    v.Nombre AS NombreVacante, 
                    e.Nombre AS NombreEmpresa, 
                    e.IdEmpleador 
                FROM vacante v 
                INNER JOIN empresa e ON v.IdEmpresa = e.IdEmpresa 
                WHERE v.IdVacante = $id_vacante";
        $resultado = $db->query($sql);

        if ($resultado->num_rows > 0) {
            $vacante = $resultado->fetch_assoc();

            $datos = [
                "nombre_vacante" => $vacante["NombreVacante"],
                "nombre_empresa" => $vacante["NombreEmpresa"],
                "id_empeador"    => $vacante["IdEmpleador"]
            ];
            return $datos;
        }

        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Envía un email al estudiante para confirmar la postulación.
 *
 * @param  int  $id_estudiante  ID del estudiante.
 * @param  int  $id_vacante     ID de la vacante.
 * @return bool                 `true` si envió el email o `false` si hubo algún error.
 */
function enviar_email_estudiante($id_estudiante, $id_vacante)
{
    $id_estudiante = filter_var($id_estudiante, FILTER_VALIDATE_INT);
    $id_vacante = filter_var($id_vacante, FILTER_VALIDATE_INT);

    if (!$id_estudiante || !$id_vacante) return false;

    /**
     * Obtener el email del estudiante.
     */
    $email = get_email($id_estudiante, "estudiante");
    if (!$email) return false;

    /**
     * Obtener el nombre de la vacante y de la empresa.
     */
    $info_vacante = get_info_vacante($id_vacante);
    if (!$info_vacante) return false;

    $nombre_vacante = $info_vacante["nombre_vacante"];
    $nombre_empresa = $info_vacante["nombre_empresa"];

    /**
     * Crear asunto para el correo.
     */
    $asunto_email = "Postulación: $nombre_vacante";

    /**
     * Crear el cuerpo del correo.
     */
    $plantilla = file_get_contents(__DIR__ . "/../../email/postulacion-estudiante.html");
    $cuerpo_email = str_replace(
        ["{nombre_vacante}", "{nombre_empresa}"],
        [$nombre_vacante, $nombre_empresa],
        $plantilla
    );

    /**
     * Enviar email.
     */
    $mailer = new Mailer();
    $mailer->enviar($email, $asunto_email, $cuerpo_email);

    return true;
}

/**
 * Envía un email al empleador para notificar sobre una nueva postulación.
 *
 * @param  int $id_vacante ID de la vacante a la que el estudiante se postuló.
 * @return bool            `true` si envió el email o `false` si hubo algún error.
 */
function enviar_email_empleador($id_vacante)
{
    $id_vacante = filter_var($id_vacante, FILTER_VALIDATE_INT);
    if (!$id_vacante) return false;

    /**
     * Obtener información de la vacante.
     */
    $info_vacante = get_info_vacante($id_vacante);
    if (!$info_vacante) return false;

    $nombre_vacante = $info_vacante["nombre_vacante"];
    $nombre_empresa = $info_vacante["nombre_empresa"];
    $id_empleador = $info_vacante["id_empeador"];

    /**
     * Obtener el email del empleador.
     */
    $email = get_email($id_empleador, "empleador");
    if (!$email) return false;

    /**
     * Crear asunto para el correo.
     */
    $asunto_email = "Nueva postulación: $nombre_vacante";

    /**
     * Crear el cuerpo del correo.
     */
    $plantilla = file_get_contents(__DIR__ . "/../../email/postulacion-representante.html");
    $cuerpo_email = str_replace(
        ["{nombre_vacante}", "{nombre_empresa}"],
        [$nombre_vacante, $nombre_empresa],
        $plantilla
    );

    /**
     * Enviar email.
     */
    $mailer = new Mailer();
    $mailer->enviar($email, $asunto_email, $cuerpo_email);

    return true;
}
