<?php
// /Users/yagizebil/Developer/post2kf1_db/db_connect.php
<?php
require_once 'config.php';

// MySQL connection function
function connect_mysql() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $conn;
}

// MongoDB connection function
function connect_mongodb() {
    try {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
        return $manager;
    } catch (Exception $e) {
        die("MongoDB Connection Error: " . $e->getMessage());
    }
}
?>