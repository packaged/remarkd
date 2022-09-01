<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;

interface Block
{
  public function setTitle(string $title);

  public function setAttributes(Attributes $attributes);

  public function closer(): ?string;

  public function close(): array;

  public function isOpen(): bool;

  public function isContainer(): bool;

  public function closesOnEmptyLine(): bool;

  public function trimLeftLength(): int;

  public function allowChildren(): bool;

  public function children(): array;

  public function addChild($child);

  public function allowLine(string $line): bool;

  /**
   * @param \Packaged\Remarkd\RemarkdContext $ctx
   * @param string                           $line
   *
   * true = line appended
   * false = block complete
   *
   * @return bool
   */
  public function addLine(RemarkdContext $ctx, string $line): bool;
}
