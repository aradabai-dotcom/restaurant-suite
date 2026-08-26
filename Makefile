SHELL := /bin/bash
DDEV ?= ddev

.PHONY: doctor install start stop reset composer-validate php-lint phpcs stan unit integration contracts js-build js-lint js-test e2e a11y perf security package validate

doctor:
	./scripts/doctor.sh

install:
	composer install --no-interaction --prefer-dist
	pnpm install --frozen-lockfile
	./scripts/bootstrap-local.sh

start:
	$(DDEV) start

stop:
	$(DDEV) stop

reset:
	CRS_ALLOW_FIXTURE_RESET=1 ./scripts/reset-fixtures.sh

composer-validate:
	composer validate --strict

php-lint:
	composer lint

phpcs:
	composer cs

stan:
	composer stan

unit:
	composer test:unit

integration:
	composer test:integration

contracts:
	composer contracts

js-build:
	pnpm run build

js-lint:
	pnpm run lint

js-test:
	pnpm run test:js

e2e:
	pnpm run e2e

a11y:
	pnpm run a11y

perf:
	pnpm run perf

security:
	composer audit
	pnpm audit --audit-level high

package:
	./scripts/package-release.sh

validate: composer-validate php-lint phpcs stan unit contracts js-build js-lint js-test
	@echo "Phase 0.0 validation complete; feature modules are intentionally not implemented yet."
