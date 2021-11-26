<?php
namespace Packaged\Remarkd\Blocks;

class UnorderedListBlock extends AbstractListBlock
{
  protected $_listType = 'ul';

  public function startCodes(): array
  {
    return ['- ', '* ', '+ '];
  }

}
