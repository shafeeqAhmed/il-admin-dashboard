<?php

namespace App\payment\checkout\Interfaces;

interface CheckoutInterface
{
    public static function processPayment( $params,$slug);
    public static function makeGhuzzleRequest($params,$slug,$requestType);
}
