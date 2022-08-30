<?php
namespace Packaged\Remarkd;

use Packaged\Glimpse\Core\AbstractContainerTag;
use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Tags\Lists\ListItem;
use Packaged\Glimpse\Tags\Lists\OrderedList;
use Packaged\Glimpse\Tags\Lists\UnorderedList;
use Packaged\Glimpse\Tags\Span;
use Packaged\Glimpse\Tags\Text\CodeBlock;
use Packaged\Glimpse\Tags\Text\HeadingFive;
use Packaged\Glimpse\Tags\Text\HeadingFour;
use Packaged\Glimpse\Tags\Text\HeadingOne;
use Packaged\Glimpse\Tags\Text\HeadingSix;
use Packaged\Glimpse\Tags\Text\HeadingThree;
use Packaged\Glimpse\Tags\Text\HeadingTwo;
use Packaged\Glimpse\Tags\Text\Paragraph;
use Packaged\Helpers\Strings;
use Packaged\Remarkd\Rules\RuleEngine;
use Packaged\SafeHtml\ISafeHtmlProducer;
use Packaged\SafeHtml\SafeHtml;

class Section extends Element implements ISafeHtmlProducer
{
  public $title;
  public $level = 0;

  /** @var null|Section */
  public $parent;

  /**
   * @var Section|Block
   */
  public $children = [];

  /**
   * @var Block[]
   */
  protected $_openBlocks = [];

  /**
   * @var Block
   */
  protected $_currentBlock;

  public function hasChildren(): bool
  {
    return !empty($this->children);
  }

  public function addChild(Section $child)
  {
    $this->children[] = $child;
    $child->parent = $this;
    return $this;
  }

  public function addLine(RuleEngine $ruleEngine, $matchers, $line, $title = null, $attribute = null)
  {
    if(empty($line) && $this->_currentBlock && $this->_currentBlock->closesOnEmptyLine())
    {
      $this->_currentBlock->close();
      $this->_currentBlock = null;
      return $this;
    }

    if((empty($line) || !is_scalar($line)) && $this->_currentBlock)
    {
      if(!$this->_currentBlock->allowChildren())
      {
        $this->_currentBlock->close();
        $this->_currentBlock = null;
      }
    }

    if(!$this->_currentBlock && $line instanceof ISafeHtmlProducer)
    {
      $this->children[] = $line;
      return $this;
    }

    if(empty($line) && (!$this->_currentBlock || $this->_currentBlock->isClosed()))
    {
      return $this;
    }

    if(is_scalar($line))
    {
      /** @var \Packaged\Remarkd\BlockMatcher $match */
      foreach($matchers as $match)
      {
        if($match->match($line))
        {
          if(isset($this->_openBlocks[$line]))
          {
            //Close the block
            $closedBlocks = $this->_openBlocks[$line]->close();
            foreach($closedBlocks as $blockID)
            {
              unset($this->_openBlocks[$blockID]);
            }
            if($this->_currentBlock && $this->_currentBlock->isClosed())
            {
              $this->_currentBlock = null;
            }
          }
          else
          {
            $block = $match->createBlock($line);

            $this->_openBlocks[$line] = $block;

            if(!$this->_currentBlock || $this->_currentBlock->isClosed() || !$this->_currentBlock->allowChildren())
            {
              $this->_addBlock($block);
            }
            else if($block !== $this->_currentBlock)
            {
              $this->_currentBlock->addChild($block);
            }

            $line = $match->pendingLine();
            if($line)
            {
              break;
            }
          }
          return $this;
        }
      }
    }

    $listType = null;
    switch(substr($line, 0, 2))
    {
      case '- ':
      case '* ':
        $listType = UnorderedList::class;
        break;
      case '. ':
        $listType = OrderedList::class;
        break;
    }
    if($listType !== null)
    {
      $line = substr($line, 2);
      if(!$this->_currentBlock || $this->_currentBlock->tag() !== $listType)
      {
        $listBlock = new Block();
        $listBlock->setTag($listType);
        $listBlock->setTitle($title);
        $listBlock->setAttr($attribute);
        $this->_addBlock($listBlock);
      }
      $liBlock = new Block();
      $liBlock->setTag(ListItem::class);
      $liBlock->setAllowChildren(false);
      $liBlock->addLine(new SafeHtml($ruleEngine->parse($line)));
      $this->_currentBlock->addChild($liBlock);
      return $this;
    }

    if(!$this->_currentBlock)
    {
      $block = $this->_newBlockForLine($line);
      $block->setTitle($title);
      $block->setAttr($attribute);
      $this->_addBlock($block);
    }

    $this->_currentBlock->addLine(new SafeHtml($ruleEngine->parse($line)));

    if($this->_currentBlock->isClosed())
    {
      $this->_currentBlock = null;
    }

    return $this;
  }

  protected function _newBlockForLine($line, $tag = null)
  {
    $block = new Block();
    if($tag)
    {
      $block->setTag($tag);
    }
    else if(is_scalar($line))
    {
      $block->setTag(Paragraph::class);
      $block->setCloseOnEmptyLine(true);
    }
    $block->setAllowChildren(false);
    return $block;
  }

  protected function _addBlock(Block $block)
  {
    $this->_currentBlock = $block;
    $this->children[] = $block;
    return $this;
  }

  public function close()
  {
    foreach($this->children as $block)
    {
      if($block instanceof Block)
      {
        $block->close();
      }
    }
    $this->_openBlocks = [];

    return $this;
  }

  public function produceSafeHTML(): SafeHtml
  {
    $head = null;
    $content = $this->children;
    switch($this->level)
    {
      case 0:
        $content = Div::create($content)->addClass('preamble');
        break;
      case 1:
        $head = HeadingOne::create($this->title);
        break;
      case 2:
        $head = HeadingTwo::create($this->title);
        break;
      case 3:
        $head = HeadingThree::create($this->title);
        break;
      case 4:
        $head = HeadingFour::create($this->title);
        break;
      case 5:
        $head = HeadingFive::create($this->title);
        break;
      case 6:
        $head = HeadingSix::create($this->title);
        break;
    }

    return AbstractContainerTag::create($head, $content)->produceSafeHTML();
  }

}

