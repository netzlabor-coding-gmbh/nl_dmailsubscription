<?php

namespace NL\NlDmailsubscription\Validation;


class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{
    /**
     * @param string $validateValue
     * @return array
     */
    public function getParsedValidatorAnnotation(string $validateValue): array
    {
        return $this->parseValidatorAnnotation($validateValue);
    }
}