<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';


// START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * GET TRANSACTIONS
 * SHOW ONLY LOGGED-IN USER DATA
 */

function getTransactions(
    int $user_id,
    array $filter = [],
    bool $limit = true
): array
{
    global $conn;

    // BASE QUERY
    $query = "
        SELECT *
        FROM transactions
        WHERE user_id = :user_id
    ";


    // SEARCH FILTER
    if (!empty($filter['search'])) {

        $query .= "
            AND description LIKE :search
        ";
    }


    // FROM DATE FILTER
    if (!empty($filter['from_date'])) {

        $query .= "
            AND transaction_date >= :from_date
        ";
    }


    // TO DATE FILTER
    if (!empty($filter['to_date'])) {

        $query .= "
            AND transaction_date <= :to_date
        ";
    }


    // ORDER
    $query .= "
        ORDER BY transaction_date DESC
    ";


    // LIMIT
    if ($limit) {

        $query .= "
            LIMIT 5
        ";
    }


    // PREPARE
    $stmt = $conn->prepare($query);


    // USER ID
    $stmt->bindValue(
        ':user_id',
        $user_id,
        PDO::PARAM_INT
    );


    // SEARCH
    if (!empty($filter['search'])) {

        $stmt->bindValue(
            ':search',
            '%' . $filter['search'] . '%',
            PDO::PARAM_STR
        );
    }


    // FROM DATE
    if (!empty($filter['from_date'])) {

        $stmt->bindValue(
            ':from_date',
            $filter['from_date'],
            PDO::PARAM_STR
        );
    }


    // TO DATE
    if (!empty($filter['to_date'])) {

        $stmt->bindValue(
            ':to_date',
            $filter['to_date'],
            PDO::PARAM_STR
        );
    }


    // EXECUTE
    $stmt->execute();


    // RETURN DATA
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}