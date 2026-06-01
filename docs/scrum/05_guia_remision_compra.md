# Historia de Usuario: Módulo de Guía de Remisión de Compra

**Estado actual:** El flujo actual inicia con el registro de la Factura de Compra (en estado PENDIENTE) por parte de administración/compras. Luego, el almacenero realiza la "Recepción de Pendientes" para ingresar la mercadería al almacén basándose en esa factura. 
**Lo que se busca:** Invertir el flujo para reflejar la realidad operativa física. El proveedor envía la mercadería con una Guía de Remisión, la cual es recibida por el almacenero y debe ingresar inmediatamente al almacén transitorio. Posteriormente, cuando llega la Factura de Compra, administración la registra utilizando la Guía como referencia (sin duplicar el ingreso al inventario).

## Descripción

**Como** Almacenero y Usuario de Compras,
**quiero** registrar las Guías de Remisión recibidas de los proveedores para el ingreso inmediato de materia prima al almacén transitorio, y luego usar esa guía como referencia al registrar la Factura de Compra,
**para** independizar la recepción física de la recepción de facturas, manteniendo el inventario actualizado en tiempo real y evitando ingresos duplicados.

## Criterios de Aceptación

- `[ ]` **SCRUM-49 (Nuevo Módulo Guía de Remisión):** Crear una nueva interfaz para "Guías de Remisión de Compra" donde el almacenero pueda registrar los datos de la guía (Proveedor, N° Documento, Fecha).
- `[ ]` **SCRUM-50 (Detalle de Ingreso y Lotes):** Al registrar la guía, se debe detallar los productos, cantidades, lote y fecha de vencimiento. El almacén de destino será por defecto "ALMACÉN COMPRAS NAC/IMP".
- `[ ]` **SCRUM-51 (Impacto Inmediato en Inventario):** Guardar la Guía de Remisión debe generar automáticamente el ingreso al Kardex e Inventario, marcando la guía como "RECIBIDA".
- `[ ]` **SCRUM-52 (Factura referenciando a Guía):** Al crear una Factura de Compra en el módulo de Compras, debe existir la opción de "Vincular a Guía de Remisión". Esto debe precargar el proveedor y las filas de productos de la guía.
- `[ ]` **SCRUM-53 (Anulación de Ingreso por Factura):** Si una Factura de Compra está vinculada a una o más Guías de Remisión, el registro de la Factura **NO** debe generar nuevos movimientos de ingreso en el inventario/kardex. 

---

## Análisis de Factibilidad y Reglas de Negocio

El flujo propuesto es **completamente factible** a nivel arquitectónico y estructural dentro del proyecto LeonPlast, de hecho, se alinea mucho mejor con las prácticas contables y logísticas (flujo "Three-Way Match" simplificado).

**Reglas de negocio a considerar en la implementación técnica:**

1. **Problema de la Valorización (Kardex Valorizado):**
   * **El desafío:** El almacenero que recibe la guía física normalmente desconoce el precio de los productos (este dato llega luego en la factura). Sin embargo, el Kardex en este proyecto es estrictamente valorizado y requiere un "Costo de Entrada" para calcular el "Costo Promedio".
   * **Solución técnica factible:** Al momento de que el almacenero guarde la Guía de Remisión y se genere el ingreso al Kardex, el sistema deberá capturar el **Último Costo Promedio** del producto (mediante `KardexService`) y usarlo temporalmente como costo de ingreso valorizado para no romper la continuidad contable del kardex.

2. **Cambio de Responsabilidades en Base de Datos:**
   * Se requiere crear dos tablas nuevas: `guia_remision_compras` y `detalle_guia_remision_compras`.
   * En la tabla actual de `compras`, se debe agregar un campo `id_guia_remision_compra` (o crear una tabla intermedia si se permite agrupar múltiples guías en una sola factura).

3. **Flujo de Recepciones Pendientes:**
   * El módulo actual de "Recepción de Pendientes" perdería su propósito principal o tendría que convivir con el nuevo flujo (por ejemplo, permitir compras sin guía o compras por servicios). Sería recomendable decidir si el flujo Guía -> Factura será el 100% obligatorio para materia prima.

4. **Desacoplamiento:**
   * El cambio asegurará que el inventario físico ("ALMACÉN COMPRAS NAC/IMP") refleje la realidad instantánea cuando llega el camión, sin esperar a que el área de administración digite la factura (lo cual suele suceder días después).
