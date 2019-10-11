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

        // wizards
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            'mod {
                wizards.newContentElement.wizardItems.plugins {
                    elements {
                        sform {
                            iconIdentifier = tx_nldmailsubscription-user-plugin-sform
                            title = LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_sform
                            description = LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_sform.description
                            tt_content_defValues {
                                CType = list
                                list_type = nldmailsubscription_sform
                            }
                        }
                    }
                    show = *
                }
            }'
        );
    }
);
