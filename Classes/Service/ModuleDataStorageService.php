<?php

namespace NL\NlDmailsubscription\Service;


use NL\NlDmailsubscription\Domain\Model\Dto\ModuleData;
use NL\NlDmailsubscription\Domain\Model\Dto\AddressDemand;
use NL\NlDmailsubscription\Utility\PageUtility;

class ModuleDataStorageService implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var string
     */
    const KEY = 'tx_nldmailsubscription';

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return ModuleData
     */
    public function loadModuleData()
    {
        $moduleData = $GLOBALS['BE_USER']->getModuleData($this->getKey()) ?? '';
        if ($moduleData !== '') {
            $moduleData = @unserialize($moduleData, ['allowed_classes' => [ModuleData::class, AddressDemand::class]]);
            if ($moduleData instanceof ModuleData) {
                return $moduleData;
            }
        }

        return $this->objectManager->get(ModuleData::class);
    }

    /**
     * @param ModuleData $moduleData
     */
    public function persistModuleData(ModuleData $moduleData)
    {
        $GLOBALS['BE_USER']->pushModuleData($this->getKey(), serialize($moduleData));
    }

    protected function getKey()
    {
        return self::KEY . '_' . PageUtility::getRootPageUid();
    }
}
