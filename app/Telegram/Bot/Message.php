<?php

namespace App\Telegram\Bot;

class Message extends Bot
{
    protected $data;
    protected $method;

    public function message(mixed $chat_id, string $text, $reply_id = null)
    {
        $this->method = 'sendMessage';
        $this->data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "html",
        ];
        if ($reply_id) {
            $this->data['reply_parameters'] = [
                'message_id' => $reply_id,
            ];
        }
        return $this;
    }

    public function editMessage(mixed $chat_id, string $text, int $message_id)
    {
        $this->method = 'editMessageText';
        $this->data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "html",
            'message_id' => $message_id,
        ];
        return $this;
    }

    public function inlineButtons(mixed $chat_id, string $text, array $buttons, $reply_id = null)
    {
        $this->method = 'sendMessage';
        $this->data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "html",
            'reply_markup' => $buttons
        ];
        if ($reply_id) {
            $this->data['reply_parameters'] = [
                'message_id' => $reply_id,
            ];
        }
        return $this;
    }

    public function editButtons(mixed $chat_id, string $text, ?array $buttons, int $message_id)
    {
        $this->method = 'editMessageText';
        $this->data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "html",
            'message_id' => $message_id,
        ];

        if ($buttons !== null) {
            $this->data['reply_markup'] = $buttons;
        }

        return $this;
    }

    public function editMessageMedia(mixed $chat_id, string $media, string $type = 'photo', ?string $caption = null, ?array $buttons = null, int $message_id)
    {
        $this->method = 'editMessageMedia';
        $this->data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'media' => [
                'type' => $type,
                'media' => $media,
                'caption' => $caption,
                'parse_mode' => 'HTML'
            ]
        ];

        if ($buttons !== null) {
            $this->data['reply_markup'] = $buttons;
        }

        return $this;
    }

    public function deleteMessage(mixed $chat_id, int $message_id)
    {
        $this->method = 'deleteMessage';
        $this->data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
        ];

        return $this;
    }

    public function answerCallbackQuery(string $callbackQueryId, array $params = [])
    {
        $this->method = "answerCallbackQuery";

        $this->data = array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $params);

        return $this;
    }
}
