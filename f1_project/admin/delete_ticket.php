<?php
session_start(); // Optional: for flash messages if you implement them

$ticket_id_str = $_GET['id'] ?? null;

if (!$ticket_id_str) {
    // If no ID is provided, redirect back.
    $_SESSION['error_message'] = "No Ticket ID provided for deletion."; // Optional flash message
    header("Location: admin_view_tickets.php");
    exit;
}

try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    $databaseName = 'support_db';
    $collectionName = 'tickets';

    // Convert string ID to MongoDB\BSON\ObjectID
    $object_id = new MongoDB\BSON\ObjectID($ticket_id_str);

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->delete(['_id' => $object_id], ['limit' => 1]); // Ensure only one document is deleted

    $result = $manager->executeBulkWrite("$databaseName.$collectionName", $bulk);

    if ($result->getDeletedCount() == 1) {
        $_SESSION['success_message'] = "Ticket deleted successfully!"; // Optional flash message
    } else {
        $_SESSION['error_message'] = "Could not delete ticket or ticket not found."; // Optional flash message
    }

} catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
    $_SESSION['error_message'] = "Invalid Ticket ID format. Error: " . $e->getMessage();
} catch (MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
    $_SESSION['error_message'] = "Failed to connect to MongoDB: Connection timed out. Error: " . $e->getMessage();
} catch (MongoDB\Driver\Exception\Exception $e) {
    $_SESSION['error_message'] = "An error occurred with MongoDB during deletion: " . $e->getMessage();
} catch (Throwable $t) {
    $_SESSION['error_message'] = "A general error occurred during deletion: " . $t->getMessage();
}

// Redirect back to the admin ticket list
header("Location: admin_view_tickets.php");
exit;
?>