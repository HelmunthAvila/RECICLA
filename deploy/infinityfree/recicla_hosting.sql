-- =====================================================================
-- RECICLA+ | Esquema + datos semilla (17 tablas)
-- ---------------------------------------------------------------------
-- Versión para HOSTING (InfinityFree / cPanel): NO crea ni selecciona la
-- base de datos, porque el hosting ya la entrega creada.
-- Importar desde phpMyAdmin ENTERANDO a la base de datos del hosting.
-- =====================================================================
-- =====================================================================
-- RECICLA+  Sistema Web de Gestion de Reciclaje
-- Base de datos: recicla   (MySQL 8.x  /  utf8mb4)
-- Generado para WAMP (C:\wamp64\www\RECICLA)
-- Uso:  mysql -uroot -h127.0.0.1 < database/recicla.sql
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


-- ---------------------------------------------------------------------
-- 1. ROLES
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
  id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(30)      NOT NULL,
  descripcion VARCHAR(120)     NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 2. EMPRESAS OPERADORAS
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS empresas;
CREATE TABLE empresas (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(120) NOT NULL,
  nit         VARCHAR(20)  NOT NULL,
  responsable VARCHAR(120) NOT NULL,
  telefono    VARCHAR(20)  NULL,
  email       VARCHAR(120) NULL,
  direccion   VARCHAR(160) NULL,
  ciudad      VARCHAR(60)  NOT NULL,
  estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_empresas_nit (nit),
  KEY ix_empresas_ciudad (ciudad),
  KEY ix_empresas_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 3. USUARIOS  (ciudadano / empresa / administrador)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  rol_id              TINYINT UNSIGNED NOT NULL,
  empresa_id          INT UNSIGNED NULL,          -- solo rol empresa
  nombres             VARCHAR(80)  NOT NULL,
  apellidos           VARCHAR(80)  NOT NULL,
  documento           VARCHAR(20)  NOT NULL,
  telefono            VARCHAR(20)  NULL,
  email               VARCHAR(120) NOT NULL,
  password            VARCHAR(255) NOT NULL,      -- password_hash() bcrypt
  direccion           VARCHAR(160) NULL,
  barrio              VARCHAR(60)  NULL,
  ciudad              VARCHAR(60)  NULL,
  estado              ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  token_recuperacion  VARCHAR(64)  NULL,
  token_expira        DATETIME     NULL,
  ultimo_acceso       DATETIME     NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_usuarios_email (email),
  UNIQUE KEY uk_usuarios_documento (documento),
  KEY ix_usuarios_rol (rol_id),
  KEY ix_usuarios_empresa (empresa_id),
  KEY ix_usuarios_ciudad_barrio (ciudad, barrio),
  CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles (id),
  CONSTRAINT fk_usuarios_empresa FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 4. CATEGORIAS
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS categorias;
CREATE TABLE categorias (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(60) NOT NULL,
  descripcion VARCHAR(200) NULL,
  icono       VARCHAR(40) NOT NULL DEFAULT 'fa-recycle',
  color       VARCHAR(20) NOT NULL DEFAULT '#2e8800',
  estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (id),
  UNIQUE KEY uk_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 5. MATERIALES
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS materiales;
CREATE TABLE materiales (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  categoria_id        INT UNSIGNED NOT NULL,
  nombre              VARCHAR(90)  NOT NULL,
  descripcion         VARCHAR(400) NULL,
  recomendaciones     TEXT         NULL,
  condiciones_entrega VARCHAR(400) NULL,
  unidad_medida       ENUM('kg','unidad','bulto','caja','litro','par') NOT NULL DEFAULT 'kg',
  puntos_por_unidad   INT UNSIGNED NOT NULL DEFAULT 10,   -- puntos que otorga por unidad recolectada
  imagen              VARCHAR(180) NULL,       -- ruta en /uploads/materiales
  icono               VARCHAR(40)  NOT NULL DEFAULT 'fa-box',
  estado              ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_materiales_nombre (nombre),
  KEY ix_materiales_categoria (categoria_id),
  KEY ix_materiales_estado (estado),
  CONSTRAINT fk_materiales_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 6. VEHICULOS
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS vehiculos;
CREATE TABLE vehiculos (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id INT UNSIGNED NOT NULL,
  placa     VARCHAR(10)  NOT NULL,
  tipo      VARCHAR(40)  NOT NULL,
  capacidad VARCHAR(40)  NULL,
  estado    ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (id),
  UNIQUE KEY uk_vehiculos_placa (placa),
  KEY ix_vehiculos_empresa (empresa_id),
  CONSTRAINT fk_vehiculos_empresa FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 7. RUTAS (organizacion de recolecciones por zona/barrio/fecha)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS rutas;
CREATE TABLE rutas (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  empresa_id INT UNSIGNED NOT NULL,
  nombre     VARCHAR(90) NOT NULL,
  zona       VARCHAR(60) NULL,
  ciudad     VARCHAR(60) NOT NULL,
  fecha      DATE        NOT NULL,
  estado     ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  PRIMARY KEY (id),
  KEY ix_rutas_empresa_fecha (empresa_id, fecha),
  KEY ix_rutas_ciudad (ciudad),
  CONSTRAINT fk_rutas_empresa FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 8. SOLICITUDES
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS solicitudes;
CREATE TABLE solicitudes (
  id                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  numero               VARCHAR(12)  NOT NULL,          -- REC-000001
  ciudadano_id         INT UNSIGNED NOT NULL,
  empresa_id           INT UNSIGNED NULL,
  empresa_usuario_id   INT UNSIGNED NULL,              -- usuario que acepto
  direccion            VARCHAR(160) NOT NULL,
  barrio               VARCHAR(60)  NOT NULL,
  ciudad               VARCHAR(60)  NOT NULL,
  fecha_disponible     DATE         NOT NULL,
  horario_disponible   ENUM('manana','tarde','noche','indiferente') NOT NULL DEFAULT 'indiferente',
  observaciones        VARCHAR(400) NULL,
  estado               ENUM('REGISTRADA','EN_REVISION','ACEPTADA','PROGRAMADA','EN_RUTA','RECOLECTADA','CANCELADA')
                       NOT NULL DEFAULT 'REGISTRADA',
  fecha_aceptacion     DATETIME NULL,
  fecha_programada     DATE     NULL,
  hora_programada      TIME     NULL,
  operador             VARCHAR(120) NULL,
  vehiculo_id          INT UNSIGNED NULL,
  ruta_id              INT UNSIGNED NULL,
  observaciones_empresa VARCHAR(400) NULL,
  created_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_solicitudes_numero (numero),
  KEY ix_solicitudes_ciudadano (ciudadano_id),
  KEY ix_solicitudes_empresa_estado (empresa_id, estado),
  KEY ix_solicitudes_estado (estado),
  KEY ix_solicitudes_geo (ciudad, barrio),
  KEY ix_solicitudes_fecha (fecha_disponible),
  KEY ix_solicitudes_programada (fecha_programada),
  CONSTRAINT fk_solicitudes_ciudadano FOREIGN KEY (ciudadano_id) REFERENCES usuarios (id),
  CONSTRAINT fk_solicitudes_empresa FOREIGN KEY (empresa_id) REFERENCES empresas (id) ON DELETE SET NULL,
  CONSTRAINT fk_solicitudes_empresa_usuario FOREIGN KEY (empresa_usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL,
  CONSTRAINT fk_solicitudes_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id) ON DELETE SET NULL,
  CONSTRAINT fk_solicitudes_ruta FOREIGN KEY (ruta_id) REFERENCES rutas (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 9. SOLICITUD_MATERIALES  (detalle de material publicado / solicitado)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS solicitud_materiales;
CREATE TABLE solicitud_materiales (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id  INT UNSIGNED NOT NULL,
  material_id   INT UNSIGNED NOT NULL,
  cantidad      DECIMAL(10,2) NOT NULL DEFAULT 1,
  unidad        ENUM('kg','unidad','bulto','caja','litro','par') NOT NULL DEFAULT 'kg',
  descripcion   VARCHAR(300) NULL,
  observaciones VARCHAR(300) NULL,
  foto          VARCHAR(180) NULL,        -- ruta en /uploads/solicitudes
  PRIMARY KEY (id),
  KEY ix_sm_solicitud (solicitud_id),
  KEY ix_sm_material (material_id),
  CONSTRAINT fk_sm_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes (id) ON DELETE CASCADE,
  CONSTRAINT fk_sm_material FOREIGN KEY (material_id) REFERENCES materiales (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 10. RECOLECCIONES
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS recolecciones;
CREATE TABLE recolecciones (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id   INT UNSIGNED NOT NULL,
  empresa_id     INT UNSIGNED NOT NULL,
  usuario_id     INT UNSIGNED NOT NULL,
  vehiculo_id    INT UNSIGNED NULL,
  ruta_id        INT UNSIGNED NULL,
  fecha          DATE NOT NULL,
  hora           TIME NOT NULL,
  observaciones  VARCHAR(400) NULL,
  evidencia      VARCHAR(180) NULL,      -- ruta en /uploads/recolecciones
  peso_total     DECIMAL(10,2) NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_recolecciones_solicitud (solicitud_id),
  KEY ix_recolecciones_empresa_fecha (empresa_id, fecha),
  KEY ix_recolecciones_material_fecha (fecha),
  CONSTRAINT fk_rec_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes (id) ON DELETE CASCADE,
  CONSTRAINT fk_rec_empresa FOREIGN KEY (empresa_id) REFERENCES empresas (id),
  CONSTRAINT fk_rec_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
  CONSTRAINT fk_rec_vehiculo FOREIGN KEY (vehiculo_id) REFERENCES vehiculos (id) ON DELETE SET NULL,
  CONSTRAINT fk_rec_ruta FOREIGN KEY (ruta_id) REFERENCES rutas (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 11. RECOLECCION_MATERIALES  (cantidades realmente recolectadas)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS recoleccion_materiales;
CREATE TABLE recoleccion_materiales (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  recoleccion_id  INT UNSIGNED NOT NULL,
  material_id     INT UNSIGNED NOT NULL,
  cantidad        DECIMAL(10,2) NOT NULL DEFAULT 0,
  unidad          ENUM('kg','unidad','bulto','caja','litro','par') NOT NULL DEFAULT 'kg',
  PRIMARY KEY (id),
  KEY ix_rm_recoleccion (recoleccion_id),
  KEY ix_rm_material (material_id),
  CONSTRAINT fk_rm_recoleccion FOREIGN KEY (recoleccion_id) REFERENCES recolecciones (id) ON DELETE CASCADE,
  CONSTRAINT fk_rm_material FOREIGN KEY (material_id) REFERENCES materiales (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 12. HISTORIAL_SOLICITUDES  (trazabilidad de estados)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS historial_solicitudes;
CREATE TABLE historial_solicitudes (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  solicitud_id    INT UNSIGNED NOT NULL,
  usuario_id      INT UNSIGNED NULL,
  estado_anterior VARCHAR(20) NULL,
  estado_nuevo    VARCHAR(20) NOT NULL,
  observacion     VARCHAR(300) NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_hs_solicitud (solicitud_id, created_at),
  CONSTRAINT fk_hs_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes (id) ON DELETE CASCADE,
  CONSTRAINT fk_hs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 13. NOTIFICACIONES
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS notificaciones;
CREATE TABLE notificaciones (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  titulo     VARCHAR(120) NOT NULL,
  mensaje    VARCHAR(300) NOT NULL,
  url        VARCHAR(200) NULL,
  leida      TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_notif_usuario (usuario_id, leida),
  CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 14. CONFIGURACION
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS configuracion;
CREATE TABLE configuracion (
  clave  VARCHAR(40)  NOT NULL,
  valor  VARCHAR(255) NOT NULL,
  PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 15. PREMIOS (programa de incentivos: los puntos se cambian por premios)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS premios;
CREATE TABLE premios (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(120) NOT NULL,
  descripcion       VARCHAR(300) NULL,
  puntos_requeridos INT UNSIGNED NOT NULL DEFAULT 100,
  stock             INT NOT NULL DEFAULT 0,
  icono             VARCHAR(40) NOT NULL DEFAULT 'fa-gift',
  imagen            VARCHAR(180) NULL,
  estado            ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_premios_nombre (nombre),
  KEY ix_premios_estado (estado),
  KEY ix_premios_puntos (puntos_requeridos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- 16. PUNTOS_MOVIMIENTOS (historial de puntos ganados y canjeados)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS puntos_movimientos;
CREATE TABLE puntos_movimientos (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id     INT UNSIGNED NOT NULL,
  solicitud_id   INT UNSIGNED NULL,
  recoleccion_id INT UNSIGNED NULL,
  canje_id       INT UNSIGNED NULL,
  tipo           ENUM('ganados','canjeados','ajuste') NOT NULL,
  puntos         INT NOT NULL,
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

-- ---------------------------------------------------------------------
-- 17. CANJES (solicitudes de premios hechas por los ciudadanos)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS canjes;
CREATE TABLE canjes (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo        VARCHAR(14) NOT NULL,
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

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- DATOS SEMILLA
-- =====================================================================
INSERT INTO roles (id, nombre, descripcion) VALUES
 (1,'ciudadano','Persona que entrega materiales reciclables'),
 (2,'empresa','Empresa operadora que realiza las recolecciones'),
 (3,'administrador','Administra usuarios, materiales, empresas y reportes');

INSERT INTO configuracion (clave, valor) VALUES
 ('nombre_sistema','RECICLA+'),
 ('puntos_bonus_recoleccion','20'),
 ('puntos_minimos_canje','100'),
 ('email_contacto','contacto@recicla.local'),
 ('telefono_contacto','601 000 0000'),
 ('horario_atencion','Lunes a sabado 7:00 a.m. - 5:00 p.m.'),
 ('max_kg_solicitud','500'),
 ('version','1.0');

INSERT INTO categorias (id, nombre, descripcion, icono, color) VALUES
 (1,'Papel y cartón','Papel, cartón y derivados de fibra vegetal.','fa-scroll','#8d6e63'),
 (2,'Plásticos','Envases, botellas y plásticos rígidos o flexibles.','fa-bottle-water','#0288d1'),
 (3,'Vidrio','Botellas y envases de vidrio reutilizables.','fa-wine-bottle','#00897b'),
 (4,'Metales','Aluminio, hierro, cobre y chatarra en general.','fa-gears','#607d8b'),
 (5,'Electrónicos','Aparatos eléctricos y electrónicos (RAEE).','fa-plug','#5e35b1'),
 (6,'Textiles','Prendas de vestir, telas y fibras.','fa-shirt','#d81b60'),
 (7,'Otros','Materiales aprovechables no clasificados arriba.','fa-recycle','#2e8800');

INSERT INTO materiales
 (categoria_id,nombre,descripcion,recomendaciones,condiciones_entrega,unidad_medida,icono) VALUES
 (1,'Papel de archivo','Papel blanco de oficina, cuadernos, fotocopias.','Retira ganchos y clips metálicos. No debe estar húmedo ni engrasado.','Debe entregarse seco y atado o dentro de una bolsa o caja.','kg','fa-file-lines'),
 (1,'Periódico','Periódico y revistas de circulación diaria.','Conserva el material seco; el periódico mojado pierde valor.','Amarrado en paquetes o dentro de bolsa.','kg','fa-newspaper'),
 (1,'Cartón corrugado','Cajas de cartón de empaques y embalajes.','Desarma las cajas para reducir volumen. Retira cinta adhesiva y plástico.','Cajas desarmadas, secas y sin residuos.','kg','fa-boxes-stacked'),
 (1,'Papel mixto','Papel de color, kraft, papel de revista con brillo.','Separa el papel de mejor calidad si deseas mejor precio.','Se entregará seco, sin residuos orgánicos ni plástico.','kg','fa-copy'),
 (2,'Botellas PET','Botellas de gaseosa, agua y jugos en PET.','Enjuaga, retira la tapa y aplasta la botella para reducir volumen.','Botellas limpias y sin líquido.','kg','fa-bottle-water'),
 (2,'Envases plásticos rígidos','Envases de aseo y de alimentos (#2, #5).','Enjuaga residuos de jabón o aceite. Retira etiquetas si es posible.','Sin restos de producto; seco.','kg','fa-jar'),
 (2,'Bolsas plásticas limpias','Bolsas de supermercado y empaque flexibles.','Deben estar limpias y sin restos de comida.','Empacadas dentro de una bolsa grande.','kg','fa-bag-shopping'),
 (2,'Tapas plásticas','Tapas de botellas y envases plásticos.','Lávalas y sepáralas del cuerpo de la botella.','Sueltas en bolsa o recipiente.','kg','fa-circle-dot'),
 (3,'Botellas de vidrio','Botellas y frascos de vidrio (color natural).','Enjuaga y retira la tapa metálica o plástica.','Sin rupturas y sin líquido.','kg','fa-wine-bottle'),
 (3,'Vidrio de color','Vidrio ámbar y verde de envases.','Separa por color si acumulas más de 20 kg.','Envuelto o en caja para evitar accidentes.','kg','fa-wine-glass'),
 (4,'Latas de aluminio','Latas de bebidas gaseosas y cerveza.','Aplasta las latas para reducir volumen.','Limpias y sin líquido.','kg','fa-beer-mug-empty'),
 (4,'Chatarra metálica','Hierro, lámina y perfiles de metal en desuso.','Retira puntas o elementos cortantes y avisa al operador.','Material sin objetos cortantes expuestos.','kg','fa-screwdriver-wrench'),
 (4,'Cobre','Cable y tubería de cobre.','Retira el plástico del cable solo si puedes hacerlo de forma segura.','Piezas sueltas en bolsa o caja.','kg','fa-bolt'),
 (5,'Celulares en desuso','Teléfonos y accesorios electrónicos fuera de uso.','Retira la tarjeta SIM y borra tu información personal.','Entregar con cargador si lo tienes; no debe estar hinchada la batería.','unidad','fa-mobile-screen'),
 (5,'Computadores y periféricos','Computadores, impresoras, teclados y monitores.','Respalda y borra la información antes de entregar.','Equipos completos o por partes, sin vidrio roto expuesto.','unidad','fa-desktop'),
 (5,'Pilas y baterías','Pilas AA, AAA y baterías de equipos.','No abrir ni perforar. Guardar en recipiente seco.','Recipiente cerrado; material peligroso.','kg','fa-battery-half'),
 (5,'Cables y cargadores','Cables de datos, cargadores y fuentes.','Agrupa los cables y retira los que estén pelados.','En bolsa o caja.','kg','fa-plug'),
 (6,'Ropa usada','Prendas de vestir en buen estado.','Lava y seca la ropa antes de entregarla.','Empacada en bolsa cerrada.','kg','fa-shirt'),
 (6,'Retales de tela','Recortes de tela de confección.','Separa por tipo de fibra si es posible.','Sin humedad ni residuos.','kg','fa-scissors'),
 (7,'Aceite de cocina usado','Aceite vegetal usado de cocina.','Deja enfriar y filtra restos de comida.','Viértelo con embudo en una botella plástica cerrada.','litro','fa-oil-can'),
 (7,'Llantas usadas','Llantas de vehículo, moto o bicicleta.','Retira el rin antes de la entrega.','Sin rin y sin agua en su interior.','unidad','fa-car-side'),
 (7,'Electrodomésticos pequeños','Licuadoras, planchas, ventiladores fuera de uso.','Avisa si el artefacto tiene vidrio o resistencia expuesta.','Equipo completo, sin necesidad de empaque.','unidad','fa-blender'),
 (7,'Madera y estibas','Estibas, tablas y madera de embalaje.','Retira clavos y grapas expuestas.','Apilada y sin pintura con plomo.','kg','fa-tree');

-- ---------------------------------------------------------------------
-- PROGRAMA DE PUNTOS Y PREMIOS (incentivos por reciclar)
-- Puntos que otorga cada material por unidad recolectada.
-- ---------------------------------------------------------------------
UPDATE materiales SET puntos_por_unidad = CASE categoria_id
    WHEN 1 THEN 10      -- papel y cartón
    WHEN 2 THEN 15      -- plásticos
    WHEN 3 THEN 8       -- vidrio
    WHEN 4 THEN 25      -- metales
    WHEN 5 THEN 20      -- electrónicos
    WHEN 6 THEN 10      -- textiles
    ELSE 12 END;        -- otros
UPDATE materiales SET puntos_por_unidad = 40 WHERE nombre = 'Cobre';
UPDATE materiales SET puntos_por_unidad = 30 WHERE nombre IN ('Latas de aluminio','Celulares en desuso');
UPDATE materiales SET puntos_por_unidad = 5  WHERE nombre = 'Madera y estibas';
UPDATE materiales SET puntos_por_unidad = 15 WHERE nombre IN ('Aceite de cocina usado','Llantas usadas');

INSERT INTO premios (nombre, descripcion, puntos_requeridos, stock, icono) VALUES
 ('Botella reutilizable RECICLA+','Botella térmica de acero inoxidable de 500 ml con el logo del programa.',150,30,'fa-bottle-water'),
 ('Bolsa ecológica de tela','Bolsa reutilizable de tela resistente y lavable para tus compras.',250,25,'fa-bag-shopping'),
 ('Árbol sembrado a tu nombre','Sembramos un árbol nativo y te enviamos el certificado con tu nombre.',300,100,'fa-tree'),
 ('Kit de aseo ecológico','Jabón artesanal, cepillo de bambú y bolsas compostables.',400,15,'fa-spray-can-sparkles'),
 ('Saldo de datos móviles 5 GB','Recarga de 5 GB de navegación para tu línea móvil.',600,20,'fa-mobile-screen-button'),
 ('Bono de mercado $ 30.000','Bono redimible en la tienda aliada del barrio.',900,10,'fa-cart-shopping'),
 ('Audífonos inalámbricos','Audífonos Bluetooth con estuche de carga.',2500,5,'fa-headphones'),
 ('Bicicleta todo terreno','Bicicleta de 21 velocidades para tus recorridos.',5000,2,'fa-bicycle');

INSERT INTO empresas (id,nombre,nit,responsable,telefono,email,direccion,ciudad) VALUES
 (1,'EcoRecicla S.A.S.','901001002-3','Laura Mendoza','310 555 1010','contacto@ecorecicla.local','Calle 15 # 22-40, Bodega 4','Bucaramanga'),
 (2,'Recuperar del Oriente Ltda.','901001003-4','Jorge Rangel','310 555 2020','contacto@recuperar.local','Carrera 9 # 12-15','Bucaramanga');

INSERT INTO vehiculos (empresa_id,placa,tipo,capacidad) VALUES
 (1,'EGR-101','Camion de estacas','6 toneladas'),
 (1,'EGR-102','Camioneta de platón','1.5 toneladas'),
 (2,'ROR-201','Camion compactador','4 toneladas');

-- Contrasenas demo (bcrypt):  Admin123*  /  Operador123*  /  Ciudadano123*
INSERT INTO usuarios (id,rol_id,empresa_id,nombres,apellidos,documento,telefono,email,password,direccion,barrio,ciudad) VALUES
 (1,3,NULL,'Administrador','RECICLA+','1000000001','320 000 0001','admin@recicla.local','$2y$10$YHJQ1aVarlCuYadF54GQV.gW6y9fKO3ZbWzXiskxAu0PmEdxfUs1i','Calle 1 # 1-1','Centro','Bucaramanga'),
 (2,2,1,'Laura','Mendoza','1000000002','310 555 1010','operador@recicla.local','$2y$10$eY3E5WL3Vfn.ijRtCPBFNePhgfF6rYF0aXp3znpP1.8jUnjconjl6','Calle 15 # 22-40','San Francisco','Bucaramanga'),
 (3,2,2,'Jorge','Rangel','1000000003','310 555 2020','operador2@recicla.local','$2y$10$eY3E5WL3Vfn.ijRtCPBFNePhgfF6rYF0aXp3znpP1.8jUnjconjl6','Carrera 9 # 12-15','Cabecera','Bucaramanga'),
 (4,1,NULL,'Maria','Suarez','1000000004','320 000 0004','ciudadano@recicla.local','$2y$10$rHpM/DxLDwjmnRFEXIJ5oe.o82XQgLn.sDtwPYvOMbCMRskm04Z3C','Carrera 33 # 45-12','Alvarez','Bucaramanga'),
 (5,1,NULL,'Pedro','Gomez','1000000005','320 000 0005','pedro@recicla.local','$2y$10$rHpM/DxLDwjmnRFEXIJ5oe.o82XQgLn.sDtwPYvOMbCMRskm04Z3C','Calle 105 # 20-14','Caneyes','Bucaramanga');

-- Fin del script.
