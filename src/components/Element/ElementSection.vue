<!--
@component:         ElementSection
@environment:       Hyprid
@description:       -
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-08
@modified:          2018-10-11
-->
<template>
    <div class="checklist__element-section" v-loading="loading">
        <draggable
            :list="entries"
            :move="checkEditOrder"
            :options="drapOptions"
            @update="handleChangeOrder">
            <ElementContainer
                v-for="element in entries" :key="element.id"
                :data="element"
                :open="open">
            </ElementContainer>
        </draggable>

        <div class="text-center m-t-1">
            <ButtonAddChecklistElement :context="context" :flat="true"></ButtonAddChecklistElement>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import ButtonAddChecklistElement from '@/components/Button/ButtonAddChecklistElement';
import checklistMixins from '@/shared/mixins/checklists';
import { Checkpoint } from '@/shared/classes/Checkpoint';
import draggable from 'vuedraggable';

export default {
	name: 'ElementSection',

	mixins: [checklistMixins],

	components: {
		ButtonAddChecklistElement,
		draggable
	},

	props: {
		data: {
			type: Object,
			required: true
		},

		open: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	data() {
		return {
			context: {
				id: this.data.id,
				type: 'sections'
			},
			entries: [],
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

		doUpdateCheckpoint(checkpoint) {
			return this.$store
				.dispatch('checkpoints/UPDATE_CHECKPOINT', checkpoint)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		init() {
			this.reloadSection();
			this.registerEvents();

			console.log('ElementSection mounted');
		},

		handleChangeOrder() {
			const REQUEST = [];

			this.entries.forEach((element, index) => {
				if (element.objectType === 'checkpoint') {
					const CHECKPOINT = new Checkpoint(element.object);
					CHECKPOINT.mandatory = CHECKPOINT.mandatory === 'true' ? 1 : 0;
					CHECKPOINT.index = index;
					REQUEST.push(this.doUpdateCheckpoint(CHECKPOINT));
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

		reloadSection() {
			this.loading = true;
			this.requestSectionEntries(this.data.id)
				.then(response => {
					this.loading = false;
					this.entries = response.data.data;

					// Sort elements by creation date
					this.entries = _.sortBy(response.data.data, element => {
						return element.createdAt;
					});
					// Sort elements by index
					this.entries = _.sortBy(response.data.data, element => {
						return element.object.index;
					});
				})
				.catch(err => {
					this.loading = false;
				});
		},

		registerEvents() {
			this.$eventbus.$on('section:' + this.context.id + ':refresh', payload => {
				this.reloadSection();
			});
		},

		unregisterEvents() {
			this.$eventbus.$off('section:' + this.context.id + ':refresh');
		}
	},

	destroyed() {
		this.unregisterEvents();
	}
};
</script>

<style lang="scss">
.checklist__element-section {
	background-color: $c-lighter-gray;
	padding: 0.8rem;

	.checklist__element {
		margin-top: 1rem;

		&:first-of-type {
			margin-top: 0;
		}
	}
}
</style>

