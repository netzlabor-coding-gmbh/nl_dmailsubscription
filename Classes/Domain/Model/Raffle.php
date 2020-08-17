<?php
namespace NL\NlDmailsubscription\Domain\Model;

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
 * Raffle
 */
class Raffle extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * title
     *
     * @var string
     * @validate NotEmpty
     */
    protected $title = '';

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
