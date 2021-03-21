<?php

namespace App\Helpers;

class Address {

    private const REPLACEMENTS = [
        '/\b(N\.|North\b)/i' => 'N',
        '/\b(S\.|South\b)/i' => 'S',
        '/\b(E\.|East\b)/i' => 'E',
        '/\b(W\.|West\b)/i' => 'W',
        '/\b(State|St\.?) (Route\b|Rt\.?)/i' => 'SR',
        '/\bSR\./i' => 'SR',
        '/\b(Rt\.|Route\b)/i' => 'Rt',
        '/\b(St\.|Street\b)/i' => 'St',
        '/\b(Rd\.|Road\b)/i' => 'Rd',
        '/\b(Ct\.|Court\b)/i' => 'Ct',
        '/\bU\.?S(\.|\b)/i' => 'US',
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