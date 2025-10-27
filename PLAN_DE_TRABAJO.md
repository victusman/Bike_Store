# 📋 PLAN DE TRABAJO - TIENDA EN LÍNEA BIKE STORE

## 📊 ANÁLISIS DEL PROYECTO ACTUAL

### ✅ COMPONENTES IMPLEMENTADOS

#### 1. **Base de Datos (MySQL)**
- ✅ Tabla `usuarios` - Gestión de usuarios administradores
- ✅ Tabla `categoria` - Categorías de productos
- ✅ Tabla `productos` - Catálogo de productos con relación a categorías
- ✅ Tabla `customer` - Clientes registrados
- ✅ Tabla `orders` - Pedidos realizados
- ✅ Tabla `order_items` - Detalle de items por pedido
- ✅ Datos de ejemplo insertados

#### 2. **Sistema de Autenticación**
- ✅ `login.php` - Login con usuario/email y password
- ✅ `auth.php` - Protección de rutas administrativas
- ✅ `cerrar.php` - Cierre de sesión
- ✅ Sistema de sesiones implementado

#### 3. **Panel Administrativo (CRUD Completo)**
- ✅ **Módulo Categorías** (crear, editar, eliminar, listar)
- ✅ **Módulo Productos** (crear, editar, eliminar, listar con búsqueda)
- ✅ **Módulo Clientes** (crear, editar, eliminar, listar)
- ✅ **Módulo Usuarios** (crear, editar, eliminar, listar)
- ✅ **Módulo Pedidos** (crear, ver detalle, anular, generar PDF)
- ✅ **Módulo Stocks** (gestión de inventario)
- ✅ **Módulo Tiendas** (gestión de tiendas)

#### 4. **Funcionalidades Adicionales**
- ✅ Subida de imágenes para productos
- ✅ Generación de PDF para pedidos (usando dompdf)
- ✅ Generación de PDF para fichas de clientes
- ✅ Bootstrap 5 integrado
- ✅ Diseño responsive
- ✅ Búsqueda de productos

---

## ❌ COMPONENTES FALTANTES (SEGÚN REQUISITOS)

### 🔴 **ÁREA CLIENTE (FRONTEND PÚBLICO)**

#### **REQUISITOS PENDIENTES:**

1. **Página de Inicio Pública (sin login)**
   - ❌ Carrusel con 5 imágenes de productos destacados
   - ❌ Visualización de 4 productos más vendidos en tarjetas
   - ❌ Acceso sin login requerido

2. **Navegación Cliente**
   - ❌ Menú público: Productos, Pedidos, Carrito, Iniciar Sesión
   - ❌ Visualización de productos por categorías (A-Z)
   - ❌ Productos en formato de tarjetas con: imagen, nombre, año, precio, descuento, botón "Agregar al carrito"

3. **Sistema de Carrito de Compras**
   - ❌ Carrito de compras funcional
   - ❌ Agregar/quitar productos
   - ❌ Modificar cantidades
   - ❌ Visualizar total

4. **Proceso de Compra**
   - ❌ Login de cliente SIN contraseña (solo email o identificación simple)
   - ❌ Registro rápido de cliente
   - ❌ Confirmación de pedido

5. **Métodos de Pago**
   - ❌ Visualización de métodos: Envío gratuito, QR, débito automático, efectivo
   - ❌ Selección de método de pago
   - ❌ Procesamiento de pago (simulado)

6. **Facturación**
   - ❌ Generación de factura al finalizar compra
   - ❌ Opción de impresión
   - ❌ Envío automático al correo electrónico del cliente

### 🟡 **ÁREA ADMINISTRADOR (MEJORAS)**

7. **Dashboard de Estadísticas**
   - ❌ Panel con estadísticas generales (ventas, productos, clientes)
   - ❌ Gráficos y métricas
   - ❌ Productos más vendidos
   - ❌ Ventas del mes/año

8. **Gestión de Productos Destacados**
   - ❌ Marcar productos como "destacados" para el carrusel
   - ❌ Gestión de descuentos/promociones

---

## 🎯 PLAN DE TRABAJO PASO A PASO

### **FASE 1: PREPARACIÓN Y ESTRUCTURA** ⏱️ Estimado: 1-2 horas

#### Paso 1.1: Actualizar Base de Datos
- [x] Agregar campo `destacado` (BOOLEAN) a tabla `productos`
- [x] Agregar campo `descuento` (DECIMAL) a tabla `productos`
- [x] Agregar campo `stock_disponible` (INT) a tabla `productos` (si no existe)
- [x] Crear tabla `carrito_temporal` para gestionar carritos de clientes no registrados
- [x] Crear tabla `metodos_pago` para gestionar métodos de pago disponibles

#### Paso 1.2: Crear Estructura de Carpetas
- [x] Crear carpeta `/cliente/` para el frontend público
- [x] Crear subcarpetas: `/cliente/pages/`, `/cliente/components/`, `/cliente/assets/`
- [x] Separar lógica de admin y cliente

