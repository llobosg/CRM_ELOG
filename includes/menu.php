<!-- includes/menu.php -->
<!-- Menú principal -->
<nav style="background: #3a4f63; padding: 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <ul style="list-style: none; margin: 0; padding: 0; display: flex; align-items: center;">
        <li><a href="?page=dashboard" style="color: white; text-decoration: none; padding: 1rem 1.2rem; display: block; font-weight: 500;">Dashboard</a></li>
        <li><a href="?page=prospectos" style="color: white; text-decoration: none; padding: 1rem 1.2rem; display: block; font-weight: 500;">Prospectos</a></li>
        
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <li style="position: relative;">
            <a href="#" id="menu-tablas" style="color: white; text-decoration: none; padding: 1rem 1.2rem; display: block; font-weight: 500; cursor: pointer;">
                Tablas <i class="fas fa-caret-down" style="margin-left: 0.4rem;"></i>
            </a>
            <div id="submenu-tablas" style="display: none; position: absolute; top: 100%; left: 0; background: white; min-width: 200px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 6px; z-index: 1000;">
                <?php
                $tablas = [
                    ['label' => 'Agentes', 'page' => 'agentes'],
                    ['label' => 'Aplicación costos', 'page' => 'aplicacion_costos'],
                    ['label' => 'Comerciales', 'page' => 'comerciales'],
                    ['label' => 'Commoditys', 'page' => 'commoditys'],
                    ['label' => 'Concéptos', 'page' => 'conceptos'],
                    ['label' => 'Contactos', 'page' => 'contactos'],
                    ['label' => 'Incoterm', 'page' => 'incoterm'],
                    ['label' => 'Lugares', 'page' => 'lugares'],
                    ['label' => 'Medios de Transporte', 'page' => 'medios_transporte'],
                    ['label' => 'Operación', 'page' => 'operacion'],
                    ['label' => 'Proveedores', 'page' => 'proveedor_pnac'],
                    ['label' => 'Servicios', 'page' => 'cartaservicios'],
                    ['label' => 'Tráfico', 'page' => 'trafico']
                ];
                foreach ($tablas as $t) {
                    echo "<a href='?page={$t['page']}' style='display: block; padding: 0.6rem 1rem; color: #333; text-decoration: none; border-bottom: 1px solid #eee; font-size: 0.95rem;'>{$t['label']}</a>";
                }
                ?>
            </div>
        </li>
        <?php endif; ?>
    </ul>
</nav>

<!-- Script para desplegar menú Tablas -->
<script>
document.getElementById('menu-tablas')?.addEventListener('click', function(e) {
    e.preventDefault();
    const submenu = document.getElementById('submenu-tablas');
    submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
});
// Cerrar al hacer clic fuera
document.addEventListener('click', function(e) {
    const menu = document.getElementById('menu-tablas');
    const submenu = document.getElementById('submenu-tablas');
    if (menu && !menu.contains(e.target) && submenu && !submenu.contains(e.target)) {
        submenu.style.display = 'none';
    }
});
</script>

<!-- Espacio para que el contenido no quede debajo del menú fijo -->
<div style="height: 70px;"></div>

<!-- Estilos para el menú -->
<style>
.nav-link {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    position: relative;
}

.nav-link:hover {
    background: #5a6e82;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Estilo para la opción activa: fondo suave + borde inferior elegante */
.nav-link.active {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    font-weight: 600;
    transform: none;
    box-shadow: none;
}

.nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #007bff, #00c6ff);
    border-radius: 2px 2px 0 0;
}
</style>