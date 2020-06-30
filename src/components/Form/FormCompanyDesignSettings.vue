<template>
    <el-form class="company__design-settings">
        <el-row :gutter="40">

            <el-col :xs="24" :md="5">
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

                        <i class="el-icon-upload" v-show="!showPreview && !form.image"></i>
                        <!-- Preview -->
                        <img :src="form.source_b64" v-if="showPreview" id="avatar__preview" height="200">

                        <div class="el-upload__text">
                            Drop file here or <em>click to upload</em> <br>
                            <span class="font--small color-gray">Max. 2MB (JPG/PNG/GIF)</span>
                        </div>
                    </div>
                </el-upload>

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

            <el-col :xs="24" :md="6">
                <!-- <q-field class="m-b-1">
                    <q-color
                        v-model="form.brand_primary"
                        :float-label="$t('COLOR_PRIMARY')"
                        :format-model="format"
                        clearable />
                </q-field> -->

                <!-- <q-field class="m-b-1">
                    <q-color
                        v-model="form.brand_secondary"
                        :float-label="$t('COLOR_SECONDARY')"
                        :format-model="format"
                        clearable />
                </q-field> -->
            </el-col>

            <el-col :xs="24" :md="6">
                <!-- <q-field class="m-b-1">
                    <q-color
                        v-model="form.link_color"
                        :float-label="$t('COLOR_LINK')"
                        :format-model="format"
                        clearable />
                </q-field> -->
            </el-col>
        </el-row>

        <el-row class="m-t-1">
            <el-col :span="24" class="text-right">
                <q-btn
                    :label="$t('SAVE')"
                    color="primary"
                    class="w-100--sm"
                    no-ripple
                    @click="doSubmit"
                    v-loading="loading">
                </q-btn>
            </el-col>
        </el-row>

    </el-form>
</template>

<script>
import commonMixins from '@/shared/mixins/common';
import { CorporateIdentity } from '@/shared/classes/Company';
import { getBase64 } from '@/services/utils';

export default {
	name: 'FormCompanyDesignSettings',

	mixins: [commonMixins],

	props: {
		company: {
			type: Object,
			required: true
		},

		preferences: {
			type: Object,
			required: true
		}
	},

	data() {
		return {
			form: {
				brand_primary: '',
				brand_secondary: '',
				link_color: ''
			},
			format: 'hex',
			loading: false,
			loadingFile: false,
			showPreview: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			this.loading = true;

			const DATA = new CorporateIdentity(this.form);

			console.log('data', this.form, DATA);

			this.$store
				.dispatch('companies/UPDATE_COMPANY_DESIGN_PREFERENCES', DATA)
				.then(response => {
					this.loading = false;

					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.$store.dispatch('companies/GET_COMPANY_DESIGN_PREFERENCES', { id: this.company.id });
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			console.log('CHEKC SKADSA DJKSD');
			console.log(this.preferences)
			this.form = Object.assign({}, this.form, this.preferences);
			if (this.form.image) {
				this.form.source_b64 = this.form.image;
				this.showPreview = true;
			}
			console.log('form', this.form, this.preferences, this.designPreferences);
		},

		onChangeUpload(file, fileList) {
			console.log('onChangeUpload upload', file);

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
			this.form.image = null;
			this.form.source_b64 = null;

			console.log('onRemoveImage upload');
		}
	},

	watch: {
		preferences(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
.company__design-settings {
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
