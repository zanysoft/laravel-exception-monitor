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
     * Whether the message text should be interpreted in Slack's
     * Markdown-like language
     *
     * @var boolean
     */
    protected $allow_markdown = true;

    /**
     * The attachment fields which should be formatted with
     * Slack's Markdown-like language
     *
     * @var array
     */
    protected $markdown_in_attachments = [];

    /**
     * An array of attachments to send
     *
     * @var array
     */
    protected $attachments = [];

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

        /*if (isset($attributes['link_names'])) $this->setLinkNames($attributes['link_names']);

        if (isset($attributes['unfurl_links'])) $this->setUnfurlLinks($attributes['unfurl_links']);

        if (isset($attributes['unfurl_media'])) $this->setUnfurlMedia($attributes['unfurl_media']);*/

        if (isset($attributes['allow_markdown'])) $this->setAllowMarkdown($attributes['allow_markdown']);

        if (isset($attributes['markdown_in_attachments'])) $this->setMarkdownInAttachments($attributes['markdown_in_attachments']);

        $this->client = new Client();
    }

    /**
     * Get the message text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
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
     * Get the channel we will post to
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
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
     * Get the username we will post as
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
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
     * Get the icon (either URL or emoji) we will post as
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
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
     * Get the icon type being used, if an icon is set
     *
     * @return string
     */
    public function getIconType()
    {
        return $this->iconType;
    }

    /**
     * Get whether message text should be formatted with
     * Slack's Markdown-like language
     *
     * @return boolean
     */
    public function getAllowMarkdown()
    {
        return $this->allow_markdown;
    }

    /**
     * Set whether message text should be formatted with
     * Slack's Markdown-like language
     *
     * @param boolean $value
     * @return void
     */
    public function setAllowMarkdown($value)
    {
        $this->allow_markdown = (boolean)$value;

        return $this;
    }

    /**
     * Enable Markdown formatting for the message
     *
     * @return void
     */
    public function enableMarkdown()
    {
        $this->setAllowMarkdown(true);

        return $this;
    }

    /**
     * Disable Markdown formatting for the message
     *
     * @return void
     */
    public function disableMarkdown()
    {
        $this->setAllowMarkdown(false);

        return $this;
    }

    /**
     * Get the attachment fields which should be formatted
     * in Slack's Markdown-like language
     *
     * @return array
     */
    public function getMarkdownInAttachments()
    {
        return $this->markdown_in_attachments;
    }

    /**
     * Set the attachment fields which should be formatted
     * in Slack's Markdown-like language
     *
     * @param array $fields
     * @return $this
     */
    public function setMarkdownInAttachments(array $fields)
    {
        $this->markdown_in_attachments = $fields;

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
    public function attach($options, $fields = [])
    {
        if (empty($fields)) {
            $fields = $options['fields'] ?? [];
        }
        $attachment = [
            'fallback' => $options['fallback'] ?? null,
            'text' => $options['text'] ?? null,
            'pretext' => $options['pretext'] ?? null,
            'color' => $options['color'] ?? 'danger',
            'mrkdwn_in' => $options['mrkdwn_in'] ?? $this->getMarkdownInAttachments(),
            'image_url' => $options['image_url'] ?? null,
            'thumb_url' => $options['thumb_url'] ?? null,
            'title' => $options['title'] ?? null,
            'title_link' => $options['title_link'] ?? null,
            'author_name' => $options['author_name'] ?? null,
            'author_link' => $options['author_link'] ?? null,
            'author_icon' => $options['author_icon'] ?? null
        ];

        $attachment_fields = [];
        foreach ($fields as $field) {
            $value = $field['value'] ?? null;
            $short = $field['value'] ?? false;
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
     * Get the attachments for the message
     *
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
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
        $payload = $this->preparePayload();

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $this->client->post($this->endpoint, ['body' => $encoded]);
    }

    /**
     * Prepares the payload to be sent to the webhook
     *
     * @return array
     */
    protected function preparePayload()
    {
        $payload = [
            'text' => $this->getText(),
            'channel' => $this->getChannel(),
            'username' => $this->getUsername(),
            //'link_names' => $this->getLinkNames() ? 1 : 0,
            //'unfurl_links' => $this->getUnfurlLinks(),
            //'unfurl_media' => $this->getUnfurlMedia(),
            'mrkdwn' => $this->getAllowMarkdown()
        ];

        if ($icon = $this->getIcon()) {
            $payload[$this->getIconType()] = $icon;
        }

        $payload['attachments'] = $this->getAttachments();

        return $payload;
    }
}