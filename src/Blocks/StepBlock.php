<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\Media\Image;
use Packaged\Glimpse\Tags\Text\HeadingThree;
use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;
use Packaged\Ui\Html\HtmlElement;

class StepBlock extends BasicBlock implements BlockMatcher
{
  protected $_title;
  protected $_closeOnEmpty = false;

  const MATCH = '_|-';

  public function contentType(): string
  {
    return Block::TYPE_COMPOUND;
  }

  public function match($line, ?Block $parent): ?Block
  {
    if(preg_match('/^_\|- (.*)(\[.*\])?\s*$/U', $line, $matches))
    {
      $block = new static();
      $block->_title = $matches[1];
      $block->_attr = new Attributes($matches[2] ?? '');

      if(!($parent instanceof StepsContainer || $parent instanceof static))
      {
        $parent = new StepsContainer();
        $parent->addChild($block);
        return $parent;
      }

      return $block;
    }
    return null;
  }

  public function allowLine(string $line): ?bool
  {
    $first3 = substr($line, 0, 3);
    return empty($this->_children) || $first3 !== self::MATCH;
  }

  protected function _produceElement(): HtmlElement
  {
    $img = $this->_attr->get('img');
    if($img)
    {
      $img = Div::create(Image::create($img))->addClass('step-image');
    }
    return Div::create(
      Div::create(HeadingThree::create($this->_title), $this->_children)->addClass('step-content'),
      $img
    )->addClass('step');
  }

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    if(substr($line, 0, 3) === self::MATCH)
    {
      $this->_children[] = '';
      return true;
    }
    return parent::appendLine($ctx, $line);
  }

}
