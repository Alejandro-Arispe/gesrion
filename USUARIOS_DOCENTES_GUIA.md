# 📋 GUÍA RÁPIDA: Sistema de Usuarios Docentes

## 🎯 Objetivo
Crear automáticamente usuarios para los docentes con credenciales seguras y permisos limitados.

---

## 📊 RESUMEN DE CAMBIOS

### 1. **Base de Datos**

#### Migraciones agregadas:
```
✅ 2025_11_11_add_id_docente_to_usuario.php
   └─ Agrega campo `id_docente` en tabla `usuario` (FK a docente)

✅ PermisoDocenteSeeder.php
   └─ Crea permisos específicos para docentes
   └─ Asigna permisos al rol "Docente"
```

#### Nuevos campos:
| Tabla | Campo | Tipo | Descripción |
|-------|-------|------|-------------|
| usuario | id_docente | FK | Referencia al docente |
| permiso | (nuevos) | - | marcar_asistencia_qr, ver_mis_horarios, etc |

#### Permisos creados:
- `marcar_asistencia_qr` - Marcar asistencia mediante QR
- `ver_mis_horarios` - Ver distribución de horarios propios
- `ver_mis_asistencias` - Consultar registro de asistencias
- `actualizar_perfil` - Editar información personal

---

## 🚀 INSTRUCCIONES DE USO

### Paso 1: Ejecutar Migraciones
```bash
php artisan migrate
php artisan db:seed --class=PermisoDocenteSeeder
```

### Paso 2: Crear Usuarios Masivamente
1. Ir a: **Administración → Gestión de Usuarios Docentes**
2. Ver tabla con:
   - Nombre del docente
   - Estado del usuario (Creado/Pendiente)
   - Correo
   - Acciones

3. Hacer clic en botón **"Generar Usuarios Faltantes"**
   - Sistema genera automáticamente:
     - Username: `primer.apellido` (ej: juan.perez)
     - Password: `Nombre123` (ej: Juan123)
     - Rol: Docente
     - Permisos: marcar_asistencia_qr, ver_mis_horarios

### Paso 3: Descargar Credenciales en PDF
1. Hacer clic en **"Descargar Credenciales en PDF"**
2. Se genera PDF con:
   - Nombre del docente
   - Usuario de acceso
   - Contraseña (oculta en PDF, mostrada en original)
   - Correo
   - Estado (Activo/Inactivo)

> ⚠️ **NOTA**: Las contraseñas en PDF están ocultas por seguridad. El documento original (HTML) las muestra.

### Paso 4: Opciones Adicionales
- **Regenerar Contraseña**: Botón con icono 🔑 → Genera nueva (Nombre123)
- **Desactivar Usuario**: Botón con icono 🚫 → Usuario no puede ingresar
- **Reactivar**: Editar usuario directamente desde Administración → Usuarios

---

## 🔐 SEGURIDAD

### Contrasenas
```
✅ Se almacenan con Hash::make() (BCrypt)
✅ Nunca se guardan en texto plano
✅ Se pueden regenerar desde la interfaz
✅ Se pueden cambiar desde el perfil del usuario
```

### Permisos
```
✅ Docentes solo pueden marcar asistencia mediante QR
✅ Solo ven sus propios horarios
✅ Solo ven sus propias asistencias
✅ No pueden acceder a configuración del sistema
```

### Middleware
- `ValidarPermisoRol` - Verifica permisos antes de ejecutar acción
- `auth` - Requiere autenticación
- Protege rutas de asistencia y horarios

---

## 📋 ESTRUCTURA DE DATOS

### Tabla: usuario (actualizada)
```
id_usuario (PK)
username (UNIQUE)
password (HASH)
correo
activo (BOOLEAN)
id_rol (FK → rol)
id_docente (FK → docente) ← NUEVO
created_at
updated_at
```

### Tabla: permiso (nueva)
```
id_permiso (PK)
nombre (UNIQUE)
descripcion
created_at
updated_at
```

### Tabla: rol_permiso (sin cambios)
```
id_rol (FK)
id_permiso (FK)
PRIMARY KEY (id_rol, id_permiso)
```

---

## 🛠️ FLUJO TÉCNICO

### Generar Usuarios (GeneradorUsuariosDocentesService)

```php
1. Obtener todos los docentes activos
2. Para cada docente:
   a. Verificar si ya existe usuario
   b. Si no existe:
      - Generar username: primer.apellido
      - Generar password: Nombre123
      - Hashear password con Hash::make()
      - Crear usuario con rol "Docente"
      - Asignar id_docente
   c. Si existe: omitir
3. Retornar resumen (creados, omitidos, errores)
```

