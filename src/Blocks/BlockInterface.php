<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\RemarkdContext;

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

  public function complete(RemarkdContext $ctx): string;
}
