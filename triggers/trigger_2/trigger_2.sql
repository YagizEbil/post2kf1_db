-- Trigger 2: Update Driver's win and pole counts after a race insertion
CREATE TRIGGER UpdateDriverWinsPoles
ON Race
AFTER INSERT
AS
BEGIN
    -- Update driver's win count
    UPDATE Driver
    SET num_wins = num_wins + 1
    FROM Driver
    INNER JOIN inserted
    ON Driver.driver_id = inserted.winning_driver_id
    WHERE inserted.winning_driver_id IS NOT NULL;

    -- Update driver's pole count
    UPDATE Driver
    SET num_poles = num_poles + 1
    FROM Driver
    INNER JOIN inserted
    ON Driver.driver_id = inserted.pole_position_driver_id
    WHERE inserted.pole_position_driver_id IS NOT NULL;
END;