import i18n from '@/i18n/index';

export function getRepeatations() {
	return [
        {
            id: 1,
            label: i18n.t('HOURLY'),
            value: 'hourly'
        },
        {
            id: 2,
            label: i18n.t('DAILY'),
            value: 'daily'
        },
        {
            id: 3,
            label: i18n.t('WEEKLY'),
            value: 'weekly'
        },
        {
            id: 4,
            label: i18n.t('MONTHLY'),
            value: 'monthly'
        }
    ];
}
