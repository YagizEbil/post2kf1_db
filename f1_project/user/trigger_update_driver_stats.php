<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "F1_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getDriverStats($conn_obj, $driverId) {
    $stmt = $conn_obj->prepare("SELECT driver_name, num_wins, num_podiums, num_poles FROM Driver WHERE driver_id = ?");
    if (!$stmt) {
        return "Error preparing driver stats query: " . $conn_obj->error;
    }
    $stmt->bind_param("i", $driverId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $stats = $result->fetch_assoc();
    } else {
        $stats = null;
    }
    $stmt->close();
    return $stats;
}

$message = '';
$error_message_page = '';
$driver_id_to_watch = 1;
$driver_stats_before = null;
$driver_stats_after = null;

$driver_stats_before = getDriverStats($conn, $driver_id_to_watch);
if (is_string($driver_stats_before)) {
    $error_message_page = $driver_stats_before;
    $driver_stats_before = null;
} elseif (!$driver_stats_before) {
    $error_message_page = "Driver ID " . htmlspecialchars($driver_id_to_watch) . " not found for 'before' stats.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $race_id = filter_input(INPUT_POST, 'race_id', FILTER_VALIDATE_INT);
    $circuit_id = filter_input(INPUT_POST, 'circuit_id', FILTER_VALIDATE_INT);
    $car_id = filter_input(INPUT_POST, 'car_id', FILTER_VALIDATE_INT);
    $team_id = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    
    $driver_id_for_race = filter_input(INPUT_POST, 'driver_id_for_race', FILTER_VALIDATE_INT);
    $race_date = $_POST['race_date'] ?? date('Y-m-d');
    
    $winning_driver_id_for_race = filter_input(INPUT_POST, 'winning_driver_id_for_race', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'null_on_failure' => true]]);
    $pole_position_driver_id_for_race = filter_input(INPUT_POST, 'pole_position_driver_id_for_race', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'null_on_failure' => true]]);
    $finishing_position = filter_input(INPUT_POST, 'finishing_position', FILTER_VALIDATE_INT);
    
    $grid_position = filter_input(INPUT_POST, 'grid_position', FILTER_VALIDATE_INT, ['options' => ['default' => 1]]);
    $winning_team_id = filter_input(INPUT_POST, 'winning_team_id', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'null_on_failure' => true]]);
    $fastest_lap_driver_id = null;

    if ($driver_id_for_race) {
        $driver_id_to_watch = $driver_id_for_race;
    }
    $driver_stats_before = getDriverStats($conn, $driver_id_to_watch);
     if (is_string($driver_stats_before)) {
        $message = $driver_stats_before;
        $driver_stats_before = null;
    } elseif (!$driver_stats_before && $driver_id_to_watch) {
        $message = "Warning: Driver ID " . htmlspecialchars($driver_id_to_watch) . " (selected for watching) not found.";
    }

    if ($race_id === false || $circuit_id === false || $car_id === false || $team_id === false ||
        $driver_id_for_race === false || $finishing_position === false || $grid_position === false ) {
        $message = "Error: Race ID, Circuit ID, Car ID, Team ID, participating Driver ID, Finishing Position, and Grid Position must be valid integers.";
    } else {
        $stmt_insert_race = $conn->prepare("INSERT INTO Race (race_id, circuit_id, car_id, driver_id, team_id, race_date, winning_team_id, winning_driver_id, pole_position_driver_id, fastest_lap_driver_id, grid_position, finishing_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_insert_race) {
            $message = "Error preparing race insert query: " . $conn->error;
        } else {
            $winning_team_id_val = $winning_team_id_for_race ? $winning_team_id : null;

            $stmt_insert_race->bind_param("iiiiisisiiii",
                $race_id, $circuit_id, $car_id, $driver_id_for_race, $team_id, $race_date,
                $winning_team_id,
                $winning_driver_id_for_race, 
                $pole_position_driver_id_for_race,
                $fastest_lap_driver_id, 
                $grid_position, $finishing_position
            );

            if ($stmt_insert_race->execute()) {
                $message = "New race record (ID: " . htmlspecialchars($race_id) . ") inserted successfully! Trigger 'UpdateDriverStats' should have fired.";
                $driver_stats_after = getDriverStats($conn, $driver_id_to_watch);
                 if (is_string($driver_stats_after)) {
                     $message .= "<br>Error fetching 'after' stats: " . $driver_stats_after;
                     $driver_stats_after = null;
                }
            } else {
                $message = "Error inserting new race record: " . $stmt_insert_race->error . ". Please ensure Race ID is unique and all foreign keys exist.";
            }
            $stmt_insert_race->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demonstrate 'UpdateDriverStats' Trigger</title>
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
        <h2>Demonstrate 'UpdateDriverStats' Trigger</h2>
        <p>This trigger fires after a new race is inserted. It updates the winning driver's wins, pole driver's poles, and participating driver's podiums if they finished in the top 3.</p>
        <p>We will monitor Driver ID: <strong><?php echo htmlspecialchars($driver_id_to_watch); ?></strong> (<?php echo htmlspecialchars($driver_stats_before['driver_name'] ?? 'Driver not found or error'); ?>) for changes.</p>

        <?php if ($error_message_page): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message_page); ?></p>
        <?php endif; ?>

        <?php if ($driver_stats_before && !is_string($driver_stats_before)): ?>
            <div class="stats-box">
                <h3>Stats for Driver ID <?php echo htmlspecialchars($driver_id_to_watch); ?> (<?php echo htmlspecialchars($driver_stats_before['driver_name']); ?>) - BEFORE Insert:</h3>
                <p><strong>Total Wins:</strong> <?php echo htmlspecialchars($driver_stats_before['num_wins']); ?></p>
                <p><strong>Total Podiums:</strong> <?php echo htmlspecialchars($driver_stats_before['num_podiums']); ?></p>
                <p><strong>Total Poles:</strong> <?php echo htmlspecialchars($driver_stats_before['num_poles']); ?></p>
            </div>
        <?php endif; ?>

        <hr style="margin: 30px 0;">
        <h3>Insert New Race Record (to fire trigger)</h3>
        <form action="trigger_update_driver_stats.php" method="POST">
            <p><em>Ensure Race ID is unique and all other IDs exist. Sample data has IDs 1-10 for most entities.</em></p>
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
                    <label for="team_id">Team ID (driver raced for):</label>
                    <input type="number" id="team_id" name="team_id" value="1" required>
                </div>
                 <div>
                    <label for="winning_team_id">Winning Team ID (can be NULL):</label> <input type="number" id="winning_team_id" name="winning_team_id" value="1">
                </div>
                <hr style="grid-column: 1 / -1;"> <div>
                    <label for="driver_id_for_race">Driver ID (participating, for podium):</label>
                    <input type="number" id="driver_id_for_race" name="driver_id_for_race" value="<?php echo $driver_id_to_watch; ?>" required>
                </div>
                <div>
                    <label for="winning_driver_id_for_race">Winning Driver ID (for win count, can be NULL):</label>
                    <input type="number" id="winning_driver_id_for_race" name="winning_driver_id_for_race" value="<?php echo $driver_id_to_watch; ?>">
                </div>
                <div>
                    <label for="pole_position_driver_id_for_race">Pole Position Driver ID (can be NULL):</label>
                    <input type="number" id="pole_position_driver_id_for_race" name="pole_position_driver_id_for_race" value="<?php echo $driver_id_to_watch; ?>">
                </div>
                <div>
                    <label for="finishing_position">Finishing Position (of participating Driver ID):</label>
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
            <button type="submit">Insert Race & See Driver Trigger Effect</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="message <?php echo (strpos(strtolower($message), 'error') === false && strpos(strtolower($message), 'warning') === false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <?php if ($driver_stats_after): ?>
            <div class="stats-box" style="background-color: #d4edda;">
                 <h3>Stats for Driver ID <?php echo htmlspecialchars($driver_id_to_watch); ?> (<?php echo htmlspecialchars($driver_stats_after['driver_name']); ?>) - AFTER Insert:</h3>
                <p><strong>Total Wins:</strong> <?php echo htmlspecialchars($driver_stats_after['num_wins']); ?></p>
                <p><strong>Total Podiums:</strong> <?php echo htmlspecialchars($driver_stats_after['num_podiums']); ?></p>
                <p><strong>Total Poles:</strong> <?php echo htmlspecialchars($driver_stats_after['num_poles']); ?></p>
            </div>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message) && $driver_stats_before && !$error_message_page): ?>
             <p class="message error">Could not retrieve 'after' stats for driver. The driver might not have been affected as expected or an issue occurred.</p>
        <?php endif; ?>

        <p style="text-align: center;">
            <a href="index.php" class="back-link">Back to Main Page</a>
        </p>
    </div>
</body>
</html>