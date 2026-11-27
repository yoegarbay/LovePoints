/**
 * FamilyCoinsApp - Datos de prueba (Sprint 4, tarea del SM)
 *
 * Uso:
 *   node datos_prueba.js            -> resumen de saldos por consola
 *   node datos_prueba.js --sql      -> genera datos_prueba.sql (INSERTs para MySQL)
 *   node datos_prueba.js --json     -> genera datos_prueba.json (para el FE / mocks)
 *   En navegador: <script src="datos_prueba.js"></script> y usar window.DatosPrueba
 *
 * Contraseña de TODOS los usuarios de prueba: Test1234
 * (cumple la regla: 1 mayúscula y mínimo 4 caracteres)
 * El hash es bcrypt $2y$, compatible con password_verify() de PHP.
 *
 * Regla de saldo (briefing, regla 3):
 *   saldo(u) = saldo_inicial(u) + recargas(u)
 *              - costes de eventos "resta" asignados a u
 *              + costes de eventos "suma" asignados a su pareja
 *   Los eventos anulados no cuentan.
 *
 * Casos cubiertos:
 *   - superadmin
 *   - 2 parejas con datos (Marta+Kevin, Lucía+Diego)
 *   - 1 usuario SIN pareja (Pablo) para probar vincular.php
 *   - 1 par de usuarios sin pareja (Pablo y Irene) para probar la vinculación real
 *   - 1 usuario dado de baja lógica (activo = 0)
 *   - saldo_inicial NULL, 0 y con valor
 *   - saldos negativos
 *   - eventos que restan, que suman, anulados, con y sin predefinido
 *   - eventos repartidos en 2025 y 2026 (meses, trimestres y años para las gráficas)
 */

const PASSWORD_PLANO = 'Test1234';
const PASSWORD_HASH = '$2y$10$b.xcgRH3/U13XnZjZ7rA6O7OyKk9VVu9wJ/7DFO3qR7NXxb98VJuG';

// ---------------------------------------------------------------- usuarios
const usuarios = [
  { id: 1, nombre: 'Admin',         email: 'admin@familycoinsapp.local',  codigo: 'ADM0001', rol: 'superadmin', saldo_inicial: null, activo: 1, created_at: '2025-01-05 10:00:00' },
  { id: 2, nombre: 'Marta',         email: 'marta@example.com',           codigo: 'MKF2312', rol: 'usuario',    saldo_inicial: 100,  activo: 1, created_at: '2025-01-10 18:30:00' },
  { id: 3, nombre: 'Kevin',         email: 'kevin@example.com',           codigo: 'KVN4471', rol: 'usuario',    saldo_inicial: 50,   activo: 1, created_at: '2025-01-10 19:00:00' },
  { id: 4, nombre: 'Lucía',         email: 'lucia@example.com',           codigo: 'LCA9084', rol: 'usuario',    saldo_inicial: null, activo: 1, created_at: '2025-03-02 12:15:00' },
  { id: 5, nombre: 'Diego',         email: 'diego@example.com',           codigo: 'DGO1256', rol: 'usuario',    saldo_inicial: 0,    activo: 1, created_at: '2025-03-02 12:40:00' },
  { id: 6, nombre: 'Pablo',         email: 'pablo@example.com',           codigo: 'PBL7731', rol: 'usuario',    saldo_inicial: null, activo: 1, created_at: '2026-10-20 09:00:00' },
  { id: 7, nombre: 'Irene',         email: 'irene@example.com',           codigo: 'IRN3302', rol: 'usuario',    saldo_inicial: 25,   activo: 1, created_at: '2026-10-21 09:30:00' },
  { id: 8, nombre: 'Usuario Baja',  email: 'baja@example.com',            codigo: 'BJA0000', rol: 'usuario',    saldo_inicial: null, activo: 0, created_at: '2025-02-01 08:00:00' },
].map(u => ({ ...u, password_hash: PASSWORD_HASH }));

// ----------------------------------------------------------------- parejas
// Un usuario solo puede estar en una pareja. Pablo (6) e Irene (7) quedan libres.
const parejas = [
  { id: 1, usuario_1_id: 2, usuario_2_id: 3, created_at: '2025-01-11 20:00:00' }, // Marta + Kevin
  { id: 2, usuario_1_id: 4, usuario_2_id: 5, created_at: '2025-03-03 10:00:00' }, // Lucía + Diego
];

