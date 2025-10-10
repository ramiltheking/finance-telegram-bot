<?php

namespace App\Telegram\Helpers;

class KeyboardButton
{
    public static $buttons = [
        'keyboard' => [],
        'resize_keyboard' => true,
        'one_time_keyboard' => false,
    ];

    public static function add(mixed $text, int $row = 1)
    {
        $rowIndex = $row - 1;

        if (!isset(self::$buttons['keyboard'][$rowIndex])) {
            self::$buttons['keyboard'][$rowIndex] = [];
        }

        self::$buttons['keyboard'][$rowIndex][] = [
            'text' => $text
        ];
    }

    public static function remove()
    {
        self::$buttons = [
            'remove_keyboard' => true,
        ];
    }

    public static function clear()
    {
        self::$buttons = [
            'keyboard' => [],
            'resize_keyboard' => true,
        ];
    }
}
