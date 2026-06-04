<?php

function emailExists(PDO $conn, string $email): bool
{
    $check = $conn->prepare("
        SELECT user_id
        FROM users
        WHERE email = ?
    ");

    $check->execute([$email]);

    return $check->rowCount() > 0;
}

function createUser(
    PDO $conn,
    string $name,
    string $email,
    string $phone,
    string $password,
    string $role
): bool {

    $stmt = $conn->prepare("
        INSERT INTO users
        (
            name,
            email,
            phone,
            password,
            role,
            is_active
        )
        VALUES
        (?, ?, ?, ?, ?, 1)
    ");

    return $stmt->execute([
        $name,
        $email,
        $phone,
        $password,
        $role
    ]);
}