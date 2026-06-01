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
    $body = new CustomHtmlTag('tbody');

    foreach($this->_rows as $idx => $row)
    {
      $tableRow = new CustomHtmlTag('tr');
      if($idx === 0)
      {
        $head = new CustomHtmlTag('thead');
        foreach($row as $cell)
        {
          $tableRow->appendContent(TableHeading::create($cell));
        }
        $head->appendContent($tableRow);
        $table->appendContent($head);
      }
      else
      {
        foreach($row as $cell)
        {
          $tableRow->appendContent(TableCell::create($cell));
        }
        $body->appendContent($tableRow);
      }
    }
    $table->appendContent($body);

    if($this->_title)
    {
      return Div::create(Div::create($this->_title)->addClass('title'), $table)->addClass('table-block');
    }

    return $table;
  }
}
