<!--
@component:         ElementAuditSection
@environment:       Hyprid
@description:       -
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-02
@modified:          2018-10-11
-->
<template>
    <div class="checklist__element-section" v-loading="loading">
        <ElementAuditContainer
            v-for="entry in entries.checks" :key="entry.id"
            :data="entry"
            :audit="audit"
            :open="open">
        </ElementAuditContainer>

        <ElementAuditContainer
            v-for="section in entries.sections" :key="section.id"
            :data="section"
            :audit="audit"
            :open="open">
        </ElementAuditContainer>
    </div>
</template>

<script>
import auditMixins from '@/shared/mixins/audits';
import checklistMixins from '@/shared/mixins/checklists';

export default {
	name: 'ElementAuditSection',

	mixins: [auditMixins],

	props: {
		audit: {
			type: Object,
			required: false,
			default: function() {
				return {};
			}
		},

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
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.reloadSection();
			// this.registerEvents();

			console.log('ElementSection mounted', this.data);
		},

		reloadSection() {
			this.loading = true;

			this.requestSectionEntries(this.audit.id)
				.then(response => {
					this.loading = false;
					this.entries = response.data;
					console.log('requestSectionEntries', response, this.entries);
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

		requestSectionEntries(auditId) {
			return this.$store
				.dispatch('audits/GET_AUDIT_ENTRIES', { id: auditId, sectionId: this.data.object.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
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

