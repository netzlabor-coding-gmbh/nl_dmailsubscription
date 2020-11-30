<?php

namespace NL\NlDmailsubscription\Domain\Validator;


use NL\NlDmailsubscription\Domain\Model\Address;
use NL\NlDmailsubscription\Domain\Repository\AddressRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

class ValidAddressValidator extends AbstractValidator
{
    /**
     * @var AddressRepository
     */
    protected $addressRepository;

    /* @var ValidatorResolver */
    protected $validatorResolver;

    /**
     * @param ValidatorResolver $validatorResolver
     */
    public function injectValidatorResolver(ValidatorResolver $validatorResolver)
    {
        $this->validatorResolver = $validatorResolver;
    }

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
        'property' => array('', 'The property to use for address lookup', 'string', true)
    );

    /**
     * Check if $address is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param Address $address
     * @return bool
     */
    protected function isValid($address)
    {
        $property = $this->options['property'];
        $propertyValue = ObjectAccess::getProperty($address, $property);

        /* @var $conjunctionValidator \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator */
        $conjunctionValidator = $this->validatorResolver->getBaseValidatorConjunction(Address::class);

        foreach ($conjunctionValidator->getValidators() as $validator) {
            if ($validator instanceof GenericObjectValidator) {
                $propertyValidators = $validator->getPropertyValidators($property);

                foreach ($propertyValidators as $propertyValidator) {
                    /* @var AbstractValidator $propertyValidator */
                    $validationResults = $propertyValidator->validate($propertyValue);

                    if ($validationResults->hasErrors()) {
                        $this->result->forProperty($property)->merge($validationResults);
                    }
                }
            }
        }

        if ($this->result->hasErrors()) {
            return false;
        }

        $countMethod = 'countBy' . ucfirst($property);

        $count = $this->addressRepository->$countMethod($propertyValue);
        if ($count === 0) {
            /* @var $error Error */
            $error = GeneralUtility::makeInstance(
                Error::class,
                $this->translateErrorMessage(
                    'validator.validAddress.invalid',
                    'NlDmailsubscription',
                    [$propertyValue]
                ),
                1514633458
            );

            $this->result->forProperty($property)->addError($error);
            return false;
        }
        return true;
    }
}