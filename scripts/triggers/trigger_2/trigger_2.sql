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
