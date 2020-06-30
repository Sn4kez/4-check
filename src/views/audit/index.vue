<!--
@component:         AuditIndexView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-25
@modified:          2018-09-25
-->
<template>
    <div class="view__audit main-container">
        Audit
    </div>
</template>

<script>
export default {
	name: 'AuditIndexView',

	components: {},

	data() {
		return {};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('AuditIndexView mounted');
		}
	}
};
</script>
