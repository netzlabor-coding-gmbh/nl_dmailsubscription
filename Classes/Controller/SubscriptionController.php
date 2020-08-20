<?php

namespace NL\NlDmailsubscription\Controller;


use NL\NlDmailsubscription\Domain\Model\Address;
use NL\NlDmailsubscription\Domain\Model\Raffle;
use NL\NlDmailsubscription\Domain\Repository\AddressRepository;
use NL\NlDmailsubscription\Domain\Repository\RaffleRepository;
use NL\NlDmailsubscription\Domain\Validator\AbstractEntityValidator;
use NL\NlDmailsubscription\Domain\Validator\AddressTokenValidator;
use NL\NlDmailsubscription\Domain\Validator\UniqueAddressValidator;
use NL\NlDmailsubscription\Property\TypeConverter\AddressObjectConverter;
use NL\NlDmailsubscription\Service\MailService;
use NL\NlDmailsubscription\Type\SubscriptionSignalType;
use NL\NlDmailsubscription\Utility\ArrayUtility;
use NL\NlDmailsubscription\Utility\LinkUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;

/**
 * Class Subscription
 * @package NL\NlDmailsubscription\Controller
 */
class SubscriptionController extends AbstractController
{
    /**
     * @var AddressRepository
     */
    protected $addressRepository = null;

    /**
     * @var RaffleRepository
     */
    protected $raffleRepository = null;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var HashService
     */
    protected $hashService;

    /**
     * @var MailService
     */
    protected $mailService;

    /**
     * @var array
     */
    protected $addressObjectConverterActions = [
        'subscribe',
        'unsubscribe',
        'confirmSubscription',
        'confirmUnsubscription'
    ];

    /**
     * @param AddressRepository $repository
     */
    public function injectAddressRepository(AddressRepository $repository)
    {
        $this->addressRepository = $repository;
    }

