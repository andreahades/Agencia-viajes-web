CREATE DATABASE IF NOT EXISTS AGENCIA
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE AGENCIA;

CREATE TABLE IF NOT EXISTS VUELO (
  id_vuelo INT AUTO_INCREMENT PRIMARY KEY,
  origen VARCHAR(80) NOT NULL,
  destino VARCHAR(80) NOT NULL,
  fecha DATE NOT NULL,
  plazas_disponibles INT NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  CHECK (plazas_disponibles >= 0),
  CHECK (precio >= 0)
);

CREATE TABLE IF NOT EXISTS HOTEL (
  id_hotel INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  ubicacion VARCHAR(120) NOT NULL,
  habitaciones_disponibles INT NOT NULL,
  tarifa_noche DECIMAL(10,2) NOT NULL,
  CHECK (habitaciones_disponibles >= 0),
  CHECK (tarifa_noche >= 0)
);

CREATE TABLE IF NOT EXISTS RESERVA (
  id_reserva INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  fecha_reserva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_vuelo INT NULL,
  id_hotel INT NULL,
  CONSTRAINT fk_reserva_vuelo FOREIGN KEY (id_vuelo) REFERENCES VUELO(id_vuelo)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_reserva_hotel FOREIGN KEY (id_hotel) REFERENCES HOTEL(id_hotel)
    ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE INDEX idx_vuelo_destino_fecha ON VUELO(destino, fecha);
CREATE INDEX idx_hotel_ubicacion ON HOTEL(ubicacion);
CREATE INDEX idx_reserva_hotel ON RESERVA(id_hotel);
CREATE INDEX idx_reserva_vuelo ON RESERVA(id_vuelo);
