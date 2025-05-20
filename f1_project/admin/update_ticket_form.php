<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Admin Update Ticket Status & Add Comment

$ticket_id_str = $_GET['id'] ?? null;
$ticket = null;
$message = ''; // For success messages
$error_message = ''; // For error messages
$possible_statuses = ['Open', 'In Progress', 'Pending User Response', 'Closed'];

if (!$ticket_id_str) {
    header("Location: admin_view_tickets.php");
    exit;
}

// MongoDB Connection
$dbname_mongo = "support_db";
$collectionName = "tickets";
$manager = null;

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $object_id = new MongoDB\BSON\ObjectID($ticket_id_str);

    // --- Handle ADMIN "Add Comment" Form Submission ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_add_comment'])) {
        $admin_comment_text = trim($_POST['admin_comment_text'] ?? '');

        if (empty($admin_comment_text)) {
            $error_message = "Admin comment text cannot be empty.";
        } else {
            $new_admin_comment = [
                'username' => 'admin', // Admin's username is fixed
                'comment_text' => $admin_comment_text,
                'commented_at' => new MongoDB\BSON\UTCDateTime()
            ];

            $bulk_comment = new MongoDB\Driver\BulkWrite;
            $bulk_comment->update(
                ['_id' => $object_id],
                ['$push' => ['comments' => $new_admin_comment]],
                ['multi' => false, 'upsert' => false]
            );
            $result_comment = $manager->executeBulkWrite("$dbname_mongo.$collectionName", $bulk_comment);

            if ($result_comment->getModifiedCount() == 1) {
                $message = "Admin comment added successfully!";
            } else {
                $error_message = "Could not add admin comment. Please try again.";
            }
        }
    }
    // --- Handle "Update Status" Form Submission ---
    elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) { // Check for 'update_status' submit
        $new_status = $_POST['status'] ?? null;

        if ($new_status && in_array($new_status, $possible_statuses)) {
            $bulk_status = new MongoDB\Driver\BulkWrite;
            $update_data = [
                '$set' => [
                    'status' => $new_status,
                    'last_updated_admin' => new MongoDB\BSON\UTCDateTime()
                ]
            ];
            $bulk_status->update(['_id' => $object_id], $update_data);
            $result_status = $manager->executeBulkWrite("$dbname_mongo.$collectionName", $bulk_status);

            if ($result_status->getModifiedCount() == 1) {
                $message = "Ticket status updated successfully to '" . htmlspecialchars($new_status) . "'!";
            } else if ($result_status->getMatchedCount() == 1 && $result_status->getModifiedCount() == 0) {
                 $message = "Ticket status is already '" . htmlspecialchars($new_status) . "'. No changes made.";
            } else {
                $error_message = "Could not update ticket status or ticket not found.";
            }
        } else {
            $error_message = "Invalid status selected.";
        }
    }

    // --- Fetch ticket details (always fetch to get latest state) ---
    $query = new MongoDB\Driver\Query(['_id' => $object_id]);
    $cursor = $manager->executeQuery("$dbname_mongo.$collectionName", $query);
    $ticket_array = $cursor->toArray();

    if (count($ticket_array) > 0) {
        $ticket = $ticket_array[0];
    } else {
        if (empty($error_message) && empty($message)) {
            $error_message = "Ticket not found with ID: " . htmlspecialchars($ticket_id_str);
        }
        $ticket = null;
    }

} catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
    $error_message = "Invalid Ticket ID format. Error: " . $e->getMessage(); $ticket = null;
} catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    $error_message = "Failed to connect to MongoDB: Connection timed out. Error: " . $e->getMessage(); $ticket = null;
} catch (MongoDB\Driver\Exception\Exception $e) {
    $error_message = "An error occurred with MongoDB: " . $e->getMessage(); $ticket = null;
} catch (Throwable $t) {
    $error_message = "A general error occurred: " . $t->getMessage(); $ticket = null;
}

