<?php

declare(strict_types=1);

namespace PhpClass\Generator;

final class CodeGen
{
    /**
     * Generate a random alphanumeric code of the given length.
     *
     * Uses random_int() (a cryptographically secure PRNG) rather than
     * mt_rand(), since generated codes are commonly used for tokens,
     * confirmation codes, or filenames.
     */
    public function generate(int $length): string
    {
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= match (random_int(1, 3)) {
                1 => (string) random_int(0, 9),
                2 => chr(random_int(65, 90)),
                3 => chr(random_int(97, 122)),
            };
        }

        return $code;
    }
}
