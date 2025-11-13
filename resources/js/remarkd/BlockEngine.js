/**
 * BlockEngine - processes block-level elements
 */
export class BlockEngine {
  constructor(context) {
    this._context = context;
    this._matchers = [];
    this._activeRoot = null;
    this._rootBlocks = [];
  }

  clearBlocks() {
    this._activeRoot = null;
    this._rootBlocks = [];
  }

  blocks() {
    return this._rootBlocks;
  }

  getMatchers() {
    return this._matchers;
  }

  setMatchers(matchers) {
    this._matchers = matchers;
    return this;
  }

  addMatcher(matcher) {
    this._matchers.push(matcher);
    return this;
  }

  async addLine(line, title = null, attribute = null) {
    let append = false;
    let block = this._activeRoot;

    if (this._activeRoot === null || !this._activeRoot.isOpen()) {
      block = await this.getBlock(line, attribute);
      append = true;
    }

    const added = await this._addLine(line, block, append, title, attribute);
    if (added === false) {
      await this._addLine(line, await this.getBlock(line, attribute), true, title, attribute);
    } else if (added === null) {
      this._activeRoot = null;
    }
  }

  async _addLine(line, block, appendBlock = false, title = null, attribute = null) {
    if (block === null) {
      if (line !== '') {
        this._rootBlocks.push(line);
      }
      return true;
    }

    if (appendBlock) {
      this._activeRoot = block;
      this._rootBlocks.push(block);
    }

    const result = await this._addBlockLine(line, block, attribute);
    if (result !== false) {
      if (title) {
        block.setTitle(title);
      }
      if (attribute !== null) {
        block.setAttributes(attribute);
      }
    }
    return result;
  }

  async _addBlockLine(line, block, attributes) {
    if (line === '' && block.closesOnEmptyLine()) {
      block.close();
      return null;
    }

    const allowLine = block.allowLine(line);
    if (allowLine === false) {
      block.close();
      return false;
    }

    if (line !== '' && line === block.closer()) {
      if (block.children().length > 0) {
        block.close();
        return null;
      }
      return true;
    }

    if (allowLine !== null) {
      for (const child of block.children()) {
        if (child.isOpen && child.isOpen()) {
          const res = await this._addBlockLine(line, child, attributes);
          if (res !== false) {
            return true;
          }
        }
      }
    }

    if (line === '+') {
      switch (block.contentType()) {
        case 'simple':
        case 'compound':
          line = '<br>';
          break;
      }
    }

    if (block.trimLeftLength() > 0 && typeof line === 'string' &&
        line.substring(0, block.trimLeftLength()) === block.trimLeftStr()) {
      line = line.substring(block.trimLeftLength());
    }

    if (block.allowChildren()) {
      switch (block.contentType()) {
        case 'compound':
          const child = await this.getBlock(line, attributes, block);
          if (child !== null && block.allowChild(child)) {
            block.addChild(child);
            const childAdd = await this._addBlockLine(line, child, attributes);
            return childAdd !== false;
          }
        default:
          await this._append(block, line);
          return true;
      }
    }

    return await this._append(block, line);
  }

  async _append(block, line) {
    if (typeof line === 'object' && line !== null) {
      block.addChild(line);
      return true;
    }

    let append = null;
    switch (block.contentType()) {
      case 'simple':
      case 'compound':
        if (line.endsWith(' +')) {
          line = line.substring(0, line.length - 2);
          append = '<br>';
        }
        break;
    }

    const ret = await block.appendLine(this._context, line);
    if (append !== null) {
      block.addChild(append);
    }

    if (ret) {
      return true;
    }

    block.close();
    return null;
  }

  async getBlock(line, attr = null, parent = null) {
    if (line === '') {
      return null;
    }

    let block = null;
    for (const matcher of this._matchers) {
      block = matcher.match(line, parent);
      if (block !== null) {
        if (attr !== null) {
          block.setAttributes(attr);
        }
        return block;
      }
    }

    if (line.trim() !== '') {
      if (attr && attr.position(0) === 'source') {
        const { CodeBlock } = await import('./blocks/CodeBlock.js');
        block = new CodeBlock();
        block.setCloseOnEmptyLine(true);
      } else if (!line.startsWith('{{') || !line.endsWith('}}')) {
        const { ParagraphBlock } = await import('./blocks/ParagraphBlock.js');
        block = new ParagraphBlock();
      } else {
        const { BasicBlock } = await import('./blocks/BasicBlock.js');
        block = new BasicBlock();
      }
    }

    if (attr !== null && block !== null) {
      block.setAttributes(attr);
    }
    return block;
  }
}