---

### **FASE 2: FRONTEND PÚBLICO - CATÁLOGO** ⏱️ Estimado: 3-4 horas

#### Paso 2.1: Página de Inicio Pública
- [ ] Crear `cliente/index.php` - Home page pública
- [ ] Implementar carrusel con 5 productos destacados (Bootstrap Carousel)
- [ ] Mostrar 4 productos más vendidos en tarjetas
- [ ] Crear header público (sin autenticación)
- [ ] Crear footer público

#### Paso 2.2: Catálogo de Productos
- [ ] Crear `cliente/catalogo.php` - Listado de productos por categorías
- [ ] Ordenar categorías alfabéticamente (A-Z)
- [ ] Mostrar productos en tarjetas con:
  - Imagen
  - Nombre del producto
  - Año del modelo
  - Precio
  - Descuento (si aplica)
  - Botón "Agregar al carrito"
- [ ] Implementar filtros por categoría
- [ ] Implementar búsqueda de productos

#### Paso 2.3: Vista Detalle de Producto
- [ ] Crear `cliente/producto.php?id=` - Detalle de producto individual
- [ ] Mostrar información completa
- [ ] Opción para seleccionar cantidad
- [ ] Botón "Agregar al carrito"

---

### **FASE 3: SISTEMA DE CARRITO DE COMPRAS** ⏱️ Estimado: 3-4 horas

#### Paso 3.1: Funcionalidad del Carrito
- [ ] Crear `cliente/carrito.php` - Vista del carrito
- [ ] Implementar lógica para agregar productos (usando sesiones o cookies)
- [ ] Mostrar productos agregados en tabla/lista
- [ ] Calcular subtotales y total general
- [ ] Aplicar descuentos automáticamente
- [ ] Permitir modificar cantidades
- [ ] Permitir eliminar productos del carrito
- [ ] Validar disponibilidad de stock

#### Paso 3.2: API/Ajax para Carrito
- [ ] Crear `api/carrito_add.php` - Agregar producto vía AJAX
- [ ] Crear `api/carrito_update.php` - Actualizar cantidad
- [ ] Crear `api/carrito_remove.php` - Eliminar producto
- [ ] Crear `api/carrito_get.php` - Obtener contenido del carrito
- [ ] Mostrar contador de productos en el menú

---

### **FASE 4: AUTENTICACIÓN DE CLIENTE** ⏱️ Estimado: 2-3 horas

#### Paso 4.1: Login de Cliente SIN Contraseña
- [ ] Crear `cliente/login_cliente.php` - Login simplificado
- [ ] Validar solo por email o identificación
- [ ] Enviar código de verificación por email (opcional)
- [ ] Crear sesión de cliente

#### Paso 4.2: Registro Rápido
- [ ] Crear `cliente/registro.php` - Formulario de registro
- [ ] Solicitar solo datos básicos: nombre, apellido, email, teléfono
- [ ] Autocompletar dirección (opcional)
- [ ] Validar email único

#### Paso 4.3: Perfil de Cliente
- [ ] Crear `cliente/perfil.php` - Ver y editar datos personales
- [ ] Ver historial de pedidos
- [ ] Actualizar dirección de envío

---

### **FASE 5: PROCESO DE COMPRA** ⏱️ Estimado: 3-4 horas

#### Paso 5.1: Checkout
- [ ] Crear `cliente/checkout.php` - Página de finalización de compra
- [ ] Verificar login de cliente (redirigir si no está logueado)
- [ ] Mostrar resumen del carrito
- [ ] Solicitar/confirmar dirección de envío
- [ ] Seleccionar método de pago

#### Paso 5.2: Métodos de Pago
- [ ] Crear `cliente/pago.php` - Página de selección de método
- [ ] Mostrar opciones:
  - ✅ Envío gratuito
  - ✅ Carteras electrónicas (QR)
  - ✅ Débito automático
  - ✅ Efectivo contra entrega
- [ ] Implementar lógica de procesamiento (simulado)
- [ ] Guardar método seleccionado en el pedido

#### Paso 5.3: Confirmación de Pedido
- [ ] Crear `cliente/confirmar_pedido.php` - Procesar pedido
- [ ] Insertar registro en tabla `orders`
- [ ] Insertar items en tabla `order_items`
- [ ] Actualizar stock de productos
- [ ] Vaciar carrito
- [ ] Generar número de orden

---

### **FASE 6: FACTURACIÓN** ⏱️ Estimado: 2-3 horas

#### Paso 6.1: Generar Factura PDF
- [ ] Crear `cliente/factura.php?order_id=` - Generar factura en PDF
- [ ] Usar dompdf (ya está instalado)
- [ ] Incluir información del cliente
- [ ] Incluir detalle de productos
- [ ] Incluir método de pago
- [ ] Incluir total y descuentos

#### Paso 6.2: Envío por Email
- [ ] Configurar PHPMailer o función `mail()` de PHP
- [ ] Crear plantilla de email para factura
- [ ] Adjuntar PDF de factura
- [ ] Enviar automáticamente al finalizar compra
- [ ] Confirmar envío al cliente

