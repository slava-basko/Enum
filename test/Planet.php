<?php

namespace DASPRiD\EnumTest;

use DASPRiD\Enum\AbstractEnum;

/**
 * @method static self MERCURY()
 * @method static self VENUS()
 * @method static self EARTH()
 * @method static self MARS()
 * @method static self JUPITER()
 * @method static self SATURN()
 * @method static self URANUS()
 * @method static self NEPTUNE()
 */
final class Planet extends AbstractEnum
{
    protected static $MERCURY = [3.303e+23, 2.4397e6];
    protected static $VENUS = [4.869e+24, 6.0518e6];
    protected static $EARTH = [5.976e+24, 6.37814e6];
    protected static $MARS = [6.421e+23, 3.3972e6];
    protected static $JUPITER = [1.9e+27, 7.1492e7];
    protected static $SATURN = [5.688e+26, 6.0268e7];
    protected static $URANUS = [8.686e+25, 2.5559e7];
    protected static $NEPTUNE = [1.024e+26, 2.4746e7];

    /**
     * Universal gravitational constant.
     */
    private static $G = 6.67300E-11;

    /**
     * Mass in kilograms.
     *
     * @var float
     */
    private $mass;

    /**
     * Radius in meters.
     *
     * @var float
     */
    private $radius;

    /**
     * @param float $mass
     * @param float $radius
     */
    protected function __construct($mass, $radius)
    {
        $this->mass = $mass;
        $this->radius = $radius;
    }

    /**
     * @return float
     */
    public function mass()
    {
        return $this->mass;
    }

    /**
     * @return float
     */
    public function radius()
    {
        return $this->radius;
    }

    /**
     * @return float
     */
    public function surfaceGravity()
    {
        return self::$G * $this->mass / ($this->radius * $this->radius);
    }

    /**
     * @param float $otherMass
     * @return float
     */
    public function surfaceWeight($otherMass)
    {
        return $otherMass * $this->surfaceGravity();
    }
}
