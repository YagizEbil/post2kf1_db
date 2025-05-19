<?php
// /Users/yagizebil/Developer/post2kf1_db/user/support/ticket_create.php
<?php
require_once '../../db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['message'])) {
        $username = trim($_POST['username']);
        $ticket_message = trim($_POST['message']);
        
        if (empty($username) || empty($ticket_message)) {
            $message = "Error: Both username and message are required.";
        } else {
            // Create a new ticket document
            $ticket = [
                'username' => $username,
                'message' => $ticket_message,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => true,
                'comments' => []
            ];
            
            $mongo = connect_mongodb();
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert($ticket);
            
            try {
                $result = $mongo->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);
                if ($result->getInsertedCount() > 0) {
                    $message = "Ticket created successfully!";
                } else {
                    $message = "Error: Failed to create ticket.";
                }
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
            }
        }
    }
}

// Get list of users from MySQL
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
    <title>Create Support Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], textarea, select { width: 100%; padding: 8px; }
        textarea { height: 150px; }
        .navigation { margin-bottom: 20px; }
        .message { padding: 10px; margin: 10px 0; border: 1px solid #ddd; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="../index.php">Home</a> | 
        <a href="ticket_list.php">Support Tickets</a> | 
        <a href="ticket_create.php">Create New Ticket</a>
    </div>

    <h1>Create Support Ticket</h1>
    
    <?php if ($message): ?>
    <div class="message">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <form method="post">
        <div class="form-group">
            <label for="username">Username:</label>
            <select name="username" id="username" required>
                <option value="">-- Select Username --</option>
                <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['username']; ?>"><?php echo $user['username']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea name="message" id="message" required></textarea>
        </div>
        
        <button type="submit">Create Ticket</button>
    </form>
</body>
</html>