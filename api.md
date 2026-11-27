# API - FamilyCoinsApp

## Información general

Todas las peticiones que envían o reciben JSON deben llevar la cabecera:

`Content-Type: application/json`

Las sesiones se gestionan con cookies nativas de PHP. El Front-End debe enviar las credenciales para que la sesión se mantenga:

```javascript
fetch(url, {
    credentials: 'include'
});
```

Todas las respuestas incluyen el campo `ok` (booleano). Si `ok: false`, habrá un campo `error` con el mensaje explicativo.

---

# 1. Autenticación (`api/auth.php`)

Todas las acciones se enrutan mediante el parámetro `?accion=...`.

## 1.1. Registro

- **URL:** `api/auth.php?accion=register`
- **Método:** `POST`
- **Body (JSON):**

```json
{
  "nombre": "Ana",
  "email": "ana@email.com",
  "password": "Password123"
}
```

**Respuesta OK (201 Created):**

```json
{
  "ok": true,
  "id": 5,
  "codigo": "ABC1234"
}
```

**Errores:**
- `400`: validación fallida.
- `409`: el email ya está registrado.

## 1.2. Login

- **URL:** `api/auth.php?accion=login`
- **Método:** `POST`
- **Body (JSON):**

```json
{
  "email": "ana@email.com",
  "password": "Password123"
}
```

**Respuesta OK (200):**

```json
{
  "ok": true,
  "id": 1,
  "nombre": "Ana",
  "rol": "usuario",
  "codigo": "ABC1234",
  "tiene_pareja": true
}
```

**Errores:**
- `401`: credenciales incorrectas o usuario inactivo.

## 1.3. Logout

- **URL:** `api/auth.php?accion=logout`
- **Método:** `POST`

**Respuesta OK (200):**

```json
{
  "ok": true
}
```

## 1.4. Comprobar sesión (Check)

- **URL:** `api/auth.php?accion=check`
- **Método:** `GET`

**Respuesta OK (200) - Logueado:**

```json
{
  "logueado": true,
  "usuario": {
    "id": 1,
    "rol": "usuario",
    "nombre": "Ana"
  },
  "tiene_pareja": true
}
```

**Respuesta OK (200) - No logueado:**

```json
{
  "logueado": false,
  "usuario": null,
  "tiene_pareja": false
}
```

## 1.5. Mi código personal

- **URL:** `api/auth.php?accion=mi_codigo`
- **Método:** `GET`

**Respuesta OK (200):**

```json
{
  "ok": true,
  "codigo": "ABC1234"
}
```

**Errores:**
- `401`: no logueado.
- `404`: usuario no encontrado.

## 1.6. Vincular pareja

- **URL:** `api/auth.php?accion=vincular`
- **Método:** `POST`
- **Body (JSON):**

```json
{
  "codigo": "XYZ5678"
}
```

**Respuesta OK (200):**

```json
{
  "ok": true,
  "pareja": {
    "id": 2,
    "nombre": "Carlos"
  }
}
```

**Errores:**
- `400`: formato de código inválido.
- `404`: el código no existe.
- `409`: ya tienes pareja o el otro usuario ya la tiene.

## 1.7. Estado de la pareja

- **URL:** `api/auth.php?accion=pareja`
- **Método:** `GET`

**Respuesta OK (200):**

```json
{
  "ok": true,
  "pareja": {
    "id": 2,
    "nombre": "Carlos",
    "codigo": "XYZ5678",
    "saldo_inicial": 100.00
  }
}
```

Si no tiene pareja, el campo `pareja` devuelve `null`.

---

# 2. Aplicación (`api/app.php`)

Todas estas rutas requieren estar logueado y tener pareja vinculada. Si no se cumplen estas condiciones, devuelven `401` o `403`.

## 2.1. Predefinidos

### Listar mis predefinidos

- **Método:** `GET`
- **URL:** `api/app.php?accion=predefinidos_list`

**Respuesta:**

```json
{
  "ok": true,
  "items": [
    {
      "id": 1,
      "usuario_id": 1,
      "nombre": "Cena",
      "coste": 15.5,
      "efecto": "resta"
    }
  ]
}
```

### Obtener un predefinido

- **Método:** `GET`
- **URL:** `api/app.php?accion=predefinido_get&id=1`

**Respuesta:**

```json
{
  "ok": true,
  "item": {}
}
```

### Crear un predefinido

- **Método:** `POST`
- **URL:** `api/app.php?accion=predefinido_create`
- **Body (JSON):**

```json
{
  "nombre": "Cena",
  "coste": 15.5,
  "efecto": "resta"
}
```

**Respuesta:**

```json
{
  "ok": true,
  "id": 5
}
```

### Actualizar un predefinido

- **Método:** `POST`
- **URL:** `api/app.php?accion=predefinido_update`
- **Body (JSON):**

```json
{
  "id": 5,
  "nombre": "Cena",
  "coste": 20,
  "efecto": "suma"
}
```

**Respuesta:**

```json
{
  "ok": true
}
```

### Borrar un predefinido

- **Método:** `POST`
- **URL:** `api/app.php?accion=predefinido_delete`
- **Body (JSON):**

```json
{
  "id": 5
}
```

**Respuesta:**

```json
{
  "ok": true
}
```

## 2.2. Eventos

### Listar eventos (con filtros opcionales)

- **Método:** `GET`
- **URL:**

