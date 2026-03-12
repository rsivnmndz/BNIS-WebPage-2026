-- ============================================================
--  BNIS SHS ENROLLMENT DATABASE  (Grade 11 & 12 ONLY)
--  Import this file into phpMyAdmin
--  Database: bnis_db
-- ============================================================

DROP DATABASE IF EXISTS bnis_db;
CREATE DATABASE bnis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bnis_db;

-- school_years
CREATE TABLE school_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(20) NOT NULL UNIQUE,
    year_from YEAR NOT NULL,
    year_to YEAR NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO school_years VALUES
(1,'SY 2026-2027',2026,2027,1),
(2,'SY 2025-2026',2025,2026,0),
(3,'SY 2024-2025',2024,2025,0);

-- strands master
CREATE TABLE strands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    track VARCHAR(60) NOT NULL,
    color_hex VARCHAR(7) NOT NULL DEFAULT '#800000'
) ENGINE=InnoDB;

INSERT INTO strands VALUES
(1,'STEM','Science, Technology, Engineering & Mathematics','Academic','#800000'),
(2,'ABM','Accountancy, Business & Management','Academic','#1d4ed8'),
(3,'HUMSS','Humanities & Social Sciences','Academic','#15803d'),
(4,'TVL','Technical-Vocational-Livelihood','TVL','#d97706'),
(5,'GAS','General Academic Strand','Academic','#6d28d9');

-- sections
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_year_id INT NOT NULL,
    grade TINYINT NOT NULL,
    strand_code VARCHAR(20) NOT NULL,
    section_name VARCHAR(40) NOT NULL,
    capacity INT NOT NULL DEFAULT 50,
    adviser VARCHAR(120) DEFAULT NULL,
    room VARCHAR(40) DEFAULT NULL,
    FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE,
    FOREIGN KEY (strand_code) REFERENCES strands(code),
    INDEX idx_gs (grade, strand_code)
) ENGINE=InnoDB;

INSERT INTO sections (school_year_id,grade,strand_code,section_name,capacity,adviser,room) VALUES
(1,11,'STEM','STEM 11-A',50,'Mr. Eduardo Cruz','Room 201'),
(1,11,'STEM','STEM 11-B',50,'Ms. Rosario Santos','Room 202'),
(1,11,'STEM','STEM 11-C',50,'Mr. Roberto Dela Cruz','Room 203'),
(1,11,'ABM','ABM 11-A',50,'Ms. Maricel Reyes','Room 204'),
(1,11,'ABM','ABM 11-B',50,'Mr. Jonathan Lim','Room 205'),
(1,11,'HUMSS','HUMSS 11-A',50,'Ms. Carla Garcia','Room 206'),
(1,11,'HUMSS','HUMSS 11-B',50,'Mr. Ramon Flores','Room 207'),
(1,11,'TVL','TVL 11-A',50,'Mr. Dennis Villanueva','Room 208'),
(1,11,'TVL','TVL 11-B',50,'Ms. Anita Torres','Room 209'),
(1,11,'GAS','GAS 11-A',50,'Ms. Ligaya Ramos','Room 210'),
(1,12,'STEM','STEM 12-A',50,'Ms. Florinda Mendoza','Room 211'),
(1,12,'STEM','STEM 12-B',50,'Mr. Alejandro Rivera','Room 212'),
(1,12,'STEM','STEM 12-C',50,'Ms. Corazon Aquino','Room 213'),
(1,12,'ABM','ABM 12-A',50,'Mr. Renato Bautista','Room 214'),
(1,12,'ABM','ABM 12-B',50,'Ms. Evelyn Castro','Room 215'),
(1,12,'HUMSS','HUMSS 12-A',50,'Mr. Ricardo Ortega','Room 216'),
(1,12,'HUMSS','HUMSS 12-B',50,'Ms. Natividad Navarro','Room 217'),
(1,12,'TVL','TVL 12-A',50,'Mr. Gregorio Santiago','Room 218'),
(1,12,'GAS','GAS 12-A',50,'Ms. Perla Fernandez','Room 219'),
-- SY 2025-2026 sections
(2,11,'STEM','STEM 11-A',50,'Mr. Eduardo Cruz','Room 201'),
(2,11,'STEM','STEM 11-B',50,'Ms. Rosario Santos','Room 202'),
(2,11,'ABM','ABM 11-A',50,'Ms. Maricel Reyes','Room 204'),
(2,11,'ABM','ABM 11-B',50,'Mr. Jonathan Lim','Room 205'),
(2,11,'HUMSS','HUMSS 11-A',50,'Ms. Carla Garcia','Room 206'),
(2,11,'TVL','TVL 11-A',50,'Mr. Dennis Villanueva','Room 208'),
(2,11,'TVL','TVL 11-B',50,'Ms. Anita Torres','Room 209'),
(2,11,'GAS','GAS 11-A',50,'Ms. Ligaya Ramos','Room 210'),
(2,12,'STEM','STEM 12-A',50,'Ms. Florinda Mendoza','Room 211'),
(2,12,'STEM','STEM 12-B',50,'Mr. Alejandro Rivera','Room 212'),
(2,12,'ABM','ABM 12-A',50,'Mr. Renato Bautista','Room 214'),
(2,12,'HUMSS','HUMSS 12-A',50,'Mr. Ricardo Ortega','Room 216'),
(2,12,'TVL','TVL 12-A',50,'Mr. Gregorio Santiago','Room 218'),
(2,12,'GAS','GAS 12-A',50,'Ms. Perla Fernandez','Room 219');

