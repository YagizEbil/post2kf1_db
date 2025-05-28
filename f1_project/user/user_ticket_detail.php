<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$ticket_id_str = $_GET['id'] ?? null;
$ticket = null;
$message = ''; 
$error_message = '';

if (!$ticket_id_str) {
    header("Location: user_ticket_list.php");
    exit;
}

$dbname_mongo = "support_db";
$collectionName = "tickets";
$manager = null;

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $object_id = new MongoDB\BSON\ObjectID($ticket_id_str);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
        $commenter_username = trim($_POST['commenter_username'] ?? 'Anonymous');
        $comment_text = trim($_POST['comment_text'] ?? '');

        if (empty($commenter_username) || empty($comment_text)) {
            $error_message = "Username and comment text cannot be empty.";
        } else {
            $new_comment = [
                'username' => $commenter_username,
                'comment_text' => $comment_text,
                'commented_at' => new MongoDB\BSON\UTCDateTime()
            ];

            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update(
                ['_id' => $object_id],
                ['$push' => ['comments' => $new_comment]],
                ['multi' => false, 'upsert' => false]
            );
            $result = $manager->executeBulkWrite("$dbname_mongo.$collectionName", $bulk);

            if ($result->getModifiedCount() == 1) {
                $message = "Comment added successfully!";
            } else {
                $error_message = "Could not add comment. Please try again.";
            }
        }
    }

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
    $error_message = "Invalid Ticket ID format. Error: " . $e->getMessage();
    $ticket = null;
} catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    $error_message = "Failed to connect to MongoDB: Connection timed out. Error: " . $e->getMessage();
    $ticket = null;
} catch (MongoDB\Driver\Exception\Exception $e) {
    $error_message = "An error occurred with MongoDB: " . $e->getMessage();
    $ticket = null;
} catch (Throwable $t) {
    $error_message = "A general error occurred: " . $t->getMessage();
    $ticket = null;
}

function formatMongoTimestamp($mongoTimestamp, $timezone = 'Europe/Istanbul') {
    if (isset($mongoTimestamp) && $mongoTimestamp instanceof MongoDB\BSON\UTCDateTime) {
        $dateTime = $mongoTimestamp->toDateTime();
        try {
            $dateTime->setTimezone(new DateTimeZone($timezone));
            return htmlspecialchars($dateTime->format('Y-m-d H:i:s T'));
        } catch (Exception $e) {
            return 'Invalid Date';
        }
    }
    return 'N/A';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details</title>
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

        .add-comment-form label { display: block; margin-top: 10px; margin-bottom: 5px; font-weight: bold; }
        .add-comment-form input[type="text"], .add-comment-form textarea {
            width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
        }
        .add-comment-form textarea { min-height: 80px; resize: vertical; }
        .add-comment-form button {
            background-color: #007bff; color: white; padding: 10px 18px; border: none;
            border-radius: 4px; cursor: pointer; font-size: 1em; transition: background-color 0.2s;
        }
        .add-comment-form button:hover { background-color: #0056b3; }
        
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
        <h2>Ticket Details</h2>

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
                <?php if (!empty($ticket->comments) && is_array($ticket->comments) || $ticket->comments instanceof MongoDB\Model\BSONArray ): ?>
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

            <div class="add-comment-form">
                <h3>Add Your Comment</h3>
                <form action="user_ticket_detail.php?id=<?php echo htmlspecialchars($ticket_id_str); ?>" method="POST">
                    <div>
                        <label for="commenter_username">Your Username:</label>
                        <input type="text" id="commenter_username" name="commenter_username" value="<?php echo htmlspecialchars($ticket->username ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="comment_text">Comment:</label>
                        <textarea id="comment_text" name="comment_text" required></textarea>
                    </div>
                    <button type="submit" name="add_comment">Add Comment</button>
                </form>
            </div>

        <?php elseif (!$error_message): ?>
            <p>Loading ticket information...</p>
        <?php endif; ?>

        <div class="action-links">
            <a href="user_ticket_list.php<?php echo isset($ticket->username) ? '?username=' . htmlspecialchars($ticket->username) : ''; ?>">Back to My Ticket List</a>
            <a href="index.php">Back to Main Page</a>
        </div>
    </div>
</body>
</html>