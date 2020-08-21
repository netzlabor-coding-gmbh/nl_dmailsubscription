<?php

namespace NL\NlDmailsubscription\Domain\Repository;


use NL\NlDmailsubscription\Domain\Model\Dto\AddressDemand;
use TYPO3\CMS\Extbase\Persistence\Repository;

class AddressRepository extends Repository
{
    use QuerySettingsTrait;

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

        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query->matching($query->equals('uid', $identifier))->execute()->getFirst();
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

    /**
     * @param AddressDemand $demand
     * @param bool $raw
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findDemanded(AddressDemand $demand, $raw = false)
    {
        $query = $this->createQuery();

        $constraints = [];

        if ($raffle = $demand->getRaffle()) {
            $constraints[] = $query->equals('txNldmailsubscriptionRaffle', $raffle);
        }

        if (false === $demand->isShowHidden()) {
            $constraints[] = $query->equals('hidden', false);
        }

        if ($constraints) {
            $query->matching($query->logicalAnd($constraints));
        }

        return $query->execute($raw);
    }
}