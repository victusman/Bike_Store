Cómo instalar Bootstrap localmente (Windows / PowerShell)

1) Crear carpetas assets si no existen:

   New-Item -ItemType Directory -Path "C:\xampp\htdocs\Bike_Store\assets\css" -Force
   New-Item -ItemType Directory -Path "C:\xampp\htdocs\Bike_Store\assets\js" -Force

2) Descargar los archivos oficiales (ejemplo con PowerShell):

   Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" -OutFile "C:\xampp\htdocs\Bike_Store\assets\css\bootstrap.min.css"
   Invoke-WebRequest -Uri "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" -OutFile "C:\xampp\htdocs\Bike_Store\assets\js\bootstrap.bundle.min.js"

3) Opcional: verificar que la web carga los archivos locales abriendo:
   http://localhost/Bike_Store/index.php

Notas:
- Si prefieres seguir usando CDN, no hace falta descargar nada.
- Si usas rutas distintas para tu proyecto, ajusta los paths en los archivos de templates.