// ------------------------------------------------------------- predefinidos
// nombre único por usuario
const predefinidos = [
  // Marta
  { id: 1,  usuario_id: 2, nombre: 'Cena fuera',        coste: 45,  efecto: 'resta' },
  { id: 2,  usuario_id: 2, nombre: 'Cine',              coste: 20,  efecto: 'resta' },
  { id: 3,  usuario_id: 2, nombre: 'Regalo aniversario', coste: 80, efecto: 'suma'  },
  // Kevin
  { id: 4,  usuario_id: 3, nombre: 'Cena fuera',        coste: 50,  efecto: 'resta' },
  { id: 5,  usuario_id: 3, nombre: 'Festival',          coste: 120, efecto: 'resta' },
  { id: 6,  usuario_id: 3, nombre: 'Escapada fin de semana', coste: 150, efecto: 'suma' },
  // Lucía
  { id: 7,  usuario_id: 4, nombre: 'Brunch',            coste: 30,  efecto: 'resta' },
  { id: 8,  usuario_id: 4, nombre: 'Concierto',         coste: 90,  efecto: 'resta' },
  // Diego
  { id: 9,  usuario_id: 5, nombre: 'Cena fuera',        coste: 60,  efecto: 'resta' },
  { id: 10, usuario_id: 5, nombre: 'Detalle sorpresa',  coste: 40,  efecto: 'suma'  },
];

// ------------------------------------------------------------------ eventos
// usuario_id = a quién se asigna el evento; registrado_por_id = quién lo creó.
// Helper: ev(usuario, registradoPor, predefinido|null, nombre, fecha, coste, efecto, anulado)
let _evId = 0;
const ev = (usuario_id, registrado_por_id, predefinido_id, nombre, fecha, coste, efecto, anulado = 0) => ({
  id: ++_evId, usuario_id, registrado_por_id, predefinido_id, nombre,
  fecha, coste, efecto, anulado, created_at: fecha + ' 21:00:00',
});

const eventos = [
  // ---- Pareja 1: Marta (2) + Kevin (3) ----
  // 2025 T1
  ev(2, 2, 1,    'Cena fuera',               '2025-01-18', 45,  'resta'),
  ev(3, 3, 4,    'Cena fuera',               '2025-01-25', 50,  'resta'),
  ev(2, 3, 2,    'Cine',                     '2025-02-08', 20,  'resta'),
  ev(3, 2, null, 'Cumpleaños Kevin',         '2025-02-14', 70,  'suma'),
  ev(2, 2, null, 'Taxi noche',               '2025-03-09', 18,  'resta'),
  // 2025 T2
  ev(3, 3, 5,    'Festival',                 '2025-04-12', 120, 'resta'),
  ev(2, 2, 3,    'Regalo aniversario',       '2025-05-20', 80,  'suma'),
  ev(2, 2, null, 'Spa',                      '2025-06-07', 65,  'resta'),
  ev(3, 3, null, 'Partido de fútbol',        '2025-06-21', 35,  'resta', 1), // anulado
  // 2025 T3
  ev(3, 3, 6,    'Escapada fin de semana',   '2025-07-19', 150, 'suma'),
  ev(2, 2, 1,    'Cena fuera',               '2025-08-02', 45,  'resta'),
  ev(3, 2, null, 'Helados',                  '2025-08-15', 12,  'resta'),
  ev(2, 3, null, 'Musical',                  '2025-09-13', 110, 'resta'),
  // 2025 T4
  ev(3, 3, 4,    'Cena fuera',               '2025-10-11', 50,  'resta'),
  ev(2, 2, 2,    'Cine',                     '2025-11-08', 20,  'resta'),
  ev(2, 3, null, 'Regalo Navidad',           '2025-12-20', 95,  'suma'),
  // 2026
  ev(3, 3, 5,    'Festival',                 '2026-02-14', 120, 'resta'),
  ev(2, 2, 1,    'Cena fuera',               '2026-03-07', 45,  'resta'),
  ev(2, 2, 3,    'Regalo aniversario',       '2026-04-12', 80,  'suma'),
  ev(3, 2, null, 'Reparación bici',          '2026-05-30', 55,  'resta', 1), // anulado
  ev(2, 3, null, 'Concierto',                '2026-06-19', 85,  'resta'),
  ev(3, 3, 6,    'Escapada fin de semana',   '2026-08-08', 150, 'suma'),
  ev(2, 2, 2,    'Cine',                     '2026-10-04', 20,  'resta'),
  ev(3, 3, 4,    'Cena fuera',               '2026-11-14', 50,  'resta'),

  // ---- Pareja 2: Lucía (4) + Diego (5) ----
  ev(4, 4, 7,    'Brunch',                   '2025-04-05', 30,  'resta'),
  ev(5, 5, 9,    'Cena fuera',               '2025-04-26', 60,  'resta'),
  ev(4, 5, 8,    'Concierto',                '2025-06-14', 90,  'resta'),
  ev(5, 4, 10,   'Detalle sorpresa',         '2025-09-01', 40,  'suma'),
  ev(4, 4, 7,    'Brunch',                   '2025-10-19', 30,  'resta'),
  ev(5, 5, null, 'Viaje a Valencia',         '2025-12-27', 200, 'resta'), // saldo de Diego queda negativo
  ev(4, 4, 8,    'Concierto',                '2026-01-24', 90,  'resta'),
  ev(5, 5, 9,    'Cena fuera',               '2026-03-14', 60,  'resta'),
  ev(5, 4, 10,   'Detalle sorpresa',         '2026-05-09', 40,  'suma', 1), // anulado
  ev(4, 4, null, 'Clase de cerámica',        '2026-07-11', 45,  'resta'),
  ev(5, 5, null, 'Cumpleaños Lucía',         '2026-09-22', 75,  'suma'),
  ev(4, 5, 7,    'Brunch',                   '2026-11-08', 30,  'resta'),
];

