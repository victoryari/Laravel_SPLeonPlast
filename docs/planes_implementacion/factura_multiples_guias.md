# Vincular Múltiples Guías a una Factura

Este plan detalla los cambios necesarios para permitir que el área de compras registre una sola factura vinculada a varias Guías de Remisión (ej. consolidado mensual del proveedor).

## Preguntas Abiertas Resueltas
1. **Proveedores Diferentes:** El sistema validará que todas las guías seleccionadas pertenezcan al mismo proveedor (mismo RUC) para evitar errores.

## Cambios Propuestos

---

### Módulo de Base de Datos

#### [MODIFY] `guia_remision_compras`
- Agregar la columna `id_compra` (entero, que permite nulos) para establecer la relación de Múltiples Guías -> 1 Compra.

#### [MODIFY] `compras`
- La columna actual `id_guia_remision_compra` quedará inactiva o como null (se mantendrá por ahora por compatibilidad).

---

### Módulo de Compras (Controladores y Modelos)

#### [MODIFY] `app/Models/Compra.php` y `GuiaRemisionCompra.php`
- Cambiar la relación para soportar que Una Compra tiene Muchas Guías.

#### [MODIFY] `app/Http/Controllers/CompraController.php`
- En el método `store()`, modificar la lógica: 
  - Recibir un arreglo de IDs de guías (`$request->ids_guias`).
  - Al guardar la compra, buscar todas las guías de ese arreglo y actualizarlas masivamente para asignarles el `id_compra` y cambiar su estado a `FACTURADA`.

#### [NEW] API Endpoint (opcional/ajuste)
- Modificar el endpoint de consulta JS (o crear uno nuevo) para que si el usuario envía múltiples IDs de guía, devuelva el detalle combinado de todas ellas.

---

### Módulo de Compras (Interfaz de Usuario)

#### [MODIFY] `resources/views/compras/create.blade.php`
- Cambiar el `<select name="id_guia_remision_compra">` por un `<select name="ids_guias[]" multiple="multiple">`.
- Aplicar la librería **Select2** (ya incluida en el proyecto) para que el usuario pueda hacer clic, buscar y seleccionar varias guías de forma visual y amigable.
- Actualizar el evento `on('change')` en JavaScript para que:
  - Al seleccionar o deseleccionar una guía, limpie la tabla y cargue el detalle agrupado de todas las guías actualmente seleccionadas.
  - Asegure que el proveedor general de la factura sea el correspondiente a las guías.
