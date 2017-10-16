<?php

return [
    'default' => 'ldap',

    'ldap' => [
        "host" => "localhost",
        "port" => 389,
        "useSsl" => false,
        "username" => "username",
        "password" => "password",
        "bindRequiresDn" => false,
        "baseDn" => "",
        "accountCanonicalForm" => null,
        "accountDomainName" => null,
        "accountDomainNameShort" => null,
        "accountFilterFormat" => null,
        "allowEmptyPassword" => false,
        "useStartTls" => false,
        "optReferrals" => false,
        "tryUsernameSplit" => true,
        "networkTimeout" => null
    ]
];