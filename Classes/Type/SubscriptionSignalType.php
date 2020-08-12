<?php


namespace NL\NlDmailsubscription\Type;


class SubscriptionSignalType
{
    const AFTER_SUBSCRIBE = 'afterSubscribe';

    const AFTER_UNSUBSCRIBE = 'afterUnsubscribe';

    const AFTER_CONFIRM = 'afterConfirm';

    const AFTER_CONFIRM_UNSUBSCRIBE = 'afterConfirmUnsubscribe';
}