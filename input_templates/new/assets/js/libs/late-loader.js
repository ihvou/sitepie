/* global window, document */
(function () {
	"use strict";

	var candidates = document.getElementsByTagName('script');

	function addListner(tag) {
		var go = function () {
			tag.src = tag.dataset.src;
		};

		var el = tag.dataset.dependency ? document.getElementById(tag.dataset.dependency) : window;

		if (window.addEventListener) {
			el.addEventListener("load", go, false);
		} else if (window.attachEvent) {
			el.attachEvent("onload", go);
		}
	}

	function parse(dependency) {
		for (var i = 0; candidates.length > i; i++) {
			if (candidates[i] && candidates[i].dataset.src) {

				if (candidates[i].dataset.dependency != dependency) {
					continue;
				}

				if (candidates[i].dataset.id) {
					candidates[i].id = candidates[i].dataset.id;
				}

				addListner(candidates[i]);

				if (candidates[i].dataset.id) {
					parse(candidates[i].dataset.id);
				}

			}
		}
	}

	parse();

}());