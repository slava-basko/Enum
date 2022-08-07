<?php

namespace DASPRiD\EnumTest;

use DASPRiD\Enum\AbstractEnum;
use DASPRiD\Enum\Exception\CloneNotSupportedException;
use DASPRiD\Enum\Exception\IllegalArgumentException;
use DASPRiD\Enum\Exception\MismatchException;
use DASPRiD\Enum\Exception\SerializeNotSupportedException;
use DASPRiD\Enum\Exception\UnserializeNotSupportedException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AbstractEnumTest extends TestCase
{
    public function setUp()
    {
        $reflectionClass = new ReflectionClass(AbstractEnum::class);

        $constantsProperty = $reflectionClass->getProperty('constants');
        $constantsProperty->setAccessible(true);
        $constantsProperty->setValue([]);

        $casesProperty = $reflectionClass->getProperty('values');
        $casesProperty->setAccessible(true);
        $casesProperty->setValue([]);

        $allCasesLoadedProperty = $reflectionClass->getProperty('allValuesLoaded');
        $allCasesLoadedProperty->setAccessible(true);
        $allCasesLoadedProperty->setValue([]);
    }

    public function testToString()
    {
        $weekday = WeekDay::FRIDAY();
        self::assertSame('FRIDAY', (string) $weekday);
    }

    public function testName()
    {
        $this->assertSame('WEDNESDAY', WeekDay::WEDNESDAY()->name());
        $this->assertSame('WEDNESDAY', WeekDay::WEDNESDAY()->name);
    }

    public function testOrdinal()
    {
        $this->assertSame(2, WeekDay::WEDNESDAY()->ordinal());
    }

    public function testSameInstanceIsReturned()
    {
        self::assertSame(WeekDay::FRIDAY(), WeekDay::FRIDAY());
        self::assertTrue(WeekDay::FRIDAY() == WeekDay::FRIDAY());
        self::assertTrue(WeekDay::FRIDAY() === WeekDay::FRIDAY());
        self::assertFalse(WeekDay::FRIDAY() != WeekDay::FRIDAY());
        self::assertFalse(WeekDay::FRIDAY() !== WeekDay::FRIDAY());
    }

    public static function testValueOf()
    {
        self::assertSame(WeekDay::FRIDAY(), WeekDay::valueOf('FRIDAY'));
    }

    public static function testTryFromInvalidConstant()
    {
        self::assertSame(null, WeekDay::tryFrom('CATURDAY'));
    }

    public function testValueOfInvalidConstant()
    {
        $this->setExpectedException(IllegalArgumentException::class);
        WeekDay::valueOf('CATURDAY');
    }

    public function testExceptionOnCloneAttempt()
    {
        $this->setExpectedException(CloneNotSupportedException::class);
        clone WeekDay::FRIDAY();
    }

    public function testExceptionOnSerializeAttempt()
    {
        $this->setExpectedException(SerializeNotSupportedException::class);
        serialize(WeekDay::FRIDAY());
    }

    public function testExceptionOnUnserializeAttempt()
    {
        $this->setExpectedException(UnserializeNotSupportedException::class);
        unserialize('O:24:"DASPRiD\\EnumTest\\WeekDay":0:{}');
    }

    public function testReturnValueOfValuesIsSortedByOrdinal()
    {
        // Initialize some week days out of order
        WeekDay::SATURDAY();
        WeekDay::TUESDAY();

        $ordinals = array_values(array_map(function (WeekDay $weekDay) {
            return $weekDay->ordinal();
        }, WeekDay::values()));

        self::assertSame([0, 1, 2, 3, 4, 5, 6], $ordinals);

        $cachedOrdinals = array_values(array_map(function (WeekDay $weekDay) {
            return $weekDay->ordinal();
        }, WeekDay::values()));
        $this->assertSame($ordinals, $cachedOrdinals);
    }

    public function testCompareTo()
    {
        $this->assertSame(-4, WeekDay::WEDNESDAY()->compareTo(WeekDay::SUNDAY()));
        $this->assertSame(4, WeekDay::SUNDAY()->compareTo(WeekDay::WEDNESDAY()));
        $this->assertSame(0, WeekDay::WEDNESDAY()->compareTo(WeekDay::WEDNESDAY()));
    }

    public function testCompareToWrongEnum()
    {
        $this->setExpectedException(MismatchException::class);
        WeekDay::MONDAY()->compareTo(Planet::EARTH());
    }

    public function testParameterizedEnum()
    {
        $planet = Planet::EARTH();
        $this->assertSame(5.976e+24, $planet->mass());
        $this->assertSame(6.37814e6, $planet->radius());
    }
}
