<?php

require_once '../config.php';

session_start();



/*
|---------------------------------------
| CHECK USER ID
|---------------------------------------
*/

if (!isset($_GET['id'])) {

    die("User ID missing");
}

$id = (int) $_GET['id'];

try {

    $conn->beginTransaction();

    /*
    |---------------------------------------
    | GET CATEGORY IDS
    |---------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT category_id
        FROM categories
        WHERE user_id = ?
    ");

    $stmt->execute([$id]);

    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    /*
    |---------------------------------------
    | DELETE TRANSACTIONS USING CATEGORY
    |---------------------------------------
    */

    if (!empty($categories)) {

        $placeholders = implode(',', array_fill(0, count($categories), '?'));

        $stmt = $conn->prepare("
            DELETE FROM transactions
            WHERE category_id IN ($placeholders)
        ");

        $stmt->execute($categories);

        /*
        |---------------------------------------
        | DELETE EXPENSES USING CATEGORY
        |---------------------------------------
        */

        $stmt = $conn->prepare("
            DELETE FROM expenses
            WHERE category_id IN ($placeholders)
        ");

        $stmt->execute($categories);
    }

    /*
    |---------------------------------------
    | DELETE USER TRANSACTIONS
    |---------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM transactions
        WHERE user_id = ?
    ");

    $stmt->execute([$id]);

    /*
    |---------------------------------------
    | DELETE CATEGORIES
    |---------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM categories
        WHERE user_id = ?
    ");

    $stmt->execute([$id]);

    /*
    |---------------------------------------
    | DELETE USER
    |---------------------------------------
    */

    $stmt = $conn->prepare("
        DELETE FROM users
        WHERE user_id = ?
    ");

    $stmt->execute([$id]);

    $conn->commit();

    header("Location: ../login/logout.php");

    exit();

} catch (PDOException $e) {

    $conn->rollBack();

    die("Error: " . $e->getMessage());
}

?>

