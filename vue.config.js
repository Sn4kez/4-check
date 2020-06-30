module.exports = {
	css: {
		loaderOptions: {
			sass: {
				data: `@import "@/assets/scss/base/_variables.scss";
                        @import "@/assets/scss/base/_helper.scss";`
			}
		}
	},

	chainWebpack: config => {
		const svgRule = config.module.rule('svg');

		// clear all existing loaders.
		// if you don't do this, the loader below will be appended to
		// existing loaders of the rule.
		svgRule.uses.clear();

		// add replacement loader(s)
		svgRule.use('vue-svg-loader').loader('vue-svg-loader');
	},

	configureWebpack: config => {
		if (process.env.NODE_ENV === 'production') {
			// mutate config for production...
		} else {
			// mutate for development...
			// console.log('configureWebpack', config);
		}
	},

	pluginOptions: {
		quasar: {
			theme: 'mat'
		}
	},

	pwa: {
		name: '4-check',
		themeColor: '#4DAE4E',
		msTileColor: '#ffffff',
		appleMobileWebAppCapable: 'yes',
		appleMobileWebAppStatusBarStyle: 'black',

		// configure the workbox plugin
		workboxPluginMode: 'InjectManifest',
		workboxOptions: {
		// swSrc is required in InjectManifest mode.
			swSrc: 'public/service-worker.js'
			// swSrc: 'service-worker.js'
		// ...other Workbox options...
		}
	}
};
