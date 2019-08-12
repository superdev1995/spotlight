function Recommender(user_id, options){
    options = options || {};
    this.user_id = user_id;
    this.frameworks = options.frameworks || [];
    this.inputTimeoutTime = options.inputTimeoutTime || 500;
    this.input = options.input;
    this.inputTimeout =null;
}
Recommender.prototype.recommend = function(text){
    return fetch(geturl('/recommend'), {
        method: 'POST',
        body: JSON.stringify({
            user_id: this.user_id,
            text: text,
            frameworks: this.frameworks
        }),
        cache: 'no-cache',
        redirect: 'follow',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(function(r){
            //We'll get a dict of framework_id -> { suggestion: suggestion of goal id}
            return r.json()
        })
        .then(function(j){
            return j
        })
};
Recommender.prototype.recommendAndMark = function(text){
    var self = this;
    this.recommend(text)
        .then(function(resp){
           var fids = Object.keys(resp);
           for(var i=0; i<fids.length; i++){
               var fid = fids[i];
               var fdata = resp[fid];
               var suggestions = fdata['suggestion'];
                if(!(suggestions===null)){
                    self.mark(fid, suggestions)
                }
           }
        });
};
Recommender.prototype.mark = function(fid, suggestion_goal_ids){
    for(var i=0; i<suggestion_goal_ids.length; i++){
        var goal_id = suggestion_goal_ids[i];
        if(goal_id===null){
            continue;
        }
        this.markGoal(goal_id);
    }
};
Recommender.prototype.markGoal = function(goal_id){
    var selector = "[data-goal-id=\"" + goal_id + "\"]";
    var goal_badge = document.querySelector(selector);
    if(goal_badge===null){
        return false;
    }
    goal_badge.classList.remove('hidden');
    console.log("Marked goal: ", goal_badge);
    return true;
};
Recommender.prototype.val = function(){
    return this.input ? this.input.value : '';
};
Recommender.prototype.resetInputTimeout = function(){
    if(this.inputTimeout===null) {
        return;
    }
    //console.warn("Resetting input timeout");
    clearTimeout(this.inputTimeout);
    this.inputTimeout = null;
};
Recommender.prototype.startInputTimeout = function(inputElement){
    if(this.inputTimeout!==null){
        console.warn("Recommender timeout already started.", this.inputTimeout);
        return;
    }
    this.timeoutInputElement = inputElement;
    //console.warn("Timeout for ", this.inputTimeoutTime, this.timeoutInputElement);
    this.inputTimeout = setTimeout(this.onInputTimedOut.bind(this), this.inputTimeoutTime);
};
/**
 * Called when the input has timed out and a recommendation needs to be triggered
 */
Recommender.prototype.onInputTimedOut = function(){

    var value = this.timeoutInputElement[0].value;
    //console.warn("Input timed out", this.timeoutInputElement, value);
    this.recommendAndMark(value);
    this.inputTimeout = null; //Cleanup

};

var recommenders = [];
Recommender.resetInputTimeouts = function(){
    for(var i=0; i<recommenders.length; i++){
        var r = recommenders[i];
        r.resetInputTimeout();
    }
};
Recommender.init = function(user_id, options){
    var r = new Recommender(user_id, options);
    recommenders.push(r);
    return r;
};


/// AUTOCOMPLETE _________________________________________________

/**
 *
 * @param user_id
 * @param options
 * @constructor
 */
function Autocompletion(user_id, options){
    options = options || {};
    this.lastQuery = null;
    this.user_id = user_id;
    this.lastResults = [];
    this.querySubstrIndex = 0;
    this.fetcher = new SuggestionFetcher(user_id);
    this.manageIndex = options.manageIndex===true;
    this.callbackOnSuggestionStart = function(){};
}

function geturl(u){
    return "https://teachkloud.com/mlapi" + u
    //return "http://localhost:8081/mlapi" + u
}

Autocompletion.prototype.wc = function(){
    if(this.lastQuery===null) return 0;
    return this.lastQuery.split(' ').length
};
Autocompletion.prototype.getQueryDetails = function(str){
    var isWhitespace = str.trim().length == 0
    return {
        val: str,
        start: this.lastQuery.length,
        whitespace: isWhitespace
    }
}

Autocompletion.prototype.parseQuery = function(query){
    var output = query;
    if(!this.manageIndex){
        return output;
    }
    output = output.substr(this.querySubstrIndex); // Substring the query if needed
    if(this.querySubstrIndex>query.length){
        this.querySubstrIndex = getIndexOfLastWord(query);
        query = query.substr(this.querySubstrIndex); // Substring the query if needed
        console.log("Trimming last word start to", query.length)
    }
    return output
};

function getIndexOfLastWord(s){
    var re = /\S+/g;
    var match;
    var lastWordStart = 0;
    while ((match = re.exec(s)) != null) {
        //console.log("match found at " + match.index, match);
        lastWordStart = match.index
    }
    return lastWordStart
}

Autocompletion.prototype.handleBadTranslation = function(failedQuery){
    var queryPartIx = getIndexOfLastWord(failedQuery);
    this.setQueryIndex(queryPartIx);
    //var substr = failedQuery.substr(this.lastWordStart);
    //console.log("Updating start index: ", queryPartIx);
    return new Promise(function(resolve, reject){
                resolve([])
            })
};

///Not used so far
Autocompletion.prototype.completeSuggestion = function(){

}
Autocompletion.prototype.setQueryIndex = function(pos){
    this.querySubstrIndex = pos
}

Autocompletion.prototype.suggest = function(query){
    var parsedQuery = this.parseQuery(query)
    //console.log("Got query: ", query)
    if(parsedQuery.trim().length==0){
        console.warn("Query is empty!", this);
        return new Promise(function(resolve, reject){
            resolve([])
        })
    }
    this.lastQuery = parsedQuery;
    //console.log(this.lastQuery, query);
    return this.fetcher.fetch(parsedQuery,0)
        .then((function(suggestions){
            this.lastResults = suggestions || [];
            if(this.lastResults.length===0){
                return this.handleBadTranslation(query)
            }
            this.callbackOnSuggestionStart();
            //console.log("Setting last results to cnt: ", "`" + parsedQuery + "`", this.lastResults.length)
            return this.lastResults
        }).bind(this))

};
Autocompletion.prototype.suggestionIndex = function(){
    return this.querySubstrIndex
};

Autocompletion.prototype.train = function(text_to_add){
    return fetch(geturl('/train_autocomplete'), {
        method: 'POST',
        body: JSON.stringify({
            user_id: this.user_id,
            text: text_to_add
        }),
        cache: 'no-cache',
        redirect: 'follow',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(function(r){
            return r.json()
        })
        .then(function(j){
            return j
        })
};
Autocompletion.prototype.onStart = function(callback){
    this.callbackOnSuggestionStart = callback;
};
var aci = 0;
window.autocompleters = {};
Autocompletion.init = function(userId, input, options){
    //userId = 9
    options = options || {};
    options.manageIndex = false;
    var aui = new AutosuggestUi(input, new Autocompletion(userId,options), options);
    window.autocompleters[aci] = aui;
    aci++;
    return aui
};

Autocompletion.load_all = function(user_id){
    var textareas = document.querySelectorAll('[data-autocomplete]');
    for(var i=0; i<textareas.length; i++){
        var area = textareas[i];
        var a = Autocompletion.init(user_id, area, {
            onValueChange: function(a){
                //doKeywordSearch({ autocomplete: a })
            }
        });
    }
}

function SuggestionFetcher(user_id){
    this.currentSuggestions = [];
    this.user_id = user_id
}

function overlaySuggestion(suggestion, text){
    var sugright = suggestion.substr(text.length)
    var out = text + sugright;
    return out;
}

//Fetch the suggestions
SuggestionFetcher.prototype.fetch = function(textForSuggestion, ix){
    console.log("Fetching suggestion for : `" + textForSuggestion + "`");
    var matches = [];
    var t= textForSuggestion.toLowerCase()
    //Find all the suggestions that start in the same way
    for(var i=0; i<this.currentSuggestions.length; i++){
        var s = this.currentSuggestions[i].toLowerCase()
        if(s.startsWith(t)){
            //TODO: Match the text the same way
            var overlay = overlaySuggestion(s, textForSuggestion)
            matches.push(overlay)
            break; //We need just 1 suggestion
        }else{
            //console.log(hexdump(s)); console.log(hexdump(t))
        }
    }
    if(matches.length>0){

        return new Promise(function(res, rej){
            res(matches)
        })
    }
    return fetch(geturl("/autocomplete"),{
        method: 'POST',
        body: JSON.stringify({
            user_id: this.user_id,
            text: textForSuggestion
        }),
        cache: 'no-cache',
        redirect: 'follow',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(function(r){
            if(!r.ok){
                throw 'Request failed'
            }
            return r.json()
        })
        .then((function(ret){
            this.currentSuggestions = ret.suggestions;
            return this.currentSuggestions
        }).bind(this))
};


function AutosuggestUi(inputElement, engine, options){
    this._rEscapeChars = /\/|\\|\.|\||\*|\&|\+|\(|\)|\[|\]|\?|\$|\^/g;
    this._rMatch = /[A-Z]?[a-z]+|[0-9]+/g;
    this._completeKeys = [
        //13,
        9
    ];
    //Callbacks
    options = options || {};
    //Called whenever the value changes
    this.onValueChange = options.onValueChange || function(s){};


    //Elements
    this.input = $(inputElement);
    this.searchContainer = $("<div class='autocomplete-box-container'></div>");
    this.searchBox = $("<div class='autocomplete-box' contenteditable='true' aria-multiline='true' hidefocus='true' tabindex=\"0\"></div>");
    this.searchBox.addClass(inputElement.className);
    var attachPoint = this.input.prev();
    var parent = this.input.parent();
    if(attachPoint.length===0) {
        attachPoint = parent;
        this.searchContainer.appendTo(attachPoint);
    }else{
        this.searchContainer.insertAfter(attachPoint);
    }

    this.searchBox.appendTo(this.searchContainer);
    this.currentTextNode = null;

    this.suggestionElement = this.__createSuggestionBox();

    //Indexes
    this.suggestionSourceStartIndex = 0;
    this.currentSuggestions = [];
    this.setKeySelection(0,0,null); // Nothing is selected

    //Event handlers
    this.searchBox.on('keydown', this.handleSearchbarKeyDown.bind(this));
    //this.searchBox.on('keyup', this.handleSearchbarKeyDown.bind(this));
    this.searchBox.on('keyup', this.handleSearchbarKeyUp.bind(this));
    this.searchBox.on('click', this.handleSearchbarClick.bind(this));

    if(this.isInputField()){
        this.input.css('opacity', 0)
        this.input.css('height', 0);
        this.input.css('padding', 0);
    }else{
        this.input.hide()
    }
    
    console.warn(this.isInputField())
    this.applySizing()
    $(window).on('resize', (function(){
        this.applySizing()
    }).bind(this));

    //The autocompletion engine that would be used
    this.engine = engine;
    var currentValue = this.input.get(0).value;
    this.engine.onStart(function(){
       //this.currentSuggestionStartIndex = this.index
    });

    //Style it up
    //this.input.hide();
    this.searchBox.get(0).textContent = currentValue;
}

AutosuggestUi.prototype.isInputField = function(){
    return this.input.get(0).tagName.toLowerCase()=='input';
}

AutosuggestUi.prototype.applySizing = function(applyWidth){
    var attachPoint = this.input.prev();
    var parent = this.input.parent();
    var elementWidth = 0;
    var elementHeight = 0;
    var inp = this.input.get(0);
    var rowheight = getStyle(inp, "line-height");
    var letterSpacing = getStyle(inp, "letter-spacing");
    var rowcount = inp.getAttribute('rows');
    var sizeAttr = inp.getAttribute('size')
    
    
    elementHeight = parseFloat(rowheight) * parseFloat(rowcount);
    
    //elementWidth = this.input.width();
    if(elementHeight<=0){
        elementHeight = this.input.height()
    }
    if(attachPoint.length===0) {
        attachPoint = parent;
        elementWidth = this.input.width();
    }
    if(sizeAttr){
        // var cw = cwidth(inp);
        // elementWidth = sizeAttr * cw;
        // console.log("Size attrs: ", sizeAttr, cw)
        // applyWidth = true;
    }

    if(elementWidth>0 && applyWidth) {
        this.searchBox.width(elementWidth);
        console.log("Setting new width to " + elementWidth + " for ", this.searchBox.get(0))
    }
    if(elementHeight>0){
        this.searchBox.height(elementHeight);
        console.log("Setting new height to " + elementHeight + " for ", this.searchBox.get(0))
    }
    console.log("Applied sizing");
}

AutosuggestUi.prototype.inputVal = function(){
    return this.input.get(0).value;
};
/**
 * @var suggestions
 * @var textToComplete The value to make the suggestion complete from
 */
AutosuggestUi.prototype._showSuggestions = function(suggestions, cursor, textToComplete, completeRowText){
    if(suggestions.length===0) {
        var lastWordIndex = getIndexOfLastWord(completeRowText);
        //this.setSuggestionSourceStartIndex(this.index);
        this.setSuggestionSourceStartIndex(lastWordIndex);
        //console.log("Setting suggestion source start: ", this.suggestionSourceStartIndex);
        //console.log("Got no suggestions, update the suggestion source start index to: ", completeRowText, lastWordIndex, this.getSuggestionSourceText());
        this.suggestionHolder.hide();
        this.suggestionElement.removeClass('active');
        return;
    }
    this.__setSuggestionText(this.__getSuggestionLeft(suggestions[0], textToComplete))
};

/**
 * Where the source text for a suggestion starts from.
 * @param ix
 */
AutosuggestUi.prototype.setSuggestionSourceStartIndex = function(ix){
    console.warn("Set index of suggestion source: ", ix);
    this.suggestionSourceStartIndex = ix;
}

/**
 * Gets the vale of the current suggestion that's been typed in already.
 */
AutosuggestUi.prototype.getSuggestionSourceText = function(){
    //var val = this.val();
    var crNodeValue = this.getCurrentNodeText();
    var ix = Math.max(0, this.suggestionSourceStartIndex);
    //var ix = Math.max(0, this.index);
    return crNodeValue.substring(ix)
}

/**
 * Gets the value next value that should be used to lookup a suggestion.
 * @var nextval The value + the change that happened to it.
 */
AutosuggestUi.prototype.getNextSuggestionSourceText = function(nextval){
    var hasCurrentSuggestion = this.currentSuggestions.length > 0;
    var sugSrc = nextval.substr(this.suggestionSourceStartIndex);
    //Deal with cases where the suggestion source starts with a space, make it instead start with the next word.
    if(!hasCurrentSuggestion || true){
        sugSrc = sugSrc.trim()
    }
    console.log("Suggestion src: ", sugSrc)
    return sugSrc
}

/**
 * Sets the suggestion's text. If no text is given, the suggestion is hidden.
 * @param txt
 * @private
 */
AutosuggestUi.prototype.__setSuggestionText = function(txt){
    if(!txt || txt.length==0){
        this.suggestionHolder.hide();
        this.suggestionElement.removeClass('active');
        return;
    }
    console.log("Showing suggestion in node: ", this.currentTextNode);
    //In the current node, remove all br's so that our suggestion is on the same line
    var distNodes = this.currentTextNode.childNodes;
    for(var i=0; i<distNodes.length; i++){
        var n = distNodes[i]; if(n.tagName!=='BR') continue;  this.currentTextNode.removeChild(n);
    }    
    //console.log("Child nodes: ", distNodes);

    //Move the suggestion element to the current node
    this.suggestionHolder.text(txt);
    this.suggestionHolder.show()
    this.suggestionElement.addClass('active');
};
/**
 * Gets the text at the current node.
 * @returns {*}
 */
AutosuggestUi.prototype.getCurrentNodeText = function(){
    if(!this.currentTextNode){
        return "";
    }
    var text = div1d2text(this.currentTextNode);
    return text;
};
AutosuggestUi.prototype.getCurrentNodeIndex = function(){
    if(!this.currentTextNode){
        return 0;
    }
    var index = getContenteditableRow(this.searchBox.get(0), this.currentTextNode);
    return index;
}

AutosuggestUi.prototype.setCurrentNodeText = function(text){
    var dist = this.currentTextNode;
    if(dist.nodeType!==3){
        var items = dist.contents ? dist.contents() : Array.from(dist.childNodes);
        var texts = items.filter(function(f){ return f.nodeType==3});
        dist = texts[0];
    }else{

    }
    dist.nodeValue = text
};

AutosuggestUi.prototype.__createSuggestionBox = function(){
    var box = $("<span class='suggestion' contenteditable=\"false\">" +
        "<span class='suggestion-value'></span><span class='sugtip'>Tab<img src='/images/tab.png'></span></span>");
    this.suggestionElement = box;
    this.suggestionHolder = this.suggestionElement.find('.suggestion-value');
    
    //Get the current selected node
    this.searchBox.append(this.suggestionElement);
    //this.suggestionElement.show();
    return box;
};

AutosuggestUi.prototype.__getSuggestionLeft = function(suggestion, currentTextValue){
    var myVal = currentTextValue; 
    var len = Math.min(myVal.length, suggestion.length);
    var output = suggestion;
    for(var i=0; i<len; i++){
        var a = myVal[i];
        var b = suggestion[i];
        //make em lowercase
        a = a.toLowerCase();
        b = b.toLowerCase();
        if(a!==b){
            break;
        }else{
            output = output.substr(1)
        }
    }
    return output;
};

/**
 * Sets the node on which the suggestion is attached.
 */
AutosuggestUi.prototype.__setSuggestionNode = function(node){
    var dist = node;
    if(node.nodeType==3) { // Text node
        dist = dist.parentNode; //Use the parent div
    }else if(node.nodeType==1){ //Div node
        dist = node;
    }
    this.currentTextNode = dist;
    var currentNodeIndex = this.getCurrentNodeIndex()
    var textNode = Array.from(this.currentTextNode.childNodes).filter(function(f){ return f.nodeType == 3})[0];
    if(currentNodeIndex==0){
        if(textNode){
            insertAfter(this.suggestionElement.get(0), textNode)
        }else{
            this.suggestionElement.appendTo(dist)
        }
    }else{
        this.suggestionElement.appendTo(this.currentTextNode);
    }
    //console.warn("Attached suggestion to: ", this.currentTextNode, textNode, currentNodeIndex)
    //this.suggestionElement.appendTo(this.currentTextNode);
};

AutosuggestUi.prototype.val = function(a){
    if(typeof a !== 'undefined'){
        throw 'Not supported!';
    }
    var nodesText = div2text(this.searchBox);
    //var text = node ? node.nodeValue: '';
    return nodesText || ""
};

AutosuggestUi.prototype._setval = function(v){
    this.input.val(v);
    this.onValueChange(this)
};

AutosuggestUi.prototype.completeSuggestion = function(toValue, cursor){
    if(toValue.length==0){
        return
    }
    //debugger;
    var totalVal = this.val();
    var currentNode = this.currentTextNode;
    var currentNodeVal = this.getCurrentNodeText();
    var currentNodeIndex = this.getCurrentNodeIndex();

    //Trim the value to everything before the start of the suggestion
    totalVal = totalVal.substr(0, this.suggestionSourceStartIndex); // currentSuggestionStartIndex
    currentNodeVal = currentNodeVal.substr(0, this.suggestionSourceStartIndex);

    //Append the value of the suggestion
    totalVal += toValue;
    currentNodeVal += toValue;

    this._setval(totalVal);
    //this.val(totalVal);
    this.setCurrentNodeText(currentNodeVal);
    var elem = this.searchBox.get(0);
    setCaretPosition(elem, currentNodeIndex + 1); //Go to the end of the div for input
    this.currentSuggestions = [];
    this.__setSuggestionText("");
    //this.index = totalVal.length; // Update the index to the end of the suggestion
    //this.setSuggestionSourceStartIndex(this.index); // Move the suggestion source start after the end of the current value
    console.log("Completing at index: ", cursor.offset);
    this.setSuggestionSourceStartIndex(cursor.offset); // Move the suggestion source start after the end of the current value
};

AutosuggestUi.prototype.cancelSuggestion = function(){
    this.currentSuggestions = [];
    this.__setSuggestionText("")
};


AutosuggestUi.prototype.__handleDeletions = function(){
    //Make sure the suggestion box is still there and not deleted by ctrl-a + del
    var active = this.currentNode;
    console.log("Active: ", active);
    var isAttached = this.suggestionHolder.parents('html').length > 0;
    if(!isAttached){
        this.__createSuggestionBox()
    }
};

AutosuggestUi.prototype.__handleEmptyInput = function(){
    this.__deleteAll();
    this.cancelSuggestion();
};

AutosuggestUi.prototype.__deleteAll = function(){
    this.setSuggestionSourceStartIndex(0);
    //this.currentSuggestionStartIndex = 0;
    //this.index = 0;
    this.currentSuggestions = [];
    console.log("rekt")
}

//Set the selection
AutosuggestUi.prototype.setKeySelection = function(start, end, mod){
    this.keySelection = {
        start: start,
        end: end,
        mod: mod
    }
};

/**
 * Store the current value in the input and update the index at which we're at.
 */
AutosuggestUi.prototype.handleSearchbarKeyUp = function(e){
    var crval = this.val();
    //this.index = crval.length-1; // .length -1 since this is the location where the last char starts from
    this._setval(crval);
};

AutosuggestUi.prototype.handleSearchbarClick = function(){
    ensureFirstLineIsDiv(this.searchBox.get(0))
    var cursor = getCaretCharacterOffsetWithin(this.searchBox.get(0));
    this.__setSuggestionNode(cursor.node);
    var currentNodeText = this.getCurrentNodeText();
    var currentValue = new TextBuff(currentNodeText);
    currentValue.setCursor(cursor);
    var suggestionSrc = this.getNextSuggestionSourceText(currentValue.text);
    this.setSuggestionSourceStartIndex(currentNodeText.length);
    ensureLineWrap(this.searchBox.get(0), cursor)
    if(currentValue.cursorIsAtEnd()){

    }else{
        this._showSuggestions(this.currentSuggestions, cursor, suggestionSrc, currentValue.text)
    }
    console.log(cursor);
};

/**
 * Invoked on key down
 * @param e
 * @returns {boolean}
 */
AutosuggestUi.prototype.handleSearchbarKeyDown = function(e){
    ///Handle keys that complethe the whole suggestion
    var totalCurrentValue = new TextBuff(this.val());
    var currentNodeValue = new TextBuff(this.getCurrentNodeText());
    var cursor = getCaretCharacterOffsetWithin(this.searchBox.get(0), e.keyCode===8);
    var trimmedCursor = trimCursorOffset(cursor);
    //var nextValue = this.val();
    totalCurrentValue.setCursor(cursor);
    currentNodeValue.setCursor(trimmedCursor);
    ensureLineWrap(this.searchBox.get(0), cursor)

    this.__setSuggestionNode(cursor.node);
    console.log("kdown: ", e.keyCode);
    var hasSuggestions = this.currentSuggestions.length > 0;
    //Completion key pressed, while suggestions present
    if ( this._completeKeys.indexOf( e.keyCode ) !== -1 && hasSuggestions) { // Completion keys pressed
        var bestSuggestion = this.currentSuggestions.length > 0 ? this.currentSuggestions[0] : '';
        if(bestSuggestion.length==0) return;
        this.completeSuggestion(bestSuggestion, trimmedCursor);
        return false;
    } //Escape key pressed
    else if(e.keyCode === 27) {
        this.cancelSuggestion();
        return false;
    } //Backspace
    else if(e.keyCode===8 && !e.ctrlKey){
        //Backspace
        totalCurrentValue.backspace();
        currentNodeValue.backspace()
    } //
    else if(e.keyCode==32){ //Space
        //nextValue += " ";
        totalCurrentValue.add(' ')
        currentNodeValue.add(' ')
    }else if(e.keyCode==9){
        //nextValue += "\t";
        return;
    } //Letter and control key
    else if(isletter(e) && e.ctrlKey){ // Modifier + key
        // Select all
        if(e.keyCode == 65 && e.ctrlKey){ // Ctrl + A was pressed
            this.setKeySelection(0,0, 'all');
        }
        return;
    } // DEL
    else if(e.keyCode==46){ // DEL was pressed
        console.log(this.keySelection);
        if(this.keySelection.mod == 'all'){
            this.__deleteAll();
            this.__setSuggestionText("");
            totalCurrentValue.clear();
            currentNodeValue.clear()
            this.setKeySelection(0,0,'none');
            return;
        }else{
            //currentValue.delete();
            if(totalCurrentValue.length()==0){
                this.__deleteAll();
                this.__setSuggestionText("");
                this.setKeySelection(0,0,'none');
            }
            return;
        }
    } //Not a letter
    else if(e.keyCode==13){
        this.__setSuggestionText("");
        this.setSuggestionSourceStartIndex(0); //Reset it on new line
        this.currentSuggestions = []
        this.setKeySelection(0,0,'none');
        return;
    }
    else if(!isletter(e)){ // Ignore other keys
        this.setKeySelection(0,0,'none');
        return;
    } //A letter
    else{
        this.setKeySelection(0,0,'none');
        totalCurrentValue.add(e.key);
        currentNodeValue.add(e.key)
    }

    if(this.suggestionSourceStartIndex > currentNodeValue.length()-1){
        //this.suggestionSourceStartIndex = currentNodeValue.length()-1;
        this.setSuggestionSourceStartIndex(currentNodeValue.length()-1);
        console.log("Suggestion can't start after the current input + changes, resetting it to the end.")
    }
    if(this.suggestionSourceStartIndex<0) {
        console.log("Resetting suggestion source start index: ", this.suggestionSourceStartIndex, currentNodeValue.length());
        this.setSuggestionSourceStartIndex(0);
    }
    //Substring the text that's needed for the suggestion
    var suggestionSrc = this.getNextSuggestionSourceText(currentNodeValue.text);
    
    if(!currentNodeValue.text || !suggestionSrc){
        //Maybe add a placeholder for no input?
        this.__handleEmptyInput();
        return
    }

    //Make suggestions only for new text.
    if(currentNodeValue.cursorIsAtEnd()){
        //Make new suggestions
        this.engine.suggest(suggestionSrc)
            .then((function(suggestions){
                console.log("Suggestions: ", suggestions, "Index: ", trimmedCursor.col, this.suggestionSourceStartIndex);
                this.currentSuggestions = suggestions;
                this._showSuggestions(suggestions, trimmedCursor, suggestionSrc, currentNodeValue.text)
            }).bind(this))
            .catch(function(e){
                console.warn(e)
            });
    }else{
        this._showSuggestions(this.currentSuggestions, trimmedCursor, suggestionSrc, currentNodeValue.text)
    }

};


/**
 * Text buffer helper class
 * @param initialValue
 * @constructor
 */
function TextBuff(initialValue){
    this.text = initialValue || "";
    this.text = decodeHtmlchars(this.text);
    this.cursor = {line: 0, col: 0, offset: 0};
}
/**
 *
 * @param c {{col: number, offset: number, line: number}}
 */
TextBuff.prototype.setCursor = function(c){
    //console.log("Cursor: ", c);
    this.cursor = c;
};
TextBuff.prototype.backspace = function(){
    var offset = this.cursor.offset;
    offset--;
    this.cursor.offset = offset;
    this.text = spliceSlice(this.text, offset, 1)
    //nextValue = nextValue.substring(0, nextValue.length-1)
};
TextBuff.prototype.delete = function(){
    var offset = this.cursor.offset;
    //offset--;
    this.cursor.offset = offset;
    this.text = spliceSlice(this.text, offset, 1)
    //nextValue = nextValue.substring(0, nextValue.length-1)
};
TextBuff.prototype.add = function(text){
    var offset = this.cursor.offset;
    this.text = spliceSlice(this.text, offset, 0, text)
};
TextBuff.prototype.cursorIsAtEnd = function(){
    var len = this.text.length;
    //console.warn("@end: ", this.cursor.offset, len)
    return this.cursor.offset == (len-1);
};
TextBuff.prototype.length = function(){
    return this.text.length;
};
TextBuff.prototype.clear = function(){
    this.cursor = {line: 0, col: 0, offset: 0};
    this.text = '';
};
function backcopy(b){
    var col = b.col;
    var line = b.line;
    var node = b.node;
    var offset = b.offset;
    col = col-1;
    return {
        col: Math.max(0, col),
        line: line,
        node: node,
        offset: offset
    }
}


function cwidth(refElement){
    var fontSize = getStyle(refElement, 'font-size');
    var testel = document.createElement("div")
    testel.innerText = "T";
    //testel.setAttribute('readonly', '');
    testel.style.position = 'absolute';
    testel.style.display = 'inline-block';
    //testel.style.left = '-9999px';
    testel.style.fontSize = fontSize;
    document.body.appendChild(testel);
    var w = (testel.clientWidth-1);
    document.body.removeChild(testel);
    return w;    
}