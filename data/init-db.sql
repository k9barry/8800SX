CREATE DATABASE IF NOT EXISTS viavi;

USE viavi;

CREATE USER 'viavi'@'%' IDENTIFIED BY '8800SX';
GRANT ALL ON viavi.* TO 'viavi'@'%';

FLUSH PRIVILEGES;

CREATE TABLE IF NOT EXISTS alignments (
  id INT NOT NULL AUTO_INCREMENT,
  datetime DATETIME NOT NULL,
  model VARCHAR(25) NOT NULL,
  serial VARCHAR(25) NOT NULL,
  file BLOB NOT NULL,
  entered DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
  filename VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) Engine = InnoDB;