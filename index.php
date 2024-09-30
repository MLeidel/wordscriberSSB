<?php
/*
TABLE "documents"
	"category"
	"name"
	"document"
*/
extract($_POST);
extract($_GET);

if (!isset($cat)) {
  $cat = $_COOKIE["WScat"];
}
     // // // // // // //
    //  Options Save  //
   // // // // // // //

if (isset($options)) {  // save options : font, size, color, height of CE
  $r = file_put_contents( "options.dat", $otf . "\n" . $ots . "\n"
                                              . $otc . "\n" . $oth . "\n"
                                              . $olh . "\n" . $obg . "\n" );
  if (!$r) {
    echo "save options failed";
    exit;
  }
}

// get save options : font, size, color, height of CE
$Opts = file("options.dat");
$Opts = array_map('trim', $Opts);

// build document options list
sleep(1); // handler.php may need a little time
$flist = "";
$lastrow = "";
$db = new SQLite3('iDoc.db');
$sql = "SELECT * FROM documents WHERE category = '$cat' ORDER by name COLLATE NOCASE";
$results = $db->query($sql) or die($sql);
while ($row = $results->fetchArray()) {
  if ($row['name'] == $lastrow) {
    continue;
  }
  $flist .= "<option value='$row[name]'>" . $row['name'] . "</option>\n";
  $lastrow = $row['name'];
}

///////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset='UTF-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
	<link rel="manifest" href="manifest.json">
	<title>wordScriber</title>
	<script type="text/javascript" src="myJS-1.2.min.js"></script>
  <script src="ulcase.min.js"></script>
  <script src="JSmodal.min.js"></script>
  <link rel="stylesheet" href="JSmodalani.css">

<style>
  body {
    margin:10px;
    background-color: <?php echo $Opts[5]?>;
  }
  h1 {
    font-family: 'Arial';
    margin-bottom: 2px;
  }
  .imgbutton:hover {
    background-color: white;
  }
  .imgbutton {
    vertical-align: middle;
    width: 28px;
    height: 28px;
    cursor: pointer;
    margin-bottom: 8px;
    transition: background-color 0.6s;
  }
  #CE {
  	height: <?php echo $Opts[3]?>;
  	background: #fff;
  	color: <?php echo $Opts[2]?>;
  	font: normal <?php echo $Opts[1], " $Opts[0]"?>;
  	padding: 5px;
  	/*width: 95%;*/
  	border: 1px solid navy;
  	line-height: <?php echo $Opts[4]?>;
  	overflow: auto;

  }
  textarea {
  	height: <?php echo $Opts[3]?>; /*Note: must be same as #CE*/
  	width: 98%;
  	background: #111;
  	color: lightgreen;
  	font: normal 10pt monospace;
  	tab-size: 4;
  }
  input[type=date] {
    margin-bottom:5px;
    font: bold 12pt 'Arial';
  }
  input[type=submit] {
    border-radius: 5px;
    background: black;
    color: white;
    /*font: bold 12pt 'Arial';*/
    margin-top:5px;
  }
  #savbtn {
    width: 60px;
    height: 40px;
    font: bold 12pt 'Arial';
  }
  #MSG {
    color: DarkRed;
    font-weight: bold;
  }
  #SAV {
    color: DarkGreen;
    font: bold 10pt "sans-serif";
  }
  /* style for options display */
  #OPTS {
    border: thick solid gray;
    padding: 6px;
    background: white;

  }
  input[type=text] {
    border-width: 0;
    padding: 2px;
  }

  .big-text {
    font-size: larger;
  }
  .small-text {
    font-size: smaller;
  }
  .highlight {
      background-color: yellow;
  }
  label {
    font: normal 11pt Helvetica;
    margin-bottom:12px;
  }
</style>

