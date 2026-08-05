# 👔 Atelier Élie — Tienda de sastrería artesanal

Bienvenido a **Atelier Élie**, una plataforma e-commerce dedicada a la venta de corbatas y accesorios de alta sastrería, inspirada en la estética clásica y artesanal de los Reinos del Norte.

Este proyecto es un trabajo práctico del curso **Programación Web y PHP**. Está implementado en PHP puro, sin frameworks externos, con un enfoque modular y prioridades claras: seguridad, consistencia transaccional y una experiencia de usuario temática y fluida.

---

## 🚀 Requisitos

- Recomendado: `Docker` y `Docker Compose`.
- Alternativa: Servidor local con `Apache`/`Nginx`, `PHP 8.2+` y `MySQL 8.0+` (XAMPP, Laragon, MAMP, etc.).

---

## 🛠️ Instalación y despliegue

### Opción A — Docker Compose (recomendada)

1. Clona el repositorio y sitúate en la raíz del proyecto:

```bash
git clone <url-del-repositorio>
cd atelier-elie
```

2. Levanta los contenedores:

```bash
docker-compose up -d --build
```

3. Poblado de la base de datos

Si la base de datos no se inicializa automáticamente, importa el script SQL localizado en `bd/schema.sql` (o `bd/database.sql`) mediante `phpMyAdmin` o la CLI de MySQL.

4. Accede a la aplicación desde tu navegador:

- Tienda web: http://localhost:8080
- phpMyAdmin: http://localhost:8081

### Opción B — Instalación manual (XAMPP / servidor tradicional)

1. Copia la carpeta `www/` al directorio público de tu servidor (por ejemplo, `C:\xampp\htdocs\atelier-elie`).
2. Crea una base de datos y, si procede, importa `bd/schema.sql`.
3. Ajusta las credenciales en `www/helpers/db.php` si son distintas:

```php
$host = "127.0.0.1";
$db   = "mydatabase";
$user = "tu_usuario";
$pass = "tu_contraseña";
```

4. Abre `http://localhost:8080/index.php` (o la ruta y puerto configurados en tu servidor).

---

## Estructura del proyecto

```
atelier-elie/
├── bd/                      # Scripts SQL (schema, datos de ejemplo)
├── www/                     # Código fuente de la aplicación web
│   ├── assets/              # Imágenes y recursos estáticos
│   ├── helpers/             # Utilidades y middleware de seguridad
│   │   ├── autoload.php
│   │   ├── db.php
│   │   ├── csrf.php
│   │   ├── sanitizer.php
│   │   ├── headers.php
│   │   └── logger.php
│   ├── logs/                # Registros de errores (protegidos)
│   ├── index.php
│   ├── catalogo.php
│   ├── producto.php
│   ├── carrito.php
│   ├── checkout.php
│   ├── cuenta.php
│   ├── login.php
│   ├── registro.php
│   └── logout.php
├── docker-compose.yml
└── README.md
```

---

## Capas de seguridad (hardening)

El proyecto incorpora varias medidas alineadas con buenas prácticas OWASP:

- Protección CSRF: tokens criptográficos (`random_bytes(32)`), validación con `hash_equals()`.
- Protección anti-XSS: sanitización de entradas (`sanear_string()`) y escapado en vistas con `e()` (`htmlspecialchars`).
- Session cookie hardening: `HttpOnly`, `SameSite=Strict` y `Secure` cuando procede.
- Cabeceras de seguridad: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, y `Content-Security-Policy`.
- Transacciones atómicas (`FOR UPDATE`) para control de concurrencia y gestión de stock.
- Gestión de errores y logs: detalles técnicos ocultos al usuario; auditoría interna en `logs/app_errors.log` (protegido con `.htaccess`).

---

## Diseño y UX

- Estética: modo oscuro con paleta inspirada en tonos pizarra y acentos dorados/ámbar.
- Tipografía: serif clásica para transmitir exclusividad.
- Maquetación: responsive, adaptada a móviles y escritorios; estilo y utilidades compatibles con Tailwind CSS.

---

## Autor

Pedro Simón — Participante del curso Programación Web con PHP, estudiante de 2.º curso DAM.

---
