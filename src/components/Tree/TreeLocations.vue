<template>
    <table class="w-100">
        <tr>
            <el-tree
                :data="locations"
                :props="defaultProps"
                @node-click="loadNode"
                show-checkbox
                default-expand-all>
                <div slot-scope="{node, data}" class="w-100">
                    <!-- <span>{{node.label}}</span> -->
                    <!-- <span>{{data.name}}</span> -->

                    <td>{{data.name}}</td>
                    <td>{{data.typ}}</td>
                    <td>{{data.description}}</td>
                    <td>{{data.street}}</td>
                    <td>{{data.streetNumber}}</td>
                    <td>{{data.postalCode}}</td>
                    <td>{{data.postalCode}}</td>
                </div>
            </el-tree>
        </tr>
    </table>
</template>

<script>
import locationsMixin from '@/shared/mixins/locations';

export default {
	name: 'TreeLocations',

	mixins: [locationsMixin],

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		locations() {
			return this.$store.state.locations.locations;
			// return this.$store.getters['locations/locationTree'];
		}
	},

	data() {
		return {
			data: [
				{
					id: 1,
					label: 'neussel',
					children: []
				},
				{
					id: 2,
					label: 'kreussel',
					children: [
						{
							id: 1,
							label: 'sdfsadfsadfasdfssel',
							children: []
						}
					]
				}
			],
			defaultProps: {
				children: 'children',
				label: 'name'
			}
		};
	},

	methods: {
		loadNode(node) {
			console.log(node);

			if (node.parentId) {
				return;
			}

			this.$store.commit('locations/SET_FILTER', {
				selected: node.id
			});
			this.$emit('refresh');

			// this.requestLocation(node.data.id)
			// 	.then(response => {
			// 		response.data.data.children = [{ name: 'test' }];
			// 		return resolve(response.data.data);
			// 	})
			// 	.catch(err => {
			// 		return resolve([]);
			// 	});

			// return resolve([{ name: 'test' }]);
		}
	}
};
</script>

