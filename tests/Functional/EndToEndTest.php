<?php

namespace Krixon\Rules\Tests\Functional;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\Compiler;
use Krixon\Rules\Compiler\DelegatingCompiler;
use Krixon\Rules\Compiler\SpecificationGenerator;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Operator;
use Krixon\Rules\Parser\DefaultParser;
use Krixon\Rules\Parser\Parser;
use Krixon\Rules\Specification\BooleanMatches;
use Krixon\Rules\Specification\BooleanMatchesGenerator;
use Krixon\Rules\Specification\Contains;
use Krixon\Rules\Specification\ContainsGenerator;
use Krixon\Rules\Specification\DateMatches;
use Krixon\Rules\Specification\DateMatchesGenerator;
use Krixon\Rules\Specification\NumberMatches;
use Krixon\Rules\Specification\NumberMatchesGenerator;
use Krixon\Rules\Specification\Specification;
use Krixon\Rules\Specification\StringMatches;
use Krixon\Rules\Specification\StringMatchesGenerator;
use Krixon\Rules\Specification\TimezoneMatches;
use Krixon\Rules\Specification\TimezoneMatchesGenerator;
use PHPUnit\Framework\TestCase;
use function json_encode;
use const DATE_ATOM;

class EndToEndTest extends TestCase
{
    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * @var Parser
     */
    private $parser;


    protected function setUp() : void
    {
        parent::setUp();

        $this->compiler = new DelegatingCompiler(
            self::stringMatchesGenerator(),
            self::numberMatchesGenerator(),
            self::booleanMatchesGenerator(),
            self::dateMatchesGenerator(),
            self::timezoneMatchesGenerator(),
            self::containsGenerator()
        );

        $this->parser = new DefaultParser();
    }


    /**
     * @dataProvider expressionProvider
     *
     * @param object $value
     */
    public function testExpression(string $expression, $value, bool $expected) : void
    {
        $specification = $this->compile($expression);

        $message = sprintf(
            'Expression `%s` with value %s was %s but was expected to be %s.',
            $expression,
            $value,
            $expected ? 'unsatisfied' : 'satisfied',
            $expected ? 'satisfied' : 'unsatisfied'
        );

        static::assertSame($expected, $specification->isSatisfiedBy($value), $message);
    }


