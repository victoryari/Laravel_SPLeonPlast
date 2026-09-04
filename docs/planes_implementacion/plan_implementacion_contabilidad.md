# Propuesta de Funcionalidades para el Módulo de Contabilidad

Basado en la estructura actual del sistema (que ya maneja compras, inventario valorizado, órdenes de producción, mermas y procesos de terceros), el módulo de **Contabilidad** tiene el potencial de convertirse en el núcleo financiero de la empresa. 

Actualmente, el sistema ya captura la base más importante: **cantidades y costos unitarios** (Kardex). A partir de esto, sugiero implementar las siguientes funcionalidades divididas por áreas de impacto:

## 1. Contabilidad de Costos (Costeo Industrial)
Dado que LeonPlast es una empresa de manufactura, el costeo preciso es vital.
- **Costeo de Órdenes de Producción (Real vs. Estándar):** Un submódulo que tome una OP finalizada y calcule el costo real total sumando:
  - Costo de materia prima consumida (según Kardex de salida).
  - Costos indirectos de fabricación (CIF) o tasas por hora de máquina/centro de trabajo (Inyectora, Molino, etc.).
  - Mano de obra directa (opcional).
- **Valorización de Mermas y Recuperados:** Asignar un valor financiero (costo de recuperación) al material molido/reciclado que reingresa al inventario, para no perder el rastro del dinero invertido en purgas y coladas.

## 2. Gestión de Inventarios y Cierres
- **Reporte de Valorización de Inventario:** Un reporte en tiempo real que muestre el "Dinero inmovilizado" por almacén (Insumos, Productos en Proceso, Producto Terminado).
- **Cierre Contable Mensual de Kardex:** Funcionalidad para "congelar" el Kardex al final de cada mes, impidiendo modificaciones en fechas pasadas y generando un saldo inicial inamovible para el mes siguiente.
- **Ajustes Contables de Inventario:** Interfaz específica para justificar diferencias de inventario (robos, deterioro, mermas no operativas) con su respectiva cuenta de gasto.

## 3. Cuentas por Pagar (Proveedores)
Actualmente el sistema registra ingresos de compras. Faltaría la gestión del pasivo:
- **Registro de Facturas de Compra (Provisión):** Vincular las Guías de Remisión / Órdenes de Compra con la Factura física/electrónica del proveedor.
- **Cronograma de Pagos (Estado de Cuenta):** Reporte de deudas por vencer a 30, 60 y 90 días, alertando a tesorería qué proveedores deben pagarse esta semana.
- **Registro de Pagos y Abonos:** Permitir ingresar transferencias bancarias o cheques entregados para ir amortizando facturas.

## 4. Integración Contable (Asientos)
- **Generador de Asientos Automáticos:** Reglas que conviertan cada movimiento operativo en un asiento contable (Debe / Haber). Por ejemplo:
  - *Ingreso de Compra:* Ingresa a Inventario (Debe) / Cuenta por Pagar (Haber).
  - *Consumo de Producción:* Gasto de Producción (Debe) / Inventario de Insumos (Haber).
- **Exportación a Software Contable (Concar, Siscont, Exact, etc.):** Formatos TXT o Excel preconfigurados para exportar el registro de compras, ventas o diario al software que use el contador externo.

## 5. Control de Terceros (Maquilas)
- **Conciliación de Cuentas de Maquila:** Reporte que cruce los kilos de materia prima enviados al tercero vs. los kilos de producto terminado devueltos, costeando la diferencia como pérdida o cobro al tercero.
- **Registro de Facturas por Servicio de Maquila.**

## User Review Required

> [!IMPORTANT]
> **Priorización:** De todos los puntos mencionados arriba, ¿cuál consideras que es la prioridad número uno para la gerencia o administración en este momento? 
> 
> Te sugiero iniciar por el **Costeo de Órdenes de Producción** o por **Cuentas por Pagar**, ya que brindan beneficios financieros inmediatos.

> [!NOTE]
> Revisa la propuesta y dime por qué bloque te gustaría empezar para preparar un diseño más detallado de esa funcionalidad en específico.
