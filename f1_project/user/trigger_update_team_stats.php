<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Trigger Demonstration: UpdateTeamStats

// MySQL Connection Details
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password
$dbname = "F1_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // Critical error, stop script
}

// --- Helper function to get team stats ---
function getTeamStats($conn_obj, $teamId) {
    $stmt = $conn_obj->prepare("SELECT team_name, total_race_wins, total_podiums FROM Team WHERE team_id = ?");
    if (!$stmt) {
        return "Error preparing team stats query: " . $conn_obj->error;
    }
    $stmt->bind_param("i", $teamId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stats = $result->fetch_assoc();
    } else {
        $stats = null; // Team not found
    }
    $stmt->close();
    return $stats;
}

// --- Variables for the page ---
$message = ''; // For success/error messages from form submission
$error_message_page = ''; // For general page errors (e.g., DB connection on initial load)
$team_id_to_watch = 1; // Default Team ID to watch (e.g., Ferrari from your sample data)
$team_stats_before = null;
$team_stats_after = null;

// Fetch initial "before" stats for the team to watch
$team_stats_before = getTeamStats($conn, $team_id_to_watch);
if (is_string($team_stats_before)) { // Check if getTeamStats returned an error string
    $error_message_page = $team_stats_before;
    $team_stats_before = null;
} elseif (!$team_stats_before) {
    $error_message_page = "Team ID " . htmlspecialchars($team_id_to_watch) . " not found for 'before' stats.";
}


