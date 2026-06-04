# Módulo Independiente de Mermas y Scrap - Plan de Implementación

Este documento detalla la estructura para el **Módulo Independiente** que maneja las mermas y el reciclado en LeonPlast, adaptado a la realidad de la planta.

## Flujograma del Proceso Operativo

A continuación, se detalla el ciclo de vida del material desde que es requerido en planta, hasta cómo se manejan sus desperdicios dependiendo del proceso.

```mermaid
flowchart TD
    %% Estilos
    classDef almacen fill:#e1f5fe,stroke:#0288d1,stroke-width:2px;
    classDef proceso fill:#fff3e0,stroke:#f57c00,stroke-width:2px;
    classDef decision fill:#f3e5f5,stroke:#8e24aa,stroke-width:2px;
    classDef merma fill:#ffebee,stroke:#d32f2f,stroke-width:2px;
    classDef recuperado fill:#e8f5e9,stroke:#388e3c,stroke-width:2px;

    %% Nodos
    REQ[Requerimiento de Materiales] --> ALM_PRIN[Almacén Principal<br>(Mat. Virgen + Recuperado)]:::almacen
    ALM_PRIN -->|Despacho| OP(Orden de Producción):::proceso
    
    OP --> PROC{¿Qué proceso se ejecuta?}:::decision
    
    %% Rama 1: Inyección / Extrusión
    PROC -- Inyección / Extrusión --> INY[Inyección de Piezas]:::proceso
    INY --> OK_INY{¿Es Conforme?}:::decision
    OK_INY -- Sí --> PT[Pasa al siguiente proceso]:::proceso
    
    OK_INY -- No (Colada / Mala Inyección) --> MOLINO[Molino Automático<br>a pie de máquina]:::proceso
    MOLINO --> REG_REC[Operador registra 'Reciclado']:::recuperado
    REG_REC -.->|Retorna al almacén<br>(Costo según Parámetro)| ALM_PRIN
    
    %% Rama 2: Procesos Posteriores
    PROC -- Armado / Embolsado / Encartonado --> EMP[Procesos de Empaque]:::proceso
    EMP --> OK_EMP{¿Se dañó algo?}:::decision
    OK_EMP -- No --> FINAL[Producto Terminado<br>Listo para Venta]:::almacen
    
    OK_EMP -- Sí (Vaso roto / Caja rota) --> REG_MERMA[Operador registra 'Merma Pura']:::merma
    REG_MERMA --> ALM_REC[ALM-REC<br>Almacén de Mermas y Reciclaje]:::almacen
    ALM_REC -.-> DESTINO((Disposición Final:<br>Venta como scrap, Destrucción))
```

---

## 1. Tipos de Desperdicio y Comportamiento
Existen dos tipos principales de desperdicio que el sistema manejará:

1.  **Merma Pura (No se reprocesa):** 
    - Desecho que no vuelve a producción (ej. cajas rotas, vasos rotos en encartonado).
    - Al registrarse, el sistema hace una transferencia automática al **Almacén de Mermas y Reciclado (ALM-REC)** y se queda ahí como merma definitiva.
2.  **Reciclado / Molido Automático (Solo Inyección/Extrusión con molino a pie de máquina):** 
    - Las coladas y piezas mal inyectadas se muelen inmediatamente.
    - Al registrarse, el sistema ingresa este material (con su código de producto recuperado) directamente al **Almacén Original** de la materia prima, dejándolo listo para la siguiente inyección.

## 2. Nuevo Módulo Independiente centralizado
Se creará una sección exclusiva en el menú principal llamada **"Mermas y Reciclado"**. 

- **Pantalla de Registro:** Cualquier operador, de cualquier proceso, entra aquí.
    - Selecciona su OP / Máquina.
    - Si es Inyección, puede elegir registrar **"Reciclado"** (y el sistema lo manda al almacén original como molido).
    - Si es de procesos posteriores (Armado, Embolsado, Encartonado), **solo puede registrar "Merma Pura"**, mandando el daño al `ALM-REC` definitivamente.

### **Interacción con la Orden de Producción**
- **Se elimina el campo/sección de "Registro de Merma"** de la vista de ejecución de actividades de la OP.
- La OP quedará limpia y enfocada solo en el consumo normal de materiales y tiempos.
- Dentro de la OP habrá un enlace o etiqueta: *"Mermas asociadas: X Kg (Ver detalle)"* que redireccionará al Módulo de Mermas.

## 3. Costeo del Reciclado (Resolución Contable)
El costo con el que el material "Reciclado Automático" ingresa de vuelta al Kardex y al Almacén Original **no estará fijo (hardcoded) en el sistema**. 
Se utilizará el modelo y tabla existente `ParametroSistema` para definir cómo se costea.
- Se creará un parámetro (ej. `PORCENTAJE_COSTO_RECICLADO` o `COSTO_FIJO_RECICLADO`).
- El área de contabilidad podrá entrar al "Módulo de Parámetros del Sistema" y decidir si el reciclado cuesta $0, si cuesta un porcentaje del material original, o si tiene un costo fijo predeterminado.
- Al registrar el reciclado, el controlador leerá este parámetro y registrará el ingreso al Kardex con el valor dictado.
