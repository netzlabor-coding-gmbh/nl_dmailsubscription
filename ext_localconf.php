<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'NL.NlDmailsubscription',
            'Sform',
            [
                'Subscription' => 'showSubscriptionForm, subscribe, confirmSubscription, showUnsubscriptionForm, unsubscribe, confirmUnsubscription',
            ],
            // non-cacheable actions
            [
                'Subscription' => 'showSubscriptionForm, subscribe, confirmSubscription, showUnsubscriptionForm, unsubscribe, confirmUnsubscription',
            ]
        );
    }
);
