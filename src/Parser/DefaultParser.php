<?php

namespace Krixon\Rules\Parser;

use Krixon\Rules\Ast;
use Krixon\Rules\Exception\SyntaxError;
use Krixon\Rules\Lexer\Lexer;
use Krixon\Rules\Lexer\Token;

class DefaultParser implements Parser
{
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

        if ($token->is(Token::AND)) {
            return Ast\LogicalNode::and($left, $this->parseExpression(1));
        }

        return Ast\LogicalNode::or($left, $this->parseExpression(1));
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
    private function parseMatchesComparison(Ast\IdentifierNode $identifier) : Ast\Node
    {
        $this->match(Token::STRING);

        $value = $this->token()->value();

        $this->next();

        return Ast\ComparisonNode::matches($identifier, new Ast\StringNode($value));
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
        $this->matchLiteral();

        $token = $this->token();

        $this->next();

        if ($token->is(Token::STRING)) {
            return new Ast\StringNode($token->value());
        }

        if ($token->is(Token::BOOLEAN)) {
            return $token->value() === 'true' ? Ast\BooleanNode::true() : Ast\BooleanNode::false();
        }

        return new Ast\NumberNode($token->value());
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
}
