var BINGSPEECH_API_KEY = "eff8ab5d8e034c9e96bb972c7f490549";
var BINGSPEECH_LANGUAGE = "en-GB";
var img_id = "";
var byname = false;
var BINGSPEECH_RECOGNITION_MODE = "Dictation"; // Interactive  or Conversation
var BINGSPEECH_FORMAT = "Simple" ; //Detailed other one
var currentinput = null;
var SDK;
var recognizer;
$( document ).ready(function() {
    $('ul.nav-tabs li.nav-item').click(function(e){
        stopspeech();
    });
    Initialize(function (speechSdk) {
        SDK = speechSdk;
        
    });
   // Setup();
});
function setText(text) {
    if(byname){
        document.getElementsByName(currentinput).value += text + " " ;
    }
    else
    {
        document.getElementById(currentinput).value += text + " " ;
    }
    
}
function recordspeech(input,imgid) {
   
    img_id = imgid;
   // speechActivity = document.getElementById(imgid);
    currentinput = input;
   // document.getElementById("startBtn").disabled = true;
    //document.getElementById("stopBtn").disabled = false;
   // if (!recognizer) {
       
        Setup();
   // }
    RecognizerStart(SDK, recognizer);
}
function recordspeechbn(input,imgid) {
    byname = true;
    img_id = imgid;
   // speechActivity = document.getElementById(imgid);
    currentinput = input;
   // document.getElementById("startBtn").disabled = true;
    //document.getElementById("stopBtn").disabled = false;
    if (!recognizer) {
       
        Setup();
    }
    RecognizerStart(SDK, recognizer);
}
function stopspeech() {
    //document.getElementById("startBtn").disabled = false;
    //document.getElementById("stopBtn").disabled = true;
    RecognizerStop(SDK, recognizer);
    OnSpeechEndDetected();
}

function Initialize(onComplete) {
    if (!!window.SDK) {
       
        onComplete(window.SDK);
    }
}

// Setup the recognizer
function RecognizerSetup(SDK, recognitionMode, language, format, BINGSPEECH_API_KEY) {
    
    switch (recognitionMode) {
        case "Interactive" :
            recognitionMode = SDK.RecognitionMode.Interactive;    
            break;
        case "Conversation" :
            recognitionMode = SDK.RecognitionMode.Conversation;    
            break;
        case "Dictation" :
            recognitionMode = SDK.RecognitionMode.Dictation;    
            break;
        default:
            recognitionMode = SDK.RecognitionMode.Interactive;
    }

    var recognizerConfig = new SDK.RecognizerConfig(
        new SDK.SpeechConfig(
            new SDK.Context(
                new SDK.OS(navigator.userAgent, "Browser", null),
                new SDK.Device("SpeechSample", "SpeechSample", "1.0.00000"))),
        recognitionMode,
        language, // Supported languages are specific to each recognition mode. Refer to docs.
        format); // SDK.SpeechResultFormat.Simple (Options - Simple/Detailed)


    var useTokenAuth = false;
    
    var authentication = function() {
        if (!useTokenAuth)
            return new SDK.CognitiveSubscriptionKeyAuthentication(BINGSPEECH_API_KEY);

        var callback = function() {
            var tokenDeferral = new SDK.Deferred();
            try {
                var xhr = new(XMLHttpRequest || ActiveXObject)('MSXML2.XMLHTTP.3.0');
                xhr.open('GET', '/token', 1);
                xhr.onload = function () {
                    if (xhr.status === 200)  {
                        tokenDeferral.Resolve(xhr.responseText);
                    } else {
                        tokenDeferral.Reject('Issue token request failed.');
                    }
                };
                xhr.send();
            } catch (e) {
                window.console && console.log(e);
                tokenDeferral.Reject(e.message);
            }
            return tokenDeferral.Promise();
        }

        return new SDK.CognitiveTokenAuthentication(callback, callback);
    }();
    
    
        return SDK.CreateRecognizer(recognizerConfig, authentication);
   
}

// Start the recognition
function RecognizerStart(SDK, recognizer) {
    recognizer.Recognize((event) => {
        /*
         Alternative syntax for typescript devs.
         if (event instanceof SDK.RecognitionTriggeredEvent)
        */
        switch (event.Name) {
            case "RecognitionTriggeredEvent" :
                //UpdateStatus("Initializing");
                break;
            case "ListeningStartedEvent" :
                //UpdateStatus("Listening");
               
                break;
            case "RecognitionStartedEvent" :
                OnSpeechBeginDetected();
                //UpdateStatus("Listening_Recognizing");
                break;
            case "SpeechStartDetectedEvent" :
               // UpdateStatus("Listening_DetectedSpeech_Recognizing");
                //console.log(JSON.stringify(event.Result)); // check console for other information in result
                break;
            case "SpeechHypothesisEvent" :
               // UpdateRecognizedHypothesis(event.Result.Text, false);
               // console.log(JSON.stringify(event.Result)); // check console for other information in result
                break;
            case "SpeechFragmentEvent" :
                //UpdateRecognizedHypothesis(event.Result.Text, true);
                //console.log(JSON.stringify(event.Result)); // check console for other information in result
                break;
            case "SpeechEndDetectedEvent" :
                OnSpeechEndDetected();
                //UpdateStatus("Processing_Adding_Final_Touches");
               // console.log(JSON.stringify(event.Result)); // check console for other information in result
                break;
            case "SpeechSimplePhraseEvent" :
                UpdateRecognizedPhrase(JSON.stringify(event.Result, null, 3));
                break;
            case "SpeechDetailedPhraseEvent" :
                UpdateRecognizedPhrase(JSON.stringify(event.Result, null, 3));
                break;
            case "RecognitionEndedEvent" :
                OnComplete();
               // UpdateStatus("Idle");
              //  console.log(JSON.stringify(event)); // Debug information
                break;
            default:
               // console.log(JSON.stringify(event)); // Debug information
        }
    })
    .On(() => {
        // The request succeeded. Nothing to do here.
    },
    (error) => {
        console.error(error);
    });
}

// Stop the Recognition.
function RecognizerStop(SDK, recognizer) {
    // recognizer.AudioSource.Detach(audioNodeId) can be also used here. (audioNodeId is part of ListeningStartedEvent)
    recognizer.AudioSource.TurnOff();
}
function Setup() {
    if (recognizer != null) {
        RecognizerStop(SDK, recognizer);
    }
    recognizer = RecognizerSetup(SDK, BINGSPEECH_RECOGNITION_MODE, BINGSPEECH_LANGUAGE, SDK.SpeechResultFormat[BINGSPEECH_FORMAT], BINGSPEECH_API_KEY);
}

function UpdateStatus(status) {
    statusDiv.innerHTML = status;
}


function OnSpeechEndDetected() {
    $("#" + img_id).addClass("hidden");
}
function OnSpeechBeginDetected() {
    $("#" + img_id).removeClass("hidden");
}
function UpdateRecognizedPhrase(json) {
    var jobj = JSON.parse(json);
    if(jobj.DisplayText != undefined)
    {
        setText(jobj.DisplayText);
    }
}

function OnComplete() {
   
}
