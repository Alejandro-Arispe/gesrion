# ✅ CHECKLIST DE PRUEBA RÁPIDA - Sistema GESTION v2.0

## 🚀 PRE-REQUISITOS
- [ ] Base de datos PostgreSQL corriendo
- [ ] Laravel 11 configurado
- [ ] Migraciones ejecutadas: `php artisan migrate`
- [ ] Seeder ejecutado: `php artisan db:seed --class=PermisoDocenteSeeder`
- [ ] Servidor running: `php artisan serve`
- [ ] Acceso a: `http://localhost:8000`

---

## 📋 PRUEBAS DE VALIDACIÓN

### 1️⃣ USUARIOS DOCENTES

#### Crear usuarios masivamente
```
[ ] Ir a: http://localhost:8000/administracion/usuarios-docentes
[ ] Verificar tabla con docentes
[ ] Click: "Generar Usuarios Faltantes"
[ ] Esperar confirmación
[ ] Verificar que aparecen usuarios en tabla
[ ] Status: ✅ CREAR / ⏭️ OMITIR (si ya existe)
```

#### Verificar credenciales
```
[ ] Click: "Descargar Credenciales en PDF"
[ ] Abrir PDF descargado
[ ] Verificar:
    [ ] Contiene lista de docentes
    [ ] Usuario = primer.apellido (ej: juan.perez)
    [ ] Contraseñas ocultas (• • • • • • •)
    [ ] Email del docente visible
    [ ] Estado (Activo/Inactivo)
```

#### Probar regenerar contraseña
```
[ ] Click: botón 🔑 de un docente
[ ] Confirmar regeneración
[ ] Verificar notificación: ✅ Nueva contraseña
[ ] Copiar nueva contraseña mostrada
```

---

### 2️⃣ AUTENTICACIÓN Y AUTORIZACIÓN

#### Login como docente
```
[ ] Ir a: http://localhost:8000/login
[ ] Usuario: (usar uno generado, ej: juan.perez)
[ ] Contraseña: Nombre123 (o la regenerada)
[ ] Click: "Ingresar"
[ ] Verificar: ✅ Dashboard carga
```

#### Verificar permisos docente
```
[ ] Como docente logueado:
    [ ] ✅ Ver: Control-Seguimiento → Marcar Asistencia
    [ ] ✅ Ver: Consultas → Mis Horarios
    [ ] ✅ Ver: Consultas → Mis Asistencias
    [ ] ❌ NO ver: Administración (debe mostrar 403)
    [ ] ❌ NO ver: Crear Grupo
```

#### Login como admin
```
[ ] Logout de docente
[ ] Login como admin (usuario de prueba)
[ ] Verificar: ✅ Ve Administración
[ ] Verificar: ✅ Ve Usuarios, Roles, etc
```

---

### 3️⃣ QR Y ESCANEO

#### Generar QRs
```
[ ] Ir a: http://localhost:8000/planificacion/generador-qr
[ ] Click: "Generar Todos"
[ ] Esperar completación
[ ] Verificar: "X QRs generados"
[ ] Click: "Descargar PDF Imprimible"
[ ] Verificar: Descarga archivo PDF
```

#### Escanear QR (test manual)
```
[ ] Ir a: Control-Seguimiento → Marcar Asistencia
[ ] Click: "Abrir Lector QR"
[ ] Permitir acceso a cámara
[ ] Mostrar código QR impreso (u otro código válido)
[ ] Verificar: ✅ Detecta código
[ ] Verificar: Muestra información del aula
```

#### Validación GPS
```
[ ] En formulario de asistencia:
    [ ] Click: "Obtener Ubicación"
    [ ] Permitir acceso a ubicación
    [ ] Verificar: ✅ Muestra coordenadas
    [ ] Si dentro de 50m: ✅ Verde (dentro de aula)
    [ ] Si fuera de 50m: ❌ Rojo (fuera de aula)
```

---

### 4️⃣ MARCADO DE ASISTENCIA

