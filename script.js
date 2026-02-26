// Variables globales
let mesActual = new Date().getMonth();
let anioActual = new Date().getFullYear();
let datos = {};
let gastosFijos = [];
let editarTransaccionId = null;
const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const nombresMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    const hoy = new Date();
    document.getElementById('selectorMes').value = hoy.getMonth().toString();
    mesActual = hoy.getMonth();
    anioActual = hoy.getFullYear();
    // Inicializar selector de año
    inicializarSelectorAnio();
    configurarEventos();
    actualizarTituloAnio();
    cargarTodosDatos();
    // Mostrar calendario aunque el selector de año esté vacío
    setTimeout(() => {
        if (!document.getElementById('selectorAnio').value) {
            document.getElementById('selectorAnio').value = anioActual;
            actualizarTituloAnio();
        }
        actualizarCalendario();
        calcularResumenMes();
    }, 100);
});

function actualizarTituloAnio() {
    const selectorAnio = document.getElementById('selectorAnio');
    const anio = selectorAnio ? selectorAnio.value : anioActual;
    const tituloAnio = document.getElementById('tituloAnio');
    const tituloAnioNav = document.getElementById('tituloAnioNav');
    if (tituloAnio) tituloAnio.textContent = anio;
    if (tituloAnioNav) tituloAnioNav.textContent = anio;
}

function inicializarSelectorAnio() {
    const selectorAnio = document.getElementById('selectorAnio');
    selectorAnio.innerHTML = '';
    // Rango de años: desde 2020 hasta 2030 (puedes ajustar)
    for (let y = 2020; y <= 2030; y++) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        if (y === anioActual) opt.selected = true;
        selectorAnio.appendChild(opt);
    }
}

// Configurar eventos de los elementos
function configurarEventos() {
    document.getElementById('selectorMes').addEventListener('change', (e) => {
        mesActual = parseInt(e.target.value);
        actualizarCalendario();
        calcularResumenMes();
    });
    document.getElementById('selectorAnio').addEventListener('change', (e) => {
        anioActual = parseInt(e.target.value);
        actualizarCalendario();
        calcularResumenMes();
        actualizarTituloAnio();
    });
    document.getElementById('btnGuardarTransaccion').addEventListener('click', guardarTransaccion);
    document.getElementById('btnGuardarGastoFijo').addEventListener('click', guardarGastoFijo);
}

// Generar el calendario
function actualizarCalendario() {
    const calendarioGrid = document.getElementById('calendarioGrid');
    calendarioGrid.innerHTML = '';

    const primerDia = new Date(anioActual, mesActual, 1);
    const ultimoDia = new Date(anioActual, mesActual + 1, 0);
    const diasDelMes = ultimoDia.getDate();
    const primerDiaDelMes = primerDia.getDay();

    document.getElementById('mesAnio').textContent = `${nombresMeses[mesActual]} ${anioActual}`;

    // Agregar días del mes anterior
    const diasMesAnterior = new Date(anioActual, mesActual, 0).getDate();
    for (let i = primerDiaDelMes - 1; i >= 0; i--) {
        const dia = diasMesAnterior - i;
        const diaElement = crearElementoDia(dia, mesActual - 1, true, anioActual);
        calendarioGrid.appendChild(diaElement);
    }

    // Agregar días del mes actual
    for (let i = 1; i <= diasDelMes; i++) {
        const diaElement = crearElementoDia(i, mesActual, false, anioActual);
        calendarioGrid.appendChild(diaElement);
    }

    // Agregar solo los días necesarios del mes siguiente para completar la última semana
    const totalCeldas = calendarioGrid.children.length;
    const restoSemana = totalCeldas % 7;
    const celdasFaltantes = restoSemana === 0 ? 0 : 7 - restoSemana;
    for (let i = 1; i <= celdasFaltantes; i++) {
        const diaElement = crearElementoDia(i, mesActual + 1, true, anioActual);
        calendarioGrid.appendChild(diaElement);
    }

    cargarGastosFijos();
}

