import { BasicBlock } from './BasicBlock.js';

/**
 * CodeBlock - handles code blocks
 */
export class CodeBlock extends BasicBlock {
  constructor() {
    super();
    this._tag = 'pre';
    this._allowChildren = false;
    this._contentType = 'verbatim';
    this._closeOnEmpty = false;
  }

  async _formatLine(ctx, line) {
    // Code blocks don't process rules/objects
    return line;
  }
}

