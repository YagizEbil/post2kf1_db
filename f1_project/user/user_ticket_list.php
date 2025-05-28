<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname_mongo = "support_db";
$collectionName = "tickets";

$distinct_usernames = [];
$user_tickets = [];
$selected_username = $_GET['username'] ?? null;
$error_message = '';

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

    $distinct_command = new MongoDB\Driver\Command([
        'distinct' => $collectionName,
        'key' => 'username',
        'query' => ['status' => ['$ne' => 'Closed']]
    ]);
    $cursor_usernames = $manager->executeCommand($dbname_mongo, $distinct_command);
    $distinct_usernames_result = $cursor_usernames->toArray();
    if (count($distinct_usernames_result) > 0 && isset($distinct_usernames_result[0]->values)) {
        $distinct_usernames = $distinct_usernames_result[0]->values;
        sort($distinct_usernames);
    }

    if ($selected_username) {
        $query_tickets = new MongoDB\Driver\Query(
            ['username' => $selected_username, 'status' => ['$ne' => 'Closed']],
            ['sort' => ['timestamp' => -1]]
        );
        $cursor_tickets = $manager->executeQuery("$dbname_mongo.$collectionName", $query_tickets);
        $user_tickets = $cursor_tickets->toArray();
    }

} catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    $error_message = "Failed to connect to MongoDB: Connection timed out. Please ensure MongoDB server is running. Error: " . $e->getMessage();
} catch (MongoDB\Driver\Exception\Exception $e) {
    $error_message = "An error occurred with MongoDB: " . $e->getMessage();
} catch (Throwable $t) {
    $error_message = "A general error occurred: " . $t->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Support Tickets</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f8; color: #333; }
        .page-container { max-width: 900px; margin: 30px auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .form-select-user { margin-bottom: 25px; padding: 15px; background-color: #e9ecef; border-radius: 5px; display: flex; align-items: center; gap: 10px; }
        .form-select-user label { font-weight: bold; margin-right: 5px; }
        .form-select-user select, .form-select-user button { padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 1em; }
        .form-select-user select { flex-grow: 1; max-width: 300px; }
        .form-select-user button { background-color: #007bff; color: white; cursor: pointer; border: none; }
        .form-select-user button:hover { background-color: #0056b3; }
        
        .ticket-list { list-style: none; padding: 0; }
        .ticket-item { background-color: #f9f9f9; border: 1px solid #eee; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .ticket-item strong { color: #555; }
        .ticket-item p { margin: 5px 0 10px 0; line-height: 1.6; }
        .ticket-meta { font-size: 0.9em; color: #777; margin-bottom: 10px; }
        .view-details-btn {
            display: inline-block; padding: 8px 15px; background-color: #28a745; color: white;
            text-decoration: none; border-radius: 4px; font-size: 0.9em; transition: background-color 0.2s;
        }
        .view-details-btn:hover { background-color: #218838; }
        
        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .no-tickets, .no-users { text-align: center; color: #777; font-size: 1.1em; padding: 15px; background-color: #e9ecef; border-radius: 4px;}
        .action-links { margin-top: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;}
        .action-links a {
            padding: 10px 18px; background-color: #17a2b8; color: white;
            text-decoration: none; border-radius: 4px; font-size: 1em; transition: background-color 0.2s;
        }
        .action-links a:hover { background-color: #138496; }
        .back-link-main { display: inline-block; margin-top: 20px; padding: 8px 15px; background-color: #6c757d; color: white; border-radius: 4px; text-decoration: none; text-align:center; }
        .back-link-main:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="page-container">
        <h2>My Support Tickets</h2>

        <div class="action-links">
            <a href="submit_ticket_form.php">Create a New Ticket</a>
            <a href="index.php" style="background-color: #6c757d;">Back to Main Page</a>
        </div>


        <form class="form-select-user" action="user_ticket_list.php" method="GET">
            <label for="username">Select Your Username:</label>
            <select id="username" name="username" required>
                <option value="">-- Select Username --</option>
                <?php foreach ($distinct_usernames as $uname): ?>
                    <option value="<?php echo htmlspecialchars($uname); ?>" <?php echo ($selected_username == $uname) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($uname); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View My Tickets</button>
        </form>

        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if ($selected_username): ?>
            <h3>Active Tickets for <?php echo htmlspecialchars($selected_username); ?></h3>
            <?php if (!empty($user_tickets)): ?>
                <ul class="ticket-list">
                    <?php foreach ($user_tickets as $ticket): ?>
                        <li class="ticket-item">
                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($ticket->subject ?? 'N/A'); ?></p>
                            <p><strong>Ticket ID:</strong> <?php echo htmlspecialchars($ticket->ticket_id ?? 'N/A'); ?></p>
                            <p class="ticket-meta">
                                <strong>Status:</strong> <?php echo htmlspecialchars($ticket->status ?? 'N/A'); ?> |
                                <strong>Priority:</strong> <?php echo htmlspecialchars($ticket->priority ?? 'N/A'); ?> |
                                <strong>Created:</strong>
                                <?php
                                if (isset($ticket->timestamp) && $ticket->timestamp instanceof MongoDB\BSON\UTCDateTime) {
                                    $dateTime = $ticket->timestamp->toDateTime();
                                    try {
                                        $dateTime->setTimezone(new DateTimeZone('Europe/Istanbul'));
                                        echo htmlspecialchars($dateTime->format('Y-m-d H:i:s'));
                                    } catch (Exception $e) { echo 'Invalid Date';}
                                } else { echo 'N/A'; }
                                ?>
                            </p>
                            <p><strong>Description Snippet:</strong> <?php echo nl2br(htmlspecialchars(substr($ticket->description ?? '', 0, 150) . (strlen($ticket->description ?? '') > 150 ? '...' : ''))); ?></p>
                            <a href="user_ticket_detail.php?id=<?php echo $ticket->_id; ?>" class="view-details-btn">View Details / Comment</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-tickets">You have no active support tickets.</p>
            <?php endif; ?>
        <?php elseif (empty($distinct_usernames) && empty($error_message)): ?>
             <p class="no-users">No users with active tickets found in the system.</p>
        <?php endif; ?>

    </div>
</body>
</html>