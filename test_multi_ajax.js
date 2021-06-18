combinedAlert = {
        
	init: function(urlArray) {
		theContent = "";
		combinedAlert.requestWrapper(urlArray, theContent);
	},
	
	requestWrapper: function(urlArray, theContent) {
		/*The closure inside which we place the XMLHttpRequest call*/
		requestObject = makeRequestObject();
		requestObject.onreadystatechange = processRequest;
		/*(Defined below, as functions inside requestWrapper*/
		
		var url = urlArray[0];
		/*Get the first url out of the array*/
		
		requestObject.open("GET", url, true);
		requestObject.send(null);
		/*Does the actual opening of the connection*/
		
		function makeRequestObject() {
			/*This function forks for IE and returns the XMLHttpRequest object.*/
			if (window.XMLHttpRequest) {
				return new XMLHttpRequest();
			} else if (window.ActiveXObject) {
				return new ActiveXObject("Microsoft.XMLHTTP");
			}
		};
	
		function processRequest() {
			/*This function gets called when the XMLHttpRequest object reports a change in its state*/
			if (requestObject.readyState == 4) {
			/*We only care if it reports the state as 'finished'*/
				if (requestObject.status == 200) {
				/*We only want to support actual page loads, not 404s etc.*/
				combinedAlert.takeText(urlArray, theContent, requestObject.responseText);
				/*Here we pass the parameters to the requestWrapper function, along with the text from the page we grabbed asynchronously, to takeText()*/
				}
			}
		
		};
	},
	
	takeText: function(urlArray, theContent, responseText) {
		/*What gets called after each AJAX request completes*/
		theContent += responseText;
		/*The basic operation we want to do, adding what we got from the asynchronous call to our theContent variable*/
		
		urlArray.shift();
		/*since we've gotten this far, we must have finished with the loading of the URL in the position urlArray[0], which we set the XMLHttpRequest object to fetch from in requestWrapper. So we remove it from the array.*/
		if (urlArray.length > 0) {
			/*If, after the shift, we still have URLs to process*/
			combinedAlert.requestWrapper(urlArray, theContent);
			/*The core of the trickiness. We now send the altered urlArray and theContent variables back to requestWrapper, which will in turn load the next URL and kick off takeText() again with the responseText from that page load...*/
		} else {
			/*Or, if we have no more URLs to process*/
			combinedAlert.doAlert(theContent);
			/*doAlert doesn't care about anything except what we want it to alert*/
		}
	},
	
	doAlert: function(theContent) {
		/*What gets called when the last AJAX request completes*/
		alert(theContent);
	}

}