<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Project Main Page</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f8;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            width: 80%;
            max-width: 700px;
        }
        h1 {
            color: #d90429;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            font-size: 2.2em;
        }
        h2 {
            color: #333;
            margin-top: 35px;
            margin-bottom: 18px;
            font-size: 1.6em;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            margin-bottom: 14px;
        }
        ul li a {
            display: block;
            background-color: #007bff;
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-size: 1.1em;
            font-weight: 500;
        }
        ul li a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .mysql-features a {
        }
        .mongodb-features a {
            background-color: #28a745;
        }
        .mongodb-features a:hover {
            background-color: #218838;
        }
        .admin-link a {
            background-color: #6c757d;
        }
        .admin-link a:hover {
            background-color: #5a6268;
        }
        footer {
            margin-top: 40px;
            font-size: 0.9em;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>F1 Database Project Interface</h1>

        <h2>MySQL Database Features (F1_db)</h2>
        <ul class="mysql-features">
            <li><a href="trigger_update_team_stats.php">Demonstrate 'UpdateTeamStats' Trigger</a></li>
            <li><a href="trigger_update_driver_stats.php">Demonstrate 'UpdateDriverStats' Trigger</a></li>
            <li><a href="trigger_update_car_stats.php">Demonstrate 'UpdateCarStats' Trigger</a></li>
        </ul>
        <ul class="mysql-features" style="margin-top: 20px;"> 
            <li><a href="sp_get_driver_summary.php">Execute 'GetDriverCareerSummary' Procedure</a></li>
            <li><a href="sp_get_team_races.php">Execute 'GetTeamWonRaces' Procedure</a></li>
            <li><a href="sp_get_team_standings.php">Execute 'GetTeamStandings' Procedure</a></li>
        </ul>

        <h2>NoSQL Support System (MongoDB)</h2>
        <ul class="mongodb-features">
            <li><a href="submit_ticket_form.php">Submit a New Support Ticket</a></li>
            <li><a href="user_ticket_list.php">View My Support Tickets</a></li> 
            <li class="admin-link" style="margin-top: 20px;"><a href="../admin/index.php">Admin: View/Manage Tickets</a></li> 
        </ul>

        <footer>
            <p>CS306 Project Phase III - Web Integrations</p>
        </footer>
    </div>
</body>
</html>
