<?php


namespace NL\NlDmailsubscription\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

trait QuerySettingsTrait
{
    /**
     * @return $this
     */
    public function disableStorage()
    {
        return $this->setDefaultQueryRespectStoragePage(false);
    }

    /**
     * @return $this
     */
    public function enableStorage()
    {
        return $this->setDefaultQueryRespectStoragePage(true);
    }

    /**
     * @param bool $respectStoragePage
     * @return $this
     */
    protected function setDefaultQueryRespectStoragePage($respectStoragePage = true)
    {
        $querySettings = $this
            ->createQuery()
            ->getQuerySettings()
            ->setRespectStoragePage($respectStoragePage);

        $this->setDefaultQuerySettings($querySettings);

        return $this;
    }

    /**
     * @param array $uids
     * @return $this
     */
    public function setStorage(array $uids)
    {
        $querySettings = $this
            ->createQuery()
            ->getQuerySettings()
            ->setStoragePageIds($uids);

        $this->setDefaultQuerySettings($querySettings);

        return $this;
    }

    /**
     * @return $this
     */
    public function displayHidden()
    {
        return $this->setEnableFieldsToBeIgnored(['hidden']);
    }

    /**
     * @param array $enableFieldsToBeIgnored
     * @return $this
     */
    public function ignoreEnableFields(array $enableFieldsToBeIgnored)
    {
        return $this->setEnableFieldsToBeIgnored($enableFieldsToBeIgnored);
    }

    /**
     * @param array $enableFieldsToBeIgnored
     * @return $this
     */
    protected function setEnableFieldsToBeIgnored(array $enableFieldsToBeIgnored)
    {
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = $this
            ->createQuery()
            ->getQuerySettings();

        $querySettings->setIgnoreEnableFields(true);
        $querySettings->setEnableFieldsToBeIgnored($enableFieldsToBeIgnored);

        $this->setDefaultQuerySettings($querySettings);

        return $this;
    }
}
