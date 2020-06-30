<!--
@component:         BreadcrumbDirectories
@environment:       Hyprid
@description:       This component handle the breadcrumb navigation for directories and archives
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-21
@modified:          2018-09-26
-->
<template>
    <div class="breadcrumbs__container">
        <ul class="breadcrumbs">
            <!-- Root -->
            <li class="breadcrumbs__item m-l-0">
                <router-link :to="{path: baseUrl}"
                    class="breadcrumbs__item-link"
                    :title="$t('ROOT_DIRECTORY')">
                    <q-icon name="home"></q-icon>
                    <span class="m-l-small">{{rootDirectory.name}}</span>
                    <q-icon name="chevron_right" v-if="breadcrumbs.path"></q-icon>
                </router-link>
            </li>
            <!-- Path -->
            <li v-for="(item, index) in breadcrumbs.path" :key="index"
                class="breadcrumbs__item">

                <router-link
                    :to="{path: baseUrl + '/' + item.id}"
                    class="breadcrumbs__item-link">
                    <span>{{item.name}}</span>
                    <q-icon name="chevron_right"></q-icon>
                </router-link>
            </li>
            <!-- Current Directory -->
            <li class="breadcrumbs__item is-last">
                <span>{{breadcrumbs.name}}</span>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
	name: 'BreadcrumbDirectories',

	props: {
		breadcrumbs: {
			type: Object,
			required: true
		},

		directories: {
			type: Array,
			required: true
		},

		isArchive: {
			type: Boolean,
			required: false,
			default: false
		},

		rootDirectory: {
			type: Object,
			required: true
		}
	},

	computed: {
		baseUrl() {
			if (this.isArchive) {
				return '/checklists/archive';
			} else {
				return '/checklists/directories';
			}
		}
	}
};
</script>

<style lang="scss">
.breadcrumbs {
	list-style: none;
	margin: 0;
	padding: 0;
	flex-wrap: wrap;

	display: flex;

	&__item {
		font-size: 0.9rem;
		margin-left: 0.8rem;

		display: flex;
		align-items: center;
	}

	&__item-link {
		&.is-active--exact {
			color: $c-gray;
			cursor: initial;
		}
	}
}
</style>
