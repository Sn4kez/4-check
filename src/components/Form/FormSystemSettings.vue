<template>
    <el-form>
        <q-field class="m-b-1">
            <q-select
                v-model.trim="form.language"
                :float-label="$t('CHOSE_LANGUAGE')"
                :options="languageOptions" />
        </q-field>

        <q-field class="m-b-1">
            <q-select
                v-model.trim="form.dateformat"
                :float-label="$t('DATE_FORMAT')"
                :options="dateformatOptions" />
        </q-field>

        <q-field class="">
            <q-select
                v-model.trim="form.timezone"
                :float-label="$t('TIMEZONE')"
                :options="timezoneOptions" />
        </q-field>
    </el-form>
</template>

<script>
export default {
	name: 'FormSystemSettings',

	data() {
		return {
			form: {
				language: '',
				dateformat: '',
				timezone: ''
			},
			dateformatOptions: [
				{
					label: 'DD.MM.YYYY',
					value: 'DD.MM.YYYY'
				},
				{
					label: 'YYYY/MM/DD',
					value: 'YYYY/MM/DD'
				}
			],
			languageOptions: [
				{
					label: this.$t('GERMAN'),
					value: 'de'
				},
				{
					label: this.$t('ENGLISH'),
					value: 'en'
				}
			],
			timezoneOptions: [
				{
					label: 'Berlin',
					value: 'UTC'
				},
				{
					label: 'USA',
					value: 'GMT'
				}
			]
		};
	}
};
</script>

<style lang="scss">
</style>
