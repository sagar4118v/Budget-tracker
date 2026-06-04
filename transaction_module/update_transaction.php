<?php
include 'db.php';

if(isset($_POST['update'])) {

    $id = $_POST['transaction_id'];
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $transaction_date = $_POST['transaction_date'];

    $stmt = $conn->prepare("UPDATE transactions
                            SET amount=?, type=?, description=?, transaction_date=?
                            WHERE transaction_id=?");

    $stmt->bind_param("dsssi",
        $amount,
        $type,
        $description,
        $transaction_date,
        $id
    );

    if($stmt->execute()) {
        header("Location: view_transaction.php");
    } else {
        echo "Update Failed";
    }
}
?>