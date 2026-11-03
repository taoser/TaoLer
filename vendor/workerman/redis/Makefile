# Dual-backend test orchestration for workerman/redis.
#
# The suite runs against two engines selected by the REDIS_URL + REDIS_BACKEND
# pair: Dragonfly on :6379 and a real Redis on :63790. Coverage is collected
# from the Dragonfly leg only (single source of truth, avoids double counting).
#
# These targets are thin wrappers around composer scripts.

DRAGONFLY_URL ?= redis://127.0.0.1:6379
REDIS_URL_REDIS ?= redis://127.0.0.1:63790

.PHONY: help test-dragonfly test-redis test-all coverage analyze

help:
	@echo "Targets:"
	@echo "  test-dragonfly  Run the suite against Dragonfly ($(DRAGONFLY_URL))"
	@echo "  test-redis      Run the suite against real Redis ($(REDIS_URL_REDIS))"
	@echo "  test-all        Run both engines sequentially; fail if either fails"
	@echo "  coverage        Merged subprocess coverage (Dragonfly leg only)"
	@echo "  analyze         Run PHPStan (composer analyze)"

test-dragonfly:
	REDIS_URL=$(DRAGONFLY_URL) REDIS_BACKEND=dragonfly composer test

test-redis:
	REDIS_URL=$(REDIS_URL_REDIS) REDIS_BACKEND=redis composer test

test-all:
	@echo "=== Dragonfly leg ==="
	$(MAKE) test-dragonfly
	@echo "=== Redis leg ==="
	$(MAKE) test-redis

coverage:
	REDIS_URL=$(DRAGONFLY_URL) REDIS_BACKEND=dragonfly composer test:coverage

analyze:
	composer analyze
