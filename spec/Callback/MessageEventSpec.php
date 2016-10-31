<?php

namespace spec\Tgallice\FBMessenger\Callback;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Tgallice\FBMessenger\Model\Callback\Message;

class MessageEventSpec extends ObjectBehavior
{
    function let(Message $message)
    {
        $this->beConstructedWith('sender_id', 'recipient_id', 123456, $message);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Tgallice\FBMessenger\Callback\MessageEvent');
    }

    function it_is_a_callback_event()
    {
        $this->shouldImplement('Tgallice\FBMessenger\Callback\CallbackEvent');
    }

    function it_has_a_sender_id()
    {
        $this->getSenderId()->shouldReturn('sender_id');
    }

    function it_has_a_recipient_id()
    {
        $this->getRecipientId()->shouldReturn('recipient_id');
    }

    function it_has_a_timestamp()
    {
        $this->getTimeStamp()->shouldReturn(123456);
    }

    function it_has_a_message($message)
    {
        $this->getMessage()->shouldReturn($message);
    }

    function it_has_a_type()
    {
        $this->getType()->shouldReturn('message_event');
    }
}