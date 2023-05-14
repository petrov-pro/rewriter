# rewriter
Rewriter - application which use openAI and new api for create rewrite content

API description
/api/doc/api
/api/doc/manager

1 copy .env.docker.dist .env

2 /app copy .env.dist .env

3 run docker compose -f DOCKER_FILE up --build

4 sudo chown www-data:www-data -R ./app/

#DB

./bin/console doctrine:migrations:migrate
./bin/console --env=dev doctrine:fixtures:load


## Deploy
git pull

docker compose -f docker-compose-prod.yaml down
docker compose -f docker-compose-prod.yaml up --build -d


#Dev
For control workers - supervisorctl status

For debug setup web root in project(netbeans)

For console command export XDEBUG_CONFIG="idekey=PHPSTORM"
