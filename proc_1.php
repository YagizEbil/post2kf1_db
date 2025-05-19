<?php
// /Users/yagizebil/Developer/post2kf1_db/user/proc_1.php
<?php
require_once '../db_connect.php';

$results = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connect_mysql();
    
    if (isset($_POST['execute_proc'])) {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        
        // Prepare and execute the stored procedure
        $sql = "CALL get_user_orders(?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result) {
            $results = mysqli_fetch_all($result, MYSQLI_ASSOC);
            if (count($results) > 0) {
                $message = "Found " . count($results) . " orders for user '$username'";
            } else {
                $message = "No orders found for user '$username'";
            }
        } else {
            $message = "Error executing procedure: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
}

// Get list of users for dropdown
$conn = connect_mysql();
$users_query = "SELECT username FROM users";
$users_result = mysqli_query($conn, $users_query);
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Execute Procedure: get_user_orders</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin-top: 20px; padding: 10px; border: 1px solid #ddd; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .navigation { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="index.php">Home</a> | 
        <a href="support/ticket_list.php">Support Tickets</a>
    </div>

    <h1>Execute Stored Procedure: get_user_orders</h1>
    <p>This procedure retrieves all orders for a specific user.</p>
    
    <form method="post">
        <div>
            <label for="username">Username:</label>
            <select name="username" id="username" required>
                <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['username']; ?>"><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="execute_proc">Execute Procedure</button>
    </form>
    
    <?php if ($message): ?>
    <div class="result">
        <h3>Result:</h3>
        <p><?php echo $message; ?></p>
        
        <?php if (count($results) > 0): ?>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Status</th>
            </tr>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['order_date']; ?></td>
                <td><?php echo $row['status']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</body>
</html>