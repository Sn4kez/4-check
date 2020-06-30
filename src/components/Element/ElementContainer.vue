<!--
@component:         ElementContainer
@environment:       Hyprid
@description:       -
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-18
@modified:          2018-10-19
-->
<template>
    <el-card class="checklist__element" :class="{'is-open': isOpen}">
        <!-- Header -->
        <div slot="header" class="checklist__element-header">
            <div class="checklist__element-headline">
                <slot name="title">
                    <!-- Section -->
                    <div class="checklist__element-headline-inner" v-if="data.objectType === 'section'">
                        <q-icon name="drag_indicator" class="drag__handler"></q-icon>

                        <el-tooltip class="item" effect="dark" :content="$t('GROUP')" placement="top">
                            <q-icon name="group_work" size="1.3rem" class="m-r-half color-gray"></q-icon>
                        </el-tooltip>

                        <span class="color-dark-light">{{data.object.title}}</span>
                    </div>

                    <!-- Checkpoint -->
                    <div class="checklist__element-headline-inner" v-if="data.objectType === 'checkpoint'">
                        <q-icon name="drag_indicator" class="drag__handler"></q-icon>

                        <!-- Question -->
                        <span v-if="data.object.evaluationSchemeType === 'App\\ChoiceScheme'">
                            <el-tooltip class="item" effect="dark" :content="$t('QUESTION')" placement="top">
                                <q-icon name="help" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('QUESTION') }}: {{data.object.prompt}}</span>
                        </span>

                        <!-- Value question -->
                        <span v-if="data.object.evaluationSchemeType === 'App\\ValueScheme'">
                            <el-tooltip class="item" effect="dark" :content="$t('VALUE_QUESTION')" placement="top">
                                <q-icon name="settings_input_component" size="1.3rem" class="m-r-half color-gray"></q-icon>
                            </el-tooltip>

                            <span class="color-dark-light">{{ $t('VALUE_QUESTION') }}: {{data.object.prompt}}</span>
                        </span>
                    </div>

                    <!-- Extension -->
                    <div class="checklist__element-headline-inner" v-if="data.objectType === 'extension'">
                        <q-icon name="drag_indicator" class="drag__handler"></q-icon>

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
                <div class="">
                    <ButtonChecklistSectionItem
                        :data="data">
                    </ButtonChecklistSectionItem>
                </div>
            </div>
        </div>

        <transition name="collapse">
            <!-- Content -->
            <div class="checklist__element-content" v-show="isOpen">
                <!-- Parent can write some text inside -->
                <slot></slot>

                <!-- Element component goes here -->
                <component
                    v-if="data.objectType"
                    :is="getComponentName(data.objectType)"
                    :data="data.object"
                    :open="isOpen">
                </component>
            </div>
        </transition>
    </el-card>
</template>

<script>
import ButtonChecklistSectionItem from '@/components/Button/ButtonChecklistSectionItem';
import ElementCheckpoint from '@/components/Element/ElementCheckpoint';
import ElementExtension from '@/components/Element/ElementExtension';
import ElementSection from '@/components/Element/ElementSection';

export default {
	name: 'ElementContainer',

	components: {
		ButtonChecklistSectionItem,
		ElementCheckpoint,
		ElementExtension,
		ElementSection
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
			isOpen: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		getComponentName(objectType) {
			let result = '';

			switch (objectType) {
				case 'section':
					result = 'ElementSection';
					break;
				case 'checkpoint':
					result = 'ElementCheckpoint';
					break;
				case 'extension':
					result = 'ElementExtension';
					break;
			}

			return result;
		},

		init() {
			this.isOpen = this.open;
		},

		onClickToogleSection(event) {
			this.isOpen = !this.isOpen;
			console.log('onClickToogleSection', event, event.target.parentNode.parentNode);
		}
	},

	watch: {
		open(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
.checklist__element {
	&:not(:first-child) {
		margin-top: 1.2rem;
	}

	.el-card__body {
		padding: 0;
		transition: all 0.4s ease-in-out;
	}

	&-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	&-headline {
		flex: 1;
		white-space: normal;

		&-inner {
			overflow: hidden;
			text-overflow: ellipsis;
			max-width: 60vw;
			white-space: nowrap;
		}
	}

	&-content {
		// padding: 1rem;
	}

	&-btn-toggle {
		transition: all 0.3s ease-in-out;

		&.is-open {
			color: $c-brand;
			transform: rotate(180deg);
		}
	}

	.collapse-enter-active,
	.collapse-leave-active {
		transition: max-height 0.3s ease-in-out;
	}
	.collapse-enter,
	.collapse-leave-to {
		max-height: 99rem;
	}

	.drag__handler {
		cursor: row-resize;
		margin-right: 0.5rem;
	}
}
</style>
