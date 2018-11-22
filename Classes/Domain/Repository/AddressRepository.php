<?php

namespace NL\NlDmailsubscription\Domain\Repository;


use TYPO3\CMS\Extbase\Persistence\Repository;

class AddressRepository extends Repository
{
    /**
     *
     */
    public function initializeObject()
    {
        $this->setDefaultQueryIgnoreEnableFields(['disabled']);
    }

    /**
     * @param array $enableFieldsToBeIgnored
     * @param bool $ignoreEnableFields
     * @return $this
     */
    public function setDefaultQueryIgnoreEnableFields($enableFieldsToBeIgnored, $ignoreEnableFields = true)
    {
        $querySettings = $this
            ->createQuery()
            ->getQuerySettings()
            ->setEnableFieldsToBeIgnored($enableFieldsToBeIgnored)
            ->setIgnoreEnableFields($ignoreEnableFields);

        $this->setDefaultQuerySettings($querySettings);

        return $this;
    }

    /**
     * @param bool $respectStoragePage
     * @return $this
     */
    public function setDefaultQueryRespectStoragePage($respectStoragePage = true)
    {
        $querySettings = $this
            ->createQuery()
            ->getQuerySettings()
            ->setRespectStoragePage($respectStoragePage);

        $this->setDefaultQuerySettings($querySettings);

        return $this;
    }

    /**
     * @param mixed $identifier
     * @return object
     */
    public function findByIdentifier($identifier)
    {
        $query = $this->createQuery();

        return $query
            ->matching($query->equals('uid', $identifier))
            ->setLimit(1)
            ->execute()
            ->getFirst();
    }

    /**
     * @param $field
     * @param $value
     * @param bool $respectStoragePage
     * @return int
     */
    public function countByField($field, $value, $respectStoragePage = true)
    {
        $this->setDefaultQueryRespectStoragePage($respectStoragePage);

        $query = $this->createQuery();

        return $query->matching($query->equals($field, $value))->count();
    }
}