// Crear elemento de día del calendario
function crearElementoDia(dia, mes, esOtroMes, anio) {
    const contenedor = document.createElement('div');
    contenedor.className = 'dia-calendario';

    if (esOtroMes) {
        contenedor.classList.add('otro-mes');
    }

    // Comprobador si es hoy
    const hoy = new Date();
    anio = typeof anio !== 'undefined' ? anio : anioActual;
    if (!esOtroMes && dia === hoy.getDate() && mes === hoy.getMonth() && hoy.getFullYear() === anio) {
        contenedor.classList.add('dia-hoy');
    }

    const fechaObj = new Date(anio, mes, dia);
    const fechaStr = fechaObj.toISOString().split('T')[0];

    // Número del día
    const numeroDia = document.createElement('div');
    numeroDia.className = 'dia-numero';
    numeroDia.textContent = dia;
    contenedor.appendChild(numeroDia);

    // Contenedor de transacciones
    const transaccionesDiv = document.createElement('div');
    transaccionesDiv.className = 'transacciones-resumen';

    // Obtener transacciones del día
    if (Array.isArray(datos[fechaStr])) {
        datos[fechaStr].forEach(t => {
            const itemDiv = document.createElement('div');
            itemDiv.className = `transaccion-item transaccion-${t.tipo}`;

            const simbolo = t.tipo === 'ingreso' ? '+' : '-';
            const texto = document.createElement('span');
            texto.style.flex = '1';
            texto.style.display = 'inline-block';
            texto.style.overflow = 'hidden';
            texto.style.textOverflow = 'ellipsis';
            texto.innerHTML = `<i class=\"bi bi-circle-fill\" style=\"font-size: 6px; margin-right: 6px;\"></i>${simbolo}$${t.cantidad.toFixed(2)} <small style=\"color:#666; margin-left:6px;\">${t.descripcion || ''}</small>`;

            const btnEditar = document.createElement('button');
            btnEditar.className = 'btn-transaccion-ico btn-editar';
            btnEditar.title = 'Editar';
            btnEditar.innerHTML = '<i class=\"bi bi-pencil-fill\"></i>';
            btnEditar.addEventListener('click', (e) => {
                e.stopPropagation();
                abrirModalTransaccion(fechaStr, t);
            });

            const btnEliminar = document.createElement('button');
            btnEliminar.className = 'btn-transaccion-ico btn-eliminar';
            btnEliminar.title = 'Eliminar';
            btnEliminar.innerHTML = '<i class=\"bi bi-trash-fill\"></i>';
            btnEliminar.addEventListener('click', (e) => {
                e.stopPropagation();
                if (confirm('¿Eliminar esta transacción?')) {
                    eliminarTransaccion(t.id, fechaStr);
                }
            });

            itemDiv.appendChild(texto);
            itemDiv.appendChild(btnEditar);
            itemDiv.appendChild(btnEliminar);
            transaccionesDiv.appendChild(itemDiv);
        });
    }

    // Agregar gastos fijos del día
    const diaSemana = fechaObj.getDay();
    const ultimoDiaMes = new Date(2026, mes, 0).getDate();
    gastosFijos.forEach(gf => {
        // Validar rango de fechas
        let aplica = true;
        if (gf.fechaInicio) {
            aplica = aplica && (fechaStr >= gf.fechaInicio);
        }
        if (gf.fechaFin) {
            aplica = aplica && (fechaStr <= gf.fechaFin);
        }
        // Por día de semana
        if (aplica && gf.activo && Array.isArray(gf.dias) && gf.dias.includes(diaSemana.toString())) {
            const itemDiv = document.createElement('div');
            itemDiv.className = `transaccion-item transaccion-${gf.tipo}`;
            const simbolo = gf.tipo === 'ingreso' ? '+' : '-';
            itemDiv.innerHTML = `<i class="bi bi-circle-fill" style="font-size: 6px; margin-right: 4px;"></i>${simbolo}$${gf.cantidad.toFixed(2)}`;
            itemDiv.style.opacity = '0.7';
            itemDiv.title = 'Gasto Fijo';
            transaccionesDiv.appendChild(itemDiv);
        }
        // Por día del mes
        if (aplica && gf.activo && gf.diaMes) {
            let diaAplicar = parseInt(gf.diaMes);
            let diaMostrar = diaAplicar > ultimoDiaMes ? ultimoDiaMes : diaAplicar;
            if (dia === diaMostrar) {
                const itemDiv = document.createElement('div');
                itemDiv.className = `transaccion-item transaccion-${gf.tipo}`;
                const simbolo = gf.tipo === 'ingreso' ? '+' : '-';
                itemDiv.innerHTML = `<i class="bi bi-circle-fill" style="font-size: 6px; margin-right: 4px;"></i>${simbolo}$${gf.cantidad.toFixed(2)}`;
                itemDiv.style.opacity = '0.7';
                itemDiv.title = 'Gasto Fijo (día del mes)';
                transaccionesDiv.appendChild(itemDiv);
            }
        }
    });

    contenedor.appendChild(transaccionesDiv);

    // Total del día
    const totalDiv = document.createElement('div');
    totalDiv.className = 'total-dia';
    const total = calcularTotalDia(fechaStr, diaSemana);
    if (total !== 0) {
        const signo = total > 0 ? '+' : '';
        totalDiv.innerHTML = `<strong class="${total > 0 ? 'total-positivo' : 'total-negativo'}">${signo}$${total.toFixed(2)}</strong>`;
        contenedor.appendChild(totalDiv);
    }

    // Balance acumulado desde inicio de año
    const balanceAcumulado = calcularBalanceAcumulado(fechaStr, anio);
    const balanceDiv = document.createElement('div');
    balanceDiv.className = 'balance-acumulado';
    const signoBalance = balanceAcumulado >= 0 ? '+' : '';
    balanceDiv.innerHTML = `<small class="${balanceAcumulado >= 0 ? 'balance-positivo' : 'balance-negativo'}">Balance: ${signoBalance}$${balanceAcumulado.toFixed(2)}</small>`;
    contenedor.appendChild(balanceDiv);

    // Eventos del día: abrir directamente el modal de agregar transacción
    if (!esOtroMes) {
        contenedor.style.cursor = 'pointer';
        contenedor.addEventListener('click', (e) => {
            try {
                abrirModalTransaccion(fechaStr);
            } catch (err) {
                console.error('Error al abrir modal de transacción:', err);
                alert('Ocurrió un error al abrir el modal. Revisa la consola para más detalles.');
            }
        });
    }

    return contenedor;
}

