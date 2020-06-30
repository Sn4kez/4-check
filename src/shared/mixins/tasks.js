export default {
	methods: {
		doDeleteTasks(taskIds) {
			return this.$store
				.dispatch('tasks/DELETE_TASKS', taskIds)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doUpdateTask(task) {
			return this.$store
				.dispatch('tasks/UPDATE_TASK', task)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doFinishTasks(tasks) {
			return this.$store
				.dispatch('tasks/FINISH_TASKS', tasks)
				.then(response => {
					console.log('Finish Tasks: ', response)
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		getTaskPriorityById(id) {
			return this.$store.getters['tasks/getTaskPriorityById'](id);
		},

		getTaskPriorityByName(name) {
			return this.$store.getters['tasks/getTaskPriorityByName'](name);
		},

		getTaskStateById(id) {
			return this.$store.getters['tasks/getTaskStateById'](id);
		},

		getTaskStateByName(name) {
			return this.$store.getters['tasks/getTaskStateByName'](name);
		},

		getTaskTypeById(id) {
			return this.$store.getters['tasks/getTaskTypeById'](id);
		},

		requestTasks(companyId) {
			return this.$store
				.dispatch('tasks/GET_TASKS', { id: companyId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestTasksPriorities(companyId) {
			return this.$store
				.dispatch('tasks/GET_TASK_PRIORITIES', { id: companyId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestTasksStates(companyId) {
			return this.$store
				.dispatch('tasks/GET_TASK_STATES', { id: companyId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestTasksTypes(companyId) {
			return this.$store
				.dispatch('tasks/GET_TASK_TYPES', { id: companyId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
