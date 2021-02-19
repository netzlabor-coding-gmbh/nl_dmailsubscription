<?php

namespace NL\NlDmailsubscription\Domain\Validator;


use NL\NlDmailsubscription\Domain\Model\Address;
use NL\NlDmailsubscription\Domain\Repository\AddressRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class UniqueAddressValidator extends AbstractValidator
{
    /**
     * @var AddressRepository
     */
    protected $addressRepository;

    /**
     * @param AddressRepository $addressRepository
     */
    public function injectAddressRepository(AddressRepository $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * @var array
     */

    protected $supportedOptions = array(
        'property' => array('email', 'The property to use for address lookup', 'string'),
        'global' => array(false, 'Check unique globally'),
        'dirty' => array(false, 'Validate only if property is dirty'),
    );

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param Address $address
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\TooDirtyException
     */
    protected function isValid($address)
    {
        $property = $this->options['property'];

        if ($this->options['dirty'] && !$address->_isDirty($property)) {
            return true;
        }

        $value = ObjectAccess::getPropertyPath($address, $property);

        $count = $this->addressRepository->countByField($property, $value, !$this->options['global']);

        if ($count !== 0) {

            /* @var $error Error */
            $error = GeneralUtility::makeInstance(
                Error::class,
                $this->translateErrorMessage(
                    'validator.uniqueAddress.invalid',
                    'nl_dmailsubscription',
                    [$value]
                ),
                1516001603
            );

            $this->result->forProperty($property)->addError($error);
            return false;
        }

        return true;
    }
}