-- enrollees (Grade 11 & 12 ONLY)
CREATE TABLE enrollees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_year_id INT NOT NULL,
    lrn VARCHAR(12) DEFAULT NULL UNIQUE,
    last_name VARCHAR(80) NOT NULL,
    first_name VARCHAR(80) NOT NULL,
    middle_name VARCHAR(80) DEFAULT NULL,
    gender ENUM('Male','Female') NOT NULL,
    birth_date DATE DEFAULT NULL,
    grade TINYINT NOT NULL,
    strand_code VARCHAR(20) NOT NULL,
    section_id INT DEFAULT NULL,
    status ENUM('Enrolled','Pending','Dropped','Transferred') NOT NULL DEFAULT 'Enrolled',
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE,
    FOREIGN KEY (strand_code) REFERENCES strands(code),
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    INDEX idx_sy (school_year_id),
    INDEX idx_grade (grade),
    INDEX idx_strand (strand_code),
    INDEX idx_status (status),
    INDEX idx_gender (gender),
    INDEX idx_date (enrolled_at)
) ENGINE=InnoDB;

-- ── SEED DATA ─────────────────────────────────────────────
-- SY 2026-2027 Grade 11 STEM (~220 students)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*20),'Santos','Reyes','Cruz','Bautista','Garcia','Mendoza','Torres','Flores','Ramos','Villanueva','Aquino','Lim','Tan','Manalo','Aguilar','Pineda','Soriano','Pascual','Rivera','Morales'),
ELT(1+FLOOR(RAND()*20),IF(RAND()<.46,'Juan','Maria'),IF(RAND()<.46,'Carlos','Ana'),IF(RAND()<.46,'Miguel','Rosa'),IF(RAND()<.46,'Jose','Elena'),IF(RAND()<.46,'Rafael','Carla'),IF(RAND()<.46,'Daniel','Jasmine'),IF(RAND()<.46,'Marco','Christine'),IF(RAND()<.46,'Antonio','Angel'),IF(RAND()<.46,'Luis','Jessa'),IF(RAND()<.46,'Paolo','Kristine'),IF(RAND()<.46,'Angelo','Patricia'),IF(RAND()<.46,'Mark','Grace'),IF(RAND()<.46,'Ryan','Nicole'),IF(RAND()<.46,'James','Sheila'),IF(RAND()<.46,'Kevin','Maricel'),IF(RAND()<.46,'Joshua','Charmaine'),IF(RAND()<.46,'Nathan','Rowena'),IF(RAND()<.46,'Matthew','Joanna'),IF(RAND()<.46,'Christian','Vanessa'),IF(RAND()<.46,'Jerome','Cathleen')),
IF(RAND()<.46,'Male','Female'),11,'STEM',1+FLOOR(RAND()*3),'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 220) x;