#### Marcar asistencia completa
```
[ ] Como docente:
    [ ] Ir a: Control-Seguimiento → Marcar Asistencia
    [ ] Seleccionar docente (auto-cargado si es docente)
    [ ] Escanear QR válido
    [ ] Obtener ubicación GPS
    [ ] Seleccionar horario que coincida
    [ ] Click: "Guardar Asistencia"
    [ ] Verificar: ✅ "Asistencia registrada"
```

#### Validaciones
```
[ ] Intentar sin QR: ❌ Error requerido
[ ] Intentar sin GPS: ❌ Error requerido
[ ] Intentar con GPS fuera de 50m: Estado "Fuera de aula"
[ ] Intentar sin seleccionar horario: ❌ Error requerido
```

---

### 5️⃣ REPORTES

#### Generar reporte de asistencia
```
[ ] Ir a: Reporte-Datos → Reportes
[ ] Seleccionar docente
[ ] Seleccionar fecha inicio/fin
[ ] Click: "Generar Reporte"
[ ] Verificar estructura:
    [ ] Docente: nombre
    [ ] Por cada asignación (grupo/día):
        [ ] Materia y aula
        [ ] Tabla de asistencias
        [ ] Estadísticas (%, presentes, atrasados, etc)
    [ ] Total general
```

#### Descargar formatos
```
[ ] Click: "Descargar PDF"
    [ ] Verificar: Descarga PDF formateado
    [ ] Verificar: Color-coding (verde/amarillo/rojo)
    [ ] Verificar: Imprimible sin problemas
    
[ ] Click: "Descargar Excel"
    [ ] Verificar: Descarga archivo .xlsx
    [ ] Abrir en Excel/Calc
    [ ] Verificar: Datos bien estructurados
```

---

### 6️⃣ DISTRIBUCIÓN DE HORARIOS

#### Crear distribución LMV
```
[ ] Ir a: Planificación → Distribución Horarios
[ ] Seleccionar grupo
[ ] Patrón: LMV
[ ] Hora inicio: 08:00
[ ] Click: "Generar"
[ ] Verificar: ✅ 3 horarios creados
    [ ] Lunes 08:00-09:30
    [ ] Miércoles 08:00-09:30
    [ ] Viernes 08:00-09:30
```

#### Crear distribución MJ
```
[ ] Seleccionar otro grupo
[ ] Patrón: MJ
[ ] Hora inicio: 14:00
[ ] Click: "Generar"
[ ] Verificar: ✅ 2 horarios creados
    [ ] Martes 14:00-16:15
    [ ] Jueves 14:00-16:15
```

#### Validar conflictos
```
[ ] Intentar crear horario en hora ocupada:
    [ ] ❌ Error: "Conflicto de horario"
    [ ] Verificar: Valida docente ocupado
    [ ] Verificar: Valida aula ocupada
```

---

### 7️⃣ ASIGNACIÓN INTELIGENTE DE AULAS

#### Asignar aulas automáticamente
```
[ ] Ir a: Planificación → Horarios
[ ] Horarios sin aula asignada: ✅ Visible
[ ] Click: "Asignar Aulas Automáticamente"
[ ] Esperar completación
[ ] Verificar: ✅ "X aulas asignadas"
[ ] Revisar tabla:
    [ ] Cada horario tiene aula_id
    [ ] Aulas no están en conflicto
    [ ] Laboratorios solo para materias que requieren
```

#### Validar laboratorios
```
[ ] Materia que requiere laboratorio:
    [ ] ✅ Asignada a aula tipo "Laboratorio"
    
[ ] Materia que NO requiere laboratorio:
    [ ] ✅ Asignada a aula tipo "Aula Normal"
```

---

### 8️⃣ SEGURIDAD

#### Validar encriptación de contraseñas
```
[ ] En BD (PostgreSQL):
    SELECT username, password FROM usuario LIMIT 1;
[ ] Verificar: ✅ Password NO es texto plano
[ ] Verificar: ✅ Password comienza con $2y$ (BCrypt)
```

#### Validar middleware de permisos
```
[ ] Como docente, intentar:
    [ ] GET /administracion/usuarios
    [ ] Resultado: ❌ 403 Forbidden
    
[ ] Como admin, intentar:
    [ ] GET /administracion/usuarios
    [ ] Resultado: ✅ 200 OK
```

