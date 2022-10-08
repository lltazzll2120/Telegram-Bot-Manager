<?php declare(strict_types=1);
/**
 * Ce fichier fait partie du package TelegramBotManager.
 *
 * (c) Tazz <https://discord.gg/5wSZfq9eBf>
 *
 * Pour obtenir toutes les informations sur les droits d’auteur et la licence, veuillez consulter la LICENCE
 * fichier qui a été distribué avec ce code source.
 */

namespace Tazz\TelegramBot\TelegramBotManager;

use TelegramBot\TelegramBotManager\Exception\InvalidActionException;

class Action
{
    private static array $valid_actions = [
        'set',
        'unset',
        'reset',
        'handle',
        'cron',
        'webhookinfo',
    ];

    private string $action;

    /**
     * @throws InvalidActionException
     */
    public function __construct(?string $action = 'handle')
    {
        $this->action = $action ?: 'handle';

        if (!$this->isAction(self::$valid_actions)) {
            throw new InvalidActionException('Invalid action: ' . $this->action);
        }
    }

    public function isAction(array|string $actions): bool
    {
        return in_array($this->action, (array) $actions, true);
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public static function getValidActions(): array
    {
        return self::$valid_actions;
    }
}
