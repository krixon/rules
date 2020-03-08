<?php

namespace Krixon\Rules\Parser;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Krixon\Rules\Ast;
use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Lexer\Lexer;
use Krixon\Rules\Lexer\Token;
use function in_array;
use function sprintf;

class DefaultParser implements Parser
{
    private const HINT_DATE     = 'date';
    private const HINT_TIMEZONE = 'timezone';

    private const HINTS = [
        self::HINT_DATE,
        self::HINT_TIMEZONE,
    ];

    /**
     * @var Token[]
     */
    private $tokens;
    private $pointer;
    private $pointerMax;
    private $lexer;
    private $expression;


    public function __construct(Lexer $lexer = null)
    {
        $this->lexer = $lexer ?: new Lexer();
    }


    public function parse(string $expression) : Ast\Node
    {
        $this->expression = $expression;
        $this->tokens     = $this->lexer->tokenize($this->expression);
        $this->pointer    = 0;
        $this->pointerMax = count($this->tokens) - 1;

        // Check for an empty expression explicitly.
        // This is not technically necessary, but allows for a more helpful error message over the more
        // generic "Expected 'IDENTIFIER', got 'EOF'." that would otherwise be produced.
        if ($this->token()->is(Token::EOF)) {
            throw new SyntaxError('Empty expression.', '', 0);
        }

        $node = $this->parseExpression();

        $this->match(Token::EOF);

        return $node;
    }


    /**
     * @throws SyntaxError
     */
    private function parseExpression($precedence = 0) : Ast\Node
    {
        $left = $this->parsePrimaryNode();

        // Represents the precedence of the current token if it's an operator. Right now every operator has the same
        // level of precedence and everything is left-associate but this will allow us to easily add new operators in
        // the future with varying levels of precedence.
        $operatorPrecedence = 1;

        while ($this->is(...Token::OPERATORS) && $operatorPrecedence > $precedence) {
            $left = $this->parseLogicalExpression($left);
        }

        return $left;
    }


    /**
     * @throws SyntaxError
     */
    private function parsePrimaryNode() : Ast\Node
    {
        if ($this->token()->is(Token::LEFT_PAREN)) {
            $node = $this->parseSubExpression();
        } else {
            $node = $this->parseComparisonExpression();
        }

        return $node;
    }


    /**
     * @throws SyntaxError
     */
    private function parseLogicalExpression(Ast\Node $left) : Ast\Node
    {
        $this->matchLogicalOperator();

        $token = $this->token();

        $this->next();

        switch ($token->type()) {
            case Token::AND:
                return Ast\LogicalNode::and($left, $this->parseExpression(1));
            case Token::OR:
                return Ast\LogicalNode::or($left, $this->parseExpression(1));
            case Token::XOR:
                return Ast\LogicalNode::xor($left, $this->parseExpression(1));
            default:
                // @codeCoverageIgnoreStart
                // This has already been validated by matchLogicalOperator(), but is thrown here to
                // help prevent future bugs if a new token type is implemented without a corresponding branch
                // in this case statement.
                throw SyntaxError::unexpectedToken($this->expression, 'LOGICAL_OPERATOR', $token);
                // @codeCoverageIgnoreEnd
        }
    }


