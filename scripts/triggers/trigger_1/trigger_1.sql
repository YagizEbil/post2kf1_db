-- Trigger 1: Update Team's total_race_wins after a race insertion
CREATE TRIGGER UpdateTeamWins
ON Race
AFTER INSERT
AS
BEGIN
    UPDATE Team
    SET total_race_wins = total_race_wins + 1
    FROM Team
    INNER JOIN inserted
    ON Team.team_id = inserted.winning_team_id
    WHERE inserted.winning_team_id IS NOT NULL;
END;