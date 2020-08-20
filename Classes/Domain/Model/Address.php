<?php

namespace NL\NlDmailsubscription\Domain\Model;

use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Class Address
 * @package NL\NlDmailsubscription\Domain\Model
 */
class Address extends \FriendsOfTYPO3\TtAddress\Domain\Model\Address
{
    /**
     * @var \DateTime
     */
    protected $crdate;

    /**
     * @var \DateTime
     */
    protected $tstamp;

    /**
     * @var string
     * @validate NotEmpty, EmailAddress, StringLength(minimum=3, maximum=255)
     */
    protected $email;

    /**
     * @var boolean
     */
    protected $hidden;

    /**
     * @var boolean
     */
    protected $moduleSysDmailHtml = 1;

    /**
     * @var \DateTime
     */
    protected $txNldmailsubscriptionConfirmedAt;

    /**
     * @var string
     */
    protected $txNldmailsubscriptionTokenIdentifier = '';

    /**
     * @var \DateTime
     */
    protected $txNldmailsubscriptionTokenExpires;

    /**
     * @var \NL\NlDmailsubscription\Domain\Model\Raffle
     */
    protected $txNldmailsubscriptionRaffle = null;

    /**
     * @var \DateTime
     */
    protected $txNldmailsubscriptionParticipationConfirmedAt;

    /**
     * @var bool
     */
    protected $txNldmailsubscriptionParticipationConfirmed = false;

    /**
     * @var \DateTime
     */
    protected $txNldmailsubscriptionDataprocessingConfirmedAt;

    /**
     * @var bool
     */
    protected $txNldmailsubscriptionDataprocessingConfirmed = false;


    /**
     * @return $this
     * @throws \Exception
     */
    public function confirm()
    {
        $this->setConfirmedAt(new \DateTime());
        $this->setHidden(false);
        $this->setToken('', null);

        return $this;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return (bool)$this->getConfirmedAt();
    }

    /**
     * @return bool
     */
    public function isTokenExpired()
    {
        if ($this->getTokenExpires() instanceof \DateTime) {
            return time() >= $this->getTokenExpires()->getTimestamp();
        }

        return false;
    }

    /**
     * Returns the hidden
     *
     * @return boolean $hidden
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Sets the hidden
     *
     * @param boolean $hidden
     * @return void
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @param int $hashLength
     * @param int $lifetime
     * @return array
     * @throws \Exception
     */
    public function generateToken($hashLength = 32, $lifetime = null)
    {
        $token = [
            'hash' => GeneralUtility::makeInstance(Random::class)->generateRandomHexString($hashLength),
            'expires' => (int)$lifetime > 0 ? $this->getTokenExpiryDateByLifetime($lifetime) : null
        ];

        $this->setToken($token['hash'], $token['expires']);

        return $token;
    }

    /**
     * @param $lifetime
     * @return \DateTime
     * @throws \Exception
     */
    public function getTokenExpiryDateByLifetime($lifetime)
    {
        return new \DateTime(sprintf('now + %d seconds', $lifetime));
    }

    /**
     * @param string $hash
     * @param \DateTime $expires
     */
    public function setToken($hash, $expires = null)
    {
        $this->setTokenIdentifier($hash);
        $this->setTokenExpires($expires);
    }

    /**
     * @param string $hash
     */
    public function setTokenIdentifier($hash)
    {
        $this->setTxNldmailsubscriptionTokenIdentifier($hash);
    }

    /**
     * @param \DateTime $expires
     */
    public function setTokenExpires($expires = null)
    {
        $this->setTxNldmailsubscriptionTokenExpires($expires);
    }

    /**
     * @return string
     */
    public function getTokenIdentifier()
    {
        return $this->getTxNldmailsubscriptionTokenIdentifier();
    }

    /**
     * @return \DateTime
     */
    public function getTokenExpires()
    {
        return $this->getTxNldmailsubscriptionTokenExpires();
    }

    /**
     * Returns the moduleSysDmailHtml
     *
     * @return boolean $moduleSysDmailHtml
     */
    public function getModuleSysDmailHtml()
    {
        return $this->moduleSysDmailHtml;
    }

    /**
     * Sets the moduleSysDmailHtml
     *
     * @param boolean $moduleSysDmailHtml
     * @return void
     */
    public function setModuleSysDmailHtml($moduleSysDmailHtml)
    {
        $this->moduleSysDmailHtml = $moduleSysDmailHtml;
    }

    /**
     * @return \DateTime
     */
    public function getTxNldmailsubscriptionConfirmedAt()
    {
        return $this->txNldmailsubscriptionConfirmedAt;
    }

    /**
     * @param \DateTime $txNldmailsubscriptionConfirmedAt
     */
    public function setTxNldmailsubscriptionConfirmedAt(\DateTime $txNldmailsubscriptionConfirmedAt)
    {
        $this->txNldmailsubscriptionConfirmedAt = $txNldmailsubscriptionConfirmedAt;
    }