    /**
     * @throws SyntaxError
     */
    private function parseComparisonExpression() : Ast\Node
    {
        $left    = $this->parseIdentifier();
        $negated = false;

        // NOT negates the following comparison operator.

        if ($this->is(Token::NOT)) {
            $next = $this->peek();

            // Consume the NOT.
            $this->next();

            if ($next && $this->isLiteralOrTypeHint($next)) {
                // NOT was found immediately before a literal rather than a comparison operator (e.g. foo not "bar").
                // This is considered shorthand for NOT EQUALS.
                return new Ast\NegationNode(Ast\ComparisonNode::equals($left, $this->parseLiteral()));
            }

            // This is regular negation of the subsequent comparison operator.
            $negated = true;
        }

        $token = $this->token();

        $this->matchComparisonOperator();
        $this->next();

        switch ($token->type()) {
            case TOKEN::EQUALS:
                $node = Ast\ComparisonNode::equals($left, $this->parseLiteral());
                break;
            case TOKEN::GREATER:
                $node = Ast\ComparisonNode::greaterThan($left, $this->parseLiteral());
                break;
            case TOKEN::GREATER_EQUALS:
                $node = Ast\ComparisonNode::greaterThanOrEqualTo($left, $this->parseLiteral());
                break;
            case TOKEN::LESS:
                $node = Ast\ComparisonNode::lessThan($left, $this->parseLiteral());
                break;
            case TOKEN::LESS_EQUALS:
                $node = Ast\ComparisonNode::lessThanOrEqualTo($left, $this->parseLiteral());
                break;
            case TOKEN::IN:
                $node = Ast\ComparisonNode::in($left, $this->parseLiteralList());
                break;
            case TOKEN::CONTAINS:
                $node = $this->parseContainsComparison($left);
                break;
            case TOKEN::MATCHES:
                $node = $this->parseMatchesComparison($left);
                break;
            case TOKEN::BETWEEN:
                $node = $this->parseBetweenComparison($left);
                break;
            default:
                // @codeCoverageIgnoreStart
                // This has already been validated by matchComparisonOperator(), but is thrown here to
                // help prevent future bugs if a new token type is implemented without a corresponding branch
                // in this case statement.
                throw SyntaxError::unexpectedToken($this->expression, 'COMPARISON_OPERATOR', $token);
                // @codeCoverageIgnoreEnd
        }

        if ($negated) {
            $node = new Ast\NegationNode($node);
        }

        return $node;
    }


    /**
     * @throws SyntaxError
     */
    private function parseContainsComparison(Ast\IdentifierNode $identifier) : Ast\ComparisonNode
    {
        // There are a two main forms of this comparison, ANY and ALL.
        //
        // ANY means that the specification is satisfied when any of of the values is present.
        // `foo contains any [1,2]` -> CONTAINS_ANY
        //
        // ALL means that the specification is satisfied when all of of the values are present.
        // `foo contains all [1,2]` -> CONTAINS_ALL
        //
        // Note that there is no EXACTLY modifier - for this the existing `is` comparison can be used.
        //
        // If no modifier is specified, the comparison is assumed to be ANY:
        // `foo contains [1,2]` -> CONTAINS_ANY
        //
        // The `of` keyword can be added for readability. This is just syntactic sugar and is ignored if present.
        // `foo contains any of [1,2]` -> CONTAINS_ANY
        // `foo contains all of [1,2]` -> CONTAINS_ALL
        //
        // Finally, it is possible to check if a single value is contained. It doesn't matter if ANY or ALL is used
        // here as they are equivalent for a single value.
        // `foo contains 1` -> CONTAINS_ANY
        // Note that for consistency, if the comparison specifies ALL with a single value, it is converted to an ANY.
        // This avoids the need for the compiler to handle both cases. The single value is also converted into a
        // single-item list, again to simplify things for the compiler.

        $modifier = Token::ANY;

        if ($this->is(Token::ANY, Token::ALL)) {
            $modifier = $this->token()->type();
            $this->next();
        }

        if ($this->is(Token::OF)) {
            $this->next();
        }

        $value = $this->parseLiteralSingleOrList();

        if (!$value instanceof Ast\LiteralNodeList) {
            $modifier = Token::ANY;
            $value    = new Ast\LiteralNodeList($value);
        }

        if ($modifier === Token::ANY) {
            return Ast\ComparisonNode::containsAny($identifier, $value);
        }

        return Ast\ComparisonNode::containsAll($identifier, $value);
    }


    /**
     * @throws SyntaxError
     */
    private function parseMatchesComparison(Ast\IdentifierNode $identifier) : Ast\ComparisonNode
    {
        $this->match(Token::STRING);

        $value = $this->token()->value();

        $this->next();

        return Ast\ComparisonNode::matches($identifier, new Ast\StringNode($value));
    }


    /**
     * @throws SyntaxError
     */
    private function parseBetweenComparison(Ast\IdentifierNode $identifier) : Ast\LogicalNode
    {
        // There are two forms of between comparison.
        // The first uses interval notation (https://en.wikipedia.org/wiki/Interval_%28mathematics%29) to explicitly
        // define the boundaries of the interval.
        // The second is a simplified version (`foo between 10 and 20`) which implies a closed interval.
        // Both versions are really just syntactic sugar for two separate comparisons joined with AND.
        // For example, `foo between (10, 20]` is the same as `foo > 10 and foo <= 20`.

        // Simplified version.
        if ($this->is(...Token::LITERALS)) {
            $a = $this->parseLiteral();

            $this->match(Token::AND);
            $this->next();

            $b = $this->parseLiteral();

            return Ast\LogicalNode::and(
                Ast\ComparisonNode::greaterThanOrEqualTo($identifier, $a),
                Ast\ComparisonNode::lessThanOrEqualTo($identifier, $b)
            );
        }

        // Full interval notation.
        return $this->parseIntervalNotation($identifier);
    }


