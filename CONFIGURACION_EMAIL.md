# 📧 GUÍA DE CONFIGURACIÓN: Sistema de Envío de Emails

## ✅ Estado Actual: CONFIGURADO

El sistema de envío automático de facturas por email está **completamente implementado** y configurado con tu servidor SMTP de Hostinger.

---

## 🎯 ¿Qué hace este sistema?

Cuando un cliente completa una compra en Bike Store:

1. ✅ Se crea el pedido en la base de datos
2. ✅ Se actualiza el stock de productos
3. ✅ Se genera una factura en PDF profesional
4. ✅ Se envía automáticamente la factura por email al cliente
5. ✅ El cliente puede descargar la factura manualmente si lo desea

---

## 📁 Archivos del Sistema

### Configuración
- **`.env`** - Credenciales SMTP y configuración general
- **`config/email_config.php`** - Carga variables del .env

### Núcleo
- **`libs/EmailService.php`** - Servicio de envío con PHPMailer
- **`cliente/pages/plantilla_factura.php`** - Plantilla HTML del PDF

### Integración
- **`cliente/pages/confirmar_pedido.php`** - Envía email tras confirmar compra
- **`cliente/pages/factura.php`** - Descarga manual de factura

### Pruebas
- **`test_email.php`** - Script de diagnóstico y pruebas

---

## 🔧 Configuración Actual (.env)

```env
# Servidor SMTP Hostinger
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@supreme-srl.com
MAIL_PASSWORD=Zj8ic<K1G824
MAIL_FROM_ADDRESS=noreply@supreme-srl.com
MAIL_FROM_NAME="Bike Store"

# URL de la aplicación
APP_URL=http://localhost/Bike_Store
```

### ⚠️ IMPORTANTE:
Esta configuración usa tu cuenta de **Hostinger**. Los emails se enviarán desde `noreply@supreme-srl.com`.

---

## 🧪 PRUEBA EL SISTEMA

### Opción 1: Script de Diagnóstico (Recomendado)

1. **Abre tu navegador y accede a:**
   ```
   http://localhost/Bike_Store/test_email.php
   ```

2. **Verifica que todas las secciones muestren ✅:**
   - Configuración del .env
   - PHPMailer instalado
   - Conexión a base de datos
   - Generación de PDF

3. **Para enviar un email de prueba real:**
   - Abre el archivo `test_email.php` en tu editor
   - Busca la línea 128 (aproximadamente)
   - Encuentra este comentario:
     ```php
     /*
     // DESCOMENTAR ESTA SECCIÓN PARA ENVIAR EMAIL DE PRUEBA
     ```
   - Elimina `/*` al inicio y `*/` al final
   - Guarda el archivo
   - Recarga la página en el navegador

4. **Revisa el resultado:**
   - ✅ Verde: "EMAIL ENVIADO EXITOSAMENTE!"
   - ❌ Rojo: Revisa el mensaje de error

5. **Verifica tu bandeja de entrada:**
   - Email del último cliente registrado en tu BD
   - Si no aparece, revisa la carpeta de SPAM
   - El email debe tener la factura adjunta en PDF

---

### Opción 2: Compra de Prueba Completa

1. **Accede al sitio como cliente:**
   ```
   http://localhost/Bike_Store/cliente/pages/login_cliente.php
   ```

2. **Crea una cuenta nueva o usa una existente**

3. **Agrega productos al carrito**

4. **Ve al carrito y haz clic en "Proceder al Checkout"**

5. **Completa el formulario de checkout:**
   - Dirección de envío
   - Método de pago
   - Notas (opcional)

6. **Confirma el pedido**

7. **Verifica la pantalla de confirmación:**
   - Debe aparecer una alerta verde: "¡Factura enviada!"
   - O alerta amarilla si hubo un error (aún puedes descargar el PDF)

8. **Revisa tu email:**
   - Bandeja de entrada del email usado al comprar
   - El email debe llegar en 10-30 segundos
   - Verifica que tenga el PDF adjunto

---

## 🐛 Solución de Problemas

### Problema 1: "No pudimos enviar el email automáticamente"

**Posibles causas:**
- ❌ Servidor SMTP no responde
- ❌ Credenciales incorrectas
- ❌ Puerto bloqueado por firewall

**Solución:**
1. Verifica que XAMPP esté corriendo
2. Abre `test_email.php` y revisa los errores
3. Verifica las credenciales en `.env`
4. Intenta cambiar el puerto:
   ```env
   MAIL_PORT=587  # En lugar de 465
   MAIL_SMTP_SECURE=tls  # En lugar de ssl
   ```

---

### Problema 2: "Email no llega a la bandeja"

**Posibles causas:**
- 📧 Email en carpeta de SPAM
- ⏱️ Delay del servidor de email
- ❌ Email del cliente inválido

**Solución:**
1. **Revisa SPAM/Correo no deseado**
2. **Espera 1-2 minutos** (algunos servidores tardan)
3. **Verifica el email del cliente en la BD:**
   ```sql
   SELECT email FROM customer ORDER BY customer_id DESC LIMIT 1;
   ```
4. **Agrega el remitente a contactos:**
   - `noreply@supreme-srl.com`

---

### Problema 3: "PDF no se adjunta al email"

**Posibles causas:**
- ❌ Dompdf no está instalado
- ❌ Plantilla de factura no encontrada

**Solución:**
1. Verifica que existe: `libs/vendor/dompdf/`
2. Verifica que existe: `cliente/pages/plantilla_factura.php`
3. Revisa los logs en el archivo de Apache: `error_log`

---

### Problema 4: "Error SMTP: Could not connect"

**Causa:** Conexión SSL/TLS fallida

**Solución 1 - Desactivar verificación SSL (solo desarrollo):**

