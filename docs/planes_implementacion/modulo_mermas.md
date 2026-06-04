# Módulo de Mermas y Producto Reciclado

## Viabilidad
**Es 100% factible y altamente recomendable.** De hecho, la estructura actual del proyecto ya dejó preparadas las bases para esto. Actualmente, cuando registras merma en la ejecución de un proceso, el sistema crea automáticamente un producto genérico llamado `MERMA-001` y lo envía a un almacén que creamos internamente llamado `ALM-REC` (Almacén de Reciclaje y Merma).

Sin embargo, en la industria del plástico, la merma no es genérica. Generas scrap de diferentes materiales (PP, PS, colores) que luego se muelen o peletizan para volver a ser Materia Prima Recuperada. 

Para manejar esto de forma profesional, propongo la siguiente estructura:

## 1. Clasificación de Productos (Catálogos)
Actualmente tenemos `MTP` (Materia Prima) y `SUM` (Suministros). Crearemos dos nuevos tipos de producto (o usaremos Suministros clasificados):
- **SCRAP / MERMA PURA (`SCR`)**: Es el desecho tal cual sale de la máquina (coladas, piezas mal inyectadas). Se almacena en `ALM-REC`.
- **MATERIAL RECUPERADO / MOLIDO (`REC`)**: Es el scrap después de pasar por el molino. Se comporta igual que una Materia Prima (`MTP`) y se almacena en el Almacén Principal para ser usado en nuevas fórmulas.

## 2. Registro de Mermas en Producción (Mejora de la OP)
- **Actualmente**: Al declarar merma, solo pones los kilos y va a `MERMA-001`.
- **Propuesta**: Al registrar componentes en el proceso de Inyección/Troquelado, si hubo merma, el usuario podrá seleccionar de un desplegable **qué tipo de Scrap generó** (Ej: "Scrap PP Rojo", "Scrap PS Cristal") y los Kilos. Ese scrap exacto ingresará al `ALM-REC`.

## 3. Nuevo Módulo: "Procesos de Reciclaje / Molienda"
Crearemos una pantalla nueva en Producción dedicada exclusivamente a transformar la Merma en Material Recuperado.
- **Entrada**: El usuario selecciona qué Scrap va a moler y la cantidad de kilos que sacará del `ALM-REC`.
- **Salida**: El usuario indica cuánto Material Recuperado se obtuvo (Ej. "Molido PP Rojo") y esto ingresa como stock al Almacén Principal.
- El sistema hará automáticamente la "Salida" del Scrap y el "Ingreso" del Material Molido en el Kardex, permitiendo costear el material recuperado.

## 4. Reutilización en Fórmulas
Una vez que el Material Recuperado está en el almacén principal, podrás agregarlo a las Fórmulas de Producción (`composicion_formula`) igual que una resina virgen, asignando porcentajes de material virgen vs recuperado.

---

## User Review Required
> [!IMPORTANT]
> **Aprobación de la lógica**
> ¿Esta lógica de "Scrap -> Molino -> Material Recuperado" coincide con la forma en que trabajan en planta? 

## Open Questions
1. **Costeo de la Merma:** Actualmente la merma entra al almacén con un costo de $0 (porque es desecho). ¿Deseas que se mantenga en costo $0, o prefieres que la merma herede el costo de la materia prima virgen de la cual provino? (La mayoría de las plantas lo dejan en $0 para que el material molido baje el costo de las futuras producciones, pero depende de tu área contable).
2. **Máquinas Molinos:** ¿Los molinos los tratamos como "Centros de Trabajo" que generan un costo por hora de molienda, o hacemos una pantalla rápida y sencilla de "Molienda" que solo transforme un material en otro sin costear la mano de obra del molino?
