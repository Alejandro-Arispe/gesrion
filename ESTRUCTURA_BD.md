# 📊 ESTRUCTURA DE BASE DE DATOS - Sistema Multi-día

## Cambios Realizados

### Tabla: `materia`

**Campos Nuevos:**
```
requiere_laboratorio BOOLEAN DEFAULT FALSE
```

**Ejemplo de Datos:**
```sql
INSERT INTO materia VALUES (
    1,              -- id_materia
    'MAT-101',      -- codigo
    'Cálculo I',    -- nombre
    4.5,            -- carga_horaria
    false,          -- requiere_laboratorio ← NUEVO
    1               -- id_facultad
);

INSERT INTO materia VALUES (
    2,
    'LAB-PYTHON',
    'Laboratorio de Python',
    4.5,
    true,           -- requiere_laboratorio ← NUEVO (laboratorio)
    1
);
```

---

### Tabla: `horario`

**Campos Nuevos:**
```
distribucion_dias JSON
```

**Estructura JSON:**
```json
{
  "patron": "LMV",
  "dias": ["Lunes", "Miércoles", "Viernes"],
  "duracion_minutos": 90
}
```

**Ejemplo de Datos:**
```sql
INSERT INTO horario VALUES (
    1,                              -- id_horario
    5,                              -- id_grupo
    10,                             -- id_aula
    'Lunes',                        -- dia_semana
    '08:00:00',                     -- hora_inicio
    '09:30:00',                     -- hora_fin
    'Automática',                   -- tipo_asignacion
    '{"patron":"LMV","dias":["Lunes","Miércoles","Viernes"],"duracion_minutos":90}'
    -- distribucion_dias ← NUEVO (JSON)
);

INSERT INTO horario VALUES (
    2,
    5,
    10,
    'Miércoles',
    '08:00:00',
    '09:30:00',
    'Automática',
    '{"patron":"LMV","dias":["Lunes","Miércoles","Viernes"],"duracion_minutos":90}'
);

INSERT INTO horario VALUES (
    3,
    5,
    10,
    'Viernes',
    '08:00:00',
    '09:30:00',
    'Automática',
    '{"patron":"LMV","dias":["Lunes","Miércoles","Viernes"],"duracion_minutos":90}'
);
```

---

## 🔀 Relaciones de Datos

```
                    ┌─────────────────┐
                    │     MATERIA     │
                    ├─────────────────┤
                    │ id_materia      │◄──────────────────┐
                    │ nombre          │                   │
                    │ carga_horaria   │                   │
    ┌──────────────►│ requiere_lab ✨ │                   │
    │               │ id_facultad     │                   │
    │               └─────────────────┘                   │
    │                                                     │
    │                                                  1:N
    │                    ┌─────────────────┐             │
    │                    │      GRUPO      │             │
    │                    ├─────────────────┤             │
    │                    │ id_grupo        │             │
    │                    │ nombre          │             │
    │                    │ id_materia      │─────────────┘
    │                    │ id_docente      │
    │                    │ id_gestion      │
    │                    └─────────────────┘
    │                          ▲
    │                          │
    │                       1:N │
    │                          │
    │                    ┌─────────────────┐
    └────────────────────│     HORARIO     │
                         ├─────────────────┤
                         │ id_horario      │
                         │ id_grupo        │
                         │ id_aula         │
                         │ dia_semana      │
                         │ hora_inicio     │
                         │ hora_fin        │
                         │ tipo_asignacion │
                         │ distribucion✨  │ ← JSON con patrón
                         └─────────────────┘
                               ▲
                               │
                            1:N │
                               │
                         ┌─────────────────┐
                         │      AULA       │
                         ├─────────────────┤
                         │ id_aula         │
                         │ nro             │
                         │ piso            │
                         │ tipo_aula       │
                         │ capacidad       │
                         │ disponible      │
                         │ ubicacion_gps   │
                         └─────────────────┘
```

---

## 📈 Flujo de Datos - Generación de Distribución

