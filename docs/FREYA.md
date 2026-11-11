# Freya, el asistente de LovePoints

Freya es un asistente visual (un robot con burbujas de diálogo) que vive solo en el
Front-End. **No cambia nada del Back-End**: ni endpoints, ni campos JSON, ni valores
como `resta` / `suma`. Solo decide *cómo se muestran* los mensajes al usuario.

## Qué hace

- Todo mensaje al usuario sale por Freya: errores de validación, avisos del servidor
  y confirmaciones (por ejemplo, «Evento guardado»).
- Los mensajes de error del BE (que siempre incluyen un texto claro) se muestran tal cual
  a través de ella.

## Cuándo habla

- **En formularios** (login, registro, vincular pareja, evento, recarga, predefinido):
  habla sola al enfocar un campo y explica cómo rellenarlo
  (por ejemplo: «La contraseña debe tener al menos 4 caracteres y 1 mayúscula»).
- **En el resto de la app**: solo cuando el usuario pulsa el botón «?».
- Hover, focus y blur son solo CSS, sin mensajes.

## Dónde se ve

- Abajo a la izquierda por defecto. Más adelante: movible y ocultable.
- Si el usuario la oculta, el mismo mensaje aparece junto al campo con error.
  La app debe funcionar igual sin Freya.
- Los campos con error se marcan también visualmente (borde grueso y «!»),
  no solo con el mensaje.

## Datos y almacenamiento

- La app son páginas separadas, así que el estado en memoria se pierde al cambiar de página.
  Por eso los últimos 5 mensajes (`state.mensajesFreya`) y el mensaje pendiente para la
  página siguiente (por ejemplo, tras vincular la pareja) se guardan en **sessionStorage**:
  sobreviven a redirecciones y recargas y se borran al cerrar la pestaña.
- En **localStorage** solo van las preferencias de Freya (oculta/visible y posición),
  nunca información de eventos ni importes.
- Toda lectura y escritura va en `try/catch`: la app debe funcionar igual si el
  almacenamiento falla (por ejemplo, en una ventana privada).

## Estado

- Sprint 1: wireframes con burbujas y ayudas de ejemplo (`docs/wireframes`) y huecos
  `<aside id="freya">` en las páginas.
- Pendiente: implementación (`decirFreya()` en `app.js`), estilos (Sprint 2) y textos de ayuda.
