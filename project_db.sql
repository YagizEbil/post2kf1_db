CREATE TABLE Team (
    team_id SERIAL PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    country VARCHAR(50) NOT NULL,
    num_constructor_championships INT DEFAULT 0,
    num_driver_championships INT DEFAULT 0,
    total_race_wins INT DEFAULT 0,
    total_podiums INT DEFAULT 0
);

CREATE TABLE Driver (
    driver_id SERIAL PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    num_wins INT DEFAULT 0,
    num_poles INT DEFAULT 0,
    num_podiums INT DEFAULT 0,
    nationality VARCHAR(50) NOT NULL,
    num_championships INT DEFAULT 0,
    current_team_id INT REFERENCES Team(team_id),
    driver_number INT NOT NULL
);

CREATE TABLE Principal (
    principal_id SERIAL PRIMARY KEY,
    principal_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    nationality VARCHAR(50) NOT NULL,
    team_id INT REFERENCES Team(team_id),
    start_year INT NOT NULL
);

CREATE TABLE Race (
    race_id SERIAL PRIMARY KEY,
    grand_prix_name VARCHAR(100) NOT NULL,
    num_laps INT NOT NULL,
    circuit_name VARCHAR(100) NOT NULL,
    race_date DATE NOT NULL,
    winner_driver_id INT REFERENCES Driver(driver_id),
    winning_team_id INT REFERENCES Team(team_id),
    country VARCHAR(50) NOT NULL,
    pole_position_driver_id INT REFERENCES Driver(driver_id),
    fastest_lap_driver_id INT REFERENCES Driver(driver_id)
);

CREATE TABLE Car (
    car_id SERIAL PRIMARY KEY,
    car_name VARCHAR(100) NOT NULL,
    season INT NOT NULL,
    team_id INT REFERENCES Team(team_id),
    engine_supplier VARCHAR(100) NOT NULL,
    wins INT DEFAULT 0,
    poles INT DEFAULT 0,
    podiums INT DEFAULT 0
);

-- Penalty (Weak Entity)
CREATE TABLE Penalty (
    penalty_id SERIAL PRIMARY KEY,
    race_id INT REFERENCES Race(race_id),
    penalty_type VARCHAR(100) NOT NULL,
    driver_id INT REFERENCES Driver(driver_id) ON DELETE CASCADE,
    team_id INT REFERENCES Team(team_id) ON DELETE CASCADE
);

-- Sponsor (Superclass)
CREATE TABLE Sponsor (
    sponsor_id SERIAL PRIMARY KEY,
    sponsor_name VARCHAR(100) NOT NULL,
    industry VARCHAR(100) NOT NULL
);

-- Driver Sponsor (ISA)
CREATE TABLE Driver_Sponsor (
    sponsor_id INT REFERENCES Sponsor(sponsor_id) ON DELETE CASCADE,
    driver_id INT REFERENCES Driver(driver_id),
    PRIMARY KEY (sponsor_id, driver_id),
    contract_value DECIMAL(10,2),
    contract_start DATE
);

-- Team Sponsor (ISA)
CREATE TABLE Team_Sponsor (
    sponsor_id INT REFERENCES Sponsor(sponsor_id) ON DELETE CASCADE,
    team_id INT REFERENCES Team(team_id),
    PRIMARY KEY (sponsor_id, team_id),
    contract_value DECIMAL(10,2),
    contract_start DATE
);

-- Relationship: Drives For
CREATE TABLE Drives_For (
    driver_id INT REFERENCES Driver(driver_id) ON DELETE CASCADE,
    team_id INT REFERENCES Team(team_id) ON DELETE CASCADE,
    start_year INT NOT NULL,
    end_year INT,
    PRIMARY KEY (driver_id, team_id)
);

-- Relationship: Race Participation (to track drivers in each race)
CREATE TABLE Race_Participation (
    race_id INT REFERENCES Race(race_id) ON DELETE CASCADE,
    driver_id INT REFERENCES Driver(driver_id) ON DELETE CASCADE,
    team_id INT REFERENCES Team(team_id),
    car_id INT REFERENCES Car(car_id),
    grid_position INT,
    finishing_position INT,
    PRIMARY KEY (race_id, driver_id)
);


