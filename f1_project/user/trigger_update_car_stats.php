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

function getCarStats($conn_obj, $carId) {
    $stmt = $conn_obj->prepare("SELECT car_name, wins, podiums, poles FROM Car WHERE car_id = ?");
    if (!$stmt) {
        return "Error preparing car stats query: " . $conn_obj->error;
    }
    $stmt->bind_param("i", $carId);
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
$car_id_to_watch = 1;
$car_stats_before = null;
$car_stats_after = null;

$car_stats_before = getCarStats($conn, $car_id_to_watch);
if (is_string($car_stats_before)) {
    $error_message_page = $car_stats_before;
    $car_stats_before = null;
} elseif (!$car_stats_before) {
    $error_message_page = "Car ID " . htmlspecialchars($car_id_to_watch) . " not found for 'before' stats.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $race_id = filter_input(INPUT_POST, 'race_id', FILTER_VALIDATE_INT);
    $car_id_for_race = filter_input(INPUT_POST, 'car_id_for_race', FILTER_VALIDATE_INT);
    
    $circuit_id = filter_input(INPUT_POST, 'circuit_id', FILTER_VALIDATE_INT);
    $driver_id = filter_input(INPUT_POST, 'driver_id', FILTER_VALIDATE_INT);
    $team_id = filter_input(INPUT_POST, 'team_id', FILTER_VALIDATE_INT);
    $race_date = $_POST['race_date'] ?? date('Y-m-d');
    
    $winning_driver_id = filter_input(INPUT_POST, 'winning_driver_id', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'null_on_failure' => true]]);
    $pole_position_driver_id = filter_input(INPUT_POST, 'pole_position_driver_id', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'null_on_failure' => true]]);
    $finishing_position = filter_input(INPUT_POST, 'finishing_position', FILTER_VALIDATE_INT);
    
    $grid_position = filter_input(INPUT_POST, 'grid_position', FILTER_VALIDATE_INT, ['options' => ['default' => 1]]);
    $winning_team_id = filter_input(INPUT_POST, 'winning_team_id', FILTER_VALIDATE_INT, ['options' => ['default' => null, 'null_on_failure' => true]]);
    $fastest_lap_driver_id = null;

    if ($car_id_for_race) {
        $car_id_to_watch = $car_id_for_race;
    }
    $car_stats_before = getCarStats($conn, $car_id_to_watch);
     if (is_string($car_stats_before)) {
        $message = $car_stats_before;
        $car_stats_before = null;
    } elseif (!$car_stats_before && $car_id_to_watch) {
        $message = "Warning: Car ID " . htmlspecialchars($car_id_to_watch) . " (from form) not found.";
    }


    if ($race_id === false || $car_id_for_race === false || $circuit_id === false || $driver_id === false ||
        $team_id === false || $finishing_position === false || $grid_position === false) {
        $message = "Error: Race ID, Car ID for Race, Circuit ID, Driver ID, Team ID, Finishing Position, and Grid Position must be valid integers.";
    } else {
        $stmt_insert_race = $conn->prepare("INSERT INTO Race (race_id, circuit_id, car_id, driver_id, team_id, race_date, winning_team_id, winning_driver_id, pole_position_driver_id, fastest_lap_driver_id, grid_position, finishing_position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_insert_race) {
            $message = "Error preparing race insert query: " . $conn->error;
        } else {
            $stmt_insert_race->bind_param("iiiiisisiiii",
                $race_id, $circuit_id, $car_id_for_race, $driver_id, $team_id, $race_date,
                $winning_team_id, $winning_driver_id, $pole_position_driver_id,
                $fastest_lap_driver_id, $grid_position, $finishing_position
            );

            if ($stmt_insert_race->execute()) {
                $message = "New race record (ID: " . htmlspecialchars($race_id) . ") inserted successfully! Trigger 'UpdateCarStats' should have fired for Car ID " . htmlspecialchars($car_id_for_race) . ".";
                $car_stats_after = getCarStats($conn, $car_id_to_watch);
                if (is_string($car_stats_after)) {
                     $message .= "<br>Error fetching 'after' stats: " . $car_stats_after;
                     $car_stats_after = null;
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
    <title>Demonstrate 'UpdateCarStats' Trigger</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f4f4f8; color: #333; }
        .container { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 30px auto; }
        h2, h3 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        label { display: block; margin-top: 12px; margin-bottom: 5px; font-weight: bold; }
        input[type="number"], input[type="date"] {
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
        <h2>Demonstrate 'UpdateCarStats' Trigger</h2>
        <p>This trigger fires after a new race is inserted. It updates wins, poles, and podiums for the car specified in the race entry based on race results.</p>
        <p>We will monitor Car ID: <strong><?php echo htmlspecialchars($car_id_to_watch); ?></strong> (<?php echo htmlspecialchars($car_stats_before['car_name'] ?? 'Car not found or error'); ?>) for changes.</p>

        <?php if ($error_message_page): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message_page); ?></p>
        <?php endif; ?>

        <?php if ($car_stats_before && !is_string($car_stats_before)): ?>
            <div class="stats-box">
                <h3>Stats for Car ID <?php echo htmlspecialchars($car_id_to_watch); ?> (<?php echo htmlspecialchars($car_stats_before['car_name']); ?>) - BEFORE Insert:</h3>
                <p><strong>Total Wins:</strong> <?php echo htmlspecialchars($car_stats_before['wins']); ?></p>
                <p><strong>Total Podiums:</strong> <?php echo htmlspecialchars($car_stats_before['podiums']); ?></p>
                <p><strong>Total Poles:</strong> <?php echo htmlspecialchars($car_stats_before['poles']); ?></p>
            </div>
        <?php endif; ?>

        <hr style="margin: 30px 0;">
        <h3>Insert New Race Record (to fire trigger)</h3>
        <form action="trigger_update_car_stats.php" method="POST">
            <p><em>Ensure Race ID is unique. The 'Car ID for Race' is the car whose stats will be updated. Other IDs must exist.</em></p>
            <div class="form-grid">
                <div>
                    <label for="race_id">New Race ID (Unique):</label>
                    <input type="number" id="race_id" name="race_id" required>
                </div>
                <div>
                    <label for="car_id_for_race">Car ID for Race (this car's stats will be updated):</label>
                    <input type="number" id="car_id_for_race" name="car_id_for_race" value="<?php echo $car_id_to_watch; ?>" required>
                </div>
                 <div>
                    <label for="circuit_id">Circuit ID:</label>
                    <input type="number" id="circuit_id" name="circuit_id" value="1" required>
                </div>
                <div>
                    <label for="driver_id">Driver ID (driving this car):</label>
                    <input type="number" id="driver_id" name="driver_id" value="1" required>
                </div>
                <div>
                    <label for="team_id">Team ID (of this car/driver):</label>
                    <input type="number" id="team_id" name="team_id" value="1" required>
                </div>
                <hr style="grid-column: 1 / -1;">
                <div>
                    <label for="winning_driver_id">Winning Driver ID (if car won, can be NULL):</label>
                    <input type="number" id="winning_driver_id" name="winning_driver_id" value="1">
                </div>
                 <div>
                    <label for="winning_team_id">Winning Team ID (can be NULL):</label>
                    <input type="number" id="winning_team_id" name="winning_team_id" value="1">
                </div>
                <div>
                    <label for="pole_position_driver_id">Pole Position Driver ID (if car got pole, can be NULL):</label>
                    <input type="number" id="pole_position_driver_id" name="pole_position_driver_id" value="1">
                </div>
                <div>
                    <label for="finishing_position">Finishing Position (for this car):</label>
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
            <button type="submit">Insert Race & See Car Trigger Effect</button>
        </form>

        <?php if (!empty($message)): ?>
            <p class="message <?php echo (strpos(strtolower($message), 'error') === false && strpos(strtolower($message), 'warning') === false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <?php if ($car_stats_after): ?>
            <div class="stats-box" style="background-color: #d4edda;">
                 <h3>Stats for Car ID <?php echo htmlspecialchars($car_id_to_watch); ?> (<?php echo htmlspecialchars($car_stats_after['car_name']); ?>) - AFTER Insert:</h3>
                <p><strong>Total Wins:</strong> <?php echo htmlspecialchars($car_stats_after['wins']); ?></p>
                <p><strong>Total Podiums:</strong> <?php echo htmlspecialchars($car_stats_after['podiums']); ?></p>
                <p><strong>Total Poles:</strong> <?php echo htmlspecialchars($car_stats_after['poles']); ?></p>
            </div>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message) && $car_stats_before && !$error_message_page): ?>
             <p class="message error">Could not retrieve 'after' stats for car. The car might not have been affected as expected or an issue occurred.</p>
        <?php endif; ?>

        <p style="text-align: center;">
            <a href="index.php" class="back-link">Back to Main Page</a>
        </p>
    </div>
</body>
</html>