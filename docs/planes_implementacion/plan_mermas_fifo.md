# Plan de Implementación: Trazabilidad de Lotes en Mermas

Este plan detalla los cambios necesarios para que el módulo de mermas considere la trazabilidad de lotes.
Por decisión actual, **solo se implementará la lógica FIFO para la salida de materia prima**.
El ingreso de material recuperado con lotes queda **pendiente** para una fase posterior.

## Cambios a Implementar

### `app/Http/Controllers/MermaController.php`

1.  **Salida de Materia Prima (FIFO):**
    *   Modificar el bucle que descuenta la materia prima para obtener todos los lotes disponibles del producto ordenados por `fecha_vencimiento` (ascendente) y `lote` (ascendente).
    *   Implementar consumo en cascada (FIFO): si el primer lote no tiene suficiente stock para cubrir la merma, descontará lo que haya y pasará al siguiente lote, registrando un movimiento en el `kardex` por cada lote afectado.
    *   Incluir el campo `lote` y `fecha_vencimiento` en el `insert` del Kardex.
