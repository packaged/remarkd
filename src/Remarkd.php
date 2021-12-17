<?php
namespace Packaged\Remarkd;

use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Blocks\BlockQuote;
use Packaged\Remarkd\Blocks\CodeBlock;
use Packaged\Remarkd\Blocks\HeadingBlock;
use Packaged\Remarkd\Blocks\HintBlock;
use Packaged\Remarkd\Blocks\OrderedListBlock;
use Packaged\Remarkd\Blocks\ParagraphBlock;
use Packaged\Remarkd\Blocks\TableBlock;
use Packaged\Remarkd\Blocks\TabsBlock;
use Packaged\Remarkd\Blocks\UnorderedListBlock;
use Packaged\Remarkd\Blocks\VideoBlock;
use Packaged\Remarkd\Blocks\WellBlock;
use Packaged\Remarkd\Markup\MarkupResource;
use Packaged\Remarkd\Objects\LineBreakObject;
use Packaged\Remarkd\Objects\ObjectEngine;
use Packaged\Remarkd\Objects\ProgressMeterObject;
use Packaged\Remarkd\Objects\References\ReferenceListObject;
use Packaged\Remarkd\Objects\References\ReferenceObject;
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
  protected RemarkdContext $_context;

  public function __construct(RemarkdContext $context = null)
  {
    if($context === null)
    {
      $context = new RemarkdContext();
      $this->applyDefaultRules($context->ruleEngine());
      $this->applyDefaultBlocks($context->blockEngine());
      $this->applyDefaultObjects($context->objectEngine());
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
    return $this->ctx()->ruleEngine()->parse(
      $this->ctx()->objectEngine()->parse(implode("", $blocks))
    );
  }

  public function render($text, $cssClass = 'remarkd-styled')
  {
    $return = '';
    $css = $this->ctx()->resources(MarkupResource::TYPE_CSS);
    if(!empty($css))
    {
      $return .= '<style>' . implode("\n", $css) . '</style>';
    }
    $js = $this->ctx()->resources(MarkupResource::TYPE_JS);
    if(!empty($js))
    {
      $return .= '<script>' . implode("\n", $js) . '</script>';
    }
    $return .= implode("\n", $this->ctx()->resources(MarkupResource::TYPE_HTML));

    $return .= '<div class="remarkd ' . $cssClass . '">' . $this->parse($text) . '</div>';
    return $return;
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

  public function applyDefaultObjects(ObjectEngine $engine)
  {
    $engine->registerObject(new ProgressMeterObject());
    $engine->registerObject(new LineBreakObject());
    $engine->registerObject(new ReferenceObject());
    $engine->registerObject(new ReferenceListObject());
    return $engine;
  }

  public function applyDefaultBlocks(BlockEngine $engine): BlockEngine
  {
    $engine->registerBlock(new CodeBlock());
    $engine->registerBlock(new TableBlock());
    $engine->registerBlock(new UnorderedListBlock());
    $engine->registerBlock(new OrderedListBlock());
    $engine->registerBlock(new BlockQuote());
    $engine->registerBlock(new HeadingBlock());
    $engine->registerBlock(new WellBlock());
    $engine->registerBlock(new HintBlock());
    $engine->registerBlock(new VideoBlock());
    $engine->registerBlock(new TabsBlock());
    $engine->setDefaultBlock(new ParagraphBlock());

    return $engine;
  }
}