    /**
     * @return \DateTime
     */
    public function getConfirmedAt()
    {
        return $this->getTxNldmailsubscriptionConfirmedAt();
    }

    /**
     * @param \DateTime $datetime
     */
    public function setConfirmedAt(\DateTime $datetime)
    {
        return $this->setTxNldmailsubscriptionConfirmedAt($datetime);
    }

    /**
     * @return string
     */
    public function getTxNldmailsubscriptionTokenIdentifier()
    {
        return $this->txNldmailsubscriptionTokenIdentifier;
    }

    /**
     * @param string $txNldmailsubscriptionTokenIdentifier
     */
    public function setTxNldmailsubscriptionTokenIdentifier($txNldmailsubscriptionTokenIdentifier)
    {
        $this->txNldmailsubscriptionTokenIdentifier = $txNldmailsubscriptionTokenIdentifier;
    }

    /**
     * @return \DateTime
     */
    public function getTxNldmailsubscriptionTokenExpires()
    {
        return $this->txNldmailsubscriptionTokenExpires;
    }

    /**
     * @param \DateTime $txNldmailsubscriptionTokenExpires
     */
    public function setTxNldmailsubscriptionTokenExpires($txNldmailsubscriptionTokenExpires)
    {
        $this->txNldmailsubscriptionTokenExpires = $txNldmailsubscriptionTokenExpires;
    }

    /**
     * @return Raffle
     */
    public function getTxNldmailsubscriptionRaffle()
    {
        return $this->txNldmailsubscriptionRaffle;
    }

    /**
     * @param Raffle $txNldmailsubscriptionRaffle
     */
    public function setTxNldmailsubscriptionRaffle(Raffle $txNldmailsubscriptionRaffle = null)
    {
        $this->txNldmailsubscriptionRaffle = $txNldmailsubscriptionRaffle;
    }

    /**
     * @param Raffle $raffle
     */
    public function setRaffle(Raffle $raffle = null)
    {
        return $this->setTxNldmailsubscriptionRaffle($raffle);
    }

    /**
     * @return Raffle
     */
    public function getRaffle()
    {
        return $this->getTxNldmailsubscriptionRaffle();
    }

    /**
     * @return \DateTime
     */
    public function getTxNldmailsubscriptionParticipationConfirmedAt()
    {
        return $this->txNldmailsubscriptionParticipationConfirmedAt;
    }

    /**
     * @param \DateTime $txNldmailsubscriptionParticipationConfirmedAt
     */
    public function setTxNldmailsubscriptionParticipationConfirmedAt(?\DateTime $txNldmailsubscriptionParticipationConfirmedAt)
    {
        $this->txNldmailsubscriptionParticipationConfirmedAt = $txNldmailsubscriptionParticipationConfirmedAt;
    }

    /**
     * @return bool
     */
    public function isTxNldmailsubscriptionParticipationConfirmed()
    {
        return $this->txNldmailsubscriptionParticipationConfirmed;
    }

    /**
     * @param bool $txNldmailsubscriptionParticipationConfirmed
     */
    public function setTxNldmailsubscriptionParticipationConfirmed($txNldmailsubscriptionParticipationConfirmed)
    {
        $this->txNldmailsubscriptionParticipationConfirmed = $txNldmailsubscriptionParticipationConfirmed;
        $this->setTxNldmailsubscriptionParticipationConfirmedAt($this->txNldmailsubscriptionParticipationConfirmed ? new \DateTime() : null);
    }

    /**
     * @return \DateTime
     */
    public function getTxNldmailsubscriptionDataprocessingConfirmedAt()
    {
        return $this->txNldmailsubscriptionDataprocessingConfirmedAt;
    }

    /**
     * @param \DateTime $txNldmailsubscriptionDataprocessingConfirmedAt
     */
    public function setTxNldmailsubscriptionDataprocessingConfirmedAt(?\DateTime $txNldmailsubscriptionDataprocessingConfirmedAt)
    {
        $this->txNldmailsubscriptionDataprocessingConfirmedAt = $txNldmailsubscriptionDataprocessingConfirmedAt;
    }

    /**
     * @return bool
     */
    public function isTxNldmailsubscriptionDataprocessingConfirmed()
    {
        return $this->txNldmailsubscriptionDataprocessingConfirmed;
    }

    /**
     * @param bool $txNldmailsubscriptionDataprocessingConfirmed
     */
    public function setTxNldmailsubscriptionDataprocessingConfirmed(bool $txNldmailsubscriptionDataprocessingConfirmed)
    {
        $this->txNldmailsubscriptionDataprocessingConfirmed = $txNldmailsubscriptionDataprocessingConfirmed;
        $this->setTxNldmailsubscriptionDataprocessingConfirmedAt($this->txNldmailsubscriptionDataprocessingConfirmed ? new \DateTime() : null);
    }

    /**
     * @return bool
     */
    public function isParticipationConfirmed()
    {
        return $this->isTxNldmailsubscriptionParticipationConfirmed();
    }

    /**
     * @return \DateTime
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * @return \DateTime
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}