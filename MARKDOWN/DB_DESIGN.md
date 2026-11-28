# Diseño de Base de Datos --- `db_inter2`

## 1. Resumen

La base de datos `db_inter2` está diseñada para gestionar usuarios,
parejas de usuarios, conceptos predefinidos, eventos económicos,
recargas de saldo y parámetros generales de configuración.

El modelo permite: - Registrar usuarios con credenciales, código
identificativo, rol y estado. - Asociar usuarios por parejas. - Definir
conceptos reutilizables con un coste y un efecto (`suma` o `resta`). -
Registrar eventos aplicados a usuarios, indicando quién los registró. -
Añadir recargas al saldo de un usuario. - Almacenar opciones globales de
la aplicación.

**Motor y sintaxis:** el script utiliza sintaxis de MySQL (por ejemplo,
`AUTO_INCREMENT`, `ENGINE` implícito, `USE` y `ON DELETE`). No se
especifica una versión concreta del servidor.

## 2. Diagrama entidad-relación

``` mermaid
erDiagram
    USUARIOS ||--o{ PREDEFINIDOS : "define"
    USUARIOS ||--o{ EVENTOS : "recibe"
    USUARIOS ||--o{ EVENTOS : "registra"
    PREDEFINIDOS o|--o{ EVENTOS : "sirve de base"
    USUARIOS ||--o| PAREJAS : "participa como usuario_1"
    USUARIOS ||--o| PAREJAS : "participa como usuario_2"
    USUARIOS ||--o{ RECARGAS : "recibe"

    USUARIOS {
        int id PK
        varchar nombre
        varchar email UK
        varchar password_hash
        varchar codigo UK
        varchar rol
        decimal saldo_inicial
        boolean activo
        timestamp created_at
    }
    PAREJAS {
        int id PK
        int usuario_1_id FK,UK
        int usuario_2_id FK,UK
        timestamp created_at
    }
    PREDEFINIDOS {
        int id PK
        int usuario_id FK
        varchar nombre
        decimal coste
        text efecto
    }
    EVENTOS {
        int id PK
        int usuario_id FK
        int registrado_por_id FK
        int predefinido_id FK
        varchar nombre
        datetime fecha
        decimal coste
        text efecto
        boolean anulado
        timestamp created_at
    }
    RECARGAS {
        int id PK
        int usuario_id FK
        decimal importe
        datetime fecha
        varchar motivo
    }
    CONFIGURACION {
        varchar clave PK
        text valor
    }
```

## 3. Tablas

### 3.1. `usuarios`

Almacena las cuentas de las personas que utilizan la aplicación.

  -----------------------------------------------------------------------------------
  Campo             Tipo              Restricciones / valor por     Descripción
                                      defecto                       
  ----------------- ----------------- ----------------------------- -----------------
  `id`              `INT`             PK, `AUTO_INCREMENT`          Identificador
                                                                    interno del
                                                                    usuario.

  `nombre`          `VARCHAR(100)`    `NOT NULL`                    Nombre visible.

  `email`           `VARCHAR(150)`    `NOT NULL`, `UNIQUE`          Correo
                                                                    electrónico
                                                                    único.

  `password_hash`   `VARCHAR(255)`    `NOT NULL`                    Hash de la
                                                                    contraseña; no
                                                                    debe almacenar
                                                                    texto plano.

  `codigo`          `VARCHAR(50)`     `NOT NULL`, `UNIQUE`          Código
                                                                    identificativo
                                                                    único.

  `rol`             `VARCHAR(50)`     `DEFAULT 'usuario'`           Rol de la cuenta.

  `saldo_inicial`   `DECIMAL(10,2)`   `NULL`                        Saldo inicial
                                                                    opcional.

  `activo`          `BOOLEAN`         `DEFAULT TRUE`                Indica si la
                                                                    cuenta está
                                                                    activa.

  `created_at`      `TIMESTAMP`       `DEFAULT CURRENT_TIMESTAMP`   Fecha de creación
                                                                    del registro.
  -----------------------------------------------------------------------------------

**Relaciones:** es la tabla principal. Se referencia desde `parejas`,
`predefinidos`, `eventos` (dos roles distintos) y `recargas`.

### 3.2. `parejas`

