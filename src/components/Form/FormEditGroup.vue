<template>
    <el-form class="form-edit-group">
        <el-row :gutter="40">
            <el-col :sm="24" :md="6" class="text-center--sm">
                <el-upload
                    action=""
                    drag
                    :show-file-list="false"
                    :on-change="onChangeUpload"
                    :auto-upload="false"
                    :multiple="false"
                    :on-remove="onRemoveImage"
                    ref="upload">

                    <div v-loading="loadingFile" class="el-upload-dragger__inner">

                        <i class="el-icon-upload" v-show="!showPreview"></i>
                        <img :src="form.source_b64" v-if="showPreview" id="avatar__preview" height="200">

                        <div class="el-upload__text m-t-half">
                            Drop file here or <em>click to upload</em> <br>
                            <span class="font--small color-gray">Max. 2MB (JPG/PNG/GIF)</span>
                        </div>
                    </div>
                </el-upload>

                <!-- Delete Button -->
                <div class="text-center m-t-half" v-if="showPreview">
                    <q-btn
                        icon="delete"
                        :label="$t('REMOVE_IMAGE')"
                        flat
                        no-ripple
                        @click="onRemoveImage">
                    </q-btn>
                </div>
            </el-col>

            <el-col :sm="24" :md="18" class="m-t-2--sm">
                <q-field
                    class="m-b-1"
                    :error="$v.form.name.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.name.$model"
                        :float-label="$t('NAME')">
                    </q-input>
                </q-field>

                <q-field
                    class="m-b-1">
                    <q-input type="textarea"
                        v-model.trim="form.description"
                        :float-label="$t('DESCRIPTION')">
                    </q-input>
                </q-field>

                <div class="text-right m-t-3--sm">
                    <q-btn
                        :label="$t('SAVE')"
                        @click="onSubmit"
                        :loading="loading"
                        color="primary"
                        no-ripple
                        class="w-100--sm"/>
                </div>

            </el-col>
        </el-row>
    </el-form>
</template>

<script>
import QS from 'qs';
import axios from 'axios';
import { cloneDeep } from 'lodash';
import { required, email, minLength } from 'vuelidate/lib/validators';
import { getBase64 } from '@/services/utils';
import { Group } from '@/shared/classes/Group';
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormEditGroup',

	mixins: [commonMixins],

	props: {
		company: {
			type: Object,
			required: false
		},

		data: {
			type: Object,
			required: false
		},

		edit: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			form: {
				name: '',
				description: ''
			},
			loading: false,
			loadingFile: false,
			showPreview: false
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
		doSave() {
			this.loading = true;

			const GROUP = new Group(this.form);
			GROUP.id = this.company.id;

			this.$store
				.dispatch('companies/CREATE_GROUP', GROUP)
				.then(response => {
					this.loading = false;
					if (response.status === 201 || response.status === 204) {
						this.$emit('group-created', response.data.data);

						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
					this.onCancel();
				});
		},

		doUpdate() {
			this.loading = true;

			this.$store
				.dispatch('groups/UPDATE_GROUP', this.form)
				.then(response => {
					this.loading = false;
					this.onCancel();

					this.$q.notify({
						message: this.$t('SAVE_SUCCESS'),
						type: 'positive'
					});
				})
				.catch(err => {
					this.loading = false;
					this.onCancel();
				});
		},

		init() {
			if (this.data) {
				this.form = Object.assign({}, this.form, this.data);

				if (this.data.image) {
					this.form.source_b64 = this.data.image;
					this.showPreview = true;
				}
			}

			console.log('FormEditGroup', this.data);
		},

		onCancel() {
			this.$emit('cancel');
		},

		onChangeUpload(file, fileList) {
			const isJPG = file.raw.type === 'image/jpeg';
			const isPNG = file.raw.type === 'image/png';
			const isGIF = file.raw.type === 'image/gif';
			const isLt2M = file.size / 1024 / 1024 < 2;

			if (!isJPG && !isPNG && !isGIF) {
				this.$q.notify({
					message: this.$t('FORMAT_NOT_SUPPORTED'),
					type: 'negative'
				});
				return;
			}

			if (!isLt2M) {
				this.$q.notify({
					message: this.$t('FILE_SIZE_EXCEEDED'),
					type: 'negative'
				});
				return;
			}

			this.loadingFile = true;

			getBase64(file.raw).then(response => {
				this.form.source_b64 = response;
				this.showPreview = true;

				setTimeout(() => {
					this.loadingFile = false;
				}, 1000);
			});

			console.log('onChangeUpload upload', file, fileList, this.form.source_b64);
		},

		onRemoveImage() {
			this.showPreview = false;

			this.form.image = '';
			delete this.form.source_b64;

			console.log('onRemoveImage upload');
		},

		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				if (this.edit) {
					this.doUpdate();
				} else {
					this.doSave();
				}
			}
		}
	},

	watch: {
		data(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
.group__image {
	@media screen and (min-width: $screen-md) {
		min-width: 100%;
	}
}

.form-edit-group {
	.el-upload,
	.el-upload-dragger {
		width: 100%;
	}

	.el-upload-dragger {
		display: flex;
		min-height: 220px;
		height: auto;

		.el-icon-upload {
			margin: 0 0 10px 0;
		}
	}

	.el-upload-dragger__inner {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		width: 100%;
	}
}
</style>
