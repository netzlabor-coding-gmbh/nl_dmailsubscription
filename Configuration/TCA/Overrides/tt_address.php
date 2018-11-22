<?php
defined('TYPO3_MODE') || die();

call_user_func(function()
{
    $tmp_nl_dmailsubscription_columns = [
        'tx_nldmailsubscription_confirmed_at' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_address.confirmed_at',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ]
        ],
        'tx_nldmailsubscription_token_identifier' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_address.token_identifier',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'tx_nldmailsubscription_token_expires' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_address.token_expires',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true,
            ]
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tmp_nl_dmailsubscription_columns);
});