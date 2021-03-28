<?php

namespace App\Helpers;

use Str;

class Address {

    private const REPLACEMENTS = [
        '/\b(N\.|North\b)/i' => 'N',
        '/\b(S\.|South\b)/i' => 'S',
        '/\b(E\.|East\b)/i' => 'E',
        '/\b(W\.|West\b)/i' => 'W',
        '/\b(State|St\.?) (Route\b|Rt\.?)/i' => 'SR',
        '/\bSR\.|SR\b/i' => 'SR',
        '/\b(Rt\.|Route\b)/i' => 'Rt',
        '/\b(St\.|Street\b)/i' => 'St',
        '/\b(Rd\.|Road\b)/i' => 'Rd',
        '/\b(Ct\.|Court\b)/i' => 'Ct',
        '/\bU\.?S(\.|\b)/i' => 'US',
        '/\bOh\b/i' => 'OH',
        '/\bOhio(?= \d{5})/i' => 'OH', // replace 'Ohio' as the state with 'OH'
        '/ (\w+)\n\s/i' => "\n$1 ",  // Fix addresses that got split in the middle of a 2-word city
        '/\s*[\|\r\n]+\s*/i' => "\n",  // replace a pipe with a line break
    ];

    private const REPLACEMENTS_CAPITALIZE = [
        '/("|\'|[\r\n]+)[a-z]/i',
        '/(?<=Mc|Mac)[a-z]/i',
    ];

    public static function standardize($address) {
        // Add linebreak if needed
        $address = static::addLinebreak($address);

        // Force title case for all
        $address = ucwords(strtolower($address));

        // Loop through replacement strings for directions, street abbrev, etc.
        foreach(static::REPLACEMENTS as $regexp => $replace) {
            $address = preg_replace($regexp, $replace, $address);
        }

        // Loop through capitalization rules that don't follow Title Case
        foreach(static::REPLACEMENTS_CAPITALIZE as $regexp) {
            $address = preg_replace_callback(
                $regexp,
                function ($matches) {
                    return strtoupper($matches[0]);
                },
                $address
            );
        }

        return trim($address);
    }

    public static function parseState($address) {
        return preg_replace('/^.*, ([A-Z]{2}) \d{5}.*$/','$1',$address);
    }

    public static function isInState($address, $state) {
        return static::parseState($address,$state) === $state;
    }

    public static function addLinebreak($address)
    {
        // If there's already a line break, just return the original address
        if(preg_match('/\n/',$address)) {
            return $address;
        }

        return preg_replace('/ \w+(?=, OH \d{5})/s', "\n$0", $address);
    }
}