### Rutas Implementadas

| Ruta | Método | Descripción |
|------|--------|-------------|
| `/administracion/usuarios-docentes` | GET | Vista principal |
| `/administracion/usuarios-docentes/generar-masivo` | POST | Crear usuarios |
| `/administracion/usuarios-docentes/descargar-credenciales-pdf` | GET | Descargar PDF |
| `/administracion/usuarios-docentes/{id}/regenerar-password` | POST | Regenerar contraseña |
| `/administracion/usuarios-docentes/{id}/desactivar` | POST | Desactivar usuario |

### Vistas Creadas

```
resources/views/administracion/
├── usuarios-docentes.blade.php
│  ├─ Tabla de docentes
│  ├─ Botón generar masivo
│  ├─ Estadísticas (total, creados, pendientes, %)
│  ├─ Acciones por fila (regenerar, desactivar)
│  └─ AJAX para operaciones
│
└── credenciales-docentes-pdf.blade.php
   ├─ Header con información
   ├─ Tabla de credenciales
   ├─ Advertencias de seguridad
   ├─ Contraseñas ocultas
   └─ Estilos para impresión
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

```
✅ Migración: id_docente en usuario
✅ Migración: permisos docentes
✅ Seeder: PermisoDocenteSeeder
✅ Service: GeneradorUsuariosDocentesService
✅ Controller: GeneradorUsuariosDocentesController
✅ Middleware: ValidarPermisoRol
✅ Rutas: usuarios-docentes
✅ Vista: usuarios-docentes.blade.php
✅ Vista PDF: credenciales-docentes-pdf.blade.php
✅ Kernel: Registrar middleware
✅ AsistenciaController: Proteger con middleware
```

---

## 🔍 EJEMPLOS DE USO

### Generar usuarios mediante artisan
```bash
# No es necesario, usar interfaz web
# Pero se puede llamar el servicio desde tinker:
php artisan tinker
> $service = app('App\Services\GeneradorUsuariosDocentesService');
> $resultado = $service->generarUsuariosDocentes();
> dd($resultado);
```

### Consultar usuarios docentes creados
```php
// En cualquier lugar del código:
$usuariosDocentes = Usuario::whereHas('docente')
                           ->where('activo', true)
                           ->with('docente')
                           ->get();

// Resultado:
// [
//   { id_usuario: 1, username: "juan.perez", correo: "juan@email.com", ... },
//   { id_usuario: 2, username: "maria.lopez", correo: "maria@email.com", ... }
// ]
```

### Verificar permisos de un usuario
```php
$usuario = Auth::user();
$permisos = DB::table('rol_permiso')
    ->join('permiso', 'rol_permiso.id_permiso', '=', 'permiso.id_permiso')
    ->where('rol_permiso.id_rol', $usuario->id_rol)
    ->pluck('permiso.nombre')
    ->toArray();

// $permisos = ['marcar_asistencia_qr', 'ver_mis_horarios', ...]
```

---

## ⚠️ CONSIDERACIONES

1. **Contraseñas iniciales**
   - Son: Nombre + 123 (ej: Juan123)
   - Los docentes DEBEN cambiarla en primer acceso
   - Recomendación: Avisar a docentes antes de generar

2. **Cambiar contraseña**
   - El docente ingresa con Nombre123
   - Va a: Perfil → Seguridad → Cambiar Contraseña
   - Ingresa contraseña actual (Nombre123) + nueva contraseña

3. **Permisos insuficientes**
   - Si falta ejecutar PermisoDocenteSeeder, ocurrirá error
   - Solución: `php artisan db:seed --class=PermisoDocenteSeeder`

4. **Usar ID de docente**
   - Después de crear usuarios, el campo `id_docente` en tabla `usuario` permite:
     - Asociar asistencias al docente correcto
     - Mostrar solo horarios del docente
     - Filtrar consultas por rol

---

## 📞 SOPORTE

Si encuentras errores:

1. **"No existe el rol Docente"**
   - Ejecuta: `php artisan db:seed --class=PermisoDocenteSeeder`

2. **"Email ya está registrado"**
   - Verifica que cada docente tenga email único

3. **"Usuario no se creó"**
   - Revisa los logs en `storage/logs/laravel.log`
   - Verifica que el campo `correo` esté lleno en docentes

---

## 🎓 PRÓXIMOS PASOS

1. Avisar a docentes sobre sus credenciales
2. Docentes ingresan y cambian contraseña
3. Docentes marcan asistencia mediante QR
4. Generar reportes de asistencia

---

**Última actualización**: 11 de Noviembre de 2025
