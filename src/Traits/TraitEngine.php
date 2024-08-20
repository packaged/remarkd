<?php

namespace Packaged\Remarkd\Traits;

use Packaged\Remarkd\RemarkdContext;

class TraitEngine
{
  protected RemarkdContext $_context;

  /** @var AbstractTraits[] */
  protected array $_traits = [];

  public function __construct(RemarkdContext $ctx)
  {
    $this->_context = $ctx;
  }

  public function registerTrait(AbstractTraits $trait): self
  {
    $this->_traits[$trait->getIdentifier()] = $trait;
    return $this;
  }

  public function parse(array &$rawLines, string $text)
  {
    foreach($this->_traits as $trait)
    {
      if(preg_match($trait->getPattern(), $text, $matches))
      {
        $text = $trait->parse($rawLines, $text, $matches);
      }
    }

    return $text;
  }
}
