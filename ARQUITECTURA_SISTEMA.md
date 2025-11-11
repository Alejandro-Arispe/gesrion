# 🏗️ ARQUITECTURA DEL SISTEMA - Diagrama Completo

## 📊 FLUJO DE AUTENTICACIÓN Y AUTORIZACIÓN

```
┌─────────────────────────────────────────────────────────────┐
│                    USUARIO INTENTA ACCESO                    │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│         ¿Es Docente, Admin, etc?                            │
│         (Verificar tabla: usuario.id_rol)                   │
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        ▼                         ▼
    ┌────────┐            ┌──────────────┐
    │ Docente│            │   Admin      │
    └────────┘            └──────────────┘
        │                         │
        ▼                         ▼
   ROL: Docente             ROL: Admin/etc
        │                         │
        ▼                         ▼
   PERMISOS:                PERMISOS:
   • marcar_asistencia_qr   • gestionar_usuarios
   • ver_mis_horarios       • crear_grupos
   • ver_mis_asistencias    • asignar_aulas
   • actualizar_perfil      • generar_reportes
        │                         │
        └────────────┬────────────┘
                     │
                     ▼
         ┌─ Middleware: ValidarPermisoRol
         │           (verifica tabla rol_permiso)
         │
         ├─ Si ✅ permiso existe → CONTINUAR
         └─ Si ❌ permiso no existe → ERROR 403
```

---

## 📁 ESTRUCTURA DE TABLAS RELACIONADAS

```
usuario
├── id_usuario (PK)
├── username (UNIQUE)
├── password (HASH)
├── correo
├── activo (BOOLEAN)
├── id_rol (FK) ─────────┐
├── id_docente (FK) ──┐  │
│                     │  │
│  CREADO POR:        │  │
│  GeneradorUsuariosDocentesService  │
│  └─ GenerarUsuariosDocentes()      │
│  └─ Hash::make(password)           │
│  └─ username: primer.apellido      │
│  └─ password: Nombre123            │
│                     │  │
└─────────────────────┼──┘

docente
├── id_docente (PK)
├── ci (UNIQUE)
├── nombre
├── sexo
├── telefono
├── correo
├── estado (BOOLEAN)
├── id_facultad (FK)
└─ usuario (relación 1:1)

rol
├── id_rol (PK)
├── nombre (UNIQUE)
├── descripcion
└─ usuario (relación 1:N)
└─ rol_permiso (relación 1:N)

permiso
├── id_permiso (PK)
├── nombre (UNIQUE)
│   ├─ marcar_asistencia_qr
│   ├─ ver_mis_horarios
│   ├─ ver_mis_asistencias
│   └─ actualizar_perfil
├── descripcion
└─ rol_permiso (relación 1:N)

rol_permiso
├── id_rol (FK)
├── id_permiso (FK)
└─ PRIMARY KEY (id_rol, id_permiso)
```

---

## 🔄 FLUJO: Generación de Usuarios Docentes

```
┌─ Administrador accede a:
│  /administracion/usuarios-docentes
│
├─ Ver tabla con:
│  • Nombre docente
│  • ¿Usuario creado?
│  • Correo
│  • Acciones
│
├─ Click: "Generar Usuarios Faltantes"
│
├─ GeneradorUsuariosDocentesController@generarMasivo()
│  ├─ GeneradorUsuariosDocentesService::generarUsuariosDocentes()
│  │  ├─ Obtener docentes activos (sin usuario)
│  │  ├─ Para cada docente:
│  │  │  ├─ Generar username: primer.apellido
│  │  │  ├─ Generar password: Nombre123
│  │  │  ├─ Hash::make(password)
│  │  │  ├─ Crear usuario:
│  │  │  │  ├─ username
│  │  │  │  ├─ password (hasheada)
│  │  │  │  ├─ correo
│  │  │  │  ├─ id_rol = Docente
│  │  │  │  └─ id_docente = docente.id_docente
│  │  │  └─ Guardar en BD
│  │  └─ Retornar resumen (creados, omitidos, errores)
│  │
│  └─ response()->json($resultado, 201)
│
├─ JavaScript recibe respuesta
│  ├─ Mostrar notificación "✅ X usuarios creados"
│  └─ Recargar página (window.location.reload())
│
└─ Ver usuario creado en tabla con estado "Activo"
```

---

## 📋 FLUJO: Descargar Credenciales en PDF

