<?php
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");

// PROCESO: Factura un pedido y descuenta el stock de forma segura mediante una transacción.
if (isset($_GET['facturar'])) {
    $pedido_id = intval($_GET['facturar']);
    $fecha = date("Y-m-d");

    // CONSULTA: Busca los datos y el total del pedido seleccionado.
    $consulta = $conexion->query("
        SELECT 
            pedidos.id,
            pedidos.estado,
            pedido_detalle.producto_id,
            pedido_detalle.cantidad,
            pedido_detalle.precio_unitario,
            (pedido_detalle.cantidad * pedido_detalle.precio_unitario) AS total
        FROM pedidos
        INNER JOIN pedido_detalle ON pedidos.id = pedido_detalle.pedido_id
        WHERE pedidos.id = $pedido_id
    ");

    $pedido = $consulta->fetch_assoc();

    if ($pedido && $pedido['estado'] != 'facturado') {
        $producto_id = $pedido['producto_id'];
        $cantidad = $pedido['cantidad'];
        $total = $pedido['total'];

        // VALIDACIÓN: Consulta el stock actual del producto en el depósito.
        $consultaStock = $conexion->query("
            SELECT stock 
            FROM productos 
            WHERE id = $producto_id
        ");

        $datosStock = $consultaStock->fetch_assoc();
        $stock_actual = intval($datosStock['stock']);

        // VERIFICACIÓN: Comprueba si hay suficiente stock antes de facturar.
        if ($stock_actual >= $cantidad) {
            $conexion->begin_transaction();

            try {
                // ACCIÓN: Inserta el registro en la tabla facturas.
                $conexion->query("
                    INSERT INTO facturas (pedido_id, fecha, total)
                    VALUES ('$pedido_id', '$fecha', '$total')
                ");

                // ACCIÓN: Actualiza el estado del pedido a 'facturado'.
                $conexion->query("
                    UPDATE pedidos 
                    SET estado = 'facturado'
                    WHERE id = $pedido_id
                ");

                // ACCIÓN: Descuenta la cantidad vendida del stock de productos.
                $conexion->query("
                    UPDATE productos
                    SET stock = stock - $cantidad
                    WHERE id = $producto_id
                ");

                // ACCIÓN: Registra la salida en el historial de movimientos de stock.
                $conexion->query("
                    INSERT INTO movimientos_stock (producto_id, tipo, cantidad, fecha, motivo)
                    VALUES ('$producto_id', 'egreso', '$cantidad', '$fecha', 'Venta facturada')
                ");

                $conexion->commit();
                header("Location: facturacion.php?mensaje=ok");
                exit();
            } catch (Exception $e) {
                $conexion->rollback();
                header("Location: facturacion.php?mensaje=error_db");
                exit();
            }
        } else {
            header("Location: facturacion.php?mensaje=sin_stock");
            exit();
        }
    }

    header("Location: facturacion.php");
    exit();
}

// CONSULTA: Lista todos los pedidos que están pendientes de facturación.
$pedidosPendientes = $conexion->query("
    SELECT 
        pedidos.id,
        pedidos.fecha,
        pedidos.estado,
        clientes.nombre AS cliente,
        productos.nombre AS producto,
        pedido_detalle.cantidad,
        pedido_detalle.precio_unitario,
        (pedido_detalle.cantidad * pedido_detalle.precio_unitario) AS total
    FROM pedidos
    INNER JOIN clientes ON pedidos.cliente_id = clientes.id
    INNER JOIN pedido_detalle ON pedidos.id = pedido_detalle.pedido_id
    INNER JOIN productos ON pedido_detalle.producto_id = productos.id
    WHERE pedidos.estado = 'pendiente'
    ORDER BY pedidos.id DESC
");

// CONSULTA: Lista el historial de facturas ya emitidas.
$facturas = $conexion->query("
    SELECT
        facturas.id,
        facturas.fecha,
        facturas.total,
        pedidos.id AS pedido_id,
        clientes.nombre AS cliente
    FROM facturas
    INNER JOIN pedidos ON facturas.pedido_id = pedidos.id
    INNER JOIN clientes ON pedidos.cliente_id = clientes.id
    ORDER BY facturas.id DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <title>Facturación de Pedidos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>

<div class="container mt-5">
    
    <!-- ALERTAS: Mensaje de éxito al facturar -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'ok') { ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>✅ ¡Éxito!</strong> Pedido facturado correctamente. El stock comercial fue descontado.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- ALERTAS: Mensaje de error por falta de stock -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'sin_stock') { ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>⚠️ Atención:</strong> No se puede facturar el pedido porque no hay stock suficiente en el depósito.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- ALERTAS: Mensaje de error general en la base de datos -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'error_db') { ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>⚠️ Error:</strong> Ocurrió un problema al procesar la transacción en la base de datos.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- SECCIÓN 1: TABLA DE PEDIDOS PENDIENTES DE FACTURACIÓN -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h1 class="mb-4">Módulo de Facturación</h1>

            <h2 class="h5 mb-3 text-primary fw-bold">Pedidos Pendientes de Facturación</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Pedido</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>P. Unitario</th>
                            <th>Total</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pedidosPendientes->num_rows == 0) { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">No hay pedidos pendientes para facturar.</td>
                            </tr>
                        <?php } else { ?>
                            <?php while($fila = $pedidosPendientes->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $fila['id']; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($fila['fecha'])); ?></td>
                                    <td><?php echo $fila['cliente']; ?></td>
                                    <td><?php echo $fila['producto']; ?></td>
                                    <td><?php echo $fila['cantidad']; ?></td>
                                    <td>$<?php echo number_format($fila['precio_unitario'], 2, ',', '.'); ?></td>
                                    <td><strong>$<?php echo number_format($fila['total'], 2, ',', '.'); ?></strong></td>
                                    <td>
                                        <!-- BOTÓN: Ejecuta la acción de facturar -->
                                        <a href="facturacion.php?facturar=<?php echo $fila['id']; ?>"
                                           class="btn btn-sm btn-success fw-bold px-3"
                                           onclick="return confirm('¿Facturar este pedido #<?php echo $fila['id']; ?>? Se descontará el stock automáticamente.')">
                                           📄 Facturar
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: TABLA DE HISTORIAL DE FACTURAS -->
    <div class="card shadow">
        <div class="card-body">
            <h2 class="h5 mb-3 text-secondary fw-bold">Historial de Facturas Generadas</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>N° Factura</th>
                            <th>Pedido Asignado</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total Facturado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($facturas->num_rows == 0) { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No hay facturas emitidas todavía.</td>
                            </tr>
                        <?php } else { ?>
                            <?php while($factura = $facturas->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $factura['id']; ?></td>
                                    <td>#<?php echo $factura['pedido_id']; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($factura['fecha'])); ?></td>
                                    <td><?php echo $factura['cliente']; ?></td>
                                    <td><strong>$<?php echo number_format($factura['total'], 2, ',', '.'); ?></strong></td>
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