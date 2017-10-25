<?php

namespace Tgallice\FBMessenger\Model;

interface ThreadSetting {
    // Setting type
    const TYPE_GREETING = 'greeting';
    const TYPE_CALL_TO_ACTIONS = 'call_to_actions';
    const TYPE_DOMAIN_WHITELISTING = 'domain_whitelisting';
    const TYPE_GET_STARTED = 'get_started';
    const TYPE_PERSISTENT_MENU = 'persistent_menu';

    // Thread state
    const NEW_THREAD = 'new_thread';
    const EXISTING_THREAD = 'existing_thread';
}