```
┌──────────────────────────────────────────────────────────────────┐
│ USUARIO: Selecciona Grupo sin Horario en /planificacion/dist     │
└──────────────────────────────────┬───────────────────────────────┘
                                   │
                                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ USUARIO: Elige Patrón (LMV) + Hora (08:00)                      │
└──────────────────────────────────┬───────────────────────────────┘
                                   │
                                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ FRONTEND: Envía JSON al backend                                  │
│ {                                                                 │
│   "id_grupo": 5,                                                 │
│   "patron": "LMV",                                               │
│   "hora_inicio": "08:00"                                         │
│ }                                                                 │
└──────────────────────────────────┬───────────────────────────────┘
                                   │
                                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ CONTROLADOR: DistribucionHorariosController@generar             │
│ - Valida datos                                                   │
│ - Obtiene grupo                                                  │
│ - Valida carga horaria vs duracion                               │
└──────────────────────────────────┬───────────────────────────────┘
                                   │
                                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ SERVICIO: DistribucionHorariosService@generarDistribucion      │
│ - Obtiene patrón (LMV → 3 días × 1:30h)                        │
│ - Valida conflictos con otros grupos del docente                │
│ - Crea 3 registros en tabla horario:                            │
│   • Lunes 08:00-09:30                                           │
│   • Miércoles 08:00-09:30                                       │
│   • Viernes 08:00-09:30                                         │
│ - Almacena JSON en campo distribucion_dias                      │
└──────────────────────────────────┬───────────────────────────────┘
                                   │
                                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ BD: INSERT en tabla HORARIO (×3)                                 │
│                                                                   │
│ Horario 1: Lunes, distribucion_dias = {"patron":"LMV",...}     │
│ Horario 2: Miércoles, distribucion_dias = {"patron":"LMV",...} │
│ Horario 3: Viernes, distribucion_dias = {"patron":"LMV",...}   │
└──────────────────────────────────┬───────────────────────────────┘
                                   │
                                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ RESPUESTA JSON al Frontend:                                      │
│ {                                                                 │
│   "exito": true,                                                 │
│   "mensaje": "3 horarios creados",                               │
│   "horarios": [                                                  │
│     {"id_horario": 1, "dia": "Lunes", "hora": "08:00-09:30"},  │
│     {"id_horario": 2, "dia": "Miércoles", "hora": "08:00-09:30"},
│     {"id_horario": 3, "dia": "Viernes", "hora": "08:00-09:30"}  │
│   ],                                                              │
│   "distribucion": {"patron":"LMV", ...}                          │
│ }                                                                 │
└──────────────────────────────────┬───────────────────────────────┘
                                   │
                                   ▼
┌──────────────────────────────────────────────────────────────────┐
│ USUARIO: Ve resultado en pantalla ✅                             │
│ "3 horarios generados exitosamente"                             │
└──────────────────────────────────────────────────────────────────┘
```

---

## 🔍 Consultas Útiles en BD

### Contar materias que requieren laboratorio
```sql
SELECT COUNT(*) as total_materias_lab
FROM materia
WHERE requiere_laboratorio = true;
```

### Ver todos los horarios generados automáticamente
```sql
SELECT 
    h.id_horario,
    g.nombre as grupo,
    m.nombre as materia,
    h.dia_semana,
    h.hora_inicio,
    h.hora_fin,
    JSON_EXTRACT(h.distribucion_dias, '$.patron') as patron
FROM horario h
JOIN grupo g ON h.id_grupo = g.id_grupo
JOIN materia m ON g.id_materia = m.id_materia
WHERE h.tipo_asignacion = 'Automática'
AND h.distribucion_dias IS NOT NULL
ORDER BY h.dia_semana, h.hora_inicio;
```

### Ver estadísticas por patrón
```sql
SELECT 
    JSON_EXTRACT(distribucion_dias, '$.patron') as patron,
    COUNT(*) as cantidad_horarios
FROM horario
WHERE distribucion_dias IS NOT NULL
GROUP BY JSON_EXTRACT(distribucion_dias, '$.patron');
```

### Verificar grupos sin asignación de laboratorio que lo requieren
```sql
SELECT 
    g.id_grupo,
    g.nombre,
    m.nombre as materia,
    m.requiere_laboratorio,
    COUNT(h.id_horario) as horarios_asignados
FROM grupo g
JOIN materia m ON g.id_materia = m.id_materia
LEFT JOIN horario h ON g.id_grupo = h.id_grupo
WHERE m.requiere_laboratorio = true
GROUP BY g.id_grupo
HAVING horarios_asignados = 0;
```

---

## 📊 Estados de Datos

### Estado Inicial (Sin Distribución)
```
Materia: Cálculo I
├─ requiere_laboratorio: false
└─ Grupo: Secc A
   ├─ id_docente: 2
   ├─ horarios: []  ← SIN HORARIOS
   └─ estado: "sin_asignar"
```

### Estado Después de Distribución LMV
```
Materia: Cálculo I
├─ requiere_laboratorio: false
└─ Grupo: Secc A
   ├─ id_docente: 2
   ├─ horarios: [
   │   ├─ Horario 1: Lunes 08:00-09:30 (distribucion_dias: LMV)
   │   ├─ Horario 2: Miércoles 08:00-09:30 (distribucion_dias: LMV)
   │   └─ Horario 3: Viernes 08:00-09:30 (distribucion_dias: LMV)
   │]
   └─ estado: "asignado_automatico"
```

### Estado Laboratorio
```
Materia: Python Lab
├─ requiere_laboratorio: true  ← LABORATORIO
└─ Grupo: Secc B
   ├─ id_docente: 5
   ├─ horarios: [
   │   ├─ Horario 1: Martes 10:00-12:15 (Aula Laboratorio, distribucion_dias: MJ)
   │   └─ Horario 2: Jueves 10:00-12:15 (Aula Laboratorio, distribucion_dias: MJ)
   │]
   └─ estado: "asignado_laboratorio"
```

---

## ✅ VALIDACIONES EN BD

| Tabla | Campo | Validación | Error si |
|-------|-------|-----------|---------|
| materia | requiere_laboratorio | BOOLEAN | NULL |
| horario | distribucion_dias | JSON | Formato inválido |
| horario | hora_inicio | TIME | > hora_fin |
| grupo | id_materia | FK | Materia no existe |
| grupo | id_docente | FK | Docente no existe |

---

**Versión**: 2.0  
**Migración ejecutada**: 11 Noviembre 2025  
**Nuevos campos**: 2 (requiere_laboratorio, distribucion_dias)
