<?php

namespace DASPRiD\Enum;

use DASPRiD\Enum\Exception\CloneNotSupportedException;
use DASPRiD\Enum\Exception\IllegalArgumentException;
use DASPRiD\Enum\Exception\MismatchException;
use DASPRiD\Enum\Exception\SerializeNotSupportedException;
use DASPRiD\Enum\Exception\UnserializeNotSupportedException;
use ReflectionClass;
use ReflectionProperty;

/**
 * @property $name
 */
abstract class AbstractEnum
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $ordinal;

    /**
     * @var array<string, array<string, static>>
     */
    private static $values = [];

    /**
     * @var array<string, bool>
     */
    private static $allValuesLoaded = [];

    /**
     * @var array<string, array>
     */
    private static $constants = [];

    /**
     * The constructor is private by default to avoid arbitrary enum creation.
     *
     * When creating your own constructor for a parameterized enum, make sure to declare it as protected, so that
     * the static methods are able to construct it. Avoid making it public, as that would allow creation of
     * non-singleton enum instances.
     */
    private function __construct()
    {
    }

    /**
     * Magic getter which forwards all calls to {@see self::valueOf()}.
     *
     * @param string $name
     * @param array $arguments
     * @return static
     * @throws \DASPRiD\Enum\Exception\IllegalArgumentException|\ReflectionException
     */
    final public static function __callStatic($name, array $arguments)
    {
        return static::valueOf($name);
    }

    /**
     * Returns an enum with the specified name.
     *
     * The name must match exactly an identifier used to declare an enum in this type (extraneous whitespace characters
     * are not permitted).
     *
     * @param string $name
     * @return static
     * @throws IllegalArgumentException if the enum has no constant with the specified name
     * @throws \ReflectionException
     */
    final public static function valueOf($name)
    {
        if (isset(self::$values[static::class][$name])) {
            return self::$values[static::class][$name];
        }

        $constants = self::constants();

        if (array_key_exists($name, $constants)) {
            return self::createValue($name, $constants[$name][0], $constants[$name][1]);
        }

        throw new IllegalArgumentException(sprintf('No enum constant %s::%s', static::class, $name));
    }

    /**
     * Proxy method to match to a native enum.
     *
     * @param $name
     * @return static
     * @throws \DASPRiD\Enum\Exception\IllegalArgumentException
     * @throws \ReflectionException
     */
    final public static function from($name)
    {
        return static::valueOf($name);
    }

    /**
     * Proxy method to match to a native enum.
     *
     * @param $name
     * @return static|null
     */
    final public static function tryFrom($name)
    {
        try {
            return static::valueOf($name);
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $name
     * @param int $ordinal
     * @param array $arguments
     * @return static
     * @throws \ReflectionException
     */
    private static function createValue($name, $ordinal, array $arguments)
    {
        $args = func_get_args();
        $reflect = new ReflectionClass(static::class);
        $instance = $reflect->newInstanceWithoutConstructor();

        $constructor = $reflect->getConstructor();
        $constructor->setAccessible(true);
        $closure = $constructor->getClosure($instance);
        call_user_func_array($closure, $args[2]);

        $instance->name = $name;
        $instance->ordinal = $ordinal;
        self::$values[static::class][$name] = $instance;
        return $instance;
    }

    /**
     * Obtains all possible types defined by this enum.
     *
     * @return static[]
     * @throws \ReflectionException
     */
    final public static function values()
    {
        if (isset(self::$allValuesLoaded[static::class])) {
            return self::$values[static::class];
        }

        if (! isset(self::$values[static::class])) {
            self::$values[static::class] = [];
        }

        foreach (self::constants() as $name => $constant) {
            if (array_key_exists($name, self::$values[static::class])) {
                continue;
            }

            static::createValue($name, $constant[0], $constant[1]);
        }

        uasort(self::$values[static::class], function (self $a, self $b) {
            if ($a->ordinal() == $b->ordinal()) {
                return 0;
            }
            return ($a->ordinal() < $b->ordinal()) ? -1 : 1;
        });

        self::$allValuesLoaded[static::class] = true;
        return self::$values[static::class];
    }

    /**
     * Proxy method to match to a native enum.
     *
     * @return static[]
     * @throws \ReflectionException
     */
    final public static function cases()
    {
        return static::values();
    }

    /**
     * @return array
     */
    private static function constants()
    {
        if (isset(self::$constants[static::class])) {
            return self::$constants[static::class];
        }

        self::$constants[static::class] = [];
        $reflectionClass = new ReflectionClass(static::class);
        $ordinal = -1;

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_STATIC) as $reflectionProperty) {
            if (! $reflectionProperty->isProtected()) {
                continue;
            }

            $reflectionProperty->setAccessible(true);
            $value = $reflectionProperty->getValue();

            self::$constants[static::class][$reflectionProperty->getName()] = [
                ++$ordinal,
                is_array($value) ? $value : []
            ];
        }

        return self::$constants[static::class];
    }

    /**
     * Returns the name of this enum constant, exactly as declared in its enum declaration.
     *
     * Most programmers should use the {@see self::__toString()} method in preference to this one, as the toString
     * method may return a more user-friendly name. This method is designed primarily for use in specialized situations
     * where correctness depends on getting the exact name, which will not vary from release to release.
     *
     * @return string
     */
    final public function name()
    {
        return $this->name;
    }

    /**
     * Proxy method to match to a native enum.
     *
     * @return string
     * @throws \DASPRiD\Enum\Exception\IllegalArgumentException
     */
    final public function __get($name)
    {
        if ($name === 'name') {
            return $this->name;
        }

        throw new IllegalArgumentException(sprintf('No property %s::%s', static::class, $name));
    }

    /**
     * Returns the ordinal of this enumeration constant (its position in its enum declaration, where the initial
     * constant is assigned an ordinal of zero).
     *
     * Most programmers will have no use for this method. It is designed for use by sophisticated enum-based data
     * structures.
     *
     * @return int
     */
    final public function ordinal()
    {
        return $this->ordinal;
    }

    /**
     * Compares this enum with the specified object for order.
     *
     * Returns negative integer, zero or positive integer as this object is less than, equal to or greater than the
     * specified object.
     *
     * Enums are only comparable to other enums of the same type. The natural order implemented by this method is the
     * order in which the constants are declared.
     *
     * @return int
     * @throws MismatchException if the passed enum is not of the same type
     */
    final public function compareTo(self $other)
    {
        if (! $other instanceof static) {
            throw new MismatchException(sprintf(
                'The passed enum %s is not of the same type as %s',
                get_class($other),
                static::class
            ));
        }

        return $this->ordinal - $other->ordinal;
    }

    /**
     * Forbid cloning enums.
     *
     * @throws CloneNotSupportedException
     */
    final public function __clone()
    {
        throw new CloneNotSupportedException();
    }

    /**
     * Forbid serializing enums.
     *
     * @throws SerializeNotSupportedException
     */
    final public function __sleep()
    {
        throw new SerializeNotSupportedException();
    }

    /**
     * Forbid unserializing enums.
     *
     * @throws UnserializeNotSupportedException
     */
    final public function __wakeup()
    {
        throw new UnserializeNotSupportedException();
    }

    /**
     * Turns the enum into a string representation.
     *
     * You may override this method to give a more user-friendly version.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
