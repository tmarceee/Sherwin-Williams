<?php  
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");  
  
// PARÁMETROS: Captura el tipo de informe seleccionado y el filtro de ventas (mes o año).
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'clientes';  
$filtroVentas = isset($_GET['filtro_ventas']) ? $_GET['filtro_ventas'] : 'mes';  
  
// 1. INFORME DE CLIENTES (Deuda e Historial de compras)  
if ($tipo == 'clientes') {  
    $tituloInforme = "Informe de Clientes (Historial de Compras y Deuda)";  
    
    // CONSULTA: Obtiene datos del cliente, total de pedidos y deuda estimada por pedidos pendientes.
    $datosInforme = $conexion->query("  
        SELECT   
            c.id, c.nombre, c.telefono, c.email, c.direccion,  
            COUNT(p.id) AS total_pedidos,  
            COALESCE(SUM(CASE WHEN p.estado = 'pendiente' THEN pd.cantidad * pd.precio_unitario ELSE 0 END), 0) AS deuda_actual  
        FROM clientes c  
        LEFT JOIN pedidos p ON c.id = p.cliente_id  
        LEFT JOIN pedido_detalle pd ON p.id = pd.pedido_id  
        GROUP BY c.id, c.nombre, c.telefono, c.email, c.direccion  
        ORDER BY c.nombre ASC  
    ");  
}  
  
// 2. INFORME DE PEDIDOS FACTURADOS  
elseif ($tipo == 'facturados') {  
    $tituloInforme = "Informe de Pedidos Facturados";  
    // CONSULTA: Lista todos los pedidos que ya fueron facturados.
    $datosInforme = $conexion->query("  
        SELECT   
            p.id, p.fecha, c.nombre AS cliente, pr.nombre AS producto,   
            pd.cantidad, (pd.cantidad * pd.precio_unitario) AS total  
        FROM pedidos p  
        INNER JOIN clientes c ON p.cliente_id = c.id  
        INNER JOIN pedido_detalle pd ON p.id = pd.pedido_id  
        INNER JOIN productos pr ON pd.producto_id = pr.id  
        WHERE p.estado = 'facturado'  
        ORDER BY p.id DESC  
    ");  
}  
  
// 3. INFORME DE PEDIDOS ANULADOS  
elseif ($tipo == 'anulados') {  
    $tituloInforme = "Informe de Pedidos Anulados";  
    // CONSULTA: Lista todos los pedidos que fueron anulados.
    $datosInforme = $conexion->query("  
        SELECT   
            p.id, p.fecha, c.nombre AS cliente, pr.nombre AS producto,   
            pd.cantidad, (pd.cantidad * pd.precio_unitario) AS total  
        FROM pedidos p  
        INNER JOIN clientes c ON p.cliente_id = c.id  
        INNER JOIN pedido_detalle pd ON p.id = pd.pedido_id  
        INNER JOIN productos pr ON pd.producto_id = pr.id  
        WHERE p.estado = 'anulado'  
        ORDER BY p.id DESC  
    ");  
}  
  
// 4. INFORME DE PEDIDOS PENDIENTES  
elseif ($tipo == 'pendientes') {  
    $tituloInforme = "Informe de Pedidos Pendientes";  
    // CONSULTA: Lista los pedidos que siguen pendientes.
    $datosInforme = $conexion->query("  
        SELECT   
            p.id, p.fecha, c.nombre AS cliente, pr.nombre AS producto,   
            pd.cantidad, (pd.cantidad * pd.precio_unitario) AS total  
        FROM pedidos p  
        INNER JOIN clientes c ON p.cliente_id = c.id  
        INNER JOIN pedido_detalle pd ON p.id = pd.pedido_id  
        INNER JOIN productos pr ON pd.producto_id = pr.id  
        WHERE p.estado = 'pendiente'  
        ORDER BY p.id DESC  
    ");  
}  
  
// 5. INFORME DE STOCK DE MATERIAS PRIMAS  
elseif ($tipo == 'materias') {  
    $tituloInforme = "Informe de Stock de Materias Primas";  
    // CONSULTA: Muestra el stock actual de cada insumo.
    $datosInforme = $conexion->query("  
        SELECT id, nombre, stock   
        FROM materias_primas   
        ORDER BY nombre ASC  
    ");  
}  
  
// 6. INFORME DE VENTAS (Por Mes o Por Año)  
elseif ($tipo == 'ventas') {  
    if ($filtroVentas == 'anio') {  
        $tituloInforme = "Informe de Ventas por Año";  
        // CONSULTA: Agrupa las ventas totales por año.
        $datosInforme = $conexion->query("  
            SELECT   
                YEAR(fecha) AS periodo,  
                COUNT(id) AS cantidad_facturas,  
                SUM(total) AS total_recaudado  
            FROM facturas  
            GROUP BY YEAR(fecha)  
            ORDER BY periodo DESC  
        ");  
    } else {  
        $tituloInforme = "Informe de Ventas por Mes";  
        // CONSULTA: Agrupa las ventas totales por mes y año.
        $datosInforme = $conexion->query("  
            SELECT   
                CONCAT(YEAR(fecha), '-', LPAD(MONTH(fecha), 2, '0')) AS periodo_orden,  
                YEAR(fecha) AS anio,  
                MONTH(fecha) AS mes,  
                COUNT(id) AS cantidad_facturas,  
                SUM(total) AS total_recaudado  
            FROM facturas  
            GROUP BY YEAR(fecha), MONTH(fecha)  
            ORDER BY periodo_orden DESC  
        ");  
    }  
}  
?>  
  
<!DOCTYPE html>  
<html lang="es">  
<head>  
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <title>Informes del Sistema</title>  
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">  
      
    <!-- LIBRERÍAS: Carga jsPDF para permitir la exportación directa a PDF desde el navegador -->  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>  
</head>  
<body class="bg-light">  
  
<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>  
  
<div class="container mt-5 mb-5">  
   
    <!-- SECCIÓN: ENCABEZADO Y SELECTOR DE INFORMES -->  
    <div class="card shadow mb-4">  
        <div class="card-body">  
            <h1 class="h3 mb-3 fw-bold text-primary">📊 Panel de Informes y Estadísticas</h1>  
            <p class="text-muted">Seleccione el informe que desea consultar. Puede ver deudas, historial y ventas desglosadas por mes o año.</p>  
  
            <div class="row g-2 align-items-center">  
                <div class="col-md-9">  
                    <!-- BOTONES: Permiten alternar entre los distintos tipos de informes -->
                    <div class="btn-group flex-wrap w-100" role="group">  
                        <a href="informes.php?tipo=clientes" class="btn btn-outline-primary <?php echo $tipo == 'clientes' ? 'active' : ''; ?>">Clientes (Deuda / Compras)</a>  
                        <a href="informes.php?tipo=facturados" class="btn btn-outline-primary <?php echo $tipo == 'facturados' ? 'active' : ''; ?>">Facturados</a>  
                        <a href="informes.php?tipo=pendientes" class="btn btn-outline-primary <?php echo $tipo == 'pendientes' ? 'active' : ''; ?>">Pendientes</a>  
                        <a href="informes.php?tipo=anulados" class="btn btn-outline-primary <?php echo $tipo == 'anulados' ? 'active' : ''; ?>">Anulados</a>  
                        <a href="informes.php?tipo=materias" class="btn btn-outline-primary <?php echo $tipo == 'materias' ? 'active' : ''; ?>">Stock Materias</a>  
                        <a href="informes.php?tipo=ventas" class="btn btn-outline-primary <?php echo $tipo == 'ventas' ? 'active' : ''; ?>">Ventas</a>  
                    </div>  
                </div>  
                <div class="col-md-3 text-md-end">  
                    <!-- BOTÓN: Llama a la función de JavaScript para generar el PDF -->
                    <button onclick="descargarPDF()" class="btn btn-danger fw-bold w-100 shadow-sm">  
                        📥 Descargar PDF Directo  
                    </button>  
                </div>  
            </div>  
  
            <!-- SUBFILTROS: Opciones adicionales si se selecciona el informe de ventas -->
            <?php if ($tipo == 'ventas') { ?>  
                <div class="mt-3 pt-3 border-top d-flex align-items-center gap-2">  
                    <span class="text-muted small fw-bold">Ver ventas por:</span>  
                    <a href="informes.php?tipo=ventas&filtro_ventas=mes" class="btn btn-sm <?php echo $filtroVentas == 'mes' ? 'btn-dark' : 'btn-outline-dark'; ?>">Mes</a>  
                    <a href="informes.php?tipo=ventas&filtro_ventas=anio" class="btn btn-sm <?php echo $filtroVentas == 'anio' ? 'btn-dark' : 'btn-outline-dark'; ?>">Año</a>  
                </div>  
            <?php } ?>  
        </div>  
    </div>  
  
    <!-- SECCIÓN: TABLA DE RESULTADOS DEL INFORME -->  
    <div class="card shadow">  
        <div class="card-body">  
            <div class="d-flex justify-content-between align-items-center mb-4">  
                <h2 class="h4 text-secondary fw-bold" id="titulo-informe">  
                    <?php echo $tituloInforme; ?>  
                </h2>  
                <span class="text-muted small">Emisión: <?php echo date("d/m/Y"); ?></span>  
            </div>  
  
            <div class="table-responsive">  
                <table id="tabla-informe" class="table table-bordered table-striped align-middle">  
                    <thead class="table-dark">  
                        <?php if ($tipo == 'clientes') { ?>  
                            <tr>  
                                <th>ID</th>  
                                <th>Cliente</th>  
                                <th>Contacto</th>  
                                <th>Total Pedidos Realizados</th>  
                                <th>Deuda Actual Estimada</th>  
                            </tr>  
                        <?php } elseif ($tipo == 'facturados' || $tipo == 'pendientes' || $tipo == 'anulados') { ?>  
                            <tr>  
                                <th>ID Pedido</th>  
                                <th>Fecha</th>  
                                <th>Cliente</th>  
                                <th>Producto</th>  
                                <th>Cantidad</th>  
                                <th>Total</th>  
                            </tr>  
                        <?php } elseif ($tipo == 'materias') { ?>  
                            <tr>  
                                <th>ID</th>  
                                <th>Materia Prima</th>  
                                <th>Stock Actual</th>  
                            </tr>  
                        <?php } elseif ($tipo == 'ventas') { ?>  
                            <tr>  
                                <th>Período</th>  
                                <th>Cantidad de Facturas</th>  
                                <th>Total Recaudado</th>  
                            </tr>  
                        <?php } ?>  
                    </thead>  
                    <tbody>  
                        <?php if ($datosInforme->num_rows == 0) { ?>  
                            <tr>  
                                <td colspan="6" class="text-center text-muted py-4">No se encontraron registros para este informe.</td>  
                            </tr>  
                        <?php } else { ?>  
                            <?php while($row = $datosInforme->fetch_assoc()) { ?>  
                                <?php if ($tipo == 'clientes') { ?>  
                                    <tr>  
                                        <td>#<?php echo $row['id']; ?></td>  
                                        <td>  
                                            <strong><?php echo $row['nombre']; ?></strong><br>  
                                            <small class="text-muted"><?php echo !empty($row['direccion']) ? $row['direccion'] : 'Sin dirección'; ?></small>  
                                        </td>  
                                        <td>  
                                            Tel: <?php echo $row['telefono']; ?><br>  
                                            <small class="text-muted"><?php echo !empty($row['email']) ? $row['email'] : 'Sin email'; ?></small>  
                                        </td>  
                                        <td><span class="badge bg-secondary"><?php echo $row['total_pedidos']; ?> compras</span></td>  
                                        <td>  
                                            <?php if ($row['deuda_actual'] > 0) { ?>  
                                                <span class="badge bg-danger fs-6">$<?php echo number_format($row['deuda_actual'], 2, ',', '.'); ?></span>  
                                            <?php } else { ?>  
                                                <span class="badge bg-success">Sin Deuda</span>  
                                            <?php } ?>  
                                        </td>  
                                    </tr>  
                                <?php } elseif ($tipo == 'facturados' || $tipo == 'pendientes' || $tipo == 'anulados') { ?>  
                                    <tr>  
                                        <td>#<?php echo $row['id']; ?></td>  
                                        <td><?php echo date("d/m/Y", strtotime($row['fecha'])); ?></td>  
                                        <td><?php echo $row['cliente']; ?></td>  
                                        <td><?php echo $row['producto']; ?></td>  
                                        <td><?php echo $row['cantidad']; ?> un.</td>  
                                        <td>$<?php echo number_format($row['total'], 2, ',', '.'); ?></td>  
                                    </tr>  
                                <?php } elseif ($tipo == 'materias') { ?>  
                                    <tr>  
                                        <td>#<?php echo $row['id']; ?></td>  
                                        <td><strong><?php echo $row['nombre']; ?></strong></td>  
                                        <td><?php echo $row['stock']; ?> un. / kg / lts</td>  
                                    </tr>  
                                <?php } elseif ($tipo == 'ventas') { ?>  
                                    <tr>  
                                        <td>  
                                            <strong>  
                                                <?php   
                                                if ($filtroVentas == 'anio') {  
                                                    echo "Año " . $row['periodo'];  
                                                } else {  
                                                    $meses = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];  
                                                    echo $meses[$row['mes']] . " " . $row['anio'];  
                                                }  
                                                ?>  
                                            </strong>  
                                        </td>  
                                        <td><?php echo $row['cantidad_facturas']; ?> facturas</td>  
                                        <td><strong class="text-success">$<?php echo number_format($row['total_recaudado'], 2, ',', '.'); ?></strong></td>  
                                    </tr>  
                                <?php } ?>  
                            <?php } ?>  
                        <?php } ?>  
                    </tbody>  
                </table>  
            </div>  
  
        </div>  
    </div>  
  
</div>  
  
<!-- SCRIPT: Función de JavaScript que captura la tabla actual y genera el archivo PDF -->  
<script>  
function descargarPDF() {  
    const { jsPDF } = window.jspdf;  
    const doc = new jsPDF();  
      
    const titulo = document.getElementById('titulo-informe').innerText;  
    const fechaEmision = "Fecha de emisión: " + new Date().toLocaleDateString();  
      
    doc.setFontSize(18);  
    doc.setTextColor(40, 40, 40);  
    doc.text("Fábrica de Pinturas - Reportes", 14, 20);  
      
    doc.setFontSize(12);  
    doc.setTextColor(100, 100, 100);  
    doc.text(titulo, 14, 28);  
      
    doc.setFontSize(10);  
    doc.text(fechaEmision, 14, 34);  
      
    doc.autoTable({  
        html: '#tabla-informe',  
        startY: 40,  
        theme: 'grid',  
        headStyles: { fillColor: [33, 37, 41] },  
        styles: { fontSize: 9, cellPadding: 4 }  
    });  
      
    const nombreArchivo = titulo.toLowerCase().replace(/[^a-z0-9]/g, '_') + '.pdf';  
    doc.save(nombreArchivo);  
}  
</script>  
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>  
</body>  
</html>