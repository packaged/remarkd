<?php
namespace Packaged\Remarkd;

use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Blocks\BlockQuote;
use Packaged\Remarkd\Blocks\HeadingBlock;
use Packaged\Remarkd\Blocks\HintBlock;
use Packaged\Remarkd\Blocks\OrderedListBlock;
use Packaged\Remarkd\Blocks\ParagraphBlock;
use Packaged\Remarkd\Blocks\TableBlock;
use Packaged\Remarkd\Blocks\TabsBlock;
use Packaged\Remarkd\Blocks\UnorderedListBlock;
use Packaged\Remarkd\Blocks\VideoBlock;
use Packaged\Remarkd\Blocks\WellBlock;
use Packaged\Remarkd\Rules\BoldText;
use Packaged\Remarkd\Rules\CheckboxRule;
use Packaged\Remarkd\Rules\DeletedText;
use Packaged\Remarkd\Rules\EmojiRule;
use Packaged\Remarkd\Rules\HighlightText;
use Packaged\Remarkd\Rules\ItalicText;
use Packaged\Remarkd\Rules\KeyboardKey;
use Packaged\Remarkd\Rules\LinkText;
use Packaged\Remarkd\Rules\MonospacedText;
use Packaged\Remarkd\Rules\RuleEngine;
use Packaged\Remarkd\Rules\TipText;
use Packaged\Remarkd\Rules\TypographicSymbolRule;
use Packaged\Remarkd\Rules\UnderlinedText;

class Remarkd
{
  /** @var \Packaged\Remarkd\Rules\RuleEngine */
  protected $_ruleEngine;
  /** @var \Packaged\Remarkd\Blocks\BlockEngine */
  protected $_blockEngine;

  public function __construct()
  {
    $this->_ruleEngine = $this->createRuleEngine();
    $this->_blockEngine = $this->createBlockEngine($this->_ruleEngine);
  }

  public function parse($text)
  {
    $lines = explode("\n", $text);
    $blocks = $this->_blockEngine->parseLines($lines);
    return $this->_ruleEngine->parse(implode("", $blocks));
  }

  public function createRuleEngine(): RuleEngine
  {
    $engine = new RuleEngine();
    $engine->registerRule(new MonospacedText());
    $engine->registerRule(new UnderlinedText());//must be before bold

    $engine->registerRule(new TypographicSymbolRule());
    $engine->registerRule(new EmojiRule());
    $engine->registerRule(new KeyboardKey());

    $engine->registerRule(new TipText());
    $engine->registerRule(new LinkText());
    $engine->registerRule(new BoldText());
    $engine->registerRule(new ItalicText());
    $engine->registerRule(new DeletedText());
    $engine->registerRule(new HighlightText());

    $engine->registerRule(new CheckboxRule());

    return $engine;
  }

  public function createBlockEngine(RuleEngine $ruleEngine): BlockEngine
  {
    $engine = new BlockEngine($ruleEngine);

    $engine->registerBlock(new TableBlock());
    $engine->registerBlock(new UnorderedListBlock());
    $engine->registerBlock(new OrderedListBlock());
    $engine->registerBlock(new BlockQuote());
    $engine->registerBlock(new HeadingBlock());
    $engine->registerBlock(new WellBlock());
    $engine->registerBlock(new HintBlock());
    $engine->registerBlock(new VideoBlock());
    $engine->registerBlock(new TabsBlock());
    $engine->registerBlock(new ParagraphBlock());

    return $engine;
  }
}