    /**
     * @throws SyntaxError
     */
    private function parseIntervalNotation(Ast\IdentifierNode $identifier) : Ast\LogicalNode
    {
        // (0,1) is open; it does not include its endpoints, so becomes "> 0 && < 1".
        // [0,1] is closed; it includes its endpoints, so becomes ">= 0 && <= 1".
        // [0,1) and (0,1] are half-open; they include one endpoint, so become "> 0 && <= 1" or ">= 0 && < 1".

        $this->match(Token::LEFT_BRACKET, Token::LEFT_PAREN);

        $start = $this->token();
        $this->next();

        $a = $this->parseLiteral();

        $this->match(Token::COMMA);
        $this->next();

        $b = $this->parseLiteral();

        $this->match(Token::RIGHT_BRACKET, Token::RIGHT_PAREN);

        $end = $this->token();
        $this->next();

        if ($start->is(Token::LEFT_BRACKET)) {
            $left = Ast\ComparisonNode::greaterThanOrEqualTo($identifier, $a);
        } else {
            $left = Ast\ComparisonNode::greaterThan($identifier, $a);
        }

        if ($end->is(Token::RIGHT_BRACKET)) {
            $right = Ast\ComparisonNode::lessThanOrEqualTo($identifier, $b);
        } else {
            $right = Ast\ComparisonNode::lessThan($identifier, $b);
        }

        return Ast\LogicalNode::and($left, $right);
    }


    /**
     * @throws SyntaxError
     */
    private function parseIdentifier() : Ast\IdentifierNode
    {
        $this->match(Token::IDENTIFIER);

        $token = $this->token();

        $this->next();

        if (!$this->is(Token::DOT)) {
            return new Ast\IdentifierNode($token->value());
        }

        $this->next();

        return new Ast\IdentifierNode($token->value(), $this->parseIdentifier());
    }


    /**
     * @throws SyntaxError
     */
    private function parseLiteralSingleOrList() : Ast\LiteralNode
    {
        if ($this->is(Token::LEFT_BRACKET)) {
            return $this->parseLiteralList();
        }

        return $this->parseLiteral();
    }


    /**
     * @throws SyntaxError
     */
    private function parseLiteral() : Ast\LiteralNode
    {
        // Literals can be prefixed with a specific type hint, e.g. date:"1st Jan 2019".
        // This can affect the type of LiteralNode which is produced.

        $type = $this->parseLiteralTypeHint();

        if ($type === 'date') {
            return $this->parseDateLiteral();
        }

        if ($type === 'timezone') {
            return $this->parseTimezoneLiteral();
        }

        // Type hints for other types are unsupported as they have distinct literal representations
        // which correspond to specific Tokens.

        $this->matchLiteral();

        $token = $this->token();

        $this->next();

        if ($token->is(Token::STRING)) {
            return new Ast\StringNode($token->value());
        }

        if ($token->is(Token::BOOLEAN)) {
            return new Ast\BooleanNode($token->value() === 'true');
        }

        return new Ast\NumberNode($token->value());
    }


    /**
     * @throws SyntaxError
     */
    private function parseLiteralTypeHint(string ...$allowed) : ?string
    {
        $type = null;

        if ($this->is(Token::IDENTIFIER)) {
            $this->matchLiteralTypeHint(...$allowed);
            $type = $this->token()->value();
            $this->next();

            $this->match(Token::COLON);
            $this->next();
        }

        return $type;
    }