```
┌─ Administrador click: "Descargar Credenciales en PDF"
│
├─ GeneradorUsuariosDocentesController@descargarCredencialesPDF()
│  ├─ GeneradorUsuariosDocentesService::obtenerCredencialesDocentes()
│  │  └─ SELECT docente, usuario, correo FROM usuario
│  │     WHERE id_rol = (SELECT id_rol FROM rol WHERE nombre='Docente')
│  │
│  ├─ Pasar datos a vista: credenciales-docentes-pdf.blade.php
│  │  ├─ Renderizar HTML con tabla
│  │  ├─ Contraseñas: mostrar • • • • • • • (ocultas)
│  │  ├─ Información de seguridad
│  │  └─ Estilos para impresión
│  │
│  └─ PDF::loadView() → Generar PDF con DomPDF
│     └─ download('credenciales_docentes_Y-m-d.pdf')
│
└─ Descargar archivo: credenciales_docentes_2025-11-11.pdf
```

---

## 🔐 FLUJO: Docente Ingresa a la Plataforma

```
┌─ Docente accede a login
│  ├─ Usuario: juan.perez
│  ├─ Contraseña: Juan123 (desde PDF)
│
├─ Laravel Sanctum verifica credenciales
│  ├─ SELECT * FROM usuario WHERE username = 'juan.perez'
│  ├─ Hash::check('Juan123', usuario.password)
│  │  └─ ✅ Contraseña válida
│  └─ Crear sesión / token
│
├─ Usuario autenticado
│  ├─ Middleware: 'auth' → ✅ Aprobado
│  ├─ ir a Dashboard
│
├─ En Dashboard:
│  ├─ Acceder a: Control-Seguimiento → Marcar Asistencia
│  │  └─ Middleware: 'permiso:marcar_asistencia_qr' → ✅ OK
│  │
│  ├─ Acceder a: Consultas → Mis Horarios
│  │  └─ Middleware: 'permiso:ver_mis_horarios' → ✅ OK
│  │
│  └─ Intentar acceder a: Administración → Usuarios
│     └─ Middleware: ??? → ❌ PROHIBIDO (no tiene permiso)
│
└─ Fin de sesión (logout)
```

---

## 🎯 FLUJO: Marcar Asistencia con QR + GPS

```
┌─ Docente accede a: /control-seguimiento/asistencia/create
│
├─ Validación:
│  ├─ Middleware 'auth' → ✅ Autenticado
│  ├─ Middleware 'permiso:marcar_asistencia_qr' → ✅ Permiso OK
│
├─ Interfaz del formulario:
│  ├─ 1. ESCANEAR QR
│  │  ├─ Activar cámara (onclick: abrirLectorQR())
│  │  ├─ jsQR procesa frame en tiempo real
│  │  ├─ Detecta código QR
│  │  ├─ Enviar a: POST /planificacion/qr/validar
│  │  │  ├─ Obtener QrAula.token del código
│  │  │  ├─ Validar token existe en BD
│  │  │  ├─ Retornar: { aula, id_aula }
│  │  │  └─ Asignar a: request.qr_aula_validada
│  │  └─ Mostrar: ✅ "QR válido - Aula 101"
│  │
│  ├─ 2. VALIDAR GPS
│  │  ├─ Click: "Obtener Ubicación"
│  │  ├─ Navigator.geolocation.getCurrentPosition()
│  │  ├─ Obtener latitud y longitud
│  │  ├─ Calcular distancia:
│  │  │  └─ Haversine formula con ubicacion_gps del aula
│  │  ├─ Si distancia ≤ 50m → ✅ Dentro de aula
│  │  └─ Si distancia > 50m → ❌ Fuera de aula
│  │
│  ├─ 3. ENVIAR ASISTENCIA
│  │  ├─ Validaciones en controller:
│  │  │  ├─ QR leído: required
│  │  │  ├─ GPS válido: required
│  │  │  ├─ Horario existe: required
│  │  │  ├─ QR aula = Horario aula
│  │  │  └─ Rango horario correcto
│  │  │
│  │  ├─ Crear registro en asistencia:
│  │  │  ├─ id_docente = Auth::user()->id_docente
│  │  │  ├─ id_horario = request.id_horario
│  │  │  ├─ fecha = today()
│  │  │  ├─ hora_marcado = now()
│  │  │  ├─ latitud = request.latitud
│  │  │  ├─ longitud = request.longitud
│  │  │  └─ estado = calcular_estado(hora_marcado, horario)
│  │  │     ├─ Si hora < hora_inicio → 'Presente'
│  │  │     ├─ Si hora < hora_inicio + 10 min → 'Atrasado'
│  │  │     ├─ Si hora > hora_inicio + 10 min → 'Ausente'
│  │  │     └─ Si GPS > 50m → 'Fuera de aula'
│  │  │
│  │  └─ Guardar en BD
│  │
│  └─ ✅ Respuesta: "Asistencia registrada exitosamente"
│
└─ Fin: Volver a página de asistencias
```

