import { LocationType } from '@/shared/classes/LocationType';

export default {
	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		user() {
			return this.$store.state.user;
		}
	},

	methods: {
		doCreateLocationType(value) {
			// let locationType = {};
			let locationType = new LocationType({
				company: this.user.company.id,
				name: value
			});
			let dispatcherName = 'locations/CREATE_LOCATION_TYPE';

			console.log('doSubmit new locationtype', locationType);

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, locationType)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						// Refresh
						this.requestLocationTypes();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		doDeleteLocations(locations) {
			return this.$store
				.dispatch('locations/DELETE_LOCATIONS', locations)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		getLocationById(id) {
			return this.$store.getters['locations/getLocationById'](id);
		},

		getLocationByName(name) {
			return this.$store.getters['locations/getLocationByName'](name);
		},

		getLocationStateById(id) {
			return this.$store.getters['locations/getLocationStateById'](id);
		},

		getLocationStateByName(name) {
			return this.$store.getters['locations/getLocationStateByName'](name);
		},

		getLocationTypeById(id) {
			return this.$store.getters['locations/getLocationTypeById'](id);
		},

		handleErrors(response) {
			if (response.response.data.message.length) {
				this.$q.notify({
					message: response.response.data.message,
					type: 'negative'
				});
			}

			if (response.response.data.errors) {
				_.forEach(response.response.data.errors, (value, key) => {
					console.log('invalid fields:', value, key);
					this.$q.notify({
						message: value[0],
						type: 'negative'
					});
				});
			}
		},

		promptLocationType() {
			if (this.isDeviceGreaterSM) {
				this.$prompt(this.$t('CREATE_LOCATION_TYPE'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					inputErrorMessage: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'),
					inputValidator(value) {
						return value !== null;
					}
				})
					.then(data => {
						this.doCreateLocationType(data.value);
					})
					.catch(() => {});
			} else {
				this.$q
					.dialog({
						title: this.$t('CREATE_LOCATION_TYPE'),
						message: '',
						prompt: {
							model: '',
							type: 'text' // optional
						},
						ok: this.$t('OK'),
						cancel: this.$t('CANCEL')
					})
					.then(data => {
						if (!data) {
							return;
						}
						this.doCreateLocationType(data);
					})
					.catch(() => {});
			}
		},
		requestLocation(id) {
			return this.$store
				.dispatch('locations/GET_LOCATION', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestLocations() {
			return this.$store
				.dispatch('locations/GET_LOCATIONS', { id: this.user.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestLocationTypes() {
			return this.$store
				.dispatch('locations/GET_LOCATION_TYPES', { id: this.user.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		searchLocation(value, done) {
			this.$http
				.get('/locations/company/' + this.company.id, {
					params: {
						name: value
					}
				})
				.then(response => {
					console.log('HEY CHECK MY RESPONSE')
					console.log(response)
					// Transform location for autocomplete
					const locations = _.forEach(response.data.data, location => {
						location.value = location.id;
						location.label = location.name;
						if (location.street) {
							location.sublabel = `${location.street} ${location.streetNumber}, ${location.city}`;
						}
					});

					done(locations);
				})
				.catch(err => {
					// done([]);
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDb found');

						var dbPromise = window.indexedDB.open('4check', 1);

						dbPromise.onsuccess = function(event){
				            var db = dbPromise.result;
				            var tx = db.transaction('location', 'readonly');
				            var store = tx.objectStore('location');
				            var records = store.getAll()
				            
				            store.getAll().onsuccess = function() {
				            	const locations = _.forEach(records.result, location => {
									location.value = location.id;
									location.label = location.name;
									if (location.street) {
										location.sublabel = `${location.street} ${location.streetNumber}, ${location.city}`;
									}
								});

								done(locations);
				            }
				        }

					}else{
						done([]);
					}
				});
		}
	}
};
