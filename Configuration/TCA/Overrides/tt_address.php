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
                'eval' => 'datetime,int',
                'default' => 0,
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
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => true,
            ]
        ],
        'tx_nldmailsubscription_raffle' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_address.raffle',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_nldmailsubscription_domain_model_raffle',
                'default' => 0,
                'items' => [
                    ['-', 0]
                ]
            ]
        ],
        'tx_nldmailsubscription_participation_confirmed_at' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_address.participation_confirmed_at',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => true,
            ]
        ],
        'tx_nldmailsubscription_dataprocessing_confirmed_at' => [
            'exclude' => true,
            'label' => 'LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_address.dataprocessing_confirmed_at',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime,int',
                'default' => 0,
                'readOnly' => true,
            ]
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tmp_nl_dmailsubscription_columns);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        ',--div--;LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_db.xlf:tx_nldmailsubscription_domain_model_address.palette,--palette--;;Tx_NlDmailsubscription_TtAddress_Raffle',
        '',
        ''
    );

    $GLOBALS['TCA']['tt_address']['palettes']['Tx_NlDmailsubscription_TtAddress_Raffle'] = array(
        'showitem' => 'tx_nldmailsubscription_raffle'
    );
});