<script>
/*
    Event Listeners
*/

  // Toggles Spellcheck
  document.addEventListener('keydown', function(event) {
    if (event.altKey && event.key === '/') {
        event.preventDefault();
        spellCheck();
    }
  });

  // Insert Horizontal Rule <hr>
  document.addEventListener('keydown', function(event) {
      if (event.altKey && event.key === 'r') {
            event.preventDefault();
            document.execCommand("insertHorizontalRule", false, null);
          }
      });

  // print
  document.addEventListener('keydown', function(event) {
      if (event.altKey && event.key === 'p') {
            event.preventDefault();
            printPage();
          }
      });

  // Insert table
  document.addEventListener('keydown', function(event) {
      if (event.altKey && event.key === 't') {
            event.preventDefault();
            generateTableHTML();
          }
      });

  // Insert fieldset
  document.addEventListener('keydown', function(event) {
      if (event.altKey && event.key === 'f') {
            event.preventDefault();
            let htmltext = `<fieldset style="display:inline;">\n<legend>legend</legend>\nbody<br><br><br><br>\n</fieldset>`
            insertHTML(htmltext);
          }
      });

  // Remove formatting on selected HTML
  document.addEventListener('keydown', function(event) {
      if (event.altKey && event.key === ';') {
            event.preventDefault();
            document.execCommand("removeFormat", false, null);
          }
      });

  // Toggles the Options panel on the page
  document.addEventListener('keydown', function(event) {
      if ((event.ctrlKey && event.altKey)) {
            event.preventDefault();
            JS.tod("#OPTS", "block");
          }
      });

  // Save current document
  document.addEventListener('keydown', function(event) {
      if ((event.ctrlKey || event.metaKey) && event.key === 's') {
          event.preventDefault();
          saveText();
      }
    });

  // Launch Emoji Window
  document.addEventListener('keydown', function(event) {
      if ((event.altKey || event.metaKey) && event.key === 'e') {
          event.preventDefault();
          window.open("https://michaelleidel.net/Emoji","Emoji","height=500, width=500");
      }
    });

  window.addEventListener('beforeunload', function (event) {
    if (nSave === 2) {
      const confirmationMessage = "Leave this page?";
      event.returnValue = confirmationMessage;
      return confirmationMessage;
    }
  });

  document.addEventListener('keydown', function(event) {
      if ((event.ctrlKey || event.metaKey) && event.key === 'h') {
          event.preventDefault();
          showHelp();
      }
    }
  );

  document.addEventListener('keydown', function(event) {
      if ((event.ctrlKey || event.metaKey) && event.key === 'Escape') {
          event.preventDefault();
          findText(false);
      }
    }
  );

document.addEventListener('keydown', function(event) {  // page-break
  if (event.altKey && event.key === 'b') {
      event.preventDefault();
      pageBreak();
  }
});

document.addEventListener('keydown', function(event) {  // switch Def/Fin
  if (event.altKey && event.key === 's') {
      event.preventDefault();
      //debugger;
      const radioF = JS.doq("#RF");
      const radioD = JS.doq("#RD");
      // Toggle the checked state
      if (radioF.checked) {
          radioF.checked = false;
          radioD.checked = true;
      } else {
          radioF.checked = true;
          radioD.checked = false;
      }
  }
});


// GLOBAL VARS for control button functions
  var last_color = "red";
  var last_font = "Courier New, 3"
  var nSave = 1; // 1=Saved 2=Not Saved


  document.addEventListener('keydown', function(event) {
    // set 'Not Saved' on keydowns
    if (event.ctrlKey) return;
    if (nSave === 1) {
      setmsg();
      nSave = 2;
    }
  });

  document.addEventListener('keydown', function(event) {
    if (event.key === 'Tab') {
      if (event.shiftKey) {
        event.preventDefault();
        document.execCommand('outdent', false, '')
      } else {
        event.preventDefault();
        document.execCommand('indent', false, '')
      }
    }
  });

document.addEventListener('keydown', function(event) {  // page-break
  if (event.ctrlKey && event.key === 'd') {
      event.preventDefault();
      insertHTML(JS.getMDY());
  }
});

</script>
</head>
<!-- onload reset the select options index -->
<!-- onunload store the current document info -->
<body onload="init_wordScriber()"
      onunload="storeLastDoc()">

<h1 style="color: #cde;text-align:right;margin:1px">wordScriber&nbsp;&nbsp;</h1>
<!-- Put the pop-up Options window under the heading -->
<div id="OPTS" style="display:none;">
  <form name="fopt" method="post">
  <h3 style="margin: 0">Options</h3>
  <label for="otf">Text Font: </label>
  <input id="otf" type="text" name="otf" placeholder="Arial" value="<?php echo $Opts[0] ?>" /><br>
  <label for="oTS">Text Size: </label>
  <input id="oTS" type="text" size=1 name="ots" placeholder="11pt" value="<?php echo $Opts[1] ?>" /><br>
  <label for="oTC">Text Color: </label>
  <input id="oTC" type="text" size=1 name="otc" placeholder="#111" value="<?php echo $Opts[2] ?>" /><br>
  <label for="oTH">Document Height: </label>
  <input id="oTH" type="text" size=2 name="oth" placeholder="240px" value="<?php echo $Opts[3] ?>" /><br>
  <label for="oLH">Line Height: </label>
  <input id="oLH" type="text" size=2 name="olh" placeholder="100%" value="<?php echo $Opts[4] ?>" /><br>
  <label for="oBG">Background: </label>
  <input id="oBG" type="text" size=2 name="obg" placeholder="#abc" value="<?php echo $Opts[5] ?>" /><br>
  <input type="submit" name="options" value="Save">
  </form>
