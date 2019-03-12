<?php

namespace Krixon\Rules\Parser;

use Krixon\Rules\Ast;
use Krixon\Rules\Error\BufferErrorReporter;
use Krixon\Rules\Error\ErrorCollection;
use Krixon\Rules\Error\ErrorReporter;
use Krixon\Rules\Error\ThrowErrorReporter;
use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Lexer\Lexer;
use Krixon\Rules\Lexer\Token;

class DefaultParser implements Parser
{
    private const HINT_DATE     = 'date';
    private const HINT_TIMEZONE = 'timezone';

    private const HINTS = [
        self::HINT_DATE,
        self::HINT_TIMEZONE,
    ];

    /**
     * @var ErrorCollection
     */
    private $errors;

    /**
     * @var Token[]
     */
    private $tokens;
    private $pointer;
    private $lexer;
    private $errorReporter;
    private $expression;



    public function __construct(Lexer $lexer = null, ErrorReporter $errorReporter = null)
    {
        $this->lexer         = $lexer ?: new Lexer();
        $this->errorReporter = $errorReporter ?: new BufferErrorReporter();
    }


    public function parse(string $expression) : Ast\Node
    {
        $this->expression = $expression;
        $this->tokens     = $this->lexer->tokenize($this->expression);
        $this->pointer    = 0;
        $this->errors     = new ErrorCollection();

        try {
            $node = $this->parseExpression();

            $this->match(Token::EOF);

            return $node;
        } catch (SyntaxError $error) {
            $this->errors->append($error);
        } finally {
            if ($this->errors->count()) {
                $this->errorReporter->report($this->errors);
                // TODO: Throw a compound SyntaxError exception.
            }
        }
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
            try {
                $node = $this->parseComparisonExpression();
            } catch (SyntaxError $error) {
                $this->errors->append($error);

                if (!$this->synchronize()) {
                    // Unable to recover from the error. Panic!
                    throw $error;
                }

                $node = $this->parsePrimaryNode();
            }
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

            if ($next && $next->isLiteral()) {
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
    private function parseBetweenComparison(Ast\IdentifierNode $identifier) : Ast\Node
    {
        // `foo between 10 and 20` is syntactic sugar for `foo >= 10 and foo <= 20`.

        $a = $this->parseLiteral();

        $this->match(Token::AND);
        $this->next();

        $b = $this->parseLiteral();

        return Ast\LogicalNode::and(
            Ast\ComparisonNode::greaterThanOrEqualTo($identifier, $a),
            Ast\ComparisonNode::lessThanOrEqualTo($identifier, $b)
        );
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
            $date = new \DateTimeImmutable($token->value(), $timezone);
        } catch (\Exception $e) {
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
            $timezone = new \DateTimeZone($token->value());
        } catch (\Exception $e) {
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

        while (true) {
            // Technically it might be better to allow expressions rather than just literals.
            // This would require that a literal on its own is a valid expression though, which doesn't make
            // a lot of sense in the context of a rule.
            $literals[] = $this->parseLiteral();

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
        return $this->tokens[$this->pointer] ?? null;
    }


    private function peek($distance = 1) : ?Token
    {
        return $this->tokens[$this->pointer + $distance] ?? null;
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


    private function synchronize() : bool
    {
        $this->next();

        // Look for an identifier followed by a comparison operator.
        // An identifier can contain sub-identifiers separated by dots.

        while (!$this->eof()) {
            $offset = 0;

            // The next comparison expression might be in a group. Start looking for the comparison after the
            // left paren if that's the next token.
            if ($this->is(Token::LEFT_PAREN)) {
                $offset++;
            }

            $identifier = $this->peek($offset);
            $operator   = $this->peek($offset + 1);

            if ($identifier
                && $operator
                && $identifier->is(Token::IDENTIFIER)
                && $operator->is(...Token::COMPARISON_OPERATORS)) {

                return true;
            }

            $this->next();
        }

        return false;
    }


    private function eof() : bool
    {
        $token = $this->token();

        return !$token || $token->is(Token::EOF);
    }
}
