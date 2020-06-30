<!--
@component:         RegisterComplete
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-05
@modified:          2018-10-05
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

                        RegisterComplete

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
import Separator from '@/components/Misc/Separator';
import SVGLogo from '@/assets/img/4-check-logo.svg';

export default {
	name: 'RegisterComplete',

	components: {
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
			loading: false,
			token: ''
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			if (this.$route.params.token) {
				this.token = this.$route.params.token;
			}

			console.log('RegisterView mounted', this.$route.params, this.token);
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
