DC   = docker compose
EXEC = $(DC) exec app
CON  = $(EXEC) php bin/console

.DEFAULT_GOAL := help
.PHONY: help up down restart build logs sh install migrate fixtures reset-db test test-unit test-integration test-functional analyse cs cs-fix qa

help: ## Lista os comandos disponíveis
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

up: ## Sobe a stack (app, nginx, postgres). Instala dependências e roda as migrations
	$(DC) up -d --build

down: ## Derruba a stack
	$(DC) down

restart: down up ## Reinicia a stack

logs: ## Acompanha os logs
	$(DC) logs -f

sh: ## Shell no container da aplicação
	$(EXEC) bash

install: ## composer install
	$(EXEC) composer install

migrate: ## Aplica as migrations (dev e test)
	$(CON) doctrine:migrations:migrate -n
	$(CON) doctrine:migrations:migrate -n --env=test

fixtures: ## Carrega dados de exemplo
	$(CON) doctrine:fixtures:load -n

reset-db: ## Recria o banco de dev do zero e carrega os dados de exemplo
	$(CON) doctrine:database:drop --force --if-exists
	$(CON) doctrine:database:create
	$(CON) doctrine:migrations:migrate -n
	$(CON) doctrine:fixtures:load -n

test: ## Roda toda a suíte de testes
	$(EXEC) php bin/phpunit

test-unit: ## Só testes unitários (sem banco)
	$(EXEC) php bin/phpunit --testsuite unit

test-integration: ## Testes de integração (banco real)
	$(EXEC) php bin/phpunit --testsuite integration

test-functional: ## Testes funcionais (HTTP + banco)
	$(EXEC) php bin/phpunit --testsuite functional

analyse: ## Análise estática (PHPStan nível 8)
	$(CON) cache:warmup -q
	$(EXEC) vendor/bin/phpstan analyse --memory-limit=512M

cs: ## Verifica o estilo de código
	$(EXEC) env PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Corrige o estilo de código
	$(EXEC) env PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix

qa: cs analyse test ## Estilo + análise estática + testes