// Calcular total del día (considerando transacciones y gastos fijos)
function calcularTotalDia(fecha, diaSemana) {
    let totalIngresos = 0;
    let totalGastos = 0;

    // Transacciones específicas del día
    if (Array.isArray(datos[fecha])) {
        datos[fecha].forEach(t => {
            if (t.tipo === 'ingreso') {
                totalIngresos += t.cantidad;
            } else {
                totalGastos += t.cantidad;
            }
        });
    }

    // Gastos fijos del día
    if (Array.isArray(gastosFijos)) {
        gastosFijos.forEach(gf => {
            let aplica = true;
            const fechaObj = new Date(fecha + 'T00:00:00');
            const fechaStr = fechaObj.toISOString().split('T')[0];
            const ultimoDiaMes = new Date(fechaObj.getFullYear(), fechaObj.getMonth() + 1, 0).getDate();
            if (gf.fechaInicio) aplica = aplica && (fechaStr >= gf.fechaInicio);
            if (gf.fechaFin) aplica = aplica && (fechaStr <= gf.fechaFin);
            if (aplica && gf.activo && Array.isArray(gf.dias) && gf.dias.includes(diaSemana.toString())) {
                if (gf.tipo === 'ingreso') {
                    totalIngresos += gf.cantidad;
                } else {
                    totalGastos += gf.cantidad;
                }
            }
            if (aplica && gf.activo && gf.diaMes) {
                let diaAplicar = parseInt(gf.diaMes);
                let diaMostrar = diaAplicar > ultimoDiaMes ? ultimoDiaMes : diaAplicar;
                if (fechaObj.getDate() === diaMostrar) {
                    if (gf.tipo === 'ingreso') {
                        totalIngresos += gf.cantidad;
                    } else {
                        totalGastos += gf.cantidad;
                    }
                }
            }
        });
    }

    // El total del día es ingresos menos gastos
    return totalIngresos - totalGastos;
}

