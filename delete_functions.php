<?php

function validateUserId(int $id): bool
{
    return $id > 0;
}