</div>
<!-- end Options hidden window code -->

<img src="KO.gif" id="LOAD" style="display:none;position:absolute;top:0;left:0;" />

<form id="FRM" method="post">
  <input type="hidden" id="ACT" name="action" /> <!--action for submit-->
  <input type="hidden" id="TXT" name="txt" /> <!--edited text into form field-->
  <!--<input id="CAL" style="float:left"-->
  <!--    name="caldate" type="date" onchange="date_changed()" />&nbsp;&nbsp;&nbsp;-->
  <select name="list" id="SEL"
          onchange="load_document(this.options[this.selectedIndex].text)">
  	<!-- php will write out the options -->
  	<option disabled>Select Document</option>
    <?php echo $flist ?>
  </select>
  <input type="text" name="docName" id="DOC">

  <div>&nbsp;
  	<input type="radio" name="cat" id="RD" value="default"
  	                    onclick="window.location = '?cat=d'; JS.tod('#LOAD', 'block');"
  	                    title="Default" checked><label for="RD" title="Category">Default</label>&nbsp;
  	<input type="radio" name="cat" id="RF" value="final"
  	                    onclick="window.location = '?cat=f'; JS.tod('#LOAD', 'block');"
  	                    title="Final"><label for="RF" title="Category">Final</label>&nbsp;&nbsp;&nbsp;
  	<input type="checkbox" name="cbdel" id="CBD" value="delete"
  	                    title="Delete"><label for="CBD" title="check and Save to delete">Delete</label>
    <span id="SAV" style=""></span>
    <span id="MSG" style=""></span><br>
  </div>
  <textarea id="TA" style="display:none;" spellcheck=false></textarea>
  <div id="CE" contenteditable="true" onfocus="setmsg()" spellcheck=false></div>

  <br>
  <center> <!-- control & format buttons -->

  <img class="imgbutton" src="icons/save.png" title="Save (Ctrl-s)"
      onclick="saveText()" />&nbsp;
  <img class="imgbutton" src="icons/html.png"
      onclick="toggleHtml()" title="toggle html" />&nbsp;
  <img class="imgbutton" src="icons/bold.png" title="Bold Text (Ctrl-b)"
      onclick="document.execCommand('bold', false, '')" />&nbsp;
  <img class="imgbutton" src="icons/italic.png" title="italic (Ctrl-i)"
      onclick="document.execCommand('italic', false, '')" />&nbsp;
  <img class="imgbutton" src="icons/underline.png" title="underline (Ctrl-u)"
      onclick="document.execCommand('underline', false, '')" />&nbsp
    <img class="imgbutton" src="icons/upper.png" title="uppercase (Alt-u)"
      onclick="convertToUppercase()" />&nbsp;
    <img class="imgbutton" src="icons/lower.png" title="lowercase (Alt-l)"
      onclick="convertToLowercase()" />&nbsp;
  <img class="imgbutton" src="icons/link_small.png"
      onclick="doLink()" title="Make Link" />&nbsp;
  <img class="imgbutton" src="icons/fonts.png"
      onclick="doFont()" title="text font" />&nbsp;
  <img class="imgbutton" src="icons/textcolor.png"
      onclick="doColor()" title="text color" />&nbsp;
  <img class="imgbutton" src="icons/refresh.png"
      onclick="location.reload()" title="reload page" />&nbsp;
  <br>
    <img class="imgbutton" src="icons/image.png" title="Insert Image"
      onclick="doImage()" />&nbsp;
    <img class="imgbutton" src="icons/highlighter.png" title="Hilite Color"
      onclick="doHilite()" />&nbsp;
    <img class="imgbutton" src="icons/horizontal_rule.png" title="Horizontal Rule (Alt-r)"
      onclick="document.execCommand('insertHorizontalRule', false, '')" />&nbsp;
    <img class="imgbutton" src="icons/lists.png" title="List"
      onclick="document.execCommand('insertUnorderedList', false, '')" />&nbsp;
    <img class="imgbutton" src="icons/format_list_numbered.png" title="Numbered List"
      onclick="document.execCommand('insertOrderedList', false, '')" />&nbsp;
    <img class="imgbutton" src="icons/format_align_left.png" title="Justify Left"
      onclick="document.execCommand('justifyLeft', false, '')" />&nbsp;
    <img class="imgbutton" src="icons/format_align_center.png" title="Justify Center"
      onclick="document.execCommand('justifyCenter', false, '')" />&nbsp;
    <img class="imgbutton" src="icons/format_align_right.png" title="Justify Right"
      onclick="document.execCommand('justifyRight', false, '')" />&nbsp;
    <img class="imgbutton" src="icons/unformat.png" title="Remove Format"
      onclick="document.execCommand('removeFormat', false, '')" />&nbsp;
    <img class="imgbutton" src="icons/help.png" title="Help (Ctrl-h)"
      onclick="showHelp()" />&nbsp;
    <img class="imgbutton" src="icons/page_break.png" title="Page Break (Alt-b)"
      onclick="pageBreak()" />&nbsp;
  <br>
    <img class="imgbutton" src="icons/files.png" title="manage image files"
      onclick="window.open('fm.php', '_blank', 'width=560,height=520')" />&nbsp;
    <img class="imgbutton" src="icons/big.png" title="bigger text"
      onclick="changeText('big-text')" />&nbsp;
    <img class="imgbutton" src="icons/small.png" title="smaller text"
      onclick="changeText('small-text')" />&nbsp;
    <img class="imgbutton" src="icons/h1.png" title="heading size"
      onclick="document.execCommand('formatBlock', false, '<h1>')" />&nbsp;
    <img class="imgbutton" src="icons/h2.png" title="heading size"
      onclick="document.execCommand('formatBlock', false, '<h2>')" />&nbsp;
    <img class="imgbutton" src="icons/h3.png" title="heading size"
      onclick="document.execCommand('formatBlock', false, '<h3>')" />&nbsp;
    <img class="imgbutton" src="icons/table.png" title="insert Table"
      onclick="generateTableHTML()" />&nbsp;
    <img class="imgbutton" src="icons/fieldset.png" title="insert Fieldset"
      onclick="generateFieldSet()" />&nbsp;
    <img class="imgbutton" src="icons/spell.png" title="Toggle Spell Check (Alt-/)"
      onclick="spellCheck()" />&nbsp;
    <img class="imgbutton" src="icons/settings.png" title="Settings (Ctrl-Alt)"
      onclick="JS.tod('#OPTS', 'block');" />&nbsp;
    <img class="imgbutton" src="icons/printer.png" title="Print (Alt-p)"
      onclick="printPage()" />&nbsp;

