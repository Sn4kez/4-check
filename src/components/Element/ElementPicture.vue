<!--
@component:         ElementPicture
@environment:       Hyprid
@description:       This component is used to build a checklist as well as to create an audit.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-13
@modified:          2018-10-28
-->
<template>
    <div class="checklist__element-picture" :class="{'p-1': audit.id}">
        <!-- Checklist -->
        <p v-if="!audit.id" class="color-gray m-b-0">{{ $t('SELECTION_WILL_BE_AVAILABLE_DURING_AUDIT') }}</p>

        <!-- Audit -->
        <div v-if="audit.id">
            <!-- Picture -->
            <div class="checklist__element-picture__picture-container" v-if="!isSignature">
                <el-upload
                    action=""
                    drag
                    :show-file-list="false"
                    :on-change="onChangeUpload"
                    :auto-upload="false"
                    :multiple="false"
                    :on-remove="onRemoveImage"
                    ref="upload">

                    <div v-loading="loading" class="el-upload-dragger__inner">
                        <i class="el-icon-upload" v-show="!showPreview && !form.image"></i>

                        <div v-show="showPreview">
                            <!-- Preview -->
                            <img :src="form.source_b64" alt="Preview" class="checklist__element-picture__preview">
                        </div>

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
                        @click="onRemoveImage"
                        class="m-r-1">
                    </q-btn>
                </div>
            </div>

            <!-- Signature -->
            <div class="checklist__element-picture__signature-container" v-if="isSignature">
                <div v-show="!showPreview">
                    <canvas
                        id="signature-canvas"
                        class="checklist__element-picture__signature"
                        v-touch-pan.prevent="handlePan"
                        ref="area">
                    </canvas>
                </div>

                <div v-show="showPreview">
                    <img :src="form.source_b64" alt="Preview" class="checklist__element-picture__preview">
                </div>

                <div class="m-t-1 text-right" v-if="isDraft">
                    <q-btn
                        :label="$t('DELETE')"
                        flat
                        @click="clearSignature"
                        class="m-r-1">
                    </q-btn>
                    <q-btn
                        :label="$t('SAVE')"
                        color="primary"
                        no-ripple
                        @click="onSaveSignature"
                        v-loading="loading">
                    </q-btn>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { getBase64, getCompressedImage } from '@/services/utils';
import { Check } from '@/shared/classes/Check';
import auditMixins from '@/shared/mixins/audits';
import commonMixin from '@/shared/mixins/common';