---

### **FASE 7: DASHBOARD ADMINISTRATIVO** ⏱️ Estimado: 3-4 horas

#### Paso 7.1: Página Principal del Admin
- [ ] Rediseñar `index.php` como dashboard
- [ ] Mostrar estadísticas generales:
  - Total de ventas del mes
  - Número de pedidos
  - Productos más vendidos
  - Nuevos clientes
- [ ] Crear consultas SQL para métricas
- [ ] Implementar gráficos (Chart.js o similar)

#### Paso 7.2: Gestión de Productos Destacados
- [ ] Agregar checkbox "Destacado" en `secciones/Productos/crear.php`
- [ ] Agregar checkbox "Destacado" en `secciones/Productos/editar.php`
- [ ] Modificar listado para mostrar estado destacado
- [ ] Limitar a máximo 5 productos destacados

#### Paso 7.3: Gestión de Descuentos
- [ ] Agregar campo "Descuento (%)" en formularios de productos
- [ ] Calcular precio con descuento automáticamente
- [ ] Mostrar precio original y precio con descuento en tarjetas

---

### **FASE 8: MEJORAS Y PULIDO** ⏱️ Estimado: 2-3 horas

#### Paso 8.1: Validaciones y Seguridad
- [ ] Validar formularios del lado del cliente (JavaScript)
- [ ] Validar formularios del lado del servidor (PHP)
- [ ] Proteger contra inyección SQL (ya usando PDO preparadas ✓)
- [ ] Sanitizar entradas de usuario
- [ ] Implementar CSRF tokens en formularios

#### Paso 8.2: Diseño y UX
- [ ] Mejorar diseño de tarjetas de productos
- [ ] Agregar animaciones CSS
- [ ] Optimizar para móviles
- [ ] Agregar breadcrumbs de navegación
- [ ] Mensajes de éxito/error consistentes

#### Paso 8.3: Testing
- [ ] Probar flujo completo de compra
- [ ] Probar carrito en diferentes escenarios
- [ ] Verificar generación de PDF
- [ ] Verificar envío de emails
- [ ] Probar en diferentes navegadores

---

## 📝 RESUMEN DE TAREAS PRIORITARIAS

### 🔥 **ALTA PRIORIDAD (Core del Proyecto)**
1. ✅ Base de datos actualizada con campos necesarios
2. ✅ Frontend público con catálogo de productos
3. ✅ Sistema de carrito funcional
4. ✅ Proceso de checkout completo
5. ✅ Generación de factura

### 🟡 **MEDIA PRIORIDAD (Mejoras)**
6. ✅ Dashboard con estadísticas
7. ✅ Gestión de productos destacados
8. ✅ Envío de factura por email

### 🟢 **BAJA PRIORIDAD (Opcionales)**
9. ✅ Animaciones y efectos visuales
10. ✅ Búsqueda avanzada de productos
11. ✅ Wishlist / Lista de deseos

---

## 📊 ESTIMACIÓN TOTAL DE TIEMPO

| Fase | Descripción | Tiempo Estimado |
|------|-------------|-----------------|
| Fase 1 | Preparación y estructura | 1-2 horas |
| Fase 2 | Frontend público - catálogo | 3-4 horas |
| Fase 3 | Sistema de carrito | 3-4 horas |
| Fase 4 | Autenticación de cliente | 2-3 horas |
| Fase 5 | Proceso de compra | 3-4 horas |
| Fase 6 | Facturación | 2-3 horas |
| Fase 7 | Dashboard admin | 3-4 horas |
| Fase 8 | Mejoras y pulido | 2-3 horas |
| **TOTAL** | **Proyecto completo** | **19-27 horas** |

---

## 🎯 PRÓXIMOS PASOS RECOMENDADOS

### **COMENZAR CON:**
1. **Fase 1** - Actualizar la base de datos con los campos necesarios
2. **Fase 2** - Crear el frontend público básico
3. **Fase 3** - Implementar el carrito de compras

### **¿POR DÓNDE EMPEZAMOS?**
Te sugiero comenzar con la **Fase 1: Preparación y Estructura**, específicamente:
- Actualizar el esquema de base de datos
- Crear las carpetas para el área de cliente

**¿Estás de acuerdo con este plan? ¿Quieres que empecemos con la Fase 1?**

---

## 📌 NOTAS IMPORTANTES

- El proyecto ya tiene una base sólida del área administrativa ✅
- El enfoque principal será crear el área pública para clientes
- Se utilizará Bootstrap 5 (ya integrado)
- Se usará dompdf para PDFs (ya instalado)
- La base de datos ya tiene buena estructura, solo necesita algunos campos adicionales

---

**Fecha de creación del plan:** 27 de octubre de 2025
**Proyecto:** Bike Store - Tienda en Línea
**Tecnologías:** PHP, MySQL, Bootstrap 5, JavaScript