</form>
  </center> <!-- end control buttons -->

<!-- ########################## S C R I P  C O D E #########################  -->
<script>

/*
loStor function:
op = 1:	set localStorage value by key
op = 2: get localStorage value by key
op = 3: not used here
----------
Used here to store the CURRENT document key
to reload document and set category
after a refresh (needed to update the select options)
skey => idoc
data => category|name
Will be preserved (idealy) between browser sessions.
*/
  function loStor(op, skey) {
  	switch ( op ) {
  		case 1:	// set
  			localStorage.setItem("idoc", skey);
  			return;
  			break;
  		case 2: // get
  			return localStorage.getItem("idoc");
  			break;
  	}
  }

  // reload previous document
  // reset the select options index to 0
  // whenever the browser is refreshed
  function init_wordScriber() {
//debugger;
    JS.doq("#SEL").selectedIndex = 0;
    skey = loStor(2, "");
    if (skey == null) return;
    cn = skey.split("|");
    let radios = document.getElementsByName('cat');
    if (cn[0] == 'd') {
      JS.doq("#RD").checked = true;
    } else {
      JS.doq("#RF").checked = true;
    }
    load_document(cn[1]);
  }

  function showHelp() {
          JSmodal.open(1,`<pre>
<b>Action Keys</b>
Alt-/   -  Toggle spellcheck on / off
Alt-;   -  Remove HTML formatting
Alt-b   -  Insert Page Break
Alt-e   -  Emojis Window
Alt-f   -  Insert HTML Fieldset
Alt-l   -  Lower case
Alt-p   -  Print
Alt-r   -  Insert Horizontal Rule
Alt-s   -  Toggle Default/Final
Alt-t   -  Insert HTML table
Alt-u   -  Upper Case
Ctrl-Alt-  Toggle Options panel
Ctrl-a  -  Select All
Ctrl-b  -  Bold
Ctrl-c  -  Copy to Clipboard
Ctrl-d  -  Insert mm/dd/yyyy date
Ctrl-f  -  Find Text
Ctrl-h  -  Help
Ctrl-i  -  Italic
Ctrl-s  -  Save the current note
Ctrl-u  -  Underline
Ctrl-v  -  Paste from Clipboard
Ctrl-w  -  Close this SSB window
Sft-Tab -  outdent
Tab     -  indent
<strong>Mac users</strong> may have problems with the 'Alt' keys,
and sometimes you have to use 'command' instead of
Ctrl. There are buttons for most 'Alt' keys.

<strong>HTML code:</strong>
  Ctrl-Enter - &lt;br&gt;
  Ctrl-Space - &amp;nbsp;
`);
  }

  // common async routing function
  function webresponse(n, text) {
    switch (n) {
      case 1:
        // text is this date's entry
        JS.htm("#CE", text);
        JS.doq("#SEL").selectedIndex = 0; // reset the listbox
        break;
      case 2:
        // future
        break;
    }
  }

  function isTextSelected() {
    const selection = window.getSelection();
    return !selection.isCollapsed;
  }
  function getSelectedText() {
    const selection = window.getSelection();
    // let oHTM = JS.doq("#CE");
    // const selection = oHTM.getSelection();
    return selection.toString();
  }

  function spellCheck() {
    let ce = JS.doq("#CE");
    // ce.spellcheck = !ce.spellcheck;
    if (ce.spellcheck) {
      ce.spellcheck = false;
      JS.htm("#MSG", "");
    } else {
      ce.spellcheck = true;
      JS.htm("#MSG", "&nbsp;&nbsp;☼")
    }
    ce.blur();
    ce.focus();
  }

  function printPage() {
    let txt = "";
  	let oHTM = JS.doq("#CE");
  	txt = oHTM.innerHTML;
    const printWindow = window.open('', '_blank', 'width=800,height=<?php echo $Opts[3]?>');
    printWindow.document.write(txt);
    printWindow.document.title = "WordScriber Printing Window";
  }

  function generateTableHTML() {
      let tp = prompt("Define table:\nrows,cols,border,space,padding", "2,2,1,0,4");
      if (tp == null) return;
      let prm = tp.split(",");

      let tableHTML = `<table border=${prm[2]} cellspacing=${prm[3]} cellpadding=${prm[4]}>\n`;

      for (let i = 0; i < prm[0]; i++) {
          tableHTML += '  <tr>\n';
          for (let j = 0; j < prm[1]; j++) {
              tableHTML += '    <td>cell' + '</td>\n';
          }
          tableHTML += '  </tr>\n';
      }

      tableHTML += '</table>';
      insertHTML(tableHTML);
  }

