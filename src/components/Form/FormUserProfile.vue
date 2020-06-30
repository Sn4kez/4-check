<template>
    <div class="form-user-profile">

        <el-row :gutter="40">

            <el-col :xs="24" :md="6">
                <el-upload
                    action=""
                    drag
                    :show-file-list="false"
                    :on-change="onChangeUpload"
                    :auto-upload="false"
                    :multiple="false"
                    :on-remove="onRemoveImage"
                    ref="upload">

                    <div v-loading="loadingFile" class="el-upload-dragger__inner">

                        <i class="el-icon-upload" v-show="!showPreview"></i>
                        <!-- Preview -->
                        <img :src="form.user.source_b64" v-if="showPreview" id="avatar__preview" height="200">

                        <div class="el-upload__text m-t-half">
                            Drop file here or <em>click to upload</em> <br>
                            <span class="font--small color-gray">Max. 2MB (JPG/PNG/GIF)</span>
                        </div>
                    </div>
                </el-upload>

                <!-- Delete Button -->
                <div class="text-center m-t-half" v-if="showPreview">
                    <q-btn
                        icon="delete"
                        :label="$t('REMOVE_IMAGE')"
                        flat
                        no-ripple
                        @click="onRemoveImage">
                    </q-btn>
                </div>
            </el-col>

            <el-col :xs="24" :md="18" class="m-t-1--sm">
                <el-row :gutter="20">
                    <el-col :xs="24" :md="8">
                        <q-field class="m-b-1">
                            <q-select
                                v-model="form.user.gender"
                                :stack-label="$t('SEX')"
                                :options="sexOptions" />
                        </q-field>
                    </el-col>
                </el-row>

                <el-row :gutter="20">
                    <el-col :xs="24" :md="8">
                        <q-field
                            class="m-b-1"
                            :error="$v.form.user.firstName.$error"
                            :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                            <q-input
                                v-model.trim="$v.form.user.firstName.$model"
                                :stack-label="$t('FIRSTNAME')">
                            </q-input>
                        </q-field>
                    </el-col>

                    <el-col :xs="24" :md="8">
                        <q-field class="m-b-1"
                            :error="$v.form.user.lastName.$error"
                            :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                            <q-input
                                v-model.trim="$v.form.user.lastName.$model"
                                :stack-label="$t('LASTNAME')">
                            </q-input>
                        </q-field>
                    </el-col>
                </el-row>

                <!-- System settings -->
                <el-row :gutter="20">
                    <!-- Timezone -->
                    <el-col :xs="24" :md="8">
                        <q-field class="m-b-1">
                            <q-select
                                v-model.trim="form.user.timezone"
                                :stack-label="$t('TIMEZONE')"
                                :options="timezoneOptions"/>
                        </q-field>
                    </el-col>
                    <!-- Locale -->
                    <el-col :xs="24" :md="8">
                        <q-field class="m-b-1">
                            <q-select
                                v-model.trim="form.user.locale"
                                :stack-label="$t('LANGUAGE')"
                                :options="localOptions"/>
                        </q-field>
                    </el-col>
                </el-row>

                <!-- Role: Only for admins -->
                <el-row v-if="user.role !== 'user'" class="m-t-1">
                    <el-col :xs="24">
                        <q-field class="q-field--overflow">
                            <p class="caption">{{ $t('ROLE') }}</p>
                            <q-toggle
                                v-model="form.user.role"
                                true-value="admin"
                                false-value="user"
                                color="primary"
                                :label="$t('ADMINISTRATOR')"
                                :disable="user.id === data.id"/>
                        </q-field>
                    </el-col>
                </el-row>

                <!-- Phone -->
                <div class="m-t-3">
                    <el-row :gutter="20" v-for="(phone, index) in $v.form.phones.$each.$iter" :key="phone.id">
                        <el-col :xs="24" class="no-padding">
                            <el-col :xs="6" :sm="2">
                                <q-field class="m-b-1"
                                         :error="phone.countryCode.$error"
                                         :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                                    <q-input
                                        v-model.trim="phone.countryCode.$model"
                                        stack-label=" "
                                        prefix="+"
                                        placeholder="49">
                                    </q-input>
                                </q-field>
                            </el-col>

                            <el-col :xs="18" :sm="6">
                                <q-field class="m-b-1"
                                         :error="phone.nationalNumber.$error"
                                         :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                                    <q-input
                                        v-model.trim="phone.nationalNumber.$model"
                                        type="number"
                                        :stack-label="$t('NUMBER')">
                                    </q-input>
                                </q-field>
                            </el-col>

                            <el-col :xs="24" :md="4">
                                <q-field class="m-b-1"
                                         :error="phone.type.$error"
                                         :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                                    <q-select
                                        v-model.trim="phone.type.$model"
                                        :stack-label="$t('TYPE')"
                                        :options="phoneTypesOptions"/>
                                </q-field>
                            </el-col>

                            <el-col :xs="24" :md="2">
                                <q-btn v-if="phone.id && phones.length > 1"
                                       flat
                                       no-ripple
                                       color="primary"
                                       :label="$t('DELETE')"
                                       @click="onRemovePhone(phone, index)"
                                       v-loading="removingPhone">
                                </q-btn>
                            </el-col>
                        </el-col>
                    </el-row>

                    <div>
                        <q-btn
                            flat
                            no-ripple
                            color="primary"
                            icon="add_circle_outline"
                            :label="$t('ADD_PHONE')"
                            @click="onClickAddPhone">
                        </q-btn>
                    </div>
                </div>

                <el-row class="m-t-1">
                    <el-col :xs="24" class="text-right">
                        <q-btn
                            color="primary"
                            :label="$t('SAVE')"
                            v-loading="loading"
                            @click="onSubmit"
                            class="w-100--sm">
                        </q-btn>
                    </el-col>
                </el-row>
            </el-col>

        </el-row>

    </div>
