<?php

namespace Tgallice\FBMessenger\Model\Attachment\Template\ElementList;

use Tgallice\FBMessenger\Model\Attachment\Template\AbstractElement;
use Tgallice\FBMessenger\Model\Button;
use Tgallice\FBMessenger\Model\DefaultAction;

class Element extends AbstractElement
{
    /**
     * @var []|Button
     */
    private $buttons;

    /**
     * @var null|DefaultAction
     */
    private $defaultAction;

    /**
     * @param string $title
     * @param null|string $subtitle
     * @param null|string $imageUrl
     * @param Button|[] $buttons
     * @param DefaultAction|null $defaultAction
     */
    public function __construct($title, $subtitle = null, $imageUrl = null, $buttons = null, DefaultAction $defaultAction = null)
    {
        parent::__construct($title, $subtitle, $imageUrl);
        $this->buttons = $buttons;
        $this->defaultAction = $defaultAction;
    }

    public function getButtons()
    {
        return $this->buttons;
    }

    public function getDefaultAction()
    {
        return $this->defaultAction;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'title' => $this->getTitle(),
            'subtitle' => $this->getSubtitle(),
            'image_url' => $this->getImageUrl(),
            'buttons' => ($this->buttons instanceof Button) ? [$this->buttons] : $this->buttons,
            'default_action' => $this->defaultAction,
        ];
    }
}
