<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Datos - Calendario Financiero 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .btn-custom {
            padding: 10px 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🛠️ Gestor de Datos - Calendario Financiero 2026</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Opciones de Gestión de Datos</h5>
                        
                        <div class="alert alert-info" role="alert">
                            <strong>💡 Información:</strong> Usa estas opciones para gestionar tus datos.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">📊 Cargar Datos de Ejemplo</h6>
                                        <p class="card-text text-muted small">Carga datos de ejemplo para ver cómo se vería la aplicación con transacciones.'</p>
                                        <button class="btn btn-primary btn-custom w-100" onclick="cargarEjemplos()">
                                            Cargar Ejemplos
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">🗑️ Limpiar Todos los Datos</h6>
                                        <p class="card-text text-muted small">Elimina todos los datos y comienza desde cero.</p>
                                        <button class="btn btn-danger btn-custom w-100" onclick="limpiarDatos()">
                                            Limpiar Datos
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">📥 Descargar Datos (JSON)</h6>
                                        <p class="card-text text-muted small">Descarga tus datos en formato JSON para hacer backup.</p>
                                        <button class="btn btn-success btn-custom w-100" onclick="descargarDatos()">
                                            Descargar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">📥 Cargar Datos (JSON)</h6>
                                        <p class="card-text text-muted small">Carga tus datos desde un archivo JSON para restaurar un backup. Esto sobrescribirá los datos actuales.</p>
                                        <form method="post" enctype="multipart/form-data" onsubmit="return confirmarCargaRespaldo();">
                                            <input type="file" name="archivo_json" accept=".json" class="form-control mb-2" required>
                                            <button class="btn btn-success btn-custom w-100" type="submit" name="cargar_json">Cargar Archivo</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </nav>

                        <?php
                        if (isset($_POST['cargar_json']) && isset($_FILES['archivo_json'])) {
                            $archivo = $_FILES['archivo_json'];
                            if ($archivo['error'] === UPLOAD_ERR_OK) {
                                $contenido = file_get_contents($archivo['tmp_name']);
                                // Validar que sea JSON válido
                                json_decode($contenido);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    if (file_put_contents('datos.json', $contenido) !== false) {
                                        echo '<script>
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    mostrarEstado("✅ Éxito", "El archivo de datos se cargó correctamente. Redirigiendo...", "success");
                                                    setTimeout(function(){ window.location.href = "index.php"; }, 2000);
                                                });
                                            </script>';
                                    } else {
                                        echo '<script>
                                                document.addEventListener("DOMContentLoaded", function() {
                                                    mostrarEstado("❌ Error", "No se pudo guardar el archivo de datos.", "danger");
                                                });
                                            </script>';
                                    }
                                } else {
                                    echo '<script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                mostrarEstado("❌ Error", "El archivo no es un JSON válido.", "danger");
                                            });
                                        </script>';
                                }
                            } else {
                                echo '<script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            mostrarEstado("❌ Error", "Error al subir el archivo.", "danger");
                                        });
                                    </script>';
                            }
                        }
                        ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">📋 Ver Estado del Sistema</h6>
                                        <p class="card-text text-muted small">Verifica que todos los componentes estén funcionando correctamente.</p>
                                        <a href="verificar.php" class="btn btn-warning btn-custom w-100">
                                            Ver Verificación
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">➡️ Volver a la Aplicación</h6>
                                        <p class="card-text text-muted small">Regresa al calendario para gestionar tus finanzas.</p>
                                        <button class="btn btn-secondary btn-custom w-100" onclick="window.location.href='index.php'">
                                            Ir a la Aplicación
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div id="estado" class="alert alert-info d-none" role="alert">
                            <strong id="estadoTitulo"></strong>
                            <p id="estadoMensaje" class="mb-0"></p>
                        </div>

                        <div class="card bg-light mt-3">
                            <div class="card-body">
                                <h6 class="card-title">📋 Información de Datos</h6>
                                <p class="card-text small">
                                    <strong>Archivo de datos:</strong> datos.json<br>
                                    <strong>Ubicación:</strong> Misma carpeta que la aplicación<br>
                                    <strong>Estado:</strong> <span id="estadoDatos" class="badge bg-success">Listo</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarEstado(titulo, mensaje, tipo = 'success') {
            const estado = document.getElementById('estado');
            const estadoTitulo = document.getElementById('estadoTitulo');
            const estadoMensaje = document.getElementById('estadoMensaje');
            
            estado.className = `alert alert-${tipo}`;
            estadoTitulo.textContent = titulo;
            estadoMensaje.textContent = mensaje;
            estado.classList.remove('d-none');
        }

        function cargarEjemplos() {
            if (!confirm('¿Estás seguro? Esto sobrescribirá los datos actuales.')) {
                return;
            }

            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=cargarEjemplos'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarEstado('✅ Éxito', 'Los datos de ejemplo se han cargado correctamente. Abre la aplicación para verlos.');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    mostrarEstado('❌ Error', data.error || 'Error al cargar los datos.', 'danger');
                }
            })
            .catch(error => {
                mostrarEstado('❌ Error', 'Error de conexión: ' + error, 'danger');
            });
        }

        function limpiarDatos() {
            if (!confirm('⚠️ ¿Estás seguro? Esto eliminará TODOS los datos (ingresos, gastos y gastos fijos).')) {
                return;
            }
            
            if (!confirm('Esta acción no se puede deshacer. ¿Continuar?')) {
                return;
            }

            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=limpiarDatos'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarEstado('✅ Éxito', 'Todos los datos han sido eliminados correctamente.');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    mostrarEstado('❌ Error', data.error || 'Error al limpiar los datos.', 'danger');
                }
            })
            .catch(error => {
                mostrarEstado('❌ Error', 'Error de conexión: ' + error, 'danger');
            });
        }

        function descargarDatos() {
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=obtenerTodosDatos'
            })
            .then(response => response.json())
            .then(data => {
                const dataStr = JSON.stringify(data, null, 2);
                const dataBlob = new Blob([dataStr], { type: 'application/json' });
                const url = URL.createObjectURL(dataBlob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'datos_respaldo_' + new Date().toISOString().split('T')[0] + '.json';
                link.click();
                URL.revokeObjectURL(url);
                
                mostrarEstado('✅ Éxito', 'Los datos se han descargado correctamente.');
            })
            .catch(error => {
                mostrarEstado('❌ Error', 'Error al descargar los datos: ' + error, 'danger');
            });
        }

        function confirmarCargaRespaldo() {
            return confirm('⚠️ Cargar un archivo JSON sobrescribirá los datos actuales. ¿Estás seguro de que deseas continuar?');
        }
    </script>
</body>
</html>