-- SY 2026-2027 Grade 11 ABM (~148)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*20),'Santos','Reyes','Cruz','Bautista','Garcia','Mendoza','Torres','Flores','Ramos','Villanueva','Aquino','Lim','Tan','Manalo','Aguilar','Pineda','Soriano','Pascual','Rivera','Morales'),
ELT(1+FLOOR(RAND()*10),IF(RAND()<.40,'Juan','Maria'),IF(RAND()<.40,'Carlos','Ana'),IF(RAND()<.40,'Miguel','Rosa'),IF(RAND()<.40,'Jose','Elena'),IF(RAND()<.40,'Rafael','Carla'),IF(RAND()<.40,'Daniel','Jasmine'),IF(RAND()<.40,'Marco','Christine'),IF(RAND()<.40,'Antonio','Angel'),IF(RAND()<.40,'Luis','Jessa'),IF(RAND()<.40,'Paolo','Kristine')),
IF(RAND()<.40,'Male','Female'),11,'ABM',4+FLOOR(RAND()*2),'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 148) x;

-- SY 2026-2027 Grade 11 HUMSS (~132)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*15),'Santos','Reyes','Cruz','Garcia','Mendoza','Torres','Flores','Ramos','Aquino','Lim','Manalo','Aguilar','Pineda','Pascual','Rivera'),
ELT(1+FLOOR(RAND()*10),IF(RAND()<.38,'Juan','Maria'),IF(RAND()<.38,'Carlos','Ana'),IF(RAND()<.38,'Miguel','Rosa'),IF(RAND()<.38,'Jose','Elena'),IF(RAND()<.38,'Daniel','Carla'),IF(RAND()<.38,'Marco','Jasmine'),IF(RAND()<.38,'Antonio','Christine'),IF(RAND()<.38,'Luis','Angel'),IF(RAND()<.38,'Paolo','Jessa'),IF(RAND()<.38,'Angelo','Kristine')),
IF(RAND()<.38,'Male','Female'),11,'HUMSS',6+FLOOR(RAND()*2),'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 132) x;

-- SY 2026-2027 Grade 11 TVL (~116)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*15),'Villanueva','Torres','Castillo','Ramos','Dela Cruz','Morales','Navarro','Guerrero','Ortega','Fernandez','Santiago','Lopez','Gonzales','Hernandez','Perez'),
ELT(1+FLOOR(RAND()*10),IF(RAND()<.55,'Juan','Maria'),IF(RAND()<.55,'Carlos','Ana'),IF(RAND()<.55,'Miguel','Rosa'),IF(RAND()<.55,'Jose','Elena'),IF(RAND()<.55,'Daniel','Carla'),IF(RAND()<.55,'Marco','Jasmine'),IF(RAND()<.55,'Antonio','Christine'),IF(RAND()<.55,'Kevin','Angel'),IF(RAND()<.55,'Ryan','Jessa'),IF(RAND()<.55,'James','Kristine')),
IF(RAND()<.55,'Male','Female'),11,'TVL',8+FLOOR(RAND()*2),'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 116) x;

