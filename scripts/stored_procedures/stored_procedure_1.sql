-- Procedure 1: Get Driver's Career Summary
CREATE PROCEDURE GetDriverCareerSummary (@driverId INT)
AS
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
    WHERE driver_id = @driverId;
END;
GO