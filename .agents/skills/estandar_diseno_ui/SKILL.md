---
name: estandar_diseno_ui
description: Estándar corporativo de diseño UI/UX, tipografía, tarjetas y tablas para todas las vistas Blade del ERP LeonPlast. Se activa al crear, modificar o rediseñar vistas, tablas maestras, formularios o pantallas de consulta.
---

# Estándar Corporativo de Diseño UI/UX y Tipografía (LeonPlast ERP)

Este documento establece las directrices visuales, espaciados, tipografías y reglas de maquetación en TailwindCSS para mantener un diseño visual impecable, consistente, industrial y limpio en todo el sistema.

---

## 1. Contenedores y Estructura General
- **Contenedor Principal de la Página:**
  ```html
  <div class="container mx-auto pb-8 md:pb-10">
  ```
- **Cabecera Estándar de la Página:**
  Siempre utilizar el componente Blade reutilizable `<x-page-header>`:
  ```html
  <x-page-header title="Título de la Vista" subtitle="Descripción corta y funcional de la vista">
      <x-slot:actions>
          <a href="..." class="btn-primary">
              <i class="fas fa-plus"></i>
              <span class="hidden sm:inline ml-2">Nuevo</span>
          </a>
      </x-slot:actions>
  </x-page-header>
  ```

---

## 2. Formularios y Campos de Entrada (Inputs/Selects)
- **Etiquetas de Campo (Labels):**
  Texto en mayúsculas, negrita, tamaño pequeño con rastreo amplio e icono descriptivo:
  ```html
  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
      🏢 Nombre del Campo <span class="text-red-500">*</span>
  </label>
  ```
- **Entradas de Texto (`input`), Desplegables (`select`) y Áreas de Texto (`textarea`):**
  Altura compacta de 40px (`py-2`), esquinas redondeadas (`rounded-lg`), borde gris suave (`border-slate-300`) y sombra ligera (`shadow-xs`):
  ```html
  <input type="text" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition bg-white shadow-xs" placeholder="...">
  ```

---

## 3. Tarjetas y Secciones (`Cards`)
- **Contenedor de Sección:**
  ```html
  <div class="bg-white rounded-xl shadow-md border border-slate-200/80 p-5 md:p-6 mb-5">
      <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
          <i class="fas fa-icon text-emerald-600 text-base"></i>
          <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wide">1. Título de la Sección</h2>
      </div>
      ...
  </div>
  ```

---

## 4. Tablas de Datos (Listados y Maestros)
- **Contenedor Principal de Tabla:**
  ```html
  <div class="bg-white rounded-xl shadow-md border border-slate-200/80 overflow-hidden">
      <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse min-w-[700px]">
  ```
- **Encabezado de Tabla (`thead`):**
  Fondo oscuro slate profesional (`bg-slate-800`), texto blanco en mayúsculas de 11px en negrita (`text-white text-[11px] uppercase tracking-wider font-bold`) y padding vertical de 12px (`py-3 px-4 md:px-6`):
  ```html
  <thead>
      <tr class="bg-slate-800 text-white text-[11px] uppercase tracking-wider font-bold">
          <th class="py-3 px-4 md:px-6 w-32">Código</th>
          <th class="py-3 px-4 md:px-6">Descripción</th>
          <th class="py-3 px-4 md:px-6 text-center">Estado</th>
          <th class="py-3 px-4 md:px-6 text-center w-36">Acciones</th>
      </tr>
  </thead>
  ```
- **Filas de Tabla (`tbody tr` y `td`):**
  Efecto hover suave (`hover:bg-slate-50/80`), padding vertical compacto de 10px (`py-2.5 px-4 md:px-6`):
  ```html
  <tr class="hover:bg-slate-50/80 transition duration-150">
      <td class="px-4 md:px-6 py-2.5 font-bold text-xs md:text-sm text-slate-900 whitespace-nowrap">{{ $codigo }}</td>
      <td class="px-4 md:px-6 py-2.5 text-xs md:text-sm font-medium text-slate-800 uppercase">{{ $descripcion }}</td>
      ...
  </tr>
  ```
- **Botones de Acción en Tablas (Tamaño 32x32px `w-8 h-8`):**
  - **Editar:** `inline-flex items-center justify-center w-8 h-8 text-primary bg-primary-50 hover:bg-primary hover:text-white rounded-lg transition-all shadow-2xs`
  - **Anular / Eliminar:** `inline-flex items-center justify-center w-8 h-8 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white rounded-lg transition-all shadow-2xs`
  - **Especial / Composición:** `inline-flex items-center justify-center w-8 h-8 text-purple-600 bg-purple-50 hover:bg-purple-600 hover:text-white rounded-lg transition-all shadow-2xs`

---

## 5. Etiquetas de Estado y Píldoras (`Badges`)
- **Asignado / Éxito / Disponible:**
  ```html
  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100/80">
      Texto
  </span>
  ```
- **Peligro / Bajo Mínimo / Anulado:**
  ```html
  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
      Alerta
  </span>
  ```
- **No asignado / Vacío:**
  ```html
  <span class="text-xs text-slate-400 italic font-normal">No asignado</span>
  ```

---

## 6. Botones Principales de Acción
- **Botón Primario (`.btn-primary`):** Verde esmeralda con texto en negrita y sombra.
- **Botón Secundario (`.btn-secondary`):** Fondo gris slate muy suave con borde `border-slate-300`.
