<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {
        $extensionKey = 'nl_dmailsubscription';

        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'NL.NlDmailsubscription',
                'web', // Make module a submodule of 'web'
                'raffleexport', // Submodule key
                '', // Position
                [
                    'Address' => 'list,export',

                ],
                [
                    'access' => 'user,group',
                    'icon'   => 'EXT:nl_dmailsubscription/Resources/Public/Icons/user_mod_raffleexport.svg',
                    'labels' => 'LLL:EXT:nl_dmailsubscription/Resources/Private/Language/locallang_raffleexport.xlf',
                ]
            );

        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extensionKey, 'Configuration/TypoScript', 'Direct Mail Subscription');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_nldmailsubscription_domain_model_raffle', 'EXT:nl_dmailsubscription/Resources/Private/Language/locallang_csh_tx_nldmailsubscription_domain_model_raffle.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_nldmailsubscription_domain_model_raffle');

        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            'tx_' . str_replace('_', '', $extensionKey) . '-user-plugin-sform',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:' . $extensionKey . '/Resources/Public/Icons/user_plugin_sform.svg']
        );

    }
);
