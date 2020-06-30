import { forEach } from 'lodash';

/**
 * Source: https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding
 * @param {String} str
 */
export function b64EncodeUnicode(str) {
	// first we use encodeURIComponent to get percent-encoded UTF-8,
	// then we convert the percent encodings into raw bytes which
	// can be fed into btoa.
	return btoa(
		encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function toSolidBytes(match, p1) {
			return String.fromCharCode('0x' + p1);
		})
	);
}

/**
 * Source: https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding
 * @param {String} str
 */
export function b64DecodeUnicode(str) {
	// Going backwards: from bytestream, to percent-encoding, to original string.
	return decodeURIComponent(
		atob(str)
			.split('')
			.map(function(c) {
				return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
			})
			.join('')
	);
}

/**
 * Search for a given string in an array with objects. Used to find a string
 * Example: Used to find a string in an table with accountants or clients
 *
 * @param {String} searchString
 * @param {array} searchInArray
 * @param {array} searchInColumns
 * @returns array
 */
export function searchStringInArray(searchString, searchInArray, searchInColumns) {
	const filteredData = searchInArray.filter(row => {
		let match = false;

		_.forEach(searchInColumns, column => {
			if (!row[column]) {
				return;
			}

			let stringIncluded = false;
			const currentValue = row[column].toString();
			const lowercaseValue = currentValue.toLowerCase();
			stringIncluded = lowercaseValue.includes(searchString);

			if (stringIncluded) {
				match = true;
			}
		});

		return match ? row : false;
	});

	return filteredData;
}

/**
 * Get first day of the year
 * @returns Date
 */
export function getFirstDayOfYear() {
	const currentDate = new Date();
	const year = currentDate.getFullYear();
	return new Date(Date.UTC(year, 0, 1));
}

/**
 * Get last day of the year
 * @returns Date
 */
export function getLastDayOfYear() {
	const currentDate = new Date();
	const year = currentDate.getFullYear();
	return new Date(Date.UTC(year, 11, 31));
}

/**
 * Get object site
 * @param {Object} obj
 * @returns {int}
 */
export function getObjectSize(obj) {
	let size = 0;

	for (let key in obj) {
		if (obj.hasOwnProperty(key)) {
			size++;
		}
	}

	return size;
}

/**
 * Return base64 string of file
 * @Source      https://stackoverflow.com/questions/36280818/how-to-convert-file-to-base64-in-javascript
 *
 * @param {File} file
 * @return {String}
 */
export function getBase64(file) {
	let reader = new FileReader();

	return new Promise((resolve, reject) => {
		reader.addEventListener(
			'load',
			() => {
				resolve(reader.result);
			},
			false
		);

		reader.readAsDataURL(file);
	});
}

/**
 * Compress base64 image
 * Usage: we need to compress to images because the strings are cutted on upload.
 *
 * @param   {String}    base64
 * @param   {String}    mimeType
 * @param   {Float}     quality
 * @returns {Promise}
 */
export function getCompressedImage(base64, mimeType, quality = 0.5) {
	const maxHeight = 768;
	const maxWidth = 1024;

	return new Promise(function(resolve, reject) {
		const canvas = document.createElement('canvas');
		canvas.width = maxWidth;
		canvas.height = maxHeight;
		const ctx = canvas.getContext('2d');

		const img = new Image();
		img.onload = () => {
			if (img.height > img.width) {
				canvas.height = maxWidth;
				canvas.width = maxHeight;
			}

			ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

			let result = canvas.toDataURL(mimeType, quality);
			if (img.width < maxWidth || img.height < maxHeight) {
				result = base64;
			}

			resolve(result);
		};

		img.src = base64;
	});
}