---

## 📊 FLUJO: Generar Reporte de Asistencia

```
┌─ Administrador va a: /reporte-datos/reportes
│
├─ Selecciona:
│  ├─ Docente: Juan Pérez
│  ├─ Fecha inicio: 01/11/2025
│  └─ Fecha fin: 11/11/2025
│
├─ Click: "Generar Reporte"
│
├─ ReporteController@asistenciaPorAsignacion()
│  ├─ Query complexa:
│  │  ├─ SELECT docente.nombre
│  │  ├─ SELECT grupo, materia, aula, día, hora
│  │  ├─ GROUP BY asignacion (grupo+día+hora)
│  │  ├─ SELECT asistencias per asignacion
│  │  └─ CALCULATE estadísticas:
│  │     ├─ Total asistencias
│  │     ├─ Presentes: COUNT(estado='Presente')
│  │     ├─ Atrasados: COUNT(estado='Atrasado')
│  │     ├─ Ausentes: COUNT(estado='Ausente')
│  │     ├─ Fuera aula: COUNT(estado='Fuera')
│  │     ├─ %: (Presentes+Atrasados) / Total * 100
│  │     └─ Color-code: >= 80% (verde), 60-80% (amarillo), <60% (rojo)
│  │
│  ├─ Pasar datos a vista: asistencia-asignacion-pdf.blade.php
│  │  └─ Renderizar HTML con estructura:
│  │     ├─ DOCENTE: Juan Pérez
│  │     ├─ ┌─────────────────────────────┐
│  │     │  ASIGNACIÓN 1
│  │     │  Materia: Cálculo
│  │     │  Grupo: A
│  │     │  Aula: 101
│  │     │  Días: Lunes 08:00-09:30
│  │     │  ├─ 01/11 | 08:00 | ✅ Presente
│  │     │  ├─ 03/11 | 08:05 | ⏱️ Atrasado
│  │     │  └─ 05/11 | ❌ | ❌ Ausente
│  │     │  STATS: 2 Presentes, 1 Atrasado = 66% 🟡
│  │     └─────────────────────────────
│  │     ├─ ┌─────────────────────────────┐
│  │     │  ASIGNACIÓN 2
│  │     │  ... (más asignaciones)
│  │     └─────────────────────────────
│  │     ├─ TOTAL GENERAL:
│  │     │  └─ 15 Presentes, 3 Atrasados, 2 Ausentes
│  │     │     = 85% 🟢
│  │     └─────────────────────────────
│  │
│  └─ PDF::loadView() → Generar PDF
│
├─ Opciones de descarga:
│  ├─ 📄 Descargar PDF
│  ├─ 📊 Descargar Excel
│  └─ 🖨️ Imprimir
│
└─ Reporte generado exitosamente
```

---

## 🎯 FLUJO: Distribución de Horarios Multi-día

```
┌─ Administrador accede a:
│  /planificacion/distribucion-horarios
│
├─ Seleccionar grupo sin horarios asignados
│
├─ Información mostrada:
│  ├─ Materia: Cálculo I
│  ├─ Docente: Juan Pérez
│  ├─ Carga horaria: 4.5 horas/semana
│
├─ Opciones de patrón:
│  ├─ 🔘 LMV (Lunes, Miércoles, Viernes)
│  │   └─ 1:30 horas c/día = 4:30 total ✅
│  │
│  ├─ 🔘 MJ (Martes, Jueves)
│  │   └─ 2:15 horas c/día = 4:30 total ✅
│  │
│  ├─ 🔘 Personalizado
│  │   └─ Seleccionar días y duración
│  │
│  └─ Hora inicio: 08:00
│
├─ Click: "Generar Distribución"
│
├─ DistribucionHorariosService::generarDistribucion()
│  ├─ Para cada día en patrón:
│  │  ├─ Validar conflictos:
│  │  │  ├─ ¿Docente tiene otra clase ese día/hora?
│  │  │  ├─ ¿Aula ya está ocupada?
│  │  │  └─ ¿Hay grupo que se superpone?
│  │  │
│  │  ├─ Si no hay conflictos:
│  │  │  └─ Crear registro en horario:
│  │  │     ├─ id_grupo = grupo.id_grupo
│  │  │     ├─ dia_semana = "Lunes" (o correspondiente)
│  │  │     ├─ hora_inicio = "08:00"
│  │  │     ├─ hora_fin = "09:30" (calculada)
│  │  │     ├─ id_aula = null (se asigna después)
│  │  │     ├─ tipo_asignacion = "Automática"
│  │  │     └─ distribucion_dias = { "patron": "LMV", "dias": [...], "duracion_minutos": 90 }
│  │  │
│  │  └─ Si hay conflictos → RETORNAR ERROR
│  │
│  └─ RETORNAR resumen:
│     ├─ 3 horarios creados (Lunes, Miércoles, Viernes)
│     ├─ Carga total: 4.5 horas
│     └─ ✅ Exitoso
│
├─ JavaScript muestra:
│  ├─ ✅ "Distribución creada exitosamente"
│  ├─ Horarios:
│  │  ├─ Lunes 08:00 - 09:30
│  │  ├─ Miércoles 08:00 - 09:30
│  │  └─ Viernes 08:00 - 09:30
│
└─ Horarios listos para asignación de aulas
```

