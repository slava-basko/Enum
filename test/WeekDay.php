<?php

namespace DASPRiD\EnumTest;

use DASPRiD\Enum\AbstractEnum;

/**
 * @method static self MONDAY()
 * @method static self TUESDAY()
 * @method static self WEDNESDAY()
 * @method static self THURSDAY()
 * @method static self FRIDAY()
 * @method static self SATURDAY()
 * @method static self SUNDAY()
 */
final class WeekDay extends AbstractEnum
{
    protected static $MONDAY;
    protected static $TUESDAY;
    protected static $WEDNESDAY;
    protected static $THURSDAY;
    protected static $FRIDAY;
    protected static $SATURDAY;
    protected static $SUNDAY;
}
