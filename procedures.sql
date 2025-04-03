-- Procedures

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

-- Procedure 2: Get Races Won by a Team
CREATE PROCEDURE GetTeamWonRaces (@teamId INT)
AS
BEGIN
    SELECT
        r.race_id,
        c.circuit_name,
        r.race_date,
        d.driver_name AS winning_driver,
        r.finishing_position AS winning_driver_position
    FROM Race r
    JOIN Circuit c ON r.circuit_id = c.circuit_id
    JOIN Driver d ON r.winning_driver_id = d.driver_id
    WHERE r.winning_team_id = @teamId
    ORDER BY r.race_date DESC;
END;
GO

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