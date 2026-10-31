<?php
// CONEXIÓN: Incluye la conexión a la base de datos.
include("conexion.php");

// PROCESO: Agrega un nuevo cliente a la base de datos.
if (isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);

    if (!empty($nombre) && !empty($telefono)) {
        $sql = "INSERT INTO clientes (nombre, telefono, email, direccion) 
                VALUES ('$nombre', '$telefono', '$email', '$direccion')";
        $conexion->query($sql);
        header("Location: index.php?mensaje=guardado");
        exit();
    }
}

// PROCESO: Elimina un cliente según su ID.
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conexion->query("DELETE FROM clientes WHERE id = $id");
    header("Location: index.php?mensaje=eliminado");
    exit();
}

// PROCESO: Actualiza los datos de un cliente existente.
if (isset($_POST['actualizar'])) {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);

    if (!empty($nombre) && !empty($telefono)) {
        $sql = "UPDATE clientes 
                SET nombre='$nombre', telefono='$telefono', email='$email', direccion='$direccion' 
                WHERE id=$id";
        $conexion->query($sql);
        header("Location: index.php?mensaje=actualizado");
        exit();
    }
}

// CONSULTA: Obtiene los datos de un cliente específico para cargarlos en el formulario de edición.
$clienteEditar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $resultadoEditar = $conexion->query("SELECT * FROM clientes WHERE id = $id");
    $clienteEditar = $resultadoEditar->fetch_assoc();
}

// CONSULTA: Lista todos los clientes registrados ordenados del más nuevo al más viejo.
$resultado = $conexion->query("SELECT * FROM clientes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="logo.png" type="image/png">
    <title>Módulo de Clientes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- MENÚ: Barra de navegación superior -->
<?php include("menu.php"); ?>

<div class="container mt-5">

    <!-- ALERTAS: Mensaje de éxito al registrar un cliente -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'guardado') { ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>✅ ¡Éxito!</strong> Cliente registrado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- ALERTAS: Mensaje de éxito al actualizar un cliente -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'actualizado') { ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <strong>✅ ¡Éxito!</strong> Datos del cliente actualizados correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- ALERTAS: Mensaje de advertencia al eliminar un cliente -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'eliminado') { ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <strong>🗑️ ¡Eliminado!</strong> El cliente fue borrado del sistema.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php } ?>

    <!-- SECCIÓN: FORMULARIO DE ALTA O EDICIÓN DE CLIENTES -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <h1 class="mb-4">Gestión de Clientes</h1>

            <?php if ($clienteEditar) { ?>
                <!-- FORMULARIO DE EDICIÓN -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 text-warning fw-bold mb-0">✏️ Editando al cliente: <?php echo $clienteEditar['nombre']; ?></h2>
                    <a href="index.php" class="btn btn-sm btn-outline-secondary">Cancelar edición</a>
                </div>

                <form method="POST" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo $clienteEditar['id']; ?>">

                    <div class="col-md-6">
                        <label class="form-label">Nombre y Apellido</label>
                        <input type="text" name="nombre" class="form-control" 
                               value="<?php echo $clienteEditar['nombre']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" 
                               value="<?php echo $clienteEditar['telefono']; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?php echo $clienteEditar['email']; ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" 
                               value="<?php echo $clienteEditar['direccion']; ?>">
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" name="actualizar" class="btn btn-warning fw-bold px-4">Actualizar Cliente</button>
                    </div>
                </form>
            <?php } else { ?>
                <!-- FORMULARIO DE NUEVO CLIENTE -->
                <h2 class="h5 mb-3 text-primary fw-bold">➕ Agregar Nuevo Cliente</h2>
                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre y Apellido</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" placeholder="Ej: 1122334455" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Ej: correo@email.com">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" placeholder="Ej: Av. Rivadavia 1234">
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" name="guardar" class="btn btn-primary w-100 py-2 fw-bold">💾 Guardar Cliente</button>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>

    <!-- SECCIÓN: TABLA DE CLIENTES REGISTRADOS -->
    <div class="card shadow">
        <div class="card-body">
            <h2 class="h5 mb-3 text-secondary fw-bold">Lista de Clientes Registrados</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre y Apellido</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado->num_rows == 0) { ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No hay clientes registrados todavía.</td>
                            </tr>
                        <?php } else { ?>
                            <?php while($fila = $resultado->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $fila['id']; ?></td>
                                    <td><strong><?php echo $fila['nombre']; ?></strong></td>
                                    <td><?php echo $fila['telefono']; ?></td>
                                    <td><?php echo !empty($fila['email']) ? $fila['email'] : '<span class="text-muted italic">-</span>'; ?></td>
                                    <td><?php echo !empty($fila['direccion']) ? $fila['direccion'] : '<span class="text-muted italic">-</span>'; ?></td>
                                    <td class="text-center">
                                        <!-- BOTÓN: Carga los datos del cliente para editar -->
                                        <a href="index.php?editar=<?php echo $fila['id']; ?>" class="btn btn-sm btn-warning fw-bold px-2">Editar</a>
                                        <!-- BOTÓN: Elimina al cliente del sistema -->
                                        <a href="index.php?eliminar=<?php echo $fila['id']; ?>" 
                                           class="btn btn-sm btn-danger fw-bold px-2"
                                           onclick="return confirm('¿Seguro que querés eliminar a este cliente?')">
                                           Eliminar
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

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>