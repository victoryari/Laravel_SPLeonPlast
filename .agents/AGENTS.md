# LeonPlast - Reglas y Arquitectura Global del Proyecto

Este archivo documenta las directrices globales, estructura modular y estándares de diseño del proyecto LeonPlast. Se carga automáticamente como contexto en cada sesión para que la IA comprenda la arquitectura general.

## 1. Arquitectura y Stack Tecnológico
- **Backend:** Laravel (PHP). 
- **Frontend:** Laravel Blade.
- **Estilos:** Clases utilitarias de TailwindCSS y CSS puro.

## 2. Módulos Principales del Sistema
El sistema es un ERP enfocado en la producción de plásticos. Está dividido en los siguientes módulos clave, cada uno con su propia lógica de negocio:

1. **Producción (`OrdenProcesoController`, `OrdenProduccionController`, `FormulaController`)**
   - **Lógica Clave:** Controla los procesos de Inyectado, Mezclado, Ensamblado, Molido, etc.
   - Existen operaciones que consumen componentes en base a una Fórmula (`codigo_formula_produccion`), y operaciones directas (como mermas o reciclados) que usan productos directos y mandan `null` a la base de datos para la fórmula.
   - Siempre debe coordinar el consumo/ingreso de materiales con el `KardexService`.

2. **Inventario y Almacén (`InventarioController`, `TransferenciaAlmacenController`, `MermaController`)**
   - **Lógica Clave:** Todo movimiento (ingreso/salida) impacta la tabla `inventario` y `kardex`. 
   - `KardexService` es el núcleo responsable de registrar historial y recalcular costos promedios. Jamás se debe alterar el inventario sin pasar por este servicio.

3. **Compras (`CompraController`, `GuiaRemisionCompraController`)**
   - Gestiona las entradas de materia prima o insumos. 
   - Impacta cuentas por pagar e inventarios.

4. **Terceros / Maquila (`GuiaTercerosSalidaController`, `TercerosLiquidacionController`)**
   - Gestión de material enviado a empresas externas (maquiladores) para ser inyectado, ensamblado o troquelado, y su posterior retorno.

5. **Requerimientos (`RequerimientoMaterialController`, `DespachoRequerimientoController`)**
   - Lógica de pedidos internos desde planta hacia almacén y sus despachos.

## 3. Estándares Visuales (Diseño UI/UX)
- Se debe mantener una estética "Clean & Industrial".
- **Colores y Alertas Visuales (Badges):** 
  - `PRODUCCIÓN`: `bg-blue-100 text-blue-800`
  - `ACTIVIDAD` / `MANUAL`: `bg-teal-100 text-teal-800`
  - `LIMPIEZA`: `bg-red-100 text-red-800`
  - `MERMA`: `bg-orange-100 text-orange-800`
  - `RECICLADO`: `bg-purple-100 text-purple-800`
- **Formularios e Inputs:** Uso consistente de `border-slate-300 rounded-md shadow-sm text-sm focus:ring-primary focus:border-primary`.

## 4. Tipos de Productos (`codigo_tipo_producto`)
- `PEP`: Producto en Proceso (Ensamblados, Cáscaras inyectadas). (Empiezan con `EN` o `CA`).
- `MTP`: Materia Prima (Clips, Polímeros, Masterbatch).
- `INS`: Insumos.

## 5. Reglas de Código (Convenciones)
- **Frontend (Blade):** Para interactividad en vistas se utiliza **Vanilla JavaScript** (DOM puro con `document.getElementById`, `addEventListener`). Evitar jQuery o Alpine.js a menos que el módulo ya lo use de forma nativa.
- **Controladores:** Los controladores deben validar datos, procesar la lógica y apoyarse en `DB::transaction` para operaciones que involucren múltiples tablas (Ej. Producción + Inventario).
