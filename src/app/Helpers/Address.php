<?php

namespace App\Helpers;

class Address {

    private const REPLACEMENTS = [
        '/\b(N\.|North\b)/' => 'N',
        '/\b(S\.|South\b)/' => 'S',
        '/\b(E\.|East\b)/' => 'E',
        '/\b(W\.|West\b)/' => 'W',
        '/\b(State|St\.?) (Route\b|Rt\.?)/' => 'SR',
        '/\bSR\./' => 'SR',
        '/\b(Rt\.|Route\b)/' => 'Rt',
        '/\b(St\.|Street\b)/' => 'St',
        '/\b(Rd\.|Road\b)/' => 'Rd',
        '/\b(Ct\.|Court\b)/' => 'Ct',
        '/\bU\.?S(\.|\b)/' => 'US',
    ];

    public static function standardize($address) {
        foreach(static::REPLACEMENTS as $regexp => $replace) {
            $address = preg_replace($regexp, $replace, $address);
        }

        return $address;
    }

    public static function parseState($address) {
        return preg_replace('/^.*, ([A-Z]{2}) \d{5}.*$/','$1',$address);
    }

    public static function isInState($address, $state) {
        return static::parseState($address,$state) === $state;
    }
}