<?php
session_start(); // Start session for flash messages

// Admin View Tickets
$tickets = [];
$error_message = '';

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $databaseName = 'support_db';
    $collectionName = 'tickets';
    $query = new MongoDB\Driver\Query([], ['sort' => ['timestamp' => -1]]); // Sort by newest first
    $cursor = $manager->executeQuery("$databaseName.$collectionName", $query);
    $tickets = $cursor->toArray();
} catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    $error_message = "Failed to connect to MongoDB: Connection timed out. Error: " . $e->getMessage();
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
    <title>Admin - View Support Tickets</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; word-break: break-word; }
        th { background-color: #007bff; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .error { color: red; font-weight: bold; padding: 10px; border: 1px solid red; background-color: #ffebeb; margin-bottom:15px; }
        .no-tickets { text-align: center; color: #777; font-size: 1.1em; padding: 20px; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .actions a {
            margin-right: 5px;
            display: inline-block;
            padding: 6px 12px; /* Adjusted padding */
            color: white;
            border-radius: 4px; /* Slightly more rounded */
            font-size: 0.9em;
            text-align: center;
            min-width: 60px; /* Ensure buttons have some minimum width */
        }
        .actions a.update-btn { background-color: #28a745; /* Green for update */ }
        .actions a.update-btn:hover { background-color: #218838; }
        .actions a.delete-btn { background-color: #dc3545; /* Red for delete */ }
        .actions a.delete-btn:hover { background-color: #c82333; }
        /* Flash messages styling */
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        /* .error class already defined, but ensure it's suitable for flash messages too */
    </style>
</head>
<body>
    <div class="container">
        <h2>Support Ticket Admin View</h2>

        <?php // Display Flash Messages
        if (isset($_SESSION['success_message'])) {
            echo '<p class="message success">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<p class="message error">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
            unset($_SESSION['error_message']);
        }
        ?>

        <?php if (!empty($error_message) && empty($tickets) ): // Show general connection error only if no tickets due to it ?>
            <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if (!empty($tickets)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th>Service Affected</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket->ticket_id ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ticket->subject ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ticket->priority ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ticket->service_affected ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ticket->status ?? 'N/A'); ?></td>
                            <td>
                                <?php
                                if (isset($ticket->timestamp) && $ticket->timestamp instanceof MongoDB\BSON\UTCDateTime) {
                                    $dateTime = $ticket->timestamp->toDateTime();
                                    try {
                                        $dateTime->setTimezone(new DateTimeZone('Europe/Istanbul'));
                                        echo htmlspecialchars($dateTime->format('Y-m-d H:i:s'));
                                    } catch (Exception $e) { echo 'Invalid Date'; }
                                } else { echo 'N/A'; }
                                ?>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($ticket->description ?? 'N/A')); ?></td>
                            <td class="actions">
                                <a href="update_ticket_form.php?id=<?php echo $ticket->_id; ?>" class="update-btn">Update</a>
                                <a href="delete_ticket.php?id=<?php echo $ticket->_id; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to permanently delete this ticket?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (empty($error_message)): // Only show "no tickets" if there wasn't a connection error ?>
            <p class="no-tickets">No support tickets found.</p>
        <?php endif; ?>
        <p style="margin-top: 20px;"><a href="../user/submit_ticket_form.php">Submit a New Ticket</a></p>
    </div>
</body>
</html>
