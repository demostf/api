.PHONY: docker
docker:
	docker build -t demostf/api .

.PHONY: testdb
testdb:
	docker run -d --name api-test -p 5433:5432 -e POSTGRES_PASSWORD=test demostf/db

.PHONY: test
test:
	cd tests; DB_PORT=5433 DB_TYPE=pgsql DB_HOST=localhost DB_USERNAME=postgres DB_USERNAME=postgres DB_PASSWORD=test DB_DATABASE=postgres phpunit
