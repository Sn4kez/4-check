<!--
@component:         ListNavItems
@environment:       Hyprid
@description:       This component is used for list navigation on mobile and provide data for underlaying components
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-29
@modified:          2018-10-14
-->
<template>
    <div>
        <!-- List -->
        <q-list separator link no-border :sparse="sparse" class="list__nav-items p-t-0 p-b-0">
            <!-- Item -->
            <q-item v-for="(item, index) in items" :key="index" v-if="isRoleSufficent(item)"
                @click.native="onClickItem(item)"
                class="list__nav-item "
                :class="{'p-l-0 p-r-half': sparse}">
                <!-- Icon -->
                <q-item-side v-if="item.icon" :icon="item.icon" />

                <!-- Main content -->
                <q-item-main>
                    {{$t(item.name)}}
                </q-item-main>

                <!-- Icon right -->
                <q-item-side right>
                    <q-item-tile icon="keyboard_arrow_right"></q-item-tile>
                </q-item-side>
            </q-item>
        </q-list>

        <!-- Modal -->
        <q-modal
            v-model="modalOpened"
            maximized
            class="list__nav-modal">
            <q-modal-layout>
                <q-toolbar class="text-black" slot="header" inverted>
                    <q-btn
                        flat
                        round
                        icon="close"
                        @click="modalOpened = false">
                    </q-btn>

                    <q-toolbar-title>{{$t(currentItem.name)}}</q-toolbar-title>
                </q-toolbar>

                <div class="p-t-2 p-b-2 p-l-1 p-r-1">
                    <component :is="loadComponent" v-bind="currentProperties"></component>
                </div>

            </q-modal-layout>
        </q-modal>
    </div>
</template>

<script>
import navigationMixin from '@/shared/mixins/navigation';
import { forEach } from 'lodash';

export default {
	name: 'ListNavItems',

	mixins: [navigationMixin],

	props: {
		items: {
			type: Array,
			required: true
		},

		sparse: {
			type: Boolean,
			required: false,
			default: true
		}
	},

	computed: {
		currentProperties() {
			let props = new Object();

			if (this.currentItem.component && this.currentItem.component.props.length) {
				_.forEach(this.currentItem.component.props, (value, key) => {
					props[value.name] = value.data;
				});
			}

			console.log('props', props);

			return props;
		},

		loader() {
			if (!this.currentItem.component) {
				return null;
			}

			return () => import(`@/components/${this.currentItem.component.name}`);
		}
	},

	data() {
		return {
			currentItem: {},
			loadComponent: null,
			modalOpened: false
		};
	},

	mounted() {
		console.log('ListNavItems mounted');
	},

	methods: {
		generateProperties(item) {},

		handleLoadComponent() {
			this.loader()
				.then(() => {
					this.loadComponent = () => this.loader();
				})
				.catch(() => {
					this.loadComponent = null; //() => import('./ListEmpty');
				});
		},

		onClickItem(item) {
			console.log('onClickItem', item);

			this.currentItem = item;

			if (this.currentItem.component) {
				this.modalOpened = true;
				this.handleLoadComponent();
			}

			if (this.currentItem.route) {
				this.$router.push(this.currentItem.route);
			}
		}
	}
};
</script>

<style lang="scss">
.list__nav-modal {
	z-index: 2000;
}
</style>
