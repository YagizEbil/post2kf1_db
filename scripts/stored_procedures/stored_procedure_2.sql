-- Procedure 2: Get Races Won by a Team
DELIMITER //

CREATE PROCEDURE GetTeamWonRaces (IN teamId INT)
BEGIN
    SELECT
        r.race_id,
        c.circuit_name,
        r.race_date,
        d.driver_name AS winning_driver,
    FROM Race r
    JOIN Circuit c ON r.circuit_id = c.circuit_id
    JOIN Driver d ON r.winning_driver_id = d.driver_id
    WHERE r.winning_team_id = teamId
    ORDER BY r.race_date DESC;
END;
//

DELIMITER ;
