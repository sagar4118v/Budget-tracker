<?php

require_once '../config.php';

session_start();

$id = (int) $_GET['id'];

$stmt = $conn->prepare("

    SELECT *
    FROM expenses

    WHERE expense_id = ?

");

$stmt->execute([$id]);

$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['update'])){

    $amount = $_POST['amount'];

    $description = $_POST['description'];

    $expense_date = $_POST['expense_date'];

    $update = $conn->prepare("

        UPDATE expenses

        SET
            amount = ?,
            description = ?,
            expense_date = ?

        WHERE expense_id = ?

    ");

    $update->execute([

        $amount,
        $description,
        $expense_date,
        $id
    ]);

    header("Location: view_expense.php");
    exit();
}

?>

<form method="POST">

    <input
        type="number"
        step="0.01"
        name="amount"
        value="<?= $expense['amount'] ?>"
    >

    <input
        type="text"
        name="description"
        value="<?= $expense['description'] ?>"
    >

    <input
        type="date"
        name="expense_date"
        value="<?= $expense['expense_date'] ?>"
    >

    <button name="update">

        Update Expense

    </button>

</form>