<?php
include 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="transactions.csv"');

$output = fopen("php://output", "w");

fputcsv($output, ['ID', 'Amount', 'Type', 'Description', 'Date']);

$result = $conn->query("SELECT * FROM transactions");

while($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
?>