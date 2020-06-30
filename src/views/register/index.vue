<!--
@component:         RegisterView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-06-09
@modified:          2018-09-28
-->
<template>
    <div class="register">

        <div class="box-container box-container--wide">
            <div class="box-container__inner">
                <el-card>
                    <div class="text-center m-t-1 m-b-1">
                        <SVGLogo class="register__logo" />
                    </div>

                    <h2 class="register__headline" v-t="'CREATE_ACCOUNT'"></h2>

                    <div class="p-l-1 p-r-1">
                        <!-- Register form -->
                        <FormRegister v-if="token"
                            :data="invitation"
                            :valid-invitation="validInvitation"
                            v-loading="loading" />

                        <!-- Register steps -->
                        <RegistrationSteps v-if="!token"></RegistrationSteps>

                        <Separator
                            :text="$t('ALREADY_REGISTERED')"
                            show-line
                            class="m-t-1 m-b-1"/>

                        <router-link class="el-button w-100 el-button--text" to="/login">{{$t('LOGIN')}}</router-link>
                    </div>
                </el-card>
            </div>
        </div>

	</div>
</template>

<script>
import Vue from 'vue';
import VuePhoneNumberInput from 'vue-phone-number-input';
Vue.component('vue-phone-number-input', VuePhoneNumberInput);


import FormRegister from '@/components/Form/FormRegister';
import Separator from '@/components/Misc/Separator';
import SVGLogo from '@/assets/img/4-check-logo.svg';

import RegistrationSteps from '@/components/RegistrationSteps';
import 'vue-phone-number-input/dist/vue-phone-number-input.css';



export default {
	name: 'RegisterView',

	components: {
		FormRegister,
		RegistrationSteps,
		Separator,
		SVGLogo
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		user() {
			return this.$store.state.user.data;
		}
	},

	data() {
		return {
			invitation: {},
			loading: false,
			token: '',
			validInvitation: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			if (this.$route.query.token) {
				this.token = this.$route.query.token;

				this.loading = true;
				this.requestInvitation(this.token)
					.then(response => {
						this.loading = false;
						this.invitation = response.data.data;
						this.validInvitation = true;
					})
					.catch(err => {
						this.loading = false;
					});
			}

			console.log('RegisterView mounted', this.$route.query, this.token);
		},

		requestInvitation(token) {
			return this.$store
				.dispatch('invitations/GET_INVITATION', { token: token })
				.then(response => {
					this.invitation = response.data.data;
					return response;
				})
				.catch(err => {
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
.register {
	display: flex;
	align-items: center;
	justify-content: center;
	min-height: 100vh;

	&__logo {
		max-height: 4rem;
	}

	&__headline {
		font-size: 1.5rem;
		margin-bottom: 0.6rem;
		margin-top: 2rem;
		text-align: center;
	}

	.el-card {
		@media screen and (max-width: $screen-md) {
			border: none;
			box-shadow: none;
		}
	}
}
</style>
