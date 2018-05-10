Bodev Symfony Rest Framework

When start to setup:
-----------------------------------------------

First Step: Composer install

    composer install
    
Second step: Create User and Token table

    php bin/console doctrine:schema:update --force

Third Step: Create user with command

    php bin/console auth:user:create username password mail@addres.com USER_ROLE,OTHER_ROLES