---

## 🚀 FLUJO: Asignación Inteligente de Aulas

```
┌─ Administrador click: "Asignar Aulas Automáticamente"
│  (en gestión de horarios)
│
├─ ClassroomAssignmentEngine::asignarAulasInteligente()
│  ├─ Obtener todos los grupos sin aula
│  ├─ Agrupar por docente
│
│  ├─ Para cada grupo:
│  │  ├─ ¿Materia requiere laboratorio?
│  │  │  ├─ SI → Priorizar aulas tipo "Laboratorio"
│  │  │  └─ NO → Priorizar aulas tipo "Aula Normal"
│  │  │
│  │  ├─ Obtener aulas prioritarias:
│  │  │  ├─ Primer piso primero
│  │  │  ├─ Capacidad >= cantidad estudiantes
│  │  │  └─ Disponible = true
│  │  │
│  │  ├─ Para cada día/horario del grupo:
│  │  │  ├─ Validar conflictos:
│  │  │  │  ├─ ¿Aula disponible en ese horario?
│  │  │  │  ├─ ¿Docente disponible?
│  │  │  │  └─ ¿Grupo disponible?
│  │  │  │
│  │  │  ├─ Si OK:
│  │  │  │  └─ Asignar aula a horario
│  │  │  │     ├─ UPDATE horario SET id_aula = 101
│  │  │  │     └─ Registrar asignación exitosa
│  │  │  │
│  │  │  └─ Si hay conflictos:
│  │  │     └─ Intentar siguiente aula
│  │  │
│  │  └─ Si no hay aula disponible:
│  │     └─ Registrar error: "Aula no disponible"
│  │
│  └─ RETORNAR resumen:
│     ├─ X exitosas
│     ├─ X conflictos
│     ├─ X no asignadas
│     └─ Detalles de cada asignación
│
├─ Mostrar resultado en tabla:
│  ├─ Docente | Materia | Grupo | Aula | Día | Horario | Estado
│  ├─ Juan    | Cálculo | A     | 101  | Lun | 08-09   | ✅
│  ├─ Juan    | Cálculo | A     | 101  | Mié | 08-09   | ✅
│  ├─ Juan    | Cálculo | A     | 101  | Vie | 08-09   | ✅
│  └─ ...
│
└─ Todas las aulas asignadas inteligentemente
```

---

## 🔌 INTEGRACIÓN CON OTRAS TABLAS

```
USUARIO
  └─ Rol (1:N)
      └─ Permisos (1:N via rol_permiso)
  └─ Docente (1:1)
      └─ Grupos (1:N)
          └─ Horarios (1:N)
              ├─ Aula (N:1)
              │   └─ QR Aulas (1:1)
              └─ Asistencias (1:N)
                  └─ Reportes (N:1)
```

---

## ✅ CHECKLIST DE FLUJOS IMPLEMENTADOS

```
✅ Autenticación y autorización
✅ Generación masiva de usuarios docentes
✅ Descarga de credenciales PDF
✅ Marcado de asistencia con QR
✅ Validación GPS
✅ Generación de reportes
✅ Distribución de horarios multi-día
✅ Asignación inteligente de aulas
✅ Gestión de permisos granulares
✅ Protección con middleware
```

---

**Diagrama generado automáticamente el 11 de Noviembre de 2025**
