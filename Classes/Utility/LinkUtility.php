<?php


namespace NL\NlDmailsubscription\Utility;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

class LinkUtility
{
    /**
     * @param string $parameter
     * @param string $additionalParams
     * @param bool $useCacheHash
     * @param bool $addQueryString
     * @param string $addQueryStringMethod
     * @param string $addQueryStringExclude
     * @param bool $absolute
     * @return string
     */
    public static function typoLinkURL($parameter, $additionalParams = '', $useCacheHash = false, $addQueryString = false, $addQueryStringMethod = 'GET', $addQueryStringExclude = '', $absolute = false)
    {
        $content = '';
        if ($parameter) {
            $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $content = $contentObject->typoLink_URL(
                [
                    'parameter' => self::createTypolinkParameterFromArguments($parameter, $additionalParams),
                    'useCacheHash' => $useCacheHash,
                    'addQueryString' => $addQueryString,
                    'addQueryString.' => [
                        'method' => $addQueryStringMethod,
                        'exclude' => $addQueryStringExclude
                    ],
                    'forceAbsoluteUrl' => $absolute
                ]
            );
        }

        return $content;
    }

    /**
     * Transforms ViewHelper arguments to typo3link.parameters.typoscript option as array.
     *
     * @param string $parameter Example: 19 _blank - "testtitle with whitespace" &X=y
     * @param string $additionalParameters
     *
     * @return string The final TypoLink string
     */
    protected static function createTypolinkParameterFromArguments($parameter, $additionalParameters = '')
    {
        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typolinkConfiguration = $typoLinkCodec->decode($parameter);

        // Combine additionalParams
        if ($additionalParameters) {
            $typolinkConfiguration['additionalParams'] .= $additionalParameters;
        }

        return $typoLinkCodec->encode($typolinkConfiguration);
    }
}