// Calcular balance acumulado desde el 1 de enero hasta un día específico
function calcularBalanceAcumulado(fechaHasta, anio) {
    let balanceAcumulado = 0;
    const fechaHastaObj = new Date(`${fechaHasta}T00:00:00`);
    if (typeof anio === 'undefined' || anio === null) {
        anio = fechaHastaObj.getFullYear();
    }
    // Buscar el primer año registrado en los datos
    let primerFecha = null;
    for (const key in datos) {
        if (/^\d{4}-\d{2}-\d{2}$/.test(key)) {
            if (!primerFecha || key < primerFecha) primerFecha = key;
        }
    }
    let anioInicio = primerFecha ? (new Date(primerFecha + 'T00:00:00')).getFullYear() : anio;
    for (let year = anioInicio; year <= anio; year++) {
        let mesInicio = (year === anioInicio) ? (primerFecha ? (new Date(primerFecha + 'T00:00:00')).getMonth() : 0) : 0;
        let mesFin = (year === anio) ? fechaHastaObj.getMonth() : 11;
        for (let mes = mesInicio; mes <= mesFin; mes++) {
            const ultimoDia = (year === anio && mes === fechaHastaObj.getMonth()) ? fechaHastaObj.getDate() : new Date(year, mes + 1, 0).getDate();
            for (let dia = 1; dia <= ultimoDia; dia++) {
                const fecha = new Date(year, mes, dia);
                const fechaStr = fecha.toISOString().split('T')[0];
                const diaSemana = fecha.getDay();
                const ultimoDiaMes = new Date(year, mes + 1, 0).getDate();
                // Sumar transacciones específicas del día
                if (Array.isArray(datos[fechaStr])) {
                    datos[fechaStr].forEach(t => {
                        balanceAcumulado += t.tipo === 'ingreso' ? t.cantidad : -t.cantidad;
                    });
                }
                // Sumar gastos fijos del día por día de semana y por día del mes, solo si está dentro del rango
                if (Array.isArray(gastosFijos)) {
                    gastosFijos.forEach(gf => {
                        let aplica = true;
                        if (gf.fechaInicio) {
                            aplica = aplica && (fechaStr >= gf.fechaInicio);
                        }
                        if (gf.fechaFin) {
                            aplica = aplica && (fechaStr <= gf.fechaFin);
                        }
                        if (aplica && gf.activo && Array.isArray(gf.dias) && gf.dias.includes(diaSemana.toString())) {
                            balanceAcumulado += gf.tipo === 'ingreso' ? gf.cantidad : -gf.cantidad;
                        }
                        if (aplica && gf.activo && gf.diaMes) {
                            let diaAplicar = parseInt(gf.diaMes);
                            let diaMostrar = diaAplicar > ultimoDiaMes ? ultimoDiaMes : diaAplicar;
                            if (dia === diaMostrar) {
                                balanceAcumulado += gf.tipo === 'ingreso' ? gf.cantidad : -gf.cantidad;
                            }
                        }
                    });
                }
            }
        }
    }
    return balanceAcumulado;
}

// Abrir modal para ver/agregar transacciones del día
function abrirModalDia(fecha, dia) {
    const fechaObj = new Date(`${fecha}T00:00:00`);
    const diaSemanaStr = diasSemana[fechaObj.getDay()];
    
    document.getElementById('fechaTransaccion').value = fecha + ' (' + diaSemanaStr + ')';
    document.getElementById('tituloTransacciones').textContent = `Transacciones - ${fecha} (${diaSemanaStr})`;
    
    // Cargar transacciones del día
    cargarTransaccionesDia(fecha);
    
    // Mostrar modal de transacciones
    const modalTransacciones = new bootstrap.Modal(document.getElementById('modalTransacciones'));
    modalTransacciones.show();
}

// Cargar transacciones del día en el modal
function cargarTransaccionesDia(fecha) {
    const listado = document.getElementById('listadoTransacciones');
    listado.innerHTML = '';

    const fechaObj = new Date(`${fecha}T00:00:00`);
    const diaSemana = fechaObj.getDay();

    // Transacciones específicas del día
    let transacciones = datos[fecha] ? [...datos[fecha]] : [];

    // Agregar gastos fijos del día
    const transaccionesGastosFijos = [];
    gastosFijos.forEach(gf => {
        if (gf.activo && gf.dias.includes(diaSemana.toString())) {
            transaccionesGastosFijos.push({
                ...gf,
                esGastoFijo: true
            });
        }
    });

    if (transacciones.length === 0 && transaccionesGastosFijos.length === 0) {
        listado.innerHTML = '<p class="text-muted text-center py-4">No hay transacciones para este día</p>';
        return;
    }

    // Mostrar transacciones específicas
    if (transacciones.length > 0) {
        const tituloEspecificas = document.createElement('h6');
        tituloEspecificas.textContent = 'Transacciones Específicas';
        tituloEspecificas.className = 'mt-3 mb-2';
        listado.appendChild(tituloEspecificas);

        const divTransacciones = document.createElement('div');
        divTransacciones.className = 'listado-transacciones-dia';
        
        transacciones.forEach(t => {
            const card = crearCardTransaccion(t, fecha, false);
            divTransacciones.appendChild(card);
        });

        listado.appendChild(divTransacciones);
    }

    // Mostrar gastos fijos
    if (transaccionesGastosFijos.length > 0) {
        const tituloFijos = document.createElement('h6');
        tituloFijos.textContent = 'Gastos Fijos';
        tituloFijos.className = 'mt-4 mb-2';
        listado.appendChild(tituloFijos);

        const divFijos = document.createElement('div');
        divFijos.className = 'listado-transacciones-dia';

        transaccionesGastosFijos.forEach(t => {
            const card = crearCardTransaccion(t, fecha, true);
            divFijos.appendChild(card);
        });

        listado.appendChild(divFijos);
    }

    // Botón para agregar nueva transacción
    const btnAgregar = document.createElement('button');
    btnAgregar.type = 'button';
    btnAgregar.className = 'btn btn-primary w-100 mt-4';
    btnAgregar.innerHTML = '<i class="bi bi-plus-circle"></i> Agregar Nueva Transacción';
    btnAgregar.addEventListener('click', () => abrirModalTransaccion(fecha));
    listado.appendChild(btnAgregar);
}

