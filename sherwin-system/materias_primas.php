<?php  
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");  
  
// PROCESO: Registra el ingreso o egreso manual de una materia prima.
if (isset($_POST['registrar_movimiento'])) {  
    $materia_id = intval($_POST['materia_id']);  
    $tipo = $_POST['tipo']; // 'ingreso' o 'egreso'  
    $cantidad = floatval($_POST['cantidad']);  
    $fecha = $_POST['fecha'];  
    $motivo = trim($_POST['motivo']);  
  
    if ($materia_id > 0 && $cantidad > 0 && !empty($tipo)) {  
        
        // VALIDACIÓN: Si es egreso, comprueba que haya stock suficiente.
        $validaStock = true;  
        if ($tipo == 'egreso') {  
            $check = $conexion->query("SELECT stock FROM materias_primas WHERE id = $materia_id");  
            $rowCheck = $check->fetch_assoc();  
            if (floatval($rowCheck['stock']) < $cantidad) {  
                $validaStock = false;  
            }  
        }  
  
        if ($validaStock) {  
            $conexion->begin_transaction();  
  
            try {  
                // ACCIÓN: Actualiza el stock sumando o restando la cantidad.
                $operador = ($tipo == 'ingreso') ? '+' : '-';  
                $conexion->query("UPDATE materias_primas SET stock = stock $operador $cantidad WHERE id = $materia_id");  
  
                // ACCIÓN: Define un motivo por defecto si está vacío y registra el movimiento.
                if (empty($motivo)) {  
                    $motivo = ($tipo == 'ingreso') ? 'Ingreso manual de stock' : 'Egreso manual de stock';  
                }  
                $conexion->query("INSERT INTO movimientos_materias (materia_id, tipo, cantidad, fecha, motivo)   
                VALUES ($materia_id, '$tipo', $cantidad, '$fecha', '$motivo')");  
  
                $conexion->commit();  
                header("Location: materias_primas.php?mensaje=registrado&tipo_mov=" . $tipo);  
                exit();  
            } catch (Exception $e) {  
                $conexion->rollback();  
                header("Location: materias_primas.php?mensaje=error_db");  
                exit();  
            }  
        } else {  
            header("Location: materias_primas.php?mensaje=sin_stock");  
            exit();  
        }  
    }  
}  
  
// CONSULTA: Obtiene el listado completo de materias primas ordenadas alfabéticamente.
$materiasPrimas = $conexion->query("SELECT * FROM materias_primas ORDER BY nombre ASC");  
  
// CONSULTA: Obtiene el historial reciente de movimientos de materias primas.
$movimientos = $conexion->query("  
    SELECT mm.id, mp.nombre AS materia, mm.tipo, mm.cantidad, mm.fecha, mm.motivo  
    FROM movimientos_materias mm  
    INNER JOIN materias_primas mp ON mm.materia_id = mp.id  
    ORDER BY mm.id DESC  
    LIMIT 20  
");  
?>  
  
<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <title>Módulo de Materias Primas</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
</head>  
<body class="bg-light">  
  
<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>  
  
<div class="container mt-5 mb-5">  
  
    <!-- ALERTAS: Muestra mensajes de éxito según el tipo de movimiento -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'registrado') { ?>  
        <?php   
        $tipoMov = isset($_GET['tipo_mov']) ? $_GET['tipo_mov'] : 'ingreso';  
        $esIngreso = ($tipoMov == 'ingreso');  
        ?>  
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">  
            <strong><?php echo $esIngreso ? '➕ ¡Ingreso Registrado!' : '➖ ¡Egreso Registrado!'; ?></strong>   
            <?php echo $esIngreso ? 'Se cargaron los insumos al depósito correctamente y se actualizó el stock.' : 'Se descontaron los insumos del depósito correctamente y se actualizó el stock.'; ?>  
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>  
        </div>  
    <?php } ?>  
  
    <!-- ALERTAS: Muestra error si falta stock para el egreso -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'sin_stock') { ?>  
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">  
            <strong>⚠️ Atención:</strong> No se puede realizar el egreso porque la cantidad supera el stock actual disponible de la materia prima.  
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>  
        </div>  
    <?php } ?>  
  
    <!-- ALERTAS: Muestra error de base de datos -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'error_db') { ?>  
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">  
            <strong>⚠️ Error:</strong> Ocurrió un inconveniente al procesar la operación en la base de datos.  
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>  
        </div>  
    <?php } ?>  
  
    <!-- SECCIÓN: FORMULARIO DE REGISTRO DE MOVIMIENTOS -->
    <div class="card shadow mb-4">  
        <div class="card-body">  
            <h1 class="h4 mb-3 text-primary fw-bold">📦 Gestión y Movimientos de Materias Primas</h1>  
            <p class="text-muted small">Registre ingresos por compras a proveedores o egresos manuales de insumos del depósito.</p>  
  
            <form method="POST" class="row g-3">  
                <div class="col-md-4">  
                    <label class="form-label">Materia Prima</label>  
                    <select name="materia_id" class="form-select" required>  
                        <option value="">Seleccionar insumo...</option>  
                        <?php   
                        $materiasPrimas->data_seek(0);  
                        while($mp = $materiasPrimas->fetch_assoc()) { ?>  
                            <option value="<?php echo $mp['id']; ?>">  
                                <?php echo $mp['nombre'] . ' (Stock actual: ' . $mp['stock'] . ')'; ?>  
                            </option>  
                        <?php } ?>  
                    </select>  
                </div>  
  
                <div class="col-md-2">  
                    <label class="form-label">Tipo de Movimiento</label>  
                    <select name="tipo" class="form-select" required>  
                        <option value="ingreso">➕ Ingreso</option>  
                        <option value="egreso">➖ Egreso</option>  
                    </select>  
                </div>  
  
                <div class="col-md-2">  
                    <label class="form-label">Cantidad</label>  
                    <input type="number" step="0.01" min="0.01" name="cantidad" class="form-control" placeholder="Ej: 10" required>  
                </div>  
  
                <div class="col-md-4">  
                    <label class="form-label">Fecha</label>  
                    <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>  
                </div>  
  
                <div class="col-md-12">  
                    <label class="form-label">Motivo u Observación (Opcional)</label>  
                    <input type="text" name="motivo" class="form-control" placeholder="Ej: Compra a proveedor / Ajuste de inventario">  
                </div>  
  
                <div class="col-12 mt-3">  
                    <button type="submit" name="registrar_movimiento" class="btn btn-primary w-100 py-2 fw-bold">💾 Registrar Movimiento de Insumo</button>  
                </div>  
            </form>  
        </div>  
    </div>  
  
    <!-- SECCIÓN: TABLA DE STOCK ACTUAL DE MATERIAS PRIMAS -->
    <div class="card shadow mb-4">  
        <div class="card-body">  
            <h2 class="h5 mb-3 text-secondary fw-bold">📊 Stock Actual en Depósito</h2>  
            <div class="table-responsive">  
                <table class="table table-bordered table-striped align-middle">  
                    <thead class="table-dark">  
                        <tr>  
                            <th>ID</th>  
                            <th>Materia Prima / Insumo</th>  
                            <th>Stock Disponible</th>  
                        </tr>  
                    </thead>  
                    <tbody>  
                        <?php   
                        $materiasPrimas->data_seek(0);  
                        while($item = $materiasPrimas->fetch_assoc()) { ?>  
                            <tr>  
                                <td>#<?php echo $item['id']; ?></td>  
                                <td><strong><?php echo $item['nombre']; ?></strong></td>  
                                <td>  
                                    <span class="badge bg-success fs-6">  
                                        <?php echo $item['stock']; ?> unidades / kg / lts  
                                    </span>  
                                </td>  
                            </tr>  
                        <?php } ?>  
                    </tbody>  
                </table>  
            </div>  
        </div>  
    </div>  
  
    <!-- SECCIÓN: TABLA DE HISTORIAL DE MOVIMIENTOS -->
    <div class="card shadow">  
        <div class="card-body">  
            <h2 class="h5 mb-3 text-secondary fw-bold">📋 Historial Reciente de Insumos</h2>  
            <div class="table-responsive">  
                <table class="table table-bordered table-striped align-middle">  
                    <thead class="table-secondary">  
                        <tr>  
                            <th>ID</th>  
                            <th>Fecha</th>  
                            <th>Insumo</th>  
                            <th>Tipo</th>  
                            <th>Cantidad</th>  
                            <th>Motivo</th>  
                        </tr>  
                    </thead>  
                    <tbody>  
                        <?php if ($movimientos->num_rows == 0) { ?>  
                            <tr>  
                                <td colspan="6" class="text-center text-muted py-3">No hay movimientos de materias primas registrados.</td>  
                            </tr>  
                        <?php } else { ?>  
                            <?php while($mov = $movimientos->fetch_assoc()) { ?>  
                                <tr>  
                                    <td>#<?php echo $mov['id']; ?></td>  
                                    <td><?php echo date("d/m/Y", strtotime($mov['fecha'])); ?></td>  
                                    <td><strong><?php echo $mov['materia']; ?></strong></td>  
                                    <td>  
                                        <span class="badge <?php echo $mov['tipo'] == 'ingreso' ? 'bg-success' : 'bg-danger'; ?>">  
                                            <?php echo ucfirst($mov['tipo']); ?>  
                                        </span>  
                                    </td>  
                                    <td><?php echo $mov['cantidad']; ?></td>  
                                    <td><?php echo $mov['motivo']; ?></td>  
                                </tr>  
                            <?php } ?>  
                        <?php } ?>  
                    </tbody>  
                </table>  
            </div>  
        </div>  
    </div>  
  
</div>  
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>  
</body>  
</html>