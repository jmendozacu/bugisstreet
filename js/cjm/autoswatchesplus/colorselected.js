
function colorSelected(id, value, product_image_src, front_label) {

	var theswitchis = 'off',
		switchCounter = 0,
		k = 0,
		b = 0,
		l= 0,
		thelements = [],
		children = [],
		newchildren = [],
		thisdiv = '',
		thisopt = '',
		nextAttribute = '',
		nextAttrib = [],
		theoptions = [],
		selectedmoreview = [],
		moreviews = [],
		disableDiv = '',
		i,d,e,h,z,dropdown,textdiv,theattributeid,thedropdown,thetextdiv,selectedid,thedivid,thediv,dropdownval,base_image;
	
		base_image = document.getElementById("image");
		if (product_image_src !== '') {
			base_image.setAttribute("src", product_image_src);}
		
	
		selectedmoreview = jQuery("[name='" + "moreview" + value + "']");
		
	
	for (i = 0; i < attoptions.length; i += 1)
	{
		theattributeid = attoptions[i]; 
		thedropdown = 'attribute' + theattributeid; 
		thetextdiv = 'divattribute' + theattributeid; 
		selectedid = id;
	
		if (selectedid === thedropdown) {
			theswitchis = 'on'; } 
				
		if (theswitchis === 'on')
		{
		
			if(switchCounter === 1) {
				nextAttribute = theattributeid; } 
			
			dropdown = document.getElementById(thedropdown);
			textdiv =  document.getElementById(thetextdiv);	
			textdiv.innerHTML = selecttitle;
			dropdown.selectedIndex = 0;
			
			if(switchCounter !== 0) {
				
				thelements = document.getElementById('ul-attribute' + theattributeid).getElementsByTagName('*');
				
				for(h = 0; h < thelements.length; h += 1) {
					if (thelements[h].nodeName.toLowerCase() === 'div' || thelements[h].nodeName.toLowerCase() === 'img') {
						thedivid = thelements[h].id;
						thisdiv = document.getElementById(thedivid);
						thisdiv.className = "swatch";
					}
				}
			}
			
			for (z = 1; z < dropdown.options.length; z += 1)
			{
				dropdownval = dropdown.options[z].value;
				
				moreviews = jQuery("[name='" + "moreview" + dropdownval + "']")				;
				thediv = document.getElementById(dropdownval);
				if (thediv !== null ) {
					thediv.className = "swatch";}
				if (moreviews !== null ) {
					for(d = 0; d < moreviews.length; d += 1) {
						moreviews[d].style.display = 'none'; }
				}
			}
			switchCounter += 1;
		}
	}
	

	if(nextAttribute === null || nextAttribute === '') {
		nextAttribute = 'none'; }

			
	dropdown = document.getElementById(id);
		
	for (i = 0; i < dropdown.options.length; i += 1)
	{
		if ( dropdown.options[i].value === value )
		{
			document.getElementById('div' + id).innerHTML = front_label;
			document.getElementById(value).className = "swatchSelected";
			if ( selectedmoreview !== null ) {
				for(e = 0; e < selectedmoreview.length; e++) {
					selectedmoreview[e].style.display = 'block'; }
			}	
			dropdown.selectedIndex = i;
			break;
		}
	}
		
	spConfig.configureElement($(id));
	

	if(nextAttribute !== 'none') {
		
		children = document.getElementById('ul-attribute' + nextAttribute).getElementsByTagName('*');
		nextAttrib = document.getElementById('attribute' + nextAttribute);
		
		for(h = 0; h < nextAttrib.options.length; h += 1) {
			if(nextAttrib.options[h].value !== '') {
				theoptions[b] = nextAttrib.options[h].value;
				b += 1;
			}
		}
		for(h = 0; h < children.length; h += 1) {
			if (children[h].nodeName.toLowerCase() === 'div' || children[h].nodeName.toLowerCase() === 'img') {
				newchildren[k] = children[h].id;
				k += 1;
			}
		}
		
		for(h = 0; h < newchildren.length; h += 1) {
			thisdiv = newchildren[h];
			if(theoptions[l]) {
				thisopt = theoptions[l];
				if(thisopt === thisdiv) {
					disableDiv = document.getElementById(thisdiv);
					disableDiv.className = 'swatch';
					l += 1;
				} else {
					disableDiv = document.getElementById(thisdiv);
					disableDiv.className = 'disabledSwatch';
				}
			} else {
				disableDiv = document.getElementById(thisdiv);
				disableDiv.className = 'disabledSwatch';
			}
		}
	}

}