// Crear card de transacción
function crearCardTransaccion(transaccion, fecha, esGastoFijo) {
    const card = document.createElement('div');
    card.className = `transaccion-card ${transaccion.tipo}`;

    const info = document.createElement('div');
    info.className = 'transaccion-info';

    const desc = document.createElement('div');
    desc.className = 'transaccion-desc';
    desc.textContent = transaccion.descripcion;

    const tipo = document.createElement('div');
    tipo.className = 'transaccion-tipo';
    tipo.textContent = transaccion.tipo === 'ingreso' ? 'Ingreso' : 'Gasto';

    info.appendChild(desc);
    info.appendChild(tipo);

    const cantidad = document.createElement('div');
    cantidad.className = `transaccion-cantidad ${transaccion.tipo}`;
    const simbolo = transaccion.tipo === 'ingreso' ? '+' : '-';
    cantidad.textContent = `${simbolo}$${transaccion.cantidad.toFixed(2)}`;

    card.appendChild(info);
    card.appendChild(cantidad);

    if (!esGastoFijo) {
        const btnEliminar = document.createElement('button');
        btnEliminar.className = 'btn-eliminar-transaccion';
        btnEliminar.innerHTML = '<i class="bi bi-trash"></i>';
        btnEliminar.addEventListener('click', () => eliminarTransaccion(transaccion.id, fecha));
        card.appendChild(btnEliminar);
    }

    return card;
}

// Abrir modal para agregar transacción
function abrirModalTransaccion(fecha) {
    const fechaObj = new Date(`${fecha}T00:00:00`);
    const diaSemanaStr = diasSemana[fechaObj.getDay()];

    // Si se pasa una transacción como segundo argumento, abrimos en modo edición
    const args = Array.from(arguments);
    const transaccion = args.length > 1 ? args[1] : null;

    document.getElementById('fechaTransaccion').value = fecha + ' (' + diaSemanaStr + ')';
    if (transaccion) {
        editarTransaccionId = transaccion.id;
        document.getElementById('tipoTransaccion').value = transaccion.tipo;
        document.getElementById('cantidadTransaccion').value = transaccion.cantidad;
        document.getElementById('descripcionTransaccion').value = transaccion.descripcion;
        const tituloModal = document.querySelector('#modalTransaccion .modal-title');
        if (tituloModal) tituloModal.textContent = `Editar Transacción - ${fecha} (${diaSemanaStr})`;
    } else {
        editarTransaccionId = null;
        document.getElementById('tipoTransaccion').value = 'gasto';
        document.getElementById('cantidadTransaccion').value = '';
        document.getElementById('descripcionTransaccion').value = '';
        const tituloModal = document.querySelector('#modalTransaccion .modal-title');
        if (tituloModal) tituloModal.textContent = `Agregar Transacción - ${fecha} (${diaSemanaStr})`;
    }

    const modalTransaccion = new bootstrap.Modal(document.getElementById('modalTransaccion'));
    modalTransaccion.show();
}

// Guardar transacción
function guardarTransaccion() {
    const fecha = document.getElementById('fechaTransaccion').value.split(' ')[0];
    const tipo = document.getElementById('tipoTransaccion').value;
    const cantidad = parseFloat(document.getElementById('cantidadTransaccion').value);
    const descripcion = document.getElementById('descripcionTransaccion').value;

    if (!cantidad || cantidad <= 0) {
        alert('Por favor ingresa una cantidad válida');
        return;
    }

    if (editarTransaccionId) {
        const formData = new FormData();
        formData.append('action', 'editarTransaccion');
        formData.append('fecha', fecha);
        formData.append('id', editarTransaccionId);
        formData.append('tipo', tipo);
        formData.append('cantidad', cantidad);
        formData.append('descripcion', descripcion);

        fetch('index.php', { method: 'POST', body: formData })
        .then(resp => resp.text())
        .then(text => {
            let data;
            try { data = JSON.parse(text); } catch (err) {
                console.error('Error parseando respuesta editarTransaccion:', err, text);
                alert('Respuesta inválida del servidor. Revisa la consola.');
                return;
            }
            if (data.success) {
                // Actualizar localmente
                try {
                    const updated = data.transaccion;
                    if (datos[fecha]) {
                        datos[fecha] = datos[fecha].map(t => t.id === updated.id ? updated : t);
                    }
                } catch (err) { console.warn(err); }

                editarTransaccionId = null;
                if (bootstrap.Modal.getInstance(document.getElementById('modalTransaccion'))) bootstrap.Modal.getInstance(document.getElementById('modalTransaccion')).hide();
                actualizarCalendario();
                cargarDatos();
            } else {
                alert(data.mensaje || 'Error al editar la transacción');
            }
        })
        .catch(err => { console.error('Error fetch editarTransaccion:', err); alert('Error de red.'); });

        return;
    }

    const formData = new FormData();
    formData.append('action', 'agregarTransaccion');
    formData.append('fecha', fecha);
    formData.append('tipo', tipo);
    formData.append('cantidad', cantidad);
    formData.append('descripcion', descripcion);

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            try {
                if (datos[fecha]) {
                    datos[fecha].push(data.transaccion);
                } else {
                    datos[fecha] = [data.transaccion];
                }
            } catch (err) {
                console.warn('No se pudo actualizar datos localmente:', err);
            }

            // Ocultar modales y refrescar vista
            const modalT = document.getElementById('modalTransaccion');
            const modalTs = document.getElementById('modalTransacciones');
            if (bootstrap.Modal.getInstance(modalT)) bootstrap.Modal.getInstance(modalT).hide();
            if (bootstrap.Modal.getInstance(modalTs)) bootstrap.Modal.getInstance(modalTs).hide();
            actualizarCalendario();
            // Sincronizar datos desde servidor en segundo plano
            cargarDatos();
            calcularResumenMes();
        } else {
            alert(data.mensaje || 'Error al guardar la transacción');
        }
    })
    .catch(error => {
        console.error('Error fetch guardarTransaccion:', error);
        alert('Error de red al guardar la transacción. Revisa la consola.');
    });
}

