<?php

/*
  |--------------------------------------------------------------------------
  | Detect The Application Environment
  |--------------------------------------------------------------------------
  |
  | Laravel takes a dead simple approach to your application environments
  | so you can just specify a machine name for the host that matches a
  | given environment, then we will automatically detect it for you.
  |
 */

use Dotenv\Dotenv;

$env = $app->detectEnvironment(function () {

    $local_address = [
        "172.169.4.238",
        "localhost"
    ];
    $staging_address = [
        "staging.circlonline.com",
//        "ocirclapi-staging.7fc6rq3ijt.us-east-2.elasticbeanstalk.com",
        "staging-env"
    ];

    $envPath = realpath(dirname(__DIR__));

    // Production ENV
    $envFile = ".env";

    // If is localhost change to Local ENV
//    $remote_address = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : $_SERVER['SERVER_ADDR'];
//    if (in_array($remote_address, $local_address)) {
//        $envFile = ".local.env";
//    } elseif (in_array($remote_address, $staging_address)) {
//        $envFile = ".staging.env";
//    }
//
//    $env = Dotenv::create($envPath, $envFile);
//    $env->overload();
});
