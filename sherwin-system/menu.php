<!-- MENÚ PRINCIPAL: Barra de navegación superior responsive construida con Bootstrap 5 -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">  
    <div class="container-fluid">  
        <!-- LOGO / TÍTULO: Enlace principal que redirige al módulo de producción -->
        <a class="navbar-brand fw-bold" href="produccion.php">🎨 Fábrica de Pinturas</a>  
        
        <!-- BOTÓN RESPONSIVE: Muestra el menú en pantallas chicas o celulares -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">  
            <span class="navbar-toggler-icon"></span>  
        </button>  
        
        <!-- LISTA DE ENLACES: Contiene las rutas a todos los módulos del sistema -->
        <div class="collapse navbar-collapse" id="navbarNav">  
            <ul class="navbar-nav ms-auto">  
                <!-- MÓDULO CLIENTES -->
                <li class="nav-item">  
                    <a class="nav-link" href="index.php">👥 Clientes</a>  
                </li>  
                
                <!-- MÓDULO MATERIAS PRIMAS -->
                <li class="nav-item">  
                    <a class="nav-link" href="materias_primas.php">📦 Materias Primas</a>  
                </li>  
                
                <!-- MÓDULO PRODUCCIÓN -->
                <li class="nav-item">  
                    <a class="nav-link" href="produccion.php">⚙️ Producción</a>  
                </li>  
                
                <!-- MÓDULO PEDIDOS -->
                <li class="nav-item">  
                    <a class="nav-link" href="pedidos.php">📜 Pedidos</a>  
                </li>  
                
                <!-- MÓDULO FACTURACIÓN -->
                <li class="nav-item">  
                    <a class="nav-link" href="facturacion.php">📄 Facturación</a>  
                </li>  
                
                <!-- MÓDULO ENTREGAS -->
                <li class="nav-item">  
                    <a class="nav-link" href="entregas.php">🚚 Entregas</a>  
                </li>  
                
                <!-- MÓDULO HISTORIAL DE STOCK -->
                <li class="nav-item">  
                    <a class="nav-link" href="stock.php">📊 Historial Stock</a>  
                </li>  
                
                <!-- MÓDULO INFORMES -->
                <li class="nav-item">  
                    <a class="nav-link" href="informes.php">📈 Informes</a>  
                </li>  
            </ul>  
        </div>  
    </div>  
</nav>