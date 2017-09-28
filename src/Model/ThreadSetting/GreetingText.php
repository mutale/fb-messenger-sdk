<?php

namespace Tgallice\FBMessenger\Model\ThreadSetting;

use Tgallice\FBMessenger\Model\ThreadSetting;

class GreetingText implements ThreadSetting, \JsonSerializable
{
    /**
     * @var array
     */
    private $greetingTexts;

    /**
     * @param array $greetingTexts
     */
    public function __construct($greetingTexts)
    {
        foreach ($greetingTexts as $localizedText) {
            if (mb_strlen($localizedText['text']) > 160) {
                throw new \InvalidArgumentException('The greeting text should not exceed 160 characters.');
            }
        }


        $this->greetingTexts  = $greetingTexts;
    }

    /**
     * return array
     */
    public function getGreetingTexts()
    {
        return $this->greetingTexts;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return json_encode($this->greetingTexts);
    }
}
