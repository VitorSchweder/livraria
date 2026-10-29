<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\DBAL\Driver\AbstractException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class DatabaseExceptions
{
    public static function foreignKey(): ForeignKeyConstraintViolationException
    {
        return new ForeignKeyConstraintViolationException(self::driver('foreign key violation', '23503'), null);
    }

    public static function unique(): UniqueConstraintViolationException
    {
        return new UniqueConstraintViolationException(self::driver('unique violation', '23505'), null);
    }

    public static function connection(): ConnectionException
    {
        return new ConnectionException(self::driver('connection refused', '08006'), null);
    }

    private static function driver(string $message, string $sqlState): AbstractException
    {
        return new class($message, $sqlState) extends AbstractException {
        };
    }
}
