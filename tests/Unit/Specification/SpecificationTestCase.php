<?php

namespace Krixon\Rules\Tests\Unit\Specification;

use Krixon\Rules\Specification\Specification;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Exporter\Exporter;
use function sprintf;

abstract class SpecificationTestCase extends TestCase
{
    /**
     * @var Exporter
     */
    private static $exporter;


    public static function setUpBeforeClass() : void
    {
        self::$exporter = new Exporter();
    }


    /**
     * @dataProvider dataProvider
     *
     * @param mixed $value
     */
    public function testIsSatisfiedBy(Specification $specification, $value, bool $expected) : void
    {
        $message = sprintf(
            'Specification of type `%s` was %s by value %s but was expected to be %s.',
            get_class($specification),
            $expected ? 'unsatisfied' : 'satisfied',
            self::$exporter->export($value),
            $expected ? 'satisfied' : 'unsatisfied'
        );

        static::assertSame($expected, $specification->isSatisfiedBy($value), $message);
    }


    abstract public function dataProvider() : array;
}