</template>

<script>
    import axios from 'axios';
    import {numeric, required} from 'vuelidate/lib/validators';
    import {User} from '@/shared/classes/User';
    import {Phone} from '@/shared/classes/Phone';
    import {getLocale, getTimezone} from '@/services/browser';
    import {getBase64} from '@/services/utils';

    export default {
        name: 'FormUserProfile',

        props: {
            data: {
                type: Object,
                required: true
            },

            phones: {
                type: Array,
                required: true
            }
        },

        computed: {
            user() {
                return this.$store.state.user.data;
            },
            sexOptions() {
                return [
                    {
                        label: this.$t('MRS'),
                        value: 'female'
                    },
                    {
                        label: this.$t('MR'),
                        value: 'male'
                    }
                ];
            },
            phoneTypesOptions() {
                return [
                    {
                        label: this.$t('HOME'),
                        value: 'home'
                    },
                    {
                        label: this.$t('MOBILE'),
                        value: 'mobile'
                    },
                    {
                        label: this.$t('WORK'),
                        value: 'work'
                    },
                    {
                        label: this.$t('OTHER'),
                        value: 'other'
                    }
                ];
            },
        },

        data() {
            return {
                form: {
                    user: {
                        locale: '',
                        timezone: ''
                    },
                    phones: []
                },
                loading: false,
                loadingFile: false,
                removingPhone: false,
                showPreview: false,

                localOptions: [
                    {
                        label: this.$t('GERMAN', 'de'),
                        value: 'de'
                    },
                    {
                        label: this.$t('ENGLISH', 'en'),
                        value: 'en'
                    },
                    {
                        label: this.$t('FRENCH', 'fr'),
                        value: 'fr'
                    },
                ],
                timezoneOptions: [
                    {
                        label: 'Berlin',
                        value: 'UTC'
                    },
                    {
                        label: 'USA',
                        value: 'GMT'
                    }
                ],
            };
        },

        validations: {
            form: {
                user: {
                    firstName: {required},
                    lastName: {required},
                    gender: {required}
                },
                phones: {
                    $each: {
                        countryCode: {required},
                        nationalNumber: {required, numeric},
                        type: {required}
                    }
                }
            }
        },

        mounted() {
            this.init();
        },

        methods: {
            beforeAvatarUpload() {
            },

            /**
             * Create new phone
             *
             * @param Object Phone
             * @returns Promise
             */
            createPhone(item) {
                return this.$store
                    .dispatch('users/CREATE_PHONE', {
                        id: this.data.id,
                        data: item
                    })
                    .then(response => {
                        return response;
                    })
                    .catch(err => {
                        return err;
                    });
            },

            /**
             * Save user data and phone numbers
             *
             * @returns void
             */
            doSave() {
                this.loading = true;

                axios
                    .all([this.updateUser(), this.savePhones()])
                    .then(
                        axios.spread((...results) => {
                            //UPDATE LOCALE//
                            const locale = this.form.user.locale.substr(0, 2);
                            localStorage.setItem('locale', locale);
                            this.$i18n.locale = locale;

                            this.$q.notify({
                                message: this.$t('SAVE_SUCCESS'),
                                type: 'positive'
                            });
                            this.loading = false;
                        })
                    )
                    .catch(err => {
                        this.$q.notify({
                            message: this.$t('ERROR'),
                            type: 'negative'
                        });
                        this.loading = false;
                    });
            },

            init() {
                this.form.user.timezone = getTimezone();
                this.form.user.locale = getLocale();
                this.form.user = Object.assign({}, this.form.user, this.data);

                if (this.form.user.image) {
                    this.form.user.source_b64 = this.form.user.image;
                    this.showPreview = true;
                }

                if (this.phones.length) {
                    this.form.phones = _.cloneDeep(this.phones);
                }
            },

            onClickAddPhone() {
                const phone = new Phone({
                    countryCode: '49',
                    nationalNumber: '',
                    type: 'mobile'
                });

                this.form.phones.push(phone);
            },

            onChangeUpload(file, fileList) {
                const isJPG = file.raw.type === 'image/jpeg';
                const isPNG = file.raw.type === 'image/png';
                const isGIF = file.raw.type === 'image/gif';
                const isLt2M = file.size / 1024 / 1024 < 2;

                if (!isJPG && !isPNG && !isGIF) {
                    this.$q.notify({
                        message: this.$t('FORMAT_NOT_SUPPORTED'),
                        type: 'negative'
                    });
                    return;
                }

                if (!isLt2M) {
                    this.$q.notify({
                        message: this.$t('FILE_SIZE_EXCEEDED'),
                        type: 'negative'
                    });
                    return;
                }

                this.loadingFile = true;

                getBase64(file.raw).then(response => {
                    this.form.user.source_b64 = response;
                    this.showPreview = true;

                    setTimeout(() => {
                        this.loadingFile = false;
                    }, 1000);
                });

                console.log('onChangeUpload upload', file, fileList, this.form.user.source_b64);
            },

            onRemoveImage() {
                this.showPreview = false;

                this.form.user.image = null;
                delete this.form.user.source_b64;

                console.log('onRemoveImage upload');
            },

            /**
             * Remove current phone an request users phone
             *
             * @returns {void}
             */
            onRemovePhone(item, index) {
                this.removingPhone = true;
                this.$store
                    .dispatch('phones/DELETE_PHONE', item)
                    .then(response => {
                        this.$emit('refresh');
                        this.removingPhone = false;
                    })
                    .catch(err => {
                        this.removingPhone = false;
                    });
            },

            /**
             * Handle form validation
             *
             * @returns void
             */
            onSubmit() {
                this.$v.$touch();
                if (this.$v.$invalid) {
                    return false;
                } else {
                    this.doSave();
                }
            },

            /**
             * Create or update phones
             *
             * @returns Promise
             */
            savePhones() {
                const REQUESTS = [];
                const PHONES = [];

                _.forEach(this.form.phones, item => {
                    PHONES.push(new Phone(item));
                });

                // Check if the phone should create or update
                PHONES.forEach(phone => {
                    // Update phone
                    if (phone.id && phone.nationalNumber) {
                        REQUESTS.push(this.updatePhone(phone));
                        console.log('update phone', phone.nationalNumber);
                    } else if (phone.nationalNumber) {
                        // Create phone
                        REQUESTS.push(this.createPhone(phone));
                        console.log('create phone', phone.nationalNumber);
                    }
                });

                return axios
                    .all(REQUESTS)
                    .then(
                        axios.spread((...results) => {
                            return results;
                        })
                    )
                    .catch(err => {
                        return err;
                    });
            },

            /**
             * Update phone
             *
             * @param Object Phone
             * @returns Promise
             */
            updatePhone(item) {
                return this.$store
                    .dispatch('phones/UPDATE_PHONE', item)
                    .then(response => {
                        return response;
                    })
                    .catch(err => {
                        return err;
                    });
            },

            /**
             * Update user
             *
             * @returns Promise
             */
            updateUser() {
                const USER = new User(this.form.user);

                if (this.data.id !== this.user.id) {
                    USER.role = this.form.user.role;
                }

                return this.$store.dispatch('users/UPDATE_USER', USER).then(response => {
                    this.$store.commit('SET_USER_LOCALE', USER);

                    return response;
                });
            }
        },

        watch: {
            data(newValue, oldValue) {
                this.init();
            },

            phones(newValue, oldValue) {
                this.init();
            }
        }
    };
</script>

<style lang="scss">
    .form-user-profile {
        .el-upload,
        .el-upload-dragger {
            width: 100%;
        }

        .el-upload-dragger {
            display: flex;
            min-height: 220px;
            height: auto;

            .el-icon-upload {
                margin: 0 0 10px 0;
            }
        }

        .el-upload-dragger__inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
    }
</style>
