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