export default {
	name: 'ElementPicture',

	mixins: [auditMixins, commonMixin],

	props: {
		audit: {
			type: Object,
			required: false,
			default: function() {
				return {};
			}
		},

		data: {
			type: Object,
			required: true
		},

		open: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		isDraft() {
			if (!this.audit) {
				return false;
			}

			return this.getAuditStateById(this.audit.state).name === 'draft';
		},

		isSignature() {
			if (this.data.object.object) {
				return this.data.object.object.type === 'signature';
			}
			return this.data.object.type === 'signature';
		}
	},

	data() {
		return {
			blob: null,
			canvas: null,
			canvasCtx: null,
			form: {
				image: null,
				source_b64: null
			},
			loading: false,
			mousePressed: false,
			lastX: null,
			lastY: null,
			showPreview: false,
			flag: false,
			dot_flag: false,
			prevX: 0,
			currX: 0,
			prevY: 0,
			currY: 0,
			strokeStyle: 'black',
			lineWidth: 4,
			panning: false,
			info: null
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.form = Object.assign({}, this.form, this.data.object);
			

			if(this.data.parentId != undefined){

				let cat = this.data.createdAt;
				
				this.audit.results.map(item => {
					if(cat == item.object.createdAt){
						this.form.source_b64 = item.base64;
						this.showPreview = true;
					}

					if(this.isSignature){
						this.initCanvas();
					}
				})

			}else{
				if (this.data.base64 && this.data.value.value) {
					this.form.source_b64 = this.data.base64;
					this.showPreview = true;
				}

				if (this.isSignature) {
					this.initCanvas();
				}
			}
		},

		initCanvas() {
			this.canvas = document.querySelector('#signature-canvas');
			this.canvasCtx = this.canvas.getContext('2d');
			this.canvas.width = document.querySelector('.checklist__element-picture__signature-container').offsetWidth;
			this.canvas.height = document.querySelector(
				'.checklist__element-picture__signature-container'
			).offsetHeight;
		},

		clearSignature() {
			this.canvasCtx.clearRect(0, 0, this.canvas.width, this.canvas.height);

			if (this.form.source_b64) {
				this.showPreview = false;
				this.form.source_b64 = null;
				this.onRemoveImage();

				this.initCanvas();
			}
		},

		signatureToBae64() {
			return this.canvas.toDataURL();
		},

		/**
		 * We use the Qsasar TouchPan directive to draw the signature on both mobile and desktop
		 */
		handlePan({ position, direction, duration, distance, delta, isFirst, isFinal, evt }) {
			this.info = { position, direction, duration, distance, delta, isFirst, isFinal };

			const canvasCoordinates = this.canvas.getBoundingClientRect();

			if (position.top < canvasCoordinates.y || position.left < canvasCoordinates.x) {
				return false;
			}

			if (isFirst) {
				this.panning = true;

				this.prevX = this.currX;
				this.prevY = this.currY;
				this.currX = position.left - canvasCoordinates.x;
				this.currY = position.top - canvasCoordinates.y;

				this.canvasCtx.beginPath();
				this.canvasCtx.fillStyle = this.strokeStyle;
				this.canvasCtx.fillRect(this.currX, this.currY, 2, 2);

				this.canvasCtx.closePath();
			} else if (isFinal) {
				this.panning = false;
			} else {
				this.prevX = this.currX;
				this.prevY = this.currY;
				this.currX = position.left - canvasCoordinates.x;
				this.currY = position.top - canvasCoordinates.y;

				this.canvasCtx.beginPath();
				this.canvasCtx.moveTo(this.prevX, this.prevY);
				this.canvasCtx.lineTo(this.currX, this.currY);
				this.canvasCtx.strokeStyle = this.strokeStyle;
				this.canvasCtx.lineWidth = this.lineWidth;
				this.canvasCtx.stroke();
				this.canvasCtx.closePath();
			}
		},

		doSubmit(CHECK) {
			// Upload file to backend

			this.loading = true;
			this.doUpdateCheck(CHECK)
				.then(response => {
					this.loading = false;
					if (CHECK.value) {
						// this.showPreview = true;
					}
					if (response.status === 204) {
						console.log('doUpdateCheck', response);
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
					this.handleErrors(err);
					console.log('doUpdateCheck err', err);
				});
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

			// if (!isLt2M) {
			// 	this.$q.notify({
			// 		message: this.$t('FILE_SIZE_EXCEEDED'),
			// 		type: 'negative'
			// 	});
			// 	return;
			// }

			getBase64(file.raw).then(response => {
				getCompressedImage(response, file.raw.type, 0.2).then(result => {
					this.form.source_b64 = result;


					if(this.data.parentId != undefined){

						let cat = this.data.createdAt;
						let id = '';
						this.audit.results.map(item => {
							if(cat == item.object.createdAt){
								id = item.id
							}
						})
						
						let toSend = Object.assign({}, this.data);
						toSend['id'] = id;

						const CHECK = new Check(toSend);
						CHECK.value = this.form.source_b64;
						this.showPreview = true;

						this.doSubmit(CHECK);

					}else{
						const CHECK = new Check(this.data);
						CHECK.value = this.form.source_b64;
						this.showPreview = true;

						this.doSubmit(CHECK);

					}

				});
			});
		},

		onSaveSignature() {
			this.form.source_b64 = this.signatureToBae64();
			const CHECK = new Check(this.data);
			CHECK.value = this.form.source_b64;
			this.doSubmit(CHECK);
			this.showPreview = true;
			console.log('sdfsadf', this.form.source_b64);
		},

		onRemoveImage() {
			// Remove file from backend if it exist
			if (this.data.base64) {
				const CHECK = new Check(this.data);
				CHECK.object.value = '';
				CHECK.object.source_b64 = '';
				this.doSubmit(CHECK);
			}

			this.form.image = null;
			this.form.source_b64 = null;
			this.showPreview = false;

			console.log('onRemoveImage upload');
		}
	},

	watch: {
		open(newValue, oldValue) {
			if (newValue) {
				if (this.form.source_b64) {
					this.showPreview = true;
				} else if (this.isSignature) {
					this.initCanvas();
				}
			}
		}
	}
};
</script>

<style lang="scss">
.checklist__element-picture {
	.el-upload,
	.el-upload-dragger {
		height: auto;
		min-height: 180px;
		width: 100%;
	}
}

.checklist__element-picture__preview {
	height: 400px;
	max-width: 100%;
}

.checklist__element-picture__signature {
	border: 2px dashed $c-danger;
	width: 100%;
	height: 20rem;
}
</style>
