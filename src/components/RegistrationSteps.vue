<!--
@component:         RegistrationSteps
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-28
@modified:          2018-10-28
-->
<template>
    <div class="register__steps">
        <el-steps
            :active="activeStep"
            :process-status="currentStatus"
            align-center>
            <el-step :title="$t('GENERAL_INFORMATIONS')"></el-step>
            <el-step :title="$t('REGISTER')"></el-step>
            <el-step :title="$t('PAYMENT_DETAILS')"></el-step>
        </el-steps>

        <div class="steps-content m-t-2">
            <!-- Step 1 -->
            <div class="steps-content__item a-center" v-if="activeStep === 0">
                <ul class="list p-l-1">
                    <li class="list__item pos-r">
                        <q-icon name="done" class="m-r-small color-brand" style="position:absolute; left: -1.5rem; top: 0.1rem;"></q-icon>
                        <span>Vor Ablauf Ihres Gratismonats fallen keine Kosten an</span>
                    </li>
                    <li class="list__item pos-r">
                        <q-icon name="done" class="m-r-small color-brand" style="position:absolute; left: -1.5rem; top: 0.1rem;"></q-icon>
                        <span>Wir informieren Sie 3 Tage vor Ablauf des Probezeitraums</span>
                    </li>
                    <li class="list__item pos-r">
                        <q-icon name="done" class="m-r-small color-brand" style="position:absolute; left: -1.5rem; top: 0.1rem;"></q-icon>
                        <!-- <span>monatlich kündbar</span> -->
                        <span>Das Abo verlängert sich automatisch um 1 Monat.</span>
                    </li>
                    <li class="list__item pos-r">
                        <q-icon name="done" class="m-r-small color-brand" style="position:absolute; left: -1.5rem; top: 0.1rem;"></q-icon>
                        <span>Sie erhalten den vollen Systemumfang</span>
                    </li>
                </ul>
            </div>

            <!-- Step 2 -->
            <div class="steps-content__item a-center1" v-if="activeStep === 1">
                <FormRegister v-loading="loading" @register="onRegister" />
            </div>

            <!-- Step 3 -->
            <div class="steps-content__item a-center" v-if="activeStep === 2">
                <div class="steps-content__item-inner">
                    <!-- Generel informations -->
                    <div class="text-center" v-if="!payment">
                        <h3 class="headline">Legen Sie Ihre Zahlungsangaben fest</h3>
                        <p class="m-t-2">
                            Wenn Sie vor dem <strong>{{ $d(new Date(expiryDate), 'short') }}</strong> k&uuml;ndigen, wird Ihnen nichts in Rechnung gestellt.
                        </p>
                        <p class="m-t-1">
                            Wir werden Ihnen <strong>3 Tage vorher</strong> eine Erinnerung per E-Mail schicken.
                        </p>
                        <p class="m-t-1">
                            Keine Verpflichtungen. <br> Jederzeit online k&uuml;ndbar.
                        </p>
                    </div>

                    <div class="text-center m-t-2">
                        <div v-if="payment">
                            <!-- Credit card -->
                            <div v-if="payment === 1">
                                <div class="a-center">
                                    <div class="w-100--sm w-50--md">
                                        <h3 class="headline text-left">{{ $t('PAYMENT_PER_CREDITCARD') }}</h3>

                                        <!-- Stripe -->
                                        <FormStripe
                                            class="m-t-2"
                                            @token-created="onTokenCreated">
                                        </FormStripe>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice -->
                            <div v-if="payment === 2">
                                <h3 class="headline text-left">{{ $t('PAYMENT_PER_INVOICE') }}</h3>
                                <h3 class="headline text-left font--regular">{{ $t('BILLING_ADDRESS') }}</h3>
                                <FormCompanyProfile
                                    address-type="billing"
                                    :data="company"
                                    :addresses="addresses"
                                    :show-submit-button="false"
                                    @input="onInputForm"
                                    @validation="onFormValidation">
                                </FormCompanyProfile>
                            </div>

                            <!-- Order number -->
                            <q-field
                                class="m-b-1"
                                :class="{'q-field__order--cc': payment === 1}">
                                <q-input
                                    v-model="form.reference"
                                    :stack-label="$t('ORDER_NUMBER')">
                                </q-input>
                            </q-field>

                            <!-- Legals -->
                            <div class="a-center m-t-3">
                                <div class="w-50">
                                    <p class="font--small color-gray text-left" style="line-height: 0.9rem;">
                                        Durch Klicken auf die Schaltfläche "Mitgliedschaft beginnen" stimmen Sie unseren Nutzungsbedingungen zu und bestätigen,
                                        dass Sie über 18 Jahre alt sind. Weiterhin erkennen Sie die Datenschutzerklärung an.
                                        Sie erklären ausserdem Ihr Einverständnis zum sofortigen Beginn der Mitgliedschaft und zum Ausschluss einer
                                        Erstattung für den Fall, dass Sie vom Vertrag zurücktreten möchten.
                                        Ihr kostenloser Probezeitraum bleibt hiervon unberührt und Sie können trotzdem jederzeit online kündigen. 4-check AG wird Ihre
                                        Mitgliedschaft nach dem Ende Ihrer kostenlosen Probezeitraums automatisch fortführen und die Mitgliedsgebühr (14.90 €) bis
                                        zu Ihrer Kündigung jeden Monat von Ihrer gewählten Zahlungsart abbuchen.
                                    </p>
                                </div>
                            </div>

                            <!-- Submit payment information -->
                            <el-button
                                type="primary"
                                @click="onBtnClickSubscribe"
                                class="m-t-1"
                                style="line-height:1.2rem;"
                                :loading="loadingPayment">
                                Mitgliedschaft beginnen <br> kostenpflichtig nach Gratismonat
                            </el-button>

                            <!-- Reset payment details -->
                            <p class="text-center m-t-half">
                                <el-button type="text" @click="payment = 0">
                                    {{ $t('CHANGE_PAYMENT_DETAILS') }}
                                </el-button>
                            </p>
                        </div>

                        <!-- Choose for payment details -->
                        <div v-if="!payment">
                            <el-button type="primary" class="w-100" @click="payment = 1">{{ $t('CREDIT_DEBIT_CARD') }}</el-button>
                            <el-button type="primary" class="w-100 m-t-half" style="margin-left:0;" @click="payment = 2">{{ $t('INVOICE') }}</el-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Steps navigation -->
        <div class="steps-navigation m-t-2">
            <el-button style="display:flex;" type="secondary" @click="back" v-if="activeStep > 1">{{ $t('BACK') }}</el-button>
            <!-- <el-button type="primary" v-if="activeStep < 1 || (activeStep === 1 && user.id)" @click="next" :disabled="activeStep > 0 && currentStatus !== 'process'">{{ $t('CONTINUE') }}</el-button> -->
            <el-button type="primary" v-if="activeStep < 1" @click="next" :disabled="activeStep > 0 && currentStatus !== 'process'">{{ $t('CONTINUE') }}</el-button>
            <el-button type="primary" v-if="activeStep == 1 && user.id" @click="next" :disabled="activeStep > 0 && currentStatus !== 'process'">{{ $t('CONTINUE_WITH_REGISTER') }}</el-button>
        </div>
    </div>