-- SY 2026-2027 Grade 11 GAS (~68)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*10),'Santos','Reyes','Cruz','Garcia','Mendoza','Flores','Ramos','Aquino','Lim','Rivera'),
ELT(1+FLOOR(RAND()*10),IF(RAND()<.48,'Juan','Maria'),IF(RAND()<.48,'Carlos','Ana'),IF(RAND()<.48,'Miguel','Rosa'),IF(RAND()<.48,'Jose','Elena'),IF(RAND()<.48,'Daniel','Carla'),IF(RAND()<.48,'Marco','Jasmine'),IF(RAND()<.48,'Antonio','Christine'),IF(RAND()<.48,'Luis','Angel'),IF(RAND()<.48,'Paolo','Jessa'),IF(RAND()<.48,'Angelo','Kristine')),
IF(RAND()<.48,'Male','Female'),11,'GAS',10,'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 68) x;

-- SY 2026-2027 Grade 12 STEM (~198)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*20),'Santos','Reyes','Cruz','Bautista','Garcia','Mendoza','Torres','Flores','Ramos','Villanueva','Aquino','Lim','Tan','Manalo','Aguilar','Pineda','Soriano','Pascual','Rivera','Morales'),
ELT(1+FLOOR(RAND()*10),IF(RAND()<.47,'Juan','Maria'),IF(RAND()<.47,'Carlos','Ana'),IF(RAND()<.47,'Miguel','Rosa'),IF(RAND()<.47,'Jose','Elena'),IF(RAND()<.47,'Rafael','Carla'),IF(RAND()<.47,'Daniel','Jasmine'),IF(RAND()<.47,'Marco','Christine'),IF(RAND()<.47,'Antonio','Angel'),IF(RAND()<.47,'Luis','Jessa'),IF(RAND()<.47,'Paolo','Kristine')),
IF(RAND()<.47,'Male','Female'),12,'STEM',11+FLOOR(RAND()*3),'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 198) x;

-- SY 2026-2027 Grade 12 ABM (~104)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*15),'Santos','Reyes','Cruz','Bautista','Garcia','Mendoza','Torres','Flores','Ramos','Villanueva','Aquino','Lim','Manalo','Pascual','Rivera'),
ELT(1+FLOOR(RAND()*10),IF(RAND()<.39,'Juan','Maria'),IF(RAND()<.39,'Carlos','Ana'),IF(RAND()<.39,'Miguel','Rosa'),IF(RAND()<.39,'Jose','Elena'),IF(RAND()<.39,'Rafael','Carla'),IF(RAND()<.39,'Daniel','Jasmine'),IF(RAND()<.39,'Marco','Christine'),IF(RAND()<.39,'Antonio','Angel'),IF(RAND()<.39,'Luis','Jessa'),IF(RAND()<.39,'Paolo','Kristine')),
IF(RAND()<.39,'Male','Female'),12,'ABM',14+FLOOR(RAND()*2),'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 104) x;

-- SY 2026-2027 Grade 12 HUMSS (~88)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*15),'Santos','Reyes','Cruz','Garcia','Mendoza','Torres','Flores','Ramos','Aquino','Lim','Manalo','Aguilar','Pineda','Pascual','Rivera'),
ELT(1+FLOOR(RAND()*10),IF(RAND()<.35,'Juan','Maria'),IF(RAND()<.35,'Carlos','Ana'),IF(RAND()<.35,'Miguel','Rosa'),IF(RAND()<.35,'Jose','Elena'),IF(RAND()<.35,'Daniel','Carla'),IF(RAND()<.35,'Marco','Jasmine'),IF(RAND()<.35,'Antonio','Christine'),IF(RAND()<.35,'Luis','Angel'),IF(RAND()<.35,'Paolo','Jessa'),IF(RAND()<.35,'Angelo','Kristine')),
IF(RAND()<.35,'Male','Female'),12,'HUMSS',16+FLOOR(RAND()*2),'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 88) x;

-- SY 2026-2027 Grade 12 TVL (~64)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*10),'Villanueva','Torres','Castillo','Ramos','Dela Cruz','Morales','Navarro','Guerrero','Ortega','Fernandez'),
ELT(1+FLOOR(RAND()*8),IF(RAND()<.58,'Juan','Maria'),IF(RAND()<.58,'Carlos','Ana'),IF(RAND()<.58,'Miguel','Rosa'),IF(RAND()<.58,'Jose','Elena'),IF(RAND()<.58,'Daniel','Carla'),IF(RAND()<.58,'Marco','Jasmine'),IF(RAND()<.58,'Kevin','Christine'),IF(RAND()<.58,'Ryan','Angel')),
IF(RAND()<.58,'Male','Female'),12,'TVL',18,'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 64) x;

