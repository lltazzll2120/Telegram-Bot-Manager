# PHP Telegram Bot Manager

[![Join the bot support group on Telegram][support-group-badge]][support-group]
[![Donate][donate-badge]](#donate)

[![Scrutinizer Code Quality][code-quality-badge]][code-quality]
[![Codecov][code-coverage-badge]][code-coverage]
[![Tests Status][tests-status-badge]][tests-status]

[![Latest Stable Version][latest-version-badge]][github-tgbot-manager]
[![Dependencies][dependencies-badge]][Tidelift]
[![Total Downloads][total-downloads-badge]][packagist-tgbot-manager]
[![License][license-badge]][license]

This project builds on top of [PHP Telegram Bot]


Installation and usage is pretty straight forward:

### Require this package with [Composer]

```bash
composer require php-telegram-bot/telegram-bot-manager:^1.8
```


**Advanced:** Due to the fact that the core library is not a stable version yet, this project is partly locked to the core version, to ensure reliable functioning.

It is possible however, to override the core version that this library requires:

```yaml
"require": {
    "php-telegram-bot/telegram-bot-manager": "^1.8",
    "Tazz/telegram-bot": "dev-master as 0.78"
}
```

This example will pull the master version of the core library, making it appear to be version 0.78, which then satisfies the requirement.

### Performing actions

What use would this library be if you couldn't perform any actions?!

There are a few parameters available to get things rolling:

| Parameter | Description |
| --------- | ----------- |
| s         | **s**ecret: This is a special secret value defined in the main `manager.php` file. |
|           | This parameter is required to call the script via browser! |
| a         | **a**ction: The actual action to perform. (handle (default), webhookinfo, cron, set, unset, reset) |
|           | **handle** executes the `getUpdates` method; **webhookinfo** to get result from `getWebhookInfo`, **cron** executes cron commands; **set** / **unset** / **reset** the webhook. |
| l         | **l**oop: Number of seconds to loop the script for (used for getUpdates method). |
|           | This would be used mainly via CLI, to continually get updates for a certain period. |
| i         | **i**nterval: Number of seconds to wait between getUpdates requests (used for getUpdates method, default is 2). |
|           | This would be used mainly via CLI, to continually get updates for a certain period, every **i** seconds. |
| g         | **g**roup: Commands group for cron (only used together with `cron` action, default group is `default`). |
|           | Define which group of commands to execute via cron. Can be a comma separated list of groups. |

#### via browser

Simply point your browser to the `manager.php` file with the necessary **GET** parameters:
- `http://example.com/manager.php?s=<secret>&a=<action>&l=<loop>&i=<interval>`

**Webhook**

Set, unset and reset the webhook:
- `http://example.com/manager.php?s=super_secret&a=set`
- `http://example.com/manager.php?s=super_secret&a=unset`
- `http://example.com/manager.php?s=super_secret&a=reset` (unset & set combined)

**getUpdates**

Handle updates once:
- `http://example.com/manager.php?s=super_secret&a=handle` or simply
- `http://example.com/manager.php?s=super_secret` (`handle` action is the default)

Handle updates for 30 seconds, fetching every 5 seconds:
- `http://example.com/manager.php?s=super_secret&l=30&i=5`

**cron**

Execute commands via cron:
- `http://example.com/manager.php?s=super_secret&a=cron&g=maintenance` or multiple groups
- `http://example.com/manager.php?s=super_secret&a=cron&g=maintenance,cleanup`

#### via CLI

When using CLI, the secret is not necessary (since it could just be read from the file itself).

Call the `manager.php` file directly using `php` and pass the parameters:
- `$ php manager.php a=<action> l=<loop> i=<interval>`

**Webhook**

Set, unset and reset the webhook:
- `$ php manager.php a=set`
- `$ php manager.php a=unset`
- `$ php manager.php a=reset` (unset & set combined)

**getUpdates**

Handle updates once:
- `$ php manager.php a=handle` or simply
- `$ php manager.php` (`handle` action is the default)

Handle updates for 30 seconds, fetching every 5 seconds:
- `$ php manager.php l=30 i=5`

**cron**

Execute commands via cron:
- `$ php manager.php a=cron g=maintenance` or multiple groups
- `$ php manager.php a=cron g=maintenance,cleanup`

### Create the manager PHP file

You can name this file whatever you like, it just has to be somewhere inside your PHP project (preferably in the root folder to make things easier).
(Let's assume our file is called `manager.php`)

Let's start off with a simple example that uses the webhook method:
```php
<?php

use TelegramBot\TelegramBotManager\BotManager;

// Charger le compositeur.
require_once __DIR__ . '/vendor/autoload.php';

try {
    $bot = new BotManager([
        // Vitals!
        'api_key'      => '12345:my_api_key',

        // Extras.
        'bot_username' => 'my_own_bot',
        'secret'       => 'super_secret',
        'webhook'      => [
            'url' => 'https://example.com/manager.php',
        ]
    ]);
    $bot->run();
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
```

### Set vital bot parameters

The only vital parameter is the API key:

```php
$bot = new BotManager([
    // (chaîne) Clé API de bot fournie par @BotFather.
    'api_key' => '12345:my_api_key',
    ...
]);
```

### Set extra bot parameters

Apart from the necessary API key, the bot can be easily configured using extra parameters.

Set the webhook? Enable admins? Add custom command paths?

**All no problem!**

The `secret` is a user-defined key that is required to execute any of the library's features via webhook.
Best make it long, random and very unique!

For 84 random characters:
- If you have `pwgen` installed, just execute `pwgen 84 1` and copy the output.
- If you have `openssl` installed, use `openssl rand -hex 84`.
- Or just go [here][random-characters] and put all the output onto a single line.

(You get 2 guesses why 84 is a good number :wink:)

Below is a complete list of all available extra parameters.

```php
$bot = new BotManager([
    ...
    // (chaîne) Nom d’utilisateur du bot qui a été défini lors de la création du bot.
    'bot_username'     => 'my_own_bot',

    // (chaîne) Un mot de passe secret requis pour autoriser l’accès au webhook.
    'secret'           => 'super_secret',

    // (tableau) Toutes les options qui ont à voir avec le webhook.
    'webhook'          => [
        // (chaîne) URL du fichier PHP du gestionnaire utilisé pour configurer le webhook.
        'url'             => 'https://example.com/manager.php',
        // (chaîne) Chemin d’accès à un certificat auto-signé (si nécessaire).
        'certificate'     => __DIR__ . '/server.crt',
        // (int) Nombre maximal autorisé de connexions HTTPS simultanées au webhook.
        'max_connections' => 20,
        // (tableau) Répertoriez les types de mises à jour que vous souhaitez que votre bot reçoive.
        'allowed_updates' => ['message', 'edited_channel_post', 'callback_query'],
        // (chaîne) Jeton secret pour valider les requêtes webhook.
        'secret_token'    => 'super_secret_token',
    ],

    // (bool) N’autorisez l’accès au webhook qu’à partir d’adresses IP d’API Telegram valides.
    'validate_request' => true,
    // (tableau) Lorsque vous utilisez 'validate_request', autorisez également ces adresses IP.
    'valid_ips'        => [
        '1.2.3.4',         // single
        '192.168.1.0/24',  // CIDR
        '10/8',            // CIDR (short)
        '5.6.*',           // wildcard
        '1.1.1.1-2.2.2.2', // range
    ],

    // (tableau) Toutes les options qui ont à voir avec le limiteur.
    'limiter'          => [
        // (bool) Activez ou désactivez la fonctionnalité de limiteur.
        'enabled' => true,
        // (tableau) Toutes les options supplémentaires à passer au limiteur.
        'options' => [
            // (float) Intervalle entre les descripteurs de demande.
            'interval' => 0.5,
        ],
    ],

    // (tableau) Tableau d’ID utilisateur disposant d’un accès administrateur à votre bot (doit être composé d’entiers).
    'admins'           => [12345],

    // (tableau) Informations d’identification Mysql pour connecter une base de données (nécessaire pour la méthode ['getUpdates'](#using-getupdates-method)!).
    'mysql'            => [
        'host'         => '127.0.0.1',
        'port'         => 3306,           // optional
        'user'         => 'root',
        'password'     => 'root',
        'database'     => 'telegram_bot',
        'table_prefix' => 'tbl_prfx_',    // optional
        'encoding'     => 'utf8mb4',      // optional
    ],

    // (tableau) Liste des chemins configurables.
    'paths'            => [
        // (chaîne) Chemin de téléchargement personnalisé.
        'download' => __DIR__ . '/Download',
        // (chaîne) Chemin de téléchargement personnalisé.
        'upload'   => __DIR__ . '/Upload',
    ],

    // (tableau) Toutes les options qui ont à voir avec les commandes.
    'commands'         => [
        // (array) Liste des chemins d’accès aux commandes personnalisées.
        'paths'   => [
            __DIR__ . '/CustomCommands',
        ],
        // (tableau) Liste de toutes les configurations de commandes personnalisées.
        'configs' => [
            'sendtochannel' => ['your_channel' => '@my_channel'],
            'weather'       => ['owm_api_key' => 'owm_api_key_12345'],
        ],
    ],

    // (tableau) Toutes les options qui ont à voir avec cron.
    'cron'             => [
        // (tableau) Liste des groupes qui contiennent les commandes à exécuter.
        'groups' => [
            // Chaque groupe a un nom et un tableau de commandes.
            //Lorsqu’aucun groupe n’est défini, le groupe par défaut est exécuté.
            'default'     => [
                '/default_cron_command',
            ],
            'maintenance' => [
                '/db_cleanup',
                '/db_repair',
                '/message_admins Maintenance completed',
            ],
        ],
    ],

    // (chaîne) Remplacez l’entrée personnalisée de votre bot (principalement à des fins de test!).
    'custom_input'     => '{"some":"raw", "json":"update"}',
]);
```

### Using getUpdates method

Using the `getUpdates` method must not have a `webhook` parameter set and requires a MySQL database connection:
```php
$bot = new BotManager([
    ...
    // Extras.
    'mysql' => [
        'host'         => '127.0.0.1',
        'port'         => 3306,           // optional
        'user'         => 'root',
        'password'     => 'root',
        'database'     => 'telegram_bot',
        'table_prefix' => 'tbl_prfx_',    // optional
        'encoding'     => 'utf8mb4',      // optional
    ],
]);
```

Now, the updates can be done either through the [browser](#via-browser) or [via CLI](#via-cli).

#### Custom getUpdates output

A callback can be defined, to override the default output when updates are handled via getUpdates.

Example of the default output:
```
...
2017-07-10 14:59:25 - Updates processed: 1
123456: <text>
2017-07-10 14:59:27 - Updates processed: 0
2017-07-10 14:59:30 - Updates processed: 0
2017-07-10 14:59:32 - Updates processed: 0
2017-07-10 14:59:34 - Updates processed: 1
123456: <photo>
2017-07-10 14:59:36 - Updates processed: 0
...
```

Using custom callback that must return a string:
```php
// Dans manager.php après $bot défini :
$bot->setCustomGetUpdatesCallback(function (ServerResponse $get_updates_response) {
    $results = array_filter((array) $get_updates_response->getResult());

    return sprintf('There are %d update(s)' . PHP_EOL, count($results));
});
```
output:
```
...
There are 0 update(s)
There are 0 update(s)
There are 2 update(s)
There are 1 update(s)
...
```

## Development

When running live bot tests on a fork, you must enter the following environment variables to your [repository settings][github-actions-encrypted-secrets]:
```
API_KEY="12345:your_api_key"
BOT_USERNAME="username_of_your_bot"
```
It probably makes sense for you to create a new dummy bot for this.

## Security

See [SECURITY](SECURITY.md) for more information.


    
