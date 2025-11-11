<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema de Horarios - FICCT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --sidebar-width: 260px; }
        body { overflow-x: hidden; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3c72 0%, #2a5298 100%);
            width: var(--sidebar-width);
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar.collapsed { transform: translateX(-100%); }
        .content-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s;
            min-height: 100vh;
            background: #f5f7fa;
        }
        .content-wrapper.expanded { margin-left: 0; }
        .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #4fc3f7;
        }
        .nav-link.active {
            background: rgba(79, 195, 247, 0.2);
            color: white;
            border-left-color: #4fc3f7;
        }
        .submenu {
            background: rgba(0,0,0,0.1);
            padding-left: 15px;
        }
        .submenu .nav-link {
            font-size: 0.9rem;
            padding: 10px 20px;
        }
        .logo-section {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo-section h4 {
            margin: 10px 0 5px 0;
            font-weight: bold;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card:hover { transform: translateY(-2px); }
        .btn { border-radius: 6px; }
        .table thead { background: #f8f9fa; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .content-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-section text-white">
            <i class="bi bi-calendar-check fs-1"></i>
            <h4 class="mb-0">Sistema FICCT</h4>
            <small class="text-white-50">Gestión de Horarios</small>
        </div>
        
        <nav class="nav flex-column mt-3">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door me-2"></i> Dashboard
            </a>
            
            <!-- Paquete Administración -->
            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuAdmin">
                    <i class="bi bi-shield-lock me-2"></i> Administración
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse submenu {{ request()->is('administracion/*') ? 'show' : '' }}" id="menuAdmin">
                    <a href="{{ route('administracion.usuarios.index') }}" 
                       class="nav-link {{ request()->routeIs('administracion.usuarios.*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i> Usuarios y Roles
                    </a>
                    <a href="{{ route('administracion.bitacora.index') }}" 
                       class="nav-link {{ request()->routeIs('administracion.bitacora.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-text me-2"></i> Bitácora
                    </a>
                    <a href="{{ route('administracion.importacion.index') }}" 
                       class="nav-link {{ request()->routeIs('administracion.importacion.*') ? 'active' : '' }}">
                        <i class="bi bi-cloud-upload me-2"></i> Importar Datos
                    </a>
                </div>
            </div>

            <!-- Paquete Configuración Académica -->
            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuConfig">
                    <i class="bi bi-gear me-2"></i> Configuración Académica
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse submenu {{ request()->is('configuracion-academica/*') ? 'show' : '' }}" id="menuConfig">
                    <a href="{{ route('configuracion-academica.gestiones.index') }}" 
                       class="nav-link {{ request()->routeIs('configuracion-academica.gestiones.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-range me-2"></i> Gestiones
                    </a>
                    <a href="{{ route('configuracion-academica.docentes.index') }}" 
                       class="nav-link {{ request()->routeIs('configuracion-academica.docentes.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge me-2"></i> Docentes
                    </a>
                    <a href="{{ route('configuracion-academica.materias.index') }}" 
                       class="nav-link {{ request()->routeIs('configuracion-academica.materias.*') ? 'active' : '' }}">
                        <i class="bi bi-book me-2"></i> Materias
                    </a>
                    <a href="{{ route('configuracion-academica.grupos.index') }}" 
                       class="nav-link {{ request()->routeIs('configuracion-academica.grupos.*') ? 'active' : '' }}">
                        <i class="bi bi-collection me-2"></i> Grupos
                    </a>
                    <a href="{{ route('configuracion-academica.aulas.index') }}" 
                       class="nav-link {{ request()->routeIs('configuracion-academica.aulas.*') ? 'active' : '' }}">
                        <i class="bi bi-door-open me-2"></i> Aulas
                    </a>
                </div>
            </div>

            <!-- Paquete Planificación -->
            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuPlan">
                    <i class="bi bi-calendar3 me-2"></i> Planificación
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse submenu {{ request()->is('planificacion/*') ? 'show' : '' }}" id="menuPlan">
                    <a href="{{ route('planificacion.horarios.index') }}" 
                       class="nav-link {{ request()->routeIs('planificacion.horarios.*') ? 'active' : '' }}">
                        <i class="bi bi-clock me-2"></i> Asignar Horarios
                    </a>
                </div>
            </div>

            <!-- Paquete Control de Seguimiento -->
            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuSeguimiento">
                    <i class="bi bi-clipboard-check me-2"></i> Control de Seguimiento
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse submenu {{ request()->is('control-seguimiento/*') ? 'show' : '' }}" id="menuSeguimiento">
                    <a href="{{ route('control-seguimiento.asistencia.create') }}" 
                       class="nav-link {{ request()->routeIs('control-seguimiento.asistencia.create') ? 'active' : '' }}">
                        <i class="bi bi-check-circle me-2"></i> Registrar Asistencia
                    </a>
                    <a href="{{ route('control-seguimiento.asistencia.index') }}" 
                       class="nav-link {{ request()->routeIs('control-seguimiento.asistencia.index') ? 'active' : '' }}">
                        <i class="bi bi-list-check me-2"></i> Ver Asistencia
                    </a>
                    <a href="{{ route('control-seguimiento.consultas.dashboard') }}" 
                       class="nav-link {{ request()->routeIs('control-seguimiento.consultas.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-week me-2"></i> Consultar Horarios
                    </a>
                    <a href="{{ route('control-seguimiento.asistencia.estadisticas') }}" 
                       class="nav-link {{ request()->routeIs('control-seguimiento.asistencia.estadisticas') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart me-2"></i> Estadísticas
                    </a>
                </div>
            </div>

            <!-- Paquete Reportes -->
            <div class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuReportes">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Reportes y Datos
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <div class="collapse submenu {{ request()->is('reporte-datos/*') ? 'show' : '' }}" id="menuReportes">
                    <a href="{{ route('reporte-datos.reportes.index') }}" 
                       class="nav-link {{ request()->routeIs('reporte-datos.reportes.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-pdf me-2"></i> Generar Reportes
                    </a>
                </div>
            </div>

            <hr class="text-white mx-3">
            
            <form action="{{ route('logout') }}" method="POST" class="px-3">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                </button>
            </form>
        </nav>
    </div>

    <!-- Content -->
    <div class="content-wrapper" id="content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
            <div class="container-fluid">
                <button class="btn btn-link text-dark" id="toggleSidebar">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <span class="navbar-brand mb-0 h1 ms-2">@yield('page-title', 'Dashboard')</span>
                <div class="ms-auto d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-person-circle fs-5 me-2"></i>
                        <span class="fw-medium">{{ Auth::user()->username }}</span>
                    </div>
                    <span class="badge bg-primary">{{ Auth::user()->rol->nombre ?? 'Usuario' }}</span>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Errores de validación:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('toggleSidebar')?.addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            
            if(window.innerWidth <= 768) {
                sidebar.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');
            }
        });

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Keep submenu open on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeLink = document.querySelector('.submenu .nav-link.active');
            if(activeLink) {
                const submenu = activeLink.closest('.collapse');
                if(submenu) {
                    new bootstrap.Collapse(submenu, { toggle: false }).show();
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
