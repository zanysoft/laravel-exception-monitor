<?php

namespace ZanySoft\LaravelExceptionMonitor\Lib;

use GuzzleHttp\Client;

class Slack
{

    /**
     * Reference to the Slack client responsible for sending
     * the message
     *
     * @var Client
     */
    protected $client;
    /**
     * The Slack incoming webhook endpoint
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The channel the message should be sent to
     *
     * @var string
     */
    protected $channel;

    /**
     * The username the message should be sent as
     *
     * @var string
     */
    protected $username;

    /**
     * The text to send with the message
     *
     * @var string
     */
    protected $text;

    /**
     * The URL to the icon to use
     *
     * @var string
     */
    protected $icon;

    /**
     * The type of icon we are using
     *
     * @var enum
     */
    protected $iconType;

    /**
     * An array of attachments to send
     *
     * @var array
     */
    protected $attachments = [];

    /**
     *  allow markdown
     *
     * @var array
     */
    protected $allow_markdown = true;

    /**
     *
     * @var string
     */
    const ICON_TYPE_URL = 'icon_url';

    /**
     *
     * @var string
     */
    const ICON_TYPE_EMOJI = 'icon_emoji';

    /**
     * Instantiate a new Message
     *
     * @param $endpoint
     * @param array $attributes
     */
    public function __construct($endpoint, array $attributes = [])
    {
        $this->endpoint = $endpoint;

        if (isset($attributes['channel'])) $this->setChannel($attributes['channel']);

        if (isset($attributes['username'])) $this->setUsername($attributes['username']);

        if (isset($attributes['icon'])) $this->setIcon($attributes['icon']);

        if (isset($attributes['allow_markdown'])) $this->allow_markdown = $attributes['allow_markdown'];

        $this->client = new Client(['verify' => false]);
    }

    /**
     * Set the message text
     *
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Set the channel we will post to
     *
     * @param string $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Set the username we will post as
     *
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the icon (either URL or emoji) we will post as.
     *
     * @param string $icon
     * @return this
     */
    public function setIcon($icon)
    {
        if ($icon == null) {
            $this->icon = $this->iconType = null;

            return $this;
        }

        if (mb_substr($icon, 0, 1) == ":" && mb_substr($icon, mb_strlen($icon) - 1, 1) == ":") {
            $this->iconType = self::ICON_TYPE_EMOJI;
        } else {
            $this->iconType = self::ICON_TYPE_URL;
        }

        $this->icon = $icon;

        return $this;
    }

    /**
     * Change the name of the user the post will be made as
     *
     * @param $username
     * @return $this
     */
    public function from($username)
    {
        $this->setUsername($username);

        return $this;
    }

    /**
     * Change the channel the post will be made to
     *
     * @param $channel
     * @return $this
     */
    public function to($channel)
    {
        $this->setChannel($channel);

        return $this;
    }

    /**
     * Chainable method for setting the icon
     *
     * @param $icon
     * @return $this
     */
    public function withIcon($icon)
    {
        $this->setIcon($icon);

        return $this;
    }

    /**
     * Add an attachment to the message
     *
     * @param $options
     * @param $fields
     * @return $this
     */
    public function attach($options)
    {
        $fields = $options['fields'] ?? [];

        $attachment = [
            'title' => $options['title'] ?? null,
            'text' => $options['text'] ?? null,
            'color' => $options['color'] ?? 'danger',
            'ts' => time(),
        ];

        $attachment_fields = [];
        foreach ($fields as $field) {
            $value = $field['value'] ?? null;
            $short = $field['short'] ?? false;
            if ($value) {
                $attachment_fields[] = [
                    'title' => $field['title'] ?? null,
                    'value' => $value,
                    'short' => (boolean)$short
                ];
            }
        }
        $attachment['fields'] = $attachment_fields;

        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Set the attachments for the message
     *
     * @param string $attachments
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $this->clearAttachments();

        foreach ($attachments as $attachment) {
            $this->attach($attachment);
        }

        return $this;
    }

    /**
     * Remove all attachments for the message
     *
     * @return $this
     */
    public function clearAttachments()
    {
        $this->attachments = [];

        return $this;
    }

    /**
     * Send the message
     * @param $text
     * @return void
     */
    public function send($text = null)
    {
        if ($text) {
            $this->setText($text);
        }

        $this->sendMessage();
    }

    /**
     * Send a message
     * @return void
     */
    protected function sendMessage()
    {
        $payload = [
            'text' => $this->text,
            'channel' => $this->channel,
            'username' => $this->username,
            'mrkdwn' => $this->allow_markdown,
        ];

        if ($icon = $this->icon) {
            $payload[$this->iconType] = $icon;
        }

        $payload['attachments'] = $this->attachments;
        //dd($payload);

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $this->client->post($this->endpoint, ['body' => $encoded]);
    }
}