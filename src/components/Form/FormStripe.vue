<!--
@component:         FormStripe
@environment:       Hyprid
@description:       This component handle the stripe token generation.

@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-28
@modified:          2018-09-28
-->
<template>
    <el-form ref="payment-form" id="payment-form" class="m-t-2" @submit.native.prevent="onSubmit" v-loading="loading">
        <div class="">
            <div id="card-element">
            <!-- A Stripe Element will be inserted here. -->
            </div>

            <!-- Used to display Element errors. -->
            <div id="card-errors" role="alert"></div>
        </div>

        <!-- <el-button type="primary" native-type="submit" class="m-t-2">Submit Payment</el-button> -->
    </el-form>
</template>

<script>
export default {
	name: 'FormStripe',

	data() {
		return {
			apiKey: 'sk_live_hgB02m8l5NIHjpUqnGrF8WeI',
			cardStyle: {
				base: {
					// Add your base input styles here. For example:
					fontSize: '16px',
					color: '#E21E39',
					fontSmoothing: 'antialiased',
					'::placeholder': {
						color: '#748593'
					}
				},
				invalid: {
					color: '#E21E39',
					':focus': {
						color: '#303238'
					}
				}
			},
			loading: false,
			stripe: {},
			stripeCard: {},
			stripeElements: {}
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.loading = true;
			const $stripe = document.querySelector('#stripe-js');
			console.log($stripe);

			if (!$stripe) {
				const element = document.createElement('script');
				element.id = 'stripe-js';
				element.src = 'https://js.stripe.com/v3/';

				document.body.append(element);
				console.log(element, window.Stripe);
			}

			setTimeout(() => {
				console.log('2.', $stripe, window.Stripe);
				if (window.Stripe) {
					this.stripe = window.Stripe(this.apiKey);
					this.stripeElements = this.stripe.elements();
					console.log(this.stripe, this.stripeElements);

					// Create an instance of the card Element.
					this.stripeCard = this.stripeElements.create('card', { style: this.cardStyle });

					// Add an instance of the card Element into the `card-element` <div>.
					this.stripeCard.mount('#card-element');

					this.stripeCard.addEventListener('change', function(event) {
						const displayError = document.getElementById('card-errors');
						if (event.error) {
							displayError.textContent = event.error.message;
						} else {
							displayError.textContent = '';
						}
					});
				}
				this.loading = false;
			}, 1000);

			this.registerEvents();
		},

		onSubmit() {
			this.stripe.createToken(this.stripeCard).then(result => {
				console.log('onSubmit', result);

				if (result.error) {
					// Inform the customer that there was an error.
					const errorElement = document.getElementById('card-errors');
					errorElement.textContent = result.error.message;
				} else {
					// Send the token to your server.
					this.$emit('token-created', { token: result.token });
				}
			});
		},

		registerEvents() {
			this.$eventbus.$on('form-stripe:create:token', () => {
				this.onSubmit();
			});
		}
	}
};
</script>