-- SY 2026-2027 Grade 12 GAS (~44)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 1,ELT(1+FLOOR(RAND()*10),'Santos','Reyes','Cruz','Garcia','Mendoza','Flores','Ramos','Aquino','Lim','Rivera'),
ELT(1+FLOOR(RAND()*8),IF(RAND()<.48,'Juan','Maria'),IF(RAND()<.48,'Carlos','Ana'),IF(RAND()<.48,'Miguel','Rosa'),IF(RAND()<.48,'Jose','Elena'),IF(RAND()<.48,'Daniel','Carla'),IF(RAND()<.48,'Marco','Jasmine'),IF(RAND()<.48,'Luis','Christine'),IF(RAND()<.48,'Paolo','Angel')),
IF(RAND()<.48,'Male','Female'),12,'GAS',19,'Enrolled',
DATE_ADD('2026-06-01',INTERVAL FLOOR(RAND()*110) DAY)
FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 44) x;

-- SY 2025-2026 (previous year for YoY comparison)
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('Prev',n),CONCAT('F',n),IF(n%2=0,'Male','Female'),11,'STEM',20,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 190) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('PA',n),CONCAT('PA',n),IF(n%2=0,'Male','Female'),11,'ABM',23,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 130) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('PH',n),CONCAT('PH',n),IF(n%3=0,'Male','Female'),11,'HUMSS',25,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 110) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('PT',n),CONCAT('PT',n),IF(n%2=0,'Male','Female'),11,'TVL',26,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 98) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('PG',n),CONCAT('PG',n),IF(n%2=0,'Male','Female'),11,'GAS',28,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 55) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('P12S',n),CONCAT('P12S',n),IF(n%2=0,'Male','Female'),12,'STEM',29,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 170) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('P12A',n),CONCAT('P12A',n),IF(n%2=0,'Male','Female'),12,'ABM',31,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 92) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('P12H',n),CONCAT('P12H',n),IF(n%3=0,'Male','Female'),12,'HUMSS',32,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 74) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('P12T',n),CONCAT('P12T',n),IF(n%2=0,'Male','Female'),12,'TVL',33,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 54) x;
INSERT INTO enrollees (school_year_id,last_name,first_name,gender,grade,strand_code,section_id,status,enrolled_at)
SELECT 2,CONCAT('P12G',n),CONCAT('P12G',n),IF(n%2=0,'Male','Female'),12,'GAS',34,'Enrolled',DATE_ADD('2025-06-01',INTERVAL FLOOR(RAND()*110) DAY) FROM (SELECT a.N+b.N*10+1 n FROM (SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a,(SELECT 0 N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) b LIMIT 38) x;

-- Useful view for the dashboard
CREATE OR REPLACE VIEW v_summary AS
SELECT e.school_year_id, sy.label school_year, e.grade, e.strand_code strand,
  st.full_name strand_name, st.color_hex,
  COUNT(*) total, SUM(e.gender='Male') male, SUM(e.gender='Female') female
FROM enrollees e
JOIN school_years sy ON sy.id=e.school_year_id
JOIN strands st ON st.code=e.strand_code
WHERE e.status='Enrolled'
GROUP BY e.school_year_id,e.grade,e.strand_code;

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(120) DEFAULT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO admin_users (username, password_hash, email, role) VALUES
('admin', 'Ivan', 'admin@bnhs.edu', 'admin');

-- Verify: SELECT grade,strand_code,COUNT(*) total FROM enrollees WHERE school_year_id=1 GROUP BY grade,strand_code ORDER BY grade,total DESC;
