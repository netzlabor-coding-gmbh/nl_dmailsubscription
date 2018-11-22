<?php
defined('TYPO3_MODE') || die();

call_user_func(function()
{
    $extensionKey = 'nl_dmailsubscription';

    /**
     * Register the "DMail Subscription" plugin
     */
    $sformPluginSignature = str_replace('_', '', $extensionKey) . '_sform';

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'NL.NlDmailsubscription',
        'Sform',
        'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang_db.xlf:tt_content.list_type.' . $sformPluginSignature,
        'EXT:' . $extensionKey . '/Resources/Public/Icons/user_plugin_sform.svg'
    );

    // Disable the display of layout and select_key fields for the plugins
    // provided by the extension
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$sformPluginSignature] = 'layout,select_key,pages,recursive';

    // Activate the display of the plug-in flexform field and set FlexForm definition
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$sformPluginSignature] = 'pi_flexform';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $sformPluginSignature, 'FILE:EXT:' . $extensionKey . '/Configuration/FlexForms/flexform_' . $sformPluginSignature . '.xml'
    );
});
