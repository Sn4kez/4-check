<!--
@component:         ElementTextfield
@environment:       Hyprid
@description:       Can be used for both textfield and notefield.
                    The difference is that textfield is fixed and notefield is not.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-12
@modified:          2018-09-12
-->
<template>
    <div class="checklist__element-textfield">
        <!-- Textfield -->
        <el-form ref="ElementTextfield" v-loading="loading" v-if="data.object.fixed">
            <q-field class=""
                :error="$v.form.text.$error"
                :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                <q-input @blur="onSubmit" v-model.trim="$v.form.text.$model"></q-input>
            </q-field>
        </el-form>

        <!-- Notefield -->
        <p v-else class="color-gray m-b-0">
            {{ $t('NOTEFIELD_WILL_BE_VISIBLE_DURING_AUDIT') }}
        </p>
    </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators';
import { TextfieldExtension } from '@/shared/classes/Extension';
import checklistMixins from '@/shared/mixins/checklists';
import commonMixin from '@/shared/mixins/common';

export default {
	name: 'ElementTextfield',

	mixins: [checklistMixins, commonMixin],

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	data() {
		return {
			form: {
				text: '',
				fixed: 1
			},
			loading: false
		};
	},

	validations: {
		form: {
			text: { required },
			fixed: {}
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSave() {
			let TEXTFIELD = new TextfieldExtension({
				id: this.data.id,
				data: this.form
			});

			TEXTFIELD['text'] = this.form.text

			let dispatcherName = 'extensions/UPDATE_EXTENSION';
			this.loading = true;
			this.$store
				.dispatch(dispatcherName, TEXTFIELD)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						// Refresh
						// this.requestDirectoryEntries(this.parentId);
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			this.form = Object.assign({}, this.data.object);
		},

		onSubmit() {
			
			if(this.form.text != this.data.object.text){
				this.doSave();
			}

		}
	}
};
</script>

<style lang="scss">
.checklist__element-textfield {
}
</style>

