<?php

function generateUserFriendlyTicketID() {
    return "TICKET-" . date("Ymd-His") . "-" . substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 4);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Low';
    $service_affected = $_POST['service_affected'] ?? 'General Inquiry';
    if (empty($username) || empty($subject) || empty($description)) {
        $message = "Error: Username, Subject, and Description are required.";
    } else {
        try {
            $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

            $user_ticket_id = generateUserFriendlyTicketID();
            $newTicket = [
                'ticket_id'       => $user_ticket_id,
                'username'        => $username,
                'subject'         => $subject,
                'description'     => $description,
                'priority'        => $priority,
                'service_affected'=> $service_affected,
                'status'          => 'Open',
                'timestamp'       => new MongoDB\BSON\UTCDateTime(),
                'comments'        => []
            ];

            $databaseName = 'support_db';
            $collectionName = 'tickets';

            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert($newTicket);
            $result = $manager->executeBulkWrite("$databaseName.$collectionName", $bulk);

            if ($result->getInsertedCount() == 1) {
                $message = "Ticket submitted successfully! Your Ticket ID is <strong>" . htmlspecialchars($user_ticket_id) . "</strong>.";
            } else {
                $message = "Error: Could not submit the ticket. Please try again.";
            }

        } catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
            $message = "Failed to connect to MongoDB: Connection timed out. Error: " . $e->getMessage();
        } catch (MongoDB\Driver\Exception\Exception $e) {
            $message = "An error occurred with MongoDB: " . $e->getMessage();
        } catch (Throwable $t) {
            $message = "A general error occurred: " . $t->getMessage();
        }
    }
} else {
    header("Location: submit_ticket_form.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Submission Status</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        .message { font-size: 1.2em; margin-bottom: 20px; }
        .success { color: green; }
        .error { color: red; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($message)): ?>
            <p class="message <?php echo (strpos(strtolower($message), 'error') === false && strpos(strtolower($message), 'failed') === false) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>
        <p><a href="submit_ticket_form.php">Submit another ticket</a></p>
        <p><a href="index.php">Back to Main Page</a></p>
    </div>
</body>
</html>