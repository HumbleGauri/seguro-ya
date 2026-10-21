# Seguro Ya

Seguro Ya es un sistema académico de gestión de usuarios, vehículos y pólizas de seguro automotor, con credencial digital y panel administrativo, desarrollado en PHP y MySQL.

Este README documenta exclusivamente el contenido de **Codigo Fuente**. Las funcionalidades se describen a partir del código y la documentación incluidos; esta revisión documental no incluye una ejecución completa del sistema con XAMPP.

## Índice

1. [Funcionalidades y alcance](#1-funcionalidades-y-alcance)
2. [Tecnologías y arquitectura](#2-tecnologías-y-arquitectura)
3. [Estructura del proyecto](#3-estructura-del-proyecto)
4. [Instalación y preparación del ambiente](#4-instalación-y-preparación-del-ambiente)
5. [Uso del sistema y cuentas de prueba](#5-uso-del-sistema-y-cuentas-de-prueba)
6. [Base de datos](#6-base-de-datos)
7. [Resumen de la API](#7-resumen-de-la-api)
8. [Pruebas y limitaciones](#8-pruebas-y-limitaciones)
9. [Equipo y documentación complementaria](#9-equipo-y-documentación-complementaria)

## 1. Funcionalidades y alcance

| Área | Funcionalidad incluida en el código |
|---|---|
| Registro | Formulario en tres pasos: datos del usuario, vehículo y seguro. El backend realiza el alta conjunta dentro de una transacción. |
| Acceso | Inicio de sesión con nombre de usuario y contraseña, redirección por perfil y cierre de sesión. |
| Credencial virtual | Consulta de datos personales, vehículo y póliza, con tarjeta de frente y reverso. |
| Perfil | Consulta de nombres, nombre de usuario, correo, nacimiento, fecha de registro y estado de cuenta. |
| Recuperación | Solicitud por correo, validación manual de un token y cambio de contraseña. |
| Administración | Consulta de intentos de acceso, búsqueda de usuarios por correo y listado de solicitudes de recupero. |

El registro público crea cuentas con perfil `usuario`. Los endpoints administrativos comprueban la sesión y el perfil `admin` en el servidor.

El alcance de la pantalla de credencial es **una credencial, un auto y una póliza asociada**. La base y la consulta del backend admiten varias pólizas; si la interfaz recibe más de una, muestra un mensaje y no selecciona una automáticamente.

## 2. Tecnologías y arquitectura

| Componente | Tecnología |
|---|---|
| Interfaz | HTML, CSS y JavaScript. |
| Servidor | PHP nativo, sesiones PHP y PDO para acceso a datos. |
| Persistencia | MySQL/MariaDB, tablas InnoDB y codificación `utf8mb4`. |
| Comunicación | Solicitudes `fetch`, respuestas JSON y cookie de sesión. |
| Ambiente local de esta guía | XAMPP para Windows: Apache, PHP, MariaDB y phpMyAdmin. |

El navegador carga las páginas de `Front/`. Sus scripts llaman a `../backend/api/`; PHP valida las solicitudes, consulta la base y devuelve JSON. Las pantallas privadas envían la cookie de sesión en sus consultas.

**Front y backend deben publicarse bajo el mismo origen y mantenerse como carpetas hermanas.** La guía oficial de XAMPP describe sus componentes y el uso de su servidor local: [descarga](https://www.apachefriends.org/es/download.html) y [preguntas frecuentes para Windows](https://www.apachefriends.org/es/faq_windows.html).

## 3. Estructura del proyecto

```text
Codigo Fuente/
├── README.md                     # Guía general
├── Front/
│   ├── prototipo-login.html       # Entrada al sistema
│   ├── registro.html              # Alta en tres pasos
│   ├── recuperar-password.html    # Recuperación de acceso
│   ├── usuario.html               # Credencial y perfil
│   ├── admin.html                 # Inicio administrativo
│   ├── admin-accesos.html         # Auditoría de accesos
│   ├── admin-usuarios.html        # Búsqueda por correo
│   ├── admin-recuperos.html       # Solicitudes de recuperación
│   └── credencial-virtual/        # Componente de la tarjeta
├── backend/
│   ├── api/                      # Endpoints PHP
│   ├── config/db.php             # Conexión con la base
│   ├── includes/                 # Validación, sesiones y lógica compartida
│   └── sql/                      # Esquema alternativo, migraciones y seed
├── Estructura BD/
│   └── bd_pds.sql                # Esquema y datos de evaluación de esta guía
└── Imagenes/                     # Recursos gráficos
```

El árbol resume los archivos principales; las hojas de estilo y los scripts de las pantallas permanecen dentro de `Front/`.

## 4. Instalación y preparación del ambiente

### 4.1. Requisitos

- Windows para seguir los ejemplos de esta guía.
- XAMPP con PHP 8 o superior, Apache, MariaDB y phpMyAdmin.
- Extensiones PHP `pdo_mysql` y `mbstring` habilitadas.
- Navegador con JavaScript y cookies habilitados.
- Copia completa del contenido de `Codigo Fuente`.

La extensión PHP `curl` se utiliza además en las pruebas HTTP incluidas. No es un requisito para navegar por las pantallas.

### 4.2. Descargar e instalar XAMPP

1. Ingresar a la [página oficial de descarga de XAMPP](https://www.apachefriends.org/es/download.html).
2. Descargar el instalador para Windows con PHP 8 o superior.
3. Ejecutar el instalador y mantener los componentes Apache, MySQL, PHP y phpMyAdmin.
4. Para seguir las rutas de ejemplo, usar `C:\xampp` como carpeta de instalación. Si se elige otra ubicación, adaptar las rutas de esta guía.
5. Finalizar la instalación y abrir **XAMPP Control Panel**.

XAMPP incluye MariaDB, aunque el panel identifica el módulo como **MySQL**. El panel permite iniciar y detener los servicios. [Referencia oficial](https://www.apachefriends.org/es/faq_windows.html).

### 4.3. Iniciar los servicios

1. Presionar **Start** junto a **Apache**.
2. Presionar **Start** junto a **MySQL**.
3. Comprobar en el panel que ambos módulos estén en ejecución.
4. Abrir [localhost](http://localhost/) para comprobar que Apache responde.

Los ejemplos de esta guía usan el puerto HTTP `80` y el puerto de base de datos `3306`. Si se configura otro puerto HTTP, incorporarlo a las direcciones; por ejemplo, `http://localhost:8080/seguro-ya/Front/prototipo-login.html`. Si cambia el puerto de MariaDB, ajustar también la conexión del backend. [Puertos de XAMPP](https://www.apachefriends.org/es/faq_windows.html).

### 4.4. Ubicar el proyecto en htdocs

Crear la carpeta de ejemplo `C:\xampp\htdocs\seguro-ya` y copiar **dentro de ella el contenido de Codigo Fuente**. La disposición debe quedar así:

```text
C:\xampp\htdocs\seguro-ya\
├── Front\
├── backend\
├── Estructura BD\
├── Imagenes\
└── README.md
```

`seguro-ya` es el nombre elegido para los ejemplos, no una carpeta adicional incluida en la entrega. Si se usa otro nombre, cambiar ese segmento en las URLs.

XAMPP sirve los documentos web desde `htdocs`. Mantener `Front/` y `backend/` al mismo nivel conserva las rutas relativas de las llamadas a la API. [Ubicación del contenido web en XAMPP](https://www.apachefriends.org/es/faq_windows.html).

### 4.5. Verificar PHP y sus extensiones

Para la instalación de ejemplo, abrir PowerShell y ejecutar:

```powershell
& 'C:\xampp\php\php.exe' -v
& 'C:\xampp\php\php.exe' -m
```

Comprobar PHP 8 o superior y la presencia de `pdo_mysql` y `mbstring` en el listado. Para ejecutar las pruebas HTTP, comprobar también `curl`.

Si falta una extensión, revisar la configuración de PHP desde **Config → PHP (php.ini)** en la fila de Apache. Habilitar la extensión correspondiente y reiniciar Apache después de guardar. Volver a comprobar el funcionamiento; la comprobación por consola corresponde al ejecutable indicado en el comando.

### 4.6. Crear la base e importar los datos

Esta secuencia corresponde a una **instalación nueva**:

1. Con Apache y MySQL iniciados, abrir [phpMyAdmin](http://localhost/phpmyadmin/).
2. En la página principal del servidor, abrir la pestaña **Importar**.
3. Seleccionar `Estructura BD/bd_pds.sql` dentro de la copia del proyecto.
4. Mantener el formato SQL y ejecutar la importación.
5. Comprobar la existencia de la base `tp_programacion_sistemas`, sus siete tablas y las cuentas `admin` y `usuario_prueba` en `usuarios`.

El script contiene `CREATE DATABASE`, `USE`, las tablas y las inserciones de evaluación. Para esta instalación nueva, usar ese archivo como punto de partida; no es necesario ejecutar también `backend/sql/schema.sql`.

Si ya existe una base con ese nombre, revisar su estructura y sus datos antes de importar. `CREATE TABLE IF NOT EXISTS` no actualiza una tabla existente, y las inserciones de evaluación no contienen una lógica de actualización de duplicados. No usar la reimportación como procedimiento de migración.

### 4.7. Revisar la conexión con la base

El archivo `backend/config/db.php` lee estas variables de entorno y, cuando no tienen valor, usa los siguientes predeterminados:

| Variable | Valor predeterminado | Función |
|---|---|---|
| `DB_HOST` | `127.0.0.1` | Dirección del servidor de base de datos. |
| `DB_PORT` | `3306` | Puerto de conexión. |
| `DB_NAME` | `tp_programacion_sistemas` | Nombre de la base. |
| `DB_USER` | `root` | Usuario de la base. |
| `DB_PASS` | Cadena vacía | Contraseña del usuario de la base. |

Con esos valores en el servidor local, no hace falta editar la conexión. Si difieren, configurar las variables para el proceso que ejecuta PHP o ajustar los valores predeterminados en `backend/config/db.php` para ese ambiente. Una variable establecida solo en PowerShell no configura un Apache que ya se inició desde el panel de XAMPP.

### 4.8. Abrir el sistema y comprobar la conexión

Abrir [el inicio de sesión](http://localhost/seguro-ya/Front/prototipo-login.html).

**No abrir el HTML con doble clic ni mediante `file://`.** Las pantallas necesitan acceder a PHP por HTTP y utilizar la cookie de sesión.

Para una comprobación inicial, abrir [session_check.php](http://localhost/seguro-ya/backend/api/session_check.php). Sin una sesión activa, el resultado esperado según el endpoint es:

```json
{"ok":true,"logueado":false}
```

Después, ingresar con una de las cuentas de evaluación del apartado siguiente. Las URLs anteriores suponen la carpeta `seguro-ya` y el puerto HTTP `80`.

### 4.9. Diagnóstico de problemas de instalación

| Síntoma | Qué revisar |
|---|---|
| Apache o MySQL no inicia | Leer el mensaje y los logs del módulo en XAMPP; comprobar qué proceso utiliza el puerto indicado antes de cambiar la configuración. |
| `localhost` no responde | Estado de Apache y puerto HTTP configurado. |
| La pantalla devuelve 404 | Ubicación dentro de `htdocs`, nombre de la carpeta y ruta completa a `Front/prototipo-login.html`. |
| «No se pudo conectar a la base de datos» | Estado de MySQL, importación de la base y valores de `backend/config/db.php`. |
| Error de controlador PDO o función `mb_*` inexistente | Extensiones `pdo_mysql` y `mbstring` de la instalación PHP que atiende la solicitud. |
| La importación informa un duplicado | Existencia previa de los datos de evaluación; revisar el error antes de repetir la importación. |
| «No se pudo conectar con el servidor» o respuesta sin JSON | Respuesta del endpoint en la pestaña Red del navegador y log de errores de PHP/Apache. |
| No se conserva la sesión | Cookies habilitadas y uso del mismo origen en todas las pantallas; no alternar `localhost` con `127.0.0.1` durante la prueba. |

## 5. Uso del sistema y cuentas de prueba

### Cuentas incluidas en el SQL

Estas cuentas son ficticias y están destinadas a la evaluación local:

| Nombre de usuario | Contraseña | Perfil | Correo |
|---|---|---|---|
| `admin` | `Admin123!` | Administrador | `admin@tp.com` |
| `usuario_prueba` | `Usuario123!` | Usuario | `usuario.prueba@tp.com` |

El SQL almacena las contraseñas como hashes. El usuario de prueba tiene vehículo, credencial y póliza asociados.

### Recorrido del usuario

1. Abrir `Front/prototipo-login.html` desde la URL local.
2. Ingresar con `usuario_prueba` para consultar los datos de evaluación, o abrir el registro para crear otra cuenta.
3. En el registro, completar los tres pasos: usuario, vehículo y seguro. La solicitud se envía al finalizar el último paso.
4. En el área privada, consultar la credencial y alternar entre frente y reverso.
5. Seleccionar **Mis datos** para consultar el perfil.
6. Cerrar la sesión desde el área privada.

El DNI requiere 7 u 8 dígitos, sin puntos ni espacios. La contraseña requiere un mínimo de ocho caracteres, una mayúscula, un número y un símbolo. Las coberturas admitidas en el registro son `Terceros Básico`, `Terceros Completo` y `Todo Riesgo con Franquicia`.

Para crear una cuenta de prueba adicional, usar otro nombre de usuario, correo, DNI y patente; esos campos tienen restricciones de unicidad en la base.

### Recorrido del administrador

Ingresar con `admin`. El inicio administrativo contiene accesos a:

- **Registro de accesos:** consulta de intentos de login exitosos y fallidos.
- **Buscar usuario:** búsqueda por correo electrónico.
- **Solicitudes de recupero:** consulta de usuario, correo, token, vencimiento y estado de las solicitudes.

### Evaluar la recuperación de contraseña

El backend no envía correos. En la evaluación local, la consulta administrativa permite obtener el token y trasladarlo manualmente al formulario público:

1. Abrir `Front/recuperar-password.html` y solicitar la recuperación para `usuario.prueba@tp.com`.
2. En otro perfil de navegador o en una ventana privada, iniciar sesión como `admin` y abrir **Solicitudes de recupero**. Mantener esta sesión separada de la sesión del usuario de prueba.
3. Copiar el token de la solicitud correspondiente y volver al formulario de recuperación.
4. Ingresar el token y, después de su validación, definir y confirmar la nueva contraseña.
5. Comprobar el acceso con la nueva contraseña.

La vigencia del token es de 30 minutos desde su generación, según el reloj de la base. Solicitarlo nuevamente mientras continúa sin usar y sin vencer conserva el token y su vencimiento. El cambio de contraseña consume el token.

Al completar esta prueba cambia la contraseña de `usuario_prueba`; la contraseña de la tabla anterior deja de corresponder a esa cuenta hasta que se cambie nuevamente.

## 6. Base de datos

La base se llama `tp_programacion_sistemas`. El archivo `Estructura BD/bd_pds.sql` define estas siete tablas:

| Tabla | Contenido |
|---|---|
| `usuarios` | Datos personales, identificación de acceso, hash de contraseña, perfil y estado. |
| `vehiculos` | Vehículo y propietario, incluidos patente, año, chasis y motor. |
| `credenciales` | Número de credencial y usuario titular. |
| `aseguradoras` | Catálogo de compañías. |
| `seguros` | Póliza, cobertura, vigencia y relación con usuario, credencial, vehículo y aseguradora. |
| `log_accesos` | Intentos de inicio de sesión. |
| `recuperacion_password` | Tokens de recuperación, vencimiento y marca de uso. |

Cada usuario puede tener como máximo una credencial. El modelo admite varios vehículos por usuario y varias pólizas por credencial o vehículo. Las claves foráneas de `seguros` relacionan la credencial y el vehículo con el mismo usuario.

## 7. Resumen de la API

Las rutas parten de `backend/api/` y devuelven JSON. Los contratos completos se documentan en el [README del backend](backend/README_backend.md).

| Endpoint | Método | Acceso | Finalidad |
|---|---|---|---|
| `register.php` | POST | Público | Alta conjunta de usuario, vehículo y seguro. |
| `login.php` | POST | Público | Inicio de sesión. |
| `logout.php` | POST | Sin exigir sesión activa | Cierre de sesión. |
| `session_check.php` | GET | Público | Consulta de sesión y perfil. |
| `recover_password.php` | POST | Público | Solicitud de recuperación por correo. |
| `verify_recovery_token.php` | POST | Público | Validación de correo y token. |
| `reset_password.php` | POST | Público | Cambio de contraseña con correo y token. |
| `credential.php` | GET | Con sesión | Credencial del usuario autenticado. |
| `user_profile.php` | GET | Con sesión | Datos del perfil propio. |
| `admin_access_log.php` | GET | Administrador | Registro de accesos. |
| `admin_search_user.php` | GET | Administrador | Búsqueda por correo. |
| `admin_recovery_requests.php` | GET | Administrador | Solicitudes de recuperación. |

En recuperación, «Público» indica que no se requiere una sesión iniciada; la validación y el cambio de contraseña requieren los datos y el token correspondientes.

## 8. Limitaciones

### Límites del alcance actual

- El registro y la consulta de credenciales trabajan con la base local; no consultan ni emiten pólizas mediante servicios de aseguradoras. El SQL incluye datos ficticios para la evaluación académica.
- La interfaz de credencial presenta un auto y una póliza; no incluye selección entre varias pólizas.
- El backend no envía el token por correo; su entrega se gestiona fuera de la pantalla administrativa.
- El panel administrativo ofrece consultas; no incluye acciones para activar, desbloquear o eliminar cuentas.
- El estado `vigente` es el predeterminado al registrar una póliza. El esquema no define un cambio automático de estado al llegar la fecha de vencimiento.
- Los datos y las cuentas incluidos en el SQL son ficticios y se usan para evaluación.

## 9. Equipo y documentación complementaria

| Integrante | Rol |
|---|---|
| Miguel Angel Gaurisso | PM y Frontend |
| Agustin Hernan Albornoz Wenner | Backend |
| Pablo Ale | Backend |
| Juan Pablo Hovassapian | Base de datos |
| Marcos Daniel Presentado | Frontend |

| Documento | Contenido |
|---|---|
| [Descarga oficial de XAMPP](https://www.apachefriends.org/es/download.html) | Instaladores y componentes. |
| [Guía oficial de XAMPP para Windows](https://www.apachefriends.org/es/faq_windows.html) | Servicios, directorio web y configuración local. |
