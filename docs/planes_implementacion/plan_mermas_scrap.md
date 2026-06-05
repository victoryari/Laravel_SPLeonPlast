# Actualización del Módulo de Mermas y Scrap

Este plan detalla las modificaciones solicitadas para mejorar el flujo de registro de mermas, dividiendo el trabajo en dos etapas principales.

## Etapa 1: Interfaz de Usuario (Visual)

### Formulario de Registro
- **Filtro por Orden de Producción (OP):** 
  - Se añadirá un nuevo campo desplegable al inicio del formulario para seleccionar la **Orden de Producción (OP)** en curso.
- **Filtro Inteligente de Producto Origen:** 
  - El desplegable actual de "Producto Origen" pasará a estar vacío por defecto.
  - Al seleccionar una OP, se cargarán dinámicamente mediante una petición interna (AJAX) **únicamente los Productos en Proceso (PEP)** que pertenecen a dicha OP y que actualmente cuentan con stock disponible en piso.
- **Registro de Múltiples Cantidades (Doble Input):**
  - Se eliminará el actual desplegable de "Tipo de Merma".
  - En su lugar, se colocarán dos campos numéricos paralelos:
    1. **Cantidad Merma Pura (Irrecuperable):** Lo que va a la basura.
    2. **Cantidad Merma Recuperada (Molido):** Lo que se va a reutilizar.
  - El sistema validará visualmente mediante JavaScript que la suma de ambas cantidades no exceda el stock disponible del producto seleccionado.

---

## Etapa 2: Lógica del Sistema (Backend) y Análisis de Consumo

### Lógica de Registro Simultáneo
- **Base de Datos y Trazabilidad:** La tabla `mermas` ya cuenta con el campo `id_orden_produccion`. Este campo se poblará automáticamente.
- **Doble Registro:** Al procesar el formulario, el backend separará la lógica:
  - Si hay cantidad en **Merma Pura**, descontará del stock como salida irrecuperable.
  - Si hay cantidad en **Merma Recuperada**, realizará la salida original y el ingreso de su versión `_RECICLADO`.
  - Ambos registros irán al Kardex simultáneamente.

### Análisis Pendiente: Descuento directo de Materia Prima
*Este análisis queda pendiente de decisión final por parte de la jefatura de producción antes de ejecutar la Etapa 2.*
- **El problema del Doble Descuento:** En la ejecución de producción, el sistema ya consume la materia prima para generar el Producto en Proceso (PEP). Si la merma del PEP intenta descontar insumos directamente de la receta, se sacaría del Kardex la materia prima dos veces, y el stock del PEP quedaría irreal.
- **Decisión requerida:** Definir si se mantiene el descuento a nivel de PEP (recomendado y contablemente cuadrado) o si se implementa un método alterno de explosión de receta para la merma (muy complejo y requiere cambiar cómo se declara la producción inicialmente).