    /**
     * @throws SyntaxError
     */
    private function parseDateLiteral() : Ast\DateNode
    {
        $this->match(TOKEN::STRING);

        $token = $this->token();

        $this->next();

        // Allow an optional timezone to be specified using 'in timezone:"<tz>"'.

        $timezone = null;

        if ($this->is(Token::IN)) {
            $this->next();

            // The type hint is optional, but must be "timezone" if present.
            $this->parseLiteralTypeHint(self::HINT_TIMEZONE);

            $timezone = $this->parseTimezoneLiteral()->value();
        }

        try {
            $date = new DateTimeImmutable($token->value(), $timezone);
        } catch (Exception $e) {
            throw new SyntaxError(
                sprintf("Invalid date literal '%s'.", $token->value()),
                $this->expression,
                $token->position(),
                $e
            );
        }

        return new Ast\DateNode($date);
    }


    /**
     * @throws SyntaxError
     */
    private function parseTimezoneLiteral() : Ast\TimezoneNode
    {
        $this->match(Token::STRING);

        $token = $this->token();

        try {
            $timezone = new DateTimeZone($token->value());
        } catch (Exception $e) {
            throw new SyntaxError(
                sprintf("Invalid timezone literal '%s'.", $token->value()),
                $this->expression,
                $token->position(),
                $e
            );
        }

        $this->next();

        return new Ast\TimezoneNode($timezone);
    }


    /**
     * @throws SyntaxError
     */
    private function parseLiteralList() : Ast\LiteralNodeList
    {
        $this->match(Token::LEFT_BRACKET);

        $this->next();

        $literals = [];
        $type     = null;

        while (true) {
            // In case of mismatched types error, this is the first token with a previously unseen type.
            // Get hold of it before continuing to parse so its position can be used for the error.
            $token = $this->token();

            // Technically it might be better to allow expressions rather than just literals.
            // This would require that a literal on its own is a valid expression though, which doesn't make
            // a lot of sense in the context of a rule.
            $literals[] = $literal = $this->parseLiteral();

            if ($type === null) {
                $type = $literal::type();
            } elseif ($literal::type() !== $type) {
                throw new SyntaxError(
                    sprintf(
                        "Lists must not contain different data types. This list contains '%s' and '%s' data.",
                        $type,
                        $literal::type()
                    ),
                    $this->expression,
                    $token->position()
                );
            }

            if (!$this->is(Token::COMMA)) {
                break;
            }

            $this->next();
        }

        $this->match(Token::RIGHT_BRACKET);

        $this->next();

        return new Ast\LiteralNodeList(...$literals);
    }


    /**
     * @throws SyntaxError
     */
    private function parseSubExpression() : Ast\Node
    {
        $this->match(Token::LEFT_PAREN);

        $this->next();

        $node = $this->parseExpression();

        $this->match(Token::RIGHT_PAREN);

        $this->next();

        return $node;
    }


    private function token() : ?Token
    {
        return $this->pointer > $this->pointerMax ? null : $this->tokens[$this->pointer];
    }


    private function peek() : ?Token
    {
        return $this->tokens[$this->pointer + 1] ?? null;
    }


    private function next() : void
    {
        $this->pointer++;
    }


    private function is(string ...$tokenType) : bool
    {
        $token = $this->token();

        return $token && $token->is(...$tokenType);
    }


    /**
     * @throws SyntaxError
     */
    private function match(string ...$tokenType) : void
    {
        if (!$this->is(...$tokenType)) {
            throw SyntaxError::unexpectedToken($this->expression, $tokenType, $this->token());
        }
    }


    /**
     * @throws SyntaxError
     */
    private function matchComparisonOperator() : void
    {
        $this->match(...Token::COMPARISON_OPERATORS);
    }


    /**
     * @throws SyntaxError
     */
    private function matchLogicalOperator() : void
    {
        $this->match(...Token::LOGICAL_OPERATORS);
    }


    /**
     * @throws SyntaxError
     */
    private function matchLiteral() : void
    {
        $this->match(...Token::LITERALS);
    }


    /**
     * @throws SyntaxError
     */
    private function matchLiteralTypeHint(string ...$hints) : void
    {
        $this->match(Token::IDENTIFIER);

        if (empty($hints)) {
            $hints = self::HINTS;
        }

        $token = $this->token();
        $hint  = $token->value();

        if (!in_array($hint, $hints, true)) {
            throw SyntaxError::unexpectedToken($this->expression, $hints, $token);
        }
    }


    private function isLiteralOrTypeHint(Token $token) : bool
    {
        if ($token->isLiteral()) {
            return true;
        }

        if (!$token->is(Token::IDENTIFIER)) {
            return false;
        }

        return in_array($token->value(), self::HINTS, true);
    }
}
