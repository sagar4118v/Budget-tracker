<?php
include 'db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Transaction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

<h2>Edit Transaction</h2>

<form action="update_transaction.php" method="POST">

<input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">

<input type="number" step="0.01" name="amount" value="<?= $row['amount'] ?>">

<select name="type">
    <option value="Income">Income</option>
    <option value="Expense">Expense</option>
</select>

<input type="text" name="description" value="<?= $row['description'] ?>">

<input type="date" name="transaction_date" value="<?= $row['transaction_date'] ?>">

<button type="submit" name="update">Update</button>

</form>

</div>

</body>
</html>