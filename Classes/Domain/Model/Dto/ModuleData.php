<?php


namespace NL\NlDmailsubscription\Domain\Model\Dto;


class ModuleData
{
    /**
     * @var AddressDemand
     */
    protected $addressDemand = null;

    /**
     * @param AddressDemand $demand
     */
    public function injectAddressDemand(AddressDemand $demand)
    {
        $this->addressDemand = $demand;
    }

    /**
     * @return AddressDemand
     */
    public function getAddressDemand(): AddressDemand
    {
        return $this->addressDemand;
    }

    /**
     * @param AddressDemand $addressDemand
     */
    public function setAddressDemand(AddressDemand $addressDemand): void
    {
        $this->addressDemand = $addressDemand;
    }
}