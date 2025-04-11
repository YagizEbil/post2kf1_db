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
