-- =====================================================================
-- RECICLA+ | Datos DEMO de presentación (opcional)
-- ---------------------------------------------------------------------
-- Carga un historial creíble para mostrar el sistema en clase o en una
-- demostración: solicitudes en distintos estados, recolecciones de los
-- últimos meses, puntos acreditados, un premio canjeado y una ruta.
-- Las fechas son relativas a la fecha de ejecución (CURDATE).
--
-- Uso:  mysql -uroot -h127.0.0.1 recicla < database/demo_datos.sql
-- Ejecutar UNA sola vez sobre una base recién instalada.
-- =====================================================================
SET NAMES utf8mb4;
USE recicla;

-- ---------------------------- RUTAS --------------------------------
INSERT INTO rutas (id, empresa_id, nombre, zona, ciudad, fecha, estado) VALUES
 (1, 1, 'Ruta Álvarez - San Alonso', 'Occidente', 'Bucaramanga', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'abierta'),
 (2, 1, 'Ruta Caneyes - Norte', 'Norte', 'Bucaramanga', DATE_SUB(CURDATE(), INTERVAL 43 DAY), 'cerrada');

-- ----------------------- SOLICITUDES -------------------------------
-- 1 y 2 y 3: recolectadas (histórico de los últimos meses)
-- 4: registrada (pendiente) · 5: programada · 6: cancelada
INSERT INTO solicitudes
 (id, numero, ciudadano_id, empresa_id, empresa_usuario_id, direccion, barrio, ciudad, fecha_disponible,
  horario_disponible, observaciones, estado, fecha_aceptacion, fecha_programada, hora_programada, operador,
  vehiculo_id, ruta_id, observaciones_empresa, created_at) VALUES
 (1, 'REC-000001', 4, 1, 2, 'Carrera 33 # 45-12, apto 201', 'Álvarez', 'Bucaramanga',
  DATE_SUB(CURDATE(), INTERVAL 70 DAY), 'manana', 'El material está en el patio.', 'RECOLECTADA',
  DATE_SUB(NOW(), INTERVAL 69 DAY), DATE_SUB(CURDATE(), INTERVAL 68 DAY), '09:30:00', 'Carlos Duarte', 1, 2, 'Recogido sin novedad.', DATE_SUB(NOW(), INTERVAL 71 DAY)),
 (2, 'REC-000002', 5, 1, 2, 'Calle 105 # 20-14', 'Caneyes', 'Bucaramanga',
  DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'tarde', 'Avisar antes de llegar, hay reja.', 'RECOLECTADA',
  DATE_SUB(NOW(), INTERVAL 44 DAY), DATE_SUB(CURDATE(), INTERVAL 43 DAY), '10:00:00', 'Nidia Pérez', 2, 2, 'El ciudadano entregó todo completo.', DATE_SUB(NOW(), INTERVAL 46 DAY)),
 (3, 'REC-000003', 4, 2, 3, 'Carrera 33 # 45-12, apto 201', 'Álvarez', 'Bucaramanga',
  DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'manana', 'Cables y equipos viejos.', 'RECOLECTADA',
  DATE_SUB(NOW(), INTERVAL 19 DAY), DATE_SUB(CURDATE(), INTERVAL 18 DAY), '08:30:00', 'Jorge Rangel', 3, NULL, 'Se recogió material electrónico.', DATE_SUB(NOW(), INTERVAL 21 DAY)),
 (4, 'REC-000004', 5, NULL, NULL, 'Calle 105 # 20-14', 'Caneyes', 'Bucaramanga',
  DATE_ADD(CURDATE(), INTERVAL 2 DAY), 'indiferente', 'Tengo dos cajas de cartón grandes.', 'REGISTRADA',
  NULL, NULL, NULL, NULL, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
 (5, 'REC-000005', 4, 1, 2, 'Carrera 33 # 45-12, apto 201', 'Álvarez', 'Bucaramanga',
  DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'tarde', 'Botellas y tapas.', 'PROGRAMADA',
  DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 'Carlos Duarte', 1, 1,
  'Pasamos en la tarde, por favor dejar el material a la entrada.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
 (6, 'REC-000006', 5, 2, 3, 'Calle 105 # 20-14', 'Caneyes', 'Bucaramanga',
  DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'noche', 'Cambié de planes.', 'CANCELADA',
  DATE_SUB(NOW(), INTERVAL 12 DAY), NULL, NULL, NULL, NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 13 DAY));

-- ------------------- MATERIALES DE CADA SOLICITUD -------------------
INSERT INTO solicitud_materiales (solicitud_id, material_id, cantidad, unidad, descripcion) VALUES
 (1, 1, 20.00, 'kg', 'Papel de archivo de la oficina'),
 (1, 5,  8.00, 'kg', 'Botellas PET aplastadas'),
 (2, 11, 6.00, 'kg', 'Latas de aluminio de la tienda'),
 (2, 3, 15.00, 'kg', 'Cartón corrugado desarmado'),
 (3, 13, 2.00, 'kg', 'Cable de cobre'),
 (3, 14, 3.00, 'unidad', 'Celulares en desuso con cargador'),
 (4, 3, 12.00, 'kg', 'Cajas de cartón secas'),
 (5, 5, 10.00, 'kg', 'Botellas PET'),
 (5, 8,  1.50, 'kg', 'Tapas plásticas'),
 (6, 18, 5.00, 'kg', 'Ropa usada en buen estado');

-- ------------------------ RECOLECCIONES -----------------------------
INSERT INTO recolecciones (id, solicitud_id, empresa_id, usuario_id, vehiculo_id, ruta_id, fecha, hora,
  observaciones, peso_total, created_at) VALUES
 (1, 1, 1, 2, 1, 2, DATE_SUB(CURDATE(), INTERVAL 68 DAY), '09:35:00', 'Material pesado y seco, en buen estado.', 28.00, DATE_SUB(NOW(), INTERVAL 68 DAY)),
 (2, 2, 1, 2, 2, 2, DATE_SUB(CURDATE(), INTERVAL 43 DAY), '10:10:00', 'Se recogió todo lo publicado.', 21.00, DATE_SUB(NOW(), INTERVAL 43 DAY)),
 (3, 3, 2, 3, 3, NULL, DATE_SUB(CURDATE(), INTERVAL 18 DAY), '08:40:00', 'Equipos electrónicos completos.', 5.00, DATE_SUB(NOW(), INTERVAL 18 DAY));

INSERT INTO recoleccion_materiales (recoleccion_id, material_id, cantidad, unidad) VALUES
 (1, 1, 20.00, 'kg'), (1, 5, 8.00, 'kg'),
 (2, 11, 6.00, 'kg'), (2, 3, 15.00, 'kg'),
 (3, 13, 2.00, 'kg'), (3, 14, 3.00, 'unidad');

-- ---------------- HISTORIAL DE ESTADOS DE CADA SOLICITUD ------------
INSERT INTO historial_solicitudes (solicitud_id, usuario_id, estado_anterior, estado_nuevo, observacion, created_at) VALUES
 (1, 4, NULL, 'REGISTRADA', 'Solicitud creada por el ciudadano.', DATE_SUB(NOW(), INTERVAL 71 DAY)),
 (1, 2, 'REGISTRADA', 'ACEPTADA', 'Aceptada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 69 DAY)),
 (1, 2, 'ACEPTADA', 'PROGRAMADA', 'Recolección programada.', DATE_SUB(NOW(), INTERVAL 69 DAY)),
 (1, 2, 'PROGRAMADA', 'EN_RUTA', 'Operador en ruta hacia el domicilio.', DATE_SUB(NOW(), INTERVAL 68 DAY)),
 (1, 2, 'EN_RUTA', 'RECOLECTADA', 'Recolección registrada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 68 DAY)),
 (2, 5, NULL, 'REGISTRADA', 'Solicitud creada por el ciudadano.', DATE_SUB(NOW(), INTERVAL 46 DAY)),
 (2, 2, 'REGISTRADA', 'ACEPTADA', 'Aceptada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 44 DAY)),
 (2, 2, 'ACEPTADA', 'PROGRAMADA', 'Recolección programada.', DATE_SUB(NOW(), INTERVAL 44 DAY)),
 (2, 2, 'PROGRAMADA', 'RECOLECTADA', 'Recolección registrada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 43 DAY)),
 (3, 4, NULL, 'REGISTRADA', 'Solicitud creada por el ciudadano.', DATE_SUB(NOW(), INTERVAL 21 DAY)),
 (3, 3, 'REGISTRADA', 'ACEPTADA', 'Aceptada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 19 DAY)),
 (3, 3, 'ACEPTADA', 'PROGRAMADA', 'Recolección programada.', DATE_SUB(NOW(), INTERVAL 19 DAY)),
 (3, 3, 'PROGRAMADA', 'RECOLECTADA', 'Recolección registrada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 18 DAY)),
 (4, 5, NULL, 'REGISTRADA', 'Solicitud creada por el ciudadano.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
 (5, 4, NULL, 'REGISTRADA', 'Solicitud creada por el ciudadano.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
 (5, 2, 'REGISTRADA', 'ACEPTADA', 'Aceptada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
 (5, 2, 'ACEPTADA', 'PROGRAMADA', 'Recolección programada.', DATE_SUB(NOW(), INTERVAL 1 DAY)),
 (6, 5, NULL, 'REGISTRADA', 'Solicitud creada por el ciudadano.', DATE_SUB(NOW(), INTERVAL 13 DAY)),
 (6, 3, 'REGISTRADA', 'ACEPTADA', 'Aceptada por la empresa operadora.', DATE_SUB(NOW(), INTERVAL 12 DAY)),
 (6, 5, 'ACEPTADA', 'CANCELADA', 'Cancelada por el ciudadano.', DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ------------------- PUNTOS DEL PROGRAMA DE INCENTIVOS --------------
-- María (usuario 4): 20 kg papel (200) + 8 kg PET (120) + bono 20 = 340
--                    + 2 kg cobre (80) + 3 celulares (90) + bono 20 = 190
--                    total ganado 530 · canje de 150 → saldo 380
-- Pedro (usuario 5): 6 kg aluminio (180) + 15 kg cartón (150) + bono 20 = 350
INSERT INTO puntos_movimientos (usuario_id, solicitud_id, recoleccion_id, tipo, puntos, descripcion, created_at) VALUES
 (4, 1, 1, 'ganados', 200, 'Papel de archivo recolectado: 20 kg', DATE_SUB(NOW(), INTERVAL 68 DAY)),
 (4, 1, 1, 'ganados', 120, 'Botellas PET recolectado: 8 kg', DATE_SUB(NOW(), INTERVAL 68 DAY)),
 (4, 1, 1, 'ganados',  20, 'Bono por recolección completada con éxito', DATE_SUB(NOW(), INTERVAL 68 DAY)),
 (5, 2, 2, 'ganados', 180, 'Latas de aluminio recolectado: 6 kg', DATE_SUB(NOW(), INTERVAL 43 DAY)),
 (5, 2, 2, 'ganados', 150, 'Cartón corrugado recolectado: 15 kg', DATE_SUB(NOW(), INTERVAL 43 DAY)),
 (5, 2, 2, 'ganados',  20, 'Bono por recolección completada con éxito', DATE_SUB(NOW(), INTERVAL 43 DAY)),
 (4, 3, 3, 'ganados',  80, 'Cobre recolectado: 2 kg', DATE_SUB(NOW(), INTERVAL 18 DAY)),
 (4, 3, 3, 'ganados',  90, 'Celulares en desuso recolectado: 3 unidad', DATE_SUB(NOW(), INTERVAL 18 DAY)),
 (4, 3, 3, 'ganados',  20, 'Bono por recolección completada con éxito', DATE_SUB(NOW(), INTERVAL 18 DAY));

-- ------------------------ CANJE DE UN PREMIO ------------------------
INSERT INTO canjes (id, codigo, usuario_id, premio_id, puntos, estado, observaciones, atendido_por, fecha_entrega, created_at) VALUES
 (1, 'CANJE-000001', 4, 1, 150, 'entregado', 'Entregado en la jornada de reciclaje del barrio.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY));

INSERT INTO puntos_movimientos (usuario_id, canje_id, tipo, puntos, descripcion, created_at) VALUES
 (4, 1, 'canjeados', -150, 'Canje CANJE-000001: Botella reutilizable RECICLA+', DATE_SUB(NOW(), INTERVAL 7 DAY));

UPDATE premios SET stock = stock - 1 WHERE id = 1;

-- ------------------------- NOTIFICACIONES ---------------------------
INSERT INTO notificaciones (usuario_id, titulo, mensaje, leida, created_at) VALUES
 (4, 'Material recolectado', '¡Gracias! Tu solicitud REC-000003 fue recolectada. Ganaste 190 puntos RECICLA+.', 1, DATE_SUB(NOW(), INTERVAL 18 DAY)),
 (4, 'Recolección programada', 'Tu solicitud REC-000005 fue programada para mañana a las 14:00.', 0, DATE_SUB(NOW(), INTERVAL 1 DAY)),
 (4, 'Canje entregado', 'Tu canje CANJE-000001 (Botella reutilizable RECICLA+) fue entregado.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
 (5, 'Material recolectado', '¡Gracias! Tu solicitud REC-000002 fue recolectada. Ganaste 350 puntos RECICLA+.', 1, DATE_SUB(NOW(), INTERVAL 43 DAY)),
 (5, 'Solicitud cancelada', 'La solicitud REC-000006 fue cancelada.', 1, DATE_SUB(NOW(), INTERVAL 10 DAY));

-- Mantener los contadores al día para las próximas inserciones
ALTER TABLE solicitudes AUTO_INCREMENT = 7;
ALTER TABLE recolecciones AUTO_INCREMENT = 4;
ALTER TABLE canjes AUTO_INCREMENT = 2;
ALTER TABLE rutas AUTO_INCREMENT = 3;
