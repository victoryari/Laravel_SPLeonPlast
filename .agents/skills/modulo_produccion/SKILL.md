---
name: Lógica de Producción LeonPlast
description: Se activa al trabajar con Ordenes de Produccion, Procesos, Mermas, Ensamblados o Inyectados.
---

# Lógica de Producción LeonPlast

Escribe aquí las reglas de negocio, tablas clave y flujos específicos de este módulo.

## Reglas Actuales:
1. Las Mermas (merma_clip, merma_cascara) se guardan enviando "null" en codigo_formula_produccion para evitar errores de Foreign Key.
2. Clip Recuperado se procesa como "recuperado_clip" para diferenciarlo en BD.
