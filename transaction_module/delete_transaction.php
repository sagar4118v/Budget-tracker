<?php
include 'db.php';

if(isset($_GET['id'])) {

    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM transactions WHERE transaction_id=?");

    $stmt->bind_param("i", $id);

    if($stmt->execute()) {
        header("Location: view_transaction.php");
    } else {
        echo "Delete Failed";
    }
}
?>