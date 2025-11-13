<?php
// No es necesario llamar session_start() nuevamente si ya se ha llamado en otro lugar
require __DIR__ . "/../auth/php/functions.php";
require_once __DIR__ . "/../config/database.php";

// Verificar si el usuario está autenticado
is_logged_in(); // Este método debería manejar el session_start() si es necesario
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Perfil del Egresado</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> -->
</head>
<body>
    <?php
    include_once __DIR__ . '/../includes/header.php';

    // Verificar si la sesión ya está iniciada y si el ID de usuario está presente
    if (!isset($_SESSION['id_usuario'])) {
        exit;
    }

    $idUsuario = $_SESSION['id_usuario']; // Obtener el ID de usuario de la sesión

    // Conexión a la base de datos
    $db = conectar_db();
    if (!$db) {
        echo "<div class='alert alert-danger text-center'>Error al conectar con la base de datos.</div>";
        exit;
    }

    // Obtener el IdEstudiante asociado al IdUsuario
    $query = "SELECT IdEstudiante FROM estudiante WHERE IdUsuario = ? LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $stmt->bind_result($idEstudiante);
    $stmt->fetch();
    $stmt->close();

    if (empty($idEstudiante)) {
        echo "<div class='alert alert-danger text-center'>No se encontró un estudiante asociado a este usuario.</div>";
        exit;
    }

    // Obtener los datos necesarios para los selectores (Municipios, Colonias, Países, Carreras, Especialidades)
    $queryMunicipios = "SELECT IdMunicipio, Nombre FROM catmunicipio";
    $resultMunicipios = $db->query($queryMunicipios);

    $queryColonias = "SELECT IdColonia, Nombre FROM catcolonia";
    $resultColonias = $db->query($queryColonias);

    $queryPaises = "SELECT IdPais, Nombre FROM catpais";
    $resultPaises = $db->query($queryPaises);

    $queryCarreras = "SELECT IdCarrera, Nombre FROM carrera";
    $resultCarreras = $db->query($queryCarreras);

    $queryEspecialidades = "SELECT IdEspecialidad, Nombre FROM especialidad";
    $resultEspecialidades = $db->query($queryEspecialidades);

    // Procesar la actualización del formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Variables del formulario
        $nombre = $_POST["nombre"] ?? null;
        $primerApellido = $_POST["PrimerApellido"] ?? null;
        $segundoApellido = $_POST["SegundoApellido"] ?? null;
        $matricula = $_POST["Matricula"] ?? null;
        $curp = $_POST["CURP"] ?? null;
        $edad = $_POST["Edad"] ?? null;
        $fechaNacimiento = $_POST["FechaNacimiento"] ?? null;
        $genero = $_POST["Genero"] ?? null;
        $estadoCivil = $_POST["EstadoCivil"] ?? null;
        $idColonia = $_POST["IdColonia"] ?? null;
        $idMunicipio = $_POST["IdMunicipio"] ?? null;
        $idPais = $_POST["IdPais"] ?? null;
        $telefonoMovil = $_POST["TelefonoMovil"] ?? null;
        $telefonoParticular = $_POST["TelefonoParticular"] ?? null;
        $correoElectronico = $_POST["CorreoElectronico"] ?? null;
        $idCarrera = $_POST["IdCarrera"] ?? null;
        $idEspecialidad = $_POST["IdEspecialidad"] ?? null;

        // Validar campos obligatorios
        if ($nombre && $primerApellido && $matricula && $curp && $correoElectronico) {
            // Consulta para actualizar los datos del estudiante
            $consulta = $db->prepare("
                UPDATE estudiante
                SET
                    Nombre = ?, 
                    PrimerApellido = ?, 
                    SegundoApellido = ?, 
                    Matricula = ?, 
                    CURP = ?, 
                    Edad = ?, 
                    FechaNacimiento = ?, 
                    Genero = ?, 
                    EstadoCivil = ?, 
                    IdColonia = ?, 
                    IdMunicipio = ?, 
                    IdPais = ?, 
                    TelefonoMovil = ?, 
                    TelefonoParticular = ?, 
                    CorreoElectronico = ?, 
                    IdCarrera = ?, 
                    IdEspecialidad = ?
                WHERE IdEstudiante = ?");

            if ($consulta) {
                // Enlazar los parámetros para la consulta
                $consulta->bind_param(
                    "sssssssssiissssiii", 
                    $nombre,
                    $primerApellido,
                    $segundoApellido,
                    $matricula,
                    $curp,
                    $edad,
                    $fechaNacimiento,
                    $genero,
                    $estadoCivil,
                    $idColonia,
                    $idMunicipio,
                    $idPais,
                    $telefonoMovil,
                    $telefonoParticular,
                    $correoElectronico,
                    $idCarrera,
                    $idEspecialidad,
                    $idEstudiante
                );

                // Ejecutar la consulta
                if ($consulta->execute()) {
                    // Actualizar el campo DatosLlenos a 'S'
                    $actualizarDatosLlenos = $db->prepare("UPDATE estudiante SET DatosLlenos = 'S' WHERE IdEstudiante = ?");
                    if ($actualizarDatosLlenos) {
                        $actualizarDatosLlenos->bind_param('i', $idEstudiante);
                        $actualizarDatosLlenos->execute();
                        $actualizarDatosLlenos->close();
                    }

                    // Mostrar mensaje emergente y redirigir
                    echo "<script>
                        alert('Los datos se guardaron correctamente.');
                        window.location.href = 'http://localhost/seybt/bttescha/AdmEstudiante/';
                    </script>";
                    exit;
                } else {
                    echo "<div class='alert alert-danger text-center'>Error al actualizar los datos: " . $consulta->error . "</div>";
                }
                $consulta->close();
            } else {
                echo "<div class='alert alert-danger text-center'>Error al preparar la consulta: " . $db->error . "</div>";
            }
        } else {
            echo "<div class='alert alert-warning text-center'>Todos los campos marcados con * son obligatorios.</div>";
        }
    }

    // Cerrar la conexión a la base de datos
    $db->close();
    ?>

    <main class="container-fluid my-5">
        <h1 class="text-center">Perfil del Egresado</h1>

        <div class="row w-100 mt-4 mx-auto">
            <div class="col-12 col-sm-6 col-lg-4 mx-auto">
                <form id="formActualizarDatosEstudiante" class="z-0 position-relative" method="POST">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="PrimerApellido" class="form-label">Primer Apellido <span class="text-danger">*</span></label>
                        <input type="text" name="PrimerApellido" id="PrimerApellido" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="SegundoApellido" class="form-label">Segundo Apellido</label>
                        <input type="text" name="SegundoApellido" id="SegundoApellido" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="Matricula" class="form-label">Matrícula <span class="text-danger">*</span></label>
                        <input type="text" name="Matricula" id="Matricula" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="CURP" class="form-label">CURP <span class="text-danger">*</span></label>
                        <input type="text" name="CURP" id="CURP" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="Edad" class="form-label">Edad</label>
                        <input type="number" name="Edad" id="Edad" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="FechaNacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="FechaNacimiento" id="FechaNacimiento" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="Genero" class="form-label">Género</label>
                        <select name="Genero" id="Genero" class="form-control">
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                            <option value="Prefiero no contestar">Prefiero no contestar</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="EstadoCivil" class="form-label">Estado Civil</label>
                        <select name="EstadoCivil" id="EstadoCivil" class="form-control">
                            <option value="soltero/a">Soltero/a</option>
                            <option value="casado/a">Casado/a</option>
                            <option value="viudo/a">Viudo/a</option>
                            <option value="divorciado/a">Divorciado/a</option>
                            <option value="separado/a">Separado/a</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="IdColonia" class="form-label">Colonia</label>
                        <select name="IdColonia" id="IdColonia" class="form-control">
                            <?php while ($row = $resultColonias->fetch_assoc()) { ?>
                                <option value="<?php echo $row['IdColonia']; ?>"><?php echo $row['Nombre']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="IdMunicipio" class="form-label">Municipio</label>
                        <select name="IdMunicipio" id="IdMunicipio" class="form-control">
                            <?php while ($row = $resultMunicipios->fetch_assoc()) { ?>
                                <option value="<?php echo $row['IdMunicipio']; ?>"><?php echo $row['Nombre']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="IdPais" class="form-label">País</label>
                        <select name="IdPais" id="IdPais" class="form-control">
                            <?php while ($row = $resultPaises->fetch_assoc()) { ?>
                                <option value="<?php echo $row['IdPais']; ?>"><?php echo $row['Nombre']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="TelefonoMovil" class="form-label">Teléfono Móvil <span class="text-danger">*</span></label>
                        <input type="text" name="TelefonoMovil" id="TelefonoMovil" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="TelefonoParticular" class="form-label">Teléfono Particular</label>
                        <input type="text" name="TelefonoParticular" id="TelefonoParticular" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label for="CorreoElectronico" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="CorreoElectronico" id="CorreoElectronico" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label for="IdCarrera" class="form-label">Carrera</label>
                        <select name="IdCarrera" id="IdCarrera" class="form-control">
                            <?php while ($row = $resultCarreras->fetch_assoc()) { ?>
                                <option value="<?php echo $row['IdCarrera']; ?>"><?php echo $row['Nombre']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="IdEspecialidad" class="form-label">Especialidad</label>
                        <select name="IdEspecialidad" id="IdEspecialidad" class="form-control">
                            <?php while ($row = $resultEspecialidades->fetch_assoc()) { ?>
                                <option value="<?php echo $row['IdEspecialidad']; ?>"><?php echo $row['Nombre']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </main>

    <script src="./../assets/js/jquery.js"></script>
    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
</body>
<!-- Incluir el footer -->
<?php
    include_once __DIR__ . '/../includes/footer.php';
?>

</html>
