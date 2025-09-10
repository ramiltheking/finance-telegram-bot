<?php

namespace App\Facades;

use App\Telegram\Bot;
use App\Telegram\Bot\Message;
use App\Telegram\Bot\File;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Message message(mixed $chat_id, string $text, $reply_id = null)
 * @method static Message editMessage(mixed $chat_id, string $text, int $message_id)
 * @method static Message inlineButtons(mixed $chat_id, string $text, array $buttons)
 * @method static File document(mixed $chat_id, $file, string $filename, $reply_id = null)
 * @method static File photo(mixed $chat_id, $file, string $filename, $reply_id = null)
 * @method static File groupPhoto(mixed $chat_id, array $file_url, $reply_id = null)
 * @method Bot send(string $name)
 */

class Telegram extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */

    protected static function getFacadeAccessor()
    {
        return Telegram::class;
    }
}
