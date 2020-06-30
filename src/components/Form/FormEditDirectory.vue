<template>
    <el-form ref="FormEditDirectory">
        <q-field
            class="m-b-1"
            :error="$v.form.name.$error"
            :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
            <q-input
                v-model.trim="$v.form.name.$model"
                :float-label="$t('NAME')">
            </q-input>
        </q-field>

        <q-field class="m-b-1">
            <q-input
                v-model.trim="form.description"
                :float-label="$t('DESCRIPTION')">
            </q-input>
        </q-field>

        <div class="text-right">
            <q-btn
                :label="$t('CANCEL')"
                v-if="isDeviceGreaterSM"
                @click="onCancel"
                flat
                no-ripple
                class="m-r-1">
            </q-btn>
            <q-btn v-if="!edit"
                :label="$t('CREATE_DIRECTORY')"
                class="w-100--sm m-t-1--sm"
                @click="onSubmit"
                color="primary"
                no-ripple
                :loading="loading">
            </q-btn>
            <q-btn v-else
                :label="$t('SAVE')"
                class="w-100--sm m-t-1--sm"
                @click="onSubmit"
                color="primary"
                no-ripple
                :loading="loading">
            </q-btn>
        </div>
    </el-form>
</template>

<script>
import { required, minLength } from 'vuelidate/lib/validators';
import { Directory } from '@/shared/classes/Directory';
import directoriesMixin from '@/shared/mixins/directories';

export default {
	name: 'FormEditDirectory',

	mixins: [directoriesMixin],

	props: {
		data: {
			type: Object,
			required: false
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		edit() {
			return this.data.id ? true : false;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			form: {
				name: '',
				description: ''
			},
			loading: false,
			parentId: '' // will be set during initialization
		};
	},

	validations: {
		form: {
			name: { required }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			let directory = {};
			let dispatcherName = 'directories/CREATE_DIRECTORY';

			if (this.edit) {
				directory = new Directory(this.form);
				dispatcherName = 'directories/UPDATE_DIRECTORY';
			} else {
				directory = new Directory({
					description: this.form.description,
					name: this.form.name,
					parentId: this.parentId
				});
			}

			console.log('doSubmit new directory', directory);

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, directory)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.onCancel();

						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						// Refresh
						this.requestDirectoryEntries(this.parentId);
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			this.parentId = this.getDirectoryIdFromRoute(this.$route);
			this.parentId = this.parentId === 'directories' ? this.company.directoryId : this.parentId;
			if (this.edit && this.data.object) {
				this.form = _.cloneDeep(this.data.object);
			}
			console.log('FormEditDirectory', this.data, this.parentId, this.edit);
		},

		onCancel() {
			this.$emit('cancel');
		},

		onSubmit() {
			console.log(this.$v);
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSubmit();
			}
		}
	}
};
</script>
