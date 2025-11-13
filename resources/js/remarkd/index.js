/**
 * Remarkd JavaScript Library
 * Main entry point
 */

export { Remarkd } from './Remarkd.js';
export { Parser } from './Parser.js';
export { Document } from './Document.js';
export { Section } from './Section.js';
export { RemarkdContext } from './RemarkdContext.js';
export { BlockEngine } from './BlockEngine.js';
export { RuleEngine } from './RuleEngine.js';
export { ObjectEngine } from './ObjectEngine.js';
export { Attributes } from './Attributes.js';
export { DocumentData } from './DocumentData.js';

// Blocks
export { BasicBlock } from './blocks/BasicBlock.js';
export { ParagraphBlock } from './blocks/ParagraphBlock.js';
export { CodeBlock } from './blocks/CodeBlock.js';

// Rules
export { BoldText } from './rules/BoldText.js';

// Objects
export { LinkObject } from './objects/LinkObject.js';

/**
 * Parse remarkd text and return HTML
 * @param {string} text - The remarkd text to parse
 * @param {Object} options - Optional configuration
 * @returns {Promise<string>} - The rendered HTML
 */
export async function parse(text, options = {}) {
  // Access classes from exports - when called as Remarkd.parse(), 
  // we can access Remarkd.Remarkd and Remarkd.Parser
  // But in the bundle, we need to reference them from the module scope
  const RemarkdClass = (await import('./Remarkd.js')).Remarkd;
  const ParserClass = (await import('./Parser.js')).Parser;
  
  const remarkd = new RemarkdClass();
  const lines = text.split(/\r?\n/);
  const parser = new ParserClass(lines, remarkd);
  const doc = await parser.parse();
  return doc.render();
}

