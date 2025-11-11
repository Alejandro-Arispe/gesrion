#  GUÍA RÁPIDA - Implementación de Distribución Multi-día de Horarios

## ¿Qué se agregó?

### 1. **Distribución de materias en múltiples días**
- **LMV**: Lunes, Miércoles, Viernes (1:30h cada día)
- **MJ**: Martes, Jueves (2:15h cada día)
- **Personalizado**: Elige días y duración

### 2. **Laboratorios por materia**
- Marca si una materia requiere laboratorio
- Se asigna automáticamente aula de laboratorio (máx 1 vez/semana)

### 3. **Nueva interfaz**
- Generador automático en `/planificacion/distribucion`
- Valida conflictos automáticamente
- Muestra resultado en tiempo real

---

## 📝 PASO A PASO

### Paso 1: Ejecutar Migraciones
```bash
php artisan migrate
```
**¿Qué hace?**
- Agrega campo `requiere_laboratorio` a tabla `materia`
- Agrega campo `distribucion_dias` a tabla `horario`

### Paso 2: Actualizar Materia
1. Ir a: `/configuracion-academica/materias`
2. Crear o editar una materia
3. **Marcar checkbox**: ✓ "Esta materia requiere laboratorio" (si aplica)
4. Guardar

### Paso 3: Generar Distribución
1. Ir a: `/planificacion/distribucion`
2. Seleccionar grupo sin horario asignado
3. Elegir **patrón predeterminado**:
   - LMV (recomendado para 4.5h)
   - MJ (recomendado para 4.5h)
   - L, M, X, J, V (un día completo)
   
   **O personalizar**:
   - Marcar días específicos
   - Ingresar duración por día
   
4. Definir hora de inicio (ej: 08:00)
5. Clic en **"Generar Distribución"**

### Paso 4: Validar Resultado
- Sistema muestra horarios generados
- Verifica automáticamente conflictos
- Almacena patrón en BD para auditoría

---

## 📊 EJEMPLOS

### Ejemplo 1: Materia Teoría (LMV)
```
Materia: Cálculo I
Carga: 4.5 horas
Requiere Lab: ❌ NO

Generado:
- Lunes 08:00 - 09:30
- Miércoles 08:00 - 09:30
- Viernes 08:00 - 09:30
```

### Ejemplo 2: Materia Laboratorio (MJ)
```
Materia: Python Lab
Carga: 4.5 horas
Requiere Lab: ✅ SÍ

Generado:
- Martes 10:00 - 12:15 (Aula Laboratorio)
- Jueves 10:00 - 12:15 (Aula Laboratorio)
```

### Ejemplo 3: Personalizado
```
Materia: Inglés Avanzado
Carga: 5 horas
Requiere Lab: ❌ NO

Personalizado:
- Selecciono: Lunes, Miércoles, Viernes
- Duración: 1:40h por día (1h 40min)
- Total: 5 horas

Generado:
- Lunes 14:00 - 15:40
- Miércoles 14:00 - 15:40
- Viernes 14:00 - 15:40
```

---

## 🎯 CASOS DE USO

| Caso | Solución |
|------|----------|
| Materia 4.5h | Usar patrón LMV (3 días × 1:30h) |
| Materia 4.5h lab | Usar patrón MJ (2 días × 2:15h) + marcar laboratorio |
| Materia 3h | Personalizar: 3 días × 1h |
| Materia 6h | Personalizar: 3 días × 2h |
| Materia 2h | Personalizar: 2 días × 1h |

---

## ⚙️ PATRONES DISPONIBLES

```
LMV → Lunes, Miércoles, Viernes (1:30h cada día)
MJ  → Martes, Jueves (2:15h cada día)
L   → Lunes (4:30h)
M   → Martes (4:30h)
X   → Miércoles (4:30h)
J   → Jueves (4:30h)
V   → Viernes (4:30h)
```

---

## ✅ VALIDACIONES AUTOMÁTICAS

- ✓ No permite conflictos con otros grupos del docente
- ✓ Verifica disponibilidad de aulas
- ✓ Valida hora de inicio vs fin
- ✓ Avisa si carga no coincide exactamente
- ✓ Transacciones seguras (rollback si hay error)

---

## 🔗 RUTAS NUEVAS

```
GET    /planificacion/distribucion              → Formulario
POST   /planificacion/distribucion/generar      → Generar horarios
GET    /planificacion/distribucion/patrones     → Obtener patrones
```

---

## 📁 ARCHIVOS CREADOS/MODIFICADOS

### Creados:
- ✨ `app/Services/DistribucionHorariosService.php`
- ✨ `app/Http/Controllers/Planificacion/DistribucionHorariosController.php`
- ✨ `resources/views/planificacion/distribucion-horarios.blade.php`
- ✨ `database/migrations/2025_11_11_add_requiere_laboratorio_to_materia.php`
- ✨ `database/migrations/2025_11_11_add_distribucion_dias_to_horario.php`

### Modificados:
- 🔧 `app/Models/ConfiguracionAcademica/Materia.php` (+ campo requiere_laboratorio)
- 🔧 `app/Models/Planificacion/Horario.php` (+ campo distribucion_dias)
- 🔧 `app/Services/ClassroomAssignmentEngine.php` (validación mejorada)
- 🔧 `resources/views/configuracion-academica/materias/index.blade.php` (+ checkbox)
- 🔧 `routes/web.php` (+ rutas nuevas)

---

## ⚡ PRÓXIMOS PASOS

1. **Ejecutar**: `php artisan migrate`
2. **Probar**: Ir a `/configuracion-academica/materias` y crear materia con laboratorio
3. **Generar**: Ir a `/planificacion/distribucion` y crear distribución
4. **Validar**: Verificar que se crearon los horarios correctamente
5. **Integrar**: Con asignación de aulas (laboratorio vs normal)

---

## 💡 NOTAS IMPORTANTES

- El patrón se guarda en `distribucion_dias` (JSON) para auditoría
- Laboratorio = máximo 1 vez/semana
- Conflictos se detectan automáticamente
- Se pueden regenerar horarios sin problema
- Compatible con asignación automática de aulas

---

## 🐛 TROUBLESHOOTING

| Problema | Solución |
|----------|----------|
| Migración falla | Verificar que las tablas existan: `php artisan tinker` |
| Grupos no aparecen | Asegurar que grupo NO tenga horarios: `whereDoesntHave('horarios')` |
| Error en generación | Verificar que docente no tenga conflicto en ese día/hora |
| Laboratorio no se asigna | Verificar que campo `requiere_laboratorio` = true en BD |

---

**¿Preguntas?** Consulta `CAMBIOS_DISTRIBUCION_MULTIDIA.md` para documentación completa.
