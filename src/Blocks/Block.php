<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Remarkd\Attributes;
use Packaged\Remarkd\RemarkdContext;
use Packaged\SafeHtml\ISafeHtmlProducer;

interface Block extends ISafeHtmlProducer
{
  const TYPE_SIMPLE = 'simple';
  const TYPE_COMPOUND = 'compound';
  const TYPE_VERBATIM = 'verbatim';
  const TYPE_RAW = 'raw';
  const TYPE_EMPTY = 'empty';
  const TYPE_TABLE = 'table';

  public function contentType(): string;

  public function setTitle(string $title);

  public function setAttributes(Attributes $attributes);

  public function closer(): ?string;

  public function close(): array;

  public function isOpen(): bool;

  public function isContainer(): bool;

  public function closesOnEmptyLine(): bool;

  public function trimLeftLength(): int;

  public function trimLeftStr(): string;

  public function allowChildren(): bool;

  public function children(): array;

  public function allowChild($child): bool;

  public function addChild($child);

  /**
   * @param string $line
   *
   * true = allow
   * false = reject
   * null = direct only
   *
   * @return bool|null
   */
  public function allowLine(string $line): ?bool;

  /**
   * @param \Packaged\Remarkd\RemarkdContext $ctx
   * @param string                           $line
   *
   * true = line appended
   * false = block complete
   *
   * @return bool
   */
  public function appendLine(RemarkdContext $ctx, string $line): bool;
}
