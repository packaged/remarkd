import { BasicBlock } from './BasicBlock.js';

/**
 * ParagraphBlock - handles paragraph content
 */
export class ParagraphBlock extends BasicBlock {
  constructor() {
    super();
    this._tag = 'p';
    this._allowChildren = false;
    this._contentType = 'simple';
  }

  async appendLine(ctx, line) {
    if (this._attr && this._attr.has('%hardbreaks') && this.children().length > 0) {
      this.addChild('<br>');
    }
    return super.appendLine(ctx, line);
  }
}

