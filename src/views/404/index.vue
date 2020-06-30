<!--
@component:         NotFoundView
@environment:       Hyprid
@description:       This component show a 404 message to the user.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-09
@modified:          2018-07-09
-->
<template>
    <div class="404">
        <div class="404__inner p-1">

            <el-card>
                <div slot="header">
                    <span>404</span>
                </div>

                <div>
                    Sorry, the page you have searched for is not available.
                </div>
            </el-card>

        </div>
    </div>
</template>

<script>
export default {
	name: 'NotFoundView',

	data() {
		return {};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('NotFoundView mounted');
		}
	}
};
</script>

<style lang="scss">
</style>
