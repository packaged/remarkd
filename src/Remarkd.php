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

  public function __construct(bool $defaultBlocks = true, bool $defaultRules = true)
  {
    $this->_ruleEngine = new RuleEngine();
    if($defaultRules)
    {
      $this->applyDefaultRules($this->_ruleEngine);
    }

    $this->_blockEngine = new BlockEngine($this->_ruleEngine);
    if($defaultBlocks)
    {
      $this->applyDefaultBlocks($this->_blockEngine);
    }
  }

  /**
   * @return \Packaged\Remarkd\Rules\RuleEngine
   */
  public function ruleEngine(): RuleEngine
  {
    return $this->_ruleEngine;
  }

  /**
   * @return \Packaged\Remarkd\Blocks\BlockEngine
   */
  public function blockEngine(): BlockEngine
  {
    return $this->_blockEngine;
  }

  public function parse($text)
  {
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
    $blocks = $this->_blockEngine->parseLines($lines);
    return $this->_ruleEngine->parse(implode("", $blocks));
  }

  public function render($text, $cssClass = 'remarkd-styled')
  {
    return '<div class="remarkd ' . $cssClass . '">' . $this->parse($text) . '</div>';
  }

  /**
   * For basic implementation of the remarkd, you can use this method to include resources onto your page.
   * We recommend using https://github.com/packaged/dispatch in vendor mode for including these resources
   *
   * @return string
   */
  public function resourcesHtml()
  {
    return
      '<style>' . file_get_contents(dirname(__DIR__) . '/resources/css/remarkd.css') . '</style>'
      . '<script>' . file_get_contents(dirname(__DIR__) . '/resources/js/tabs.js') . '</script>';
  }

  public function applyDefaultRules(RuleEngine $engine)
  {
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

  public function applyDefaultBlocks(BlockEngine $engine): BlockEngine
  {
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
