<?php
namespace NL\NlDmailsubscription\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/***
 *
 * This file is part of the "Direct Mail Subscription" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020 Maksym <maksim.chubin@netzlabor-coding.de>, netzlabor coding GmbH
 *
 ***/

/**
 * The repository for Raffles
 */
class RaffleRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    use QuerySettingsTrait;

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @param array $uids
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByUids(array $uids): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->matching($query->in('uid', $uids));

        return $query->execute();
    }
}
