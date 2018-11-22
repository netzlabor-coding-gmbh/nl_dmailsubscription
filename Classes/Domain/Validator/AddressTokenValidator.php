<?php

namespace NL\NlDmailsubscription\Domain\Validator;


use FluidTYPO3\Flux\Form\Field\DateTime;
use NL\NlDmailsubscription\Domain\Model\Address;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class AddressTokenValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = array(
        'equalTo' => array(null, 'Token value to compare with', 'mixed', true),
        'strict' => array(false, 'TRUE for strict comparison (including type), FALSE otherwise', 'boolean'),
        'forProperty' => array('email', 'Property name to add error', 'string'),
    );

    /**
     * Check if $value is valid. If it is not valid, needs to add an error
     * to result.
     *
     * @param Address $address
     * @return bool
     */
    protected function isValid($address)
    {
        $tokenIdentifier = ObjectAccess::getProperty($address, 'tokenIdentifier');

        $equalTo = $this->options['equalTo'];

        $tokenIsValid = $this->options['strict'] ? $tokenIdentifier === $equalTo : $tokenIdentifier == $equalTo;

        if (!$tokenIsValid) {
            /* @var $error Error */
            $error = GeneralUtility::makeInstance(
                Error::class,
                $this->translateErrorMessage(
                    'validator.addressToken.invalid',
                    'nl_dmailsubscription'
                ),
                1515975644
            );

            $this->result->forProperty($this->options['forProperty'])->addError($error);
            return false;
        }

        $tokenIsExpired = (bool)call_user_func([$address, 'isTokenExpired']);

        if ($tokenIsExpired) {
            /* @var $error Error */
            $error = GeneralUtility::makeInstance(
                Error::class,
                $this->translateErrorMessage(
                    'validator.addressToken.expired',
                    'nl_dmailsubscription'
                ),
                1515975493
            );

            $this->result->forProperty($this->options['forProperty'])->addError($error);
            return false;
        }

        return true;
    }
}