# Historia de Usuario: Generación de Orden de Producción (OP)

**Estado actual:** El sistema ya cuenta con tablas para `orden_produccion_global`, `orden_proceso`, y `formula_produccion` (Listas de Materiales / BOM). 
**Lo que se busca:** Estandarizar y documentar ágilmente la funcionalidad principal que detona todo el trabajo en la fábrica: la creación de la OP y el cálculo automático de sus requerimientos según la receta del producto.

## Historia de Usuario
**Como** Planificador de Producción / Supervisor
**Quiero** registrar una Orden de Producción Global seleccionando un producto terminado y la cantidad a fabricar
**Para** que el sistema desglose automáticamente las etapas de fabricación (Órdenes de Proceso) y calcule con precisión los insumos necesarios basándose en la fórmula activa, estandarizando la producción.

## Criterios de Aceptación
- **Creación y Validación:** El formulario de nueva OP exige seleccionar un producto terminado, una cantidad a fabricar, y (opcionalmente) un cliente/lote destino. El sistema debe bloquear la creación si el producto seleccionado **no tiene una fórmula o ruta de procesos activa**.
- **Explosión de Materiales (BOM):** Al guardar la OP, el sistema multiplica los componentes definidos en la `formula_produccion` por la cantidad requerida, calculando la demanda teórica de materia prima y guardándola en `componentes_orden_produccion_global`.
- **Generación Automática de Órdenes de Proceso:** El sistema detecta qué procesos están vinculados al producto (ej. INYECTADO, ENSAMBLADO) y genera automáticamente los registros hijos en la tabla `orden_proceso` en estado `PENDIENTE`.
- **Gestión de Estados:** 
  - La OP nace en estado `PENDIENTE`.
  - Pasa automáticamente a `EN_PROCESO` cuando su primera orden de proceso hija recibe movimientos o reportes.
  - Pasa a `TERMINADO` cuando todas las etapas de la ruta están completadas y se ingresa el producto al almacén de terminados.
- **Modificación y Anulación:** Una OP puede editarse o anularse **solamente** si se encuentra en estado `PENDIENTE` (es decir, si el almacén aún no ha despachado materiales y no hay reportes de avance de los operarios).
- **Trazabilidad Visual:** La bandeja principal de OPs debe mostrar un indicador o barra de progreso que resuma el estado de las sub-órdenes de proceso.

## Notas Técnicas
- **Modelos Involucrados:** `OrdenProduccionGlobal`, `OrdenProceso`, `FormulaProduccion`, `ComposicionFormula`, `ComponenteOrdenProduccionGlobal`.
- **Lógica de Explosión:** Se sugiere encapsular la lógica matemática del BOM en un servicio (ej. `ProduccionService@explotarMateriales`) para mantener limpio el controlador.
- **Relaciones BD:** Asegurar las cascadas lógicas: Al anular una OP Global, deben anularse sus `orden_proceso` hijas si el estado lo permite.
- El diseño del listado (`index.blade.php`) puede beneficiarse de "Badges" (etiquetas de colores) de TailwindCSS para destacar rápidamente qué órdenes urgen y cuáles están listas.
