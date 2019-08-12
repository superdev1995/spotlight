/**
 * Gets the row of some text in a contenteditable div.
 * @param root
 * @param node
 * @returns {number}
 */
function getContenteditableRow(root, node){
    if(root == node){
        return 0;
    }
    //Following lines are wrapped inside divs.
    if(node.nodeType == 3 && node.parentElement!=root){
        node = node.parentNode
    }
    var rootChildren = root.childNodes;
    var index = Array.prototype.indexOf.call(rootChildren, node);
    return index
}
function trimCursorOffset(c){
    return {
        line: c.line,
        col: c.col,
        node: c.node,
        offset: c.col
    }
}
function getPrevCaretCharacterOffsetWithin(col, line, rootElement, selection, caretOffset){
    if(col>0){
        col--;
        caretOffset--;
    }else{
        line--;
        throw 'Not implemented..'
    }

    return {
        line: line,
        col: col,
        caretOffset: caretOffset
    }
}
/**
 * Gets the line, column and offset of a cursor within an element
 * @param element
 * @param shouldGo1PosBack
 * @returns {{col: number, offset: number, line: number, node: node}}
 */
function getCaretCharacterOffsetWithin(element, shouldGo1PosBack) {
    var caretOffset = 0;
    var doc = element.ownerDocument || element.document;
    var win = doc.defaultView || doc.parentWindow;
    var sel;
    var line = 0;
    var col = 0;
    var node = null;
    if (typeof win.getSelection != "undefined") {
        sel = win.getSelection();
        if (sel.rangeCount > 0) {
            var range = sel.getRangeAt(0);
            var preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(element);
            preCaretRange.setEnd(range.endContainer, range.endOffset);
            caretOffset = preCaretRange.toString().length;
            col = sel.anchorOffset;
            line = getContenteditableRow(element, sel.anchorNode);
            node = sel.anchorNode;
            if(shouldGo1PosBack && false){
                var newpos = getPrevCaretCharacterOffsetWithin(col, line, element, sel, caretOffset)
                col = newpos.col;
                line = newpos.line;
                caretOffset = newpos.caretOffset;
            }
        }
    } else if ( (sel = doc.selection) && sel.type != "Control") {
        console.warn("Not expected..");
        var textRange = sel.createRange();
        var preCaretTextRange = doc.body.createTextRange();
        preCaretTextRange.moveToElementText(element);
        preCaretTextRange.setEndPoint("EndToEnd", textRange);
        caretOffset = preCaretTextRange.text.length;
    }
    return {
        node: node,
        line: line,
        col: col,
        offset: caretOffset
    };
}


function insertAfter(newNode, referenceNode){
    if(referenceNode){
        referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling)
    }else{
       console.warn("Could not insert node because of nil reference.") 
    }
    
}

function ensureFirstLineIsDiv(root){
    return
    if(!root.childNodes[0]){
        var wrapper = document.createElement('div')
        root.prepend(wrapper)
    }else if(root.childNodes[0].nodeType==3){
        var wrapper = document.createElement('div')
        wrapper.prepend(root.childNodes[0])
        root.prepend(wrapper)
    }else if(root.childNodes[0].tagName == 'SPAN'){
        var wrapper = document.createElement('div')
        root.prepend(wrapper)        
    }
}
function ensureLineWrap(root, cursor){
    return;
    var node = cursor.node
    //The parent of the node should be a div, if not, wrap it
    var parentNode = node.parentNode
    if(node==root){
        //We must wrap
        var wrapper = document.createElement('div')
        var textNodes = Array.from(node.childNodes).filter(function(f){ f.nodeType == 3})
        var textNode = textNodes[0]
        if(textNode){
            textNode.appendTo(wrapper)
            wrapper.appendTo(node)
        }else{
            node.prepend(wrapper)
        }
    }else{

    }

    console.warn(cursor)
}
function getStyle(el,styleProp)
{
    if (el.currentStyle)
        var y = el.currentStyle[styleProp];
    else if (window.getComputedStyle)
        var y = document.defaultView.getComputedStyle(el,null).getPropertyValue(styleProp);
    return y;
}