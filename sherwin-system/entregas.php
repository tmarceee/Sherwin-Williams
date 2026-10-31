<?php
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");

// PROCESO: Marca el pedido como entregado usando una transacción segura.
if (isset($_GET['entregar'])) {
    $pedido_id = intval($_GET['entregar']);
    $fecha = date("Y-m-d");

    // VALIDACIÓN: Verifica que el pedido esté previamente facturado.
    $consultaPedido = $conexion->query("
        SELECT estado 
        FROM pedidos 
        WHERE id = $pedido_id
    ");

    $pedido = $consultaPedido->fetch_assoc();

    if ($pedido && $pedido['estado'] == 'facturado') {
        $conexion->begin_transaction();

        try {
            // ACCIÓN: Registra la entrega en la tabla entregas.
            $conexion->query("
                INSERT INTO entregas (pedido_id, fecha, observaciones)
                VALUES ('$pedido_id', '$fecha', 'Pedido despachado y entregado correctamente')
            ");

            // ACCIÓN: Actualiza el estado del pedido a 'entregado'.
            $conexion->query("
                UPDATE pedidos 
                SET estado = 'entregado'
                WHERE id = $pedido_id
            ");

            $conexion->commit();
            header("Location: entregas.php?mensaje=ok");
            exit();
        } catch (Exception $e) {
            $conexion->rollback();
            header("Location: entregas.php?mensaje=error_db");
            exit();
        }
    } else {
        header("Location: entregas.php?mensaje=error");
        exit();
    }
}

// CONSULTA: Trae los pedidos facturados pendientes de entrega para la primera tabla.
$pedidosFacturados = $conexion->query("
    SELECT 
        pedidos.id,
        pedidos.fecha,
        pedidos.estado,
        clientes.nombre AS cliente,
        productos.nombre AS producto,
        pedido_detalle.cantidad,
        (pedido_detalle.cantidad * pedido_detalle.precio_unitario) AS total
    FROM pedidos
    INNER JOIN clientes ON pedidos.cliente_id = clientes.id
    INNER JOIN pedido_detalle ON pedidos.id = pedido_detalle.pedido_id
    INNER JOIN productos ON pedido_detalle.producto_id = productos.id
    WHERE pedidos.estado = 'facturado'
    ORDER BY pedidos.id DESC
");

// CONSULTA: Trae el historial de entregas ya realizadas para la segunda tabla.
$entregas = $conexion->query("
    SELECT 
        entregas.id,
        entregas.fecha,
        entregas.observaciones,
        pedidos.id AS pedido_id,
        clientes.nombre AS cliente
    FROM entregas
    INNER JOIN pedidos ON entregas.pedido_id = pedidos.id
    INNER JOIN clientes ON pedidos.cliente_id = clientes.id
    ORDER BY entregas.id DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <title>Logística y Entregas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>

<div class="container mt-5">

    <!-- ALERTAS: Muestra mensajes de éxito si se entregó correctamente -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'ok') { ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>🚚 ¡Entregado!</strong> Pedido marcado como entregado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- ALERTAS: Muestra mensajes de error si falló la validación -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'error') { ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>⚠️ Atención:</strong> No se pudo procesar la entrega. Solo se pueden entregar pedidos previamente facturados.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- SECCIÓN 1: TABLA DE PEDIDOS FACTURADOS PENDIENTES -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h1 class="mb-4">Módulo de Entregas y Despachos</h1>

            <h2 class="h5 mb-3 text-success fw-bold">Pedidos Facturados Listos para Entregar</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Pedido</th>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pedidosFacturados->num_rows == 0) { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">No hay pedidos facturados pendientes de entrega.</td>
                            </tr>
                        <?php } else { ?>
                            <?php while($fila = $pedidosFacturados->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $fila['id']; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($fila['fecha'])); ?></td>
                                    <td><?php echo $fila['cliente']; ?></td>
                                    <td><?php echo $fila['producto']; ?></td>
                                    <td><?php echo $fila['cantidad']; ?></td>
                                    <td><strong>$<?php echo number_format($fila['total'], 2, ',', '.'); ?></strong></td>
                                    <td><span class="badge bg-info text-dark">Facturado</span></td>
                                    <td>
                                        <!-- BOTÓN: Ejecuta la acción de marcar como entregado -->
                                        <a href="entregas.php?entregar=<?php echo $fila['id']; ?>"
                                           class="btn btn-sm btn-success fw-bold px-3"
                                           onclick="return confirm('¿Confirmar la entrega del pedido #<?php echo $fila['id']; ?> al cliente?')">
                                           🚚 Marcar Entregado
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

    <!-- SECCIÓN 2: TABLA DE HISTORIAL DE ENTREGAS REALIZADAS -->
    <div class="card shadow">
        <div class="card-body">
            <h2 class="h5 mb-3 text-secondary fw-bold">Historial de Entregas Realizadas</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID Entrega</th>
                            <th>ID Pedido</th>
                            <th>Fecha de Despacho</th>
                            <th>Cliente</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($entregas->num_rows == 0) { ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No hay entregas registradas todavía.</td>
                            </tr>
                        <?php } else { ?>
                            <?php while($entrega = $entregas->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $entrega['id']; ?></td>
                                    <td>#<?php echo $entrega['pedido_id']; ?></td>
                                    <td><?php echo date("d/m/Y", strtotime($entrega['fecha'])); ?></td>
                                    <td><?php echo $entrega['cliente']; ?></td>
                                    <td><?php echo $entrega['observaciones']; ?></td>
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