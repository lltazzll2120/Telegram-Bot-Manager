<?php declare(strict_types=1);
/**
 * Ce fichier fait partie du package TelegramBotManager.
 *
 * (c) Tazz <https://discord.gg/5wSZfq9eBf>
 *
 * Pour obtenir toutes les informations sur les droits d’auteur et la licence, veuillez consulter la LICENCE
 * fichier qui a été distribué avec ce code source.
 */

namespace TelegramBot\TelegramBotManager;

use TelegramBot\TelegramBotManager\Exception\InvalidParamsException;

class Params
{
    private static array $valid_script_params = [
        's', // secret
        'a', // action
        'l', // loop
        'i', // interval
        'g', // group (for cron)
    ];

    private static array $valid_vital_bot_params = [
        'api_key',
    ];

    private static array $valid_extra_bot_params = [
        'bot_username',
        'secret',
        'validate_request',
        'valid_ips',
        'webhook',
        'logging',
        'limiter',
        'admins',
        'mysql',
        'paths',
        'commands',
        'cron',
        'custom_input',
    ];

    private array $script_params = [];

    private array $bot_params = [
        'validate_request' => true,
    ];

    /**
     * @throws InvalidParamsException
     */
    public function __construct(array $params)
    {
        $this->validateAndSetBotParams($params);
        $this->validateAndSetScriptParams();
    }

    /**
     * @throws InvalidParamsException
     */
    private function validateAndSetBotParams(array $params): self
    {
        $this->validateAndSetBotParamsVital($params);
        $this->validateAndSetBotParamsSpecial($params);
        $this->validateAndSetBotParamsExtra($params);

        return $this;
    }

    /**
     * @throws InvalidParamsException
     */
    private function validateAndSetBotParamsVital(array $params): void
    {
        foreach (self::$valid_vital_bot_params as $vital_key) {
            if (!array_key_exists($vital_key, $params)) {
                throw new InvalidParamsException('Some vital info is missing: ' . $vital_key);
            }

            $this->bot_params[$vital_key] = $params[$vital_key];
        }
    }

    /**
     * @throws InvalidParamsException
     */
    private function validateAndSetBotParamsSpecial(array $params): void
    {
        // Cas particulier, où le secret DOIT être défini si nous avons un webhook.
        if (($params['webhook']['url'] ?? null) && !($params['secret'] ?? null)) {
            // Cela ne s’applique pas lors de l’utilisation de l’interface de ligne de commande, mais assurez-vous qu’elle est testée!
            if ('cli' !== PHP_SAPI || BotManager::inTest()) {
                throw new InvalidParamsException('Some vital info is missing: secret');
            }
        }
    }

    private function validateAndSetBotParamsExtra(array $params): void
    {
        foreach (self::$valid_extra_bot_params as $extra_key) {
            if (!array_key_exists($extra_key, $params)) {
                continue;
            }

            $this->bot_params[$extra_key] = $params[$extra_key];
        }
    }

    /**
     * Gérez tous les paramètres de script, via le gestionnaire de serveur Web ou l’interface de ligne de commande.
     *
     * https://url/entry.php?s=<secret>&a=<action>&l=<loop>
     * $ php entry.php s=<secret> a=<action> l=<loop>
     */
    private function validateAndSetScriptParams(): self
    {
        $this->setScriptParams();
        $this->validateScriptParams();

        return $this;
    }

    private function setScriptParams(): void
    {
        $this->script_params = $_GET;

        // Si nous n’exécutons pas à partir de l’interface de ligne de commande, les paramètres de script sont déjà définis à partir de _GET $.
        if ('cli' !== PHP_SAPI) {
            return;
        }

        // Nous n’avons pas besoin du premier arg (le nom du fichier).
        $args = array_slice($_SERVER['argv'], 1);

        foreach ($args as $arg) {
            @list($key, $val) = explode('=', $arg);
            isset($key, $val) && $this->script_params[$key] = $val;
        }
    }

    private function validateScriptParams(): void
    {
        $this->script_params = array_intersect_key(
            $this->script_params,
            array_fill_keys(self::$valid_script_params, null)
        );
    }

    public function getBotParam(string $param, mixed $default = null): mixed
    {
        $param_path = explode('.', $param);

        $value = $this->bot_params[array_shift($param_path)] ?? null;
        foreach ($param_path as $sub_param_key) {
            $value = $value[$sub_param_key] ?? null;
            if (null === $value) {
                break;
            }
        }

        return $value ?? $default;
    }

    public function getBotParams(): array
    {
        return $this->bot_params;
    }

    public function getScriptParam(string $param, mixed $default = null): mixed
    {
        return $this->script_params[$param] ?? $default;
    }

    public function getScriptParams(): array
    {
        return $this->script_params;
    }
}
