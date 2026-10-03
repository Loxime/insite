router:
	docker exec insite_app php bin/console debug:router

help:
	docker exec insite_app php bin/console

clear:
	docker exec insite_app php bin/console cache:clear

migration:
	docker exec insite_app php bin/console make:migration

migrate:
	docker exec insite_app php bin/console doctrine:migrations:migrate

docker:
	docker compose up -d

build:
	docker compose up -d --build

bash:
	docker exec -it insite_app bash

front:
	npm run type-check
	npm run build

user:
	docker exec -it insite_app php bin/console app:user:create

db-valid:
	docker exec insite_app php bin/console doctrine:schema:validate
