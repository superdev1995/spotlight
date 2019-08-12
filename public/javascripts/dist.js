"use strict";

/**
 * Gets the text node content of a div
 * @param {*} div
 */
function div1d2text(div) {
  if (div.nodeType == 3) {
    return div.nodeValue;
  }

  var items = div.contents ? div.contents() : Array.from(div.childNodes);
  var texts = items.filter(function (f) {
    return f.nodeType == 3;
  });
  var t = texts[0];
  return t ? t.nodeValue : '';
}
/**
 * Contenteditable div to a single multiline string with \n
 * @param {*} rootdiv
 */


function div2text(rootdiv) {
  var output = "";
  var children = rootdiv.contents();

  for (var i = 0; i < children.length; i++) {
    var c = children[i];
    var ct = c.nodeType;
    if (ct != 3 && ct != 1) continue;
    if (c.nodeName.toLowerCase() == "span") continue; // Ignore spans

    var divval = div1d2text(c);
    output += divval;

    if (i < children.length - 2) {
      output += "\n";
    }
  }

  return output;
}

function text2divcontent(parent, text) {
  var lines = text.split('\n'); //Cleanup first

  while (parent.firstChild) {
    parent.removeChild(parent.firstChild);
  }

  if (lines.length == 0) return;
  var fl = lines[0];
  var flnode = document.createTextNode(fl);
  parent.appendChild(flnode);
  if (lines.length == 1) return;

  for (var l = 1; l < lines; l++) {
    var line = lines[l];
    var wrap = document.createElement('div');
    var lineNode = line.length == 0 ? document.createElement('br') : document.createTextNode(line);
    wrap.appendChild(lineNode);
    parent.appendChild(wrap);
  }
}

function isletter(keyevent) {
  if (keyevent.keyCode >= 48 && keyevent.keyCode <= 57) return true;
  if (keyevent.keyCode >= 65 && keyevent.keyCode <= 90) return true;
  return false;
}

function setCaretPosition(ctrl, elementPos) {
  // Modern browsers
  ctrl.focus();

  if (window.getSelection && document.createRange) {
    // IE 9 and non-IE
    var sel = window.getSelection();
    var range = document.createRange();
    range.setStart(ctrl, elementPos);
    range.collapse(true);
    sel.removeAllRanges();
    sel.addRange(range);
  } else if (document.body.createTextRange) {
    // IE < 9
    var textRange = document.body.createTextRange();
    textRange.moveToElementText(ctrl);
    textRange.collapse(true);
    textRange.select();
  }
}

function hexdump(buffer, blockSize) {
  blockSize = blockSize || 16;
  var lines = [];
  var hex = "0123456789ABCDEF";

  for (var b = 0; b < buffer.length; b += blockSize) {
    var block = buffer.slice(b, Math.min(b + blockSize, buffer.length));
    var addr = ("0000" + b.toString(16)).slice(-4);
    var codes = block.split('').map(function (ch) {
      var code = ch.charCodeAt(0);
      return " " + hex[(0xF0 & code) >> 4] + hex[0x0F & code];
    }).join("");
    codes += "   ".repeat(blockSize - block.length);
    var chars = block.replace(/[\x00-\x1F\x20]/g, '.');
    chars += " ".repeat(blockSize - block.length);
    lines.push(addr + " " + codes + "  " + chars);
  }

  return lines.join("\n");
}

function decodeHtmlchars(text) {
  var a0 = String.fromCharCode(parseInt(0xa0));
  text = text.replace(a0, ' ');
  return text;
}

function lcnt(input) {
  console.log("Input; ", input);
  return 0;
}

function spliceSlice(str, index, count, add) {
  // We cannot pass negative indexes directly to the 2nd slicing operation.
  if (index < 0) {
    index = str.length + index;

    if (index < 0) {
      index = 0;
    }
  }

  return str.slice(0, index) + (add || "") + str.slice(index + count);
}
"use strict";

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var Selection =
/*#__PURE__*/
function () {
  function Selection($container) {
    _classCallCheck(this, Selection);

    this.$container = $container;
  }

  _createClass(Selection, [{
    key: "getSelection",
    value: function getSelection() {
      if (window.getSelection) {
        return window.getSelection().getRangeAt(0);
      } else if (document.selection) {
        return document.selection.createRange();
      }
    }
  }, {
    key: "sumCurrentOffset",
    value: function sumCurrentOffset(root, node, startOffset) {
      for (var _i = 0, _Array$from = Array.from(root.childNodes); _i < _Array$from.length; _i++) {
        var ele = _Array$from[_i];

        if (node === ele) {
          break;
        }

        if (ele.contains(node)) {
          var result = this.sumCurrentOffset(ele, node, 0);
          startOffset += result;
          break;
        } else {
          startOffset += ele.textContent.length;
        }
      }

      return startOffset;
    }
  }, {
    key: "findNodeForPosition",
    value: function findNodeForPosition($container, currentOffset) {
      var node;

      var _this$findNode = this.findNode($container.childNodes, currentOffset);

      node = _this$findNode.node;
      currentOffset = _this$findNode.currentOffset;

      if (node.childNodes.length === 0) {
        return {
          node: node,
          currentOffset: currentOffset
        };
      } else {
        return this.findNodeForPosition(node, currentOffset);
      }
    }
  }, {
    key: "findNode",
    value: function findNode(childNodes, currentOffset) {
      for (var _i2 = 0, _Array$from2 = Array.from(childNodes); _i2 < _Array$from2.length; _i2++) {
        var node = _Array$from2[_i2];

        if (currentOffset - node.textContent.length <= 0) {
          return {
            node: node,
            currentOffset: currentOffset
          };
        } else {
          currentOffset -= node.textContent.length;
        }
      }
    }
  }, {
    key: "saveCurrentSelection",
    value: function saveCurrentSelection() {
      this.currentSelection = this.getSelection();
      this.startOffset = this.currentSelection.startOffset;
      this.currentOffset = this.sumCurrentOffset(this.$container, this.currentSelection.startContainer, this.startOffset);
    }
  }, {
    key: "restoreSelection",
    value: function restoreSelection() {
      var node;

      if (this.currentOffset === 0) {
        return;
      }

      var range = document.createRange();

      var _this$findNodeForPosi = this.findNodeForPosition(this.$container, this.currentOffset);

      node = _this$findNodeForPosi.node;
      this.currentOffset = _this$findNodeForPosi.currentOffset;
      range.setStart(node, this.currentOffset);
      range.collapse(true);
      var sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(range);
    }
  }]);

  return Selection;
}();
