build:
	docker compose build

up:
	docker compose up -d

down:
	docker compose down

restart: down up

rebuild: down build up

install:
	docker compose exec interest-account-library composer install

update:
	docker compose exec interest-account-library composer update

run-test:
	docker compose exec interest-account-library vendor/bin/phpunit tests/

lint:
	docker compose exec interest-account-library vendor/bin/phpcs --standard=PSR12 src/

lint-fix:
	docker compose exec interest-account-library vendor/bin/phpcbf --standard=PSR12 src/
