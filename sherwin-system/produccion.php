<?php
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");

$error = "";
$exito = "";

// -------------------------------------------------------------
// VERIFICACIÓN E INSERCIÓN AUTOMÁTICA DE PRODUCTOS COMUNES EN `productos`
// -------------------------------------------------------------
// CONSULTA: Comprueba si la tabla productos está vacía para precargar los productos iniciales.
$verifProd = $conexion->query("SELECT COUNT(*) as total FROM productos");
$rowVerif = $verifProd->fetch_assoc();

if ($rowVerif['total'] <= 1) {
    // FUNCIÓN: Busca dinámicamente los IDs de las materias primas por su nombre.
    $getMat = function($nombreBusqueda) use ($conexion) {
        $res = $conexion->query("SELECT id FROM materias_primas WHERE nombre LIKE '%$nombreBusqueda%' LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            return $row['id'];
        }
        return 0;
    };

    $idResina = $getMat("Resina Acrílica");
    $idTitanio = $getMat("Dióxido de Titanio");
    $idCarbonato = $getMat("Carbonato de Calcio");
    $idEnv20 = $getMat("Plástico (20 Litros)");
    $idEnv4 = $getMat("Hojalata (4 Litros)");
    $idTapas = $getMat("Tapas");
    $idEtiquetas = $getMat("Etiquetas");

    // ARRAY: Define los productos comunes y sus recetas estándar.
    $comunes = [
        [
            'nombre' => 'Látex Interior Blanco (Balde 20L)',
            'descripcion' => 'Balde de 20 Litros de pintura látex para interiores',
            'categoria' => 'Látex',
            'precio' => 25000.00,
            'receta' => [[$idResina, 5.00], [$idTitanio, 2.00], [$idCarbonato, 10.00], [$idEnv20, 1.00], [$idTapas, 1.00], [$idEtiquetas, 1.00]]
        ],
        [
            'nombre' => 'Látex Interior Blanco (Lata 4L)',
            'descripcion' => 'Lata de 4 Litros de pintura látex para interiores',
            'categoria' => 'Látex',
            'precio' => 6000.00,
            'receta' => [[$idResina, 1.00], [$idTitanio, 0.40], [$idCarbonato, 2.00], [$idEnv4, 1.00], [$idTapas, 1.00], [$idEtiquetas, 1.00]]
        ],
        [
            'nombre' => 'Esmalte Sintético Brillante (Lata 4L)',
            'descripcion' => 'Esmalte sintético brillante para metales y maderas',
            'categoria' => 'Sintéticos',
            'precio' => 8500.00,
            'receta' => [[$idResina, 2.50], [$idTitanio, 0.80], [$idEnv4, 1.00], [$idTapas, 1.00], [$idEtiquetas, 1.00]]
        ],
        [
            'nombre' => 'Impermeabilizante para Techos (Balde 20L)',
            'descripcion' => 'Recubrimiento elástico impermeabilizante para losas y techos',
            'categoria' => 'Impermeabilizantes',
            'precio' => 30000.00,
            'receta' => [[$idResina, 8.00], [$idCarbonato, 8.00], [$idEnv20, 1.00], [$idTapas, 1.00], [$idEtiquetas, 1.00]]
        ]
    ];

    foreach ($comunes as $cp) {
        $nom = $conexion->real_escape_string($cp['nombre']);
        $desc = $conexion->real_escape_string($cp['descripcion']);
        $cat = $conexion->real_escape_string($cp['categoria']);
        $precio = $cp['precio'];

        $chequear = $conexion->query("SELECT id FROM productos WHERE nombre = '$nom'");
        if ($chequear && $chequear->num_rows == 0) {
            // ACCIÓN: Inserta el producto base y sus ingredientes en la tabla recetas.
            if ($conexion->query("INSERT INTO productos (nombre, descripcion, categoria, precio, stock) VALUES ('$nom', '$desc', '$cat', $precio, 0)")) {
                $nId = $conexion->insert_id;
                foreach ($cp['receta'] as $ing) {
                    $materiaId = intval($ing[0]);
                    $cantNec = floatval($ing[1]);
                    if ($materiaId > 0 && $cantNec > 0) {
                        $conexion->query("INSERT INTO recetas (producto_id, materia_id, cantidad_necesaria) VALUES ($nId, $materiaId, $cantNec)");
                    }
                }
            }
        }
    }
}

