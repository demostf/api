.PHONY: docker
docker:
	docker build -t demostf/api .

.PHONY: testdb
testdb:
	docker run -d --name api-unit-test-db -p 5434:5432 -e POSTGRES_PASSWORD=test demostf/db

node_modules: package.json
	npm install

.PHONY: mocha
mocha: node_modules
	DEMO_ROOT=/tmp/demos DB_PORT=5434 DB_TYPE=pgsql DB_HOST=localhost DB_USERNAME=postgres DB_PASSWORD=test DB_DATABASE=postgres\
	 PARSER_PATH=/home/robin/Projects/demostf/tf-demo-parser/target/release/parse_demo node node_modules/.bin/mocha --recursive

.PHONY: phpunit
phpunit:
	DB_PORT=5434 DB_TYPE=pgsql DB_HOST=localhost DB_USERNAME=postgres DB_USERNAME=postgres DB_PASSWORD=test DB_DATABASE=postgres ./vendor/bin/phpunit test

.PHONY: test
tests: phpunit mocha

.PHONY: lint
lint:
	vendor/bin/php-cs-fixer fix --allow-risky yes
