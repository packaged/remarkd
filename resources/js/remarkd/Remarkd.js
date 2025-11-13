import { RemarkdContext } from './RemarkdContext.js';
import { ParagraphBlock } from './blocks/ParagraphBlock.js';
import { CodeBlock } from './blocks/CodeBlock.js';
import { BoldText } from './rules/BoldText.js';
import { LinkObject } from './objects/LinkObject.js';

/**
 * Remarkd - main class for parsing remarkd text
 */
export class Remarkd {
  constructor(context = null) {
    if (context === null) {
      context = new RemarkdContext();
      this.applyDefaultBlocks(context.blockEngine());
      this.applyDefaultRules(context.ruleEngine());
      this.applyDefaultObjects(context.objectEngine());
    }
    this._context = context;
  }

  ctx() {
    return this._context;
  }

  applyDefaultBlocks(engine) {
    // Import and register default blocks
    // For now, we'll register them dynamically as needed
    // This will be expanded with all default blocks
  }

  applyDefaultRules(engine) {
    engine.registerRule(new BoldText());
    // More rules will be added here
  }

  applyDefaultObjects(engine) {
    engine.registerObject(new LinkObject());
    // More objects will be added here
  }

  registerBlock(block) {
    return this.ctx().blockEngine().addMatcher(block);
  }

  registerRule(rule) {
    return this.ctx().ruleEngine().registerRule(rule);
  }

  registerObject(object) {
    return this.ctx().objectEngine().registerObject(object);
  }
}

