<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inclui o campo valor (R$) no livro, não previsto no modelo inicial';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE livro ADD valor NUMERIC(10, 2) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE livro ALTER valor DROP DEFAULT');
        $this->addSql('ALTER TABLE livro ADD CONSTRAINT livro_valor_chk CHECK (valor >= 0)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE livro DROP CONSTRAINT livro_valor_chk');
        $this->addSql('ALTER TABLE livro DROP valor');
    }
}
