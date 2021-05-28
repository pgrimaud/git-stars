# Git Stars

## Installation

- `git clone git@github.com:pgrimaud/git-stars.git`
- `cp .php-cs-fixer.dist.php .php-cs-fixer.php`
- `cp .env .env.local`
- Edit credentials on .env.local file
- `composer install`
- `php bin/console doctrine:database:create`
- `php bin/console doctrine:migrations:migrate`
- `php bin/console doctrine:fixtures:load --groupe=partial`