// Eliminar transacción
function eliminarTransaccion(id, fecha) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta transacción?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'eliminarTransaccion');
    formData.append('id', id);
    formData.append('fecha', fecha);

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cargarDatos();
            cargarTransaccionesDia(fecha);
            actualizarCalendario();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Refrescar todos los datos
function cargarDatos() {
    cargarTodosDatos();
}

// Función auxiliar para cargar todos los datos
function cargarTodosDatos() {
    const formData = new FormData();
    formData.append('action', 'obtenerTodosDatos');

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Separar gastosFijos del resto de los datos
        gastosFijos = Array.isArray(data.gastosFijos) ? data.gastosFijos : [];
        // Eliminar la clave gastosFijos del objeto de días
        const datosCopia = {...data};
        delete datosCopia.gastosFijos;
        datos = datosCopia;
        // Esperar un ciclo de event loop para asegurar que gastosFijos esté listo
        setTimeout(() => {
            actualizarCalendario();
            calcularResumenMes();
        }, 0);
    })
    .catch(error => {
        console.error('Error cargando datos:', error);
        // Inicializar vacío si hay error
        datos = {};
        gastosFijos = [];
        actualizarCalendario();
        calcularResumenMes();
    });
}

// Guardar gasto fijo
function guardarGastoFijo() {
    const tipo = document.getElementById('tipoGastoFijo').value;
    const cantidad = parseFloat(document.getElementById('cantidadGastoFijo').value);
    const descripcion = document.getElementById('descripcionGastoFijo').value;
    const diasCheckbox = document.querySelectorAll('.diasCheck:checked');
    const dias = Array.from(diasCheckbox).map(check => check.value);
    const diaMes = document.getElementById('diaMesGastoFijo').value;
    const fechaInicio = document.getElementById('fechaInicioGastoFijo').value;
    const fechaFin = document.getElementById('fechaFinGastoFijo').value;

    if (dias.length === 0 && !diaMes) {
        alert('Selecciona al menos un día de la semana o un día del mes');
        return;
    }
    if (!cantidad || cantidad <= 0) {
        alert('Por favor ingresa una cantidad válida');
        return;
    }
    if (!descripcion) {
        alert('Por favor ingresa una descripción');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'agregarGastoFijo');
    formData.append('tipo', tipo);
    formData.append('cantidad', cantidad);
    formData.append('descripcion', descripcion);
    dias.forEach((dia, index) => {
        formData.append(`dias[${index}]`, dia);
    });
    if (diaMes) {
        formData.append('diaMes', diaMes);
    }
    if (fechaInicio) {
        formData.append('fechaInicio', fechaInicio);
    }
    if (fechaFin) {
        formData.append('fechaFin', fechaFin);
    }

    const editarId = document.getElementById('modalGastoFijo').getAttribute('data-editar-id');
    if (editarId) {
        formData.append('editarId', editarId);
    }

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('formGastoFijo').reset();
            document.querySelectorAll('.diasCheck').forEach(check => check.checked = false);
            bootstrap.Modal.getInstance(document.getElementById('modalGastoFijo')).hide();
            document.getElementById('modalGastoFijo').removeAttribute('data-editar-id');
            cargarGastosFijos().then(() => {
                actualizarCalendario();
                calcularResumenMes();
            });
        } else {
            alert('Error al guardar el gasto fijo');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Cargar gastos fijos
function cargarGastosFijos() {
    const formData = new FormData();
    formData.append('action', 'obtenerGastosFijos');

    return fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        gastosFijos = data.gastosFijos || [];
        actualizarVistaGastosFijos();
    })
    .catch(error => {
        console.error('Error cargando gastos fijos:', error);
        // Mantener el array en estado consistente
        gastosFijos = [];
    });
}

