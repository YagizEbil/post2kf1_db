<?php
// Stored Procedure: GetDriverCareerSummary

// MySQL Connection Details
$servername = "localhost"; // Default for XAMPP
$username = "root";        // Default XAMPP username
$password = "";            // Default XAMPP password (empty)
$dbname = "F1_db";         // Your F1 database name

$driver_summary = null; // To store results from the SP
$error_message = '';    // To store any error messages
$submitted_driver_id = ''; // To keep the submitted driver ID in the form

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['driver_id']) && !empty(trim($_POST['driver_id']))) {
        $submitted_driver_id = trim($_POST['driver_id']);

        // Validate if driver_id is an integer
        if (filter_var($submitted_driver_id, FILTER_VALIDATE_INT) === false) {
            $error_message = "Error: Driver ID must be an integer.";
        } else {
            $driverId = (int)$submitted_driver_id;

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                $error_message = "Connection failed: " . $conn->connect_error;
            } else {
                // Prepare the stored procedure call
                // Using prepared statements is good practice to prevent SQL injection,
                // even with stored procedures if inputs are involved in dynamic SQL within them (though less common for SP calls).
                // For calling a simple SP like this, a direct query is often used.
                $sql = "CALL GetDriverCareerSummary(?)"; // Using a placeholder for the driverId

                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("i", $driverId); // "i" signifies the type is integer
                    $stmt->execute();
                    $result = $stmt->get_result(); // Get the result set from the executed statement

                    if ($result) {
                        if ($result->num_rows > 0) {
                            $driver_summary = $result->fetch_assoc(); // Fetch the single row of results
                        } else {
                            $error_message = "No career summary found for Driver ID: " . htmlspecialchars($driverId);
                        }
                        $result->free(); // Free the result set
                    } else {
                        $error_message = "Error executing stored procedure or getting results: " . $conn->error;
                    }
                    $stmt->close(); // Close the statement
                } else {
                    $error_message = "Error preparing stored procedure call: " . $conn->error;
                }
                $conn->close(); // Close the connection
            }
        }
    } else {
        $error_message = "Please enter a Driver ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Driver Career Summary</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f4f4f8; color: #333; }
        .container { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 700px; margin: 40px auto; }
        h2 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] {
            width: calc(100% - 22px); /* Account for padding and border */
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff; color: white; padding: 12px 20px; border: none;
            border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease;
        }
        button:hover { background-color: #0056b3; }
        .results { margin-top: 25px; padding: 15px; background-color: #e9ecef; border-radius: 4px; border: 1px solid #ced4da;}
        .results h3 { margin-top: 0; color: #0056b3; }
        .results p { margin: 8px 0; font-size: 1.05em; }
        .results strong { color: #343a40; }
        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .back-link { display: inline-block; margin-top: 20px; padding: 8px 15px; background-color: #6c757d; color: white; border-radius: 4px; text-decoration: none; }
        .back-link:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Execute Stored Procedure: Get Driver Career Summary</h2>
        <p>This procedure fetches the career summary for a given Driver ID.</p>

        <form action="sp_get_driver_summary.php" method="POST">
            <div>
                <label for="driver_id">Enter Driver ID:</label>
                <input type="number" id="driver_id" name="driver_id" value="<?php echo htmlspecialchars($submitted_driver_id); ?>" required>
            </div>
            <button type="submit">Get Summary</button>
        </form>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if ($driver_summary): ?>
            <div class="results">
                <h3>Career Summary for Driver ID: <?php echo htmlspecialchars($submitted_driver_id); ?></h3>
                <p><strong>Driver Name:</strong> <?php echo htmlspecialchars($driver_summary['driver_name']); ?></p>
                <p><strong>Driver Number:</strong> <?php echo htmlspecialchars($driver_summary['driver_number']); ?></p>
                <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($driver_summary['date_of_birth']); ?></p>
                <p><strong>Number of Wins:</strong> <?php echo htmlspecialchars($driver_summary['num_wins']); ?></p>
                <p><strong>Number of Podiums:</strong> <?php echo htmlspecialchars($driver_summary['num_podiums']); ?></p>
                <p><strong>Number of Championships:</strong> <?php echo htmlspecialchars($driver_summary['num_championships']); ?></p>
                <p><strong>Number of Poles:</strong> <?php echo htmlspecialchars($driver_summary['num_poles']); ?></p>
            </div>
        <?php endif; ?>

        <p style="text-align: center;">
            <a href="index.php" class="back-link">Back to Main Page</a>
        </p>
    </div>
</body>
</html>