    /**
     * @param RaffleRepository $repository
     */
    public function injectRaffleRepository(RaffleRepository $repository)
    {
        $this->raffleRepository = $repository;
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * @param HashService $service
     */
    public function injectHashService(HashService $service)
    {
        $this->hashService = $service;
    }

    /**
     * @param MailService $service
     */
    public function injectMailService(MailService $service)
    {
        $this->mailService = $service;
    }

    /**
     * @return array
     */
    public function defaultSettings()
    {
        return [
            'subscription' => [
                'page' => $this->getTypoScriptFrontendController()->id,
                'confirmation' => [
                    'tokenLifetime' => 86400,
                ]
            ],
            'unsubscription' => [
                'page' => $this->getTypoScriptFrontendController()->id,
                'confirmation' => [
                    'tokenLifetime' => 86400,
                ]
            ],
            'redirects' => [
                'afterSubscription' => $this->getTypoScriptFrontendController()->id,
                'afterSubscriptionConfirmation' => $this->getTypoScriptFrontendController()->id,
                'afterUnsubscription' => $this->getTypoScriptFrontendController()->id,
                'afterUnsubscriptionConfirmation' => $this->getTypoScriptFrontendController()->id,
            ],
        ];
    }

    /**
     * @param Address|null $address
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function showSubscriptionFormAction(Address $address = null)
    {
        $this->view->assignMultiple([
            'address' => $address,
            'raffle' => $this->getRaffle(),
        ]);
    }

    /**
     * @param Address|null $address
     */
    public function showUnsubscriptionFormAction(Address $address = null)
    {
        $this->view->assign('address', $address);
    }

    /**
     *
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeAction()
    {
        parent::initializeAction();

        $actionName = $this->request->getControllerActionName();

        if (in_array($actionName, $this->addressObjectConverterActions)) {
            /* @var AddressObjectConverter $typeConverter */
            $typeConverter = $this->objectManager->get(AddressObjectConverter::class);

            $this
                ->arguments
                ->getArgument('address')
                ->getPropertyMappingConfiguration()
                ->setTypeConverter($typeConverter);
        }
    }

    /**
     * @param ViewInterface $view
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    protected function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);

        if ($this->request->hasArgument('address')) {
            $this->view->assign('address', $this->request->getArgument('address'));
        }
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     * @throws \TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function initializeSubscribeAction()
    {
        /** @var PropertyMappingConfiguration $propertyMappingConfiguration */
        $propertyMappingConfiguration = $this
            ->arguments->getArgument('address')->getPropertyMappingConfiguration();

        $propertyMappingConfiguration->setTypeConverterOption(
            PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_MODIFICATION_ALLOWED,
            true
        );

        if ($this->request->hasArgument('address') && $this->getSettingsValue('subscription.confirmation.enable')) {

            /* @var string $email */
            $email = ObjectAccess::getProperty($this->request->getArgument('address'), 'email');

            /* @var Address $address */
            if ($email && $address = call_user_func([$this->addressRepository, 'findOneByEmail'], $email)) {
                if (!$address->isConfirmed()) {

                    /* @var ConjunctionValidator $conjunctionValidator */
                    $conjunctionValidator = $this->arguments->getArgument('address')->getValidator();

                    foreach ($conjunctionValidator->getValidators() as $validator) {
                        if ($validator instanceof UniqueAddressValidator) {
                            $conjunctionValidator->removeValidator($validator);
                        }
                    }
                }
            }
        }

        if ($this->request->hasArgument('address')
            && isset($this->request->getArgument('address')['txNldmailsubscriptionRaffle']) && $this->getRaffle()) {
            /* @var ConjunctionValidator $conjunctionValidator */
            $conjunctionValidator = $this->arguments->getArgument('address')->getValidator();

            /** @var AbstractEntityValidator $abstractEntityValidator */
            $abstractEntityValidator = $this->objectManager->get(AbstractEntityValidator::class, ['settingsPath' => 'raffles.subscribe.validation']);

            $conjunctionValidator->addValidator($abstractEntityValidator);

            if ($this->request->getArgument('address')['txNldmailsubscriptionParticipationConfirmed']) {
                /** @var AbstractEntityValidator $abstractEntityValidator */
                $abstractEntityValidator = $this->objectManager->get(AbstractEntityValidator::class, ['settingsPath' => 'raffles.subscribe.participationValidation']);

                $conjunctionValidator->addValidator($abstractEntityValidator);
            }
        }
    }

    /**
     * @param Address $address
     * @validate $address \NL\NlDmailsubscription\Domain\Validator\UniqueAddressValidator(property='email')
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \Exception
     */
    public function subscribeAction(Address $address)
    {
        $key = 'success';
        $severity = FlashMessage::OK;

        $address->setRaffle($address->isParticipationConfirmed() ? $this->getRaffle() : null);

        $address->setHidden($this->getSettingsValue('subscription.confirmation.enable'));

        $this->addressRepository->add($address);
        $this->persistenceManager->persistAll();

        if ($this->getSettingsValue('subscription.confirmation.enable')) {
            $this->processSubscriptionConfirmation($address);

            $key = 'needConfirm';
            $severity = FlashMessage::INFO;
        } else if ($this->getSettingsValue('subscription.generateUnsubscriptionTokenOnSubscribe')) {
            $tokenLifetime = $this->getSettingsValue('unsubscription.confirmation.tokenLifetime');
            $address->generateToken(64, $tokenLifetime);
        }

        $this->addressRepository->update($address);
        $this->persistenceManager->persistAll();

        $this->emitSignal(SubscriptionSignalType::AFTER_SUBSCRIBE, compact('address'));

        if ($this->request->getFormat() === "json") {
            $this->view->assign('value', array_merge([
                'uri' => $this->processRedirect('afterSubscription', true),
            ], $this->getLocalizedFlashMessage(
                "tx_nldmailsubscription_sform.subscription.$key.message",
                [$address->getEmail()],
                "tx_nldmailsubscription_sform.subscription.$key.title"))
            );
        } else {
            $this->addLocalizedFlashMessage(
                "tx_nldmailsubscription_sform.subscription.$key.message",
                [$address->getEmail()],
                $severity,
                "tx_nldmailsubscription_sform.subscription.$key.title"
            );

            $this->processRedirect('afterSubscription');
        }
    }

    /**
     * @param Address $address
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function unsubscribeAction(Address $address)
    {
        $key = 'success';
        $severity = FlashMessage::OK;

        if ($this->getSettingsValue('unsubscription.confirmation.enable')) {
            $this->processUnsubscriptionConfirmation($address);

            $key = 'needConfirm';
            $severity = FlashMessage::INFO;

            $this->addressRepository->update($address);
        } else {
            $this->addressRepository->remove($address);
        }
        $this->persistenceManager->persistAll();

        $this->emitSignal(SubscriptionSignalType::AFTER_UNSUBSCRIBE, compact('address'));

        $this->addLocalizedFlashMessage(
            "tx_nldmailsubscription_sform.unsubscription.$key.message",
            [$address->getEmail()],
            $severity,
            "tx_nldmailsubscription_sform.unsubscription.$key.title"
        );

        $this->processRedirect('afterUnsubscription');
    }

    /**
     * @param Address $address
     * @param string $hash
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function confirmSubscriptionAction(Address $address, $hash)
    {
        $this->processTokenValidation($address, $hash);

        $address->confirm();

        if ($this->getSettingsValue('subscription.generateUnsubscriptionTokenOnSubscribe')) {
            $tokenLifetime = $this->getSettingsValue('unsubscription.confirmation.tokenLifetime');
            $address->generateToken(64, $tokenLifetime);
        }

        $this->addressRepository->update($address);
        $this->persistenceManager->persistAll();

        $this->emitSignal(SubscriptionSignalType::AFTER_CONFIRM, compact('address'));

        $this->addLocalizedFlashMessage(
            "tx_nldmailsubscription_sform.subscription.success.message",
            [$address->getEmail()],
            FlashMessage::OK,
            "tx_nldmailsubscription_sform.subscription.success.title"
        );

        $this->processRedirect('afterSubscriptionConfirmation');
    }

    /**
     * @param Address $address
     * @param string $hash
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function confirmUnsubscriptionAction(Address $address, $hash)
    {
        $this->processTokenValidation($address, $hash);

        $this->addressRepository->remove($address);
        $this->persistenceManager->persistAll();

        $this->emitSignal(SubscriptionSignalType::AFTER_CONFIRM_UNSUBSCRIBE, compact('address'));

        $this->addLocalizedFlashMessage(
            "tx_nldmailsubscription_sform.unsubscription.success.message",
            [$address->getEmail()],
            FlashMessage::OK,
            "tx_nldmailsubscription_sform.unsubscription.success.title"
        );

        $this->processRedirect('afterUnsubscriptionConfirmation');
    }

    /**
     * @param Address $address
     * @param string $hash
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function processTokenValidation($address, $hash)
    {
        $addressTokenValidator = $this->validatorResolver->createValidator(AddressTokenValidator::class, [
            'equalTo' => $hash,
        ]);

        $validationResults = $addressTokenValidator->validate($address);

        if ($validationResults->hasErrors()) {
            $results = $this->getControllerContext()->getRequest()->getOriginalRequestMappingResults();

            $results->forProperty('address')->merge($validationResults);

            $this->getControllerContext()->getRequest()->setOriginalRequestMappingResults($results);

            $this->forward('showSubscriptionForm');
        }
    }

    /**
     * @param Address $address
     * @return mixed
     * @throws \Exception
     */
    protected function processSubscriptionConfirmation(Address $address)
    {
        $tokenLifetime = $this->getSettingsValue('subscription.confirmation.tokenLifetime');

        $token = $address->generateToken(64, $tokenLifetime);

        $hashUri = $this->getAbsoluteUriFor($this->getSettingsValue('subscription.page'), 'confirmSubscription', [
            'address' => $address,
            'hash' => $token['hash']
        ]);

        return $this->mailService->sendSubscriptionConfirmationMessage($address, $token['hash'], $hashUri, $token['expires']);
    }

    /**
     * @param Address $address
     * @return mixed
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    protected function processUnsubscriptionConfirmation(Address $address)
    {
        $tokenLifetime = $this->getSettingsValue('unsubscription.confirmation.tokenLifetime');

        $token = $address->generateToken(64, $tokenLifetime);

        $hashUri = $this->getAbsoluteUriFor($this->getSettingsValue('unsubscription.page'), 'confirmUnsubscription', [
            'address' => $address,
            'hash' => $token['hash']
        ]);

        return $this->mailService->sendUnsubscriptionConfirmationMessage($address, $token['hash'], $hashUri, $token['expires']);
    }

    /**
     * @param string $redirect
     * @param bool $return
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    protected function processRedirect($redirect, $return = false)
    {
        if (!$this->getSettingsValue('redirects.disable')) {
            if ($typolink = $this->getSettingsValue('redirects.' . $redirect)) {
                $uri = LinkUtility::typoLinkURL($typolink);

                if ($return !== false) {
                    return $uri;
                }

                $this->redirectToUri($uri);
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $page
     * @param string $actionName
     * @param array $controllerArguments
     * @return string
     */
    protected function getAbsoluteUriFor($page, $actionName = null, $controllerArguments = [])
    {
        return $this->getControllerContext()->getUriBuilder()
            ->setTargetPageUid($page)
            ->setCreateAbsoluteUri(true)
            ->setNoCache(true)
            ->uriFor($actionName, $controllerArguments);
    }

    /**
     * @return Raffle|null
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    protected function getRaffle()
    {
        $uids = GeneralUtility::trimExplode(',', $this->getSettingsValue('raffles.uids'), true);

        if (!empty($uids) && ($raffles = $this->raffleRepository->disableStorage()->findByUids($uids))) {
            $raffles = ArrayUtility::sortByPropertyValues($raffles->toArray(), 'uid', $uids);

            /** @var Raffle $raffle */
            $raffle = reset($raffles);

            return $raffle;
        }

        return null;
    }
}