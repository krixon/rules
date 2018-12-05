<?php

namespace Krixon\Rules;

use Krixon\Rules\Exception\SyntaxError;

class ExpressionParser implements Parser
{
    /**
     * @var Token[]
     */
    private $tokens;
    private $pointer;
    private $lexer;
    private $expression;


    public function __construct(Lexer $lexer = null)
    {
        $this->lexer = $lexer ?: new Lexer();
    }


    public function parse(string $expression) : Ast\Node
    {
        $this->expression = $expression;
        $this->pointer    = 0;
        $this->tokens     = $this->lexer->tokenize($this->expression);

        if (empty($this->tokens)) {
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

        while ($this->token()->isOperator() && $operatorPrecedence > $precedence) {
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
        $left = $this->parseIdentifier();

        $this->matchComparisonOperator();

        $token = $this->token();

        $this->next();

        if ($token->is(Token::EQUAL)) {
            return Ast\ComparisonNode::equal($left, $this->parseLiteral());
        }

        if ($token->is(Token::NOT_EQUAL)) {
            return Ast\ComparisonNode::notEqual($left, $this->parseLiteral());
        }

        return Ast\ComparisonNode::in($left, $this->parseLiteralList());
    }


    /**
     * @throws SyntaxError
     */
    private function parseIdentifier() : Ast\IdentifierNode
    {
        $this->match(Token::IDENTIFIER);

        $token = $this->token();

        $this->next();

        if (!$this->token()->is(Token::DOT)) {
            return new Ast\IdentifierNode($token->value());
        }

        $this->next();

        return new Ast\IdentifierNode($token->value(), $this->parseIdentifier());
    }


    /**
     * @throws SyntaxError
     */
    private function parseLiteral() : Ast\Node
    {
        $this->matchLiteral();

        $token = $this->token();

        $this->pointer++;

        if ($token->is(Token::STRING)) {
            return new Ast\StringNode($token->value());
        }

        return new Ast\NumberNode($token->value());
    }


    /**
     * @throws SyntaxError
     */
    private function parseLiteralList() : Ast\NodeList
    {
        $this->match(Token::LEFT_BRACKET);

        $this->next();

        $literals = [];

        while (true) {
            // Technically it might be better to allow expressions rather than just literals.
            // This would require that a literal on its own is a valid expression though, which doesn't make
            // a lot of sense in the context of a rule.
            $literals[] = $this->parseLiteral();

            if (!$this->token()->is(Token::COMMA)) {
                break;
            }

            $this->next();
        }

        $this->match(Token::RIGHT_BRACKET);

        $this->next();

        return new Ast\NodeList(...$literals);
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


    private function token() : Token
    {
        return $this->tokens[$this->pointer];
    }


    private function next() : void
    {
        $this->pointer++;
    }


    /**
     * @throws SyntaxError
     */
    private function match(string $tokenType) : void
    {
        if (!$this->token()->is($tokenType)) {
            throw SyntaxError::unexpectedToken($this->expression, $tokenType, $this->token());
        }
    }


    /**
     * @throws SyntaxError
     */
    private function matchComparisonOperator() : void
    {
        if (!$this->token()->isComparisonOperator()) {
            throw SyntaxError::unexpectedToken($this->expression, 'COMPARISON_OPERATOR', $this->token());
        }
    }


    /**
     * @throws SyntaxError
     */
    private function matchLogicalOperator() : void
    {
        if (!$this->token()->isLogicalOperator()) {
            throw SyntaxError::unexpectedToken($this->expression, 'LOGICAL_OPERATOR', $this->token());
        }
    }


    /**
     * @throws SyntaxError
     */
    private function matchLiteral() : void
    {
        if (!$this->token()->isLiteral()) {
            throw SyntaxError::unexpectedToken($this->expression, 'LITERAL', $this->token());
        }
    }
}
