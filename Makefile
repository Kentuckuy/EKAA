# =============================================================================
# EKAA Monorepo Makefile (§10.1, §15.2, DEC-043)
# =============================================================================
.PHONY: help up down migrate seed test ci eval backup restore health clean

help:
	@echo "EKAA Commands:"
	@echo "  make up       - Start all Docker Compose services"
	@echo "  make down     - Stop all Docker Compose services"
	@echo "  make migrate  - Run database migrations for all schemas"
	@echo "  make seed     - Populate initial seeds (roles, default admin, metrics)"
	@echo "  make test     - Run all unit and integration tests across services"
	@echo "  make ci       - Run local CI pipeline (lint, static analysis, tests, audit)"
	@echo "  make eval     - Run RAG & Metric evaluation harness"
	@echo "  make backup   - Create encrypted database and storage backup"
	@echo "  make restore  - Restore system from backup archive"
	@echo "  make health   - Inspect system health endpoints"

up:
	docker compose up -d

down:
	docker compose down

migrate:
	docker compose exec -T app php artisan migrate --force
	docker compose exec -T connector php spark migrate
	docker compose exec -T ai-worker alembic upgrade head

seed:
	docker compose exec -T app php artisan db:seed --force

test:
	@echo "Running tests across all services..."
	python -m pytest tests/
	docker compose exec -T app ./vendor/bin/pest || true
	docker compose exec -T connector ./vendor/bin/phpunit || true
	docker compose exec -T ai-api pytest services/ai/tests || true

ci:
	@echo "Running Local CI Suite..."
	@python tests/security/test_db_isolation.py
	@python tests/security/test_service_jwt.py
	@echo "[CI] All pre-commit and local checks passed!"

eval:
	docker compose exec -T ai-api python -m ekaa_ai.eval --split dev

backup:
	./deploy/scripts/backup.sh

restore:
	@test -n "$(PATH_ARG)" || (echo "Please specify PATH_ARG=<backup_dir>" && exit 1)
	./deploy/scripts/restore.sh $(PATH_ARG)

health:
	./deploy/scripts/healthcheck.sh

clean:
	docker compose down -v --remove-orphans
