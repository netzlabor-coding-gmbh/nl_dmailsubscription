<?php

namespace NL\NlDmailsubscription\Service;


use NL\NlDmailsubscription\Domain\Model\Address;
use NL\NlDmailsubscription\SettingsTrait;
use NL\NlDmailsubscription\ViewTrait;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\TemplatePaths;

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
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    protected function sendMessage($to, $subject, $view, $values = [])
    {
        $mergedValues = ['settings' => $this->settings, 'title' => $subject];

        ArrayUtility::mergeRecursiveWithOverrule($mergedValues, $values);

        $fluidEmail = GeneralUtility::makeInstance(
            FluidEmail::class,
            GeneralUtility::makeInstance(TemplatePaths::class, $this->getTemplateConfiguration())
        );

        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface) {
            $fluidEmail->setRequest($GLOBALS['TYPO3_REQUEST']);
        }

        $fluidEmail
            ->to(new \Symfony\Component\Mime\Address($to))
            ->from(new \Symfony\Component\Mime\Address(
                $this->getSettingsValue('mail.fromEmail', MailUtility::getSystemFromAddress()),
                $this->getSettingsValue('mail.fromName', MailUtility::getSystemFromName()) ?? ''
            ))
            ->subject($subject)
            ->format('both')
            ->setTemplate($view)
            ->assignMultiple($mergedValues);

        GeneralUtility::makeInstance(Mailer::class)->send($fluidEmail);

        return true;
    }

    /**
     * @return array
     */
    protected function getTemplateConfiguration(): array
    {
        $templateConfiguration = $GLOBALS['TYPO3_CONF_VARS']['MAIL'];

        if (is_array($this->getAbsoluteTemplateRootPaths() ?? null)) {
            $templateRootPaths = $this->getAbsoluteTemplateRootPaths();

            foreach ($templateRootPaths as $key => $path) {
                $templateRootPaths[$key] = rtrim($path, '/') . '/Mail/';
            }

            $templateConfiguration['templateRootPaths'] = array_replace_recursive(
                $templateConfiguration['templateRootPaths'],
                $templateRootPaths
            );
            ksort($templateConfiguration['templateRootPaths']);
        }

        if (is_array($this->getAbsolutePartialRootPaths() ?? null)) {
            $templateConfiguration['partialRootPaths'] = array_replace_recursive(
                $templateConfiguration['partialRootPaths'],
                $this->getAbsolutePartialRootPaths()
            );
            ksort($templateConfiguration['partialRootPaths']);
        }

        if (is_array($this->getAbsoluteLayoutRootPath() ?? null)) {
            $templateConfiguration['layoutRootPaths'] = array_replace_recursive(
                $templateConfiguration['layoutRootPaths'],
                $this->getAbsoluteLayoutRootPath()
            );
            ksort($templateConfiguration['layoutRootPaths']);
        }

        return $templateConfiguration;
    }
}