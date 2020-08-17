<?php


namespace NL\NlDmailsubscription\Utility;


use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class ArrayUtility
{
    /**
     * @param array $items
     * @param string $property
     * @param array $values
     * @param bool $strict
     * @return array
     */
    public static function sortByPropertyValues(array $items, string $property, array $values, bool $strict = false): array
    {
        $values = array_values($values);

        usort($items, function ($a, $b) use ($property, $values, $strict) {
            $aVal = ObjectAccess::getProperty($a, $property);
            $bVal = ObjectAccess::getProperty($b, $property);

            $aKey = array_search($aVal, $values, $strict);
            $bKey = array_search($bVal, $values, $strict);

            if ($aKey === $bKey) {
                return 0;
            } elseif (false === $aKey) {
                return -1;
            } elseif (false === $bKey) {
                return 1;
            }

            return $aKey < $bKey ? -1 : 1;
        });

        return $items;
    }

    /**
     * @param array $array
     * @param string $propertyName
     * @param bool $asc
     * @param bool $assoc
     */
    public static function sortByProperty(array &$array, string $propertyName, bool $asc = true, $assoc = false): void
    {
        call_user_func_array($assoc ? 'uasort' : 'usort', [&$array, function ($a, $b) use (&$propertyName, &$asc) {
            $aProp = ObjectAccess::getPropertyPath($a, $propertyName);
            $bProp = ObjectAccess::getPropertyPath($b, $propertyName);
            $factor = $asc ? 1 : -1;
            $res = 0;

            if (is_numeric($aProp) && is_numeric($bProp)) {
                if ($aProp != $bProp) {
                    $res = ($aProp < $bProp) ? -1 : 1;
                }
            } else {
                $res = strcmp(strtolower($aProp), strtolower($bProp));
            }

            return $res * $factor;
        }]);
    }
}