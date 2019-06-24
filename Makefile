USERID=$(shell id -u)
GROUPID=$(shell id -g)

CONSOLE=php bin/console
FIG=docker-compose
HAS_DOCKER:=$(shell command -v $(FIG) 2> /dev/null)

ifdef HAS_DOCKER
    ifdef APP_ENV
        EXECROOT=$(FIG) exec -e APP_ENV=$(APP_ENV) app
        EXEC=$(FIG) exec -e APP_ENV=$(APP_ENV) -u $(USERID):$(GROUPID) app
	else
        EXECROOT=$(FIG) exec app
        EXEC=$(FIG) exec -u $(USERID):$(GROUPID) app
	endif
else
	EXECROOT=
	EXEC=
endif

.DEFAULT_GOAL := help

.PHONY: help ## Generate list of targets with descriptions
help:
		@grep '##' Makefile \
		| grep -v 'grep\|sed' \
		| sed 's/^\.PHONY: \(.*\) ##[\s|\S]*\(.*\)/\1:\t\2/' \
		| sed 's/\(^##\)//' \
		| sed 's/\(##\)/\t/' \
		| expand -t14

##
## Project setup & day to day shortcuts
##---------------------------------------------------------------------------

.PHONY: start ## Start the project (Install in first place)
start:
start: docker-compose.override.yml
	$(FIG) pull || true
	$(FIG) build
	$(FIG) up -d
	$(FIG) exec -u 1000:1000 app composer install
	$(EXEC) $(CONSOLE) doctrine:database:create --if-not-exists
	$(EXEC) $(CONSOLE) doctrine:schema:update --force
	$(EXEC) $(CONSOLE) hautelook:fixtures:load -q
.PHONY: dup ## restart the project
dup:
	$(FIG) stop
	$(FIG) up -d

.PHONY: stop ## stop the project
stop:
	$(FIG) down

.PHONY: exe ## Run bash in the app container
exe:
	$(FIG) exec -u 1000:1000 app /bin/bash

.PHONY: tests ## Lance les tests de l'applications
tests:
	vendor/bin/phpcs src
	vendor/bin/phpstan analyse --level 6 src

.PHONY: tests-fix ## Fix le cs de mon app
tests-fix:
	vendor/bin/phpcbf src

##
## Dependencies Files
##---------------------------------------------------------------------------

docker-compose.override.yml: docker-compose.override.yml.dist
	$(RUN) cp docker-compose.override.yml.dist docker-compose.override.yml
