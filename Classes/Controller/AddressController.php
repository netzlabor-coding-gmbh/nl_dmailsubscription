<?php


namespace NL\NlDmailsubscription\Controller;


use NL\NlDmailsubscription\Domain\Model\Dto\AddressDemand;
use NL\NlDmailsubscription\Domain\Model\Dto\ModuleData;
use NL\NlDmailsubscription\Domain\Repository\AddressRepository;
use NL\NlDmailsubscription\Domain\Repository\RaffleRepository;
use NL\NlDmailsubscription\Service\ModuleDataStorageService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AddressController extends ActionController
{
    /**
     * Backend Template Container
     *
     * @var string
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var ModuleDataStorageService
     */
    protected $moduleDataStorageService = null;

    /**
     * @var ModuleData
     */
    protected $moduleData = null;

    /**
     * @var AddressRepository
     */
    protected $addressRepository = null;

    /**
     * @var RaffleRepository
     */
    protected $raffleRepository = null;

    /**
     * Page uid
     *
     * @var int
     */
    protected $pageUid = 0;

    /**
     * @var array
     */
    protected $pageInformation = [];

    /**
     * Function will be called before every other action
     *
     */
    public function initializeAction()
    {
        $this->pageUid = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id');
        $this->pageInformation = BackendUtility::readPageAccess($this->pageUid, '');

        parent::initializeAction();
    }

    /**
     * @param ModuleDataStorageService $moduleDataStorageService
     */
    public function injectModuleDataStorageService(ModuleDataStorageService $moduleDataStorageService)
    {
        $this->moduleDataStorageService = $moduleDataStorageService;
    }

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
     * Load and persist module data
     *
     * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request
     * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response)
    {
        $this->moduleData = $this->moduleDataStorageService->loadModuleData();
        // We "finally" persist the module data.
        try {
            parent::processRequest($request, $response);
            $this->moduleDataStorageService->persistModuleData($this->moduleData);
        } catch (\TYPO3\CMS\Extbase\Mvc\Exception\StopActionException $e) {
            $this->moduleDataStorageService->persistModuleData($this->moduleData);
            throw $e;
        }
    }

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);

        if ($this->actionMethodName === 'listAction') {
            $view->getModuleTemplate()->getDocHeaderComponent()->setMetaInformation([]);

            $this->registerDocheaderButtons();
            $this->view->getModuleTemplate()->setFlashMessageQueue($this->controllerContext->getFlashMessageQueue());

            if ($view instanceof BackendTemplateView) {
                $view->getModuleTemplate()->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Modal');
            }

            if (is_array($this->pageInformation)) {
                $this->view->getModuleTemplate()->getDocHeaderComponent()->setMetaInformation($this->pageInformation);
            }

            $returnUrl = rawurlencode(BackendUtility::getModuleUrl($this->request->getPluginName(), ['id' => $this->pageUid]));

            $view->assignMultiple([
                'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                'timeFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                'returnUrl' => $returnUrl,
            ]);
        }
    }

    /**
     * Registers the Icons into the docheader
     *
     * @throws \InvalidArgumentException
     */
    protected function registerDocheaderButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $currentRequest = $this->request;
        $moduleName = $currentRequest->getPluginName();
        $getVars = $this->request->getArguments();

        $extensionName = $currentRequest->getControllerExtensionName();
        if (count($getVars) === 0) {
            $modulePrefix = strtolower('tx_' . $extensionName . '_' . $moduleName);
            $getVars = ['id', 'M', $modulePrefix];
        }
        $shortcutName = LocalizationUtility::translate('backend.template.shortcutName', $extensionName) ?: 'Shortcut';

        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setModuleName($moduleName)
            ->setDisplayName($shortcutName)
            ->setGetVariables($getVars);
        $buttonBar->addButton($shortcutButton);
    }

    /**
     * @param AddressDemand|null $demand
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction(AddressDemand $demand = null)
    {
        if ($demand === null) {
            $demand = $this->moduleData->getAddressDemand();
        } else {
            $this->moduleData->setAddressDemand($demand);
        }

        $addresses = $this->addressRepository->setStorage([$this->pageUid])->ignoreEnableFields(['hidden', 'starttime', 'endtime'])
            ->findDemanded($demand);

        $raffles = $this->raffleRepository->setStorage([$this->pageUid])->ignoreEnableFields(['hidden', 'starttime', 'endtime'])->findAll();

        $this->view->assignMultiple(compact('demand', 'addresses', 'raffles'));
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function exportAction()
    {
        $demand = $this->moduleData->getAddressDemand();

        $addresses = $this->addressRepository->setStorage([$this->pageUid])->ignoreEnableFields(['hidden', 'starttime', 'endtime'])
            ->findDemanded($demand);

        $this->redirect('list');
    }
}