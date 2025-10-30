<?php

namespace Dranzd\Common\EventSourcing\Util;

final class DateUtil
{
    final public static function isDateValid(string $date): bool
    {
        static $phpVersion = null;
        if (is_null($phpVersion)) {
            $phpVersion = \version_compare(\PHP_VERSION, '5.1.0');
        }

        $valid = false;

        switch (true) {
            case $phpVersion < 0:
                $valid = - 1 != \strtotime($date);
                break;
            case $phpVersion >= 0:
                // valid timestamp according to PHP documentation is from
                // Fri, 13 Dec 1901 20:45:54 UTC to Tue, 19 Jan 2038 03:14:07 UTC
                // which corresponds to minimum and maximum values for 32-bit signed integer
                // -2147454846 = Fri, 13 Dec 1901 20:45:54 UTC
                // for 64 bit, return invalid date if it falls below Fri, 13 Dec 1901 20:45:54 UTC
                $valid = false !== \strtotime($date) && \strtotime($date) >= - 2147454846;
                break;
        }

        return $valid;
    }
}
