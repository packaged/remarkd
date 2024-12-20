<?php
namespace Packaged\Remarkd;

use Packaged\Remarkd\Blocks\AccordionBlock;
use Packaged\Remarkd\Blocks\Admonition;
use Packaged\Remarkd\Blocks\BlockEngine;
use Packaged\Remarkd\Blocks\BlockMatcher;
use Packaged\Remarkd\Blocks\CalloutBlock;
use Packaged\Remarkd\Blocks\CodeBlock;
use Packaged\Remarkd\Blocks\CommentBlock;
use Packaged\Remarkd\Blocks\DefinitionListBlock;
use Packaged\Remarkd\Blocks\ExampleBlock;
use Packaged\Remarkd\Blocks\IDBlock;
use Packaged\Remarkd\Blocks\ListingBlock;
use Packaged\Remarkd\Blocks\ListItemBlock;
use Packaged\Remarkd\Blocks\LiteralBlock;
use Packaged\Remarkd\Blocks\MarkdownHeaderBlock;
use Packaged\Remarkd\Blocks\ModuleBlock;
use Packaged\Remarkd\Blocks\OrderedListBlock;
use Packaged\Remarkd\Blocks\SidebarBlock;
use Packaged\Remarkd\Blocks\StepBlock;
use Packaged\Remarkd\Blocks\TabBlock;
use Packaged\Remarkd\Blocks\UnorderedListBlock;
use Packaged\Remarkd\Modules\RemarkdModule;
use Packaged\Remarkd\Objects\AnchorObject;
use Packaged\Remarkd\Objects\ImageObject;
use Packaged\Remarkd\Objects\LineBreakObject;
use Packaged\Remarkd\Objects\LinkObject;
use Packaged\Remarkd\Objects\ObjectEngine;
use Packaged\Remarkd\Objects\ProgressMeterObject;
use Packaged\Remarkd\Objects\References\ReferenceListObject;
use Packaged\Remarkd\Objects\References\ReferenceObject;
use Packaged\Remarkd\Objects\RemarkdObject;
use Packaged\Remarkd\Objects\VideoObject;
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
use Packaged\Remarkd\Rules\RemarkdRule;
use Packaged\Remarkd\Rules\RuleEngine;
use Packaged\Remarkd\Rules\SectionLinkText;
use Packaged\Remarkd\Rules\SubScriptText;
use Packaged\Remarkd\Rules\SuperScriptText;
use Packaged\Remarkd\Rules\TipText;
use Packaged\Remarkd\Rules\TypographicSymbolRule;
use Packaged\Remarkd\Rules\UnderlinedText;
use Packaged\Remarkd\Traits\AbstractTraits;
use Packaged\Remarkd\Traits\PartialTrait;

class Remarkd
{
  protected RemarkdContext $_context;

  public function __construct(RemarkdContext $context = null)
  {
    if($context === null)
    {
      $context = new RemarkdContext();
      $this->applyDefaultBlocks($context->blockEngine());
      $context->blockEngine()->addMatcher($context->modules());
      $this->applyDefaultModules($context->modules());
      $this->applyDefaultRules($context->ruleEngine());
      $this->applyDefaultObjects($context->objectEngine());
//      $this->applyDefaultTraits($context);
    }
    $this->_context = $context;
  }

  public function ctx()
  {
    return $this->_context;
  }

  public function applyDefaultTraits(RemarkdContext $context)
  {
    $engine = $context->traitEngine();
    $engine->registerTrait(new PartialTrait($context));
  }

  public function applyDefaultModules(ModuleBlock $block)
  {
  }

  public function applyDefaultBlocks(BlockEngine $engine)
  {
    $engine->addMatcher(new CommentBlock());
    $engine->addMatcher(new Admonition());
    $engine->addMatcher(new CalloutBlock());
    $engine->addMatcher(new ListingBlock());
    $engine->addMatcher(new ExampleBlock());
    $engine->addMatcher(new CodeBlock());
    $engine->addMatcher(new SidebarBlock());
    $engine->addMatcher(new TabBlock());
    $engine->addMatcher(new AccordionBlock());
    $engine->addMatcher(new IDBlock());
    $engine->addMatcher(new StepBlock());
    $engine->addMatcher(new LiteralBlock());
    $engine->addMatcher(new MarkdownHeaderBlock());
    $engine->addMatcher(new OrderedListBlock());
    $engine->addMatcher(new DefinitionListBlock());
    $engine->addMatcher(new UnorderedListBlock());
    $engine->addMatcher(new ListItemBlock());
  }

  public function applyDefaultObjects(ObjectEngine $engine)
  {
    $engine->registerObject(new ProgressMeterObject());
    $engine->registerObject(new LinkObject());
    $engine->registerObject(new LineBreakObject());
    $engine->registerObject(new ReferenceObject());
    $engine->registerObject(new ReferenceListObject());
    $engine->registerObject(new AnchorObject());
    $engine->registerObject(new ImageObject());
    $engine->registerObject(new VideoObject());
    return $engine;
  }

  public function applyDefaultRules(RuleEngine $engine)
  {
    $engine->registerRule(new QuoteText());
    $engine->registerRule(new MonospacedText());
    $engine->registerRule(new UnderlinedText());//must be before bold

    $engine->registerRule(new TypographicSymbolRule());
    $engine->registerRule(new EmojiRule());
    $engine->registerRule(new KeyboardKey());
    $engine->registerRule(new HighlightText());

    $engine->registerRule(new TipText());
    $engine->registerRule(new CalloutText());
    $engine->registerRule(new Image());
    $engine->registerRule(new LinkText());
    $engine->registerRule(new SectionLinkText());
    $engine->registerRule(new BoldText());
    $engine->registerRule(new ItalicText());
    $engine->registerRule(new DeletedText());
    $engine->registerRule(new SubScriptText());
    $engine->registerRule(new SuperScriptText());

    $engine->registerRule(new CheckboxRule());

    $engine->registerRule(new InlineStyleText());

    return $engine;
  }

  public function registerBlock(BlockMatcher $block)
  {
    return $this->ctx()->blockEngine()->addMatcher($block);
  }

  public function registerModule(RemarkdModule $module)
  {
    return $this->ctx()->modules()->registerModule($module);
  }

  public function registerObject(RemarkdObject $object)
  {
    return $this->ctx()->objectEngine()->registerObject($object);
  }

  public function registerRule(RemarkdRule $rule)
  {
    return $this->ctx()->ruleEngine()->registerRule($rule);
  }

  public function registerTrait(AbstractTraits $trait)
  {
    return $this->ctx()->traitEngine()->registerTrait($trait);
  }

}
