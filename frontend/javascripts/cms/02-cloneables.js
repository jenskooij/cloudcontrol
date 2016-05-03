function createCloneable(cloneButtonId, cloneableId, dropZoneId) {
	"use strict";
	var cloneButton = document.getElementById(cloneButtonId),
		clonable = document.getElementById(cloneableId),
		dropZone = document.getElementById(dropZoneId),
		removeButtons,
		cln,
		i,
		textareas,
		inputs;
	if (removeButtons !== null && dropZone !== null) {
		removeButtons = dropZone.getElementsByTagName('a');
		for (i = 0; i < removeButtons.length; i += 1) {
			removeButtons[i].onclick = removeCloneable;
		}
	}
	
	if (cloneButton !== null) {
		cloneButton.onclick = function () {
			cln = clonable.cloneNode(true);
			cln.removeAttribute('id');
			cln.style.display = 'block';
			dropZone.appendChild(cln);

			//reset all values
			textareas = cln.getElementsByTagName('textarea');
			for (i = 0; i < textareas.length; i += 1) {
				textareas[i].innerHTML='';
			}
			inputs = cln.getElementsByTagName('input');
			for (i = 0; i < inputs.length; i += 1) {
				inputs[i].setAttribute('value', '');
			}

			removeButtons = dropZone.getElementsByTagName('a');
			for (i = 0; i < removeButtons.length; i += 1) {
				if (removeButtons[i].className.indexOf('js-imageSelector') === -1 && removeButtons[i].className.indexOf('js-fileSelector') === -1) {
					removeButtons[i].onclick = removeCloneable;
				}
			}
		};
	}
}

function removeCloneable(e) {
	e = e ? e : window.event;
	var target = e.target ? e.target : e.srcElement;
	if (typeof e.preventDefault === 'function') {
		e.preventDefault();
	}

	if (target.className.indexOf('js-imageSelector') === -1  && target.className.indexOf('js-fileSelector') === -1) {
		while(target.nodeName !== "LI") {
			target = target.parentNode;
		}

		target.parentNode.removeChild(target);
	}
}