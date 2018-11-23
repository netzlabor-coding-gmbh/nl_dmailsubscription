<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "nl_dmailsubscription"
 *
 * Auto generated by Extension Builder 2018-05-22
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'NL Dmail Subscription',
    'description' => 'Direct Mail subscription by netzlabor coding GmbH',
    'category' => 'plugin',
    'author' => 'Maksym',
    'author_email' => 'maksim.chubin@netzlabor-coding.de',
    'state' => 'alpha',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-7.99.99',
            'tt_address' => '3.2',
            'direct_mail' => '5.2',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
