DELIMITER $$

-- Trigger 1: Update win count for teams
CREATE TRIGGER update_team_win_count
AFTER INSERT ON race_results
FOR EACH ROW
BEGIN
    IF NEW.position = 1 THEN
        UPDATE teams
        SET win_count = win_count + 1
        WHERE team_id = NEW.team_id;
    END IF;
END $$

-- Trigger 2: Update poles and wins for drivers
CREATE TRIGGER update_driver_poles_and_wins
AFTER INSERT ON race_results
FOR EACH ROW
BEGIN
    -- Update pole position for drivers
    IF NEW.position = 1 THEN
        UPDATE drivers
        SET pole_positions = pole_positions + 1
        WHERE driver_id = NEW.driver_id;
    END IF;
    
    -- Update wins for drivers
    IF NEW.position = 1 THEN
        UPDATE drivers
        SET wins = wins + 1
        WHERE driver_id = NEW.driver_id;
    END IF;
END $$

-- Trigger 3: Update poles and wins for cars
CREATE TRIGGER update_car_poles_and_wins
AFTER INSERT ON race_results
FOR EACH ROW
BEGIN
    -- Update pole positions for cars
    IF NEW.position = 1 THEN
        UPDATE cars
        SET pole_positions = pole_positions + 1
        WHERE car_id = NEW.car_id;
    END IF;

    -- Update wins for cars
    IF NEW.position = 1 THEN
        UPDATE cars
        SET wins = wins + 1
        WHERE car_id = NEW.car_id;
    END IF;
END $$

DELIMITER ;
