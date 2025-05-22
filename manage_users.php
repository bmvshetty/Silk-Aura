<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "silk_aura";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle deletion
if (isset($_GET['delete_user_id'])) {
    $delete_user_id = intval($_GET['delete_user_id']);
    $conn->query("UPDATE users SET status = 'deleted' WHERE id = $delete_user_id");
}

// Fetch all non-deleted users
$query = "SELECT * FROM users WHERE user_type = 'Seller' AND status != 'deleted'";
$result = $conn->query($query);
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SilkAura Admin - User Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        header {
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }
        .top-right-button {
            display: flex;
            justify-content: flex-end;
            padding: 20px 40px 0 0;
        }
        .top-right-button a {
            text-decoration: none;
        }
        .top-right-button button {
            padding: 10px 20px;
            background-color: #6c63ff;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
        }
        .container {
            width: 80%;
            margin: auto;
            padding-top: 30px;
        }
        .user-card {
            position: relative;
            background-color: #fff;
            padding: 20px 20px 20px 20px;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .user-card div.info-block {
            flex: 1;
            min-width: 200px;
            margin: 10px;
        }
        .user-card h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .user-card p {
            margin: 5px 0;
            color: #555;
        }

        .options-wrapper {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .ellipsis-btn {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: #555;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 30px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            min-width: 100px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 2;
        }

        .dropdown-menu button {
            background: none;
            border: none;
            color: #d00;
            padding: 10px;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 14px;
        }

        .dropdown-menu button:hover {
            background-color: #f8f8f8;
        }
    </style>
</head>
<body>
    <header>
        <h1>SilkAura Admin - User Management</h1>
    </header>

    <!-- 🔽 View Deleted Users Button -->
    <div class="top-right-button">
        <a href="release_users.php">
            <button>View Deleted Users</button>
        </a>
    </div>

    <div class="container">
        <h2>All Users</h2>

        <?php if (empty($users)) { ?>
            <p>No users found.</p>
        <?php } else { ?>
            <?php foreach ($users as $user) { ?>
                <div class="user-card">
                    <div class="options-wrapper">
                        <button class="ellipsis-btn" onclick="toggleDropdown(<?php echo $user['id']; ?>)">&#8942;</button>
                        <div class="dropdown-menu" id="dropdown-<?php echo $user['id']; ?>">
                            <button onclick="window.location.href='delete_users.php?user_id=<?php echo $user['id']; ?>'">Delete</button>
                        </div>
                    </div>
                    <div class="info-block">
                        <h3>ID: <?php echo $user['id']; ?></h3>
                        <p><strong>Name:</strong> <?php echo $user['name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                    </div>
                    <div class="info-block">
                        <p><strong>Shop Name:</strong> <?php echo $user['shop_name']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $user['phone']; ?></p>
                    </div>
                    <div class="info-block">
                        <p><strong>Address:</strong> <?php echo $user['address']; ?></p>
                        <p><strong>GST:</strong> <?php echo $user['gst']; ?></p>
                    </div>
                    <div class="info-block">
                        <p><strong>Created At:</strong> <?php echo $user['created_at']; ?></p>
                        <p><strong>Role:</strong> <?php echo $user['user_type']; ?></p>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>

    <script>
        function toggleDropdown(userId) {
            const menu = document.getElementById('dropdown-' + userId);
            document.querySelectorAll('.dropdown-menu').forEach(el => {
                if (el !== menu) el.style.display = 'none';
            });
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.options-wrapper')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html>
