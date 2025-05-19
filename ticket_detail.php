<?php
// /Users/yagizebil/Developer/post2kf1_db/user/support/ticket_detail.php
<?php
require_once '../../db_connect.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ticket_list.php');
    exit;
}

$ticket_id = $_GET['id'];
$message = '';
$ticket = null;

// Convert string ID to MongoDB ObjectId
try {
    $objId = new MongoDB\BSON\ObjectId($ticket_id);
} catch (Exception $e) {
    header('Location: ticket_list.php');
    exit;
}

$mongo = connect_mongodb();

// Process comment submission or ticket resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_comment']) && !empty($_POST['comment'])) {
        $comment = [
            'user' => $_POST['username'],
            'text' => $_POST['comment'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['_id' => $objId],
            ['$push' => ['comments' => $comment]]
        );
        
        try {
            $result = $mongo->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);
            if ($result->getModifiedCount() > 0) {
                $message = "Comment added successfully!";
            } else {
                $message = "Error: Failed to add comment.";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['resolve_ticket'])) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['_id' => $objId],
            ['$set' => ['status' => false]]
        );
        
        try {
            $result = $mongo->executeBulkWrite(MONGO_DB . '.' . MONGO_COLLECTION, $bulk);
            if ($result->getModifiedCount() > 0) {
                $message = "Ticket marked as resolved!";
            } else {
                $message = "Error: Failed to resolve ticket.";
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Get ticket details
$filter = ['_id' => $objId];
$query = new MongoDB\Driver\Query($filter);
$cursor = $mongo->executeQuery(MONGO_DB . '.' . MONGO_COLLECTION, $query);

foreach ($cursor as $document) {
    $ticket = $document;
    break;
}

if (!$ticket) {
    header('Location: ticket_list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Detail</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .ticket-detail { margin-top: 20px; border: 1px solid #ddd; padding: 15px; }
        .comment { border-bottom: 1px solid #eee; padding: 10px 0; }
        .comment-form { margin-top: 20px; }
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

    <h1>Ticket Detail</h1>
    
    <?php if ($message): ?>
    <div class="message">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
    
    <div class="ticket-detail">
        <h2>Ticket #<?php echo $ticket_id; ?></h2>
        <p><strong>User:</strong> <?php echo $ticket->username; ?></p>
        <p><strong>Created:</strong> <?php echo $ticket->created_at; ?></p>
        <p><strong>Status:</strong> <?php echo $ticket->status ? 'Open' : 'Resolved'; ?></p>
        <p><strong>Message:</strong> <?php echo $ticket->message; ?></p>
        
        <h3>Comments</h3>
        <?php if (empty($ticket->comments)): ?>
            <p>No comments yet.</p>
        <?php else: ?>
            <?php foreach ($ticket->comments as $comment): ?>
            <div class="comment">
                <p><strong><?php echo $comment->user; ?></strong> at <?php echo $comment->created_at; ?></p>
                <p><?php echo $comment->text; ?></p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if ($ticket->status): ?>
        <div class="comment-form">
            <h3>Add Comment</h3>
            <form method="post">
                <input type="hidden" name="username" value="<?php echo $ticket->username; ?>">
                <textarea name="comment" rows="4" style="width: 100%;" required></textarea>
                <div style="margin-top: 10px;">
                    <button type="submit" name="add_comment">Add Comment</button>
                    <button type="submit" name="resolve_ticket">Resolve Ticket</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>