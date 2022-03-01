<?php
/**
 * @author Manuel Aguirre
 */

namespace Optime\Util\Twig\TokenParser;


use Optime\Util\Twig\Node\AjaxViewNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use function dump;

/**
 * @author Manuel Aguirre
 */
class AjaxViewTokenParser extends AbstractTokenParser
{

    public function parse(Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
//        $name = $stream->expect(Token::NAME_TYPE)->getValue();
        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return new AjaxViewNode($body, $lineno, $this->getTag());
    }

    public function decideEnd(Token $token): bool
    {
        if ($token->test('end_ajax_view')) {
            return true;
        }

        return false;
    }

    public function getTag()
    {
        return 'ajax_view';
    }
}