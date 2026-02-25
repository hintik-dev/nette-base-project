DIR_DOCKER=docker
PHP_CONTAINER_NAME=nette-base-project_apache
NODEJS_CONTAINER_NAME=nette-base-project_nodejs

.PHONY: help
help: # Zobrazí nápovědu
	@grep -E '^[a-zA-Z0-9 -]+:.*#'  Makefile | while read -r l; do printf "\033[1;32m$$(echo $$l | cut -f 1 -d':')\033[00m:$$(echo $$l | cut -f 2- -d'#')\n"; done

up: # Spuštění aplikace
	cd "${DIR_DOCKER}" && docker compose up

down stop: # Zastavení aplikace
	cd "${DIR_DOCKER}" && docker compose down

bash b: # Otevřít bash v PHP kontejneru
	cd "${DIR_DOCKER}" && docker exec -it "${PHP_CONTAINER_NAME}" bash -c "umask 000 && bash"

node-bash nb: # Otevřít bash v NodeJS kontejneru
	cd "${DIR_DOCKER}" && docker compose run --rm "${NODEJS_CONTAINER_NAME}" "bash"

delete-cache dc: # Smazání cache
	find temp -mindepth 1 ! -name '.gitignore' -type f,d -exec rm -rf {} +

chmod cm: # Nastavení práv na čtení a zápis pro celou složku projektu
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c 'chmod a+rw /var/www/html -R'

all: # Spustí všechny testy aplikace.
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "composer run all"

all-fix: # Automatická oprava chyb.
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "composer run all:fix"

rector: # Zobrazí navrhované Rector změny - smazání unused use (dry-run)
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "composer rector"

rector-fix rf: # Automaticky aplikuje Rector změny - smazání unused use
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "composer rector:fix"

strict-types: # Zobrazí soubory bez <?php declare(strict_types=1); (dry-run)
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "composer strict-types"; true

strict-types-fix sf: # Přidá <?php declare(strict_types=1); do všech PHP souborů
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "composer strict-types:fix"

phpstan ps: # Spustí PHPStan statickou analýzu
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "cd /var/www/html && composer phpstan"

phpcs: # Spustí PHPCS kontrolu coding standardu
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "cd /var/www/html && composer phpcs"

phpcs-fix pf: # Automaticky opraví chyby coding standardu (PHPCBF)
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "cd /var/www/html && composer phpcs:fix"

latte-lint ll: # Spustí latte-lint kontrolu šablon
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "cd /var/www/html && composer latte-lint"

neon-lint nl: # Spustí neon-lint kontrolu konfiguračních souborů
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "cd /var/www/html && composer neon-lint"

tester t: # Spustí Nette Tester testy
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "cd /var/www/html && composer tester"

lint: # Spustí všechny lintovací nástroje a testy
	docker exec -it "${PHP_CONTAINER_NAME}" bash -c "cd /var/www/html && composer lint"
