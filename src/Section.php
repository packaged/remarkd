<?php
namespace Packaged\Remarkd;

use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\Text\HeadingFive;
use Packaged\Glimpse\Tags\Text\HeadingFour;
use Packaged\Glimpse\Tags\Text\HeadingSix;
use Packaged\Glimpse\Tags\Text\HeadingThree;
use Packaged\Glimpse\Tags\Text\HeadingTwo;
use Packaged\Remarkd\Blocks\BasicBlock;
use Packaged\Remarkd\Blocks\Block;
use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Html\HtmlElement;

class Section extends Element implements ISafeHtmlProducer
{
  public $id;
  public $title;
  public $level = 0;

  /** @var null|Section */
  public $parent;

  /**
   * @var Section|BasicBlock
   */
  public $children = [];

  protected $_remarkd;

  /** @var \Packaged\Remarkd\Blocks\BlockEngine */
  protected $_bockEngine;
  /**
   * @var \Packaged\Remarkd\Attributes
   */
  protected Attributes $_attributes;

  public function __construct(Remarkd $remarkd, $title = null, $level = 0)
  {
    $this->_remarkd = $remarkd;
    $this->_blockEngine = $remarkd->ctx()->blockEngine();
    $this->title = $title;
    $this->level = $level;
  }

  public function setAttributes(Attributes $attributes)
  {
    $this->_attributes = $attributes;
    return $this;
  }

  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  public function hasChildren(): bool
  {
    return !empty($this->children) || !empty($this->_blockEngine->blocks());
  }

  protected function _flushBlocks()
  {
    foreach($this->_blockEngine->blocks() as $block)
    {
      $this->children[] = $block;
    }
    $this->_blockEngine->clearBlocks();
  }

  public function addChild(Section $child)
  {
    $this->_flushBlocks();
    $this->children[] = $child;
    $child->parent = $this;
    return $this;
  }

  public function addLine($line, $title = null, $attribute = null)
  {
    $this->_blockEngine->addLine($line, $title, $attribute);
    return $this;
  }

  public function close()
  {
    $this->_flushBlocks();
    foreach($this->children as $block)
    {
      if($block instanceof Block)
      {
        $block->close();
      }
    }

    return $this;
  }

  public function produceSafeHTML(): SafeHtml
  {
    $head = null;
    $content = $this->children;
    switch($this->level)
    {
      case 1:
        $head = HeadingTwo::create($this->title);
        break;
      case 2:
        $head = HeadingThree::create($this->title);
        break;
      case 3:
        $head = HeadingFour::create($this->title);
        break;
      case 4:
        $head = HeadingFive::create($this->title);
        break;
      case 5:
        $head = HeadingSix::create($this->title);
        break;
    }

    $ele = Div::create($head, $content);
    $ele->addClass('section--level' . $this->level, 'section--' . (empty($this->children) ? 'empty' : 'with-content'));

    if($this->id && $head instanceof HtmlElement)
    {
      $ele->setId($this->id);
    }

    return $ele->produceSafeHTML();
  }

}

