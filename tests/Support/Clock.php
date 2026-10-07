<?php
namespace Ttt\Tests;

/** Le rate limiting travaille par minute civile : on évite de tester à cheval sur deux minutes. */
final class Clock
{
    public static function waitForSafeWindow(int $needSeconds = 25): void
    {
        $sec = (int)date('s');
        if ($sec > 60 - $needSeconds) {
            sleep(61 - $sec);
        }
    }
}
