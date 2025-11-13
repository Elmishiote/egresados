<?php
// developers.php

// Título de la página
$title = "Equipo de Desarrollo";

// Incluir el header
include_once __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .developer-card {
            margin: 10px 0;
            padding: 10px;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .developer-card h3 {
            color: #333;
        }
        .developer-card p {
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-4"><?php echo $title; ?></h1>
        <div class="row">
            <!-- Developer 1 -->
            <div class="col-md-3 col-sm-6">
                <div class="developer-card text-center">
                    <h3>Leonardo Cortés Vergara</h3>
                    <p>Contacto: </p>
                </div>
            </div>
            <!-- Developer 2 -->
            <div class="col-md-3 col-sm-6">
                <div class="developer-card text-center">
                    <h3>Pablo Xiuhnel Pérez Amaya Arellano</h3>
                    <p>Contacto: </p>
                </div>
            </div>
            <!-- Developer 3 -->
            <div class="col-md-3 col-sm-6">
                <div class="developer-card text-center">
                    <h3>Jesús Alberto Estrada Pérez</h3>
                    <p>Contacto: </p>
                </div>
            </div>
            <!-- Developer 4 -->
            <div class="col-md-3 col-sm-6">
                <div class="developer-card text-center">
                    <h3>Soriano Reyes Olivia</h3>
                    <p>Contacto: </p>
                </div>
            </div>
            <!-- Developer 5 -->
            <div class="col-md-3 col-sm-6">
                <div class="developer-card text-center">
                    <h3>Paulina Díaz Beltrán</h3>
                    <p>Contacto: </p>
                </div>
            </div>
            <!-- Developer 6 -->
            <div class="col-md-3 col-sm-6">
                <div class="developer-card text-center">
                    <h3>Amy Ramos</h3>
                    <p>Contacto: </p>
                </div>
            </div>
        </div>
        <div class="text-center mt-5">
            <!-- Enlace al índice en la raíz del proyecto -->
            <a href="../index.php" class="btn btn-primary">Volver al inicio</a>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <!-- Incluir el footer -->
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