function formatMongoTimestamp($mongoTimestamp, $timezone = 'Europe/Istanbul') {
    if (isset($mongoTimestamp) && $mongoTimestamp instanceof MongoDB\BSON\UTCDateTime) {
        $dateTime = $mongoTimestamp->toDateTime();
        try {
            $dateTime->setTimezone(new DateTimeZone($timezone));
            return htmlspecialchars($dateTime->format('Y-m-d H:i:s T'));
        } catch (Exception $e) { return 'Invalid Date'; }
    }
    return 'N/A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Update Ticket</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f4f8; color: #333; }
        .page-container { max-width: 800px; margin: 30px auto; background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .ticket-details-grid { display: grid; grid-template-columns: 150px 1fr; gap: 8px 15px; margin-bottom: 25px; padding: 15px; background-color: #f9f9f9; border: 1px solid #eee; border-radius: 5px;}
        .ticket-details-grid dt { font-weight: bold; color: #555; text-align: right; }
        .ticket-details-grid dd { margin: 0; word-break: break-word; }
        .ticket-details-grid .description { grid-column: 1 / -1; }
        .ticket-details-grid .description dt { margin-bottom: 5px; }
        .ticket-details-grid .description dd { white-space: pre-wrap; }
        
        .comments-section { margin-top: 30px; }
        .comment { background-color: #e9ecef; border: 1px solid #ddd; padding: 12px; margin-bottom: 10px; border-radius: 4px; }
        .comment-meta { font-size: 0.85em; color: #6c757d; margin-bottom: 5px; }
        .comment-meta strong { color: #495057; }
        .comment p { margin: 5px 0 0 0; line-height: 1.5; }
        .no-comments { color: #777; font-style: italic; }

        .form-section { margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc; }
        .form-section label { display: block; margin-top: 10px; margin-bottom: 5px; font-weight: bold; }
        .form-section select, .form-section textarea, .form-section button {
            width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .form-section textarea { min-height: 80px; resize: vertical; }
        .form-section button {
            background-color: #007bff; color: white; cursor: pointer; font-size: 1em; border: none;
        }
        .form-section button:hover { background-color: #0056b3; }
        
        .action-links { margin-top: 25px; display: flex; justify-content: flex-start; gap: 10px;}
        .action-links a {
            padding: 10px 18px; background-color: #6c757d; color: white;
            text-decoration: none; border-radius: 4px; font-size: 1em; transition: background-color 0.2s;
        }
        .action-links a:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="page-container">
        <h2>Admin - Ticket Details & Actions</h2>

        <?php if ($message): ?>
            <p class="message success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <p class="message error"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <?php if ($ticket): ?>
            <dl class="ticket-details-grid">
                <dt>Ticket ID:</dt><dd><?php echo htmlspecialchars($ticket->ticket_id ?? 'N/A'); ?></dd>
                <dt>Submitted By:</dt><dd><?php echo htmlspecialchars($ticket->username ?? 'N/A'); ?></dd>
                <dt>Subject:</dt><dd><?php echo htmlspecialchars($ticket->subject ?? 'N/A'); ?></dd>
                <dt>Priority:</dt><dd><?php echo htmlspecialchars($ticket->priority ?? 'N/A'); ?></dd>
                <dt>Service Affected:</dt><dd><?php echo htmlspecialchars($ticket->service_affected ?? 'N/A'); ?></dd>
                <dt>Status:</dt><dd><?php echo htmlspecialchars($ticket->status ?? 'N/A'); ?></dd>
                <dt>Created At:</dt><dd><?php echo formatMongoTimestamp($ticket->timestamp ?? null); ?></dd>
                <?php if (isset($ticket->last_updated_admin) && $ticket->last_updated_admin instanceof MongoDB\BSON\UTCDateTime): ?>
                    <dt>Last Admin Update:</dt><dd><?php echo formatMongoTimestamp($ticket->last_updated_admin); ?></dd>
                <?php endif; ?>
                <div class="description">
                    <dt>Description (Body):</dt>
                    <dd><?php echo nl2br(htmlspecialchars($ticket->description ?? 'N/A')); ?></dd>
                </div>
            </dl>

            <div class="comments-section">
                <h3>Comments</h3>
                <?php if (!empty($ticket->comments) && (is_array($ticket->comments) || $ticket->comments instanceof MongoDB\Model\BSONArray) && count((array)$ticket->comments) > 0 ): ?>
                    <?php foreach ($ticket->comments as $comment): ?>
                        <div class="comment">
                            <p class="comment-meta">
                                <strong><?php echo htmlspecialchars($comment->username ?? 'User'); ?></strong> commented at
                                <?php echo formatMongoTimestamp($comment->commented_at ?? null); ?>
                            </p>
                            <p><?php echo nl2br(htmlspecialchars($comment->comment_text ?? '')); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">No comments yet.</p>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <h3>Update Status</h3>
                <form action="update_ticket_form.php?id=<?php echo htmlspecialchars($ticket_id_str); ?>" method="POST">
                    <div>
                        <label for="status">Change Status To:</label>
                        <select id="status" name="status" required>
                            <?php foreach ($possible_statuses as $status_option): ?>
                                <option value="<?php echo htmlspecialchars($status_option); ?>" <?php echo (isset($ticket->status) && $ticket->status == $status_option) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status_option); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_status">Update Status</button>
                </form>
            </div>

            <div class="form-section"> 
                <h3>Admin: Add Comment</h3>
                <form action="update_ticket_form.php?id=<?php echo htmlspecialchars($ticket_id_str); ?>" method="POST">
                    <div>
                        <label for="admin_comment_text">Comment:</label>
                        <textarea id="admin_comment_text" name="admin_comment_text" required></textarea>
                    </div>
                    <button type="submit" name="admin_add_comment">Add Admin Comment</button>
                </form>
            </div>

        <?php elseif (!$error_message): ?>
            <p>Loading ticket information...</p>
        <?php endif; ?>

        <div class="action-links">
            <a href="admin_view_tickets.php">Back to Admin Ticket List</a>
            <a href="../user/index.php" style="margin-left: auto;">Back to Main Page</a>
        </div>
    </div>
</body>
</html>