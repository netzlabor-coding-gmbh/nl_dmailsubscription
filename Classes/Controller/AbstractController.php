<?php

namespace NL\NlDmailsubscription\Controller;

use Kikkoman\KikkomanGoods\SettingsTrait;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 * @package NL\NlDmailsubscription\Controller
 */
abstract class AbstractController extends ActionController
{
    use SettingsTrait;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @param Dispatcher $signalSlotDispatcher
     */
    public function injectSignalSlotDispatcher(Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    /**
     * Returns default settings values
     *
     * @return array
     */
    protected function defaultSettings()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function initializeAction()
    {
        parent::initializeAction();

        $settings = $this->defaultSettings();
        
        if(!is_array($this->settings)) {
            $this->settings = [];
        }

        ArrayUtility::mergeRecursiveWithOverrule($settings, $this->settings, true, false);
        $this->settings = $settings;
    }

    /**
     * Flash messages helper that provides message localization
     *
     * @param string $translationKey
     * @param array $translationArguments
     * @param int $severity
     * @param string $messageTitle
     */
    protected function addLocalizedFlashMessage($translationKey, array $translationArguments = null, $severity = FlashMessage::OK, $messageTitle = '')
    {
        $this->addFlashMessage(
            LocalizationUtility::translate(
                $translationKey,
                $this->request->getControllerExtensionName(),
                $translationArguments
            ),
            ($messageTitle != '' ? LocalizationUtility::translate($messageTitle, $this->request->getControllerExtensionName(), $translationArguments) : ''),
            $severity
        );
    }

    /**
     * @inheritdoc
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * @param $signalName
     * @param array $additionalProperties
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function emitSignal($signalName, $additionalProperties = [])
    {
        $properties = [$this, $this->settings];

        if (!empty($additionalProperties) && is_array($additionalProperties)) {
            array_push($properties, $additionalProperties);
        }

        $this->signalSlotDispatcher->dispatch(
            get_called_class(),
            $signalName,
            $properties
        );
    }
}