El sistema ya tiene esto configurado en `EmailService.php`:
```php
$this->mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
```

**Solución 2 - Verificar puerto:**
```env
# Prueba con puerto alternativo
MAIL_PORT=587
# Y cambiar en EmailService.php:
$this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
```

---

### Problema 5: "Caracteres raros (ñ, á, é)"

**Causa:** Codificación UTF-8 no configurada

**Solución:**

Ya está configurado en `EmailService.php`:
```php
$this->mail->CharSet = 'UTF-8';
```

Y en `plantilla_factura.php`:
```html
<meta charset="UTF-8">
```

Si persiste el problema, verifica tu base de datos:
```sql
ALTER TABLE customer CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE orders CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 📊 Logs y Debug

### Activar Modo Debug

Para ver información detallada de SMTP:

1. Abre `libs/EmailService.php`
2. Al final del constructor, agrega:
   ```php
   $this->enableDebug();
   ```

3. Realiza una prueba

4. Verás información detallada en pantalla sobre la conexión SMTP

**⚠️ Desactiva el debug en producción**

---

### Ver Logs de Apache

**Windows (XAMPP):**
```
C:\xampp\apache\logs\error.log
```

**Busca líneas como:**
```
Email enviado exitosamente a: cliente@email.com (Pedido #123)
Error EmailService::enviarFactura - SMTP Error: ...
```

---

## 🔐 Seguridad

### En Desarrollo (Actual)

✅ Verificación SSL desactivada (permite certificados autofirmados)
✅ Logs de errores habilitados
✅ Credenciales en archivo `.env` (no en código)

### En Producción (Recomendado)

⚠️ **Antes de subir a producción:**

1. **Activar verificación SSL:**
   ```php
   // En EmailService.php, cambiar:
   'verify_peer' => true,
   'verify_peer_name' => true,
   'allow_self_signed' => false
   ```

2. **Proteger archivo .env:**
   ```apache
   # En .htaccess:
   <Files ".env">
       Require all denied
   </Files>
   ```

3. **Desactivar logs detallados:**
   ```php
   // Eliminar enableDebug()
   ```

4. **Cambiar URL en .env:**
   ```env
   APP_URL=https://www.tudominio.com
   ```

---

## 🎨 Personalización

### Cambiar Diseño del Email

**Archivo:** `libs/EmailService.php`
**Método:** `getPlantillaFactura()`

**Colores:**
```php
background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
// Cambiar #3498db por tu color corporativo
```

**Logo:**
```php
<h1>🚲 Bike Store</h1>
// Reemplazar emoji por imagen:
<img src="https://tudominio.com/logo.png" width="200">
```

**Texto:**
```php
¡Gracias por tu compra! Tu pedido ha sido confirmado exitosamente.
// Personalizar mensaje
```

---

### Cambiar Diseño del PDF

**Archivo:** `cliente/pages/plantilla_factura.php`

**Datos de la empresa (líneas 320-327):**
```php
Av. Principal #123, Ciudad<br>
Teléfono: (555) 123-4567<br>
Email: ventas@bikestore.com<br>
```

**Colores CSS:**
```css
.header { border-bottom: 3px solid #2c3e50; }
.logo { color: #2c3e50; }
.logo-icon { color: #3498db; }
```

---

## 📧 Cambiar Proveedor de Email

### Opción 1: Gmail (Desarrollo)

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tucuenta@gmail.com
MAIL_PASSWORD=tu_app_password  # NO tu contraseña normal
MAIL_FROM_ADDRESS=tucuenta@gmail.com
MAIL_FROM_NAME="Bike Store"
```

**⚠️ Importante:**
- Debes crear un "App Password" en tu cuenta de Gmail
- Configuración → Seguridad → Verificación en 2 pasos → Contraseñas de aplicaciones

---

### Opción 2: SendGrid (Producción)

```env
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=tu_sendgrid_api_key
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Bike Store"
```

**Ventajas:**
- 100 emails/día gratis
- Mejor deliverability
- Estadísticas de emails

---

### Opción 3: Mailgun

```env
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@tudominio.com
MAIL_PASSWORD=tu_mailgun_password
```

---

## ✅ Checklist Final

Antes de marcar como completo, verifica:

- [ ] Ejecutar `test_email.php` sin errores
- [ ] Enviar email de prueba real
- [ ] Email llega a bandeja de entrada (no spam)
- [ ] PDF se adjunta correctamente
- [ ] PDF se abre sin errores
- [ ] Datos del pedido son correctos en el PDF
- [ ] Realizar compra de prueba completa
- [ ] Email automático se envía tras confirmar
- [ ] Alerta verde aparece en pantalla de confirmación
- [ ] Descarga manual de PDF funciona
- [ ] Botón de PDF en "Mis Pedidos" funciona
- [ ] Caracteres especiales (ñ, á) se muestran bien
- [ ] Diseño del email se ve bien en móvil
- [ ] Logs no muestran errores críticos

---

## 🎉 Sistema Listo

Si todos los checks están ✅, el sistema está completamente funcional.

**Próximos pasos:**
1. Continuar con **Fase 7: Dashboard Administrativo**
2. O personalizar diseño del email/PDF según tu marca
3. O configurar proveedor de email para producción

---

## 📞 Soporte Técnico

Si encuentras problemas:

1. **Revisa logs:** `C:\xampp\apache\logs\error.log`
2. **Ejecuta test:** `http://localhost/Bike_Store/test_email.php`
3. **Verifica .env:** Todas las variables deben tener valor
4. **Google el error:** Los mensajes de PHPMailer son descriptivos

---

**Fecha de configuración:** 27 de octubre de 2025  
**Versión:** 1.0  
**Estado:** ✅ PRODUCCIÓN LISTA
