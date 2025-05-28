DROP DATABASE IF EXISTS `F1_db`;
CREATE DATABASE F1_db;
USE F1_db;

-- Team Table
CREATE TABLE Team (
    team_id INT PRIMARY KEY,
    team_name CHAR(255) NOT NULL,
    total_race_wins INT DEFAULT 0,
    num_constructor_championships INT DEFAULT 0,
    num_driver_championships INT DEFAULT 0,
    total_podiums INT DEFAULT 0,
    country CHAR(100) NOT NULL
);

-- Sponsor Table
CREATE TABLE Sponsor (
    sponsor_id INT PRIMARY KEY,
    sponsor_name CHAR(255) NOT NULL,
    industry CHAR(100) NOT NULL,
    contract_value DECIMAL(10,2) NOT NULL
);

-- Driver Table
CREATE TABLE Driver (
    driver_id INT PRIMARY KEY,
    driver_name CHAR(255) NOT NULL,
    driver_number INT UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    num_wins INT DEFAULT 0,
    num_podiums INT DEFAULT 0,
    num_championships INT DEFAULT 0,
    num_poles INT DEFAULT 0
);

-- Team_Sponsor Relationship Table (ISA (merged)
CREATE TABLE Team_Sponsor (
    team_id INT,
    sponsor_id INT,
    PRIMARY KEY (team_id, sponsor_id),
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE,
    FOREIGN KEY (sponsor_id) REFERENCES Sponsor(sponsor_id) ON DELETE CASCADE
);

-- Driver_Sponsor Relationship Table (ISA) (merged)
CREATE TABLE Driver_Sponsor (
    driver_id INT,
    sponsor_id INT,
    PRIMARY KEY (driver_id, sponsor_id),
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (sponsor_id) REFERENCES Sponsor(sponsor_id) ON DELETE CASCADE
);

-- Principal and Managed by Table (merged)
CREATE TABLE Principal_Managed (
    principal_id INT PRIMARY KEY,
    team_id INT NOT NULL,
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE,
    principal_name CHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    nationality CHAR(100) NOT NULL,
    since DATE NOT NULL
);

-- Car Table
CREATE TABLE Car (
    car_id INT PRIMARY KEY,
    car_name CHAR(255) NOT NULL,
    engine_supplier CHAR(100) NOT NULL,
    season INT NOT NULL,
    wins INT DEFAULT 0,
    poles INT DEFAULT 0,
    podiums INT DEFAULT 0
);

-- Relationship: Manufactures
CREATE TABLE Manufactures (
    team_id INT,
    car_id INT,
    PRIMARY KEY (team_id, car_id),
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES Car(car_id) ON DELETE CASCADE
);

-- Circuit Table
CREATE TABLE Circuit (
    circuit_id INT PRIMARY KEY,
    circuit_name CHAR(255) NOT NULL,
    num_laps INT NOT NULL,
    country CHAR(100) NOT NULL,
    grand_prix_name CHAR(255) NOT NULL
);

-- Race Table
CREATE TABLE Race (
    race_id INT PRIMARY KEY,
    circuit_id INT NOT NULL,
    car_id INT NOT NULL,
    driver_id INT NOT NULL,
    team_id INT NOT NULL,
    race_date DATE NOT NULL,
    winning_team_id INT,
    winning_driver_id INT,
    pole_position_driver_id INT,
    fastest_lap_driver_id INT,
    grid_position INT NOT NULL,
    finishing_position INT NOT NULL,
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES Car(car_id) ON DELETE CASCADE,
    FOREIGN KEY (circuit_id) REFERENCES Circuit(circuit_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (winning_team_id) REFERENCES Team(team_id) ON DELETE SET NULL,
    FOREIGN KEY (winning_driver_id) REFERENCES Driver(driver_id) ON DELETE SET NULL,
    FOREIGN KEY (pole_position_driver_id) REFERENCES Driver(driver_id) ON DELETE SET NULL,
    FOREIGN KEY (fastest_lap_driver_id) REFERENCES Driver(driver_id) ON DELETE SET NULL
);

-- Penalty Table (merged) (weak entity)
CREATE TABLE Penalty (
    penalty_id INT PRIMARY KEY,
    race_id INT NOT NULL,
    driver_id INT NOT NULL,
    penalty_type CHAR(255) NOT NULL,
    FOREIGN KEY (race_id) REFERENCES Race(race_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE
);

-- Drives_for Relationship Table
CREATE TABLE Drives_for (
    driver_id INT,
    team_id INT,
    drive_season INT,
    PRIMARY KEY (driver_id, team_id, drive_season),
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE
);

-- Insert Teams (10 rows)
-- team_id  team_name total_race_wins num_constructor_championships num_driver_championships total_podiums country
INSERT INTO Team VALUES (1, 'Scuderia Ferrari', 243, 16, 15, 800, 'Italy');
INSERT INTO Team VALUES (2, 'McLaren', 183, 8, 12, 500, 'United Kingdom');
INSERT INTO Team VALUES (3, 'Mercedes-AMG Petronas', 125, 8, 9, 300, 'Germany');
INSERT INTO Team VALUES (4, 'Red Bull Racing', 113, 6, 7, 270, 'Austria');
INSERT INTO Team VALUES (5, 'Williams', 114, 9, 7, 300, 'United Kingdom');
INSERT INTO Team VALUES (6, 'Lotus', 81, 7, 6, 250, 'United Kingdom');
INSERT INTO Team VALUES (7, 'Renault', 35, 2, 2, 100, 'France');
INSERT INTO Team VALUES (8, 'Alpine', 1, 0, 0, 10, 'France');
INSERT INTO Team VALUES (9, 'Honda', 3, 0, 0, 10, 'Japan');
INSERT INTO Team VALUES (10, 'Brabham', 35, 2, 4, 120, 'United Kingdom');

-- Insert Drivers (10 rows)
INSERT INTO Driver VALUES (1, 'Ayrton Senna', 12, '1960-03-21', 41, 80, 3, 65);
INSERT INTO Driver VALUES (2, 'Alain Prost', 11, '1955-02-24', 51, 106, 4, 33);
INSERT INTO Driver VALUES (3, 'Michael Schumacher', 7, '1969-01-03', 91, 155, 7, 68);
INSERT INTO Driver VALUES (4, 'Lewis Hamilton', 44, '1985-01-07', 103, 197, 7, 104);
INSERT INTO Driver VALUES (5, 'Sebastian Vettel', 5, '1987-07-03', 53, 122, 4, 57);
INSERT INTO Driver VALUES (6, 'Niki Lauda', 1, '1949-02-22', 25, 54, 3, 24);
INSERT INTO Driver VALUES (7, 'Fernando Alonso', 14, '1981-07-29', 32, 104, 2, 22);
INSERT INTO Driver VALUES (8, 'Max Verstappen', 33, '1997-09-30', 54, 90, 3, 35);
INSERT INTO Driver VALUES (9, 'Jim Clark', 16, '1936-03-04', 25, 32, 2, 33);
INSERT INTO Driver VALUES (10, 'Juan Manuel Fangio', 2, '1911-06-24', 24, 35, 5, 29);

-- Insert Principals (10 rows)
INSERT INTO Principal_Managed VALUES (1, 1, 'Jean Todt', '1946-02-25', 'France', '1993-01-01');
INSERT INTO Principal_Managed VALUES (2, 2, 'Ron Dennis', '1947-06-01', 'United Kingdom', '1980-01-01');
INSERT INTO Principal_Managed VALUES (3, 3, 'Toto Wolff', '1972-01-12', 'Austria', '2013-01-01');
INSERT INTO Principal_Managed VALUES (4, 4, 'Christian Horner', '1973-11-16', 'United Kingdom', '2005-01-01');
INSERT INTO Principal_Managed VALUES (5, 5, 'Frank Williams', '1942-04-16', 'United Kingdom', '1977-01-01');
INSERT INTO Principal_Managed VALUES (6, 6, 'Colin Chapman', '1928-05-19', 'United Kingdom', '1954-01-01');
INSERT INTO Principal_Managed VALUES (7, 7, 'Flavio Briatore', '1950-04-12', 'Italy', '1991-01-01');
INSERT INTO Principal_Managed VALUES (8, 8, 'Laurent Rossi', '1975-07-10', 'France', '2021-01-01');
INSERT INTO Principal_Managed VALUES (9, 9, 'Takahiro Hachigo', '1959-02-17', 'Japan', '2016-01-01');
INSERT INTO Principal_Managed VALUES (10, 10, 'Bernie Ecclestone', '1930-10-28', 'United Kingdom', '1972-01-01');

-- Insert Sponsors (10 rows)
INSERT INTO Sponsor VALUES (1, 'Marlboro', 'Tobacco', 500.00);
INSERT INTO Sponsor VALUES (2, 'Vodafone', 'Telecommunications', 200.00);
INSERT INTO Sponsor VALUES (3, 'Petronas', 'Oil & Gas', 450.00);
INSERT INTO Sponsor VALUES (4, 'Red Bull', 'Beverages', 700.00);
INSERT INTO Sponsor VALUES (5, 'TAG Heuer', 'Luxury', 300.00);
INSERT INTO Sponsor VALUES (6, 'Santander', 'Banking', 250.00);
INSERT INTO Sponsor VALUES (7, 'Rothmans', 'Tobacco', 150.00);
INSERT INTO Sponsor VALUES (8, 'Shell', 'Oil & Gas', 400.00);
INSERT INTO Sponsor VALUES (9, 'Pirelli', 'Tyres', 100.00);
INSERT INTO Sponsor VALUES (10, 'Honda', 'Automotive', 350.00);

-- Insert Team Sponsors (10 rows)
INSERT INTO Team_Sponsor VALUES (1, 1);
INSERT INTO Team_Sponsor VALUES (2, 2);
INSERT INTO Team_Sponsor VALUES (3, 3);
INSERT INTO Team_Sponsor VALUES (4, 4);
INSERT INTO Team_Sponsor VALUES (5, 5);
INSERT INTO Team_Sponsor VALUES (6, 6);
INSERT INTO Team_Sponsor VALUES (7, 7);
INSERT INTO Team_Sponsor VALUES (8, 8);
INSERT INTO Team_Sponsor VALUES (9, 9);
INSERT INTO Team_Sponsor VALUES (10, 10);

-- Insert Driver Sponsors (10 rows)
INSERT INTO Driver_Sponsor VALUES (1, 1);
INSERT INTO Driver_Sponsor VALUES (2, 2);
INSERT INTO Driver_Sponsor VALUES (3, 3);
INSERT INTO Driver_Sponsor VALUES (4, 4);
INSERT INTO Driver_Sponsor VALUES (5, 5);
INSERT INTO Driver_Sponsor VALUES (6, 6);
INSERT INTO Driver_Sponsor VALUES (7, 7);
INSERT INTO Driver_Sponsor VALUES (8, 8);
INSERT INTO Driver_Sponsor VALUES (9, 9);
INSERT INTO Driver_Sponsor VALUES (10, 10);

-- Insert Cars (10 rows)
INSERT INTO Car VALUES (1, 'F2004', 'Ferrari', 2004, 15, 12, 20);
INSERT INTO Car VALUES (2, 'MP4/4', 'Honda', 1988, 15, 15, 25);
INSERT INTO Car VALUES (3, 'W11', 'Mercedes', 2020, 13, 10, 18);
INSERT INTO Car VALUES (4, 'RB19', 'Honda', 2023, 21, 12, 23);
INSERT INTO Car VALUES (5, 'FW14B', 'Renault', 1992, 10, 8, 15);
INSERT INTO Car VALUES (6, 'Lotus 72', 'Ford', 1972, 5, 6, 8);
INSERT INTO Car VALUES (7, 'R25', 'Renault', 2005, 8, 5, 12);
INSERT INTO Car VALUES (8, 'A522', 'Renault', 2022, 1, 1, 3);
INSERT INTO Car VALUES (9, 'Honda RA272', 'Honda', 1965, 1, 0, 2);
INSERT INTO Car VALUES (10, 'BT52', 'BMW', 1983, 4, 3, 7);

-- Insert Manufactures (10 rows)
INSERT INTO Manufactures VALUES (1, 1); -- Ferrari -> F2004
INSERT INTO Manufactures VALUES (2, 2); -- McLaren -> MP4/4
INSERT INTO Manufactures VALUES (3, 3); -- Mercedes -> W11
INSERT INTO Manufactures VALUES (4, 4); -- Red Bull -> RB19
INSERT INTO Manufactures VALUES (5, 5); -- Williams -> FW14B
INSERT INTO Manufactures VALUES (6, 6); -- Lotus -> Lotus 72
INSERT INTO Manufactures VALUES (7, 7); -- Renault -> R25
INSERT INTO Manufactures VALUES (8, 8); -- Alpine -> A522
INSERT INTO Manufactures VALUES (9, 9); -- Honda -> RA272
INSERT INTO Manufactures VALUES (10, 10); -- Brabham -> BT52

-- Insert Circuits (10 rows)
INSERT INTO Circuit VALUES (1, 'Monza', 53, 'Italy', 'Italian Grand Prix');
INSERT INTO Circuit VALUES (2, 'Silverstone', 52, 'United Kingdom', 'British Grand Prix');
INSERT INTO Circuit VALUES (3, 'Monaco', 78, 'Monaco', 'Monaco Grand Prix');
INSERT INTO Circuit VALUES (4, 'Suzuka', 53, 'Japan', 'Japanese Grand Prix');
INSERT INTO Circuit VALUES (5, 'Spa-Francorchamps', 44, 'Belgium', 'Belgian Grand Prix');
INSERT INTO Circuit VALUES (6, 'Interlagos', 71, 'Brazil', 'Brazilian Grand Prix');
INSERT INTO Circuit VALUES (7, 'Hockenheim', 67, 'Germany', 'German Grand Prix');
INSERT INTO Circuit VALUES (8, 'Nürburgring', 60, 'Germany', 'European Grand Prix');
INSERT INTO Circuit VALUES (9, 'Imola', 63, 'Italy', 'Emilia Romagna Grand Prix');
INSERT INTO Circuit VALUES (10, 'Austin', 56, 'United States', 'United States Grand Prix');

-- Insert into Race (10 rows)
INSERT INTO Race VALUES (1, 1, 1, 3, 1, '2004-09-12', 1, 3, 3, 3, 1, 1);
INSERT INTO Race VALUES (2, 2, 2, 1, 2, '1988-07-10', 2, 1, 1, 1, 1, 1);
INSERT INTO Race VALUES (3, 3, 3, 4, 3, '2020-08-30', 3, 4, 4, 4, 1, 1);
INSERT INTO Race VALUES (4, 4, 4, 8, 4, '2023-05-28', 4, 8, 8, 8, 1, 1);
INSERT INTO Race VALUES (5, 5, 5, 5, 5, '1992-08-30', 5, 5, 5, 5, 1, 1);
INSERT INTO Race VALUES (6, 6, 6, 9, 6, '1972-07-16', 6, 9, 9, 9, 1, 1);
INSERT INTO Race VALUES (7, 7, 7, 7, 7, '2005-10-09', 7, 7, 7, 7, 1, 1);
INSERT INTO Race VALUES (8, 8, 8, 8, 4, '2022-07-31', 8, 7, 7, 7, 1, 1);
INSERT INTO Race VALUES (9, 9, 9, 9, 6, '1965-10-24', 9, 9, 9, 9, 1, 1);
INSERT INTO Race VALUES (10, 10, 10, 10, 10, '1983-10-15', 10, 10, 10, 10, 1, 1);

-- Insert Penalties (10 rows)
INSERT INTO Penalty VALUES (1, 1, 3, 'Drive Through');
INSERT INTO Penalty VALUES (2, 2, 1, '5-Second Time Penalty');
INSERT INTO Penalty VALUES (3, 3, 4, '10-Second Stop and Go');
INSERT INTO Penalty VALUES (4, 4, 8, 'Grid Penalty');
INSERT INTO Penalty VALUES (5, 5, 5, 'Disqualification');
INSERT INTO Penalty VALUES (6, 6, 9, 'Track Limits Violation');
INSERT INTO Penalty VALUES (7, 7, 7, 'Causing a Collision');
INSERT INTO Penalty VALUES (8, 8, 8, 'Unsafe Release');
INSERT INTO Penalty VALUES (9, 9, 9, 'Jump Start');
INSERT INTO Penalty VALUES (10, 10, 10, 'Exceeding Track Limits');

-- Insert into Drives_for (10 rows)
-- driver_id / team_id / drive_season
INSERT INTO Drives_for VALUES (1, 2, 1988);  -- Ayrton Senna -> McLaren (1988)
INSERT INTO Drives_for VALUES (2, 2, 1989);  -- Alain Prost -> McLaren (1989)
INSERT INTO Drives_for VALUES (3, 1, 2004);  -- Michael Schumacher -> Ferrari (2004)
INSERT INTO Drives_for VALUES (4, 3, 2020);  -- Lewis Hamilton -> Mercedes (2020)
INSERT INTO Drives_for VALUES (5, 4, 2013);  -- Sebastian Vettel -> Red Bull (2013)
INSERT INTO Drives_for VALUES (6, 1, 1975);  -- Niki Lauda -> Ferrari (1975)
INSERT INTO Drives_for VALUES (7, 7, 2006);  -- Fernando Alonso -> Renault (2006)
INSERT INTO Drives_for VALUES (8, 4, 2023);  -- Max Verstappen -> Red Bull (2023)
INSERT INTO Drives_for VALUES (9, 6, 1967);  -- Jim Clark -> Lotus (1967)
INSERT INTO Drives_for VALUES (10, 10, 1954); -- Juan Manuel Fangio -> Brabham (1954)

DELIMITER //

CREATE TRIGGER UpdateTeamStats
AFTER INSERT ON Race
FOR EACH ROW
BEGIN
  -- Increment win count
  IF NEW.winning_team_id IS NOT NULL THEN
    UPDATE Team
    SET total_race_wins = total_race_wins + 1
    WHERE team_id = NEW.winning_team_id;
  END IF;

  -- Increment podium count
  IF NEW.finishing_position <= 3 THEN
    UPDATE Team
    SET total_podiums = total_podiums + 1
    WHERE team_id = NEW.team_id;
  END IF;
END;
//

DELIMITER ;

DELIMITER //

CREATE TRIGGER UpdateDriverStats
AFTER INSERT ON Race
FOR EACH ROW
BEGIN
  IF NEW.winning_driver_id IS NOT NULL THEN
    UPDATE Driver
    SET num_wins = num_wins + 1
    WHERE driver_id = NEW.winning_driver_id;
  END IF;

  IF NEW.pole_position_driver_id IS NOT NULL THEN
    UPDATE Driver
    SET num_poles = num_poles + 1
    WHERE driver_id = NEW.pole_position_driver_id;
  END IF;

  IF NEW.finishing_position <= 3 THEN
    UPDATE Driver
    SET num_podiums = num_podiums + 1
    WHERE driver_id = NEW.driver_id;
  END IF;
END;
//

DELIMITER ;

DELIMITER //

CREATE TRIGGER UpdateCarStats
AFTER INSERT ON Race
FOR EACH ROW
BEGIN
  IF NEW.winning_driver_id IS NOT NULL THEN
    UPDATE Car
    SET wins = wins + 1
    WHERE car_id = NEW.car_id;
  END IF;

  IF NEW.pole_position_driver_id IS NOT NULL THEN
    UPDATE Car
    SET poles = poles + 1
    WHERE car_id = NEW.car_id;
  END IF;

  IF NEW.finishing_position <= 3 THEN
    UPDATE Car
    SET podiums = podiums + 1
    WHERE car_id = NEW.car_id;
  END IF;
END;
//

DELIMITER ;

-- Procedure 1: Get Driver's Career Summary
DELIMITER //

CREATE PROCEDURE GetDriverCareerSummary (IN driverId INT)
BEGIN
    SELECT
        driver_name,
        driver_number,
        date_of_birth,
        num_wins,
        num_podiums,
        num_championships,
        num_poles
    FROM Driver
    WHERE driver_id = driverId;
END;
//

DELIMITER ;

-- Procedure 2: Get Races Won by a Team
DELIMITER //

CREATE PROCEDURE GetTeamWonRaces (IN teamId INT)
BEGIN
    SELECT
        r.race_id,
        c.circuit_name,
        r.race_date,
        d.driver_name AS winning_driver
    FROM Race r
    JOIN Circuit c ON r.circuit_id = c.circuit_id
    JOIN Driver d ON r.winning_driver_id = d.driver_id
    WHERE r.winning_team_id = teamId
    ORDER BY r.race_date DESC;
END;
//

DELIMITER ;

-- Procedure 3: Get Team Standings by Wins
DELIMITER //

CREATE PROCEDURE GetTeamStandings()
BEGIN
    SELECT
        team_name,
        total_race_wins,
        num_constructor_championships,
        num_driver_championships
    FROM Team
    ORDER BY total_race_wins DESC;
END;
//

DELIMITER ;
