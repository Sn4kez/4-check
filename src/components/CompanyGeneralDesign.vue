<template>
    <div class="company__general-settings">
        <el-row :gutter="60">
            <el-col :xs="24" :sm="12">
                <h5 class="headline m-t-0 m-b-2" v-html="$t('COMPANY_PERSONALIZATION.HEADLINE')"></h5>
                <p>{{$t('COMPANY_PERSONALIZATION.LOGIN_LINK_INFO')}}</p>
                <ul class="list p-l-1">
                    <li class="m-b-half">{{$t('COMPANY_PERSONALIZATION.LOGO')}}</li>
                    <li>{{$t('COMPANY_PERSONALIZATION.COLORS')}}</li>
                </ul>
                <p class="m-t-2">{{$t('COMPANY_PERSONALIZATION.LOGIN_LINK_INFO_2')}}</p>
            </el-col>

            <el-col :xs="24" :sm="12" class="m-t-1">
                <p>{{$t('COMPANY_PERSONALIZATION.LOGIN_LINK')}}</p>
                <el-input v-model="loginLink" readonly class="el-input--login-link">
                    <el-button slot="append" @click="onBtnClickCopy">{{$t('COPY')}}</el-button>
                </el-input>
            </el-col>
        </el-row>
    </div>
</template>

<script>
export default {
	name: 'CompanyGeneralDesign',

	props: {
		company: {
			type: Object,
			required: true
		}
	},

	computed: {
		loginLink() {
			return window.location.origin + '/#/login?key=' + this.company.id;
		}
	},

	data() {
		return {};
	},

	methods: {
		onBtnClickCopy() {
			const copyText = document.querySelector('.el-input--login-link .el-input__inner');
			copyText.select();
			document.execCommand('copy');

			this.$q.notify({
				message: this.$t('COPIED_SUCCESSFULLY'),
				type: 'positive'
			});
		}
	}
};
</script>
