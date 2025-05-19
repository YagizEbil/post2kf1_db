<?php
// /Users/yagizebil/Developer/post2kf1_db/user/index.php
<?php
require_once '../db_connect.php';

// Define our triggers and stored procedures with descriptions
$triggers = [
    [
        'id' => 1,
        'name' => 'after_order_insert',
        'description' => 'Logs new order creation in the system',
        'team_member' => 'John Smith'
    ]
    // Add more triggers as needed
];

$procedures = [
    [
        'id' => 1,
        'name' => 'get_user_orders',
        'description' => 'Retrieves all orders for a specific user',
        'team_member' => 'Jane Doe'
    ]
    // Add more procedures as needed
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Interaction</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .navigation { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="navigation">
        <a href="index.php">Home</a> | 
        <a href="support/ticket_list.php">Support Tickets</a>
    </div>

    <h1>Database Interaction System</h1>
    
    <div class="section">
        <h2>Triggers</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Team Member</th>
                <th>Action</th>
            </tr>
            <?php foreach ($triggers as $trigger): ?>
            <tr>
                <td><?php echo $trigger['name']; ?></td>
                <td><?php echo $trigger['description']; ?></td>
                <td><?php echo $trigger['team_member']; ?></td>
                <td><a href="trigger_<?php echo $trigger['id']; ?>.php">Test Trigger</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Stored Procedures</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Team Member</th>
                <th>Action</th>
            </tr>
            <?php foreach ($procedures as $proc): ?>
            <tr>
                <td><?php echo $proc['name']; ?></td>
                <td><?php echo $proc['description']; ?></td>
                <td><?php echo $proc['team_member']; ?></td>
                <td><a href="proc_<?php echo $proc['id']; ?>.php">Execute Procedure</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>