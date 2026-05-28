# Módulo de Parámetros Globales

Este plan detalla los pasos para aprovechar la tabla existente `parametros_sistema` y crear un panel de control intuitivo que te permita administrar las variables generales de LeonPlast, tales como el IGV, el costo de hora/hombre, y próximamente el tipo de cambio.

> [!NOTE]
> Hemos detectado que ya existe una tabla en la base de datos llamada `parametros_sistema` que tiene guardados algunos valores base como `IGV_PORCENTAJE`, `MONEDA_PRINCIPAL`, `COSTO_HORA_HOMBRE` y `MARGEN_UTILIDAD`. Aprovecharemos esta misma estructura para no tener que hacer migraciones complejas.

## Open Questions
> [!IMPORTANT]
> - ¿Deseas que incluya un botón que consulte automáticamente el **Tipo de Cambio del día** (Soles a Dólares) conectándose a una API pública (como Apis.net.pe o Sunat) para que se guarde como un parámetro más sin que tengas que digitarlo a mano?
> - Algunos parámetros actuales tienen la bandera `editable = 0` (como el IGV). ¿Mantenemos esa protección o habilitamos que todos los parámetros puedan ser modificados por el administrador desde este nuevo módulo?

## Proposed Changes

### Modelos y Controladores

#### [NEW] [ParametroSistema.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/app/Models/ParametroSistema.php)
- Se creará el modelo Eloquent vinculado a la tabla `parametros_sistema`. 
- Configurado para respetar la llave primaria `id_parametro` y deshabilitando los timestamps estándar de Laravel, ya que tu tabla utiliza `fecha_actualizacion` de manera nativa.

#### [NEW] [ParametroSistemaController.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/app/Http/Controllers/ParametroSistemaController.php)
- **Método `index`**: Se encargará de extraer todos los parámetros y enviarlos a la vista agrupados por su `categoria` (TRIBUTARIO, FINANZAS, PRODUCCION).
- **Método `updateBulk`**: Procesará el formulario para actualizar el campo `valor` de todos los parámetros que el administrador haya modificado.

---

### Interfaz de Usuario (Vistas)

#### [NEW] [index.blade.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/parametros/index.blade.php)
- Diseño estético basado en el panel actual.
- Los parámetros se dividirán en "Tarjetas" (Cards) separadas por categoría.
- Habrá `inputs` dinámicos que se adapten al `tipo` del parámetro (número, texto, etc).
- Un botón central para "Guardar Configuraciones".

#### [MODIFY] Rutas y Menú
- **Rutas**: Se agregarán las rutas `GET /parametros` y `POST /parametros/actualizar` en el archivo `routes/web.php`.
- **Menú Lateral (`app.blade.php` o similar)**: Agregaremos la opción **"Parámetros del Sistema"** dentro del menú desplegable "Tablas Maestras".

## Verification Plan
1. Ingresaremos al sistema como Administrador.
2. Navegaremos al menú "Tablas Maestras" > "Parámetros del Sistema".
3. Visualizaremos el IGV actual (18%) y lo cambiaremos a modo de prueba.
4. Le daremos a Guardar y comprobaremos que el registro en la base de datos se haya actualizado y el nuevo valor persista.
