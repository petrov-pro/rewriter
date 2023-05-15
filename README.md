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
./bin/console app:user --admin


## Deploy
git pull
docker volumes rm  PREFIX--source_data
docker compose -f docker-compose-prod.yaml down
docker compose -f docker-compose-prod.yaml up --build -d


#Dev
For control workers - supervisorctl status

For debug setup web root in project(netbeans)

For console command export XDEBUG_CONFIG="idekey=PHPSTORM"

sudo lnav /var/lib/docker/volumes/rewriter_source_data/_data/var/log/prod-2023-05-15.log

pgcli -h 172.26.0.4 -u rewriter