// ----------------------------------------------------------------- recargas
const recargas = [
  { id: 1,  usuario_id: 2, importe: 200, fecha: '2025-02-01', motivo: 'Recarga inicial San Valentín' },
  { id: 2,  usuario_id: 3, importe: 150, fecha: '2025-02-01', motivo: 'Recarga inicial' },
  { id: 3,  usuario_id: 2, importe: 100, fecha: '2025-07-01', motivo: 'Paga de verano' },
  { id: 4,  usuario_id: 3, importe: 100, fecha: '2025-07-01', motivo: 'Paga de verano' },
  { id: 5,  usuario_id: 2, importe: 120, fecha: '2026-01-02', motivo: 'Reyes' },
  { id: 6,  usuario_id: 3, importe: 60,  fecha: '2026-06-15', motivo: 'Extra por proyecto' },
  { id: 7,  usuario_id: 4, importe: 150, fecha: '2025-03-15', motivo: 'Recarga inicial' },
  { id: 8,  usuario_id: 5, importe: 100, fecha: '2025-03-15', motivo: 'Recarga inicial' },
  { id: 9,  usuario_id: 4, importe: 80,  fecha: '2026-02-01', motivo: 'Recarga trimestral' },
  { id: 10, usuario_id: 5, importe: 50,  fecha: '2026-02-01', motivo: 'Recarga trimestral' },
];

// ------------------------------------------------------------ configuracion
const configuracion = [
  { clave: 'nombre_app',         valor: 'FamilyCoinsApp' },
  { clave: 'moneda_interna',     valor: 'FamilyCoins' },
  { clave: 'formato_codigo',     valor: '^[A-Z]{3}[0-9]{4}$' },
  { clave: 'saldo_inicial_def',  valor: '0' },
];

// ---------------------------------------------------------------- utilidades
function parejaDe(usuarioId) {
  const p = parejas.find(p => p.usuario_1_id === usuarioId || p.usuario_2_id === usuarioId);
  if (!p) return null;
  return p.usuario_1_id === usuarioId ? p.usuario_2_id : p.usuario_1_id;
}

/** Saldo de un usuario según la regla 3 del briefing. */
function calcularSaldo(usuarioId) {
  const u = usuarios.find(u => u.id === usuarioId);
  const parejaId = parejaDe(usuarioId);
  let saldo = u.saldo_inicial ?? 0;
  saldo += recargas.filter(r => r.usuario_id === usuarioId).reduce((s, r) => s + r.importe, 0);
  for (const e of eventos) {
    if (e.anulado) continue;
    if (e.efecto === 'resta' && e.usuario_id === usuarioId) saldo -= e.coste;
    if (e.efecto === 'suma' && parejaId !== null && e.usuario_id === parejaId) saldo += e.coste;
  }
  return saldo;
}

