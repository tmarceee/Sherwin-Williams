<?php  
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");  
  
// PROCESO: Guarda un nuevo pedido validando que haya stock suficiente.
if (isset($_POST['guardar'])) {  
    $cliente_id = intval($_POST['cliente_id']);  
    $producto_id = intval($_POST['producto_id']);  
    $cantidad = intval($_POST['cantidad']);  
    $fecha = $_POST['fecha'];  
    $estado = "pendiente";  
  
    if ($cliente_id > 0 && $producto_id > 0 && $cantidad > 0) {  
        // CONSULTA: Obtiene el precio y stock actual del producto.
        $prodQuery = $conexion->query("SELECT precio, stock FROM productos WHERE id = $producto_id");  
        $datosProducto = $prodQuery->fetch_assoc();  
        $precio_unitario = $datosProducto['precio'];  
        $stock_disponible = intval($datosProducto['stock']);  
  
        // VALIDACIÓN: Comprueba si hay stock disponible para cubrir el pedido.
        if ($cantidad <= $stock_disponible) {  
            // ACCIÓN: Inserta la cabecera del pedido.
            $sqlPedido = "INSERT INTO pedidos (cliente_id, fecha, estado)  
            VALUES ('$cliente_id', '$fecha', '$estado')";  
            $conexion->query($sqlPedido);  
            $pedido_id = $conexion->insert_id;  
  
            // ACCIÓN: Inserta el detalle del pedido (producto, cantidad y precio).
            $sqlDetalle = "INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio_unitario)  
            VALUES ('$pedido_id', '$producto_id', '$cantidad', '$precio_unitario')";  
            $conexion->query($sqlDetalle);  
  
            header("Location: pedidos.php?mensaje=creado");  
            exit();  
        } else {  
            $error = "No hay suficiente stock disponible para este producto. Stock actual: " . $stock_disponible;  
        }  
    }  
}  
  
