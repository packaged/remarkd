<?php
namespace Packaged\Remarkd\Blocks;

use Packaged\Glimpse\Tags\Div;
use Packaged\Glimpse\Core\CustomHtmlTag;
use Packaged\Glimpse\Tags\Table\TableCell;
use Packaged\Glimpse\Tags\Table\TableHeading;
use Packaged\Remarkd\RemarkdContext;
use Packaged\SafeHtml\SafeHtml;
use Packaged\Ui\Html\HtmlElement;

class TableBlock extends BasicBlock implements BlockMatcher
{
  protected $_closer = '|===';
  protected $_rows = [];
  protected $_currentRow = [];
  const PROS_CONS_ATTRS = ['pros-cons', 'proscons'];

  public function match($line, ?Block $parent): ?Block
  {
    if($line === '|===')
    {
      return new static();
    }
    return null;
  }

  public function appendLine(RemarkdContext $ctx, string $line): bool
  {
    if($line === '|===')
    {
      return true;
    }

    if($line === '')
    {
      $this->_commitRow();
      return true;
    }

    if($line[0] === '|')
    {
      foreach($this->_parseCells($line) as $cell)
      {
        $this->_currentRow[] = new SafeHtml($ctx->objectEngine()->parse($ctx->ruleEngine()->parse($cell)));
      }
      return true;
    }

    return true;
  }

  public function close(): array
  {
    $this->_commitRow();
    return parent::close();
  }

  public function children(): array
  {
    if(!empty($this->_rows) || !empty($this->_currentRow))
    {
      return [true];
    }
    return [];
  }

  protected function _parseCells(string $line): array
  {
    $line = ltrim($line, '|');
    return array_values(array_filter(array_map('trim', preg_split('/\s+\|/', $line))));
  }

  protected function _commitRow(): void
  {
    if(!empty($this->_currentRow))
    {
      $this->_rows[] = $this->_currentRow;
      $this->_currentRow = [];
    }
  }

  protected function _produceElement(): HtmlElement
  {
    $table = new CustomHtmlTag('table');
    $table->addClass('remarkd-table');
    if($this->_enabled('striped', false))
    {
      $table->addClass('remarkd-table--striped');
    }
    if($this->_isProsCons())
    {
      $table->addClass('pros-cons-table');
      if($this->_enabled('background-colour', true) && $this->_enabled('background-color', true))
      {
        $table->addClass('pros-cons-table--background');
      }
      if($this->_enabled('text-colour', true) && $this->_enabled('text-color', true))
      {
        $table->addClass('pros-cons-table--text-color');
      }
      if($this->_enabled('header-icons', true))
      {
        $table->addClass('pros-cons-table--header-icons');
      }
    }
    $body = new CustomHtmlTag('tbody');

    foreach($this->_rows as $idx => $row)
    {
      $tableRow = new CustomHtmlTag('tr');
      if($idx === 0)
      {
        $head = new CustomHtmlTag('thead');
        foreach($row as $cellIdx => $cell)
        {
          $tableRow->appendContent($this->_cell(TableHeading::class, $cell, $cellIdx, true));
        }
        $head->appendContent($tableRow);
        $table->appendContent($head);
      }
      else
      {
        foreach($row as $cellIdx => $cell)
        {
          $tableRow->appendContent($this->_cell(TableCell::class, $cell, $cellIdx, false));
        }
        $body->appendContent($tableRow);
      }
    }
    $table->appendContent($body);

    if($this->_title)
    {
      $block = Div::create(Div::create($this->_title)->addClass('title'), $table)->addClass('table-block');
      if($this->_isProsCons())
      {
        $block->addClass('pros-cons-block');
      }
      return $block;
    }

    return $table;
  }

  protected function _cell(string $tag, SafeHtml $cell, int $cellIdx, bool $header): HtmlElement
  {
    $content = $cell;
    if($header && $this->_isProsCons() && $this->_enabled('header-icons', true))
    {
      $content = new SafeHtml($this->_icon($cellIdx) . ' ' . $cell);
    }

    $ele = $tag::create($content);
    if($this->_isProsCons())
    {
      $ele->addClass('pros-cons-cell');
      $ele->addClass($cellIdx === 0 ? 'pros-cons-cell--con' : 'pros-cons-cell--pro');
    }
    return $ele;
  }

  protected function _icon(int $cellIdx): string
  {
    $icon = $cellIdx === 0
      ? ($this->_attr ? $this->_attr->get('con-icon', '❌') : '❌')
      : ($this->_attr ? $this->_attr->get('pro-icon', '✅') : '✅');
    return '<span class="pros-cons-icon">' . htmlspecialchars($icon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
  }

  protected function _isProsCons(): bool
  {
    if(!$this->_attr)
    {
      return false;
    }
    foreach(self::PROS_CONS_ATTRS as $attr)
    {
      if($this->_attr->has($attr) || $this->_attr->position(0) === $attr)
      {
        return true;
      }
    }
    return false;
  }

  protected function _enabled(string $key, bool $default): bool
  {
    if(!$this->_attr || !$this->_attr->has($key))
    {
      return $default;
    }
    return !in_array(strtolower((string)$this->_attr->get($key)), ['0', 'false', 'no', 'off'], true);
  }
}