</template>

<script>
import { date } from 'quasar';
import { Address } from '@/shared/classes/Address';
import { Payment } from '@/shared/classes/Payment';
import FormCompanyProfile from '@/components/Form/FormCompanyProfile';
import FormRegister from '@/components/Form/FormRegister';
import FormStripe from '@/components/Form/FormStripe';
import { getCountries } from '@/services/country';

export default {
	name: 'RegistrationSteps',

	components: {
		FormCompanyProfile,
		FormRegister,
		FormStripe
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		expiryDate() {
			const current = new Date();
			return date.addToDate(current, { days: this.trailPeriodInDays });
		},

		user() {
			return this.$store.state.user.data;
		}
	},

	data() {
		return {
			activeStep: 0,
			addresses: [],
			currentStatus: 'process',
			loading: false,
			loadingPayment: false,
			payment: 0,
			trailPeriodInDays: 30,
			form: {
				reference: ''
			},
			countries: getCountries()
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doCreateAdress() {
			const ADDRESS = new Address(this.addresses[0]);
			
			let countryCode = this.countries.filter(item => item.label == ADDRESS.country);
			ADDRESS.country = countryCode.length > 0 ? countryCode[0]['value'] : "DE";

			return this.$store
				.dispatch('companies/CREATE_ADDRESS', {
					id: this.company.id,
					data: ADDRESS
				})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doCreatePayment(obj) {
			obj = Object.assign({}, this.form, obj);
			const PAYMENT = new Payment(obj);

			let addr = this.addresses[0];
			PAYMENT['invoice_company'] = this.$session.get('company')['name'];
			PAYMENT['invoice_street'] = addr['line1'];
			PAYMENT['invoice_houseno'] = addr['line2'];
			PAYMENT['invoice_city'] = addr['city'];
			PAYMENT['invoice_postcode'] = addr['postalCode'];
			PAYMENT['invoice_country'] = addr['country'].length > 2 ? "DE" : addr['country'];

			console.log('doCreatePayment', PAYMENT);
			
			this.loadingPayment = true;
			return this.$http
				.post('/payment/create', PAYMENT)
				.then(response => {
					this.loadingPayment = false;

					// We dont need the stored data anymore
					this.$session.remove('user');
					this.$session.remove('company');

					this.$router.push({ path: '/' });

					return response;
				})
				.catch(err => {
					this.loadingPayment = false;
					return err;
				});
		},

		doLogin(auth) {
			this.loading = true;
			this.$store
				.dispatch('DO_LOGIN', {
					email: auth.email,
					password: auth.password
				})
				.then(response => {
					console.log('doLogin response', { response });
					this.loading = false;

					if (response.status === 200) {
						this.handleSuccessLogin(response);
					}
				})
				.catch(err => {
					console.log('doLogin err', { err });
					this.loading = false;
				});
		},

		init() {
			if (this.$session.get('company')) {
				this.$store.commit('SET_USER_COMPANY', this.$session.get('company'));
			}

			if (!this.$route.query.token) {
				this.addresses.push(new Address({}));
			}

			console.log('RegisterView mounted', this.$route.query);
		},

		back() {
			if (this.activeStep > 0) {
				this.activeStep--;
				// this.currentStatus = 'process';
			}
		},

		next() {
			if (this.activeStep < 3) {
				// this.currentStatus = 'finish';
				this.activeStep++;
			}
		},

		handleSuccessLogin(result) {
			console.log('handleSuccessLogin', result);

			// Set authorization header
			this.$http.defaults.headers.common['Authorization'] = `${result.data.token_type} ${
				result.data.access_token
			}`;

			// Get users company
			this.requestUserCompany(this.user.companyId).then(response => {
				// Go to next step
				this.next();
			});
		},

		onBtnClickSubscribe() {
			console.log('onBtnClickSubscribe');

			if (this.payment === 1) {
				this.$eventbus.$emit('form-stripe:create:token');
			}

			if (this.payment === 2) {
				this.$eventbus.$emit('form-company-profile:validate');
			}
		},

		onFormValidation(invalid) {
			console.log('onFormValidation', invalid);

			if (!invalid) {
				this.doCreateAdress();
				this.doCreatePayment({ method: 'invoice' });
			}
		},

		onInputForm(form) {
			this.addresses[0] = Object.assign({}, form.address);
		},

		onRegister(response) {
			console.log('onRegister', response);
			if (response[0].status === 201) {
				// this.currentStatus = 'finish';

				this.$session.set('user', response[0].data.data);
				this.$store.commit('SET_USER', response[0].data.data);
				this.doLogin({ email: response[1].email, password: response[1].password });
			}
		},

		onTokenCreated(data) {
			console.log('onTokenCreated', data);
			this.doCreatePayment({ token: data.token.id });
		},

		requestUserCompany(companyId) {
			return this.$store
				.dispatch('companies/GET_COMPANY', { id: companyId })
				.then(response => {
					this.$store.commit('SET_USER_COMPANY', response.data.data);
					this.$session.set('company', response.data.data);

					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
</script>

<style lang="scss">
.register__steps {
	.el-step__title {
		line-height: 1.3rem;
		margin-top: 0.5rem;
		hyphens: auto;
		word-break: break-word;
	}

	.el-step__description {
		line-height: 1rem;
		margin-top: 0.7rem;
	}
}

.steps-navigation {
	/*display: flex;*/
	text-align:right;
	justify-content: space-between;
}

.q-field__order--cc {
	margin-top: 1rem;

	@media screen and (min-width: $screen-md) {
		margin-left: 25%;
		width: 50%;
	}
}
</style>