function generateFieldSet() {
  let htmltext = `<fieldset style="display:inline;">\n<legend>legend</legend>\nbody<br><br><br><br>\n</fieldset>`
  insertHTML(htmltext);
}

  /* so this kind of function will be useful
    for whenever the document.execCommand
    is acctually de-implemented from browsers.
  */
  function changeText(cname) {
    const selection = window.getSelection();
    if (selection.rangeCount > 0) {
      const range = selection.getRangeAt(0);
      const span = document.createElement('span');
      span.className = cname;  // .big-text, .small-text
      range.surroundContents(span);
    }
  }

  /* switches SAVED and NOT SAVED messages */
  function setsav() {
    JS.htm("#SAV", "&nbsp;&nbsp;S A V E D");
    // JS.htm("#MSG", "");
    nSave = 1;
  }
  function setmsg() {
    JS.htm("#SAV", "<font color='#be0848'>&nbsp;&nbsp;<i>Not Saved!</i></font>");
    // JS.htm("#MSG", "&nbsp;&nbsp;&nbsp;Not Saved !!");
    nSave = 2;
  }

  function doImage() {
    /* to insert a graphic use the insertHTML command ...
       NOTE: the graphic must have an Internet URL */
    url = prompt("Enter images/filename", "images/");
    if (url) insertHTML(`<img src='${url}' width=""  height=""  />`);
  }

  function doHilite() {
    color = prompt("Enter color for highlight", "yellow");
    if (color) document.execCommand('hiliteColor', false, color);
  }

	function doFont() {
	 // let fn = JS.val("#FNT");
	 // let sz = JS.val("#SIZ");
    fnts = `Suggested fonts to use: Sans, Arial, Helvetica,
    Verdana, Tahoma, Serif, Georgia,
    Times New Roman, Monospace, Courier New Size 1-6`;
	  let data = prompt(fnts, last_font);
	  if (data == null) return;
	  let m = data.split(",");
    if (m[0] == "" || m[1] == "") return;
	  document.execCommand("fontName",false, m[0]);
	  document.execCommand("fontSize",false, m[1]);
	  last_font = m[0] + ", " + m[1];
	}

	function doColor() {
	  let data = prompt("Enter color name for text", last_color);
	  if (data == null) return;
    if (data == "") return;
    document.execCommand("foreColor",false, data);
    last_color = data;
	}

	function doLink() {
	  let data = prompt("Enter URL to Link", "https://");
	  if (data == null) return;
    if (data == "") return;
    document.execCommand("createLink",false, data);
	}

  function xfer2Ta() {
    let oHTM = JS.doq("#CE");
  	JS.val("#TA", oHTM.innerHTML); // HTML ==> TEXT
  	JS.tod("#TA", "block");
  	JS.tod("#CE", "block");
  	JS.doq("#TA").focus();
  }

  function toggleHtml() {
  	// toggles between textarea (TA)
  	// and contentEditable (CE)
  	// copying text and toggling display
  	if (JS.gss("#TA", "display") == 'none') {
  		unCompress();
  	} else {
  	  let oHTM = JS.doq("#CE");
  		oHTM.innerHTML = JS.val("#TA"); // TEXT ==> HTML
  		JS.tod("#CE", "block");
  		JS.tod("#TA", "block");
  		oHTM.focus();
  	}
  }

  function fmthtml(t) {
    /* pseudo formats the HTML when switching between text and CE */
  	t = t.replace(/<div/g, "\n<div");
  	t = t.replace(/<br>/g, "<br>\n");
  	t = t.replace(/<\/div>/g, "\n</div>\n");
  	t = t.replace(/<\/blockquote>/g, "</blockquote>\n");
  	t = t.replace(/<blockquote/g, "\n<blockquote");
  	t = t.replace(/<\/span>/g, "</span>\n");
  	t = t.replace(/<span/g, "\n<span");
  	t = t.replace(/<ul/g, "\n<ul");
  	t = t.replace(/<ol/g, "\n<ol");
  	t = t.replace(/<\/ul>/g, "\n</ul>\n");
  	t = t.replace(/<\/ol>/g, "\n</ol>\n");
  	t = t.replace(/<li/g, "\n	<li");
    t = t.replace(/\n\n/g, "\n"); // clean up extra blank lines
    t = t.replace(/  /g, ""); // clean up extra spaces lines
    t = t.replace(/\t/g, ""); // clean up extra tabs
  	return t;
  }

  function unCompress() {
      let oHTM = JS.doq("#CE");
  		txt = oHTM.innerHTML;
  		txt = fmthtml(txt);
  		oHTM.innerHTML = txt;
  		xfer2Ta();
  }

  function getCategory() {
    // get current selected category d or f
    const checkedRadio = JS.doq('input[name="cat"]:checked');
    if (checkedRadio) {
        const value = checkedRadio.value;
        if (value == "default") return "d";
        else return "f";
    }
  }

  function isDelete() {
     const checkDelete = JS.doq("#CBD").checked;
     if (checkDelete) return true;
     return false;
  }

  function newDocument(name) {
    selectElement = JS.doq("#SEL");
    for (let i = 0; i < selectElement.options.length; i++) {
        if (selectElement.options[i].value === name) {
            return false;
            break;
        }
    }
    return true;
  }

  function saveText() {
    /*  Handles save existing, save new, and delete documents. */
    let cat = getCategory();
    let dname = JS.val("#DOC");
    // open in browser in HTML mode
    if (JS.gss("#TA", "display") !== 'none') {
    	let oHTM = JS.doq("#CE");
    	let txt = oHTM.innerHTML;
  		h = window.open();
  		h.document.write(txt);
  	} else { // save to database
      // Delete checked?
      if (isDelete()) {
        if (!confirm("Delete this document?\n" + dname + " " + cat)) {
          return;
        }
        JS.websend("handler.php", `cat=${cat}&nam=${dname}&action=delete`);
        JS.val("#DOC", "");
        window.location.reload();
        return;
      }
      // Else continue with SAVE...
    	let oHTM = JS.doq("#CE");
    	let txt = oHTM.innerHTML;
    	txt = encodeURIComponent(txt);
    	JS.websend("handler.php", `cat=${cat}&nam=${dname}&txt=${txt}&action=save`);
    	// console.log(cat, dname, txt);
    	setsav();
    	loStor(1, cat + "|" + dname); // store current key for new docs
    	if (newDocument(dname)) {
    	  window.location.reload();
    	}
  	}
  }

  function load_document(dname) {
    // check for "Not Saved!"
    if (nSave === 2) {
       if (!confirm("Document Not Saved\nContinue?")) {
          JS.doq("#SEL").selectedIndex = 0;
          return;
       }
    }
    let cat = ""
    let radios = document.getElementsByName('cat');
    if (radios[0].checked) {
      cat = "d";
    } else {
      cat = "f";
    }
    JS.val("#DOC", dname);
    //console.log("loaded => cat:",cat,"name: ",dname);
    JS.webpost("handler.php", 1, `cat=${cat}&nam=${dname}&act=read`);
  }

  function pageBreak() {
    insertHTML("\n<div style='page-break-before: always;'></div>");
    alert(`Page Break Here.\n<div style='page-break-before: always;'></div>\nwas inserted.
    delete this HTML code to remove Page Break.`);
  }

  function storeLastDoc() {
    // called at onUnload
    let cat = getCategory();
    let dname = JS.val("#DOC");
    loStor(1, cat + "|" + dname); // store current key
    JS.setCookie("WScat", cat, 365);
  }

  function findText(searchText) {
    const CE = JS.doq("#CE");
    const content = CE.innerHTML;

    // Remove existing highlights or clear if !searchText
    CE.innerHTML = content.replace(/<span class="highlight">([^<]+)<\/span>/gi, '$1');
    if (!searchText) {
        return;
    }

    const regex = new RegExp(`(${searchText})`, 'gi');
    const newContent = CE.innerHTML.replace(regex, '<span class="highlight">$1</span>');

    CE.innerHTML = newContent;
  }