    public static function expressionProvider() : array
    {
        $rimmer = self::user('Arnold Rimmer', 42, true, '2000-01-01 00:00:00', 'Europe/London', ['git', 'smeg-head']);
        $lister = self::user('Dave Lister', 36.5, true, '2001-02-03 04:05:06', 'Asia/Tokyo', ['bum']);
        $kryten = self::user('Kryten', 224, false, '2145-12-31 10:20:30', 'UTC', []);

        return [
            ['name is "Arnold Rimmer"', $rimmer, true],
            ['name == "Arnold Rimmer"', $rimmer, true],
            ['name is "Arnold Rimmer"', $lister, false],
            ['name not "Arnold Rimmer"', $rimmer, false],
            ['name not "Arnold Rimmer"', $lister, true],
            ['name != "Arnold Rimmer"', $lister, true],
            ['name not is "Arnold Rimmer"', $lister, true],
            ['name is "Arnold Rimmer" or name is "Dave Lister"', $rimmer, true],
            ['name is "Arnold Rimmer" or name is "Dave Lister"', $lister, true],
            ['name is "Arnold Rimmer" or name is "Dave Lister"', $kryten, false],
            ['name in ["Arnold Rimmer", "Dave Lister"]', $rimmer, true],
            ['name in ["Arnold Rimmer", "Dave Lister"]', $lister, true],
            ['name in ["Arnold Rimmer", "Dave Lister"]', $kryten, false],
            ['name > "Arnold Rimmer"', $lister, true],
            ['name not > "Arnold Rimmer"', $lister, false],
            ['name != > "Arnold Rimmer"', $lister, false], // Weird but valid!
            ['name >= "Arnold Rimmer"', $lister, true],
            ['name < "Arnold Rimmer"', $lister, false],
            ['name <= "Arnold Rimmer"', $lister, false],
            ['name matches "/Arnold/"', $rimmer, true],
            ['name matches "/arnold/i"', $rimmer, true],
            ['name matches "/dave/i"', $rimmer, false],

            ['age is 42', $rimmer, true],
            ['age is 36.5', $lister, true],
            ['age is 42.1', $rimmer, false],
            ['age > 42', $rimmer, false],
            ['age > 42.0', $rimmer, false],
            ['age > 42', $kryten, true],
            ['age >= 42', $rimmer, true],
            ['age >= 42.0', $rimmer, true],
            ['age >= 42.1', $rimmer, false],
            ['age >= 42', $lister, false],
            ['age >= 41.999999', $rimmer, true],
            ['age < 42', $rimmer, false],
            ['age < 42.0', $rimmer, false],
            ['age < 42', $kryten, false],
            ['age < 42.0000001', $rimmer, true],
            ['age <= 42', $rimmer, true],
            ['age <= 42.0', $rimmer, true],
            ['age <= 42', $kryten, false],
            ['age <= 42.0000001', $rimmer, true],

            ['git is true', $rimmer, true],
            ['git is true', $kryten, false],
            ['git is false', $rimmer, false],
            ['git is false', $kryten, true],
            ['git == true', $rimmer, true],
            ['git != true', $kryten, true],

            ['dob is date:"2000-01-01 00:00:00"', $rimmer, true],
            ['dob is date:"2000-01-01 00:00:01"', $rimmer, false],
            ['dob not date:"2000-01-01 00:00:00"', $rimmer, false],
            ['dob not date:"2000-01-01 00:00:01"', $rimmer, true],
            ['dob is date:"2000-01-01 00:00:00" in "Europe/London"', $rimmer, true],
            ['dob is date:"2000-01-01 00:00:00" in "Asia/Tokyo"', $rimmer, false],
            ['dob is date:"2001-02-02 19:05:06" in "Europe/London"', $lister, true],
            ['dob is date:"2001-02-03 04:05:06" in "Asia/Tokyo"', $lister, true],
            ['dob > date:"2000-01-01 00:00:00"', $rimmer, false],
            ['dob > date:"2000-01-01 00:00:01"', $rimmer, false],
            ['dob > date:"1999-12-31 23:59:59"', $rimmer, true],
            ['dob > date:"2000-01-01 06:00:00" in "Asia/Tokyo"', $rimmer, true],
            ['dob >= date:"2000-01-01 00:00:00"', $rimmer, true],
            ['dob >= date:"1999-12-31 23:59:59"', $rimmer, true],
            ['dob >= date:"2000-01-01 00:00:01"', $rimmer, false],
            ['dob < date:"2000-01-01 00:00:00"', $rimmer, false],
            ['dob < date:"1999-12-31 23:59:59"', $rimmer, false],
            ['dob < date:"2000-01-01 00:00:01"', $rimmer, true],
            ['dob < date:"2000-01-01 10:00:00" in "Asia/Tokyo"', $rimmer, true],
            ['dob <= date:"2000-01-01 00:00:00"', $rimmer, true],
            ['dob <= date:"2000-01-01 00:00:01"', $rimmer, true],
            ['dob <= date:"1999-12-31 23:59:59"', $rimmer, false],

            ['timezone is timezone:"Europe/London"', $rimmer, true],
            ['timezone is timezone:"Europe/London"', $kryten, false],
            ['timezone not timezone:"Europe/London"', $rimmer, false],
            ['timezone not timezone:"Europe/London"', $kryten, true],
            ['timezone matches "/utc/i"', $kryten, true],
            ['timezone matches "/europe/i"', $rimmer, true],
            ['timezone matches "/london/i"', $rimmer, true],
            ['timezone matches "/europe/i"', $kryten, false],
            ['timezone in [timezone:"Europe/London", timezone:"Asia/Tokyo"]', $kryten, false],
            ['timezone in [timezone:"Europe/London", timezone:"Asia/Tokyo"]', $rimmer, true],
            ['timezone in [timezone:"Europe/London", timezone:"Asia/Tokyo"]', $lister, true],

            ['tags contains "git"', $rimmer, true],
            ['tags contains "bum"', $rimmer, false],
            ['tags contains any "git"', $rimmer, true],
            ['tags contains any of "git"', $rimmer, true],
            ['tags contains all "git"', $rimmer, true],
            ['tags contains all of "git"', $rimmer, true],
            ['tags contains any of ["git", "bum"]', $rimmer, true],
            ['tags contains all of ["git", "bum"]', $rimmer, false],
            ['tags contains any of ["foo", "bar"]', $rimmer, false],
            ['tags contains all of ["foo", "bar"]', $rimmer, false],
            ['tags contains "bum"', $kryten, false],

            ['git is true and age is 42', $rimmer, true],
            ['git is true and age is 42', $lister, false],
            ['git is true and age is 42', $kryten, false],

            ['git is true or (name matches "/dave/i" and age < 40)', $rimmer, true],
            ['git is true or (name matches "/dave/i" and age < 40)', $lister, true],
            ['git is true or (name matches "/dave/i" and age < 40)', $kryten, false],

            ['git is true or (name matches "/dave/i" and age < 40) or age > 100', $rimmer, true],
            ['git is true or (name matches "/dave/i" and age < 40) or age > 100', $lister, true],
            ['git is true or (name matches "/dave/i" and age < 40) or age > 100', $kryten, true],
        ];
    }


    /**
     * @dataProvider compilerErrorProvider
     */
    public function testCompilerError(string $expression, int $expectedCode, ?string $expectedMessage = null) : void
    {
        $this->expectException(CompilerError::class);
        $this->expectExceptionCode($expectedCode);

        if ($expectedMessage !== null) {
            $this->expectExceptionMessageRegExp($expectedMessage);
        }

        $this->compile($expression);
    }


