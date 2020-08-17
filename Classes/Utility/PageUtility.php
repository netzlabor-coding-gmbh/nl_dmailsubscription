<?php

namespace NL\NlDmailsubscription\Utility;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class PageUtility
{
    /**
     * @param $pid
     * @param int $recursive
     * @return array
     */
    public static function getRecursivePidList($pid, $recursive = 0)
    {
        if (!MathUtility::canBeInterpretedAsInteger($pid) || !MathUtility::forceIntegerInRange($pid, 0)) {
            throw new \InvalidArgumentException('Invalid $pid Value. $pid must be positive integer, ' . gettype($pid) . "($pid) given");
        }

        /* @var QueryGenerator $queryGenerator */
        $queryGenerator = GeneralUtility::makeInstance(QueryGenerator::class);

        $pidList = GeneralUtility::intExplode(
            ',',
            $queryGenerator->getTreeList((int)$pid, $recursive, 0, '')
        );

        if (empty($pidList)) {
            throw new \InvalidArgumentException("Invalid \$pid Value. Page Does Not Exists");
        }

        return $pidList;
    }

    /**
     * @param int $uid
     * @return mixed|null
     */
    public static function getRootPage($uid = null)
    {
        if (null === $uid) {
            $uid = self::getCurrentPageUid();
        }

        $rootline = BackendUtility::BEgetRootLine((int) $uid);

        return isset($rootline[1]) ? $rootline[1] : null;
    }

    /**
     * @param null $uid
     * @return mixed|null
     */
    public static function getRootPageUid($uid = null)
    {
        $root = self::getRootPage($uid);

        return $root ? $root['uid'] : null;
    }

    /**
     * @param int $uid
     * @return array
     */
    public static function getPidListInTree($uid = null)
    {
        $root = self::getRootPage($uid);

        return $root ? self::getRecursivePidList($root['uid'], 999) : [];
    }

    /**
     * @return int
     */
    public static function getCurrentPageUid()
    {
        return (int)GeneralUtility::_GET('id');
    }
}