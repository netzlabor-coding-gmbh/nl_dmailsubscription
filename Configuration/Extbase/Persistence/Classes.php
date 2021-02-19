<?php
declare(strict_types = 1);

return [
    \FriendsOfTYPO3\TtAddress\Domain\Model\Address::class => [
        'subclasses' => [
            \NL\NlDmailsubscription\Domain\Model\Address::class
        ]
    ],
    \NL\NlDmailsubscription\Domain\Model\Address::class => [
        'tableName' => 'tt_address',
    ],
];