---

### 9️⃣ BASE DE DATOS

#### Verificar migraciones
```
[ ] En terminal:
    php artisan migrate:status
[ ] Verificar: ✅ Todas las migraciones "Ran"
[ ] Verificar: ✅ Nuevos campos visibles
    [ ] usuario.id_docente
    [ ] materia.requiere_laboratorio
    [ ] horario.distribucion_dias
```

#### Verificar relaciones
```
[ ] En PostgreSQL:
    SELECT * FROM usuario 
    JOIN docente ON usuario.id_docente = docente.id_docente
    LIMIT 1;
[ ] Verificar: ✅ Datos se unen correctamente
```

---

### 🔟 RENDIMIENTO

#### Carga de páginas
```
[ ] Dashboard: < 2 segundos ✅
[ ] Listado de asistencias: < 3 segundos ✅
[ ] Generación de reportes: < 5 segundos ✅
[ ] Generar usuarios masivos (50+): < 10 segundos ✅
```

#### Queries eficientes
```
[ ] Enable Laravel Debugbar
[ ] Verificar: < 30 queries por página
[ ] Verificar: No hay N+1 problems
[ ] Verificar: Uso correcto de JOIN vs múltiples queries
```

---

## 🔍 ERRORES COMUNES Y SOLUCIONES

### ❌ Error: "No existe el rol Docente"
```
Solución:
php artisan db:seed --class=PermisoDocenteSeeder
```

### ❌ Error: "Middleware not found"
```
Solución:
- Verificar que ValidarPermisoRol está en app/Http/Middleware/
- Verificar que está registrado en app/Http/Kernel.php
- Limpiar cache: php artisan config:clear
```

### ❌ Error: "QR no escanea"
```
Solución:
- Verificar que jsQR library está incluida en view
- Permitir acceso a cámara web
- Verificar navegador soporta Geolocation API (usar HTTPS o localhost)
- Probar con otro QR code
```

### ❌ Error: "GPS no válida"
```
Solución:
- Verificar que navegador permite acceso a ubicación
- Usar HTTPS o localhost
- Verificar que coordenadas están guardadas en BD (ubicacion_gps)
- Revisar consola browser (F12) para ver errores de geolocation
```

### ❌ Error: "PDF no genera"
```
Solución:
- Verificar que DomPDF está instalado: composer show | grep dompdf
- Verificar que view existe y es válida
- Revisar logs: tail -f storage/logs/laravel.log
```

---

## 📊 REPORTE DE PRUEBAS

Después de ejecutar todas las pruebas, completar este reporte:

```
PRUEBAS COMPLETADAS: ___/90
FECHA: ___________
PROBADO POR: ___________

MÓDULOS FUNCIONALES:
✅ Usuarios Docentes: SÍ / NO
✅ QR: SÍ / NO
✅ Asistencia: SÍ / NO
✅ Reportes: SÍ / NO
✅ Horarios: SÍ / NO
✅ Aulas: SÍ / NO
✅ Permisos: SÍ / NO

PROBLEMAS ENCONTRADOS:
1. _______________________
2. _______________________
3. _______________________

RECOMENDACIONES:
1. _______________________
2. _______________________

ESTADO GENERAL: 🟢 LISTO / 🟡 CON AJUSTES / 🔴 NO LISTO
```

---

## 🚀 PRÓXIMOS PASOS

Una vez todas las pruebas ✅:

1. **Capacitación de usuarios**
   - [ ] Docentes entienden cómo marcar asistencia
   - [ ] Admin entiende cómo generar reportes

2. **Respaldo de BD**
   - [ ] Hacer backup antes de producción
   - [ ] Verificar restauración

3. **Monitoreo**
   - [ ] Configurar logs
   - [ ] Configurar alertas

4. **Producción**
   - [ ] Cambiar APP_DEBUG a false
   - [ ] Generar APP_KEY
   - [ ] Configurar HTTPS

---

**Checklist generado el 11 de Noviembre de 2025**
