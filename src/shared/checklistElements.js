import i18n from '@/i18n/index';

const elements = {
	basic: [
		{
			id: 1,
			name: 'section',
			label: 'GROUP',
			icon: 'group_work'
		},
		{
			id: 2,
			name: 'question',
			label: 'QUESTION',
			icon: 'help'
		},
		{
			id: 3,
			name: 'value-question',
			label: 'VALUE_QUESTION',
			icon: 'settings_input_component'
		}
	],
	extensions: [
		{
			id: 1,
			name: 'location',
			label: 'LOCATION',
			icon: 'place'
		},
		{
			id: 2,
			name: 'textfield',
			label: 'TEXTFIELD',
			icon: 'text_fields'
		},
		{
			id: 3,
			name: 'participant',
			label: 'PARTICIPANT',
			icon: 'people'
		},
		{
			id: 4,
			name: 'signature',
			label: 'SIGNATURE',
			icon: 'attach_file'
		},
		{
			id: 5,
			name: 'notefield',
			label: 'NOTEFIELD',
			icon: 'comment'
		},
		{
			id: 6,
			name: 'picture',
			label: 'PICTURE',
			icon: 'photo'
		}
	]
};

export function getElements() {
	return elements;
}

export function getBasicElements() {
	return elements.basic;
}

export function getExtensionElements() {
	return elements.extensions;
}
