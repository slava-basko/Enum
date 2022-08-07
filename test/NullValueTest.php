<?php

namespace DASPRiD\EnumTest;

use DASPRiD\Enum\Exception\CloneNotSupportedException;
use DASPRiD\Enum\Exception\SerializeNotSupportedException;
use DASPRiD\Enum\Exception\UnserializeNotSupportedException;
use DASPRiD\Enum\NullValue;
use PHPUnit\Framework\TestCase;

final class NullValueTest extends TestCase
{
    public function testExceptionOnCloneAttempt()
    {
        $this->setExpectedException(CloneNotSupportedException::class);
        clone NullValue::instance();
    }

    public function testExceptionOnSerializeAttempt()
    {
        $this->setExpectedException(SerializeNotSupportedException::class);
        serialize(NullValue::instance());
    }

    public function testExceptionOnUnserializeAttempt()
    {
        $this->setExpectedException(UnserializeNotSupportedException::class);
        unserialize('O:22:"DASPRiD\\Enum\\NullValue":0:{}');
    }
}
