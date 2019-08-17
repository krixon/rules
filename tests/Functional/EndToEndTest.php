<?php

namespace Krixon\Rules\Tests\Functional;

use DateTimeZone;
use Krixon\Rules\Ast\ComparisonNode;
use Krixon\Rules\Compiler\Compiler;
use Krixon\Rules\Compiler\DelegatingCompiler;
use Krixon\Rules\Exception\CompilerError;
use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Parser\DefaultParser;
use Krixon\Rules\Parser\Parser;
use Krixon\Rules\Specification\BooleanMatchesGenerator;
use Krixon\Rules\Specification\DateMatchesGenerator;
use Krixon\Rules\Specification\NumberMatchesGenerator;
use Krixon\Rules\Specification\Specification;
use Krixon\Rules\Specification\StringMatchesGenerator;
use Krixon\Rules\Specification\TimezoneMatchesGenerator;
use PHPUnit\Framework\TestCase;

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
            new StringMatchesGenerator(),
            new NumberMatchesGenerator(),
            new BooleanMatchesGenerator(),
            new DateMatchesGenerator()
        );

        // Ensure TimezoneMatches is used for timezone regex expressions.
        $this->compiler->register(
            new class() extends TimezoneMatchesGenerator
            {
                public function attempt(ComparisonNode $comparison) : ?Specification
                {
                    if ($comparison->identifierFullName() !== 'timezone') {
                        return null;
                    }

                    return parent::attempt($comparison);
                }
            },
            100
        );

        $this->parser = new DefaultParser();
    }


    /**
     * @dataProvider expressionProvider
     *
     * @param mixed $value
     *
     * @throws CompilerError
     * @throws SyntaxError
     */
    public function testExpression(string $expression, $value, bool $expected) : void
    {
        $ast           = $this->parser->parse($expression);
        $specification = $this->compiler->compile($ast);

        static::assertSame($expected, $specification->isSatisfiedBy($value));
    }


    public static function expressionProvider() : array
    {
        return [
            ['name is "Arnold Rimmer"', 'Arnold Rimmer', true],
            ['name == "Arnold Rimmer"', 'Arnold Rimmer', true],
            ['name is "Arnold Rimmer"', 'Dave Lister', false],
            ['name not "Arnold Rimmer"', 'Arnold Rimmer', false],
            ['name not "Arnold Rimmer"', 'Dave Lister', true],
            ['name != "Arnold Rimmer"', 'Dave Lister', true],
            ['name not is "Arnold Rimmer"', 'Dave Lister', true],
            ['name is "Arnold Rimmer" or name is "Dave Lister"', 'Arnold Rimmer', true],
            ['name is "Arnold Rimmer" or name is "Dave Lister"', 'Dave Lister', true],
            ['name is "Arnold Rimmer" or name is "Dave Lister"', 'Kryten', false],
            ['name in ["Arnold Rimmer", "Dave Lister"]', 'Arnold Rimmer', true],
            ['name in ["Arnold Rimmer", "Dave Lister"]', 'Dave Lister', true],
            ['name in ["Arnold Rimmer", "Dave Lister"]', 'Kryten', false],
            ['name > "Arnold Rimmer"', 'Dave Lister', true],
            ['name not > "Arnold Rimmer"', 'Dave Lister', false],
            ['name != > "Arnold Rimmer"', 'Dave Lister', false], // Weird but valid!
            ['name >= "Arnold Rimmer"', 'Dave Lister', true],
            ['name < "Arnold Rimmer"', 'Dave Lister', false],
            ['name <= "Arnold Rimmer"', 'Dave Lister', false],
            ['name matches "/Arnold/"', 'Arnold Rimmer', true],
            ['name matches "/arnold/i"', 'Arnold Rimmer', true],
            ['name matches "/dave/i"', 'Arnold Rimmer', false],

            ['age is 42', 42, true],
            ['age is 42', 42.0, true],
            ['age is 42', 42.1, false],
            ['age > 42', 42, false],
            ['age > 42', 42.0, false],
            ['age > 42', 42.1, true],
            ['age >= 42', 42, true],
            ['age >= 42', 42.0, true],
            ['age >= 42', 42.1, true],
            ['age >= 42', 41, false],
            ['age >= 42', 41.9999999, false],

            ['timezone is timezone:"Europe/London"', new DateTimeZone('Europe/London'), true],
            ['timezone is timezone:"Europe/London"', new DateTimeZone('UTC'), false],
            ['timezone not timezone:"Europe/London"', new DateTimeZone('Europe/London'), false],
            ['timezone not timezone:"Europe/London"', new DateTimeZone('UTC'), true],
            ['timezone matches "/utc/i"', new DateTimeZone('UTC'), true],
            ['timezone matches "/europe/i"', new DateTimeZone('Europe/London'), true],
            ['timezone matches "/london/i"', new DateTimeZone('Europe/London'), true],
            ['timezone matches "/europe/i"', new DateTimeZone('UTC'), false],
            ['timezone in [timezone:"Europe/London", timezone:"Asia/Tokyo"]', new DateTimeZone('UTC'), false],
            ['timezone in [timezone:"Europe/London", timezone:"Asia/Tokyo"]', new DateTimeZone('Europe/London'), true],
            ['timezone in [timezone:"Europe/London", timezone:"Asia/Tokyo"]', new DateTimeZone('Asia/Tokyo'), true],
        ];
    }
}