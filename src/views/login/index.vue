<template>
	<div class="login">

        <div class="box-container">
            <div class="box-container__inner">
                <el-card>

                    <div class="text-center m-t-1 m-b-1" v-loading="loading">
                        <img v-if="corporateIdentity.image"
                            :src="corporateIdentity.image"
                            alt="Logo"
                            class="login__logo login__logo--custom">
                        <SVGLogo v-else
                            class="login__logo" />

                    </div>

                    <div class="p-l-1 p-r-1">
                        <!-- Password reset complete message -->
                        <div v-if="showMessageAfterPasswordReset">
                            <el-alert
                                :title="$t('PASSWORD_HAS_BEEN_CHANGED')"
                                type="success"
                                :description="$t('EMAIL_HAS_BEEN_SENT_TO_YOU')"
                                closable="false"
                                show-icon>
                            </el-alert>
                        </div>
                        <!-- Form forgot Password -->
                        <div v-if="forgotPasswordFormVisible">
                            <FormForgotPassword @reset-completed="onResetPassword" />

                            <div class="m-b-0 text-right">
                                <el-button @click="forgotPasswordFormVisible = false" type="text" class="font--small">{{$t('BACK_TO_LOGIN')}}</el-button>
                            </div>
                        </div>
                        <!-- Form login -->
                        <div v-else>
                            <FormLogin />
                            <div class="m-b-0 text-right">
                                <el-button @click="forgotPasswordFormVisible = true" type="text" class="font--small">{{$t('FORGOT_PASSWORD')}}</el-button>
                            </div>
                        </div>

                        <Separator
                            :text="$t('OR')"
                            show-line
                            class="m-t-1 m-b-1"
                        />

                        <router-link class="el-button w-100 el-button--text" to="/register">{{$t('REGISTER')}}</router-link>
                    </div>

                </el-card>
            </div>
        </div>

	</div>
</template>

<script>
import FormForgotPassword from '@/components/Form/FormForgotPassword';
import FormLogin from '@/components/Form/FormLogin';
import Separator from '@/components/Misc/Separator';
import SVGLogo from '@/assets/img/4-check-logo.svg';

export default {
	name: 'LoginView',

	components: {
		FormForgotPassword,
		FormLogin,
		Separator,
		SVGLogo
	},

	computed: {
		corporateIdentity() {
			return this.$store.state.companies.corporateIdentity;
		}
	},

	data() {
		return {
			forgotPasswordFormVisible: false,
			loading: false,
			showMessageAfterPasswordReset: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			if (this.$route.query.key) {
				// Reset corporate identity
				if (this.$route.query.key === 'reset') {
					console.log('reset');
					this.$localStorage.remove('corporateIdentity');
					this.$store.commit('companies/SET_COMPANY_CORPORATE_IDENTITY', {});
					this.$store.commit('SET_CUSTOM_DESIGN', { isCustomDesign: false });
					return;
				}
			}

			this._corporateIdentity = JSON.parse(this.$localStorage.get('corporateIdentity'));

			console.log('login init', this._corporateIdentity, this.$route.query, this.$route.query.key === 'reset');

			// Set CI
			if (this._corporateIdentity && this._corporateIdentity.image) {
				this.$store.commit('SET_CUSTOM_DESIGN', { isCustomDesign: true });
				this.$store.commit('companies/SET_COMPANY_CORPORATE_IDENTITY', this._corporateIdentity);
				return;
			}

			// Request CI if not in localStorage
			if (this.$route.query.key) {
				this.handleCustomDesign();
			}
		},

		handleCustomDesign() {
			this.requestCorporateIdentity(this.$route.query.key).then(response => {});
		},

		onResetPassword() {
			this.forgotPasswordFormVisible = !this.forgotPasswordFormVisible;
			this.showMessageAfterPasswordReset = true;
		},

		requestCorporateIdentity(companyId) {
			this.loading = true;
			return this.$store
				.dispatch('companies/GET_COMPANY_LOGIN', { id: companyId })
				.then(response => {
					this.loading = false;
					this.$store.commit('SET_CUSTOM_DESIGN', { isCustomDesign: true });
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		}
	},

	watch: {
		$route(to, from) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
.login {
	display: flex;
	align-items: center;
	justify-content: center;
	min-height: 100vh;

	&__logo {
		max-height: 4rem;

		&--custom {
			min-height: 6rem;
			max-width: 100%;
		}
	}

	.box-container {
		@media screen and (min-height: 600px) {
			margin-top: -5%;
		}
	}

	.el-card {
		@media screen and (max-width: $screen-md) {
			border: none;
			box-shadow: none;
		}
	}
}
</style>
