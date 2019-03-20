For all commands need to type:
_composer {command}_

Command for deploying:
        "+np": [
          "@composer update",
          "php artisan allTables:drop",
          "@composer dump-autoload",
          "php artisan migrate:refresh --seed"
        ]
        
Other useful commands:
        "+u": [
          "@composer update"
        ],
        "+da": [
          "@composer dump-autoload"
        ],
        "+dt": [
          "php artisan allTables:drop"
        ],
        "+mr": [
          "php artisan migrate:refresh"
        ],
        "+s": [
          "php artisan db:seed"
        ],
        "+da+s": [
          "@composer dump-autoload",
          "php artisan db:seed"
        ],
        "+da+mr": [
          "@composer dump-autoload",
          "php artisan migrate:refresh"
        ],
        "+mr+s": [
          "php artisan migrate:refresh --seed"
        ],
        "+da+mr+s": [
          "@composer dump-autoload",
          "php artisan migrate:refresh --seed"
        ],
        "+dt+da+mr+s": [
          "php artisan allTables:drop",
          "@composer dump-autoload",
          "php artisan migrate:refresh --seed"
        ],         