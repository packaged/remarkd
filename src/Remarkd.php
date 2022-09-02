<?php
namespace Packaged\Remarkd;

use Packaged\Remarkd\Blocks\Admonition;
use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Blocks\CalloutBlock;
use Packaged\Remarkd\Blocks\CodeBlock;
use Packaged\Remarkd\Blocks\ExampleBlock;
use Packaged\Remarkd\Blocks\ListingBlock;
use Packaged\Remarkd\Blocks\ListItemBlock;
use Packaged\Remarkd\Blocks\LiteralBlock;
use Packaged\Remarkd\Blocks\SidebarBlock;
use Packaged\Remarkd\Rules\BoldText;
use Packaged\Remarkd\Rules\CalloutText;
use Packaged\Remarkd\Rules\CheckboxRule;
use Packaged\Remarkd\Rules\DeletedText;
use Packaged\Remarkd\Rules\EmojiRule;
use Packaged\Remarkd\Rules\HighlightText;
use Packaged\Remarkd\Rules\Image;
use Packaged\Remarkd\Rules\InlineStyleText;
use Packaged\Remarkd\Rules\ItalicText;
use Packaged\Remarkd\Rules\KeyboardKey;
use Packaged\Remarkd\Rules\LinkText;
use Packaged\Remarkd\Rules\MonospacedText;
use Packaged\Remarkd\Rules\QuoteText;
use Packaged\Remarkd\Rules\RuleEngine;
use Packaged\Remarkd\Rules\SectionLinkText;
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
    return $this->ctx()->ruleEngine()->parse(implode("", $lines));
  }

  public function render($text, $cssClass = 'remarkd-styled')
  {
    return '<div class="remarkd ' . $cssClass . '">' . $this->parse($text) . '</div>';
  }

  public function applyDefaultBlocks(BlockEngine $engine)
  {
    $engine->addMatcher(new Admonition());
    $engine->addMatcher(new CalloutBlock());
    $engine->addMatcher(new ListItemBlock());
    $engine->addMatcher(new ListingBlock());
    $engine->addMatcher(new ExampleBlock());
    $engine->addMatcher(new CodeBlock());
    $engine->addMatcher(new SidebarBlock());
    $engine->addMatcher(new LiteralBlock());
  }

  public function applyDefaultRules(RuleEngine $engine)
  {
    $engine->registerRule(new QuoteText());
    $engine->registerRule(new MonospacedText());
    $engine->registerRule(new UnderlinedText());//must be before bold

    $engine->registerRule(new TypographicSymbolRule());
    $engine->registerRule(new EmojiRule());
    $engine->registerRule(new KeyboardKey());

    $engine->registerRule(new TipText());
    $engine->registerRule(new CalloutText());
    $engine->registerRule(new Image());
    $engine->registerRule(new LinkText());
    $engine->registerRule(new SectionLinkText());
    $engine->registerRule(new BoldText());
    $engine->registerRule(new ItalicText());
    $engine->registerRule(new DeletedText());
    $engine->registerRule(new HighlightText());
    $engine->registerRule(new SubScriptText());
    $engine->registerRule(new SuperScriptText());

    $engine->registerRule(new CheckboxRule());

    $engine->registerRule(new InlineStyleText());

    return $engine;
  }

}
