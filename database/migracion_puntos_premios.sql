-- =====================================================================
-- RECICLA+ | Migración: programa de PUNTOS Y PREMIOS (incentivos)
-- ---------------------------------------------------------------------
-- Aplica sobre una base ya instalada (conserva los datos existentes).
-- Uso:  mysql -uroot -h127.0.0.1 recicla < database/migracion_puntos_premios.sql
-- Es idempotente: puede ejecutarse varias veces sin duplicar información.
-- =====================================================================
SET NAMES utf8mb4;
USE recicla;

-- 1) Puntos que otorga cada material por unidad recolectada -------------
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'materiales' AND COLUMN_NAME = 'puntos_por_unidad');
SET @sql := IF(@existe = 0,
  'ALTER TABLE materiales ADD COLUMN puntos_por_unidad INT UNSIGNED NOT NULL DEFAULT 10 AFTER unidad_medida',
  'SELECT "columna puntos_por_unidad ya existe" AS aviso');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 2) Catálogo de premios -----------------------------------------------
CREATE TABLE IF NOT EXISTS premios (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(120) NOT NULL,
  descripcion       VARCHAR(300) NULL,
  puntos_requeridos INT UNSIGNED NOT NULL DEFAULT 100,
  stock             INT NOT NULL DEFAULT 0,        -- 0 = agotado
  icono             VARCHAR(40) NOT NULL DEFAULT 'fa-gift',
  imagen            VARCHAR(180) NULL,
  estado            ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_premios_nombre (nombre),
  KEY ix_premios_estado (estado),
  KEY ix_premios_puntos (puntos_requeridos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Movimientos de puntos (ganados / canjeados / ajustes) -------------
CREATE TABLE IF NOT EXISTS puntos_movimientos (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id     INT UNSIGNED NOT NULL,
  solicitud_id   INT UNSIGNED NULL,
  recoleccion_id INT UNSIGNED NULL,
  canje_id       INT UNSIGNED NULL,
  tipo           ENUM('ganados','canjeados','ajuste') NOT NULL,
  puntos         INT NOT NULL,                     -- firmado: + gana, - canjea
  descripcion    VARCHAR(200) NOT NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_pm_usuario (usuario_id, created_at),
  KEY ix_pm_recoleccion (recoleccion_id),
  KEY ix_pm_tipo (tipo),
  CONSTRAINT fk_pm_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
  CONSTRAINT fk_pm_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes (id) ON DELETE SET NULL,
  CONSTRAINT fk_pm_recoleccion FOREIGN KEY (recoleccion_id) REFERENCES recolecciones (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Canjes de premios -------------------------------------------------
CREATE TABLE IF NOT EXISTS canjes (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo        VARCHAR(14) NOT NULL,              -- CANJE-000001
  usuario_id    INT UNSIGNED NOT NULL,
  premio_id     INT UNSIGNED NOT NULL,
  puntos        INT UNSIGNED NOT NULL,
  estado        ENUM('solicitado','entregado','cancelado') NOT NULL DEFAULT 'solicitado',
  observaciones VARCHAR(300) NULL,
  atendido_por  INT UNSIGNED NULL,
  fecha_entrega DATETIME NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_canjes_codigo (codigo),
  KEY ix_canjes_usuario (usuario_id, estado),
  KEY ix_canjes_estado (estado),
  CONSTRAINT fk_canjes_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
  CONSTRAINT fk_canjes_premio FOREIGN KEY (premio_id) REFERENCES premios (id),
  CONSTRAINT fk_canjes_admin FOREIGN KEY (atendido_por) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) Parámetros del programa -------------------------------------------
INSERT INTO configuracion (clave, valor) VALUES ('puntos_bonus_recoleccion','20')
  ON DUPLICATE KEY UPDATE valor = VALUES(valor);
INSERT INTO configuracion (clave, valor) VALUES ('puntos_minimos_canje','100')
  ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- 6) Puntos por material (si aún están en el valor por defecto) --------
UPDATE materiales SET puntos_por_unidad = CASE categoria_id
    WHEN 1 THEN 10      -- papel y cartón
    WHEN 2 THEN 15      -- plásticos
    WHEN 3 THEN 8       -- vidrio
    WHEN 4 THEN 25      -- metales
    WHEN 5 THEN 20      -- electrónicos
    WHEN 6 THEN 10      -- textiles
    ELSE 12 END
WHERE puntos_por_unidad = 10;
UPDATE materiales SET puntos_por_unidad = 40 WHERE nombre = 'Cobre';
UPDATE materiales SET puntos_por_unidad = 30 WHERE nombre IN ('Latas de aluminio','Celulares en desuso');
UPDATE materiales SET puntos_por_unidad = 5  WHERE nombre = 'Madera y estibas';
UPDATE materiales SET puntos_por_unidad = 15 WHERE nombre IN ('Aceite de cocina usado','Llantas usadas');

-- 7) Catálogo inicial de premios ---------------------------------------
INSERT INTO premios (nombre, descripcion, puntos_requeridos, stock, icono) VALUES
 ('Botella reutilizable RECICLA+','Botella térmica de acero inoxidable de 500 ml con el logo del programa.',150,30,'fa-bottle-water'),
 ('Bolsa ecológica de tela','Bolsa reutilizable de tela resistente y lavable para tus compras.',250,25,'fa-bag-shopping'),
 ('Árbol sembrado a tu nombre','Sembramos un árbol nativo y te enviamos el certificado con tu nombre.',300,100,'fa-tree'),
 ('Kit de aseo ecológico','Jabón artesanal, cepillo de bambú y bolsas compostables.',400,15,'fa-spray-can-sparkles'),
 ('Saldo de datos móviles 5 GB','Recarga de 5 GB de navegación para tu línea móvil.',600,20,'fa-mobile-screen-button'),
 ('Bono de mercado $ 30.000','Bono redimible en la tienda aliada del barrio.',900,10,'fa-cart-shopping'),
 ('Audífonos inalámbricos','Audífonos Bluetooth con estuche de carga.',2500,5,'fa-headphones'),
 ('Bicicleta todo terreno','Bicicleta de 21 velocidades para tus recorridos.',5000,2,'fa-bicycle')
ON DUPLICATE KEY UPDATE puntos_requeridos = VALUES(puntos_requeridos);
