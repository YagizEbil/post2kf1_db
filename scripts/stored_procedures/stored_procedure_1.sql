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
