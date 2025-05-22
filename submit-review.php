<?php
session_start();
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_id = $_POST['shop_id'];
    $rating = intval($_POST['rating']);
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);
    $buyer_id = $_SESSION['user_id'];  // assuming you store logged-in buyer's id here

    // Basic validation
    if ($rating < 1 || $rating > 5 || empty($review_text)) {
        die('Invalid review input');
    }

    // Insert review into DB
    $sql = "INSERT INTO reviews (shop_id, buyer_id, rating, review_text, review_date)
            VALUES ('$shop_id', '$buyer_id', $rating, '$review_text', NOW())";

    if (mysqli_query($conn, $sql)) {
        // Redirect back to the buyer page, or wherever you want
        header("Location: buyersdisplay2.php?shop_id=$shop_id&review_submitted=1");
        exit;
    } else {
        echo "Error submitting review: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>