    public static function compilerErrorProvider() : array
    {
        $generic = CompilerError::GENERIC;
        $cmp     = CompilerError::UNSUPPORTED_COMPARISON_OPERATOR;
        $value   = CompilerError::UNSUPPORTED_VALUE_TYPE;

        return [
            ['git > true', $cmp, '/>.+boolean/'],
            ['git > 42', $generic, '/no generator was able to produce a specification/i'],
            ['git >= 42', $generic, '/no generator was able to produce a specification/i'],
            ['git < 42', $generic, '/no generator was able to produce a specification/i'],
            ['git <= 42', $generic, '/no generator was able to produce a specification/i'],
            ['git matches "/foo/"', $generic, '/no generator was able to produce a specification/i'],

            ['name is timezone:"Europe/London"', $value, '/timezone.+name.+string \| regex/'],
            ['name is date:"2012-01-01 00:00:00"', $value, '/date.+name.+string \| regex/'],
        ];
    }


    private static function stringMatchesGenerator() : SpecificationGenerator
    {
        return new class('name', 'email') extends StringMatchesGenerator
        {
            protected function generate(ComparisonNode $comparison) : StringMatches
            {
                return new class(
                    $comparison->literalValue(),
                    $comparison->operator(),
                    $comparison->identifierFullName()
                ) extends StringMatches {
                    private $property;

                    public function __construct($string, Operator $operator, string $property)
                    {
                        parent::__construct($string, $operator);

                        $this->property = $property;
                    }


                    protected function extract($value) : ?string
                    {
                        return $value->{$this->property};
                    }
                };
            }
        };
    }


    private static function numberMatchesGenerator() : SpecificationGenerator
    {
        return new class('age') extends NumberMatchesGenerator
        {
            protected function generate(ComparisonNode $comparison) : NumberMatches
            {
                return new class($comparison->literalValue(), $comparison->operator()) extends NumberMatches
                {
                    protected function extract($value)
                    {
                        return $value->age;
                    }
                };
            }
        };
    }


    private static function booleanMatchesGenerator() : SpecificationGenerator
    {
        return new class('git') extends BooleanMatchesGenerator
        {
            protected function generate(ComparisonNode $comparison) : BooleanMatches
            {
                return new class($comparison->literalValue()) extends BooleanMatches
                {
                    protected function extract($value) : ?bool
                    {
                        return $value->git;
                    }
                };
            }
        };
    }


    private static function dateMatchesGenerator() : SpecificationGenerator
    {
        return new class('dob') extends DateMatchesGenerator
        {
            protected function generate(ComparisonNode $comparison) : DateMatches
            {
                return new class($comparison->literalValue(), $comparison->operator()) extends DateMatches
                {
                    protected function extract($value) : ?DateTimeInterface
                    {
                        return $value->dob;
                    }
                };
            }
        };
    }


    private static function timezoneMatchesGenerator() : SpecificationGenerator
    {
        return new class('timezone') extends TimezoneMatchesGenerator
        {
            protected function generate(ComparisonNode $comparison) : TimezoneMatches
            {
                return new class($comparison->literalValue(), $comparison->operator()) extends TimezoneMatches
                {
                    protected function extract($value) : ?DateTimeZone
                    {
                        return $value->timezone;
                    }
                };
            }
        };
    }


    private static function containsGenerator() : SpecificationGenerator
    {
        return new class('tags') extends ContainsGenerator
        {
            protected function generate(ComparisonNode $comparison) : Contains
            {
                return new class($comparison->literalValue(), $comparison->operator()) extends Contains
                {
                    protected function extract($value) : array
                    {
                        return $value->tags;
                    }
                };
            }
        };
    }


    private static function user(
        string $name,
        float $age,
        bool $git,
        string $dob,
        string $timezone,
        array $tags
    ) {
        $timezone = new DateTimeZone($timezone);
        $dob      = new DateTimeImmutable($dob, $timezone);

        return new class($name, $age, $git, $dob, $timezone, $tags)
        {
            public $name;
            public $age;
            public $git;
            public $dob;
            public $timezone;
            public $tags;

            public function __construct(
                string $name,
                float $age,
                bool $git,
                DateTimeImmutable $dob,
                DateTimeZone $timezone,
                array $tags
            ) {
                $this->name     = $name;
                $this->age      = $age;
                $this->git      = $git;
                $this->dob      = $dob;
                $this->timezone = $timezone;
                $this->tags     = $tags;
            }

            public function __toString() : string
            {
                return json_encode([
                    'name'     => $this->name,
                    'age'      => $this->age,
                    'git'      => $this->git,
                    'dob'      => $this->dob->format(DATE_ATOM),
                    'timezone' => $this->timezone->getName(),
                    'tags'     => $this->tags,
                ]);
            }
        };
    }


    private function compile(string $expression) : Specification
    {
        $ast = $this->parser->parse($expression);

        return $this->compiler->compile($ast);
    }
}
