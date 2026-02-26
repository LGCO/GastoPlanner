<?php
// Configuración de la ruta del archivo de datos
$dataFile = __DIR__ . '/datos.json';

// Inicializar datos si no existen
if (!file_exists($dataFile)) {
    $initialData = [];
    file_put_contents($dataFile, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Función para leer datos
function leerDatos() {
    global $dataFile;
    $content = file_get_contents($dataFile);
    $data = json_decode($content, true) ?: [];
    // Normalizar gastosFijos a array si es objeto
    if (isset($data['gastosFijos']) && !is_array($data['gastosFijos'])) {
        $data['gastosFijos'] = [];
    } elseif (isset($data['gastosFijos']) && array_values($data['gastosFijos']) !== $data['gastosFijos']) {
        $data['gastosFijos'] = array_values($data['gastosFijos']);
    }
    return $data;
}

// Función para guardar datos
function guardarDatos($datos) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Procesar solicitudes AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'agregarTransaccion') {
        $datos = leerDatos();
        $fecha = $_POST['fecha'] ?? '';
        $tipo = $_POST['tipo'] ?? 'gasto'; // 'gasto' o 'ingreso'
        $cantidad = floatval($_POST['cantidad'] ?? 0);
        $descripcion = $_POST['descripcion'] ?? '';
        
        if (!isset($datos[$fecha])) {
            $datos[$fecha] = [];
        }
        
        $nuevaTransaccion = [
            'id' => uniqid(),
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'descripcion' => $descripcion,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        $datos[$fecha][] = $nuevaTransaccion;

        guardarDatos($datos);

        echo json_encode(['success' => true, 'mensaje' => 'Transacción agregada correctamente', 'transaccion' => $nuevaTransaccion]);
        exit;
    }
    
    if ($action === 'eliminarTransaccion') {
        $datos = leerDatos();
        $fecha = $_POST['fecha'] ?? '';
        $id = $_POST['id'] ?? '';
        
        if (isset($datos[$fecha])) {
            $datos[$fecha] = array_filter($datos[$fecha], function($t) use ($id) {
                return $t['id'] !== $id;
            });
            if (empty($datos[$fecha])) {
                unset($datos[$fecha]);
            }
        }
        
        guardarDatos($datos);
        echo json_encode(['success' => true, 'mensaje' => 'Transacción eliminada']);
        exit;
    }

    if ($action === 'editarTransaccion') {
        $datos = leerDatos();
        $fecha = $_POST['fecha'] ?? '';
        $id = $_POST['id'] ?? '';
        $tipo = $_POST['tipo'] ?? 'gasto';
        $cantidad = floatval($_POST['cantidad'] ?? 0);
        $descripcion = $_POST['descripcion'] ?? '';

        if (isset($datos[$fecha])) {
            foreach ($datos[$fecha] as &$t) {
                if ($t['id'] === $id) {
                    $t['tipo'] = $tipo;
                    $t['cantidad'] = $cantidad;
                    $t['descripcion'] = $descripcion;
                    $t['timestamp'] = date('Y-m-d H:i:s');
                    guardarDatos($datos);
                    echo json_encode(['success' => true, 'mensaje' => 'Transacción actualizada', 'transaccion' => $t]);
                    exit;
                }
            }
        }

        echo json_encode(['success' => false, 'mensaje' => 'Transacción no encontrada']);
        exit;
    }
    
    if ($action === 'obtenerTransacciones') {
        $datos = leerDatos();
        $fecha = $_POST['fecha'] ?? '';
        $transacciones = $datos[$fecha] ?? [];
        echo json_encode(['transacciones' => $transacciones]);
        exit;
    }
    
    if ($action === 'agregarGastoFijo') {
        $datos = leerDatos();
        $gastosFijos = $datos['gastosFijos'] ?? [];

        $dias = $_POST['dias'] ?? [];
        $diaMes = isset($_POST['diaMes']) ? intval($_POST['diaMes']) : null;
        $cantidad = floatval($_POST['cantidad'] ?? 0);
        $descripcion = $_POST['descripcion'] ?? '';
        $tipo = $_POST['tipo'] ?? 'gasto';
        $fechaInicio = $_POST['fechaInicio'] ?? null;
        $fechaFin = $_POST['fechaFin'] ?? null;
        $editarId = $_POST['editarId'] ?? null;

        if ($editarId) {
            // Editar existente
            foreach ($gastosFijos as &$gf) {
                if (isset($gf['id']) && $gf['id'] === $editarId) {
                    $gf['dias'] = $dias;
                    $gf['diaMes'] = $diaMes;
                    $gf['cantidad'] = $cantidad;
                    $gf['descripcion'] = $descripcion;
                    $gf['tipo'] = $tipo;
                    $gf['fechaInicio'] = $fechaInicio;
                    $gf['fechaFin'] = $fechaFin;
                    $gf['activo'] = true;
                }
            }
        } else {
            // Nuevo gasto fijo
            $gastoFijo = [
                'id' => uniqid(),
                'dias' => $dias, // Array con días del 0-6 (domingo a sábado)
                'diaMes' => $diaMes,
                'cantidad' => $cantidad,
                'descripcion' => $descripcion,
                'tipo' => $tipo,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'activo' => true
            ];
            $gastosFijos[] = $gastoFijo;
        }

        $datos['gastosFijos'] = $gastosFijos;
        guardarDatos($datos);

        echo json_encode(['success' => true, 'mensaje' => 'Gasto fijo agregado correctamente']);
        exit;
    }
    
    if ($action === 'obtenerGastosFijos') {
        $datos = leerDatos();
        $gastosFijos = $datos['gastosFijos'] ?? [];
        echo json_encode(['gastosFijos' => $gastosFijos]);
        exit;
    }
    
    if ($action === 'obtenerTodosDatos') {
        $datos = leerDatos();
        echo json_encode($datos);
        exit;
    }
    
    if ($action === 'cargarEjemplos') {
        $rutaEjemplo = __DIR__ . '/datos_ejemplo.json';
        if (file_exists($rutaEjemplo)) {
            $datosEjemplo = json_decode(file_get_contents($rutaEjemplo), true);
            if ($datosEjemplo) {
                guardarDatos($datosEjemplo);
                echo json_encode(['success' => true, 'mensaje' => 'Datos de ejemplo cargados correctamente']);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo decodificar el archivo de ejemplos']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Archivo de ejemplos no encontrado']);
        }
        exit;
    }
    
    if ($action === 'limpiarDatos') {
        guardarDatos([]);
        echo json_encode(['success' => true, 'mensaje' => 'Todos los datos han sido eliminados']);
        exit;
    }
    
    if ($action === 'eliminarGastoFijo') {
        $datos = leerDatos();
        $id = $_POST['id'] ?? '';

        $gastosFijos = $datos['gastosFijos'] ?? [];
        // Soportar tanto array como objeto asociativo
        if (array_values($gastosFijos) === $gastosFijos) {
            // Es array
            $datos['gastosFijos'] = array_values(array_filter($gastosFijos, function($g) use ($id) {
                return $g['id'] !== $id;
            }));
        } else {
            // Es objeto asociativo
            foreach ($gastosFijos as $k => $g) {
                if (isset($g['id']) && $g['id'] === $id) {
                    unset($gastosFijos[$k]);
                }
            }
            $datos['gastosFijos'] = $gastosFijos;
        }

        guardarDatos($datos);
        echo json_encode(['success' => true, 'mensaje' => 'Gasto fijo eliminado']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Financiero <span id="tituloAnio"></span></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="container-fluid">
        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-primary sticky-top">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">
                    <i class="bi bi-calendar-event"></i> Calendario Financiero <span id="tituloAnioNav"></span>
                </span>
                <div class="d-flex gap-2">
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#modalGastoFijo">
                        <i class="bi bi-plus-circle"></i> Agregar Movimiento Fijo
                    </button>
                    <a href="gestor_datos.php" class="btn btn-info">
                        <i class="bi bi-gear"></i> Configurar Datos
                    </a>
                </div>
            </div>
        </nav>

        <!-- Contenedor principal -->
        <div class="container-lg mt-4">
            <!-- Selector de mes -->
            <div class="row mb-4">
                <div class="">
                    <div class="w-50">
                        <div class="input-group mb-3">
                            <label class="input-group-text" for="selectorMes">Mes:</label>
                            <select class="form-select" id="selectorMes">
                                <option value="0">Enero</option>
                                <option value="1">Febrero</option>
                                <option value="2">Marzo</option>
                                <option value="3">Abril</option>
                                <option value="4">Mayo</option>
                                <option value="5">Junio</option>
                                <option value="6">Julio</option>
                                <option value="7">Agosto</option>
                                <option value="8">Septiembre</option>
                                <option value="9">Octubre</option>
                                <option value="10">Noviembre</option>
                                <option value="11">Diciembre</option>
                            </select>
                            <label class="input-group-text ms-2" for="selectorAnio">Año:</label>
                            <select class="form-select" id="selectorAnio"></select>
                        </div>
                    </div>
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Resumen del Mes</h5>
                            <div class="row g-2 align-items-center">
                                <div class="col-6 col-md-4">
                                    <p class="mb-1"><strong>Balance Anterior:</strong><br><span id="balanceAnterior" class="text-secondary">$0.00</span></p>
                                </div>
                                <div class="col-6 col-md-4">
                                    <p class="mb-1"><strong>Ingresos:</strong><br><span class="text-success" id="totalIngresos">$0.00</span></p>
                                </div>
                                <div class="col-6 col-md-4">
                                    <p class="mb-1"><strong>Gastos:</strong><br><span class="text-danger" id="totalGastos">$0.00</span></p>
                                </div>
                                <div class="col-12 col-md-4 mt-2">
                                    <hr class="my-2">
                                    <p class="mb-0"><strong>Balance:</strong><br><span id="totalBalance">$0.00</span></p>
                                </div>
                                <div class="col-12 col-md-8 mt-2">
                                    <div id="minBalanceMes"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Calendario -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 id="mesAnio" class="mb-0"></h4>
                </div>
                <div class="card-body">
                    <!-- Encabezados de días de semana -->
                    <div class="calendario-semana text-center mb-2">
                        <div class="dia-semana">Domingo</div>
                        <div class="dia-semana">Lunes</div>
                        <div class="dia-semana">Martes</div>
                        <div class="dia-semana">Miércoles</div>
                        <div class="dia-semana">Jueves</div>
                        <div class="dia-semana">Viernes</div>
                        <div class="dia-semana">Sábado</div>
                    </div>

                    <!-- Días del calendario -->
                    <div class="calendario-grid" id="calendarioGrid">
                        <!-- Se llenará con JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Resumen de gastos fijos -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-repeat"></i> Gastos Fijos Configurados</h5>
                        </div>
                        <div class="card-body">
                            <div id="listaGastosFijos">
                                <p class="text-muted">No hay gastos fijos configurados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar transacción -->
    <div class="modal fade" id="modalTransaccion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Transacción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formTransaccion">
                        <div class="mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="text" class="form-control" id="fechaTransaccion" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" id="tipoTransaccion">
                                <option value="ingreso">Ingreso</option>
                                <option value="gasto">Gasto</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidadTransaccion" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="descripcionTransaccion" placeholder="Ej: Almuerzo, Salario, etc.">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarTransaccion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar movimiento fijo -->
    <div id="modalGastoFijo" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Movimiento Fijo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formGastoFijo">
                        <div class="mb-3">
                            <label class="form-label">Tipo</label>
                            <select class="form-select" id="tipoGastoFijo">
                                <option value="ingreso">Ingreso</option>
                                <option value="gasto">Gasto</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidadGastoFijo" placeholder="0.00" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="descripcionGastoFijo" placeholder="Ej: Transporte, Almuerzo diario, etc." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Seleccionar días de la semana</label>
                            <div class="diasFijos mb-2">
                                <div class="form-check">
                                    <input class="form-check-input diasCheck" type="checkbox" value="0" id="domingo">
                                    <label class="form-check-label" for="domingo">Domingo</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input diasCheck" type="checkbox" value="1" id="lunes">
                                    <label class="form-check-label" for="lunes">Lunes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input diasCheck" type="checkbox" value="2" id="martes">
                                    <label class="form-check-label" for="martes">Martes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input diasCheck" type="checkbox" value="3" id="miercoles">
                                    <label class="form-check-label" for="miercoles">Miércoles</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input diasCheck" type="checkbox" value="4" id="jueves">
                                    <label class="form-check-label" for="jueves">Jueves</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input diasCheck" type="checkbox" value="5" id="viernes">
                                    <label class="form-check-label" for="viernes">Viernes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input diasCheck" type="checkbox" value="6" id="sabado">
                                    <label class="form-check-label" for="sabado">Sábado</label>
                                </div>
                            </div>
                            <label class="form-label">O seleccionar día del mes (1-31)</label>
                            <input type="number" class="form-control mb-2" id="diaMesGastoFijo" min="1" max="31" placeholder="Ej: 15">
                            <label class="form-label">Fecha inicio</label>
                            <input type="date" class="form-control mb-2" id="fechaInicioGastoFijo" placeholder="YYYY-MM-DD">
                            <label class="form-label">Fecha fin</label>
                            <input type="date" class="form-control mb-2" id="fechaFinGastoFijo" placeholder="YYYY-MM-DD">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarGastoFijo">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver transacciones del día -->
    <div class="modal fade" id="modalTransacciones" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloTransacciones"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="listadoTransacciones">
                        <!-- Se llenará con JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
