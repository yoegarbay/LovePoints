# LovePoints · Contrato FE ↔ BE

Acordado entre FE y BE (27/11/2026). Los nombres de los endpoints son los del briefing
y del Excel del BE: **un archivo por endpoint dentro de `/api/`**.

## Reglas generales

- **Envío de datos**
  - `POST`: como formulario normal → el BE los lee en `$_POST`.
  - `GET`: parámetros en la URL → el BE los lee en `$_GET`.
- **Respuesta:** siempre JSON (`header('Content-Type: application/json')`).
- **Correcto:** `{ "ok": true, ... }`
- **Error:** `{ "ok": false, "error": "Texto claro para el usuario" }` + código HTTP adecuado
  (400 datos mal, 401 sin sesión, 403 sin permiso, 404 no existe, 409 duplicado).
  El FE muestra `error` tal cual a través de Freya.
- **Fechas:** `AAAA-MM-DD`. **Números:** con punto decimal (`12.5`).
- **`efecto`:** `"resta"` o `"suma"`. **`rol`:** `"usuario"` o `"superadmin"`.
- **`anulado` / `activo`:** `0` o `1`.
- Un campo opcional que llega vacío (`""`) se guarda como `NULL`.

## Autenticación

| Endpoint | Recibe | Responde |
|---|---|---|
| `POST register.php` | `nombre`, `email`, `password`, `saldo_inicial` (opcional) | `{ ok, codigo }` · **deja la sesión iniciada** |
| `POST login.php` | `email`, `password` | `{ ok }` |
| `POST logout.php` | — | `{ ok }` |
| `GET check_login.php` | — | `{ ok, sesion, tiene_pareja, usuario }` |
| `GET mi_codigo.php` | — | `{ ok, codigo }` |

- `usuario` = `{ id, nombre, email, codigo, rol }`, o `null` si no hay sesión.
- Sin sesión, `check_login.php` responde `{ "ok": true, "sesion": false, "tiene_pareja": false, "usuario": null }`.
- Contraseña: mínimo 4 caracteres y 1 mayúscula. Código: 3 letras **mayúsculas** + 4 dígitos.

## Pareja

| Endpoint | Recibe | Responde |
|---|---|---|
| `POST pareja_vincular.php` | `codigo` (p. ej. `ABC1234`) | `{ ok }` |
| `GET pareja_estado.php` | — | `{ ok, pareja }` |

- `pareja` = la **otra** persona `{ id, nombre, codigo }`, o `null`.

## Predefinidos (solo los del usuario con sesión)

| Endpoint | Recibe | Responde |
|---|---|---|
| `GET predefinidos_list.php` | — | `{ ok, predefinidos: [ { id, nombre, coste, efecto } ] }` |
| `GET predefinido_get.php` | `id` | `{ ok, predefinido }` |
| `POST predefinido_create.php` | `nombre`, `coste`, `efecto` | `{ ok, id }` |
| `POST predefinido_update.php` | `id`, `nombre`, `coste`, `efecto` | `{ ok }` |
| `POST predefinido_delete.php` | `id` | `{ ok }` |

## Eventos (de la pareja)

| Endpoint | Recibe | Responde |
|---|---|---|
| `GET eventos_list.php` | filtros (ver abajo) | `{ ok, eventos: [ evento ] }` |
| `GET evento_get.php` | `id` | `{ ok, evento }` |
| `POST evento_create.php` | `predefinido_id` (opcional), `nombre`, `coste`, `fecha`, `usuario_id`, `efecto` | `{ ok, id }` |
| `POST evento_update.php` | `id` + los mismos campos | `{ ok }` |
| `POST evento_anular.php` | `id` | `{ ok }` |

- `evento` = `{ id, usuario_id, usuario_nombre, registrado_por_id, predefinido_id, nombre, fecha, coste, efecto, anulado }`.
- `usuario_id` = quien recibe el efecto. `registrado_por_id` lo pone el BE (usuario con sesión).
- **Filtros de `eventos_list.php`** (todos opcionales):
  `usuario`, `efecto`, `desde`, `hasta`, `nombre` (contiene el texto) y `anulado`.
  - Sin `anulado` → solo eventos **no** anulados. Con `anulado=1` → **todos** (también los anulados).
  - El filtro «mes» lo convierte el FE en `desde` / `hasta`: el BE no recibe `mes`.
- Orden de la lista: da igual, la ordena el FE.

## Recargas (de la pareja)

| Endpoint | Recibe | Responde |
|---|---|---|
| `GET recargas_list.php` | — | `{ ok, recargas: [ { id, usuario_id, usuario_nombre, importe, fecha, motivo } ] }` |
| `POST recarga_create.php` | `usuario_id`, `importe`, `fecha`, `motivo` (opcional) | `{ ok, id }` |
| `POST recarga_delete.php` | `id` | `{ ok }` |

## Saldos y estadísticas (los calcula el BE)

| Endpoint | Recibe | Responde |
|---|---|---|
| `GET saldos.php` | — | `{ ok, saldos: [ { usuario_id, nombre, saldo } ] }` |
| `GET stats.php` | `granularidad` (`mes` \| `trimestre` \| `año`), `desde`, `hasta` (opcionales) | ver abajo |

Respuesta de `stats.php` (puntos netos: suma − resta, sin anulados):

```json
{
  "ok": true,
  "periodos": ["2026-09", "2026-10", "2026-11"],
  "series": [
    { "usuario_id": 1, "nombre": "Ana",  "valores": [12, -3, 20] },
    { "usuario_id": 2, "nombre": "Luis", "valores": [5, 8, -2] }
  ]
}
```

Etiquetas de `periodos`: mes `2026-11`, trimestre `2026-T4`, año `2026`.

## Administración (solo `rol = superadmin`)

| Endpoint | Recibe | Responde |
|---|---|---|
| `GET admin_usuarios_list.php` | — | `{ ok, usuarios: [ { id, nombre, email, codigo, rol, activo, pareja_nombre } ] }` |
| `GET admin_usuario_get.php` | `id` | `{ ok, usuario }` |
| `POST admin_usuario_create.php` | `nombre`, `email`, `password`, `rol` | `{ ok, id }` |
| `POST admin_usuario_update.php` | `id`, `nombre`, `email`, `rol`, `password` (vacía = no cambiar) | `{ ok }` |
| `POST admin_usuario_baja.php` | `id` | `{ ok }` |
| `POST admin_pareja_set.php` | `usuario_1_id`, `usuario_2_id` | `{ ok }` |

- `pareja_nombre`: nombre de su pareja, o `null`.
- El superadmin no tiene pareja: al entrar, el FE lo lleva a `admin.php`.
