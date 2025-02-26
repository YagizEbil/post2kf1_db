CREATE DATABASE F1_db;
USE F1_db;

-- Team Table
CREATE TABLE Team (
    team_id INT PRIMARY KEY,
    team_name VARCHAR(255) NOT NULL,
    total_race_wins INT DEFAULT 0,
    num_constructor_championships INT DEFAULT 0,
    num_driver_championships INT DEFAULT 0,
    total_podiums INT DEFAULT 0,
    country VARCHAR(100) NOT NULL
);

-- Sponsor Table
CREATE TABLE Sponsor (
    sponsor_id INT PRIMARY KEY,
    sponsor_name VARCHAR(255) NOT NULL,
    industry VARCHAR(100) NOT NULL,
    contract_value DECIMAL(10,2) NOT NULL
);

-- Driver Table
CREATE TABLE Driver (
    driver_id INT PRIMARY KEY,
    driver_number INT UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    num_wins INT DEFAULT 0,
    num_podiums INT DEFAULT 0,
    num_championships INT DEFAULT 0,
    num_poles INT DEFAULT 0
);

-- Team_Sponsor Relationship Table
CREATE TABLE Team_Sponsor (
    team_id INT,
    sponsor_id INT,
    PRIMARY KEY (team_id, sponsor_id),
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE,
    FOREIGN KEY (sponsor_id) REFERENCES Sponsor(sponsor_id) ON DELETE CASCADE
);

-- Driver_Sponsor Relationship Table (ISA)
CREATE TABLE Driver_Sponsor (
    driver_id INT,
    sponsor_id INT,
    PRIMARY KEY (driver_id, sponsor_id),
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (sponsor_id) REFERENCES Sponsor(sponsor_id) ON DELETE CASCADE
);

-- Principal and Managed by Table
CREATE TABLE Principal_Managed (
    principal_id INT PRIMARY KEY,
    team_id INT NOT NULL,
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE
    principal_name VARCHAR(255) NOT NULL,
    date_of_birth DATE NOT NULL,
    nationality VARCHAR(100) NOT NULL
);

-- Car Table
CREATE TABLE Car (
    car_id INT PRIMARY KEY,
    car_name VARCHAR(255) NOT NULL,
    engine_supplier VARCHAR(100) NOT NULL,
    season INT NOT NULL,
    wins INT DEFAULT 0,
    poles INT DEFAULT 0,
    podiums INT DEFAULT 0,
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE
);

-- Circuit Table
CREATE TABLE Circuit (
    circuit_id INT PRIMARY KEY,
    circuit_name VARCHAR(255) NOT NULL,
    num_laps INT NOT NULL,
    country VARCHAR(100) NOT NULL,
    grand_prix_name VARCHAR(255) NOT NULL
);

-- Race Table
CREATE TABLE Race (
    race_id INT PRIMARY KEY,
    circuit_id INT NOT NULL,
    car_id INT NOT NULL,
    team_id INT NOT NULL,
    race_date DATE NOT NULL,
    winning_team_id INT,
    winning_driver_id INT,
    pole_position_driver_id INT,
    fastest_lap_driver_id INT,
    grid_position INT NOT NULL,
    finishing_position INT NOT NULL,
    PRIMARY KEY (circuit_id, driver_id, race_date),
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES Car(car_id) ON DELETE CASCADE,
    FOREIGN KEY (circuit_id) REFERENCES Circuit(circuit_id) ON DELETE CASCADE,
    FOREIGN KEY (winning_team_id) REFERENCES Team(team_id) ON DELETE SET NULL,
    FOREIGN KEY (winning_driver_id) REFERENCES Driver(driver_id) ON DELETE SET NULL,
    FOREIGN KEY (pole_position_driver_id) REFERENCES Driver(driver_id) ON DELETE SET NULL,
    FOREIGN KEY (fastest_lap_driver_id) REFERENCES Driver(driver_id) ON DELETE SET NULL
);

-- Penalty Table
CREATE TABLE Penalty (
    penalty_id INT PRIMARY KEY,
    race_id INT NOT NULL,
    driver_id INT NOT NULL,
    penalty_type VARCHAR(255) NOT NULL
);

-- Race_Driver Relationship Table
CREATE TABLE Race_Driver (
    race_id INT,
    driver_id INT,
    grid_position INT NOT NULL,
    finishing_position INT NOT NULL,
    PRIMARY KEY (race_id, driver_id),
    FOREIGN KEY (race_id) REFERENCES Race(race_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE
);

-- Driver_Penalty Relationship Table
CREATE TABLE Driver_Penalty (
    driver_id INT,
    race_id INT,
    penalty_id INT,
    PRIMARY KEY (driver_id, race_id, penalty_id),
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (race_id) REFERENCES Race(race_id) ON DELETE CASCADE,
    FOREIGN KEY (penalty_id) REFERENCES Penalty(penalty_id) ON DELETE CASCADE
);

-- Drives_for Relationship Table
CREATE TABLE Drives_for (
    driver_id INT,
    team_id INT,
    season INT,
    PRIMARY KEY (driver_id, team_id, season),
    FOREIGN KEY (driver_id) REFERENCES Driver(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES Team(team_id) ON DELETE CASCADE
);
