<!--
@component:         ElementAuditTextfield
@environment:       Hyprid
@description:       Can be used for both textfield and notefield.
                    The difference is that textfield is fixed and notefield is not.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-12
@modified:          2018-10-09
-->
<template>
    <div class="checklist__element-textfield p-1">
        <el-form ref="ElementAuditTextfield">
            <q-field>
                <q-input
                    v-model="form.text"
                    :readonly="form.fixed"
                    :loading="loading"
                    @blur="onBlur($event, form.fixed, form.update_id)"
                    >
                </q-input>
            </q-field>
        </el-form>
    </div>
</template>

<script>
import { Check } from '@/shared/classes/Check';
import { TextfieldExtension } from '@/shared/classes/Extension';
import auditMixins from '@/shared/mixins/audits';
import checklistMixins from '@/shared/mixins/checklists';
import commonMixin from '@/shared/mixins/common';

export default {
	name: 'ElementAuditTextfield',

	mixins: [auditMixins, checklistMixins, commonMixin],

	props: {
		data: {
			type: Object,
			required: true
		},
		audit: {
			type: Object,
			required: false,
			default: function() {
				return {};
			}
		}
	},

	data() {
		return {
			form: {
				text: '',
				fixed: true
			},
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			
			/*if (!this.data.object.fixed && this.data.text) {
				this.form.text = this.data.value.text;
			}*/

			if(this.data.parentId == undefined || this.data.parentId == ""){
				this.form = Object.assign({}, this.data.object);

				this.form.fixed = (this.data.object.fixed == 1 || this.data.object.fixed == 0) ? (this.data.object.fixed == 1 ? true : false) : this.data.object.fixed;

				// this means that the textfield for notefield are standalone, 
				// not inside some other element
				if(this.data.object.fixed){
					// means text field
					this.form.text = this.data.object.text;
				}else{
					// note field
					this.form.text = (this.data.value != null || this.data.value != undefined) && (Object.keys(this.data.value).length > 0) ? this.data.value.value : "";
					this.form.update_id = "";
				}
			}else{
				this.form = Object.assign({}, this.data.object.object);

				this.form.fixed = (this.data.object.object.fixed == 1 || this.data.object.object.fixed == 0) ? (this.data.object.object.fixed == 1 ? true : false) : this.data.object.object.fixed;

				// this means the fields are inside some other element
				if(this.data.object.object.fixed){
					// textfield
					this.form.text = this.data.object.object.text;
				}else{
					// notefield
					// this.form.text = this.data.object.object.text;
					let cat = this.data.createdAt;
					let pid = this.data.parentId;
					this.audit.results.map(item => {
						if(cat == item.object.createdAt && item.objectType == "textfield" && pid == item.parentId){
							this.form.update_id = item.id;
							this.form.text = (item.value != undefined && Object.keys(item.value).length > 0) ? item.value.value : "";
							/*if(item.value != undefined && Object.keys(item.value).length > 0){
								this.form.text = item.value.value;
								this.form.update_id = item.id;
							}*/
						}
					})
				}
			}

		},

		onBlur(event, va, id) {
			if(va == false){
				// this means notefield
				this.data.object.text = this.form.text;

				let toSend = {};

				if (!this.form.fixed) {

					if(id != ""){
						toSend['id'] = id;
						toSend['value'] = this.form.text;
						toSend['objectType'] = 'extension';
					}
					
					// const CHECK = new Check(this.data);

					const CHECK = (Object.keys(toSend).length > 0) ? new Check(toSend) : new Check(this.data);

					CHECK.value = this.form.text;
					CHECK.text = this.form.text;

					// CHECK.id = this.data.object.id;


					this.loading = true;
					this.doUpdateCheck(CHECK)
						.then(response => {
							this.loading = false;
							console.log('doUpdateCheck', response);
						})
						.catch(err => {
							this.loading = false;
							console.log('doUpdateCheck err', err);
						});
				}
			}
		}
	}
};
</script>
