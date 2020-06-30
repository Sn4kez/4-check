<!--
@component:         ListChecklistElements
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    By click on an item an event is send over the global eventbus to the checklist.
                    Further actions where made at the checklist component.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-07
@modified:          2018-09-12
-->
<template>
    <div class="list__checklist-elements">
        <!-- Basic elements -->
        <q-list
            no-border
            inset-separator
            sparse
            v-if="data.elements.basic.length">
            <q-list-header>{{ $t('BASIC_ELEMENTS') }}</q-list-header>
            <q-item
                link
                tag="a"
                v-for="item in data.elements.basic" :key="item.id"
                @click.native="onClickItem(item, data.context)"
                class="color-gray">
                <q-item-side :icon="item.icon"></q-item-side>
                <q-item-main>{{$t(item.label)}}</q-item-main>
                <q-item-side icon="add_circle_outline" right></q-item-side>
            </q-item>
        </q-list>

        <!-- Additional elements -->
        <q-list
            no-border
            inset-separator
            sparse
            v-if="data.elements.extensions.length"
            :class="{'p-t-0': !data.elements.basic.length}">
            <q-list-header>{{ $t('ADDITIONAL_ELEMENTS') }}</q-list-header>
            <q-item
                link
                tag="a"
                v-for="item in data.elements.extensions" :key="item.id"
                @click.native="onClickItem(item, data.context)"
                class="color-gray">
                <q-item-side :icon="item.icon"></q-item-side>
                <q-item-main>{{$t(item.label)}}</q-item-main>
                <q-item-side icon="add_circle_outline" right></q-item-side>
            </q-item>
        </q-list>
    </div>
</template>

<script>
export default {
	name: 'ListChecklistElements',

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	methods: {
		onClickItem(item, context) {
			this.$eventbus.$emit('checklist:add:element', { item: item, context: context });
		}
	}
};
</script>

<style lang="scss">
</style>
