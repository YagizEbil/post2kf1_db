<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Support Ticket</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        label { display: block; margin-top: 10px; margin-bottom: 5px; color: #555; }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea { resize: vertical; min-height: 100px; }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Submit a New Support Ticket</h2>
        <form action="process_ticket.php" method="POST">
            <div>
                <label for="username">Your Username:</label> 
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div>
                <label for="description">Description (Body):</label> 
                <textarea id="description" name="description" required></textarea>
            </div>
            <div>
                <label for="priority">Priority:</label>
                <select id="priority" name="priority" required>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div>
                <label for="service_affected">Affected Service:</label>
                <select id="service_affected" name="service_affected" required>
                    <option value="General Inquiry">General Inquiry</option>
                    <option value="F1 Data">F1 Data Query</option>
                    <option value="Account Issue">Account Issue</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <button type="submit">Submit Ticket</button>
        </form>
    </div>
</body>
</html>