/**
 * RuleEngine - processes inline text formatting rules
 */
export class RuleEngine {
  constructor(context) {
    this._context = context;
    this._rules = [];
  }

  registerRule(rule) {
    this._rules.push(rule);
    return this;
  }

  replaceRule(ruleClass, rule) {
    const index = this._rules.findIndex(r => r.constructor === ruleClass);
    if (index !== -1) {
      this._rules[index] = rule;
    }
    return this;
  }

  parse(text) {
    let result = text;
    for (const rule of this._rules) {
      result = rule.apply(result);
    }
    return result;
  }
}

