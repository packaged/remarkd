<?php
namespace Packaged\Remarkd;

use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Rules\BoldText;
use Packaged\Remarkd\Rules\CheckboxRule;
use Packaged\Remarkd\Rules\DeletedText;
use Packaged\Remarkd\Rules\EmojiRule;
use Packaged\Remarkd\Rules\HighlightText;
use Packaged\Remarkd\Rules\Image;
use Packaged\Remarkd\Rules\ItalicText;
use Packaged\Remarkd\Rules\KeyboardKey;
use Packaged\Remarkd\Rules\LinkText;
use Packaged\Remarkd\Rules\MonospacedText;
use Packaged\Remarkd\Rules\RuleEngine;
use Packaged\Remarkd\Rules\SubScriptText;
use Packaged\Remarkd\Rules\SuperScriptText;
use Packaged\Remarkd\Rules\TipText;
use Packaged\Remarkd\Rules\TypographicSymbolRule;
use Packaged\Remarkd\Rules\UnderlinedText;

class Remarkd
{
  protected RemarkdContext $_context;

  public function __construct(RemarkdContext $context = null)
  {
    if($context === null)
    {
      $context = new RemarkdContext();
      $this->applyDefaultBlocks($context->blockEngine());
      $this->applyDefaultRules($context->ruleEngine());
    }
    $this->_context = $context;
  }

  public function ctx()
  {
    return $this->_context;
  }

  public function parse($text)
  {
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
    $blocks = $this->ctx()->blockEngine()->parseLines($lines);
    return $this->ctx()->objectEngine()->parse(
      $this->ctx()->ruleEngine()->parse(implode("", $blocks))
    );
  }

  public function render($text, $cssClass = 'remarkd-styled')
  {
    return '<div class="remarkd ' . $cssClass . '">' . $this->parse($text) . '</div>';
  }

  public function applyDefaultBlocks(BlockEngine $engine)
  {

  }

  public function applyDefaultRules(RuleEngine $engine)
  {
    $engine->registerRule(new MonospacedText());
    $engine->registerRule(new UnderlinedText());//must be before bold

    $engine->registerRule(new TypographicSymbolRule());
    $engine->registerRule(new EmojiRule());
    $engine->registerRule(new KeyboardKey());

    $engine->registerRule(new TipText());
    $engine->registerRule(new Image());
    $engine->registerRule(new LinkText());
    $engine->registerRule(new BoldText());
    $engine->registerRule(new ItalicText());
    $engine->registerRule(new DeletedText());
    $engine->registerRule(new HighlightText());
    $engine->registerRule(new SubScriptText());
    $engine->registerRule(new SuperScriptText());

    $engine->registerRule(new CheckboxRule());

    return $engine;
  }

}
