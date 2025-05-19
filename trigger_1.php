<?php
// /Users/yagizebil/Developer/post2kf1_db/user/trigger_1.php
<?php
require_once '../db_connect.php';

$message = '';
$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connect_mysql();
    
    if (isset($_POST['test_trigger'])) {
        // Get user ID for the order
        $user_id = (int)$_POST['user_id'];
        
        // Insert a new order to trigger the after_order_insert trigger
        $sql = "INSERT INTO orders (user_id, status) VALUES (?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($conn);
            $message = "Order #$order_id created successfully and trigger fired!";
            
            // Query to see the result of the trigger
            $result_query = "SELECT * FROM order_logs WHERE order_id = $order_id";
            $result = mysqli_query($conn, $result_query);
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    mysqli_close($conn);
}

// Get list of users for dropdown
$conn = connect_mysql();
$users_query = "SELECT id, username FROM users";
$users_result = mysqli_query($conn, $users_query);
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Trigger: after_order_insert</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin-top: 20px; padding: 10px; border: 1px solid #ddd; }
        .navigation { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="index.php">Home</a> | 
        <a href="support/ticket_list.php">Support Tickets</a>
    </div>

    <h1>Test Trigger: after_order_insert</h1>
    <p>This trigger logs new order creation in the system.</p>
    
    <form method="post">
        <div>
            <label for="user_id">Select User:</label>
            <select name="user_id" id="user_id" required>
                <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="test_trigger">Create Order (Fire Trigger)</button>
    </form>
    
    <?php if ($message): ?>
    <div class="result">
        <h3>Result:</h3>
        <p><?php echo $message; ?></p>
        
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <h4>Trigger Output:</h4>
        <table border="1">
            <tr>
                <th>Log ID</th>
                <th>Order ID</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['order_id']; ?></td>
                <td><?php echo $row['action']; ?></td>
                <td><?php echo $row['action_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</body>
</html>