/** Comprobaciones de integridad de los datos de prueba. */
function validar() {
  const errores = [];
  const codigos = new Set();
  for (const u of usuarios) {
    if (!/^[A-Z]{3}[0-9]{4}$/.test(u.codigo)) errores.push(`Código inválido: ${u.codigo}`);
    if (codigos.has(u.codigo)) errores.push(`Código duplicado: ${u.codigo}`);
    codigos.add(u.codigo);
  }
  const enPareja = new Set();
  for (const p of parejas) {
    for (const id of [p.usuario_1_id, p.usuario_2_id]) {
      if (enPareja.has(id)) errores.push(`Usuario ${id} en más de una pareja`);
      enPareja.add(id);
    }
  }
  const nombresPre = new Set();
  for (const p of predefinidos) {
    const k = `${p.usuario_id}|${p.nombre}`;
    if (nombresPre.has(k)) errores.push(`Predefinido duplicado: ${k}`);
    nombresPre.add(k);
  }
  for (const e of eventos) {
    if (!(e.coste > 0)) errores.push(`Evento ${e.id}: coste no válido`);
    if (!['resta', 'suma'].includes(e.efecto)) errores.push(`Evento ${e.id}: efecto no válido`);
  }
  for (const r of recargas) if (!(r.importe > 0)) errores.push(`Recarga ${r.id}: importe no válido`);
  return errores;
}

/** Genera INSERTs MySQL en el orden correcto por claves foráneas. */
function generarSQL() {
  const q = v => v === null || v === undefined ? 'NULL'
    : typeof v === 'number' ? String(v)
    : `'${String(v).replace(/\\/g, '\\\\').replace(/'/g, "''")}'`;
  const insertar = (tabla, filas) => {
    if (!filas.length) return '';
    const cols = Object.keys(filas[0]);
    const vals = filas.map(f => '  (' + cols.map(c => q(f[c])).join(', ') + ')').join(',\n');
    return `INSERT INTO ${tabla} (${cols.join(', ')}) VALUES\n${vals};\n`;
  };
  return [
    '-- FamilyCoinsApp: datos de prueba (generado por datos_prueba.js)',
    '-- Contraseña de todos los usuarios: Test1234',
    'SET FOREIGN_KEY_CHECKS = 0;',
    'TRUNCATE TABLE recargas; TRUNCATE TABLE eventos; TRUNCATE TABLE predefinidos;',
    'TRUNCATE TABLE parejas; TRUNCATE TABLE usuarios; TRUNCATE TABLE configuracion;',
    'SET FOREIGN_KEY_CHECKS = 1;',
    '',
    insertar('usuarios', usuarios),
    insertar('parejas', parejas),
    insertar('predefinidos', predefinidos),
    insertar('eventos', eventos),
    insertar('recargas', recargas),
    insertar('configuracion', configuracion),
  ].join('\n');
}

const DatosPrueba = {
  PASSWORD_PLANO, usuarios, parejas, predefinidos, eventos, recargas, configuracion,
  parejaDe, calcularSaldo, validar, generarSQL,
};

// ------------------------------------------------------------------ salida
if (typeof module !== 'undefined' && module.exports) {
  module.exports = DatosPrueba;

  if (require.main === module) {
    const fs = require('fs');
    const errores = validar();
    if (errores.length) {
      console.error('Errores en los datos de prueba:\n- ' + errores.join('\n- '));
      process.exit(1);
    }
    if (process.argv.includes('--sql')) {
      fs.writeFileSync('datos_prueba.sql', generarSQL());
      console.log('Generado datos_prueba.sql');
    } else if (process.argv.includes('--json')) {
      const { usuarios, parejas, predefinidos, eventos, recargas, configuracion } = DatosPrueba;
      fs.writeFileSync('datos_prueba.json', JSON.stringify({ usuarios, parejas, predefinidos, eventos, recargas, configuracion }, null, 2));
      console.log('Generado datos_prueba.json');
    } else {
      console.log('Datos válidos. Saldos calculados:\n');
      for (const u of usuarios.filter(u => u.rol === 'usuario' && u.activo)) {
        const p = parejaDe(u.id);
        console.log(`${u.nombre.padEnd(8)} (${u.codigo})  pareja: ${p ? usuarios.find(x => x.id === p).nombre : '—'}`.padEnd(42) + `saldo: ${calcularSaldo(u.id)}`);
      }
      console.log(`\n${usuarios.length} usuarios, ${parejas.length} parejas, ${predefinidos.length} predefinidos, ${eventos.length} eventos, ${recargas.length} recargas`);
    }
  }
} else if (typeof window !== 'undefined') {
  window.DatosPrueba = DatosPrueba;
}
