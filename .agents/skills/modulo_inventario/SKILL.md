---
name: Lógica de Inventario y Kardex
description: Se activa al trabajar con ingresos, salidas, KardexService, almacenes y transferencias.
---

# Lógica de Inventario y Kardex

Escribe aquí las reglas de negocio, tablas clave y flujos específicos de este módulo.

## Reglas Actuales:
1. El inventario contable y los saldos promedios son administrados por KardexService.
2. Nunca se debe alterar la cantidad de la tabla inventario de forma directa con un UPDATE, siempre debe pasar por una función de registro.
