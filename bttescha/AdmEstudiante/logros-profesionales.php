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

// Obtener el ID del usuario desde la sesión
$idUsuario = $_SESSION['id_usuario']; 

// Obtener el IdEstudiante asociado al IdUsuario
$queryEstudiante = "SELECT IdEstudiante FROM estudiante WHERE IdUsuario = ?";
$stmt = $db->prepare($queryEstudiante);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$stmt->bind_result($idEstudiante);
$stmt->fetch();
$stmt->close();

// Verificar si el IdEstudiante fue obtenido
if (!$idEstudiante) {
    echo "<div class='alert alert-danger text-center'>No se pudo encontrar el estudiante asociado al usuario.</div>";
    exit;
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
        .logros-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            align-items: center;
            margin-top: 2rem;
        }

        .logro-box {
            width: 100%;
            max-width: 750px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }

        .logro-box h3 {
            font-size: 1.5em;
            color: #333;
            text-align: center;
            margin-bottom: 1rem;
        }

        .logro-box ul {
            list-style: none;
            padding: 0;
        }

        .list-group-item {
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
            border-radius: 5px;
            background-color: #fff;
        }

        .add-logro-container {
            text-align: center;
            margin: 30px 0;
        }

        .btn-add-logro {
            margin-top: 10px;
        }

        .estado-en-curso {
            background-color: #f7e50f;
            color: black;
            font-weight: bold;
        }

        .estado-terminado {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }

        .btn-editar {
            margin-top: 10px;
            margin-right: 5px;
        }

        .select-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .form-select {
            width: 250px;
        }
    </style>
</head>

<body>
    <?php
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-estudiante.php';
    ?>

    <main class="container my-5">
        <h2 class="fw-bold text-center">Mis Logros Profesionales</h2>

        <div class="add-logro-container">
            <form action="registro-logro.php" method="GET">
                <div class="select-container">
                    <label for="tipoLogro" class="me-3">Agregar nuevo logro:</label>
                    <select id="tipoLogro" name="tipoLogro" class="form-select" required>
                        <option value="" selected disabled>Selecciona un tipo de logro</option>
                        <?php
                        $tiposLogro = ['Servicio Social', 'Residencias Profesionales', 'Experiencia Laboral'];
                        foreach ($tiposLogro as $tipo) {
                            $disabled = "";
                            if ($tipo !== "Experiencia Laboral") {
                                // Verificar si ya hay un registro de este tipo de logro
                                $query = "SELECT * FROM logrosprofesionales WHERE IdEstudiante = ? AND TipoLogro = ?";
                                $stmt = $db->prepare($query);
                                $stmt->bind_param("is", $idEstudiante, $tipo);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $disabled = ($result->num_rows > 0) ? "disabled" : "";
                            }
                            echo "<option value='$tipo' $disabled>$tipo</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-primary btn-add-logro">Agregar</button>
                </div>
            </form>
        </div>

        <div class="logros-container">
            <?php
            foreach ($tiposLogro as $tipoLogro) {
                echo "<div class='logro-box'>";
                echo "<h3>$tipoLogro</h3>";
                echo "<ul class='list-group'>";

                $query = "SELECT * FROM logrosprofesionales WHERE IdEstudiante = ? AND TipoLogro = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("is", $idEstudiante, $tipoLogro);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($logro = $result->fetch_assoc()) {
                        $estadoClass = ($logro['Estatus'] == 'Finalizado') ? 'estado-terminado' : 'estado-en-curso';

                        echo "<li class='list-group-item'>";
                        echo "<div><strong>Lugar:</strong> " . htmlspecialchars($logro['LugarRealizo']) . "<br>";
                        echo "<strong>Descripción de actividades:</strong> " . htmlspecialchars($logro['DescripcionActividades']) . "<br>";
                        echo "<strong>Fecha de inicio:</strong> " . htmlspecialchars($logro['FechaInicio']) . "<br>";
                        echo "<strong>Fecha de término:</strong> " . htmlspecialchars($logro['FechaFin']) . "<br>";
                        echo "<strong>Estado:</strong> <span class='$estadoClass'>" . htmlspecialchars($logro['Estatus']) . "</span></div>";
                        echo "<div class='btn-group' role='group'>";
                        echo "<a href='editar-logro.php?idLogro=" . $logro['IdLogrosProfesionales'] . "' class='btn btn-warning btn-editar'>Editar</a>";
                        echo "</div>";
                        echo "</li>";
                    }
                } else {
                    echo "<p>No tienes logros de $tipoLogro registrados.</p>";
                }

                echo "</ul>";
                echo "</div>";
            }
            ?>
        </div>
    </main>

    <script src="./../assets/js/bootstrap.bundle.min.js"></script>
</body>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>


</html>
