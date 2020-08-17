<?php


namespace NL\NlDmailsubscription\Domain\Model\Dto;


use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class AddressDemand extends AbstractEntity
{
    /**
     * @var int
     */
    protected $raffle = 0;

    /**
     * @var bool
     */
    protected $showHidden = false;

    /**
     * @return int
     */
    public function getRaffle(): int
    {
        return $this->raffle;
    }

    /**
     * @param int $raffle
     */
    public function setRaffle($raffle): void
    {
        $this->raffle = (int) $raffle;
    }

    /**
     * @return bool
     */
    public function isShowHidden(): bool
    {
        return $this->showHidden;
    }

    /**
     * @param bool $showHidden
     */
    public function setShowHidden($showHidden): void
    {
        $this->showHidden = (bool) $showHidden;
    }
}