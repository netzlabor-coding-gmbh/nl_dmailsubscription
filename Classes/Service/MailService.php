<?php

namespace NL\NlDmailsubscription\Service;


use NL\NlDmailsubscription\Domain\Model\Address;
use NL\NlDmailsubscription\SettingsTrait;
use NL\NlDmailsubscription\ViewTrait;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class MailService implements SingletonInterface
{
    use SettingsTrait, ViewTrait;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    protected $settings;

    protected $frameworkConfiguration;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;

        $this->frameworkConfiguration = $this
            ->configurationManager
            ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

        $this->settings = $this
            ->configurationManager
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                $this->frameworkConfiguration['extensionName'],
                $this->frameworkConfiguration['pluginName']
            );
    }

    /**
     * @param Address $address
     * @param string $hash
     * @param string $hashUri
     * @param string $expiryDate
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendSubscriptionConfirmationMessage($address, $hash, $hashUri = '', $expiryDate = '')
    {
        return $this->sendMessage(
            $address->getEmail(),
            $this->getSettingsValue(
                'mail.subscriptionConfirmationSubject',
                LocalizationUtility::translate(
                    'tx_nldmailsubscription.mail.subscription_confirmation_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$this->getSettingsValue('mail.sitename', $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'])]
                )
            ),
            'SubscriptionConfirmation',
            [
                'address' => $address,
                'hash' => $hash,
                'hashUri' => $hashUri,
                'expiryDate' => $expiryDate,
            ]
        );
    }

    /**
     * @param Address $address
     * @param string $hash
     * @param string $hashUri
     * @param string $expiryDate
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function sendUnsubscriptionConfirmationMessage($address, $hash, $hashUri, $expiryDate)
    {
        return $this->sendMessage(
            $address->getEmail(),
            $this->getSettingsValue(
                'mail.unsubscriptionConfirmationSubject',
                LocalizationUtility::translate(
                    'tx_nldmailsubscription.mail.unsubscription_confirmation_subject',
                    $this->frameworkConfiguration['extensionName'],
                    [$this->getSettingsValue('mail.sitename', $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'])]
                )
            ),
            'UnsubscriptionConfirmation',
            [
                'address' => $address,
                'hash' => $hash,
                'hashUri' => $hashUri,
                'expiryDate' => $expiryDate,
            ]
        );
    }

    /**
     * @param $to
     * @param $subject
     * @param $view
     * @param array $values
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    protected function sendMessage($to, $subject, $view, $values = [])
    {
        $mergedValues = ['settings' => $this->settings];

        ArrayUtility::mergeRecursiveWithOverrule($mergedValues, $values);

        /* @var MailMessage */
        $mail = $this->objectManager->get(MailMessage::class);
        $mail
            ->setTo($to)
            ->setFrom($this->getFrom())
            ->setSubject($subject);

        $htmlView = $this->getView('Mail/' . $view, 'html');
        $htmlView->assignMultiple($mergedValues);
        $htmlBody = $htmlView->render();

        $mail->setBody($htmlBody, 'text/html');

        $plainView = $this->getView('Mail/' . $view, 'txt');
        $plainView->assignMultiple($mergedValues);
        $plainBody = $plainView->render();

        $mail->addPart($plainBody, 'text/plain');

        $mail->send();

        return $mail->isSent();
    }

    /**
     * @return array|mixed
     */
    protected function getFrom()
    {
        return $this->getSettingsValue('mail.fromName', MailUtility::getSystemFromName()) ?
            [
                $this->getSettingsValue('mail.fromEmail', MailUtility::getSystemFromAddress()) =>
                $this->getSettingsValue('mail.fromName', MailUtility::getSystemFromName())
            ] :
            $this->getSettingsValue('mail.fromEmail', MailUtility::getSystemFromAddress());
    }
}