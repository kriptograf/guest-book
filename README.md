# guest-book
Symfony learn application

docker-compose exec php bin/console make:migration
docker-compose exec php bin/console doctrine:migrations:migrate

Source Password    11111111
Encoded password   $argon2id$v=19$m=65536,t=4,p=1$URRT85RjaESs6O4pI5u0RA$mwFrVfzYv7JSs/OD8N+bt7/jCVcwtb70zk0oR33NIi4

Посмотреть роуты    docker-compose exec php bin/console debug:route

Запуск тестов: 
docker-compose exec -T php bin/console doctrine:fixtures:load
docker-compose exec -T php ./bin/phpunit
или
docker-compose exec -T php ./bin/phpunit tests/Controller/ConferenceControllerTest.php

Загрузить фикстуры в бд: docker-compose exec php bin/console doctrine:fixtures:load
