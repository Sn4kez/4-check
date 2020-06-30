<!--
@component:         HelpIndexView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-18
@modified:          2018-10-18
-->
<template>
    <div class="view__help main-container">
        <!-- <p class="m-1 m-t-2">
            <a href="https://4-check.zendesk.com" target="_blank"
                class="a-center q-btn inline relative-position q-btn-item non-selectable w-100--sm q-btn-rectangle q-focusable q-hoverable bg-primary text-white">
                {{$t('VISIT_OUR_HELPDESK')}}
            </a>
        </p>

        <p class="m-1 m-t-2">
        	<a href="#" @click="startFeatureTour" class="a-center q-btn inline relative-position q-btn-item non-selectable w-100--sm q-btn-rectangle q-focusable q-hoverable bg-primary text-white">Einführungstour starten</a>
        </p> -->

        <div>
        	<a href="https://4-check.zendesk.com" target="_blank"
                class="a-center q-btn inline relative-position q-btn-item non-selectable w-100--sm q-btn-rectangle q-focusable q-hoverable bg-primary text-white">
                {{$t('VISIT_OUR_HELPDESK')}}
            </a>
            <a @click="startFeatureTour" class="a-center q-btn inline relative-position q-btn-item non-selectable w-100--sm q-btn-rectangle q-focusable q-hoverable bg-primary text-white feature-button">{{$t('HELP_TOUR.START')}}</a>
        </div>

        <!-- tour templates -->
	    <div>
	    	<div id="v-step-0"></div>
		    <div class="v-step-1"></div>
		    <div data-v-step="2"></div>

		    <!-- <v-tour name="myTour" :steps="steps"></v-tour> -->
		    <v-tour name="myTour" :steps="steps" :options="myOptions" :callbacks="myCallbacks">
			  <template slot-scope="tour">
			    <transition name="fade">
			      <v-step
			        v-if="tour.currentStep === index"
			        v-for="(step, index) of tour.steps"
			        :key="index"
			        :step="step"
			        :previous-step="tour.previousStep"
			        :next-step="tour.nextStep"
			        :stop="tour.stop"
			        :is-first="tour.isFirst"
			        :is-last="tour.isLast"
			        :labels="tour.labels"
			      >
			      </v-step>
			    </transition>
			  </template>
			</v-tour>

			<!-- mobile tour -->
			<v-tour name="mobileTour" :steps="mobsteps" :options="myOptions" :callbacks="myCallbacks">
			  <template slot-scope="tour">
			    <transition name="fade">
			      <v-step
			        v-if="tour.currentStep === index"
			        v-for="(step, index) of tour.steps"
			        :key="index"
			        :step="step"
			        :previous-step="tour.previousStep"
			        :next-step="tour.nextStep"
			        :stop="tour.stop"
			        :is-first="tour.isFirst"
			        :is-last="tour.isLast"
			        :labels="tour.labels"
			      >
			      </v-step>
			    </transition>
			  </template>
			</v-tour>
	    </div>

    </div>

</template>

<script>
import i18n from '@/i18n/index';