Relaciona dos usuarios en una asociación denominada pareja.

  -----------------------------------------------------------------------------------
  Campo             Tipo              Restricciones / valor por     Descripción
                                      defecto                       
  ----------------- ----------------- ----------------------------- -----------------
  `id`              `INT`             PK, `AUTO_INCREMENT`          Identificador de
                                                                    la relación.

  `usuario_1_id`    `INT`             `NOT NULL`, `UNIQUE`, FK      Primer usuario
                                                                    asociado.

  `usuario_2_id`    `INT`             `NOT NULL`, `UNIQUE`, FK      Segundo usuario
                                                                    asociado.

  `created_at`      `TIMESTAMP`       `DEFAULT CURRENT_TIMESTAMP`   Fecha de
                                                                    creación.
  -----------------------------------------------------------------------------------

**Restricciones:** - `usuario_1_id` referencia `usuarios.id`, con
`ON DELETE CASCADE`. - `usuario_2_id` referencia `usuarios.id`, con
`ON DELETE CASCADE`. - `chk_diferentes_usuarios` impide que ambos
identificadores sean iguales. - Cada columna tiene una restricción
`UNIQUE` independiente: un usuario no puede repetirse en la misma
posición en más de una fila.

**Importante:** el esquema no impide que un usuario aparezca como
`usuario_1_id` en una fila y como `usuario_2_id` en otra. Tampoco impide
duplicar la misma pareja invirtiendo el orden de los identificadores. Si
la regla de negocio exige que cada usuario pertenezca como máximo a una
pareja, hace falta validación adicional o rediseñar la restricción.

### 3.3. `predefinidos`

Guarda conceptos reutilizables que un usuario puede seleccionar al
registrar un evento.

  ------------------------------------------------------------------------
  Campo             Tipo              Restricciones /    Descripción
                                      valor por defecto  
  ----------------- ----------------- ------------------ -----------------
  `id`              `INT`             PK,                Identificador del
                                      `AUTO_INCREMENT`   concepto.

  `usuario_id`      `INT`             `NOT NULL`, FK     Usuario
                                                         propietario del
                                                         concepto.

  `nombre`          `VARCHAR(100)`    `NOT NULL`         Nombre del
                                                         concepto.

  `coste`           `DECIMAL(10,2)`   `NOT NULL`         Importe asociado.

  `efecto`          `TEXT`            Nullable           Tipo de efecto o
                                                         indicación
                                                         asociada.
  ------------------------------------------------------------------------

**Restricciones y relaciones:** - `usuario_id` referencia `usuarios.id`,
con `ON DELETE CASCADE`. - `uq_usuario_nombre` hace único el nombre
dentro de cada usuario; dos usuarios distintos sí pueden tener un
predefinido con el mismo nombre.

### 3.4. `eventos`

Registra movimientos o acciones económicas aplicadas a un usuario.

  ---------------------------------------------------------------------------------------
  Campo                 Tipo              Restricciones / valor por     Descripción
                                          defecto                       
  --------------------- ----------------- ----------------------------- -----------------
  `id`                  `INT`             PK, `AUTO_INCREMENT`          Identificador del
                                                                        evento.

  `usuario_id`          `INT`             `NOT NULL`, FK                Usuario al que se
                                                                        aplica el evento.

  `registrado_por_id`   `INT`             `NOT NULL`, FK                Usuario que
                                                                        registra el
                                                                        evento.

  `predefinido_id`      `INT`             Nullable, FK                  Concepto
                                                                        predefinido de
                                                                        origen, si
                                                                        existe.

  `nombre`              `VARCHAR(100)`    `NOT NULL`                    Nombre o
                                                                        descripción
                                                                        breve.

  `fecha`               `DATETIME`        `NOT NULL`                    Fecha y hora del
                                                                        evento.

  `coste`               `DECIMAL(10,2)`   `NOT NULL`                    Importe del
                                                                        evento.

  `efecto`              `TEXT`            Nullable                      Efecto asociado,
                                                                        por ejemplo
                                                                        `suma` o `resta`
                                                                        en los datos de
                                                                        ejemplo.

  `anulado`             `BOOLEAN`         `DEFAULT FALSE`               Marca si el
                                                                        evento está
                                                                        anulado.

  `created_at`          `TIMESTAMP`       `DEFAULT CURRENT_TIMESTAMP`   Fecha de creación
                                                                        del registro.
  ---------------------------------------------------------------------------------------

**Relaciones y borrado:** - `usuario_id` referencia `usuarios.id`, con
`ON DELETE CASCADE`. - `registrado_por_id` también referencia
`usuarios.id`, con `ON DELETE CASCADE`. - `predefinido_id` referencia
`predefinidos.id`, con `ON DELETE SET NULL`: si se elimina el
predefinido, el evento permanece y pierde esa referencia.

