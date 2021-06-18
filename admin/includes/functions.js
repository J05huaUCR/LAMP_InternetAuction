// JavaScript Document
function fAddNode(whichField){
	
	var field = document.getElementById(whichField);
	field.innerHTML = "Success!";

	var input = document.createElement("input");

	input.type = "hidden";
   input.name = "myHiddenField";
   input.value = "myValue";
	/*
	input.setAttribute("type", "hidden");
	input.setAttribute("name", "name_you_want");
	input.setAttribute("value", "value_you_want");
	*/
	
	//append to form element that you want .
	document.getElementById("domain_nodes").appendChild(input);
	
	alert("wtf!");
}

function fGetURLs() {
	var lb_domains = document.getElementById('domains');
	var lb_nodes = document.getElementById('nodes');
	
	for(var i = 0; i < sourceURLs.length; i++) {
		var newOption = document.createElement("option");
		newOption.value = sourceURLs[i];
		newOption.text = sourceURLs[i];
		/*
		for(var j = 0; j < sourceURLs.length; j++){
			alert(sourceURLs.options[j]);
		}
		*/
		try {
			lb_domains.add(newOption, null); //Standard
		} catch(error) {
			lb_domains.add(newOption); // IE only
		}
	}
}

function fClearListBox(whichListBox) {
	var src = document.getElementById(whichListBox);
	for(var count=0; count < src.options.length; count++) {
		try {
			src.remove(count, null);
		} catch(error) {
			src.remove(count);
		}
		count--;
	}
}

function fGetNode(whichNode) {
	
	fClearListBox('domains');
	fClearListBox('nodes');
	var dbURLs = sourceURLs.slice(0);
	//var dbURLs = sourceURLs;
	var lb_domains = document.getElementById('domains');
	var lb_nodes = document.getElementById('nodes');
	var node_group = document.getElementById('lb_header_nodes');
	node_group.innerHTML  = "Group: " + whichNode;

	for(var i = 0; i < nodes[whichNode].length; i++) {// search for matched and add
		for (var j = 0; j < dbURLs.length; j++) { 
			//alert(sourceURLs[j] + " | " + j);
			if (nodes[whichNode][i] == dbURLs[j]) {
				// add to nodes listbox
				var newOption = document.createElement("option");
				newOption.value = nodes[whichNode][i];
				newOption.text = nodes[whichNode][i];
				try {
					lb_nodes.add(newOption, null); //Standard
				} catch(error) {
					lb_nodes.add(newOption); // IE only
				}
				dbURLs.splice(j, 1) // remove from array
			} else {
				/* add to domains listbox
				var newOption = document.createElement("option");
				newOption.value = nodes[whichNode][i];
				newOption.text = nodes[whichNode][i];
				try {
					lb_domains.add(newOption, null); //Standard
				} catch(error) {
					lb_domains.add(newOption); // IE only
				}
				*/
			}
			
		}
	}
	for (var j = 0; j < dbURLs.length; j++) { // add remaining to source box
		//alert(sourceURLs[j] + " | " + j);
		/* add to domains listbox*/
		var newOption = document.createElement("option");
		newOption.value = dbURLs[j];
		newOption.text = dbURLs[j];
		try {
			lb_domains.add(newOption, null); //Standard
		} catch(error) {
			lb_domains.add(newOption); // IE only
		}
		
	}
}

function fMoveDomain(whichWay) {
	//alert ("WHICHWAY:|"+whichWay+"|");
	var domains_list = document.getElementById('domains');
	var nodes_list = document.getElementById('nodes');
	var domains_selected = domains_list.options[domains_list.selectedIndex].value;
	
	alert(domains_selected[1]);
	
	// Add an Option object to Drop Down/List Box
	var opt = document.createElement("option");
	nodes_list.options.add(opt);
	//document.getElementById("DropDownList").options.add(opt);
	
	// Assign text and value to Option object
	opt.text = "test";
	opt.value = "wtf";
	//alert ("SELECTED: |"+domains_selected+"|");
}

function fSortList(whichList) {
	var lb = document.getElementById(whichList);
	arrTexts = new Array();
	arrValues = new Array();
	arrOldTexts = new Array();
	
	
	for(i=0; i<lb.length; i++){
		arrTexts[i] = lb.options[i].text;
		arrValues[i] = lb.options[i].value;
		
		arrOldTexts[i] = lb.options[i].text;
	}
	
	arrTexts.sort();
	
	for(i=0; i<lb.length; i++) {
		lb.options[i].text = arrTexts[i];
		for(j=0; j<lb.length; j++) {
			if (arrTexts[i] == arrOldTexts[j]) {
				lb.options[i].value = arrValues[j];
				j = lb.length;
			}
		}
	}
}

function fLbSelectAll(listID, isSelect) {
	var listbox = document.getElementById(listID);
	for(var count=0; count < listbox.options.length; count++) {
		listbox.options[count].selected = isSelect;
	}
}

function fLbMoveAcross(sourceID, destID) {
	var src = document.getElementById(sourceID);
	var dest = document.getElementById(destID);
	
	for(var count=0; count < src.options.length; count++) {
		if(src.options[count].selected == true) {
			var option = src.options[count];
			
			var newOption = document.createElement("option");
			newOption.value = option.value;
			newOption.text = option.text;
			newOption.selected = true;
			try {
				dest.add(newOption, null); //Standard
				src.remove(count, null);
			} catch(error) {
				dest.add(newOption); // IE only
				src.remove(count);
			}
			count--;
		}
	}
}

function fLbMoveDNU(listID, direction) {
	/* call methods
		listbox_move('countryList', 'up'); //move up the selected option
		listbox_move('countryList', 'down'); //move down the selected option
	*/
 
    var listbox = document.getElementById(listID);
    var selIndex = listbox.selectedIndex;
 
    if(-1 == selIndex) {
        alert("Please select an option to move.");
        return;
    }
 
    var increment = -1;
    if(direction == 'up')
        increment = -1;
    else
        increment = 1;
 
    if((selIndex + increment) < 0 ||
        (selIndex + increment) > (listbox.options.length-1)) {
        return;
    }
 
    var selValue = listbox.options[selIndex].value;
    var selText = listbox.options[selIndex].text;
    listbox.options[selIndex].value = listbox.options[selIndex + increment].value
    listbox.options[selIndex].text = listbox.options[selIndex + increment].text
 
    listbox.options[selIndex + increment].value = selValue;
    listbox.options[selIndex + increment].text = selText;
 
    listbox.selectedIndex = selIndex + increment;
}