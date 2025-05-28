<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "F1_db";

$team_standings = [];
$error_message = '';

$allowed_sort_columns = [
    'team_name' => 'Team Name',
    'total_race_wins' => 'Total Race Wins',
    'num_constructor_championships' => 'Constructor Championships',
    'num_driver_championships' => 'Driver Championships'
];
$sort_column = $_GET['sort'] ?? 'total_race_wins';
$sort_order = $_GET['order'] ?? 'desc';

if (!array_key_exists($sort_column, $allowed_sort_columns)) {
    $sort_column = 'total_race_wins';
}
if (!in_array(strtolower($sort_order), ['asc', 'desc'])) {
    $sort_order = 'desc';
}
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $error_message = "Connection failed: " . $conn->connect_error;
} else {
    $sql = "CALL GetTeamStandings()";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $team_standings[] = $row;
            }
        } else {
            $error_message = "No team standings found.";
        }
        
        while ($conn->more_results() && $conn->next_result()) {
            if ($res = $conn->store_result()) {
                $res->free();
            }
        }
        if ($result instanceof mysqli_result) {
             $result->free();
        }
        if (!empty($team_standings) && (isset($_GET['sort']))) {
            usort($team_standings, function($a, $b) use ($sort_column, $sort_order) {
                $val_a = $a[$sort_column];
                $val_b = $b[$sort_column];

                if (is_numeric($val_a) && is_numeric($val_b)) {
                    $comparison = $val_a <=> $val_b;
                } else {
                    $comparison = strcasecmp((string)$val_a, (string)$val_b);
                }
                
                return ($sort_order == 'desc') ? -$comparison : $comparison;
            });
        }

    } else {
        $error_message = "Error executing 'GetTeamStandings' stored procedure: " . $conn->error;
    }
    $conn->close();
}

function get_sort_link($column_key, $column_display_name, $current_sort_column, $current_sort_order, $allowed_columns) {
    if (!array_key_exists($column_key, $allowed_columns)) {
        return $column_display_name;
    }
    $order_for_link = 'desc';
    $arrow = '';
    if ($current_sort_column == $column_key) {
        $order_for_link = ($current_sort_order == 'asc') ? 'desc' : 'asc';
        $arrow = ($current_sort_order == 'asc') ? ' &uarr;' : ' &darr;';
    }
    return '<a href="?sort=' . $column_key . '&order=' . $order_for_link . '">' . $column_display_name . $arrow . '</a>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Standings (Sortable)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f4f4f8; color: #333; }
        .container { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 900px; margin: 40px auto; }
        h2 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .results-table { margin-top: 20px; width: 100%; border-collapse: collapse; }
        .results-table th, .results-table td {
            border: 1px solid #ddd; padding: 10px; text-align: left;
        }
        .results-table th { background-color: #007bff; color: white; }
        .results-table th a { color: white; text-decoration: none; display: block; }
        .results-table th a:hover { text-decoration: underline; }
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
        <h2>Stored Procedure Results: Team Standings (Sortable)</h2>
        <p>Click on column headers to sort the table.</p>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($team_standings)): ?>
            <table class="results-table">
                <thead>
                    <tr>
                        <th><?php echo get_sort_link('team_name', 'Team Name', $sort_column, $sort_order, $allowed_sort_columns); ?></th>
                        <th><?php echo get_sort_link('total_race_wins', 'Total Race Wins', $sort_column, $sort_order, $allowed_sort_columns); ?></th>
                        <th><?php echo get_sort_link('num_constructor_championships', 'Constructor Championships', $sort_column, $sort_order, $allowed_sort_columns); ?></th>
                        <th><?php echo get_sort_link('num_driver_championships', 'Driver Championships', $sort_column, $sort_order, $allowed_sort_columns); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($team_standings as $team): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($team['team_name']); ?></td>
                            <td><?php echo htmlspecialchars($team['total_race_wins']); ?></td>
                            <td><?php echo htmlspecialchars($team['num_constructor_championships']); ?></td>
                            <td><?php echo htmlspecialchars($team['num_driver_championships']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (empty($error_message)): ?>
            <p class="no-results">No team standings found. Please ensure the 'Team' table contains data and the 'GetTeamStandings' procedure is correct.</p>
        <?php endif; ?>

        <p style="text-align: center;">
            <a href="index.php" class="back-link">Back to Main Page</a>
        </p>
    </div>
</body>
</html>