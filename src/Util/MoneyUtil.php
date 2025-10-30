<?php

namespace Dranzd\Common\EventSourcing\Util;

class MoneyUtil
{
    /**
     * Convert a float money value to integer with lowest denomination
     *
     * Note: This also fixes the issue with float rounding error
     *
     * @param  float   $floatValue         Money in float value
     * @param  integer $lowestDenomination Lowest denomination of money
     * @return integer                     Value in the lowest denomination
     */
    public static function fromFloatToLowestDenomination($floatValue, $lowestDenomination = 100)
    {
        return (integer) (number_format($floatValue * $lowestDenomination, log10($lowestDenomination), '.', ''));
    }

    /**
     * Convert the lowest denomination value to float value
     *
     * @param  integer $integerValue       Money in lowest denomination value
     * @param  integer $lowestDenomination Lowest denomination of money
     * @return float                       Value in the float value
     */
    public static function fromLowestDenominationToFloat($integerValue, $lowestDenomination = 100)
    {
        return (float) ($integerValue / $lowestDenomination);
    }
}