/*  insertHTML is a replacement for the execCommand("insertHTML").
    It's included here as a suggestion to rewrite all of the execCommand()
    methods into strict Javascript using DOM manipulation functions. This
    becomes a more more difficult for actions like "indent" and "insertUnorderedList" ...
*/
function insertHTML(html) {
    // Get the current selection
    const selection = window.getSelection();

    if (!selection.rangeCount) return;

    // Get the first range of the selection
    const range = selection.getRangeAt(0);

    // Create a document fragment to hold the new nodes
    const fragment = document.createDocumentFragment();

    // Create a temporary div to parse the HTML string
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;

    // Append the parsed nodes to the document fragment
    while (tempDiv.firstChild) {
        fragment.appendChild(tempDiv.firstChild);
    }

    // Insert the document fragment at the current range
    range.deleteContents();
    range.insertNode(fragment);

    // Move the cursor to the end of the inserted content
    // selection.removeAllRanges();
    // const newRange = document.createRange();
    // newRange.setStartAfter(fragment.lastChild);
    // newRange.setEndAfter(fragment.lastChild);
    // selection.addRange(newRange);
}

/* TextArea Hot Keys */

function insClip(itext) {
  let TAo = JS.doq("#TA");  // textarea
	let tav = TAo.value;
	let strPos = TAo.selectionStart;
	let front  = tav.slice(0, strPos);
	let back   = tav.slice(strPos);
	TAo.value  = front + itext + back;
	TAo.selectionEnd = strPos + itext.length;
	TAo.focus();
}
// insert a break
document.addEventListener("keydown", function(event) {
	if (event.keyCode === 13 && event.ctrlKey) {
		event.preventDefault();
		insClip("<br>");
	}
});
// insert a space
document.addEventListener("keydown", function(event) {
	if (event.keyCode === 32 && event.ctrlKey) {
		event.preventDefault();
		insClip("&nbsp;");
	}
});

</script>
</body>
</html>
