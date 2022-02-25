<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

return [
    "globals" => [
        "notification_gateway" => env('NOTIFICATION_GATEWAY', 'ssl://gateway.push.apple.com:2195'),
        "hyperpay_access_token" => 'OGFjN2E0Yzc3MmE4Zjg3YzAxNzJiMWUyNDhlYzE5YzN8WFFDNTVoTnJzeg==', // test credentials
//        "hyperpay_access_token" => 'OGFjZGE0Y2Q3NTQwYWI4ZDAxNzU0YjAzODJlMDcyYTN8d3B5QVdyc3g4eA==',  // live credentials
        "hyperpay_entity_id" => '8ac7a4c772a8f87c0172b1e3c09f19c7', // test credentials
        "hyperpay_pound_entity_id" => '8ac7a4c8786e850501787790f7b011cf', // test credentials
//        "hyperpay_entity_id" => '8acda4cd7540ab8d01754b03ecbf72aa',  // live credentials
        "hyperpay_base_address" => 'https://test.oppwa.com', // test link
//        "hyperpay_base_address" => 'https://oppwa.com/', // live link
        "hyperpay_full_address" => 'https://test.oppwa.com/v1/checkouts', // test credentials
//        "hyperpay_full_address" => 'https://oppwa.com/v1/checkouts',      // live credetials
        "hyperpay_chargeback_address" => 'https://test.oppwa.com/v1/payments', // test credentials
//        "hyperpay_chargeback_address" => 'https://oppwa.com/v1/payments',      // live credetials
        "SAR" => 4.72,
        "Pound" => 0.21,
        "code_expire_time" => 120,
    ],
    "url" => [
        "staging_url" => "http://boatekstaging-env.eba-hdqvci29.ap-south-1.elasticbeanstalk.com/",
//        "staging_url" => "http://ocirclapi-staging.7fc6rq3ijt.us-east-2.elasticbeanstalk.com/",
        "development_url" => "http://boatekapi-dev.eba-sjrvhsmz.ap-south-1.elasticbeanstalk.com/",
        "production_url" => "https://api-v1.circlonline.com/",
    ],
    'chat_channel' => [
        'one_to_one' => 'presence-message-chat-',
        'personal_presence' => 'presence-boatek-channel-',
        'chat_event' => 'chat-event-',
    ],
    'ipregistry' => [
        'ipregistry_key' => 'nwdh2g829l5hs9xq',
//        'ipregistry_key' => 'tgh247t2zhyrjy',
//        'ipregistry_key' => '611k8s1a3o0g5h',
    ],
    "payfort" => [
        // test payfort url
        'payfort_url' => 'https://sbpaymentservices.payfort.com/FortAPI/paymentApi',
        // production 
//        'payfort_url' => 'https://paymentservices.payfort.com/FortAPI/paymentApi',
        'access_code' => 'rH5fd8LraUrTXGymEU4i',
        'merchant_identifier' => 'b5bc943a',
        'SHA_REQUEST_PHRASE' => '82SJmFaKaJHZES9qUEIuv9&)',
    ],
    "notifications" => [
        "notification_gateway" => env('NOTIFICATION_GATEWAY', 'ssl://gateway.push.apple.com:2195'),
        "bundle_identifier" => "com.app.boatek",
        "key_id" => "NC9VD362J5",
        "team_id" => "2VSTU8J8Z9",
        "p8_key" => "-----BEGIN PRIVATE KEY-----
MIGTAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBHkwdwIBAQQg4P4tms/5wSF0yKSX
rHQyQnOj18nAq7xcte97um4k2NSgCgYIKoZIzj0DAQehRANCAATFklkhGoOPPDu9
rCA8/IT1t4T0pJWz6CIwaQOVEo/3zXTAKJ39MnnGbsaNOyEPCSh76oWOD8GKA1dq
joOZlXPe
-----END PRIVATE KEY-----",
    ],
    'fixer' => [
        'fixer_key' => '74347d47de7750e9bb484c4065a5483b',
    ],
    'subscriptions' => [
        'monthly' => '1',
        'quarterly' => '4',
        'annual' => '12',
        'appointment' => '1',
        'class' => '1'
    ]
];
