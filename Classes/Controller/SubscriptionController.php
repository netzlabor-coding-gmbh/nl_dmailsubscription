<?php

namespace NL\NlDmailsubscription\Controller;


use NL\NlDmailsubscription\Domain\Model\Address;
use NL\NlDmailsubscription\Domain\Repository\AddressRepository;
use NL\NlDmailsubscription\Domain\Validator\AddressTokenValidator;
use NL\NlDmailsubscription\Domain\Validator\UniqueAddressValidator;
use NL\NlDmailsubscription\Property\TypeConverter\AddressObjectConverter;
use NL\NlDmailsubscription\Service\MailService;
use NL\NlDmailsubscription\Utility\LinkUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
     */
    public function showSubscriptionFormAction(Address $address = null)
    {
        $this->view->assign('address', $address);
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
     */
    public function initializeSubscribeAction()
    {
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

        $this->addLocalizedFlashMessage(
            "tx_nldmailsubscription_sform.subscription.$key.message",
            [$address->getEmail()],
            $severity,
            "tx_nldmailsubscription_sform.subscription.$key.title"
        );

        $this->processRedirect('afterSubscription');
    }

    /**
     * @param Address $address
     * @validate $address \NL\NlDmailsubscription\Domain\Validator\ValidAddressValidator(property='email')
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
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
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    protected function processRedirect($redirect)
    {
        if (!$this->getSettingsValue('redirects.disable')) {
            if ($typolink = $this->getSettingsValue('redirects.' . $redirect)) {
                $this->redirectToUri(LinkUtility::typoLinkURL($typolink));
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
}