// --- Handle Form Submission to Insert a New Race ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form inputs (simplified for demonstration)
    $race_id = filter_input(INPUT_POST, 'race_id', FILTER_VALIDATE_INT);
    $circuit_id = filter_input(INPUT_POST, 'circuit_id', FILTER_VALIDATE_INT);
    $car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
    $driver_id = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);
    // team_id_for_race is the team participating in the race, for podium check
    $team_id_for_race = filter_input(INPUT_POST, 'team_id_for_race', FILTER_VALIDATE_INT);
    $race_date = $_POST['race_date'] ?? date('Y-m-d'); // Default to today if not set
    // winning_team_id_for_race is the team that won, for win count check
    $winning_team_id_for_race = filter_input(INPUT_POST, 'winning_team_id_for_race', FILTER_VALIDATE_INT);
    // finishing_position is for the team_id_for_race, for podium check
    $finishing_position = filter_input(INPUT_POST, 'finishing_position', FILTER_VALIDATE_INT);
    
    // For demonstration, other NOT NULL fields are given default valid values or assumed nullable where appropriate
    $grid_position = filter_input(INPUT_POST, 'grid_position', FILTER_VALIDATE_INT, ['options' => ['default' => 1]]);
    // Assuming other FKs like winning_driver_id can be NULL based on schema, or need inputs
    $winning_driver_id = filter_input(INPUT_POST, 'winning_driver_id', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'null_on_failure' => true]]);
    $pole_position_driver_id = null; // Example default
    $fastest_lap_driver_id = null;   // Example default


    // --- Update $team_id_to_watch based on which team stats we want to see change ---
    // Let's prioritize showing changes for the winning_team_id if set,
    // otherwise, the team_id_for_race (for podiums).
    if ($winning_team_id_for_race) {
        $team_id_to_watch = $winning_team_id_for_race;
    } elseif ($team_id_for_race) {
        $team_id_to_watch = $team_id_for_race;
    }
    // Re-fetch "before" stats for the potentially new team_id_to_watch if form was submitted
    $team_stats_before = getTeamStats($conn, $team_id_to_watch);
     if (is_string($team_stats_before)) {
        $message = $team_stats_before; // Show error in main message area
        $team_stats_before = null;
    } elseif (!$team_stats_before && $team_id_to_watch) {
        $message = "Warning: Team ID " . htmlspecialchars($team_id_to_watch) . " (selected for watching) not found.";
    }


    // --- Basic Validation ---
    if ($race_id === false || $circuit_id === false || $car_id === false || $driver_id === false ||
        $team_id_for_race === false || $winning_team_id_for_race === false || // Allow 0 for winning_team_id if no winner or schema allows NULL
        $finishing_position === false || $grid_position === false) {
        $message = "Error: All numeric IDs, finishing position, and grid position must be valid integers.";
    } else {
        // Prepare INSERT statement for Race table
        $stmt_insert_race = $conn->prepare("INSERT INTO Race (race_id, circuit_id, car_id, driver_id, team_id, race_date, winning_team_id, winning_driver_id, pole_position_driver_id, fastest_lap_driver_id, grid_position, finishing_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_insert_race) {
            $message = "Error preparing race insert query: " . $conn->error;
        } else {
            $stmt_insert_race->bind_param("iiiiisisiiii",
                $race_id, $circuit_id, $car_id, $driver_id, $team_id_for_race, $race_date,
                $winning_team_id_for_race, $winning_driver_id, $pole_position_driver_id,
                $fastest_lap_driver_id, $grid_position, $finishing_position
            );

            if ($stmt_insert_race->execute()) {
                $message = "New race record (ID: " . htmlspecialchars($race_id) . ") inserted successfully! Trigger 'UpdateTeamStats' should have fired.";
                // Fetch "after" stats for the team to watch
                $team_stats_after = getTeamStats($conn, $team_id_to_watch);
                if (is_string($team_stats_after)) {
                     $message .= "<br>Error fetching 'after' stats: " . $team_stats_after;
                     $team_stats_after = null;
                }

            } else {
                $message = "Error inserting new race record: " . $stmt_insert_race->error . ". Please ensure Race ID is unique and all foreign keys exist.";
            }
            $stmt_insert_race->close();
        }
    }
}
$conn->close(); // Close connection after all operations for this page load
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demonstrate 'UpdateTeamStats' Trigger</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f4f4f8; color: #333; }
        .container { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 30px auto; }
        h2, h3 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        label { display: block; margin-top: 12px; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"], input[type="date"] {
            width: calc(100% - 22px); padding: 10px; margin-bottom: 15px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button {
            background-color: #28a745; color: white; padding: 12px 20px; border: none;
            border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease;
        }
        button:hover { background-color: #218838; }
        .stats-box { margin-top: 20px; padding: 15px; background-color: #e9ecef; border-radius: 4px; border: 1px solid #ced4da;}
        .stats-box p { margin: 8px 0; font-size: 1.05em; }
        .stats-box strong { color: #343a40; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .back-link { display: inline-block; margin-top: 25px; padding: 8px 15px; background-color: #6c757d; color: white; border-radius: 4px; text-decoration: none; }
        .back-link:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Demonstrate 'UpdateTeamStats' Trigger</h2>
        <p>This trigger fires after a new race is inserted. It updates the winning team's total wins and the participating team's total podiums if their finishing position is 3rd or better.</p>
        <p>We will monitor Team ID: <strong><?php echo htmlspecialchars($team_id_to_watch); ?></strong> (<?php echo htmlspecialchars($team_stats_before['team_name'] ?? 'Team not found or error'); ?>) for changes.</p>

        <?php if ($error_message_page): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message_page); ?></p>
        <?php endif; ?>

        <?php if ($team_stats_before && !is_string($team_stats_before)): ?>
            <div class="stats-box">
                <h3>Stats for Team ID <?php echo htmlspecialchars($team_id_to_watch); ?> (<?php echo htmlspecialchars($team_stats_before['team_name']); ?>) - BEFORE Insert:</h3>
                <p><strong>Total Race Wins:</strong> <?php echo htmlspecialchars($team_stats_before['total_race_wins']); ?></p>
                <p><strong>Total Podiums:</strong> <?php echo htmlspecialchars($team_stats_before['total_podiums']); ?></p>
            </div>
        <?php endif; ?>

        <hr style="margin: 30px 0;">
        <h3>Insert New Race Record (to fire trigger)</h3>
        <form action="trigger_update_team_stats.php" method="POST">
            <p><em>Ensure Race ID is unique and all other IDs (Circuit, Car, Driver, Teams) exist in their respective tables. Your sample data includes IDs 1-10 for most.</em></p>
            <div class="form-grid">
                <div>
                    <label for="race_id">New Race ID (Unique):</label>
                    <input type="number" id="race_id" name="race_id" required>
                </div>
                <div>
                    <label for="circuit_id">Circuit ID:</label>
                    <input type="number" id="circuit_id" name="circuit_id" value="1" required>
                </div>
                <div>
                    <label for="car_id">Car ID:</label>
                    <input type="number" id="car_id" name="car_id" value="1" required>
                </div>
                <div>
                    <label for="driver_id">Driver ID (participating):</label>
                    <input type="number" id="driver_id" name="driver_id" value="1" required>
                </div>
                <div>
                    <label for="team_id_for_race">Team ID (participating, for podium):</label>
                    <input type="number" id="team_id_for_race" name="team_id_for_race" value="<?php echo $team_id_to_watch; ?>" required>
                </div>
                <div>
                    <label for="winning_team_id_for_race">Winning Team ID (for win count):</label>
                    <input type="number" id="winning_team_id_for_race" name="winning_team_id_for_race" value="<?php echo $team_id_to_watch; ?>" required>
                </div>
                <div>
                    <label for="winning_driver_id">Winning Driver ID (can be NULL):</label>
                    <input type="number" id="winning_driver_id" name="winning_driver_id" value="1">
                </div>
                <div>
                    <label for="finishing_position">Finishing Position (for Team ID above):</label>
                    <input type="number" id="finishing_position" name="finishing_position" value="1" min="1" required>
                </div>
                <div>
                    <label for="grid_position">Grid Position:</label>
                    <input type="number" id="grid_position" name="grid_position" value="1" min="1" required>
                </div>
                <div>
                    <label for="race_date">Race Date:</label>
                    <input type="date" id="race_date" name="race_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            <button type="submit">Insert Race & See Trigger Effect</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="message <?php echo (strpos(strtolower($message), 'error') === false && strpos(strtolower($message), 'warning') === false) ? 'success' : 'error'; ?>">
                <?php echo $message; // Message from POST processing ?>
            </p>
        <?php endif; ?>

        <?php if ($team_stats_after): ?>
            <div class="stats-box" style="background-color: #d4edda;">
                 <h3>Stats for Team ID <?php echo htmlspecialchars($team_id_to_watch); ?> (<?php echo htmlspecialchars($team_stats_after['team_name']); ?>) - AFTER Insert:</h3>
                <p><strong>Total Race Wins:</strong> <?php echo htmlspecialchars($team_stats_after['total_race_wins']); ?></p>
                <p><strong>Total Podiums:</strong> <?php echo htmlspecialchars($team_stats_after['total_podiums']); ?></p>
            </div>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message) && $team_stats_before && !$error_message_page): ?>
             <p class="message error">Could not retrieve 'after' stats. The team might not have been affected by the insert as expected or an issue occurred.</p>
        <?php endif; ?>


        <p style="text-align: center;">
            <a href="index.php" class="back-link">Back to Main Page</a>
        </p>
    </div>
</body>
</html>