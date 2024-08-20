<?php

namespace Packaged\Remarkd\Traits;

use Packaged\Remarkd\RemarkdContext;

abstract class AbstractTraits
{
  protected RemarkdContext $_context;

  public function __construct(RemarkdContext $context)
  {
    $this->_context = $context;
  }

  abstract public function getIdentifier(): string;

  abstract function parse(array &$rawLines, string $currentLine, array $options);

  public function getPattern(): string
  {
    return '/^t::' . $this->getIdentifier() . '::(.*)$/';
  }
}
