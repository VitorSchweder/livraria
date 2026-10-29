## Como executar

Pré-requisitos: **Docker** e **Docker Compose**. Nada mais precisa estar instalado no host.

```bash
git clone git@github.com:VitorSchweder/livraria.git livraria
cd livraria

make up         
make fixtures
```

Acesse **http://localhost:8080**.

Portas padrão: web `8080`, PostgreSQL `5433` (usuário/senha/banco `livraria`). Para mudar exemplo:
`WEB_PORT=9090 DB_PORT=5544 make up`.

### Comandos úteis

```bash
make test # para rodar os testes
make reset-db # recria o banco de dev e carrega os dados de exemplo (seeders)
```
---

## Implantação sem Docker (resumo)

1. PHP 8.4 com `intl`, `pdo_pgsql`, `gd`, `zip`; PostgreSQL 13+; Composer 2.
2. `composer install --no-dev --optimize-autoloader`
3. Configure `DATABASE_URL` e `APP_SECRET` em `.env.local` (ou como variáveis de ambiente) e defina `APP_ENV=prod`.
4. `php bin/console doctrine:migrations:migrate -n` (ou rode `database/01-schema.sql`).
5. `php bin/console cache:clear` e aponte o document root do servidor web para `public/` (veja `docker/nginx/default.conf`).
