<?php

namespace App\Facades;

use App\Telegram\Bot;
use App\Telegram\Bot\Message;
use App\Telegram\Bot\File;
use App\Telegram\Bot\Invoice;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Message message(mixed $chat_id, string $text, $reply_id = null)
 * @method static Message editMessage(mixed $chat_id, string $text, int $message_id)
 * @method static Message inlineButtons(mixed $chat_id, string $text, array $buttons)
 * @method static Message editButtons(mixed $chat_id, string $text, ?array $buttons, int $message_id)
 * @method static Message editMessageMedia(mixed $chat_id, string $media, string $type = 'photo', ?string $caption = null, ?array $buttons = null, int $message_id)
 * @method static Message deleteMessage(mixed $chat_id, int $message_id)
 * @method static File document(mixed $chat_id, $file, string $filename, $reply_id = null)
 * @method static File photo(mixed $chat_id, $file, string $filename, $reply_id = null)
 * @method static File groupPhoto(mixed $chat_id, array $file_url, $reply_id = null)
 * @method Invoice createInvoiceLink(string $title, string $description, string $payload, array $prices, string $period = "2592000")
 * @method Invoice createInvoice($chatId, string $title, string $description, string $payload, array $prices, string $currency = 'XTR')
 * @method Bot send(string $name)
 * @method Bot answerPreCheckoutQuery(string $preCheckoutQueryId, bool $ok, string $errorMessage = null)
 * @method Bot answerShippingQuery(string $shippingQueryId, bool $ok, array $shippingOptions = null, string $errorMessage = null)
 * @method Bot sendRequest($method, $data)
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
