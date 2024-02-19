<?php
namespace Packaged\Remarkd\Blocks;

class ListBlock extends ContainerBlock
{
  protected $_contentType = Block::TYPE_COMPOUND;
  protected $_allowChildren = true;
  protected $_contentContainer = false;
  protected $_closeOnEmpty = true;

  public function setCloser($tag)
  {
    return $this;
  }

  public function match($line, ?Block $parent): ?Block
  {
    if($parent instanceof ListBlock)
    {
      return null;
    }
    return parent::match($line, $parent);
  }
}
