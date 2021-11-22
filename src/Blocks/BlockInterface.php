<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Rules\RuleEngine;

interface BlockInterface
{
  /**
   * @param string $line
   *
   * true = line appended
   * false = line rejected
   * null = block complete
   *
   * @return null|bool
   */
  public function addNewLine(string $line);

  public function complete(BlockEngine $blockEngine, RuleEngine $ruleEngine): string;
}
