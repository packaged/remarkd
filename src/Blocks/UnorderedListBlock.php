<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

class UnorderedListBlock extends AbstractListBlock
{
protected $_listType = 'ul';
  public function startCodes(): array
  {
    return ['- ', '* ', '+ '];
  }

}
