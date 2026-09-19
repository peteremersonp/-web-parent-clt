# ParentCLT · Servicio Web

Servicio web de control parental en **Laravel 12** + **PHP >= 8.5** con **SQLite**.
Es la contraparte REST del agente de Windows (C#/.NET, repo `agente-parent-clt`).

> El agente se registra una sola vez, hace poll de su política cada ~10 minutos
> (configurable) y **evalúa la programación horaria localmente cada 30 s con su
> propio reloj**. Nunca debe re-registrarse.

---

## 1. Requisitos

- PHP >= 8.5 con las extensiones habituales de Laravel (`pdo_sqlite`, `openssl`, `mbstring`, etc.)
- Composer 2
- Nada más: la base de datos es un fichero SQLite, no hay servidor de BD.

## 2. Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

El panel quedará en `http://127.0.0.1:8000` y la API en `http://127.0.0.1:8000/api/v1`.

Para poblar/actualizar los **perfiles de bloqueo y la programación semanal de ejemplo**:

```bash
php artisan db:seed --class=ParentScheduleSeeder --force
```

El seeder es idempotente (`firstOrCreate`): puede ejecutarse varias veces sin duplicar.

## 3. Credenciales por defecto

El seeder crea un **único admin**:

| | |
|---|---|
| Email | `ADMIN_EMAIL` (por defecto `admin@parentclt.local`) |
| Password | `ADMIN_PASSWORD` (por defecto `ChangeMe_2026!`) |

Para cambiar la contraseña: edita `ADMIN_PASSWORD` en `.env` y vuelve a ejecutar:

```bash
php artisan migrate:fresh --seed
```

> **Cambia la contraseña por defecto antes de exponer el servicio.**

### Auth de admin (decisión de diseño)

Se usa el mecanismo más simple de Laravel: **sesiones autenticadas con el guard `web`**
(`Auth::attempt` + `csrf`), con un controlador `LoginController` propio
(`GET/POST /login`, `POST /logout`). No se añadió Fortify/Breeze ni ningún paquete.
Hay exactamente una fila en `users`; el seeder usa `firstOrCreate`, así que
`migrate --seed` nunca crea admins duplicados.

### Auth de dispositivos (API)

Cada dispositivo tiene un `api_token` de 80 caracteres hex (`bin2hex(random_bytes(40))`).
El middleware `auth.device-token` lee `Authorization: Bearer <token>`, carga el
dispositivo y lo inyecta en el request; devuelve `401 {"message":"unauthorized"}`
si el token no es válido. **No se usa Sanctum** para evitar dependencias.

---

## 4. API v1 (prefix `/api/v1`)

Todas las respuestas son `application/json`. Los endpoints con `Accept: application/json`
devuelven errores de validación como `422 {"message": "...","errors":{...}}`.

Errores posibles: `401` (no autorizado), `404` (no existe), `409` (conflicto).

### 4.1 `POST /api/v1/devices/register`

Llamar una única vez en la primera ejecución del agente.

```bash
curl -X POST https://TUDOMINIO/api/v1/devices/register \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{
    "machine_id": "7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c",
    "name": "PC-Sala",
    "os_version": "10.0.19045"
  }'
```

`machine_id` (uuid) | `name` (string <=255) | `os_version` (opcional, <=64).

Respuesta `200` (incluye la política inicial para arrancar en un solo round-trip):

```json
{
  "device": { "machine_id": "7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c", "name": "PC-Sala" },
  "token": "<80 chars hex>",
  "policy": {
    "version": 1,
    "dns": { "mode": "local-filter" },
    "blacklist": [],
    "schedule": [ ],
    "settings": { "poll_interval_sec": 600, "schedule_eval_sec": 30 }
  }
}
```

Si `machine_id` ya existe → `409 {"message":"already-registered"}` con header
`X-Hint: use /api/v1/policy/{machine_id} with your existing token`.
El agente **jamás** debe re-registrarse; si perdió el token, el admin lo regenera
desde el panel (`POST /devices/{id}/token`) y lo copia para reinstalarlo.

### 4.2 `GET /api/v1/policy/{machine_id}`

```bash
curl https://TUDOMINIO/api/v1/policy/7f9c1f7a-4b0e-4e6f-a1c2-3d4e5f6a7b8c \
  -H "Authorization: Bearer <token>" \
  -H 'If-None-Match: "3"'      # versión que el agente ya tiene
```

- Si `If-None-Match: "<policy_version>"` coincide con la versión actual → **304 sin body**.
- `200` con el JSON construido **desde cero en cada GET** (nada cacheado):

```json
{
  "version": 3,
  "dns": { "mode": "local-filter" },
  "blacklist": [
    { "domain": "facebook.com", "type": "exact" },
    { "domain": "*.tiktok.com", "type": "wildcard" }
  ],
  "schedule": [
    {
      "day_of_week": 1,
      "start_time": "07:00",
      "end_time": "16:00",
      "profile": {
        "name": "estudio",
        "dns_mode": "local-filter",
        "blacklist": [
          { "domain": "instagram.com", "type": "exact" }
        ]
      }
    }
  ],
  "settings": { "poll_interval_sec": 600, "schedule_eval_sec": 30 }
}
```

- `blacklist` (fallback heredado) = reglas `enabled=true` donde `device_id IS NULL`
  (globales) **o** `device_id` = este dispositivo. Solo la aplica el agente cuando
  el dispositivo **no** tiene `schedule`.
- `schedule` = ventanas efectivas del dispositivo (ver sección 5). Cuando existe,
  es **autoritativa**: el agente aplica SOLO el perfil de la ventana activa.
- `dns.mode` viene del setting `dns_mode` (`local-filter` | `off`).
- `404` si el `machine_id` no existe. `401` si el token no corresponde.

### 4.3 `POST /api/v1/heartbeat`

```bash
curl -X POST https://TUDOMINIO/api/v1/heartbeat \
  -H "Authorization: Bearer <token>" \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{
    "status": "online",
    "applied_version": 3,
    "errors": ["DNS port 53 in use"],
    "uptime_sec": 86400,
    "applied_at": "2026-09-18T10:00:00Z"
  }'
```

Validación: `status` en `online|degraded|offline` · `applied_version` int >= 0
· `errors` array de <=20 strings · `uptime_sec` int >= 0 · `applied_at` ISO8601.

Acción: actualiza `status`, `last_seen_at`, `applied_version` y guarda el payload
completo en `last_heartbeat`. Respuesta `200 {"ok": true}`.

### Throttling

Todas las rutas `/api/v1/*` llevan `throttle:60,1` (60 requests por minuto por IP/token).

---

## 5. Perfiles de bloqueo y programación horaria

Modelo de tres piezas:

```
BlacklistProfile (perfil)        ScheduleWindow (franja)          Device
├─ name / slug                   ├─ profile_id ──────────────┐    (todos heredan
├─ dns_mode: local-filter |      ├─ day_of_week (0=Dom..6)   │     la programación
│            off | block-all     ├─ start_time  "HH:mm"      │     por defecto)
└─ rules (su propia lista        ├─ end_time    "HH:mm"      │
   de dominios exact/wildcard)   └─ enabled                  │
                                            ScheduleOverride │
                                            ├─ device_id + window_id
                                            ├─ action: delete | replace
                                            └─ profile_id (para replace)
```

### Semántica de evaluación

1. El servidor emite al agente la lista de **ventanas efectivas**: la programación
   por defecto menos los overrides `delete`, y con los `replace` resueltos a su perfil.
2. El agente **evalúa con su reloj local** (cada `schedule_eval_sec`, 30 s por
   defecto) qué ventana cubre el día de la semana y la hora actual
   (`start inclusivo`, `end exclusivo`; no existen franjas que crucen la medianoche).
3. **Ventana activa** → aplica el perfil de esa ventana:
   - `local-filter` → DNS del equipo en `127.0.0.1` + bloquea la lista del perfil.
   - `block-all` → DNS en `127.0.0.1` y responde **NXDOMAIN a todas las consultas**
     (internet sin salida; lo usan los perfiles `dormir` y `castigo`).
   - `off` → restaura el DNS del equipo (sin filtro).
4. **Sin ventana activa** (hueco de la programación) → modo `off` (DNS original,
   sin bloqueos).
5. **Dispositivo sin programación** → comportamiento heredado: aplica el
   `dns.mode` + `blacklist` global del payload (compatibilidad).

### Restauración DNS segura (agente)

- Al aplicar el filtro se detecta por adaptador si el DNS venía por **DHCP**
  (registry `NameServer` vacío) o era **estático**:
  - DHCP → al restaurar se devuelve el adaptador a **automático**
    (`SetDNSServerSearchOrder(null)`), no se fijan IPs que envejecen.
  - Estático → se restauran los servidores guardados.
- La desinstalación hace `taskkill /F` del agente **antes** de restaurar el DNS
  (elimina carreras con el watchdog) y **no borra** los originales que no pudo
  restaurar (reintento posterior).
- El upstream DoH es por **IP literal** (`https://1.1.1.1/dns-query`) para evitar
  el bucle de bootstrap (el agente es el propio resolver del sistema) y el bloqueo
  SNI de DoH que hacen algunos ISPs.

### ⚠️ Zona horaria

Las franjas se evalúan con el **reloj local del dispositivo**: "07:00" es la 7 am
donde está instalado el agente. El servidor corre en UTC; esto es transparente
para dispositivos en la misma zona horaria del hogar, pero tenlo presente si
administras equipos en otras zonas.

### Perfiles sembrados (ParentScheduleSeeder)

| Perfil | Modo | Uso |
|---|---|---|
| `estudio` | local-filter | bloquea distracciones (instagram, tiktok, youtube, twitch, netflix, discord) |
| `entretenimiento` | local-filter | lista moderada |
| `sabados` / `domingos` | local-filter | listas especiales de fin de semana |
| `dormir` | **block-all** | sin internet (22:00-23:59 y 00:00-05:00 todos los días, sembrado) |
| `castigo` | **block-all** | bloqueo total; se asigna por franja u override cuando se necesite |

---

## 6. Panel web

| Ruta | Descripción |
|---|---|
| `GET/POST /login`, `POST /logout` | login/logout del admin |
| `GET /` | redirige a `/dashboard` |
| `GET /dashboard` | contadores online (<5 min), degraded, offline, never, top 10 reglas + **descarga del instalador** |
| `GET /downloads/setup` | **descarga `ParentCLT-Setup.exe`** (solo con sesión; sirve la última compilación) |
| `GET /devices` | tabla de dispositivos; regenerar token, eliminar, link a programación |
| `GET /devices/{id}` | detalle: renombrar, reglas del dispositivo, última política enviada |
| `GET /devices/{id}/schedule` | **programación del dispositivo**: vista efectiva + overrides (borrar/reemplazar franja solo para ese dispositivo) |
| `GET /blacklist` | CRUD de reglas globales (siempre activas, base heredada) |
| `GET /profiles` | **perfiles de bloqueo**: crear/editar/eliminar + su lista de dominios |
| `GET /schedules` | **programación por defecto**: franjas semanales (día + horario + perfil), crear/toggle/eliminar |
| `GET /settings` | editar settings con validación |

**Regla crítica:** cualquier cambio en reglas, settings, **perfiles o programación**
incrementa `policy_version` en todos los dispositivos. El agente detecta el cambio
en su próximo poll (`If-None-Match` deja de coincidir → `200`).

Regla global = `device_id = NULL`. Regla de dispositivo = `device_id` concreto.
Una regla `wildcard` debe tener el formato `*.dominio.com`. Los dominios se
normalizan a minúsculas y se validan a nivel de aplicación (SQLite no aplica `NULL`
en índices únicos, así que duplicados globales se detectan por código).

### Settings semilla (tabla `settings`)

| key | valor por defecto |
|---|---|
| `poll_interval_sec` | `600` (bájalo temporalmente, ej. `60`, para pruebas: el agente lo adopta en su próximo ciclo) |
| `schedule_eval_sec` | `30` (frecuencia de re-evaluación local de la programación en el agente) |
| `dns_mode` | `local-filter` |
| `upstream_doh` | `https://1.1.1.1/dns-query` |
| `fallback_dns` | `1.1.1.1` |

### Descarga del instalador

`DownloadController` sirve el exe **directamente desde la carpeta de salida de
Inno Setup** (`Installer/Output/` en el host Windows, accesible vía `/mnt/c`):
el botón del dashboard entrega siempre la última compilación sin copiar nada.
Si el archivo no existe responde 404.

> **Nota antivirus:** el instalador/agente no están firmados digitalmente; Windows
> Defender puede detectarlo como PUA y Smart App Control (Win 11) lo bloquea.
> Para pruebas: exclusiones de Defender (`C:\Program Files\ParentCLT`,
> `%ProgramData%\ParentCLT`, el exe descargado) y desactivar SAC.
> Para producción: firmar el código y reportar el falso positivo a Microsoft.

---

## 7. Producción (importante)

- **Sirve siempre por HTTPS.** El token viaja en `Authorization: Bearer` y las
  cookies de sesión deben marcarse como `Secure`.
- Si está detrás de un proxy (Nginx, Caddy, Cloudflare Tunnel), configura el
  `TRUST_PROXIES` de `.env` con las IPs del proxy (coma-separadas) y, según tu
  proxy, `SESSION_DOMAIN`/`SESSION_SECURE_COOKIE=true` si quieres cookies seguras.
- Desactiva `APP_DEBUG` (`APP_DEBUG=false`) y **cambia `ADMIN_PASSWORD`**.
- El panel usa Tailwind vía CDN (`https://cdn.tailwindcss.com`); es CSS de juguete
  para este panel. Si quieres, puedes migrarlo a Vite + Tailwind compilado, pero
  no es necesario para el funcionamiento.
- Exponer el servidor en LAN (desarrollo en WSL): `netsh interface portproxy` del
  puerto hacia la IP de WSL (`wsl hostname -I`) + regla de firewall entrante.
  La IP de WSL cambia al reiniciar: re-ejecuta el portproxy si la conexión falla.

## 8. Tests

```bash
php artisan test
```

Suite PHPUnit contra **SQLite en memoria** (`:memory:` + `RefreshDatabase`):
register (200/409/422), policy (200/304/401/404), heartbeat, bump de
`policy_version` por reglas globales, reglas por dispositivo, settings, auth admin
y descarga del instalador (guest → redirect, admin → exe válido).

## 9. Auditoría

Se registra en el log por defecto (`storage/logs/laravel.log`): registro de
dispositivos, login/logout del admin, creación/activación/borrado de reglas,
**perfiles y sus reglas, franjas de programación, overrides por dispositivo**,
cambios de settings y heartbeats **con errores**.
