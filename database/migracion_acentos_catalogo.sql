-- =====================================================================
-- RECICLA+ | Migración: tildes y ortografía del catálogo
-- ---------------------------------------------------------------------
-- Corrige nombres y textos de categorías y materiales que se cargaron sin
-- tildes en la primera versión del esquema. Aplica sobre bases existentes
-- sin alterar relaciones (actualiza por id).
-- Uso:  mysql -uroot -h127.0.0.1 recicla < database/migracion_acentos_catalogo.sql
-- =====================================================================
SET NAMES utf8mb4;
USE recicla;

UPDATE categorias SET nombre = 'Papel y cartón', descripcion = 'Papel, cartón y derivados de fibra vegetal.' WHERE id = 1;
UPDATE categorias SET nombre = 'Plásticos',      descripcion = 'Envases, botellas y plásticos rígidos o flexibles.' WHERE id = 2;
UPDATE categorias SET nombre = 'Electrónicos',   descripcion = 'Aparatos eléctricos y electrónicos (RAEE).' WHERE id = 5;

UPDATE materiales SET nombre = 'Periódico',
  descripcion = 'Periódico y revistas de circulación diaria.',
  recomendaciones = 'Conserva el material seco; el periódico mojado pierde valor.'
  WHERE id = 2;
UPDATE materiales SET nombre = 'Cartón corrugado',
  descripcion = 'Cajas de cartón de empaques y embalajes.',
  recomendaciones = 'Desarma las cajas para reducir volumen. Retira cinta adhesiva y plástico.'
  WHERE id = 3;
UPDATE materiales SET
  condiciones_entrega = 'Se entregará seco, sin residuos orgánicos ni plástico.'
  WHERE id = 4;
UPDATE materiales SET nombre = 'Envases plásticos rígidos',
  recomendaciones = 'Enjuaga residuos de jabón o aceite. Retira etiquetas si es posible.'
  WHERE id = 6;
UPDATE materiales SET nombre = 'Bolsas plásticas limpias' WHERE id = 7;
UPDATE materiales SET nombre = 'Tapas plásticas',
  recomendaciones = 'Lávalas y sepáralas del cuerpo de la botella.'
  WHERE id = 8;
UPDATE materiales SET
  recomendaciones = 'Enjuaga y retira la tapa metálica o plástica.',
  condiciones_entrega = 'Sin rupturas y sin líquido.'
  WHERE id = 9;
UPDATE materiales SET nombre = 'Vidrio de color',
  descripcion = 'Vidrio ámbar y verde de envases.',
  recomendaciones = 'Separa por color si acumulas más de 20 kg.'
  WHERE id = 10;
UPDATE materiales SET condiciones_entrega = 'Limpias y sin líquido.' WHERE id = 11;
UPDATE materiales SET nombre = 'Chatarra metálica',
  descripcion = 'Hierro, lámina y perfiles de metal en desuso.'
  WHERE id = 12;
UPDATE materiales SET nombre = 'Cobre',
  descripcion = 'Cable y tubería de cobre.',
  recomendaciones = 'Retira el plástico del cable solo si puedes hacerlo de forma segura.'
  WHERE id = 13;
UPDATE materiales SET nombre = 'Celulares en desuso',
  descripcion = 'Teléfonos y accesorios electrónicos fuera de uso.',
  recomendaciones = 'Retira la tarjeta SIM y borra tu información personal.',
  condiciones_entrega = 'Entregar con cargador si lo tienes; no debe estar hinchada la batería.'
  WHERE id = 14;
UPDATE materiales SET nombre = 'Computadores y periféricos',
  recomendaciones = 'Respalda y borra la información antes de entregar.'
  WHERE id = 15;
UPDATE materiales SET nombre = 'Pilas y baterías',
  descripcion = 'Pilas AA, AAA y baterías de equipos.'
  WHERE id = 16;
UPDATE materiales SET nombre = 'Cables y cargadores',
  recomendaciones = 'Agrupa los cables y retira los que estén pelados.'
  WHERE id = 17;
UPDATE materiales SET nombre = 'Retales de tela',
  descripcion = 'Recortes de tela de confección.'
  WHERE id = 19;
UPDATE materiales SET nombre = 'Aceite de cocina usado',
  condiciones_entrega = 'Viértelo con embudo en una botella plástica cerrada.'
  WHERE id = 20;
UPDATE materiales SET nombre = 'Llantas usadas',
  descripcion = 'Llantas de vehículo, moto o bicicleta.'
  WHERE id = 21;
UPDATE materiales SET nombre = 'Electrodomésticos pequeños' WHERE id = 22;
UPDATE materiales SET
  recomendaciones = 'Retira ganchos y clips metálicos. No debe estar húmedo ni engrasado.'
  WHERE id = 1;

UPDATE configuracion SET valor = 'Lunes a sábado 7:00 a.m. - 5:00 p.m.' WHERE clave = 'horario_atencion';