El evento conserva `nombre`, `coste` y `efecto` propios. Por tanto, el
registro contiene una copia de esos datos y no depende de consultar el
predefinido para conocer los valores históricos.

### 3.5. `recargas`

Registra importes añadidos al saldo de un usuario.

  -----------------------------------------------------------------------------------
  Campo             Tipo              Restricciones / valor por     Descripción
                                      defecto                       
  ----------------- ----------------- ----------------------------- -----------------
  `id`              `INT`             PK, `AUTO_INCREMENT`          Identificador de
                                                                    la recarga.

  `usuario_id`      `INT`             `NOT NULL`, FK                Usuario que
                                                                    recibe la
                                                                    recarga.

  `importe`         `DECIMAL(10,2)`   `NOT NULL`                    Importe añadido.

  `fecha`           `DATETIME`        `NOT NULL`,                   Fecha y hora de
                                      `DEFAULT CURRENT_TIMESTAMP`   la recarga.

  `motivo`          `VARCHAR(255)`    Nullable                      Motivo o
                                                                    descripción.
  -----------------------------------------------------------------------------------

`usuario_id` referencia `usuarios.id`, con `ON DELETE CASCADE`.

### 3.6. `configuracion`

Almacena parámetros globales de la aplicación como pares clave-valor.

  Campo     Tipo             Restricciones / valor por defecto   Descripción
  --------- ---------------- ----------------------------------- ------------------------------
  `clave`   `VARCHAR(100)`   PK                                  Nombre único de la opción.
  `valor`   `TEXT`           `NOT NULL`                          Valor almacenado como texto.

No tiene relaciones con otras tablas.

## 4. Relaciones y cardinalidades

  -----------------------------------------------------------------------------------
  Origen            Destino                       Cardinalidad      Significado
                                                  aproximada        
  ----------------- ----------------------------- ----------------- -----------------
  `usuarios`        `parejas.usuario_1_id`        1 a 0..1 por      Un usuario puede
                                                  columna           aparecer como
                                                                    primer miembro
                                                                    en, como máximo,
                                                                    una fila.

  `usuarios`        `parejas.usuario_2_id`        1 a 0..1 por      Un usuario puede
                                                  columna           aparecer como
                                                                    segundo miembro
                                                                    en, como máximo,
                                                                    una fila.

  `usuarios`        `predefinidos`                1 a N             Un usuario puede
                                                                    definir varios
                                                                    conceptos.

  `usuarios`        `eventos.usuario_id`          1 a N             Un usuario puede
                                                                    recibir varios
                                                                    eventos.

  `usuarios`        `eventos.registrado_por_id`   1 a N             Un usuario puede
                                                                    registrar varios
                                                                    eventos.

  `predefinidos`    `eventos`                     1 a 0..N          Un concepto puede
                                                                    utilizarse en
                                                                    varios eventos;
                                                                    un evento puede
                                                                    no tener
                                                                    predefinido.

  `usuarios`        `recargas`                    1 a N             Un usuario puede
                                                                    tener varias
                                                                    recargas.
  -----------------------------------------------------------------------------------

## 5. Reglas de integridad identificadas

El SQL define explícitamente estas reglas:

1.  El email y el código de usuario son únicos.
2.  Una pareja no puede contener dos veces el mismo usuario en sus dos
    columnas.
3.  Los nombres de predefinidos son únicos por usuario.
4.  Los registros dependientes de un usuario se eliminan en cascada al
    borrar ese usuario.
5.  Al borrar un predefinido, los eventos asociados se conservan y su
    `predefinido_id` pasa a `NULL`.
6.  Los campos obligatorios están marcados con `NOT NULL`; otros, como
    `saldo_inicial`, `efecto`, `motivo` y `predefinido_id`, admiten
    `NULL`.

## 6. Cálculo del saldo: interpretación y límites

El esquema contiene `usuarios.saldo_inicial`, eventos con `coste` y
`efecto`, y recargas con `importe`. Esto sugiere que el saldo podría
calcularse a partir de un saldo inicial, las recargas y los eventos.

Una interpretación posible ---**no implementada ni garantizada por el
SQL**--- sería:

``` text
saldo = saldo_inicial
      + suma(recargas.importe)
      + suma(eventos activos con efecto "suma")
      - suma(eventos activos con efecto "resta")
```