`api/app.php?accion=eventos_list&efecto=resta&anulado=0&nombre=cena&orden=fecha&dir=asc`

**Respuesta:**

```json
{
  "ok": true,
  "items": []
}
```

### Obtener un evento

- **Método:** `GET`
- **URL:** `api/app.php?accion=evento_get&id=1`

**Respuesta:**

```json
{
  "ok": true,
  "item": {}
}
```

### Crear un evento

- **Método:** `POST`
- **URL:** `api/app.php?accion=evento_create`
- **Body (JSON):**

```json
{
  "usuario_id": 1,
  "nombre": "Regalo",
  "fecha": "2026-11-26 20:00:00",
  "coste": 10,
  "efecto": "resta",
  "predefinido_id": null
}
```

**Respuesta:**

```json
{
  "ok": true,
  "id": 10
}
```

### Actualizar un evento

- **Método:** `POST`
- **URL:** `api/app.php?accion=evento_update`
- **Body (JSON):**

```json
{
  "id": 10,
  "usuario_id": 1,
  "nombre": "Regalo2"
}
```

Los puntos suspensivos del ejemplo original indican que pueden existir más campos.

**Respuesta:**

```json
{
  "ok": true
}
```

### Anular un evento

- **Método:** `POST`
- **URL:** `api/app.php?accion=evento_anular`
- **Body (JSON):**

```json
{
  "id": 10
}
```

**Respuesta:**

```json
{
  "ok": true
}
```

## 2.3. Recargas

### Listar recargas

- **Método:** `GET`
- **URL:** `api/app.php?accion=recargas_list`

**Respuesta:**

```json
{
  "ok": true,
  "items": []
}
```

### Crear una recarga

- **Método:** `POST`
- **URL:** `api/app.php?accion=recarga_create`
- **Body (JSON):**

```json
{
  "usuario_id": 1,
  "importe": 50,
  "motivo": "Regalo"
}
```

**Respuesta:**

```json
{
  "ok": true,
  "id": 3
}
```

### Borrar una recarga

- **Método:** `POST`
- **URL:** `api/app.php?accion=recarga_delete`
- **Body (JSON):**

```json
{
  "id": 3
}
```

**Respuesta:**

```json
{
  "ok": true
}
```

## 2.4. Saldos y estadísticas

### Saldos actuales

- **Método:** `GET`
- **URL:** `api/app.php?accion=saldos`

**Respuesta OK (200):**

```json
{
  "ok": true,
  "saldos": [
    {
      "usuario_id": 1,
      "nombre": "Ana",
      "saldo": 75.5
    },
    {
      "usuario_id": 2,
      "nombre": "Carlos",
      "saldo": 120.0
    }
  ]
}
```

### Estadísticas

- **Método:** `GET`
- **URL:** `api/app.php?accion=stats&granularidad=mes`

**Valores válidos para `granularidad`:**
- `mes`
- `trimestre`
- `año`

**Respuesta OK (200):**

```json
{
  "ok": true,
  "granularidad": "mes",
  "periodos": [
    {
      "periodo": "2026-06",
      "usuario_1": {
        "id": 1,
        "gasto": 15.0
      },
      "usuario_2": {
        "id": 2,
        "gasto": 10.0
      }
    }
  ]
}
```

---

# 3. Administración (`api/admin.php`)

Todas estas rutas requieren que el usuario logueado tenga el rol `superadmin`. Si no se cumple esta condición, devuelven `403`.

## 3.1. Listar todos los usuarios

- **Método:** `GET`
- **URL:** `api/admin.php?accion=usuarios_list`

**Respuesta:**

```json
{
  "ok": true,
  "items": [
    {
      "id": 1,
      "nombre": "Ana",
      "email": "ana@email.com",
      "rol": "usuario",
      "activo": true
    }
  ]
}
```

## 3.2. Obtener un usuario

- **Método:** `GET`
- **URL:** `api/admin.php?accion=usuario_get&id=1`

**Respuesta:**

```json
{
  "ok": true,
  "item": {}
}
```

## 3.3. Crear un usuario

- **Método:** `POST`
- **URL:** `api/admin.php?accion=usuario_create`
- **Body (JSON):**

```json
{
  "nombre": "Elena",
  "email": "elena@email.com",
  "password": "Password123",
  "rol": "usuario",
  "saldo_inicial": 10
}
```

**Respuesta:**

```json
{
  "ok": true,
  "id": 6,
  "codigo": "XYZ9999"
}
```

## 3.4. Actualizar un usuario

- **Método:** `POST`
- **URL:** `api/admin.php?accion=usuario_update`
- **Body (JSON):**

```json
{
  "id": 6,
  "nombre": "Elena2",
  "email": "elena2@email.com",
  "rol": "usuario",
  "activo": true,
  "saldo_inicial": 20
}
```

**Respuesta:**

```json
{
  "ok": true
}
```

## 3.5. Dar de baja un usuario (baja lógica)

- **Método:** `POST`
- **URL:** `api/admin.php?accion=usuario_baja`
- **Body (JSON):**

```json
{
  "id": 6
}
```

**Respuesta:**

```json
{
  "ok": true
}
```

## 3.6. Asignar o corregir pareja

- **Método:** `POST`
- **URL:** `api/admin.php?accion=pareja_set`
- **Body (JSON):**

```json
{
  "usuario_1_id": 1,
  "usuario_2_id": 2
}
```

**Respuesta:**

```json
{
  "ok": true
}
```
