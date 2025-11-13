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

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $estadoLogro = $_POST['estadoLogro'];
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = ($estadoLogro == 'Finalizado') ? $_POST['fechaFin'] : null;
    $lugar = $_POST['lugar'];
    $descripcionActividades = $_POST['descripcionActividades'];

    // Obtener el tipo de logro desde la URL
    $tipoLogro = isset($_GET['tipoLogro']) ? $_GET['tipoLogro'] : null;

    if (!$tipoLogro) {
        echo "<div class='alert alert-danger text-center'>Error: No se especificó el tipo de logro.</div>";
        exit;
    }

    // Insertar el logro en la base de datos
    $queryInsertarLogro = "INSERT INTO logrosprofesionales (IdEstudiante, TipoLogro, DescripcionActividades, LugarRealizo, FechaInicio, FechaFin, Estatus) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtInsertar = $db->prepare($queryInsertarLogro);
    $stmtInsertar->bind_param("issssss", $idEstudiante, $tipoLogro, $descripcionActividades, $lugar, $fechaInicio, $fechaFin, $estadoLogro);
    
    if ($stmtInsertar->execute()) {
        // Redirigir a la página logros-profesionales.php
        header("Location: logros-profesionales.php");
        exit();
    } else {
        echo "<div class='alert alert-danger text-center'>Error al agregar el logro.</div>";
    }
    $stmtInsertar->close();
}

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logros Profesionales</title>
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
    // Solo incluir la sidebar si no es este archivo
    if (basename($_SERVER['PHP_SELF']) !== 'registro-logro.php') {
        include_once __DIR__ . '/../includes/sidebar-estudiante.php';
    }
    ?>

    <main class="container my-5">
        <div class="form-container">
            <h2 class="fw-bold">Agregar Logro Profesional</h2>
            <form action="registro-logro.php?tipoLogro=<?php echo htmlspecialchars($_GET['tipoLogro'] ?? ''); ?>" method="POST">
                <div class="mb-3">
                    <label for="lugar" class="form-label">Lugar donde se realizó (Empresa o institución):</label>
                    <input type="text" id="lugar" name="lugar" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="descripcionActividades" class="form-label">Descripción de actividades:</label>
                    <textarea id="descripcionActividades" name="descripcionActividades" class="form-control" rows="4" required></textarea>
                    <small class="form-text">Es importante describir detalladamente las actividades que realizas para que la empresa te tome en cuenta.</small>
                </div>

                <div class="mb-3">
                    <label for="estadoLogro" class="form-label">Estado:</label>
                    <select id="estadoLogro" name="estadoLogro" class="form-select" required>
                        <option value="En curso">En curso</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="fechaInicio" class="form-label">Fecha de inicio:</label>
                    <input type="date" id="fechaInicio" name="fechaInicio" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="fechaFin" class="form-label">Fecha de término:</label>
                    <input type="date" id="fechaFin" name="fechaFin" class="form-control" disabled>
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
