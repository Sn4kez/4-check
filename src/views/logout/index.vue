<template>
	<div class="logout">

        <div class="box-container">
            <div class="box-container__inner">
                <el-card>

                    <div class="text-center m-t-1 m-b-1">
                        <SVGLogo class="logout__logo" />
                    </div>

                    <div class="p-l-1 p-r-1">
                        <el-alert
                            type="success"
                            :title="$t('LOGOUT_SUCCESSFULLY')"
                            :description="$t('THANK_YOU')"
                            show-icon
                            :closable="false"
                            class="m-t-2">
                        </el-alert>

                        <div class="m-t-2 m-b-1 text-center">

                            <router-link
                                :to="{path: '/login'}"
                                class="el-button el-button--primary w-100">
                                {{ $t('LOGIN') }}
                            </router-link>
                        </div>
                    </div>

                </el-card>
            </div>
        </div>

	</div>
</template>

<script>
import Separator from '@/components/Misc/Separator';
import SVGLogo from '@/assets/img/4-check-logo.svg';

export default {
	name: 'LogoutView',

	components: {
		Separator,
		SVGLogo
	},

	data() {
		return {};
	},

	mounted() {
		console.log('Logout mounted');
		this.deleteAuth();

		if (!this.$route.query.success) {
			this.$router.push({ path: '/logout', query: { success: true } });
			window.location.reload();
		}
	},

	methods: {
		deleteAuth() {
			this.$session.remove('access_token');
			this.$session.remove('refresh_token');
			this.$session.remove('expires_in');
			this.$session.remove('token_type');
			this.$session.remove('expiry_date');
			this.$localStorage.remove('access_token');
			this.$localStorage.remove('refresh_token');
			this.$localStorage.remove('expires_in');
			this.$localStorage.remove('token_type');
			this.$localStorage.remove('expiry_date');
			this.$localStorage.remove('username');
			this.$localStorage.remove('password');
			this.$localStorage.remove('tour');
			this.$localStorage.remove('lastlogin');
			// localStorage.removeItem('lastlogin');
			localStorage.removeItem('locale'); //REMOVE LOCALE FROM STORAGE
			delete this.$http.defaults.headers.common['Authorization'];
		}
	}
};
</script>

<style lang="scss">
.logout {
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
}
</style>
