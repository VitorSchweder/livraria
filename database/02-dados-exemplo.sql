-- =============================================================================
-- Dados de exemplo (opcional). Mesmo conteúdo das fixtures Doctrine
-- (src/DataFixtures/AppFixtures.php). Inclui livros com mais de um autor para
-- demonstrar o agrupamento do relatório.
--
--     psql -h <host> -U <usuario> -d <banco> -f database/02-dados-exemplo.sql
-- =============================================================================

BEGIN;

INSERT INTO autor (nome) VALUES
    ('Machado de Assis'), ('Clarice Lispector'), ('Robert C. Martin'), ('Martin Fowler'),
    ('Kent Beck'), ('Erich Gamma'), ('Richard Helm'), ('Eric Evans');

INSERT INTO assunto (descricao) VALUES
    ('Romance'), ('Literatura Nacional'), ('Eng. de Software'), ('Arquitetura'), ('Testes'), ('Padrões de Projeto');

INSERT INTO livro (titulo, editora, edicao, anopublicacao, valor) VALUES
    ('Dom Casmurro', 'Garnier', 1, '1899', 39.90),
    ('Memórias Póstumas de Brás Cubas', 'Tipografia Nacional', 2, '1881', 42.50),
    ('A Hora da Estrela', 'Rocco', 1, '1977', 34.90),
    ('Clean Code', 'Prentice Hall', 1, '2008', 189.90),
    ('Clean Architecture', 'Prentice Hall', 1, '2017', 159.00),
    ('Refactoring', 'Addison-Wesley', 2, '2018', 249.90),
    ('Test-Driven Development', 'Addison-Wesley', 1, '2002', 199.00),
    ('Design Patterns', 'Addison-Wesley', 1, '1994', 279.90),
    ('Domain-Driven Design', 'Addison-Wesley', 1, '2003', 1234.56);

INSERT INTO livro_autor (livro_codl, autor_codau)
SELECT l.codl, a.codau
FROM (VALUES
    ('Dom Casmurro', 'Machado de Assis'),
    ('Memórias Póstumas de Brás Cubas', 'Machado de Assis'),
    ('A Hora da Estrela', 'Clarice Lispector'),
    ('Clean Code', 'Robert C. Martin'),
    ('Clean Architecture', 'Robert C. Martin'),
    ('Refactoring', 'Martin Fowler'),
    ('Refactoring', 'Kent Beck'),
    ('Test-Driven Development', 'Kent Beck'),
    ('Design Patterns', 'Erich Gamma'),
    ('Design Patterns', 'Richard Helm'),
    ('Domain-Driven Design', 'Eric Evans')
) AS v (titulo, nome)
JOIN livro l ON l.titulo = v.titulo
JOIN autor a ON a.nome = v.nome;

INSERT INTO livro_assunto (livro_codl, assunto_codas)
SELECT l.codl, s.codas
FROM (VALUES
    ('Dom Casmurro', 'Romance'), ('Dom Casmurro', 'Literatura Nacional'),
    ('Memórias Póstumas de Brás Cubas', 'Romance'), ('Memórias Póstumas de Brás Cubas', 'Literatura Nacional'),
    ('A Hora da Estrela', 'Romance'), ('A Hora da Estrela', 'Literatura Nacional'),
    ('Clean Code', 'Eng. de Software'),
    ('Clean Architecture', 'Eng. de Software'), ('Clean Architecture', 'Arquitetura'),
    ('Refactoring', 'Eng. de Software'), ('Refactoring', 'Testes'),
    ('Test-Driven Development', 'Testes'),
    ('Design Patterns', 'Padrões de Projeto'), ('Design Patterns', 'Eng. de Software'),
    ('Domain-Driven Design', 'Arquitetura'), ('Domain-Driven Design', 'Eng. de Software')
) AS v (titulo, descricao)
JOIN livro l ON l.titulo = v.titulo
JOIN assunto s ON s.descricao = v.descricao;

COMMIT;
