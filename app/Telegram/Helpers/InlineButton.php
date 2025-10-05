<?php

namespace App\Telegram\Helpers;

class InlineButton
{
    private $buttons = ['inline_keyboard' => []];
    private $buttonNumber = 1;

    public function add(mixed $text, string $action, array $data = [], int $row = 1): self
    {
        $data['action'] = $action;
        $data['button_number'] = $this->buttonNumber;
        $this->buttonNumber++;

        $rowIndex = $row - 1;
        if (!isset($this->buttons['inline_keyboard'][$rowIndex])) {
            $this->buttons['inline_keyboard'][$rowIndex] = [];
        }

        $this->buttons['inline_keyboard'][$rowIndex][] = [
            'text' => $text,
            'callback_data' => json_encode($data),
        ];

        return $this;
    }

    public function link(mixed $text, string $url, int $row = 1): self
    {
        $rowIndex = $row - 1;
        if (!isset($this->buttons['inline_keyboard'][$rowIndex])) {
            $this->buttons['inline_keyboard'][$rowIndex] = [];
        }

        $this->buttons['inline_keyboard'][$rowIndex][] = [
            'text' => $text,
            'url' => $url,
        ];

        return $this;
    }

    public function web_app(mixed $text, string $url, int $row = 1): self
    {
        $rowIndex = $row - 1;
        if (!isset($this->buttons['inline_keyboard'][$rowIndex])) {
            $this->buttons['inline_keyboard'][$rowIndex] = [];
        }

        $this->buttons['inline_keyboard'][$rowIndex][] = [
            'text' => $text,
            'web_app' => [
                'url' => $url
            ],
        ];

        return $this;
    }

    public function pay(mixed $text, int $row = 1): self
    {
        $rowIndex = $row - 1;
        if (!isset($this->buttons['inline_keyboard'][$rowIndex])) {
            $this->buttons['inline_keyboard'][$rowIndex] = [];
        }

        $this->buttons['inline_keyboard'][$rowIndex][] = [
            'text' => $text,
            'pay' => true,
        ];

        return $this;
    }

    public function get(): array
    {
        return $this->buttons;
    }

    public function clear(): self
    {
        $this->buttons = ['inline_keyboard' => []];
        $this->buttonNumber = 1;
        return $this;
    }

    public static function create(): self
    {
        return new self();
    }
}
