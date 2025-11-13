<?php

require __DIR__ . "/../auth/php/functions.php";
require_once __DIR__ . "/../config/database.php";

is_logged_in();

// Conexión a la base de datos
$db = conectar_db();
if (!$db) {
    echo "<div class='alert alert-danger text-center'>Error al conectar con la base de datos.</div>";
    exit;
}

// Obtener el ID del estudiante desde la sesión
$idUsuario = $_SESSION['id_usuario']; 

// Obtener el IdEstudiante asociado al IdUsuario
$queryEstudiante = "SELECT IdEstudiante FROM estudiante WHERE IdUsuario = ?";
$stmt = $db->prepare($queryEstudiante);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$stmt->bind_result($idEstudiante);
$stmt->fetch();
$stmt->close();

if (!$idEstudiante) {
    echo "<div class='alert alert-danger text-center'>No se pudo encontrar el estudiante asociado al usuario.</div>";
    exit;
}

// Obtener el ID del logro a editar
$idLogro = $_GET['idLogro'] ?? null;
if (!$idLogro) {
    echo "<div class='alert alert-danger text-center'>No se especificó el logro a editar.</div>";
    exit;
}

// Obtener los datos del logro
$queryLogro = "SELECT * FROM logrosprofesionales WHERE IdLogrosProfesionales = ? AND IdEstudiante = ?";
$stmt = $db->prepare($queryLogro);
$stmt->bind_param("ii", $idLogro, $idEstudiante);
$stmt->execute();
$logro = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$logro) {
    echo "<div class='alert alert-danger text-center'>No se pudo encontrar el logro especificado.</div>";
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $estadoLogro = $_POST['estadoLogro'];
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = ($estadoLogro == 'Finalizado') ? $_POST['fechaFin'] : null;
    $lugar = $_POST['lugar'];
    $descripcionActividades = $_POST['descripcionActividades'];

    // Actualizar el logro en la base de datos
    $queryActualizarLogro = "UPDATE logrosprofesionales 
                             SET DescripcionActividades = ?, LugarRealizo = ?, FechaInicio = ?, FechaFin = ?, Estatus = ? 
                             WHERE IdLogrosProfesionales = ?";
    $stmtActualizar = $db->prepare($queryActualizarLogro);
    $stmtActualizar->bind_param("sssssi", $descripcionActividades, $lugar, $fechaInicio, $fechaFin, $estadoLogro, $idLogro);

    if ($stmtActualizar->execute()) {
        header("Location: logros-profesionales.php");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center'>Error al actualizar el logro.</div>";
    }
    $stmtActualizar->close();
}

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Logro Profesional</title>
    <link rel="stylesheet" href="./../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <!--<script src="https://kit.fontawesome.com/41bcea2ae3.js" crossorigin="anonymous"></script> -->
    <style>
        .form-container {
            max-width: 800px;
            margin: auto;
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            font-size: 1.75em;
            color: #343a40;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .form-control {
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .form-text {
            font-size: 0.875em;
            color: #6c757d;
        }
    

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>

<body>
    <?php
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-estudiante.php';
    ?>

    <main class="container my-5">
        <div class="form-container">
            <h2 class="fw-bold">Editar Logro Profesional</h2>
            <form action="editar-logro.php?idLogro=<?php echo $idLogro; ?>" method="POST">
                <div class="mb-3">
                    <label for="lugar" class="form-label">Lugar donde se realizó (Empresa o institución):</label>
                    <input type="text" id="lugar" name="lugar" class="form-control" value="<?php echo htmlspecialchars($logro['LugarRealizo']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="descripcionActividades" class="form-label">Descripción de actividades:</label>
                    <textarea id="descripcionActividades" name="descripcionActividades" class="form-control" rows="4" required><?php echo htmlspecialchars($logro['DescripcionActividades']); ?></textarea>
                    <small class="form-text">Es importante describir detalladamente las actividades que realizas para que la empresa te tome en cuenta.</small>
                </div>

                <div class="mb-3">
                    <label for="estadoLogro" class="form-label">Estado:</label>
                    <select id="estadoLogro" name="estadoLogro" class="form-select" required>
                        <option value="En curso" <?php echo ($logro['Estatus'] == 'En curso') ? 'selected' : ''; ?>>En curso</option>
                        <option value="Finalizado" <?php echo ($logro['Estatus'] == 'Finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="fechaInicio" class="form-label">Fecha de inicio:</label>
                    <input type="date" id="fechaInicio" name="fechaInicio" class="form-control" value="<?php echo htmlspecialchars($logro['FechaInicio']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="fechaFin" class="form-label">Fecha de término:</label>
                    <input type="date" id="fechaFin" name="fechaFin" class="form-control" value="<?php echo ($logro['FechaFin']) ? htmlspecialchars($logro['FechaFin']) : ''; ?>" <?php echo ($logro['Estatus'] == 'En curso') ? 'disabled' : ''; ?>>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="logros-profesionales.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('estadoLogro').addEventListener('change', function () {
            let estado = this.value;
            let fechaFinField = document.getElementById('fechaFin');
            let fechaInicio = document.getElementById('fechaInicio').value;

            if (estado === 'En curso') {
                fechaFinField.disabled = true;
                fechaFinField.value = '';
            } else {
                fechaFinField.disabled = false;
                if (fechaInicio) {
                    validarFechaTermino(fechaInicio);
                }
            }
        });

        document.getElementById('fechaInicio').addEventListener('change', function () {
            let fechaInicio = this.value;
            let fechaFinField = document.getElementById('fechaFin');

            if (!fechaFinField.disabled) {
                validarFechaTermino(fechaInicio);
            }
        });

        function validarFechaTermino(fechaInicio) {
            let fechaFinField = document.getElementById('fechaFin');
            let fechaInicioObj = new Date(fechaInicio);
            fechaInicioObj.setMonth(fechaInicioObj.getMonth() + 6);

            let fechaLimite = fechaInicioObj.toISOString().split('T')[0];
            fechaFinField.setAttribute('min', fechaLimite);

            if (fechaFinField.value && fechaFinField.value < fechaLimite) {
                fechaFinField.value = '';
                alert('La fecha de término debe ser al menos 6 meses después de la fecha de inicio.');
            }
        }
    </script>

    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
</body>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>

</html>
