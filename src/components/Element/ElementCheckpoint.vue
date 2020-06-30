<!--
@component:         ElementCheckpoint
@environment:       Hyprid
@description:       -
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-08
@modified:          2018-09-13
-->
<template>
    <div class="checklist__element-checkpoint">
        <div class="p-1">
            <FormEditQuestionChoice v-if="data.evaluationSchemeType === 'App\\ChoiceScheme'"
                :data="data">
            </FormEditQuestionChoice>

            <FormEditQuestionValue v-if="data.evaluationSchemeType === 'App\\ValueScheme'"
                :data="data">
            </FormEditQuestionValue>
        </div>

        <div v-loading="loading">
            <div class="b-t-1--lighter m-t-1 p-1 bg-color-light-gray" v-if="entries.length" >
                <!-- <ElementContainer
                    v-for="(element, index) in entries" :key="element.id"
                    :data="element"
                    :class="{'is-first': index === 0}">
                </ElementContainer> -->
                <draggable
		            :list="entries"
		            :move="checkEditOrder"
		            :options="drapOptions"
		            @update="handleChangeOrder">
		            <ElementContainer
	                    v-for="(element, index) in entries" :key="element.id"
	                    :data="element"
	                    :class="{'is-first': index === 0}">
	                </ElementContainer>
		        </draggable>
            </div>
        </div>

        <div class="b-t-1--lighter text-center p-t-half p-b-half" :class="{'m-t-1': !entries.length}">
            <ButtonAddChecklistElement :context="context" :flat="true"></ButtonAddChecklistElement>
        </div>
    </div>
</template>

<script>
import ButtonAddChecklistElement from '@/components/Button/ButtonAddChecklistElement';
import FormEditQuestionChoice from '@/components/Form/FormEditQuestionChoice';
import FormEditQuestionValue from '@/components/Form/FormEditQuestionValue';
import checkpointMixins from '@/shared/mixins/checkpoints';
import draggable from 'vuedraggable';
import axios from 'axios';
import {
	Extension,
	LocationExtension,
	NotefieldExtension,
	ParticipantExtension,
	PictureExtension,
	SignatureExtension,
	TextfieldExtension
} from '@/shared/classes/Extension';

export default {
	name: 'ElementCheckpoint',

	mixins: [checkpointMixins],

	components: {
		ButtonAddChecklistElement,
		FormEditQuestionChoice,
		FormEditQuestionValue,
		draggable
	},

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	data() {
		return {
			entries: [],
			context: {
				id: this.data.id,
				type: 'checkpoints'
			},
			loading: false,
			drapOptions: {
				handle: '.drag__handler'
			},
			editOrder: true
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		checkEditOrder(evt) {
			return this.editOrder;
		},

		doUpdateExtension(extension) {
			return this.$store
				.dispatch('extensions/UPDATE_EXTENSION', extension)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		handleChangeOrder() {
			const REQUEST = [];

			this.entries.forEach((element, index) => {
				// console.log('CHECK ME: ', index)
				// console.log(element)
				if (element.objectType === 'extension') {
					// const CHECKPOINT = new Checkpoint(element.object);
					// CHECKPOINT.mandatory = CHECKPOINT.mandatory === 'true' ? 1 : 0;
					// CHECKPOINT.index = index;
					// REQUEST.push(this.doUpdateCheckpoint(CHECKPOINT));

					let EXTENSION = {};
					const data = Object.assign({}, element.object);
					data.index = index + 1;
					data.data = Object.assign({}, element.object.object);

					switch (element.object.type) {
						case 'textfield':
							if (element.object.object.fixed) {
								EXTENSION = new TextfieldExtension(data);
							} else {
								EXTENSION = new NotefieldExtension(data);
							}
							break;
						case 'picture':
							if (element.object.object.type === 'signature') {
								EXTENSION = new SignatureExtension(data);
							} else {
								EXTENSION = new PictureExtension(data);
							}
							break;
						case 'location':
							data.data.fixed = data.data.fixed ? 1 : 0;
							EXTENSION = new LocationExtension(data);
							break;
						case 'participant':
							EXTENSION = new ParticipantExtension(data);
							break;
					}
					// console.log(EXTENSION)

					/*let toSend = {}
					toSend['id'] = EXTENSION['id']
					toSend['objectType'] = "extension"
					toSend['object'] = {}
					toSend['object'] = EXTENSION
					toSend['object']['object'] = EXTENSION['data']
					console.log(toSend)

					element.object.index = index*/

					REQUEST.push(this.doUpdateExtension(EXTENSION));

				}
			});

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						//
					})
				)
				.catch(err => {
					//
				});
		},

		init() {
			this.reloadCheckpoints();
			this.registerEvents();

			console.log('ElementCheckpoint mounted');
		},

		reloadCheckpoints() {
			this.loading = true;
			this.requestCheckpointEntries(this.data.id)
				.then(response => {
					this.loading = false;
					// this.entries = response.data.data;
					
					let dd = response.data.data;

					let sortByIndex = false;

					let someElem = dd.length > 0 ? dd[0] : {};

					dd.map(elem => {
						if(elem.object.index != someElem.object.index){
							sortByIndex = true;
						}
					})

					if(sortByIndex){
						/*dd = _.sortBy(dd, element => {
					        return element.object.index;
					    })*/
					    dd.sort(function(a, b){
							return parseInt(a.object.index) > parseInt(b.object.index) ? 1 : -1;
						})
					}else{
						/*dd = _.sortBy(dd, element => {
					        return element.object.createdAt;
					    })*/
					    dd.sort(function(a, b){
							return a.object.createdAt > b.object.createdAt ? 1 : -1;
						})
					}

					this.entries = dd;

					return response;

				})
				.catch(err => {
					this.loading = false;
				});
		},

		registerEvents() {
			this.$eventbus.$on('checkpoint:' + this.context.id + ':refresh', payload => {
				this.reloadCheckpoints();
			});
		},

		unregisterEvents() {
			this.$eventbus.$off('checkpoint:' + this.context.id + ':refresh');
		}
	},

	destroyed() {
		this.unregisterEvents();
	}
};
</script>

<style lang="scss">
.checklist__element-checkpoint {
	.checklist__element {
		margin-top: 0.4rem;

		&:first-of-type {
			margin-top: 0;
		}
	}
}
</style>
