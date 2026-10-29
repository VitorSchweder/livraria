<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261028000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'View vw_livros_por_autor, fonte do relatório de livros agrupados por autor';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE VIEW vw_livros_por_autor AS
            SELECT
                a.codau AS autor_codau,
                a.nome AS autor_nome,
                l.codl AS livro_codl,
                l.titulo,
                l.editora,
                l.edicao,
                l.anopublicacao,
                l.valor,
                (
                    SELECT string_agg(s.descricao, '|' ORDER BY s.descricao)
                    FROM livro_assunto ls
                    JOIN assunto s ON s.codas = ls.assunto_codas
                    WHERE ls.livro_codl = l.codl
                )                AS assuntos,
                (
                    SELECT string_agg(co.nome, '|' ORDER BY co.nome)
                    FROM livro_autor lco
                    JOIN autor co ON co.codau = lco.autor_codau
                    WHERE lco.livro_codl = l.codl
                      AND lco.autor_codau <> a.codau
                )                AS coautores
            FROM autor a
            LEFT JOIN livro_autor la ON la.autor_codau = a.codau
            LEFT JOIN livro l ON l.codl = la.livro_codl
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW vw_livros_por_autor');
    }
}