El script no define: - Una consulta, vista, función o trigger que
calcule el saldo. - El tratamiento de `saldo_inicial = NULL`. - Una
restricción `CHECK` que limite `efecto` a `suma` o `resta`. - Una
restricción que impida importes negativos. - Si `anulado = TRUE` debe
excluirse siempre del saldo (es la interpretación más natural, pero la
aplicación debe confirmarla).

Conviene centralizar esta regla en la lógica de negocio o en una
vista/consulta compartida para evitar resultados diferentes entre
pantallas.

## 7. Datos iniciales incluidos

El script crea la base `db_inter2`, selecciona esa base y carga datos de
demostración:

-   **Usuarios:** cuatro usuarios con rol `usuario` y un usuario con rol
    `superadmin`.
-   **Parejas:** dos relaciones, con los identificadores `(1, 2)` y
    `(3, 4)`.
-   **Predefinidos:** cuatro conceptos de ejemplo, asociados a los
    usuarios 1, 2 y 3.
-   **Eventos:** tres eventos de ejemplo; uno aparece marcado como
    anulado.
-   **Recargas:** tres recargas de ejemplo.
-   **Configuración:** `moneda_nombre = Coins`, `sistema_activo = true`
    y `version_app = 1.0.0`.

El script inserta los datos de ejemplo suponiendo que los
identificadores autogenerados comienzan en 1 y se asignan en el orden de
inserción. Si se ejecuta sobre una base que ya contiene datos, esa
suposición puede no cumplirse.

## 8. Observaciones y mejoras recomendadas

Estas son recomendaciones de diseño; **no forman parte de las
restricciones que actualmente declara el SQL**.

1.  **Restringir `efecto`:** sustituir `TEXT` por un tipo más acotado
    (`VARCHAR`) y añadir una validación para aceptar únicamente los
    valores previstos, por ejemplo `suma` y `resta`.
2.  **Validar importes:** valorar restricciones para que `coste` e
    `importe` sean mayores o iguales que cero, si esa es la regla de
    negocio.
3.  **Aclarar la unicidad de parejas:** si un usuario solo puede
    pertenecer a una pareja, independientemente de la posición,
    implementar una restricción o estructura que garantice esa
    condición.
4.  **Índices para consultas:** revisar índices en
    `eventos(usuario_id, fecha)`, `eventos(registrado_por_id)`,
    `recargas(usuario_id, fecha)` y `predefinidos(usuario_id)`. Las
    claves primarias y restricciones `UNIQUE` ya crean sus índices
    correspondientes.
5.  **Historial y borrados:** los `ON DELETE CASCADE` eliminan eventos y
    recargas cuando se borra un usuario. Si se necesita conservar
    trazabilidad financiera, considerar desactivar usuarios en lugar de
    borrarlos o definir una política de retención.
6.  **Contraseñas:** mantener únicamente hashes generados con un
    algoritmo adecuado y revisar que las credenciales de demostración no
    se reutilicen en producción.
7.  **Roles:** considerar una tabla de roles o una validación de valores
    permitidos si los roles van a crecer.
8.  **Auditoría:** si se requiere trazabilidad, añadir información sobre
    modificaciones y anulaciones, no solo la fecha de creación.
9.  **Configuración tipada:** los valores de `configuracion` son texto;
    la aplicación debe convertirlos y validarlos según el uso esperado.

## 9. Resumen de claves foráneas

  -----------------------------------------------------------------------------
  Tabla hija        Campo                 Tabla y campo       Acción al borrar
                                          referenciado        el padre
  ----------------- --------------------- ------------------- -----------------
  `parejas`         `usuario_1_id`        `usuarios.id`       `CASCADE`

  `parejas`         `usuario_2_id`        `usuarios.id`       `CASCADE`

  `predefinidos`    `usuario_id`          `usuarios.id`       `CASCADE`

  `eventos`         `usuario_id`          `usuarios.id`       `CASCADE`

  `eventos`         `registrado_por_id`   `usuarios.id`       `CASCADE`

  `eventos`         `predefinido_id`      `predefinidos.id`   `SET NULL`

  `recargas`        `usuario_id`          `usuarios.id`       `CASCADE`
  -----------------------------------------------------------------------------

## 10. Alcance del documento

Este documento describe el esquema y los datos de ejemplo presentes en
`db.sql`. No presupone funcionalidades de aplicación que no estén
expresadas en ese archivo. Las reglas señaladas como interpretación o
recomendación deben confirmarse con los requisitos funcionales antes de
implementarse.
