# Gestión de Moneda y Tipo de Cambio en Compras

Este plan detalla los pasos para agregar soporte de diferentes monedas (Soles y Dólares) al registrar compras y asegurar que su costo se exprese en Soles locales al pasar al Kardex valorizado.

## Respuesta a tu consulta
> *¿O crees se tenga que crear un modulo para la gestion del tipo de cambio, igv, moneda, entre otras opciones que en la actualidad estan hardcodeados en el codigo?*

**Definitivamente sí.** A mediano plazo es la mejor práctica. Lo ideal es construir un módulo de **"Configuraciones del Sistema"** o **"Parámetros"**. Ese módulo permitiría configurar el IGV global (para no tener el 18% quemado en el código) y tener un submódulo que obtenga el tipo de cambio del día automáticamente (conectándose a la API de la SUNAT o de la SBS), ahorrando al usuario tener que tipearlo manualmente cada vez. 

**Sin embargo**, para no bloquear tu flujo de trabajo actual, implementaremos la solución más inmediata: habilitar los campos en la vista de compras de forma que se registren junto con cada documento, como se detalla a continuación. Siempre puedes solicitar la creación del módulo de "Parámetros" más adelante.

## Proposed Changes

### Vistas de Compras
Añadir campos de moneda y tipo de cambio en los formularios de registro y edición.

#### [MODIFY] [create.blade.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/compras/create.blade.php)
- Agregar un `<select>` para **Moneda** (Soles / Dólares).
- Agregar un `<input>` para **Tipo de Cambio**, que se mantendrá oculto mediante JavaScript y solo se mostrará si la moneda elegida es "Dólares".

#### [MODIFY] [edit.blade.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/resources/views/compras/edit.blade.php)
- Replicar el mismo comportamiento del `create.blade.php` para poder editar el documento, precargando los valores guardados en base de datos.

---

### Controladores
Actualizar la recepción de datos y el cálculo del ingreso al kardex.

#### [MODIFY] [CompraController.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/app/Http/Controllers/CompraController.php)
- En `store` y `update`: Validar los nuevos campos `moneda` y `tipo_cambio` (`nullable|numeric`).
- Asignar dichos valores al modelo `Compra` al momento de hacer el insert/update. (La base de datos actual ya cuenta con las columnas `moneda` y `tipo_cambio` en la tabla `compras`, así que no requerimos migraciones).

#### [MODIFY] [InventarioController.php](file:///c:/laragon/www/LeonPlast/LeonPlast-Laravel/app/Http/Controllers/InventarioController.php)
- En `procesarRecepcion`: Al momento de tomar la compra pendiente y enviarla al Kardex, verificar la moneda original.
- Si es `USD` (Dólares), multiplicar el precio de compra del insumo por el `tipo_cambio` de esa compra. 
- Guardar el `costo_entrada`, `total_entrada` y `costo_promedio` en Soles (PEN) dentro del `kardex` e `inventario`.

## Verification Plan

### Manual Verification
1. Acceder al formulario de "Registrar Nueva Compra".
2. Elegir "Dólares" en el campo Moneda y confirmar que aparece la casilla de Tipo de Cambio.
3. Registrar una compra con tipo de cambio (Ej: 3.80) y precios en dólares (Ej: $10).
4. Ir a "Recepciones Pendientes" y procesar la compra.
5. Revisar el "Kardex de Movimientos" y confirmar que el ingreso se ha registrado con el costo en Soles (Ej: S/38.00).
