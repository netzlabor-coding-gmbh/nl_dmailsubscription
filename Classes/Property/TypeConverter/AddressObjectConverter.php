<?php

namespace NL\NlDmailsubscription\Property\TypeConverter;


use NL\NlDmailsubscription\Domain\Repository\AddressRepository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 * Class AddressObjectConverter
 * @package NL\NlDmailsubscription\Property\TypeConverter
 */
class AddressObjectConverter extends PersistentObjectConverter
{
    /**
     * @var AddressRepository
     */
    protected $addressRepository = null;

    /**
     * @param AddressRepository $repository
     */
    public function injectAddressRepository(AddressRepository $repository)
    {
        $this->addressRepository = $repository;
    }

    /**
     * Handle the case if $source is an array.
     *
     * @param array $source
     * @param string $targetType
     * @param array &$convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     * @return object
     * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidPropertyMappingConfigurationException
     * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException
     * @throws \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException
     */
    protected function handleArrayData(array $source, $targetType, array &$convertedChildProperties, \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = null)
    {
        if (isset($source['__identity'])) {
            $object = $this->fetchObjectFromPersistence($source['__identity'], $targetType);

            if (count($source) > 1 && ($configuration === null || $configuration->getConfigurationValue(\TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::class, self::CONFIGURATION_MODIFICATION_ALLOWED) !== true)) {
                throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidPropertyMappingConfigurationException('Modification of persistent objects not allowed. To enable this, you need to set the PropertyMappingConfiguration Value "CONFIGURATION_MODIFICATION_ALLOWED" to TRUE.', 1297932028);
            }
        } else if (isset($source['email']) && !empty($source['email']) && $object = $this->fetchObjectByEmail($source['email'], $targetType)) {
            if (count($source) > 1 && ($configuration === null || $configuration->getConfigurationValue(\TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::class, self::CONFIGURATION_MODIFICATION_ALLOWED) !== true)) {
                throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidPropertyMappingConfigurationException('Modification of persistent objects not allowed. To enable this, you need to set the PropertyMappingConfiguration Value "CONFIGURATION_MODIFICATION_ALLOWED" to TRUE.', 1297932028);
            }
        } else {
            if ($configuration === null || $configuration->getConfigurationValue(\TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter::class, self::CONFIGURATION_CREATION_ALLOWED) !== true) {
                throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidPropertyMappingConfigurationException(
                    'Creation of objects not allowed. To enable this, you need to set the PropertyMappingConfiguration Value "CONFIGURATION_CREATION_ALLOWED" to TRUE',
                    1476044961
                );
            }
            $object = $this->buildObject($convertedChildProperties, $targetType);
        }
        return $object;
    }

    /**
     * Fetch an object from persistence layer.
     *
     * @param mixed $email
     * @param string $targetType
     * @throws \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException
     * @return object
     */
    protected function fetchObjectByEmail($email, $targetType)
    {
        /* @var QueryResultInterface $objects */
        $objects = call_user_func([$this->addressRepository, 'findByEmail'], (string)$email);

        if ($objects->count() > 1) {
            throw new \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException(sprintf('Object of type %s with email "%s" more than one.', $targetType, print_r($email, true)), 1297933823);
        }

        return $objects->getFirst();
    }

    /**
     * Fetch an object from persistence layer.
     *
     * @param mixed $identity
     * @param string $targetType
     * @throws \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException
     * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
     * @return object
     */
    protected function fetchObjectFromPersistence($identity, $targetType)
    {
        if (ctype_digit((string)$identity)) {
            $object = $this->addressRepository->findByIdentifier($identity);
        } else {
            throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException('The identity property "' . $identity . '" is no UID.', 1297931020);
        }

        if ($object === null) {
            throw new \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException(sprintf('Object of type %s with identity "%s" not found.', $targetType, print_r($identity, true)), 1297933823);
        }

        return $object;
    }
}