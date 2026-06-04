<?php

function calculateAdminStats(array $users): array
{
    $active = 0;
    $blocked = 0;

    foreach ($users as $u) {

        if ($u['is_active']) {
            $active++;
        } else {
            $blocked++;
        }
    }

    return [
        'active' => $active,
        'blocked' => $blocked
    ];
}