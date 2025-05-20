<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Stored Procedure: GetTeamWonRaces

// MySQL Connection Details
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password
$dbname = "F1_db";

$races_won = []; // To store results (array of rows)
$error_message = '';
$submitted_team_id = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['team_id']) && !empty(trim($_POST['team_id']))) {
        $submitted_team_id = trim($_POST['team_id']);

        if (filter_var($submitted_team_id, FILTER_VALIDATE_INT) === false) {
            $error_message = "Error: Team ID must be an integer.";
        } else {
            $teamId = (int)$submitted_team_id;

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                $error_message = "Connection failed: " . $conn->connect_error;
            } else {
                $sql = "CALL GetTeamWonRaces(?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("i", $teamId);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $races_won[] = $row; // Add each row to the array
                            }
                        } else {
                            $error_message = "No races found for Team ID: " . htmlspecialchars($teamId) . ". This team might not have any recorded wins with the current data, or the Team ID might be incorrect.";
                        }
                        $result->free();
                    } else {
                        $error_message = "Error executing stored procedure or getting results: " . $conn->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Error preparing stored procedure call: " . $conn->error;
                }
                $conn->close();
            }
        }
    } else {
        $error_message = "Please enter a Team ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Races Won by Team</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f4f4f8; color: #333; }
        .container { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 40px auto; }
        h2 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: bold; }
        input[type="number"] {
            width: calc(100% - 22px); padding: 10px; margin-bottom: 20px;
            border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        button {
            background-color: #007bff; color: white; padding: 12px 20px; border: none;
            border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease;
        }
        button:hover { background-color: #0056b3; }
        .results-table { margin-top: 25px; width: 100%; border-collapse: collapse; }
        .results-table th, .results-table td {
            border: 1px solid #ddd; padding: 10px; text-align: left;
        }
        .results-table th { background-color: #007bff; color: white; }
        .results-table tr:nth-child(even) { background-color: #f9f9f9; }
        .results-table tr:hover { background-color: #f1f1f1; }
        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .no-results { text-align: center; color: #777; font-size: 1.1em; padding: 15px; background-color: #e9ecef; border-radius: 4px;}
        .back-link { display: inline-block; margin-top: 20px; padding: 8px 15px; background-color: #6c757d; color: white; border-radius: 4px; text-decoration: none; }
        .back-link:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Execute Stored Procedure: Get Races Won by Team</h2>
        <p>This procedure lists all races won by a specific Team ID.</p>

        <form action="sp_get_team_races.php" method="POST">
            <div>
                <label for="team_id">Enter Team ID:</label>
                <input type="number" id="team_id" name="team_id" value="<?php echo htmlspecialchars($submitted_team_id); ?>" required>
            </div>
            <button type="submit">Get Races Won</button>
        </form>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($races_won)): ?>
            <h3>Races Won by Team ID: <?php echo htmlspecialchars($submitted_team_id); ?></h3>
            <table class="results-table">
                <thead>
                    <tr>
                        <th>Race ID</th>
                        <th>Circuit Name</th>
                        <th>Race Date</th>
                        <th>Winning Driver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($races_won as $race): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($race['race_id']); ?></td>
                            <td><?php echo htmlspecialchars($race['circuit_name']); ?></td>
                            <td><?php echo htmlspecialchars($race['race_date']); ?></td>
                            <td><?php echo htmlspecialchars($race['winning_driver']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (empty($error_message) && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['team_id'])): ?>
            <p class="no-results">No races found for Team ID: <?php echo htmlspecialchars($submitted_team_id); ?>. This team might not have any recorded wins with the current data, or the Team ID might be incorrect.</p>
        <?php endif; ?>

        <p style="text-align: center;">
            <a href="index.php" class="back-link">Back to Main Page</a>
        </p>
    </div>
</body>
</html>