// PROCESO: Actualiza un pedido existente validando el stock.
if (isset($_POST['actualizar'])) {  
    $pedido_id = intval($_POST['pedido_id']);  
    $cliente_id = intval($_POST['cliente_id']);  
    $producto_id = intval($_POST['producto_id']);  
    $cantidad = intval($_POST['cantidad']);  
    $fecha = $_POST['fecha'];  
  
    $prodQuery = $conexion->query("SELECT precio, stock FROM productos WHERE id = $producto_id");  
    $datosProducto = $prodQuery->fetch_assoc();  
    $precio_unitario = $datosProducto['precio'];  
    $stock_disponible = intval($datosProducto['stock']);  
  
    if ($cantidad <= $stock_disponible) {  
        // ACCIÓN: Actualiza los datos principales del pedido.
        $conexion->query("UPDATE pedidos SET cliente_id = '$cliente_id', fecha = '$fecha' WHERE id = $pedido_id");  
  
        // ACCIÓN: Actualiza el detalle del pedido.
        $conexion->query("UPDATE pedido_detalle   
        SET producto_id = '$producto_id', cantidad = '$cantidad', precio_unitario = '$precio_unitario'   
        WHERE pedido_id = $pedido_id");  
  
        header("Location: pedidos.php?mensaje=actualizado");  
        exit();  
    } else {  
        $error = "No se puede actualizar. Stock insuficiente. Stock actual: " . $stock_disponible;  
    }  
}  
  
// PROCESO: Anula un pedido si se encuentra en estado pendiente.
if (isset($_GET['anular'])) {  
    $id = intval($_GET['anular']);  
  
    $checkEstado = $conexion->query("SELECT estado FROM pedidos WHERE id = $id");  
    if ($checkEstado && $rowEst = $checkEstado->fetch_assoc()) {  
        if ($rowEst['estado'] == 'pendiente') {  
            $conexion->query("UPDATE pedidos SET estado = 'anulado' WHERE id = $id");  
            header("Location: pedidos.php?mensaje=anulado");  
            exit();  
        } else {  
            header("Location: pedidos.php?mensaje=error_anular");  
            exit();  
        }  
    }  
}  
  
// CONSULTA: Obtiene los datos de un pedido específico para cargarlo en el formulario de edición.
$pedidoEditar = null;  
if (isset($_GET['editar'])) {  
    $id = intval($_GET['editar']);  
    $consultaEdicion = $conexion->query("  
        SELECT p.id, p.cliente_id, p.fecha, pd.producto_id, pd.cantidad  
        FROM pedidos p  
        INNER JOIN pedido_detalle pd ON p.id = pd.pedido_id  
        WHERE p.id = $id AND p.estado = 'pendiente'  
    ");  
    $pedidoEditar = $consultaEdicion->fetch_assoc();  
}  
  
// CONSULTA: Obtiene los listados para los selectores de clientes y productos.
$clientes = $conexion->query("SELECT * FROM clientes ORDER BY nombre ASC");  
$productos = $conexion->query("SELECT * FROM productos ORDER BY nombre ASC");  
  
// CONSULTA: Lista todos los pedidos con sus detalles, cliente y producto asociado.
$pedidos = $conexion->query("  
    SELECT   
        pedidos.id, pedidos.fecha, pedidos.estado,  
        clientes.nombre AS cliente, productos.nombre AS producto,  
        pedido_detalle.cantidad, pedido_detalle.precio_unitario,  
        (pedido_detalle.cantidad * pedido_detalle.precio_unitario) AS total  
    FROM pedidos  
    INNER JOIN clientes ON pedidos.cliente_id = clientes.id  
    INNER JOIN pedido_detalle ON pedidos.id = pedido_detalle.pedido_id  
    INNER JOIN productos ON pedido_detalle.producto_id = productos.id  
    ORDER BY pedidos.id DESC  
");  
?>  
  
<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <title>Módulo Pedidos</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
</head>  
<body class="bg-light">  
  
<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>  
  
<div class="container mt-5">  
  
    <!-- ALERTAS: Mensajes de éxito o error en pantalla -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'creado') { ?>  
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">  
            <strong>✅ ¡Pedido Creado!</strong> El pedido se registró correctamente en el sistema.  
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>  
        </div>  
    <?php } ?>  
  
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'actualizado') { ?>  
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">  
            <strong>✅ ¡Actualizado!</strong> El pedido fue modificado correctamente.  
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>  
        </div>  
    <?php } ?>  
  
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'anulado') { ?>  
        <div class="alert alert-secondary alert-dismissible fade show shadow-sm" role="alert">  
            <strong>❌ ¡Pedido Anulado!</strong> El pedido pasó a estado anulado correctamente.  
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>  
        </div>  
    <?php } ?>  
  
    <?php if (isset($error) && !empty($error)) { ?>  
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">  
            <strong>⚠️ Atención:</strong> <?php echo $error; ?>  
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>  
        </div>  
    <?php } ?>  
  
    <!-- SECCIÓN: FORMULARIO DE ALTA O EDICIÓN DE PEDIDOS -->
    <div class="card shadow">  
        <div class="card-body">  
            <h1 class="mb-4">Módulo Pedidos</h1>  
  
            <?php if ($pedidoEditar) { ?>  
                <h2 class="h4 mb-3 text-warning fw-bold">Editar pedido #<?php echo $pedidoEditar['id']; ?></h2>  
                <form method="POST" class="row g-3">  
                    <input type="hidden" name="pedido_id" value="<?php echo $pedidoEditar['id']; ?>">  
  
                    <div class="col-md-6">  
                        <label class="form-label">Cliente</label>  
                        <select name="cliente_id" class="form-select" required>  
                            <option value="">Seleccionar cliente</option>  
                            <?php   
                            $clientes->data_seek(0);  
                            while($c = $clientes->fetch_assoc()) {   
                                $selected = ($c['id'] == $pedidoEditar['cliente_id']) ? 'selected' : '';  
                            ?>  
                                <option value="<?php echo $c['id']; ?>" <?php echo $selected; ?>>  
                                    <?php echo $c['nombre']; ?>  
                                </option>  
                            <?php } ?>  
                        </select>  
                    </div>  
  
                    <div class="col-md-6">  
                        <label class="form-label">Producto (Con Stock Disponible)</label>  
                        <select name="producto_id" class="form-select" required>  
                            <option value="">Seleccionar producto</option>  
                            <?php   
                            $productos->data_seek(0);  
                            while($p = $productos->fetch_assoc()) {   
                                $selected = ($p['id'] == $pedidoEditar['producto_id']) ? 'selected' : '';  
                            ?>  
                                <option value="<?php echo $p['id']; ?>" <?php echo $selected; ?>>  
                                    <?php echo $p['nombre'] . ' (Stock: ' . $p['stock'] . ' un.) - $' . $p['precio']; ?>  
                                </option>  
                            <?php } ?>  
                        </select>  
                    </div>  
  
                    <div class="col-md-4">  
                        <label class="form-label">Cantidad</label>  
                        <input type="number" name="cantidad" class="form-control" min="1" value="<?php echo $pedidoEditar['cantidad']; ?>" required>  
                    </div>  
  
                    <div class="col-md-4">  
                        <label class="form-label">Fecha</label>  
                        <input type="date" name="fecha" class="form-control" value="<?php echo $pedidoEditar['fecha']; ?>" required>  
                    </div>  
  
                    <div class="col-md-4">  
                        <label class="form-label">Estado</label>  
                        <input type="text" class="form-control bg-white" value="Pendiente" disabled>  
                    </div>  
  
                    <div class="col-12 mt-4">  
                        <button type="submit" name="actualizar" class="btn btn-warning fw-bold">Actualizar pedido</button>  
                        <a href="pedidos.php" class="btn btn-secondary">Cancelar</a>  
                    </div>  
                </form>  
            <?php } else { ?>  
                <h2 class="h4 mb-3 text-primary fw-bold">Agregar nuevo pedido</h2>  
                <form method="POST" class="row g-3">  
                    <div class="col-md-6">  
                        <label class="form-label">Cliente</label>  
                        <select name="cliente_id" class="form-select" required>  
                            <option value="">Seleccionar cliente...</option>  
                            <?php   
                            $clientes->data_seek(0);  
                            while($cliente = $clientes->fetch_assoc()) { ?>  
                                <option value="<?php echo $cliente['id']; ?>">  
                                    <?php echo $cliente['nombre']; ?>  
                                </option>  
                            <?php } ?>  
                        </select>  
                    </div>  
  
                    <div class="col-md-6">  
                        <label class="form-label">Producto (Con Stock Disponible)</label>  
                        <select name="producto_id" class="form-select" required>  
                            <option value="">Seleccionar producto...</option>  
                            <?php   
                            $productos->data_seek(0);  
                            while($producto = $productos->fetch_assoc()) { ?>  
                                <option value="<?php echo $producto['id']; ?>">  
                                    <?php echo $producto['nombre'] . ' (Stock: ' . $producto['stock'] . ' un.) - $' . $producto['precio']; ?>  
                                </option>  
                            <?php } ?>  
                        </select>  
                    </div>  
  
                    <div class="col-md-4">  
                        <label class="form-label">Cantidad</label>  
                        <input type="number" name="cantidad" class="form-control" min="1" placeholder="Ej: 2" required>  
                    </div>  
  
                    <div class="col-md-4">  
                        <label class="form-label">Fecha</label>  
                        <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>  
                    </div>  
  
                    <div class="col-md-4">  
                        <label class="form-label">Estado</label>  
                        <input type="text" class="form-control bg-white" value="Pendiente" disabled>  
                    </div>  
  
                    <div class="col-12 mt-4">  
                        <button type="submit" name="guardar" class="btn btn-primary w-100 py-2 fw-bold">📜 Guardar Pedido</button>  
                    </div>  
                </form>  
            <?php } ?>  
        </div>  
    </div>  
  
    <!-- SECCIÓN: TABLA DE LISTADO DE PEDIDOS -->  
    <div class="card shadow mt-4">  
        <div class="card-body">  
            <h2 class="h4 mb-3">Lista de pedidos</h2>  
  
            <div class="table-responsive">  
                <table class="table table-bordered table-striped align-middle">  
                    <thead class="table-dark">  
                        <tr>  
                            <th>ID</th>  
                            <th>Fecha</th>  
                            <th>Cliente</th>  
                            <th>Producto</th>  
                            <th>Cantidad</th>  
                            <th>Precio Unitario</th>  
                            <th>Total</th>  
                            <th>Estado</th>  
                            <th class="text-center">Acciones</th>  
                        </tr>  
                    </thead>  
                    <tbody>  
                        <?php if ($pedidos->num_rows == 0) { ?>  
                            <tr>  
                                <td colspan="9" class="text-center text-muted py-3">No hay pedidos registrados.</td>  
                            </tr>  
                        <?php } else { ?>  
                            <?php while($fila = $pedidos->fetch_assoc()) { ?>  
                                <tr>  
                                    <td>#<?php echo $fila['id']; ?></td>  
                                    <td><?php echo date("d/m/Y", strtotime($fila['fecha'])); ?></td>  
                                    <td><?php echo $fila['cliente']; ?></td>  
                                    <td><?php echo $fila['producto']; ?></td>  
                                    <td><?php echo $fila['cantidad']; ?></td>  
                                    <td>$<?php echo number_format($fila['precio_unitario'], 2, ',', '.'); ?></td>  
                                    <td><strong>$<?php echo number_format($fila['total'], 2, ',', '.'); ?></strong></td>  
                                    <td>  
                                        <span class="badge <?php echo ($fila['estado'] == 'pendiente') ? 'bg-warning text-dark' : (($fila['estado'] == 'facturado') ? 'bg-info text-dark' : 'bg-secondary'); ?>">  
                                            <?php echo ucfirst($fila['estado']); ?>  
                                        </span>  
                                    </td>  
                                    <td class="text-center">  
                                        <?php if ($fila['estado'] == 'pendiente') { ?>  
                                            <!-- BOTÓN: Editar pedido pendiente -->
                                            <a href="pedidos.php?editar=<?php echo $fila['id']; ?>" class="btn btn-sm btn-warning fw-bold px-2">Editar</a>  
                                            <!-- BOTÓN: Anular pedido pendiente -->
                                            <a href="pedidos.php?anular=<?php echo $fila['id']; ?>"   
                                               class="btn btn-sm btn-danger fw-bold px-2"  
                                               onclick="return confirm('¿Seguro que querés anular este pedido?')">  
                                               Anular  
                                            </a>  
                                        <?php } else { ?>  
                                            <span class="text-muted small">Sin acciones</span>  
                                        <?php } ?>  
                                    </td>  
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