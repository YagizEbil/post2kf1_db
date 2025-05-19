<?php
// /Users/yagizebil/Developer/post2kf1_db/admin/index.php
<?php
require_once '../db_connect.php';

$results = [];

// Get all active tickets from MongoDB
$mongo = connect_mongodb();
$filter = ['status' => true]; // Only active tickets
$options = ['sort' => ['created_at' => -1]];

$query = new MongoDB\Driver\Query($filter, $options);
$cursor = $mongo->executeQuery(MONGO_DB . '.' . MONGO_COLLECTION, $query);

foreach ($cursor as $document) {
    $results[] = $document;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Support Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .ticket-list { margin-top: 20px; }
        .ticket { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .navigation { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="index.php">Admin Home</a>
    </div>

    <h1>Admin - All Active Support Tickets</h1>
    
    <div class="ticket-list">
        <?php if (empty($results)): ?>
            <p>No active tickets found.</p>
        <?php else: ?>
            <?php foreach ($results as $ticket): ?>
            <div class="ticket">
                <h3>Ticket ID: <?php echo (string)$ticket->_id; ?></h3>
                <p><strong>User:</strong> <?php echo $ticket->username; ?></p>
                <p><strong>Created:</strong> <?php echo $ticket->created_at; ?></p>
                <p><strong>Message:</strong> <?php echo $ticket->message; ?></p>
                <a href="ticket_detail.php?id=<?php echo (string)$ticket->_id; ?>">View Details</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>