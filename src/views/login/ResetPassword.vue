<template>
	<div class="view__reset-password">

        <div class="box-container">
            <div class="box-container__inner">
                <el-card>

                    <div class="text-center m-t-1 m-b-2">
                        <SVGLogo class="login__logo" />
                    </div>

                    <div class="p-l-1 p-r-1">
                        <div v-if="hasToken">
                            <el-alert
                                :title="$t('HINT')"
                                type="info"
                                :description="$t('PLEASE_ENTER_NEW_PASSWORD')"
                                :closable="false"
                                show-icon>
                            </el-alert>
                            <FormResetPassword :token="token" class="m-t-1" />
                        </div>
                        <div v-else>
                            <el-alert
                                :title="$t('ERROR')"
                                type="error"
                                :description="$t('NO_VALID_TOKEN')"
                                :closable="false"
                                show-icon>
                            </el-alert>
                            <router-link class="el-button w-100 el-button--text m-t-2" to="/login">{{$t('BACK_TO_LOGIN')}}</router-link>
                        </div>
                    </div>

                </el-card>
            </div>
        </div>

	</div>
</template>

<script>
import FormResetPassword from '@/components/Form/FormResetPassword';
import SVGLogo from '@/assets/img/4-check-logo.svg';

export default {
	name: 'ResetPasswordView',

	components: {
		FormResetPassword,
		SVGLogo
	},

	computed: {
		token() {
			return this.$route.query.token;
		}
	},

	data() {
		return {
			hasToken: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('ResetPasswordView mounted', this.$route);

			if (this.$route.query.token) {
				if (this.$route.query.token !== '') {
					this.hasToken = true;
				} else {
					this.hasToken = false;
				}
			} else {
				this.hasToken = false;
			}
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
.view__reset-password {
	display: flex;
	align-items: center;
	justify-content: center;
	min-height: 100vh;

	&__logo {
		max-height: 4rem;
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

	.el-button {
		&.is-active {
			border: none;
		}
	}
}
</style>
