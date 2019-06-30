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

.PHONY: start ## Start the project (Install in first place)
start:
	$(FIG) pull || true
	$(FIG) build
	$(FIG) up -d
	$(FIG) exec -u 1000:1000 app composer install
	$(EXEC) $(CONSOLE) doctrine:database:create --if-not-exists
	$(EXEC) $(CONSOLE) doctrine:schema:update --force
	$(EXEC) $(CONSOLE) hautelook:fixtures:load -q

.PHONY: tests-fix ## fix code style erros
tests-fix:
	$(EXEC) vendor/bin/phpcbf src

.PHONY: tests ## launch tests
tests:
	$(EXEC) vendor/bin/phpcs src
	$(EXEC) vendor/bin/phpstan analyse --level 6 src

