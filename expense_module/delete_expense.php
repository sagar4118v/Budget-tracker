<?php

require_once '../config.php';

session_start();

if(!isset($_SESSION['user_id'])){

    exit();
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("

    DELETE FROM expenses

    WHERE expense_id = ?

");

$stmt->execute([$id]);

header("Location: view_expense.php");
exit();