import messages from "../i18n/messages";

export function getPreferredLocale() {
    const preferredLanguages = navigator.languages !== undefined ? navigator.languages : [navigator.language];
    for (let i = 0; i < preferredLanguages.length; i++) {
        const locale = preferredLanguages[i].toLocaleLowerCase().substr(0, 2);
        if (messages.hasOwnProperty(locale)) {
            return locale;
        }
    }

    return 'de';
}

/**
 * Return 'de' as default.
 *
 * @returns String      Current locale
 */
export function getLocale() {
	return localStorage.getItem('locale') || getPreferredLocale();
}

/**
 * @returns Boolean
 */
export function isDeviceXS() {
	return window.matchMedia('(max-width: 767px)').matches;
}

/**
 * @returns Boolean
 */
export function isDeviceSM() {
	return window.matchMedia('(min-width: 768px) and (max-width: 991px)').matches;
}

/**
 * @returns Boolean
 */
export function isDeviceGreaterSM() {
	return window.matchMedia('(min-width: 992px)').matches;
}

/**
 * @returns Boolean
 */
export function isDeviceMD() {
	return window.matchMedia('(min-width: 992px) and (max-width: 1199px)').matches;
}

/**
 * @returns Boolean
 */
export function isDeviceGreaterMD() {
	return window.matchMedia('(min-width: 1200px)').matches;
}

/**
 * @returns Boolean
 */
export function isDeviceLG() {
	return window.matchMedia('(min-width: 1200px) and (max-width: 1919px)').matches;
}

/**
 * @returns Boolean
 */
export function isDeviceXL() {
	return window.matchMedia('(min-width: 1920px)').matches;
}

/**
 * Returns users current timezone if the Internationalization API is supported
 *
 * @return {String}     Current timezone or empty string
 */
export function getTimezone() {
	let timezone = '';

	if ('Intl' in window) {
		timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
	}

	return timezone;
}
