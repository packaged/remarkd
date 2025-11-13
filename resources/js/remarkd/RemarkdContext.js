import { BlockEngine } from './BlockEngine.js';
import { RuleEngine } from './RuleEngine.js';
import { ObjectEngine } from './ObjectEngine.js';
import { DocumentData } from './DocumentData.js';

/**
 * RemarkdContext - central context object that holds all engines
 */
export class RemarkdContext {
  constructor() {
    this._blockEngine = new BlockEngine(this);
    this._ruleEngine = new RuleEngine(this);
    this._objectEngine = new ObjectEngine(this);
    this._documentData = new DocumentData();
    this._projectRoot = '';
    this._resourceRoot = '';
    this._meta = {};
  }

  blockEngine() {
    return this._blockEngine;
  }

  ruleEngine() {
    return this._ruleEngine;
  }

  objectEngine() {
    return this._objectEngine;
  }

  documentData() {
    return this._documentData;
  }

  setBlockEngine(blockEngine) {
    this._blockEngine = blockEngine;
    return this;
  }

  setRuleEngine(ruleEngine) {
    this._ruleEngine = ruleEngine;
    return this;
  }

  setObjectEngine(objectEngine) {
    this._objectEngine = objectEngine;
    return this;
  }

  setProjectRoot(root) {
    this._projectRoot = root;
    return this;
  }

  getProjectRoot() {
    return this._projectRoot;
  }

  setResourceRoot(root) {
    this._resourceRoot = root;
    return this;
  }

  getResourceRoot() {
    return this._resourceRoot;
  }

  meta() {
    return this._meta;
  }

  setMeta(meta) {
    this._meta = meta;
    return this;
  }
}

