<?php
namespace Tgallice\FBMessenger\Model\Button;
use Tgallice\FBMessenger\Model\Button;
class Nested extends Button
{
    /**
     * @var string
     */
    private $title;
    /**
     * Payload Array
     *
     * @var array
     */
    private $payload;
    /**
     * @param string $title
     * @param array $payload
     */
    public function __construct($title, array $payloads)
    {
        parent::__construct(Button::TYPE_NESTED);
        self::validateTitleSize($title);
        $this->title = $title;
        foreach ($payloads as $payload) {
            Button::validatePayload($payload);
        }
        $this->payload = $payloads;
    }
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }
    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['title'] = $this->title;
        $json['payload'] = $this->payload;
        return $json;
    }
}