export default {
	name: 'HelpIndexView',

	data() {
		return {
			helpCenterUrl: 'https://static.zdassets.com/ekr/snippet.js?key=',
			token: 'e4d9ba17-2dc8-4069-b88e-9d511a0f2da5',
			myOptions: {
				useKeyboardNavigation: false,
				labels: {
					buttonSkip: i18n.t('HELP_TOUR.BUTTON_SKIP'),
					buttonPrevious: i18n.t('HELP_TOUR.BUTTON_PREVIOUS'),
					buttonNext: i18n.t('HELP_TOUR.BUTTON_NEXT'),
					buttonStop: i18n.t('HELP_TOUR.BUTTON_STOP'),
				}
			},
			steps: [
				{
					// Dashboard
					target: '#sidebar_DASHBOARD',
					content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.DASHBOARD.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.DASHBOARD.BODY') + '</div></slot>',
					params: {
						placement: 'left'
					}
				},
				{
					// Checklist
					target: '#sidebar_CHECKLISTS',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.CHECKLIST.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.CHECKLIST.BODY') + '</div></slot>',
					params: {
						placement: 'left'
					}
				},
				{
					// Tasks
					target: '#sidebar_TASKS',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.TASKS.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.TASKS.BODY') + '</div></slot>',
					params: {
						placement: 'left'
					}
				},
				{
					// Location
					target: '#sidebar_LOCATIONS',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.LOCATION.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.LOCATION.BODY') + '</div></slot>',
					params: {
						placement: 'left'
					}
				},
				{
					// Users
					target: '#sidebar_USERS',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.USERS.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.USERS.BODY') + '</div></slot>',
					params: {
						placement: 'left'
					}
				},
				{
					// Help
					target: '#sidebar_HELP',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.HELP.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.HELP.BODY') + '</div></slot>',
					params: {
						placement: 'left'
					}
				},
				{
					// Notifications
					target: '.el-badge',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.NOTIFICATIONS.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.NOTIFICATIONS.BODY') + '</div></slot>',
					params: {
						placement: 'top'
					}
				},
				{
					// Quick Start
					target: '.app-topbar__button-bar .hide-sm:first-child button:first-child',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.QUICK_START.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.QUICK_START.BODY') + '</div></slot>',
					params: {
						placement: 'top'
					}
				},
				{
					// End
					target: 'header',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.END.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.END.BODY') + '</div></slot>',
					params: {
						placement: 'top'
					}
				}

			],

			mobsteps: [
				{
					// Dashboard
					target: '#sidebar_DASHBOARD',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.DASHBOARD.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.DASHBOARD.BODY') + '</div></slot>',
				},
				{
					// Checklist
					target: '#appbar_CHECKLISTS',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.CHECKLIST.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.CHECKLIST.BODY') + '</div></slot>',
					params: {
						placement: 'bottom'
					}
				},
				{
					// Tasks
					target: '#appbar_TASKS',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.TASKS.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.TASKS.BODY') + '</div></slot>',
					params: {
						placement: 'bottom'
					}
				},
				{
					// Location
					target: '#appbar_LOCATIONS',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.LOCATION.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.LOCATION.BODY') + '</div></slot>',
					params: {
						placement: 'bottom'
					}
				},
				/*{
					// Users
					target: '#appbar_Benutzer',
					content: '<slot name="header"><div class="v-step__header"> Benutzer verwalten</div><div><p> Unter der <b>Benutzerverwaltung</b> können Sie die Benutzer in Ihrem System administrieren. Zusätzlich können Sie weitere Benutzer hinzufügen und die Berechtigungen verwalten.</p></div></slot>',
					params: {
						placement: 'bottom'
					}
				},*/
				/*{
					// Help
					target: '#appbar_Hilfe',
					content: '<slot name="header"><div class="v-step__header"> Hilfe</div><div><p> Wenn Sie <b>Hilfe</b> benötigen, finden Sie hier unsere <b>Wissensdatenbank</b>. Außerdem können Sie uns jederzeit unter support@4-check.com schreiben.</p></div></slot>',
					params: {
						placement: 'bottom'
					}
				},*/
				{
					// Notifications
					target: 'header .q-toolbar button',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.NOTIFICATIONS.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.NOTIFICATIONS.BODY') + '</div></slot>',
					params: {
						placement: 'top'
					}
				},
				{
					// Quick Start
					target: '.q-page-sticky button',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.QUICK_START.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.QUICK_START.BODY') + '</div></slot>',
					params: {
						placement: 'right'
					}
				},
				{
					// End
					target: 'header',
                    content: '<slot name="header"><div class="v-step__header">' + i18n.t('HELP_TOUR.END.HEADER') + '</div><div>' + i18n.t('HELP_TOUR.END.BODY') + '</div></slot>',
					params: {
						placement: 'top'
					}
				}

			],

			myCallbacks: {
				onStop: this.tourStopCallback
			}
		};
	},

	mounted() {
		this.init();
	},

	methods: {

		tourStopCallback(){

		},

		init() {
			// this.initHelpCenter();
		},

		initHelpCenter() {
			const element = document.createElement('script');
			element.id = 'ze-snippet';
			element.src = this.helpCenterUrl + this.token;

			const head = document.querySelector('head');
			// document.head.append(element);
			// head.appendChild(element);

			console.log(element, head);
		},

		startFeatureTour(){

			var ww = window.innerWidth;

			if(ww > 990){
				this.$tours['myTour'].start();
			}else{
				this.$tours['mobileTour'].start();
			}

		}
	}
};
</script>

<style lang="scss">
@media (max-width:990px){
	.feature-button{
		margin-top:20px;
		margin-left:0px!important;
	}

	.v-step__header{background-color:#454d5d;border-top-left-radius:3px;border-top-right-radius:3px;margin:-1rem -1rem .5rem;padding:.5rem;}
	.v-step{width:400px;z-index:99999;filter: none!important;}
	.v-step[x-placement="right"]{
		bottom:350px!important;
	}
	.v-step[x-placement="left"]{
		bottom:420px!important;
	}
	.v-step[x-placement="top"]{
		bottom:455px!important;
		top:auto!important;
	}
	.v-step button{
		border:1px solid #4fc08d!important;
	}
	.v-step button:last-child{
		background-color: #4fc08d;
	}
	.v-step__buttons button{
		margin-top:5px!important;
		margin-bottom:5px!important;
	}
	@media(min-width:2500px)

	{

	.v-step p{
	font-size:20px !important;
	letter-spacing:2px;
	font-weight:normal;
	}


	}

}
@media (min-width: 601px){
	.feature-button{
		margin-left:20px;
	}
}


.v-step__header{background-color:#454d5d;border-top-left-radius:3px;border-top-right-radius:3px;margin:-1rem -1rem .5rem;padding:.5rem;}
.v-step{width:400px;z-index:999;filter: none!important;}
.v-step[x-placement="right"]{
	left:275px!important;
}
.v-step button{
	border:1px solid #4fc08d!important;
}
.v-step button:last-child{
	background-color: #4fc08d;
}

@media(min-width:2500px)

{

.v-step p{
font-size:20px !important;
letter-spacing:2px;
font-weight:normal;
}


}

.v-step__buttons button{
		margin-top:5px!important;
		margin-bottom:5px!important;
	}



</style>
