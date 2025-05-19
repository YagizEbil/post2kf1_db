<?php
// /Users/yagizebil/Developer/post2kf1_db/user/support/ticket_list.php
<?php
require_once '../../db_connect.php';

$selected_username = '';
$results = [];

// Get list of users with tickets from MongoDB
$mongo = connect_mongodb();
$filter = ['status' => true]; // Only active tickets
$options = [
    'projection' => ['username' => 1],
    'sort' => ['created_at' => -1]
];

$query = new MongoDB\Driver\Query($filter, $options);
$cursor = $mongo->executeQuery(MONGO_DB . '.' . MONGO_COLLECTION, $query);

$usernames = [];
foreach ($cursor as $document) {
    if (!in_array($document->username, $usernames)) {
        $usernames[] = $document->username;
    }
}

// If a username is selected, get all tickets for that user
if (isset($_GET['username']) && !empty($_GET['username'])) {
    $selected_username = $_GET['username'];
    
    $filter = [
        'username' => $selected_username,
        'status' => true
    ];
    $options = ['sort' => ['created_at' => -1]];
    
    $query = new MongoDB\Driver\Query($filter, $options);
    $cursor = $mongo->executeQuery(MONGO_DB . '.' . MONGO_COLLECTION, $query);
    
    foreach ($cursor as $document) {
        $results[] = $document;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .ticket-list { margin-top: 20px; }
        .ticket { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .navigation { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="../index.php">Home</a> | 
        <a href="ticket_list.php">Support Tickets</a> | 
        <a href="ticket_create.php">Create New Ticket</a>
    </div>

    <h1>Support Tickets</h1>
    
    <form method="get">
        <label for="username">Select User:</label>
        <select name="username" id="username">
            <option value="">-- Select User --</option>
            <?php foreach ($usernames as $username): ?>
            <option value="<?php echo $username; ?>" <?php echo ($selected_username === $username) ? 'selected' : ''; ?>>
                <?php echo $username; ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Tickets</button>
    </form>
    
    <div class="ticket-list">
        <?php if (empty($results)): ?>
            <?php if ($selected_username): ?>
                <p>No active tickets found for user: <?php echo $selected_username; ?></p>
            <?php else: ?>
                <p>Please select a user to view their tickets.</p>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($results as $ticket): ?>
            <div class="ticket">
                <h3>Ticket ID: <?php echo (string)$ticket->_id; ?></h3>
                <p><strong>Created:</strong> <?php echo $ticket->created_at; ?></p>
                <p><strong>Status:</strong> <?php echo $ticket->status ? 'Open' : 'Resolved'; ?></p>
                <p><strong>Message:</strong> <?php echo $ticket->message; ?></p>
                <a href="ticket_detail.php?id=<?php echo (string)$ticket->_id; ?>">View Details</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>