-- Trigger 3: Update Car's win and pole counts after a race insertion
CREATE TRIGGER UpdateCarWinsPoles
ON Race
AFTER INSERT
AS
BEGIN
    -- Update car's win count
    UPDATE Car
    SET wins = wins + 1
    FROM Car
    INNER JOIN inserted
    ON Car.car_id = inserted.car_id
    WHERE inserted.winning_driver_id IS NOT NULL;

    -- Update car's pole count
    UPDATE Car
    SET poles = poles + 1
    FROM Car
    INNER JOIN inserted
    ON Car.car_id = inserted.car_id
    WHERE inserted.pole_position_driver_id IS NOT NULL;
END;