-- Procedure 3: Get Team Standings by Wins
CREATE PROCEDURE GetTeamStandings
AS
BEGIN
    SELECT
        team_name,
        total_race_wins,
        num_constructor_championships,
        num_driver_championships
    FROM Team
    ORDER BY total_race_wins DESC;
END;
GO