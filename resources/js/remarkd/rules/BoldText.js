/**
 * BoldText - processes **bold** and *bold* text
 */
export class BoldText {
  apply(text) {
    // First handle **bold** (unconstrained)
    let result = text.replace(/(\*\*)([^\*]+?)(\*\*)/g, '<strong>$2</strong>');
    // Then handle *bold* (constrained)
    result = result.replace(/([^\w])\*([^*]+)\*/g, '$1<strong>$2</strong>');
    return result;
  }
}

