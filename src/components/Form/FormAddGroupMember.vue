<template>
    <div>
        <div v-if="!data.group.id">
            <el-alert
                :title="$t('HINT')"
                description="Bitte Speichern Sie zuerst eine Gruppe, bevor Sie Gruppenmitglieder hinzufügen."
                type="warning"
                show-icon
                :closable="false">
            </el-alert>
        </div>

        <div v-else>
            <q-list link no-border>
                <q-list-header>{{$t('AVAILABLE_USERS')}}</q-list-header>
                <q-item tag="label" v-for="item in data.users" :key="item.id" :disabled="item.role == 'admin'" v-model="selectedMember">
                    <q-item-side>
                        <!-- <q-radio :disable="item.role == 'admin'" :readonly="item.role == 'admin'" v-model="selectedMember" :val="item.id" /> -->
                        <q-checkbox v-model="memberArray" :val="item.id" />
                    </q-item-side>
                    <q-item-main>
                        <q-item-tile label>{{item.firstName + ' ' + item.lastName}}</q-item-tile>
                        <q-item-tile sublabel>{{item.email}}</q-item-tile>
                    </q-item-main>
                </q-item>
            </q-list>

            <div class="text-right">
                <q-btn
                    class="m-t-1 w-100--sm m-t-2--sm"
                    color="primary"
                    :label="$t('SAVE')"
                    @click="onClickSave"
                    no-ripple
                    :loading="loading"
                />
            </div>
        </div>
    </div>
</template>

<script>
export default {
	name: 'FormAddGroupMember',

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			loading: false,
			selectedMember: '',
			memberArray: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doAddUserToGroup() {
			this.loading = true;

			let REQUESTS = [];

			for(var item in this.memberArray){
				this.$store
					.dispatch('groups/CREATE_GROUP_MEMBER', {
						id: this.data.group.id,
						data: {
							id: this.memberArray[item]
						}
					})
					.then(response => {
						this.$store.commit('CLOSE_DIALOG');
						this.$store.commit('CLOSE_MODAL');
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});
						this.loading = false;

						// Send event to parent
						this.$eventbus.$emit('groupmembers:refresh');
					});
			}
			
		},

		init() {
			console.log('FormAddGroupMember mounted', this.data);
		},

		onClickSave() {

			if(this.memberArray.length > 0){
				this.doAddUserToGroup();
			}else{
				this.$store.commit('CLOSE_MODAL');
			}

			/*console.log('CHECK THIS: ', this.selectedMember)
			console.log(this.memberArray)*/
			// if (this.selectedMember) {
			// 	this.doAddUserToGroup();
			// 	console.log('speichern');
			// } else {
			// 	console.log('Modal schlie0en');

			// 	this.$store.commit('CLOSE_MODAL');
			// }
		},

		storeCheckedValue(id, event){
			console.log('CHECK: ', id)
		}
	}
};
</script>

<style>
.q-checkbox-icon{
	opacity:1!important;
}
</style>