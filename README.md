# Bike Store

Breve descripción

Bike Store es una aplicación web para la venta y gestión de bicicletas y accesorios. Provee una interfaz pública para clientes (catálogo, ficha de producto, carrito y checkout), generación de facturas (HTML/PDF) y un panel administrativo con CRUD para productos, categorías, clientes y pedidos.

Características principales

- Catálogo de productos con categorías y búsquedas.
- Carrito de compras (add / update / remove) gestionado por sesión y endpoints Ajax (`api/carrito_*`).
- Proceso de checkout que crea pedidos y actualiza stock en la base de datos.
- Generación de factura en HTML y exportación a PDF (Dompdf).
- Envío de factura por correo electrónico (PHPMailer).
- Panel administrativo con CRUD para productos, categorías, clientes y pedidos (`secciones/`).

Estructura del proyecto (resumen)

- `cliente/` — vistas públicas y páginas del cliente (`catalogo.php`, `producto.php`, `carrito.php`, `checkout.php`, `factura.php`).
- `api/` — endpoints para operaciones asíncronas (carrito, pedidos, etc.).
- `secciones/` — sección administrativa con subcarpetas por módulos (`Productos/`, `Categorias/`, `orders/`, `Usuarios/`).
- `libs/` — librerías y servicios (ej. `EmailService.php`).
- `vendor/`, `gmailPHP/PHPMailer-master/` — dependencias externas (Composer, PHPMailer, Dompdf).
- `db.php`, `bd.php` — conexión a la base de datos.

Tecnologías utilizadas

- PHP (servidor)
- MySQL (base de datos)
- XAMPP (entorno local: Apache + MySQL + PHP)
- Bootstrap (CSS / JS)
- JavaScript (cliente)
- PHPMailer (envío de correos)
- Dompdf (generación de PDF)
- Composer (gestión de dependencias)

Requisitos previos

- XAMPP instalado (Windows): Apache y MySQL.
- PHP 7.x / 8.x compatible con las librerías usadas.
- Composer (opcional, para instalar dependencias PHP si se actualizan).

Instalación y puesta en marcha (local)

1. Colocar la carpeta `Bike_Store` dentro de `htdocs` de XAMPP (ej. `C:\xampp\htdocs\Bike_Store`).
2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Crear la base de datos e importar el SQL del proyecto (archivos `*.sql` en la raíz o carpeta `database/`).
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin` y ejecutar los scripts SQL necesarios.
4. Configurar la conexión a la BD en `db.php` o `bd.php` (revisar las variables de host, usuario, contraseña y nombre de BD).
5. Revisar configuración de email en `config/email_config.php` (credenciales SMTP).
6. (Opcional) Si aparecen dependencias nuevas: ejecutar `composer install` en la raíz del proyecto.
7. Abrir la aplicación en el navegador: `http://localhost/Bike_Store/`.

Notas sobre funciones avanzadas

- Generación de PDF: Dompdf puede requerir la extensión GD o configuración adicional en `php.ini` (activar `extension=gd` si es necesario). Hay un script `solucion_gd_error.php` incluido para ayuda con problemas específicos de GD.
- Envío de correos: si usas Gmail, revisa configuración de seguridad (app password o OAuth) y los parámetros en `config/email_config.php`.

Buenas prácticas y recomendaciones

- Migrar consultas a PDO con prepared statements para mejorar la seguridad.
- Usar `password_hash()` para almacenar contraseñas.
- Añadir validaciones server-side y sanitización de entradas.
- Mantener un archivo `README` actualizado con credenciales de prueba y pasos para restaurar la BD (si corresponde).

Evidencias y documentación adicional

El repositorio contiene un informe más detallado en `informe_bike_store.md` con capturas sugeridas, descripción técnica y recomendaciones. Si quieres que incluya capturas embebidas en el README o genere un PDF del informe, indícamelo y lo añado.

Contacto / siguiente paso

Si deseas, puedo:

- Insertar capturas desde `uploads/` dentro del `README.md`.
- Generar `informe_bike_store.pdf` desde `informe_bike_store.md` con Dompdf y añadirlo al repo.
- Crear un `README` con formato académico (portada, índice) para entrega.

---

Archivo generado automáticamente: `README.md`.
- `libs/` — librerías y servicios (ej. `EmailService.php`).
- `vendor/`, `gmailPHP/PHPMailer-master/` — dependencias externas (Composer, PHPMailer, Dompdf).
- `db.php`, `bd.php` — conexión a la base de datos.    
