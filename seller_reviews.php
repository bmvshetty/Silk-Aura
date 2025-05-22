<?php
session_start();
include 'db_config.php';
$seller_id = $_SESSION['user_id'];
$user = $_SESSION['user_id'];

$sql = "SELECT r.rating, r.review_text, r.review_date, u.name AS buyer_name, s.shop_name
        FROM reviews r
        JOIN users u ON r.buyer_id = u.id
        JOIN shops s ON r.shop_id = s.shop_id
        WHERE s.user_id = ?
        ORDER BY r.review_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shop Reviews</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #f9f9f9, #ffe3dc);
            color: #000;
            display: flex;
            flex-direction: column;
        }

        .top-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 40px;
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            font-size: 28px;
            font-weight: bold;
            color: white;
            letter-spacing: 2px;
        }

        .shop-header {
            background-color: #fff;
            padding: 25px 20px;
            margin: 30px auto 10px auto;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }

        h2 {
            margin: 0;
            font-size: 26px;
            color: #000;
        }

        .review-container {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
            padding: 20px;
            max-width: 1200px;
            margin: auto;
            flex: 1;
        }

        .review-card {
            background: linear-gradient(to bottom right, #ffffff, #fff7f5);
            width: 320px;
            border-radius: 16px;
            box-shadow: 0 10px 18px rgba(0,0,0,0.06);
            padding: 24px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: #000;
        }

        .review-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.1);
        }

        .review-card h4,
        .review-card .shop-name,
        .review-card .review-text,
        .review-card small {
            font-size: 16px;
            font-weight: 600;
            color: #000;
            margin-bottom: 10px;
        }

        .review-card h4 {
            font-size: 18px;
        }

        .review-card .rating {
            margin: 8px 0;
            font-size: 16px;
            font-weight: 600;
            color: #000;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .review-card .stars {
            color: gold;
            font-size: 18px;
        }

        p.no-reviews {
            text-align: center;
            color: #000;
            font-size: 18px;
            margin-top: 40px;
        }

        footer {
    text-align: center;
    padding: 10px;
    color: #000;
    background: none;
}

    </style>
</head>
<body>
    <div class="top-container">SILK AURA</div>

    <div class="shop-header">
        <h2>Reviews from Buyers</h2>
    </div>

    <div class="review-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rating = (int)$row['rating'];
                $stars = str_repeat("★", $rating) . str_repeat("☆", 5 - $rating);
                echo "<div class='review-card'>";
                echo "<h4>Buyer: " . htmlspecialchars($row['buyer_name']) . "</h4>";
                echo "<div class='shop-name'>Shop: " . htmlspecialchars($row['shop_name']) . "</div>";
                echo "<div class='rating'>Rating: {$rating} / 5 <span class='stars'>{$stars}</span></div>";
                echo "<div class='review-text'>" . nl2br(htmlspecialchars($row['review_text'])) . "</div>";
                echo "<small>Reviewed on " . htmlspecialchars($row['review_date']) . "</small>";
                echo "</div>";
            }
        } else {
            echo "<p class='no-reviews'>No reviews have been submitted yet.</p>";
        }
        ?>
    </div>

    <footer>
        <p>@Silk Aura</p>
    </footer>
</body>
</html>