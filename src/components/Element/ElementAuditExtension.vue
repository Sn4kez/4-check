<!--
@component:         ElementAuditExtension
@environment:       Hyprid
@description:       We use this component to list all extensions inside of an checkpoint while user fill out audit.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-02
@modified:          2018-10-18
-->
<template>
    <el-card class="checklist__audit-element" :class="{'is-open': isOpen}">
        <!-- Header -->
        <div slot="header" class="checklist__element-header">
            <div class="checklist__element-headline">
                <slot name="title">

                    <div class="checklist__element-headline-inner">
                        <!-- Textfield -->
                        <span v-if="data.object.type === 'textfield' && data.object.object.fixed">
                            <el-tooltip class="item" effect="dark" :content="$t('TEXTFIELD')" placement="top">
                                <q-icon name="text_fields" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('TEXTFIELD') }}</span>
                        </span>

                        <!-- Notefield -->
                        <span v-if="data.object.type === 'textfield' && !data.object.object.fixed">
                            <el-tooltip class="item" effect="dark" :content="$t('NOTEFIELD')" placement="top">
                                <q-icon name="comment" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('NOTEFIELD') }}</span>
                        </span>

                        <!-- Picture -->
                        <span v-if="data.object.type === 'picture' && data.object.object.type === 'media'">
                            <el-tooltip class="item" effect="dark" :content="$t('PICTURE')" placement="top">
                                <q-icon name="photo" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('PICTURE') }}</span>
                        </span>

                        <!-- Signature -->
                        <span v-if="data.object.type === 'picture' && data.object.object.type === 'signature'">
                            <el-tooltip class="item" effect="dark" :content="$t('SIGNATURE')" placement="top">
                                <q-icon name="attach_file" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('SIGNATURE') }}</span>
                        </span>

                        <!-- Location -->
                        <span v-if="data.object.type === 'location'">
                            <el-tooltip class="item" effect="dark" :content="$t('LOCATION')" placement="top">
                                <q-icon name="place" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('LOCATION') }}</span>
                        </span>

                        <!-- Participant -->
                        <span v-if="data.object.type === 'participant'">
                            <el-tooltip class="item" effect="dark" :content="$t('PARTICIPANT')" placement="top">
                                <q-icon name="people" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('PARTICIPANT') }}</span>
                        </span>
                    </div>

                </slot>
            </div>
            <div class="flex">
                <div>
                    <q-btn
                        icon="keyboard_arrow_down"
                        round
                        flat
                        no-ripple
                        class="checklist__element-btn-toggle"
                        :class="{'is-open': isOpen}"
                        @click="onClickToogleSection">
                    </q-btn>
                </div>
            </div>
        </div>

        <transition name="collapse">
            <!-- Content -->
            <div class="checklist__element-content" v-if="isOpen">
                <!-- Parent can write some text inside -->
                <slot></slot>

                <!-- Element component goes here -->
                <component
                    v-if="data.object.type"
                    :is="getComponentName(data.object.type)"
                    :audit="audit"
                    :data="data"
                    :open="isOpen">
                </component>
            </div>
        </transition>
    </el-card>

    <!-- <div class="checklist__element-extension">
        {{data.object.type}}
        <component
            v-if="data.object.type"
            :is="getComponentName(data.object.type)"
            :data="data"
            :audit="audit">
        </component>
    </div> -->
</template>

<script>
import checklistMixins from '@/shared/mixins/checklists';
import ElementAuditTextfield from '@/components/Element/ElementAuditTextfield';
import ElementLocation from '@/components/Element/ElementLocation';
import ElementParticipant from '@/components/Element/ElementParticipant';
import ElementPicture from '@/components/Element/ElementPicture';

export default {
	name: 'ElementAuditExtension',

	mixins: [checklistMixins],

	components: {
		ElementAuditTextfield,
		ElementLocation,
		ElementParticipant,
		ElementPicture
	},

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
			isOpen: false,
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.isOpen = this.open;
		},

		getComponentName(type) {
			let result = '';

			switch (type) {
				case 'textfield':
					result = 'ElementAuditTextfield';
					break;
				case 'picture':
					result = 'ElementPicture';
					break;
				case 'participant':
					result = 'ElementParticipant';
					break;
				case 'location':
					result = 'ElementLocation';
					break;
			}

			return result;
		},

		onClickToogleSection(event) {
			this.isOpen = !this.isOpen;
		}
	}
};
</script>

<style lang="scss">
.checklist__element-extension {
	padding: 0.8rem;
}
</style>

