-- Procedure 3: Get Team Standings by Wins
DELIMITER //

CREATE PROCEDURE GetTeamStandings()
BEGIN
    SELECT
        team_name,
        total_race_wins,
        num_constructor_championships,
        num_driver_championships
    FROM Team
    ORDER BY total_race_wins DESC;
END;
//

DELIMITER ;
