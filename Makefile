.PHONY: help

help: ## Show this doc
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

install: ## Install the project
	symfony composer install
	symfony console d:d:c
	make rebuild

rebuild: ## Rebuild database
	symfony console d:d:d -f
	symfony console d:d:c
	symfony console d:s:u -f
	symfony console d:f:l -n