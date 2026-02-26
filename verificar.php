<?php
/**
 * Script de Verificación - Calendario Financiero 2026
 * 
 * Este script verifica que todo esté configurado correctamente
 */

$errores = [];
$avisos = [];
$exitos = [];

// 1. Verificar PHP
if (version_compare(PHP_VERSION, '7.4', '>=')) {
    $exitos[] = "✓ PHP versión " . PHP_VERSION . " (Requerido: 7.4+)";
} else {
    $errores[] = "✗ PHP versión " . PHP_VERSION . " - Se requiere 7.4 o superior";
}

// 2. Verificar permisos de directorio
$carpetaActual = __DIR__;
if (is_writable($carpetaActual)) {
    $exitos[] = "✓ La carpeta tiene permisos de escritura";
} else {
    $errores[] = "✗ La carpeta NO tiene permisos de escritura (necesario para guardar datos)";
}

// 3. Verificar archivos necesarios
$archivosNecesarios = ['index.php', 'script.js', 'estilos.css', 'gestor_datos.php'];
foreach ($archivosNecesarios as $archivo) {
    if (file_exists($carpetaActual . '/' . $archivo)) {
        $exitos[] = "✓ Archivo encontrado: $archivo";
    } else {
        $errores[] = "✗ Archivo no encontrado: $archivo";
    }
}

// 4. Verificar datos.json
if (file_exists($carpetaActual . '/datos.json')) {
    $exitos[] = "✓ Archivo de datos encontrado: datos.json";
    // Verificar que sea válido JSON
    $contenido = file_get_contents($carpetaActual . '/datos.json');
    $datos = json_decode($contenido, true);
    if ($datos !== null) {
        $exitos[] = "✓ Archivo JSON válido";
    } else {
        $avisos[] = "⚠ Archivo JSON corrupto o vacío (se reparará automáticamente)";
    }
} else {
    $avisos[] = "⚠ Archivo datos.json no existe (se creará en el primer uso)";
}

// 5. Verificar datos_ejemplo.json
if (file_exists($carpetaActual . '/datos_ejemplo.json')) {
    $exitos[] = "✓ Archivo de ejemplo encontrado: datos_ejemplo.json";
} else {
    $avisos[] = "⚠ Archivo datos_ejemplo.json no encontrado (no podrás cargar ejemplos)";
}

// 6. Verificar extensión JSON en PHP
if (extension_loaded('json')) {
    $exitos[] = "✓ Extensión JSON habilitada en PHP";
} else {
    $errores[] = "✗ Extensión JSON no habilitada en PHP";
}

// 7. Probar creación de archivo
$archivoTest = $carpetaActual . '/.test_write';
if (@file_put_contents($archivoTest, 'test')) {
    @unlink($archivoTest);
    $exitos[] = "✓ Se puede crear y eliminar archivos";
} else {
    $errores[] = "✗ No se pueden crear archivos en la carpeta";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación - Calendario Financiero 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
        }
        .resultado {
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
        .exito {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .aviso {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .estado-general {
            font-size: 18px;
            font-weight: bold;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .estado-ok {
            background-color: #d4edda;
            color: #155724;
        }
        .estado-advertencia {
            background-color: #fff3cd;
            color: #856404;
        }
        .estado-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .contador {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .botones {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> 
                            Verificación del Sistema - Calendario Financiero 2026
                        </h2>
                    </div>
                    <div class="card-body">
                        <!-- Estado General -->
                        <div class="estado-general <?php echo empty($errores) ? 'estado-ok' : (empty($exitos) ? 'estado-error' : 'estado-advertencia'); ?>">
                            <?php if (empty($errores) && !empty($exitos)): ?>
                                <i class="bi bi-check-circle"></i> Sistema Listo para Usar
                            <?php elseif (!empty($errores)): ?>
                                <i class="bi bi-exclamation-triangle"></i> Hay Errores Críticos
                            <?php else: ?>
                                <i class="bi bi-info-circle"></i> Sistema Operacional (con advertencias)
                            <?php endif; ?>
                        </div>

                        <!-- Contadores -->
                        <div class="row text-center mt-4">
                            <div class="col-md-3">
                                <div class="contador">
                                    <span style="color: #28a745;"><?php echo count($exitos); ?></span>
                                </div>
                                <p class="text-muted">Verificaciones Exitosas</p>
                            </div>
                            <div class="col-md-3">
                                <div class="contador">
                                    <span style="color: #dc3545;"><?php echo count($errores); ?></span>
                                </div>
                                <p class="text-muted">Errores Críticos</p>
                            </div>
                            <div class="col-md-3">
                                <div class="contador">
                                    <span style="color: #ffc107;"><?php echo count($avisos); ?></span>
                                </div>
                                <p class="text-muted">Advertencias</p>
                            </div>
                            <div class="col-md-3">
                                <div class="contador">
                                    <span style="color: #0d6efd;"><?php echo count($exitos) + count($errores) + count($avisos); ?></span>
                                </div>
                                <p class="text-muted">Verificaciones Totales</p>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Resultados Exitosos -->
                        <?php if (!empty($exitos)): ?>
                        <div>
                            <h5><i class="bi bi-check-circle"></i> Verificaciones Exitosas</h5>
                            <?php foreach ($exitos as $exito): ?>
                                <div class="resultado exito"><?php echo $exito; ?></div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <?php endif; ?>

                        <!-- Errores -->
                        <?php if (!empty($errores)): ?>
                        <div>
                            <h5><i class="bi bi-exclamation-triangle"></i> Errores Críticos</h5>
                            <?php foreach ($errores as $error): ?>
                                <div class="resultado error"><?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <?php endif; ?>

                        <!-- Avisos -->
                        <?php if (!empty($avisos)): ?>
                        <div>
                            <h5><i class="bi bi-info-circle"></i> Advertencias</h5>
                            <?php foreach ($avisos as $aviso): ?>
                                <div class="resultado aviso"><?php echo $aviso; ?></div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                        <?php endif; ?>

                        <!-- Información del Sistema -->
                        <div class="card bg-light mt-4">
                            <div class="card-body">
                                <h6 class="card-title">📊 Información del Sistema</h6>
                                <p class="card-text">
                                    <strong>Directorio:</strong> <?php echo $carpetaActual; ?><br>
                                    <strong>PHP:</strong> <?php echo PHP_VERSION; ?><br>
                                    <strong>Sistema Operativo:</strong> <?php echo PHP_OS; ?><br>
                                    <strong>Servidor Web:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'No disponible'; ?><br>
                                    <strong>Fecha/Hora:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="botones">
                            <?php if (empty($errores)): ?>
                                <a href="index.php" class="btn btn-success btn-lg w-100 mb-2">
                                    <i class="bi bi-play-circle"></i> Iniciar Aplicación
                                </a>
                            <?php else: ?>
                                <a href="verificar.php" class="btn btn-warning btn-lg w-100 mb-2">
                                    <i class="bi bi-arrow-clockwise"></i> Reintentar
                                </a>
                            <?php endif; ?>
                            
                            <a href="gestor_datos.php" class="btn btn-info btn-lg w-100">
                                <i class="bi bi-gear"></i> Configurar Datos
                            </a>
                        </div>

                        <!-- Recomendaciones -->
                        <div class="alert alert-info mt-4" role="alert">
                            <strong><i class="bi bi-lightbulb"></i> Recomendaciones:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Mantén los archivos de la aplicación en un lugar seguro</li>
                                <li>Haz backups regularmente de tu archivo datos.json</li>
                                <li>Verifica los permisos de carpeta si experimentas problemas</li>
                                <li>Usa un navegador moderno para la mejor experiencia</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