// Actualizar vista de gastos fijos
function actualizarVistaGastosFijos() {
    const listaGastosFijos = document.getElementById('listaGastosFijos');
    
    if (gastosFijos.length === 0) {
        listaGastosFijos.innerHTML = '<p class="text-muted">No hay gastos fijos configurados</p>';
        return;
    }

    listaGastosFijos.innerHTML = '';

    gastosFijos.forEach(gf => {
        const card = document.createElement('div');
        card.className = `card card-gasto-fijo ${gf.tipo}`;

        const diasNombres = [];
        const dias = Array.isArray(gf.dias) ? gf.dias : (gf.dias ? gf.dias.split(',') : []);
        dias.forEach(d => {
            diasNombres.push(diasSemana[parseInt(d)]);
        });

        card.innerHTML = `
            <div class="card-body">
                <h6 class="card-title">${gf.descripcion}</h6>
                <p class="mb-2">
                    <strong class="${gf.tipo === 'ingreso' ? 'text-success' : 'text-danger'}">
                        ${gf.tipo === 'ingreso' ? '+' : '-'}$${gf.cantidad.toFixed(2)}
                    </strong>
                </p>
                <div class="dias-gasto">
                    ${diasNombres.map(d => `<span class="badge bg-info">${d}</span>`).join('')}
                    ${gf.diaMes ? `<span class="badge bg-warning text-dark">Día ${gf.diaMes}</span>` : ''}
                </div>
                <div class="fechas-gasto mt-2">
                    ${gf.fechaInicio ? `<span class="badge bg-secondary">Desde: ${gf.fechaInicio}</span>` : ''}
                    ${gf.fechaFin ? `<span class="badge bg-secondary">Hasta: ${gf.fechaFin}</span>` : ''}
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-sm btn-primary w-100 btn-editar-gasto-fijo" data-id="${gf.id}">
                        <i class="bi bi-pencil"></i> Modificar
                    </button>
                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="eliminarGastoFijo('${gf.id}')">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        `;

        listaGastosFijos.appendChild(card);
    });

    // Asignar eventos a los botones de modificar (delegación por seguridad)
    setTimeout(() => {
        document.querySelectorAll('.btn-editar-gasto-fijo').forEach(btn => {
            btn.onclick = function() {
                const id = this.getAttribute('data-id');
                const gf = gastosFijos.find(g => g.id == id);
                if (gf) {
                    abrirModalEditarGastoFijo(gf);
                }
            };
        });
    }, 0);
}

// Eliminar gasto fijo
function eliminarGastoFijo(id) {
    if (!confirm('¿Estás seguro de que deseas eliminar este gasto fijo?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'eliminarGastoFijo');
    formData.append('id', id);

    fetch('index.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recargar gastos fijos y luego actualizar el calendario
            cargarGastosFijos().then(() => {
                actualizarCalendario();
                calcularResumenMes();
            });
        }
    })
    .catch(error => console.error('Error:', error));
}

