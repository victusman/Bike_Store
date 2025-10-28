# 📧 SISTEMA DE ENVÍO AUTOMÁTICO DE FACTURAS

## Descripción
Sistema implementado para enviar automáticamente las facturas PDF por correo electrónico a los clientes cuando generan o descargan una factura.

## Funcionamiento

### 1. Flujo Automático
Cuando un cliente accede a `factura.php?order_id=X`:

1. **Se genera el PDF** usando Dompdf con la plantilla profesional
2. **Se obtiene el contenido del PDF** en memoria 
3. **Se busca el email del cliente** logueado en la sesión
4. **Se envía automáticamente** el PDF adjunto por email
5. **Se muestra el PDF** en el navegador como siempre
6. **Se notifica al usuario** que el email fue enviado

### 2. Componentes del Sistema

#### EmailService.php
- **Ubicación**: `libs/EmailService.php`
- **Función**: Servicio completo de envío de emails usando PHPMailer
- **Características**:
  - Plantilla HTML profesional para facturas
  - Adjunto de PDF automático
  - Configuración SMTP desde .env
  - Manejo de errores robusto

#### Configuración Email
- **Archivo**: `.env`
- **Variables necesarias**:
  ```
  MAIL_HOST=smtp.hostinger.com
  MAIL_PORT=465
  MAIL_USERNAME=noreply@supreme-srl.com
  MAIL_PASSWORD=Zj8ic<K1G824
  MAIL_FROM_ADDRESS=noreply@supreme-srl.com
  MAIL_FROM_NAME="Bike Store"
  ```

#### Integración en factura.php
- **Ubicación**: `cliente/pages/factura.php`
- **Modificaciones**:
  - Captura del contenido PDF antes del stream
  - Obtención de datos del cliente logueado
  - Envío automático con EmailService
  - Notificación visual de éxito/error

### 3. Plantilla de Email

El email enviado incluye:
- **Header atractivo** con logo de Bike Store
- **Información del pedido**: número, fecha, total, productos
- **Mensaje personalizado** con nombre del cliente
- **Próximos pasos** del proceso de envío
- **Botón CTA** para ver el pedido online
- **Footer profesional** con datos de contacto
- **PDF adjunto** con la factura completa

### 4. Características de Seguridad

- ✅ **Validación de email**: Verifica que el email sea válido
- ✅ **Sesión requerida**: Solo envía a clientes autenticados
- ✅ **Manejo de errores**: Log detallado de errores
- ✅ **Configuración segura**: SMTP con SSL/TLS
- ✅ **No exposición de datos**: No muestra errores sensibles al usuario

### 5. Testing y Debugging

#### Archivo de Prueba
- **Ubicación**: `test_email_factura.php`
- **Uso**: Verificar configuración y envío de emails
- **Características**:
  - Verifica configuración SMTP
  - Simula envío de factura
  - Muestra errores detallados
  - PDF de prueba incluido

#### Logs del Sistema
- **PHP Error Log**: Todos los eventos se registran
- **Formato de logs**:
  ```
  ✅ Factura enviada por email a: cliente@email.com (Pedido #1761657009)
  ❌ Error al enviar factura por email a: cliente@email.com (Pedido #1761657009)
  ⚠️ No se pudo enviar factura por email - Cliente sin email válido
  ```

### 6. Flujo de Usuario

1. **Cliente hace pedido** y confirma pago
2. **Cliente hace clic** en "Descargar Factura PDF"
3. **Sistema genera PDF** con logo y datos
4. **Sistema envía email** automáticamente al cliente
5. **Cliente ve PDF** en navegador
6. **Cliente recibe notificación** verde de éxito
7. **Cliente recibe email** con factura adjunta

### 7. Mantenimiento

#### Verificar Configuración
```bash
# Verificar que el archivo .env tiene todas las variables
grep -E "MAIL_" .env

# Probar envío de email básico  
http://localhost/Bike_Store/test_email.php

# Probar envío de factura
http://localhost/Bike_Store/test_email_factura.php
```

#### Solución de Problemas Comunes

**Email no se envía:**
- Verificar credenciales SMTP en .env
- Verificar que el puerto 465 esté abierto
- Revisar logs de PHP para errores detallados

**PDF no se adjunta:**
- Verificar que Dompdf genera PDF correctamente
- Revisar permisos de archivos y directorios
- Verificar que el cliente esté logueado

**Cliente no recibe email:**
- Verificar que el email del cliente sea válido
- Revisar carpeta de spam
- Verificar configuración SMTP del servidor

### 8. Próximas Mejoras

- [ ] Sistema de templates de email personalizables
- [ ] Envío de email de confirmación de pedido
- [ ] Notificaciones de estado de envío
- [ ] Sistema de reenvío de facturas
- [ ] Dashboard de emails enviados

---

## 🚀 Estado: IMPLEMENTADO Y FUNCIONAL

**Fecha de implementación**: 28/10/2025  
**Versión**: 1.0  
**Desarrollador**: Sistema automatizado de Bike Store  

**Resultado**: Los clientes ahora reciben automáticamente sus facturas por email cuando las generan, mejorando significativamente la experiencia de usuario y el profesionalismo del sistema.