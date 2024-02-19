# Rewriter
Rewriter - application that uses openAI and new API to create and rewrite content.  
Stack: *PHP, RabbitMq, Symfony, PostgreSQL*

## How Run
1. copy .env.docker.dist .env
2. */app* copy .env.dist .env
3. run docker compose -f DOCKER_FILE up --build
4. sudo chown www-data:www-data -R ./app/

## DB

1. ./bin/console doctrine:migrations:migrate
2. ./bin/console app:user --admin


## Deploy

1. git pull
2. docker compose -f docker-compose-prod.yaml down
3. docker volume rm rewriter_source_data
4. docker compose -f docker-compose-prod.yaml up --build -d


## Dev
For control workers - supervisorctl status  
For debug - setup web root in project(netbeans)  
For debug console command - export XDEBUG_CONFIG="idekey=PHPSTORM"  
For log: - sudo lnav /var/lib/docker/volumes/rewriter_source_data/_data/var/log/prod-2023-05-15.log  
For connect to DB - pgcli -h 172.26.0.4 -u rewriter  

## API description
1. /api/doc/api
2. /api/doc/manager

## Doc
1. /api/doc.json/{area}
2. /api/doc/{area}

Available area - user, api, admin

