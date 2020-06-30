<template>
    <div class="table__directory-details">
        <dl class="flex--md w-100">
            <dt>{{ $t('NAME') }}</dt>
            <dd>{{data.object.name}}</dd>
        </dl>
        <dl class="flex--md w-100">
            <dt>{{ $t('CREATED_BY') }}</dt>
            <dd>
                <span v-if="data.object.createdBy && users.length">
                    {{getUserById(data.object.createdBy).lastName}}, {{getUserById(data.object.createdBy).firstName}}
                </span>
            </dd>
        </dl>
        <dl class="flex--md w-100">
            <dt>{{ $t('CREATED_AT') }}</dt>
            <dd>{{ $d(new Date(data.object.createdAt), 'long')}}</dd>
        </dl>
        <dl class="flex--md w-100">
            <dt>{{ $t('LAST_MODIFIED_BY') }}</dt>
            <dd>
                <span v-if="data.object.lastUpdatedBy && users.length">
                    {{getUserById(data.object.lastUpdatedBy).lastName}}, {{getUserById(data.object.lastUpdatedBy).firstName}}
                </span>
            </dd>
        </dl>
        <dl class="flex--md w-100">
            <dt>{{ $t('LAST_MODIFIED') }}</dt>
            <dd>{{ $d(new Date(data.object.updatedAt), 'long')}}</dd>
        </dl>
        <dl class="flex--md w-100">
            <dt>{{ $t('LAST_USAGE_BY') }}</dt>
            <dd>
                <span v-if="data.object.lastUsedBy && users.length">
                    {{getUserById(data.object.lastUsedBy).lastName}}, {{getUserById(data.object.lastUsedBy).firstName}}
                </span>
            </dd>
        </dl>
        <dl class="flex--md w-100">
            <dt>{{ $t('LAST_USAGE') }}</dt>
            <dd>
                <span v-if="data.object.usedAt">
                    {{ $d(new Date(data.object.usedAt), 'long')}}
                </span>
            </dd>
        </dl>
    </div>
</template>

<script>
import usersMixin from '@/shared/mixins/users';

export default {
	name: 'TableDirectoryDetails',

	mixins: [usersMixin],

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		users() {
			return this.$store.state.users.users;
		}
	}
};
</script>

<style lang="scss">
.table__directory-details {
	@media screen and (min-width: $screen-md) {
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	dt {
		@media screen and (min-width: $screen-md) {
			margin-right: 2rem;
			width: 50%;
			text-align: right;
		}
	}
}
</style>
