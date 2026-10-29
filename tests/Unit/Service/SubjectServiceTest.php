<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Subject;
use App\Exception\DuplicateRecordException;
use App\Exception\RecordInUseException;
use App\Exception\RecordNotFoundException;
use App\Repository\SubjectRepositoryInterface;
use App\Service\SubjectService;
use App\Tests\Support\DatabaseExceptions;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class SubjectServiceTest extends TestCase
{
    public function testGetThrowsNotFoundWhenMissing(): void
    {
        $repository = $this->createStub(SubjectRepositoryInterface::class);
        $repository->method('findById')->willReturn(null);

        $this->expectException(RecordNotFoundException::class);

        new SubjectService($repository, new NullLogger())->get(7);
    }

    public function testSaveTranslatesUniqueViolationIntoDuplicateRecord(): void
    {
        $repository = $this->createStub(SubjectRepositoryInterface::class);
        $repository->method('save')->willThrowException(DatabaseExceptions::unique());

        $this->expectException(DuplicateRecordException::class);
        $this->expectExceptionMessage('Já existe um assunto "Romance" cadastrado.');

        new SubjectService($repository, new NullLogger())->save(new Subject('Romance'));
    }

    public function testDeleteTranslatesForeignKeyViolationIntoRecordInUse(): void
    {
        $repository = $this->createStub(SubjectRepositoryInterface::class);
        $repository->method('delete')->willThrowException(DatabaseExceptions::foreignKey());

        $this->expectException(RecordInUseException::class);
        $this->expectExceptionMessage('assunto "Testes"');

        new SubjectService($repository, new NullLogger())->delete(new Subject('Testes'));
    }

    public function testDeleteDelegatesToRepository(): void
    {
        $subject = new Subject('Testes');
        $repository = $this->createMock(SubjectRepositoryInterface::class);
        $repository->expects(self::once())->method('delete')->with($subject);

        new SubjectService($repository, new NullLogger())->delete($subject);
    }
}
