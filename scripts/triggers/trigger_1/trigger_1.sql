DELIMITER //

CREATE TRIGGER UpdateTeamStats
AFTER INSERT ON Race
FOR EACH ROW
BEGIN
  -- Increment win count
  IF NEW.winning_team_id IS NOT NULL THEN
    UPDATE Team
    SET total_race_wins = total_race_wins + 1
    WHERE team_id = NEW.winning_team_id;
  END IF;

  -- Increment podium count
  IF NEW.finishing_position <= 3 THEN
    UPDATE Team
    SET total_podiums = total_podiums + 1
    WHERE team_id = NEW.team_id;
  END IF;
END;
//

DELIMITER ;
