<p align="center">
    <img width="100" src="https://user-images.githubusercontent.com/1866496/123269537-5f90b480-d4ff-11eb-9c71-ea0c5b6365d4.png"/>
</p>

<h1 align="center">Git stars</h1>

<p align="center">
    <img src="https://img.shields.io/static/v1?label=php&message=%3E=8&color=blue">

<!-- ALL-CONTRIBUTORS-BADGE:START - Do not remove or modify this section -->
<img src='https://img.shields.io/badge/all_contributors-2-orange.svg'/>
<!-- ALL-CONTRIBUTORS-BADGE:END -->
    
</p>

## About

This project was inspired by [Git Awards](http://git-awards.com)!
Since it apparently isn't supported anymore we thought we'd take the concept back and add our own touch to it.

This was made in couple weeks as an exercise in the context of an internship.

## Embed

You can embed your GitHub statistics on your profile thanks to the cards we create for each of our users, get yours by visiting your Git Stars profile and clicking on the link icon.

<p align="center">
    <img src="https://git-stars.com/share/embed/github.svg">
</p>

```
[![Git Stars](https://git-stars.com/share/embed/github.svg)](https://git-stars.com/user/github)
```

## Installation

- Fork project, then clone it
- `cp .php-cs-fixer.dist.php .php-cs-fixer.php`
- `cp .env .env.local`
- Edit credentials on .env.local file
- Run containers : `docker-compose up -d`
- `composer install`
- Create database : `php bin/console doctrine:database:create`
- Execute migrations : `php bin/console doctrine:migrations:migrate`
- Execute languages fixtures : `php bin/console doctrine:fixtures:load --groupe=partial`
- Setup queues : `php bin/console messenger:setup-transports`

## Useful commands

- `php bin/console app:fetch:active-users` : Fetch new actives users from Github Archive
- `php bin/console app:twitter:send-top` : Tweets the users of the day on Twitter
- `php bin/console app:update:ranking` : Update ranking table
- `php bin/console messenger:consume users` : Consume messages on users queue

# Contributors

Thanks goes to these wonderful people ([emoji key](https://allcontributors.org/docs/en/emoji-key)):

<!-- ALL-CONTRIBUTORS-LIST:START - Do not remove or modify this section -->
<!-- prettier-ignore-start -->
<!-- markdownlint-disable -->
<table>
  <tr>
    <td align="center"><a href="https://www.nispeon.tk"><img src="https://avatars.githubusercontent.com/u/37938250?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Julien Cousin-Alliot</b></sub></a><br /><a href="#ideas-Nispeon" title="Ideas, Planning, & Feedback">🤔</a> <a href="https://github.com/pgrimaud/git-stars/commits?author=Nispeon" title="Code">💻</a> <a href="https://github.com/pgrimaud/git-stars/commits?author=Nispeon" title="Documentation">📖</a></td>
    <td align="center"><a href="https://github.com/pgrimaud"><img src="https://avatars.githubusercontent.com/u/1866496?v=4?s=100" width="100px;" alt=""/><br /><sub><b>Pierre Grimaud</b></sub></a><br /><a href="#ideas-pgrimaud" title="Ideas, Planning, & Feedback">🤔</a> <a href="https://github.com/pgrimaud/git-stars/commits?author=pgrimaud" title="Code">💻</a> <a href="https://github.com/pgrimaud/git-stars/commits?author=pgrimaud" title="Documentation">📖</a></td>
  </tr>
</table>

<!-- markdownlint-restore -->
<!-- prettier-ignore-end -->

<!-- ALL-CONTRIBUTORS-LIST:END -->

This project follows the [all-contributors](https://github.com/all-contributors/all-contributors) specification. Contributions of any kind welcome!

# Feedback

You found a bug? You need a new feature? You can create an issue if needed or contact us on [Twitter](https://twitter.com/pgrimaud_).

# License

Licensed under the terms of the MIT License.