// Calcular resumen del mes
function calcularResumenMes() {
    let totalIngresos = 0;
    let totalGastos = 0;

    const ultimoDia = new Date(anioActual, mesActual + 1, 0).getDate();
    const fechaUltimoDia = new Date(anioActual, mesActual, ultimoDia).toISOString().split('T')[0];

    // Calcular balance anterior (último día del mes anterior)
    let balanceAnterior = 0;
    if (mesActual > 0) {
        const fechaUltimoDiaAnterior = new Date(anioActual, mesActual, 0).toISOString().split('T')[0];
        balanceAnterior = calcularBalanceAcumulado(fechaUltimoDiaAnterior, anioActual);
    } else {
        balanceAnterior = 0;
    }

    let minBalance = null;
    let minBalanceFecha = null;

    for (let dia = 1; dia <= ultimoDia; dia++) {
        const fecha = new Date(anioActual, mesActual, dia);
        const fechaStr = fecha.toISOString().split('T')[0];
        const diaSemana = fecha.getDay();
        const ultimoDiaMes = ultimoDia;

        // Transacciones específicas del día
        if (Array.isArray(datos[fechaStr])) {
            datos[fechaStr].forEach(t => {
                if (t.tipo === 'ingreso') {
                    totalIngresos += t.cantidad;
                } else {
                    totalGastos += t.cantidad;
                }
            });
        }

        // Gastos fijos del día por día de semana y por día del mes, solo si está dentro del rango
        if (Array.isArray(gastosFijos)) {
            gastosFijos.forEach(gf => {
                let aplica = true;
                if (gf.fechaInicio) {
                    aplica = aplica && (fechaStr >= gf.fechaInicio);
                }
                if (gf.fechaFin) {
                    aplica = aplica && (fechaStr <= gf.fechaFin);
                }
                if (aplica && gf.activo && Array.isArray(gf.dias) && gf.dias.includes(diaSemana.toString())) {
                    if (gf.tipo === 'ingreso') {
                        totalIngresos += gf.cantidad;
                    } else {
                        totalGastos += gf.cantidad;
                    }
                }
                if (aplica && gf.activo && gf.diaMes) {
                    let diaAplicar = parseInt(gf.diaMes);
                    let diaMostrar = diaAplicar > ultimoDiaMes ? ultimoDiaMes : diaAplicar;
                    if (dia === diaMostrar) {
                        if (gf.tipo === 'ingreso') {
                            totalIngresos += gf.cantidad;
                        } else {
                            totalGastos += gf.cantidad;
                        }
                    }
                }
            });
        }

        // Calcular balance acumulado de este día
        const balanceDia = calcularBalanceAcumulado(fechaStr, anioActual);
        if (minBalance === null || balanceDia < minBalance) {
            minBalance = balanceDia;
            minBalanceFecha = fechaStr;
        }
    }

    // El balance debe ser el acumulado hasta el último día del mes
    const balance = calcularBalanceAcumulado(fechaUltimoDia, anioActual);

    // Mostrar Balance Anterior en el span correspondiente
    const balanceAnteriorSpan = document.getElementById('balanceAnterior');
    if (balanceAnteriorSpan) {
        balanceAnteriorSpan.textContent = `$${balanceAnterior.toFixed(2)}`;
        balanceAnteriorSpan.className = balanceAnterior === 0 ? 'text-secondary' : (balanceAnterior > 0 ? 'text-success' : 'text-danger');
    }

    document.getElementById('totalIngresos').textContent = `$${totalIngresos.toFixed(2)}`;
    document.getElementById('totalGastos').textContent = `$${totalGastos.toFixed(2)}`;
    document.getElementById('totalBalance').textContent = `$${balance.toFixed(2)}`;
    document.getElementById('totalBalance').className = balance >= 0 ? 'text-success' : 'text-danger';

    // Mostrar balance mínimo del mes
    let minBalanceDiv = document.getElementById('minBalanceMes');
    if (!minBalanceDiv) {
        minBalanceDiv = document.createElement('div');
        minBalanceDiv.id = 'minBalanceMes';
        const resumenCard = document.getElementById('totalBalance').parentElement.parentElement;
        resumenCard.appendChild(minBalanceDiv);
    }
    if (minBalance !== null && minBalanceFecha) {
        // Ajuste para evitar desfase por zona horaria
        const [anio, mesNum, diaNum] = minBalanceFecha.split('-');
        const dia = parseInt(diaNum, 10);
        const mes = nombresMeses[parseInt(mesNum, 10) - 1];
        minBalanceDiv.innerHTML = `<hr><p class='mb-0'><strong>Balance mínimo del mes:</strong> <span class='${minBalance >= 0 ? 'text-success' : 'text-danger'}'>$${minBalance.toFixed(2)}</span> <br><small>(${dia} de ${mes})</small></p>`;
    } else {
        minBalanceDiv.innerHTML = '';
    }
}

// Función para abrir el modal de edición de gasto fijo
function abrirModalEditarGastoFijo(gf) {
    document.getElementById('tipoGastoFijo').value = gf.tipo;
    document.getElementById('cantidadGastoFijo').value = gf.cantidad;
    document.getElementById('descripcionGastoFijo').value = gf.descripcion;
    document.querySelectorAll('.diasCheck').forEach(check => {
        check.checked = Array.isArray(gf.dias) ? gf.dias.includes(check.value) : false;
    });
    document.getElementById('diaMesGastoFijo').value = gf.diaMes || '';
    document.getElementById('fechaInicioGastoFijo').value = gf.fechaInicio || '';
    document.getElementById('fechaFinGastoFijo').value = gf.fechaFin || '';
    document.getElementById('modalGastoFijo').setAttribute('data-editar-id', gf.id);
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalGastoFijo'));
    modal.show();
}
