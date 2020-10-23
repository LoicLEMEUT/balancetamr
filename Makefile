help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## Project
build: .check .destroy .setup run .composer .sleep run reset ## Project setup
	@echo "\033[32mBuild successfull!\033[0m"
shutdown: ## Project shutdown
	@echo "\033[33mShutting down containers ...\033[0m"
	@bash -l -c 'docker-compose -f .docker/docker-compose.yml down'
	@echo "\033[32mContainers down!\033[0m"

run: ## Project startup
	@echo "\033[33mFiring up containers ...\033[0m"
	@bash -l -c 'docker-compose -f .docker/docker-compose.yml up -d'
	@echo "\033[32mContainers running!\033[0m"

login: ## Logging to the project's php container
	@bash -l -c 'docker-compose -f .docker/docker-compose.yml exec php-fpm sh'

mysql: ## Access the project's mysql database
	@echo "\033[32mAccessing database...\033[0m"
	@docker-compose -f .docker/docker-compose.yml exec database sh -c "mysql -u root -proot balancetamr"

update: .update-code .composer ## Update project code

.sleep:
	@echo "\033[32mWaiting 30s database...\033[0m"
	@sleep 30

.PHONY: console
ifeq (console,$(firstword $(MAKECMDGOALS)))
  RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  $(eval $(subst :,\:,$(RUN_ARGS)):.ignore;@:)
endif
console: ## Using Symfony console
	@docker-compose -f .docker/docker-compose.yml exec php-fpm sh -c "bin/console $(RUN_ARGS) -vvv"

.PHONY: composer
ifeq (composer,$(firstword $(MAKECMDGOALS)))
  RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  $(eval $(subst :,\:,$(RUN_ARGS)):.ignore;@:)
endif
composer: ## Using composer
	@docker-compose -f .docker/docker-compose.yml exec php-fpm sh -c "composer $(RUN_ARGS) -vv"

.destroy:
	@echo "\033[33mRemoving containers ...\033[0m"
	@rm -f .env
	@cp .env.test .env
	@docker-compose -f .docker/docker-compose.yml rm -v --force --stop || true
	@echo "\033[32mContainers removed!\033[0m"

build_db:
	@echo "\033[33mRestting database...\033[0m"
	@docker-compose -f .docker/docker-compose.yml exec php-fpm sh -c "bin/console --env=dev doctrine:database:drop --force \
		&& bin/console --env=dev doctrine:database:create \
		&& bin/console --env=dev doctrine:migrations:migrate --no-interaction"

## Data
reset: build_db ## Reset database structure and data
	@echo "\033[32mDatabase reset!\033[0m"

reload: ## Reload database data
	@echo "\033[33mLoading fixtures...\033[0m"
	@docker-compose -f .docker/docker-compose.yml exec php-fpm sh -c "bin/console --env=test doctrine:fixtures:load --no-interaction"

migration-diff: ## Generate diff migration
	@echo "\033[33mInstalling dependencies...\033[0m"
	@docker-compose -f .docker/docker-compose.yml exec php-fpm sh -c "bin/console doctrine:migrations:diff"

## Dependencies
.composer: ## Install composer dependencies
	@echo "\033[33mInstalling dependencies...\033[0m"
	@docker-compose -f .docker/docker-compose.yml exec php-fpm sh -c "composer install --dev --no-interaction -o"

.update-code:
	@echo "\033[33mUpdating project...\033[0m"
	@( git stash && git checkout develop && git fetch --all && git pull --rebase && git checkout @{-1} && git stash pop )

.setup:
	@echo "\033[33mBuilding containers ...\033[0m"
	@docker-compose -f .docker/docker-compose.yml build
	@echo "\033[32mContainers built!\033[0m"

.check:
	@echo "\033[31mWARNING!!!\033[0m Executing this script will reinitialize the project and all of its data to factory"
	@( read -p "Are you sure you wish to continue? [y/N]: " sure && case "$$sure" in [yY]) true;; *) false;; esac )

.ignore:
	@:
