-- Vaccination Management System: MySQL 5.7+ / MariaDB 10.3+
-- Import with: mysql -u root -p < database.sql   (or use phpMyAdmin > Import)

CREATE DATABASE IF NOT EXISTS vaccination_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vaccination_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','parent','hospital') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE hospitals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(120) NOT NULL,
  address VARCHAR(200) NOT NULL,
  city VARCHAR(80) NOT NULL DEFAULT 'Karachi',
  phone VARCHAR(20) NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (user_id),
  UNIQUE (name),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE children (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  dob DATE NOT NULL,
  gender ENUM('Boy','Girl') NOT NULL,
  blood_group VARCHAR(5) NULL,
  guardian_name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vaccines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  age_weeks INT NOT NULL,
  description VARCHAR(255) NULL,
  available TINYINT(1) NOT NULL DEFAULT 1,
  UNIQUE (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  child_id INT NOT NULL,
  vaccine_id INT NOT NULL,
  hospital_id INT NOT NULL,
  preferred_date DATE NOT NULL,
  status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  admin_remark VARCHAR(255) NULL,
  result ENUM('pending','vaccinated','not_vaccinated') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
  FOREIGN KEY (vaccine_id) REFERENCES vaccines(id) ON DELETE RESTRICT,
  FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vaccination_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  child_id INT NOT NULL,
  vaccine_id INT NOT NULL,
  hospital_id INT NOT NULL,
  appointment_id INT NULL,
  given_on DATE NOT NULL,
  status ENUM('vaccinated','not_vaccinated') NOT NULL,
  UNIQUE (appointment_id),
  FOREIGN KEY (child_id) REFERENCES children(id) ON DELETE CASCADE,
  FOREIGN KEY (vaccine_id) REFERENCES vaccines(id) ON DELETE RESTRICT,
  FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE RESTRICT,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_appt_status ON appointments(status, result);
CREATE INDEX idx_appt_child ON appointments(child_id);
CREATE INDEX idx_appt_hospital ON appointments(hospital_id);
CREATE INDEX idx_rec_child ON vaccination_records(child_id);
CREATE INDEX idx_rec_date ON vaccination_records(given_on);
CREATE INDEX idx_children_parent ON children(parent_id);

-- Default admin. Email: admin@vms.local  Password: Admin@12345  (change it after first login)
INSERT INTO users (name, email, password_hash, role) VALUES
('System Admin', 'admin@vms.local', '$2y$10$ez0RpWs8G.NLXI6FRQXUjeegag8tWI4mZzTxf3cp/aY0Y.GQcP6yu', 'admin');

-- Pakistan EPI style schedule (age in weeks after birth)
INSERT INTO vaccines (name, age_weeks, description, available) VALUES
('BCG', 0, 'Protects against tuberculosis', 1),
('OPV-0', 0, 'Oral polio vaccine, birth dose', 1),
('Pentavalent-1', 6, 'DPT, Hepatitis B and Hib, first dose', 1),
('Pentavalent-2', 10, 'DPT, Hepatitis B and Hib, second dose', 1),
('Pentavalent-3', 14, 'DPT, Hepatitis B and Hib, third dose', 1),
('IPV', 14, 'Injectable polio vaccine', 1),
('Measles-1', 39, 'Measles and rubella, first dose', 1),
('Measles-2', 65, 'Measles and rubella, second dose', 1);

INSERT INTO hospitals (name, address, city, phone, active) VALUES
('Aga Khan University Hospital', 'Stadium Road', 'Karachi', '021-34930051', 1),
('Indus Hospital', 'Korangi Crossing', 'Karachi', '021-35112709', 1),
('Civil Hospital Karachi', 'Baba-e-Urdu Road', 'Karachi', '021-99215740', 1);