// PROCESO: Ejecuta la orden de producción, valida stock y descuenta insumos.
if (isset($_POST['ejecutar_produccion'])) {
    $producto_id = intval($_POST['producto_id']);
    $cantidad_fabricar = intval($_POST['cantidad_fabricar']);
    $fecha = $_POST['fecha'];

    if ($producto_id <= 0) {
        $error = "Por favor, seleccione un producto válido para fabricar.";
    } elseif ($cantidad_fabricar <= 0) {
        $error = "La cantidad a fabricar debe ser mayor a 0.";
    } else {
        // CONSULTA: Obtiene la receta y el stock actual de cada materia prima necesaria.
        $recetaQuery = $conexion->query("
            SELECT r.materia_id, r.cantidad_necesaria, mp.nombre as materia_nombre, mp.stock as materia_stock 
            FROM recetas r 
            INNER JOIN materias_primas mp ON r.materia_id = mp.id 
            WHERE r.producto_id = $producto_id
        ");

        $ingredientes = [];
        $faltaStock = false;
        $mensajeFalta = "";

        while ($row = $recetaQuery->fetch_assoc()) {
            $necesarioTotal = $row['cantidad_necesaria'] * $cantidad_fabricar;
            
            // VALIDACIÓN: Comprueba si hay suficiente stock de cada insumo.
            if (floatval($row['materia_stock']) < $necesarioTotal) {
                $faltaStock = true;
                $mensajeFalta .= "• Falta stock de <strong>{$row['materia_nombre']}</strong> (Necesario: $necesarioTotal, Disponible: {$row['materia_stock']})<br>";
            }

            $ingredientes[] = [
                'materia_id' => intval($row['materia_id']),
                'cantidad_total' => $necesarioTotal
            ];
        }

        if (empty($ingredientes)) {
            $error = "El producto seleccionado no tiene una receta configurada o faltan insumos asociados en la base de datos.";
        } elseif ($faltaStock) {
            $error = "No se puede completar la producción por falta de insumos:<br>" . $mensajeFalta;
        } else {
            // TRANSACCIÓN: Asegura que el descuento de insumos y el aumento de stock ocurran de forma atómica.
            $conexion->begin_transaction();

            try {
                // ACCIÓN: Descuenta el stock de materias primas y registra los egresos.
                foreach ($ingredientes as $ing) {
                    $mId = $ing['materia_id'];
                    $cantTotal = $ing['cantidad_total'];

                    $resUp = $conexion->query("UPDATE materias_primas SET stock = stock - $cantTotal WHERE id = $mId");
                    if (!$resUp) {
                        throw new Exception("Error al actualizar la materia prima ID $mId: " . $conexion->error);
                    }

                    $resMov = $conexion->query("INSERT INTO movimientos_materias (materia_id, tipo, cantidad, fecha, motivo) 
                                     VALUES ($mId, 'egreso', $cantTotal, '$fecha', 'Consumo para producción')");
                    if (!$resMov) {
                        throw new Exception("Error al registrar movimiento de materia prima: " . $conexion->error);
                    }
                }

                // ACCIÓN: Aumenta el stock del producto terminado.
                $resProd = $conexion->query("UPDATE productos SET stock = stock + $cantidad_fabricar WHERE id = $producto_id");
                if (!$resProd) {
                    throw new Exception("Error al actualizar el stock del producto terminado: " . $conexion->error);
                }

                // ACCIÓN: Registra el ingreso en el historial comercial de stock.
                $resStockHist = $conexion->query("INSERT INTO movimientos_stock (producto_id, tipo, cantidad, fecha, motivo) 
                                VALUES ($producto_id, 'ingreso', $cantidad_fabricar, '$fecha', 'Fabricación / Producción interna')");
                if (!$resStockHist) {
                    throw new Exception("Error al registrar el movimiento comercial: " . $conexion->error);
                }

                $conexion->commit();
                $exito = "¡Producción ejecutada con éxito! Se fabricaron $cantidad_fabricar unidades, se descontaron los insumos y se actualizó el stock para la venta.";
            } catch (Exception $e) {
                $conexion->rollback();
                $error = "Error crítico al procesar la producción: " . $e->getMessage();
            }
        }
    }
}

// CONSULTAS: Obtiene los productos para el formulario de selección y la tabla general.
$productosLista = $conexion->query("SELECT * FROM productos ORDER BY nombre ASC");
$productosTabla = $conexion->query("SELECT * FROM productos ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <title>Órdenes de Producción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>

<div class="container mt-5">

    <!-- ALERTAS: Muestra mensajes de éxito o error -->
    <?php if (!empty($error)) { ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <strong>⚠️ Atención:</strong> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <?php if (!empty($exito)) { ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>✅ ¡Éxito!</strong> <?php echo $exito; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>
    
    <!-- SECCIÓN: FORMULARIO DE ORDEN DE PRODUCCIÓN -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3 text-center">Registrar Orden de Producción (Fabricación)</h2>
                    
                    <form method="POST" class="row g-3">
                        
                        <div class="col-12">
                            <label class="form-label">Producto a Fabricar</label>
                            <select name="producto_id" class="form-select" required>
                                <option value="">Seleccionar pintura o producto...</option>
                                <?php while($p = $productosLista->fetch_assoc()) { ?>
                                    <option value="<?php echo $p['id']; ?>">
                                        <?php echo $p['nombre'] . ' (Stock actual: ' . $p['stock'] . ' un.) - $' . $p['precio']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cantidad a Producir (Unidades)</label>
                            <input type="number" min="1" name="cantidad_fabricar" class="form-control" placeholder="Ej: 5" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Producción</label>
                            <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="col-12 mt-4">
                            <!-- BOTÓN: Envía la orden para fabricar y descontar insumos -->
                            <button type="submit" name="ejecutar_produccion" class="btn btn-primary w-100 py-2 fw-bold">🚀 Ejecutar Producción y Actualizar Stock Comercial</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: TABLA DE STOCK DE PRODUCTOS COMERCIALES -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Stock Actual de Productos (Listos para Facturar y Vender)</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock Disponible</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prod = $productosTabla->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $prod['id']; ?></td>
                                <td><strong><?php echo $prod['nombre']; ?></strong></td>
                                <td><?php echo $prod['categoria']; ?></td>
                                <td>$<?php echo $prod['precio']; ?></td>
                                <td><span class="badge bg-success fs-6"><?php echo $prod['stock']; ?> unidades</span></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN: RECETARIO Y DISPONIBILIDAD DE STOCK -->
    <div class="card shadow mb-5">
        <div class="card-body">
            <h2 class="h5 mb-4">Recetario de Producción y Disponibilidad de Stock</h2>
            <div class="row">
                <?php
                // CONSULTA: Relaciona recetas, productos y materias primas para calcular la disponibilidad.
                $sql_recetas = "SELECT p.nombre AS producto, m.nombre AS materia_prima, r.cantidad_necesaria, m.stock AS materia_stock 
                                FROM recetas r
                                INNER JOIN productos p ON r.producto_id = p.id
                                INNER JOIN materias_primas m ON r.materia_id = m.id
                                ORDER BY p.nombre ASC";
                $resultado_recetas = $conexion->query($sql_recetas);

                $recetas_agrupadas = [];
                if ($resultado_recetas && $resultado_recetas->num_rows > 0) {
                    while ($row = $resultado_recetas->fetch_assoc()) {
                        $max_posible_insumo = ($row['cantidad_necesaria'] > 0) ? floor($row['materia_stock'] / $row['cantidad_necesaria']) : 0;

                        $recetas_agrupadas[$row['producto']][] = [
                            'materia_prima' => $row['materia_prima'],
                            'cantidad' => $row['cantidad_necesaria'],
                            'stock' => $row['materia_stock'],
                            'max_posible' => $max_posible_insumo
                        ];
                    }
                }

                if (!empty($recetas_agrupadas)) {
                    foreach ($recetas_agrupadas as $producto => $ingredientes) {
                        $unidades_posibles_totales = 999999;
                        foreach ($ingredientes as $ing) {
                            if ($ing['max_posible'] < $unidades_posibles_totales) {
                                $unidades_posibles_totales = $ing['max_posible'];
                            }
                        }
                        if (empty($ingredientes)) $unidades_posibles_totales = 0;

                        $badge_color = ($unidades_posibles_totales > 0) ? 'bg-success' : 'bg-danger';

                        echo '<div class="col-md-6 mb-4">
                                <div class="card h-100 border-primary shadow-sm">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
                                        <h5 class="mb-0 fs-6 fw-bold">🎨 ' . $producto . '</h5>
                                        <span class="badge ' . $badge_color . ' fs-6 px-3 py-1">Se pueden fabricar: ' . $unidades_posibles_totales . ' un.</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped mb-0 align-middle">
                                                <thead class="table-light text-muted small">
                                                    <tr>
                                                        <th class="ps-3">Insumo</th>
                                                        <th class="text-center">Stock Disp.</th>
                                                        <th class="text-end pe-3">Necesario (por unidad)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>';
                        
                        foreach ($ingredientes as $ing) {
                            $fila_alerta = ($ing['stock'] < $ing['cantidad']) ? 'table-danger' : '';
                            
                            echo '<tr class="' . $fila_alerta . '">
                                    <td class="ps-3 fw-semibold small">' . $ing['materia_prima'] . '</td>
                                    <td class="text-center small">' . number_format($ing['stock'], 1, ',', '.') . '</td>
                                    <td class="text-end pe-3 small">' . number_format($ing['cantidad'], 1, ',', '.') . ' un/kg</td>
                                  </tr>';
                        }

                        echo '                  </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                              </div>';
                    }
                } else {
                    echo '<div class="col-12"><p class="text-muted">No hay recetas configuradas o faltan relaciones en la base de datos.</p></div>';
                }
                ?>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>