.DEFAULT_GOAL := validate
SHELL := /usr/bin/env bash

DDEV ?= ddev
BASE_URL ?= https://restaurant-suite.ddev.site

.PHONY: doctor install start stop reset php-lint phpcs stan unit integration js-build js-lint js-test e2e a11y perf package validate

doctor:
	@command -v docker >/dev/null || (echo 'Docker manquant'; exit 1)
	@command -v ddev >/dev/null || (echo 'DDEV manquant'; exit 1)
	@command -v git >/dev/null || (echo 'Git manquant'; exit 1)
	@command -v node >/dev/null || (echo 'Node.js manquant'; exit 1)
	@command -v npm >/dev/null || (echo 'npm manquant'; exit 1)
	@$(DDEV) version
	@docker version --format '{{.Client.Version}}'
	@node --version
	@npm --version
	@$(DDEV) php -v | head -n 1
	@$(DDEV) wp cli version
	@$(DDEV) composer --version
	@npx playwright --version

install:
	$(DDEV) start
	$(DDEV) composer install
	npm ci
	npx playwright install --with-deps chromium

start:
	$(DDEV) start

stop:
	$(DDEV) stop

reset:
	./scripts/reset-fixtures.sh

php-lint:
	$(DDEV) composer lint

phpcs:
	$(DDEV) composer cs

stan:
	$(DDEV) composer stan

unit:
	$(DDEV) composer test:unit

integration:
	$(DDEV) composer test:integration

js-build:
	npm run build

js-lint:
	npm run lint

js-test:
	npm run test:js

e2e:
	BASE_URL=$(BASE_URL) npx playwright test --project=chromium --project=mobile-chrome

a11y:
	npm run a11y

perf:
	npx lhci autorun

package:
	./scripts/package-release.sh

validate: doctor js-build php-lint phpcs stan unit integration js-lint js-test e2e a11y
	@echo 'Validation Restaurant Suite réussie.'
