<?php  
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");  
  
// CONSULTA: Obtiene el historial de movimientos de stock comercial (entradas y salidas).
$movimientosStock = $conexion->query("  
    SELECT   
        ms.id,  
        ms.tipo,  
        ms.cantidad,  
        ms.fecha,  
        ms.motivo,  
        p.nombre AS producto,  
        p.categoria,  
        p.stock AS stock_actual_producto  
    FROM movimientos_stock ms  
    INNER JOIN productos p ON ms.producto_id = p.id  
    ORDER BY ms.id DESC  
");  
  
// CONSULTA: Obtiene el stock actual general de todos los productos terminados.
$productosStock = $conexion->query("  
    SELECT id, nombre, categoria, precio, stock   
    FROM productos   
    ORDER BY nombre ASC  
");  
?>  
  
<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">  
    <link rel="icon" href="logo.png" type="image/png">
    <title>Historial y Control de Stock</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
</head>  
<body class="bg-light">  
  
<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>  
  
<div class="container mt-5">  
  
    <!-- SECCIÓN 1: TABLA DE STOCK ACTUAL DE PRODUCTOS COMERCIALES -->
    <div class="card shadow mb-4">  
        <div class="card-body">  
            <h1 class="h4 mb-3 text-primary fw-bold">📊 Estado Actual del Stock Comercial</h1>  
            <p class="text-muted small">Inventario actual disponible en depósito tras la fabricación y las ventas.</p>  
  
            <div class="table-responsive">  
                <table class="table table-bordered table-striped align-middle">  
                    <thead class="table-dark">  
                        <tr>  
                            <th>ID</th>  
                            <th>Producto</th>  
                            <th>Categoría</th>  
                            <th>Precio Unitario</th>  
                            <th>Stock Disponible</th>  
                        </tr>  
                    </thead>  
                    <tbody>  
                        <?php if ($productosStock->num_rows == 0) { ?>  
                            <tr>  
                                <td colspan="5" class="text-center text-muted py-3">No hay productos registrados en el sistema.</td>  
                            </tr>  
                        <?php } else { ?>  
                            <?php while($prod = $productosStock->fetch_assoc()) { ?>  
                                <tr>  
                                    <td>#<?php echo $prod['id']; ?></td>  
                                    <td><strong><?php echo $prod['nombre']; ?></strong></td>  
                                    <td><?php echo $prod['categoria']; ?></td>  
                                    <td>$<?php echo number_format($prod['precio'], 2, ',', '.'); ?></td>  
                                    <td>  
                                        <span class="badge bg-success fs-6">  
                                            <?php echo $prod['stock']; ?> unidades  
                                        </span>  
                                    </td>  
                                </tr>  
                            <?php } ?>  
                        <?php } ?>  
                    </tbody>  
                </table>  
            </div>  
        </div>  
    </div>  
  
    <!-- SECCIÓN 2: TABLA DE HISTORIAL DE MOVIMIENTOS -->
    <div class="card shadow mb-5">  
        <div class="card-body">  
            <h2 class="h4 mb-3 text-secondary fw-bold">📋 Historial de Movimientos de Productos (Entradas y Salidas)</h2>  
            <p class="text-muted small">Registro detallado de cada ingreso por producción y egreso por facturación de ventas.</p>  
  
            <div class="table-responsive">  
                <table class="table table-bordered table-striped align-middle">  
                    <thead class="table-secondary">  
                        <tr>  
                            <th>ID Mov.</th>  
                            <th>Fecha</th>  
                            <th>Producto</th>  
                            <th>Tipo</th>  
                            <th>Cantidad</th>  
                            <th>Motivo</th>  
                        </tr>  
                    </thead>  
                    <tbody>  
                        <?php if ($movimientosStock->num_rows == 0) { ?>  
                            <tr>  
                                <td colspan="6" class="text-center text-muted py-3">No hay movimientos de stock registrados todavía.</td>  
                            </tr>  
                        <?php } else { ?>  
                            <?php while($mov = $movimientosStock->fetch_assoc()) { ?>  
                                <tr>  
                                    <td>#<?php echo $mov['id']; ?></td>  
                                    <td><?php echo date("d/m/Y", strtotime($mov['fecha'])); ?></td>  
                                    <td><strong><?php echo $mov['producto']; ?></strong></td>  
                                    <td>  
                                        <span class="badge <?php echo $mov['tipo'] == 'ingreso' ? 'bg-success' : 'bg-danger'; ?>">  
                                            <?php echo ucfirst($mov['tipo']); ?>  
                                        </span>  
                                    </td>  
                                    <td><?php echo $mov['cantidad']; ?> un.</td>  
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