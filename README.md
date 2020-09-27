# 4-check Checklist App
<a href="https://ibb.co/s36R2ZC"><img src="https://i.ibb.co/mzXTG30/Ger-te-bergreifend.jpg" alt="Ger-te-bergreifend" border="0"></a><br /><br />

4-check is a web-based software to digitize checklists and forms. Checklists can be created and adapted flexibly using a modular system. The following modules are available for building a checklist: Questions, signatures, localization information, images and note fields.

The checklists are automatically evaluated and are available online. In addition, PDF reports can be created and downloaded quickly and easily. 

Users can be invited and administered via a central interface.

Stripe was integrated as a payment service provider to ensure an automated payment process.

The software is also available via a service worker on every end device - sometimes even offline.

4-check is particularly suitable for digitizing checklists in the following areas: 
* Restaurants 
* Catering 
* Hotels 
* Workshops 
* Facility management 
* Cleaning 
* Hospitals 
* Offices

<a href="https://ibb.co/C6ktfkB"><img src="https://i.ibb.co/G9KJqK7/Modulbaum-Checklisten.jpg" alt="Modulbaum-Checklisten" border="0"></a><br /><a target='_blank' href='https://de.imgbb.com/'>bild vom foto</a><br /> 

We make the source code of our software available here Open Source, as unfortunately we could not build a viable business model with it. We no longer update the files used here independently. If you have any questions, suggestions, updates or requests, just get in touch!

## Disclaimer

**THIS REPOSITORY IS NOT FURTHER MAINTAINED.**

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

## Backend

### Technology

#### Framework
[Lumen](https://lumen.laravel.com) v5.6 [Documentation](https://lumen.laravel.com/docs/5.6)

#### Packages

[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv/tree/2.2) v2.2
[ramsey/uuid](https://github.com/ramsey/uuid) v3.7
[barryvdh/laravel-cors](https://github.com/fruitcake/laravel-cors/tree/0.11) v0.11
[laravel/passport](https://github.com/laravel/passport/tree/5.0) v5.0.3 [Documentation](https://laravel.com/docs/5.6/passport)
[dusterio/lumen-passport](https://github.com/dusterio/lumen-passport) v0.2.6
[illuminate/mail](https://github.com/illuminate/mail/tree/5.6) v5.6
[illuminate/support](https://github.com/illuminate/support/tree/5.6) v5.6
[laravel/cashier](https://github.com/laravel/cashier/tree/7.0) v7.0 [Documentation](https://laravel.com/docs/5.6/billing)
[simshaun/recurr](https://github.com/simshaun/recurr) v3.0
[guzzlehttp/guzzle](https://github.com/guzzle/guzzle/tree/6.5) v6.3

#### Services

[Stripe](https://stripe.com/) - Payment was handled by stripe.
[Sendgrid](https://sendgrid.com) - E-Mail logistics were handled by sendgrid.
[PDF Generator API](https://pdfgeneratorapi.com) - PDF Generation was handled by PDF Generator API

### Setup Instructions for Backend Code

Before installing the 4-check Backend please make sure that you fulfil general lumen requirements ([see here](https://lumen.laravel.com/docs/5.6)).

1. Clone backend branch from this repository to your server
2. Setup a Database (for example postgres or mysql)
3. Copy .env.example and rename it to .env
`cp .env.example .env`
4. Make sure that your .env file contains all needed configuration information
5. Install dependencies
`composer install`
6. Generate application key
`artisan key:generate`
7. Migrate database structure
`artisan migrate` or `artisan migrate:fresh`
8. Set up passport and add clients to .env file
`artisan passport:install`

For accessing the api you have to route your api url to the public directory within the cloned codebase.

If you want to see what other options you have with artisan use:
`artisan list`

### Instructions for including pdf gerneator resources

1. Clone pdf-gernerator-templates branch to get 4-check Template exportes.
2. Import those templates to your pdf genreator account.
3. Include the resources to 'app/Http/Controllers/AnalyticsController.php' and 'app/Http/Controllers/AuditController.php' (Have a look for ToDo: instructions).

## Frontend

### What is this?
4check-frontend is the user interface for creating and organizing complex workflows and checklists. It based on [vue@^2.5.17](https://github.com/vuejs/vuejs.org)

### Service Worker
#### What is a Service Worker?
A service worker is a type of web worker. It's essentially a JavaScript file that runs separately from the main browser thread, intercepting network requests, caching or retrieving resources from the cache, and delivering push messages.

Because workers run separately from the main thread, service workers are independent of the application they are associated with.
> Read more here [developers.google.com](https://developers.google.com/web/ilt/pwa/introduction-to-service-worker)


#### @vue/cli-plugin-pwa
pwa plugin for vue-cli. [Learn more](https://cli.vuejs.org/core-plugins/pwa.html#configuration)

#### Configuration
Configuration is handled via the pwa property of either the vue.config.js file, or the "vue" field in package.json.

##### Example configuration
```javascript
module.exports = {
  // ...other vue-cli plugin options...
  pwa: {
    name: '4check',
    themeColor: '#4DBA87',
    msTileColor: '#000000',
    appleMobileWebAppCapable: 'yes',
    appleMobileWebAppStatusBarStyle: 'black',

    // configure the workbox plugin
    workboxPluginMode: 'InjectManifest',
    workboxOptions: {
      // swSrc is required in InjectManifest mode.
      swSrc: 'dev/sw.js',
      // ...other Workbox options...
    }
  }
}
```

### Notable frameworks

#### capacitorjs
Capacitor turns any web app into a native app so you can run one app across iOS, Android, and the Web with the same code.

#### quasar
Quasar (pronounced /ˈkweɪ.zɑɹ/) is an MIT licensed open-source Vue.js based framework, which allows you as a web developer to quickly create responsive++ websites/apps in many flavours:

* SPAs (Single Page App)
* SSR (Server-side Rendered App) (+ optional PWA client takeover)
* PWAs (Progressive Web App)
* BEX (Browser Extension)
* Mobile Apps (Android, iOS, …) through Cordova or Capacitor
* Multi-platform Desktop Apps (using Electron)

#### html2pdf.js
html2pdf converts any webpage or element into a printable PDF entirely client-side using html2canvas and jsPDF.

#### vue-axios
A small wrapper for integrating [axios](https://github.com/axios/axios) to Vuejs

#### vue-sessionstorage
A Simple Plugin to Deal with SessionStorage on Vue.js

#### vue-chartjs
Easy and beautiful charts with (Chart.js)[https://www.chartjs.org/] and Vue.js

#### vue-i18n
Internationalization plugin for Vue.js

### Dependencies
| Package | Version |
|---|---|
| @capacitor/android | ^1.0.0-beta.8 |
| @capacitor/cli | ^1.1.1 |
| @capacitor/core | ^1.0.0-beta.8 |
| @capacitor/ios | ^1.0.0-beta.8 |
| @capacitor/ios | ^1.0.0-beta.8 |
| axios | ^0.18.1 |
| chart.js | ^2.7.3 |
| element-ui | ^2.4.6 |
| es6-promise | ^4.2.4 |
| html2pdf.js | ^0.9.0 |
| jspdf | ^1.5.3 |
| lodash | ^4.17.15 |
| qs | ^6.5.2 |
| quasar-extras | ^2.0.5 |
| quasar-framework | ^0.17.8 |
| register-service-worker | ^1.0.0 |
| vue | ^2.5.17 |
| vue-axios | ^2.1.3 |
| vue-chartjs | ^3.4.0 |
| vue-i18n | ^7.8.1 |
| vue-localstorage | ^0.6.2 |
| vue-phone-number-input | ^0.1.4 |
| vue-progressbar | ^0.7.5 |
| vue-router | ^3.0.1 |
| vue-sessionstorage | ^1.0.0 |
| vue-tour | ^1.1.0 |
| vuedraggable | ^2.16.0 |
| vuelidate | ^0.7.4 |
| vuex | ^3.0.1 |
| @vue/cli-plugin-babel | ^3.9.2 |
| @vue/cli-plugin-e2e-cypress | ^3.0.0-beta.15 |
| @vue/cli-plugin-eslint | ^3.0.0-beta.15 |
| @vue/cli-plugin-pwa | ^3.0.0 |
| @vue/cli-plugin-unit-jest | ^3.0.0-beta.15 |
| @vue/cli-service | ^3.9.3 |
| @vue/eslint-config-prettier | ^3.0.0 |
| @vue/test-utils | ^1.0.0-beta.16 |
| babel-core | 7.0.0-bridge.0 |
| babel-jest | ^23.0.1 |
| node-sass | ^4.12.0 |
| sass-loader | ^7.0.1 |
| stylus | ^0.54.5 |
| stylus-loader | ^3.0.1 |
| vue-cli-plugin-quasar | ^0.2.1 |
| vue-svg-loader | ^0.5.0 |
| vue-template-compiler | ^2.5.17 |

### Setup
To make this work you need to install and run the [backend](https://github.com/Sn4kez/4-check/tree/backend) as well.

#### Installation
1. Clone the frontend branch from this repository to your environment
2. Set environment variable `VUE_APP_API_URL` (for more information see [environment variables](#environment-variables))
3. Run `yarn install` or `npm install` to install dependencies

*Supported node version: `**12.0.0**`*

#### Run and build
| Command | Description |
|---|---|
| `yarn serve` or `npm run serve` | serve with hot reload at localhost:8080 |
| `yarn build` or `npm run build` | creates a build for production with minification |
| `yarn build --report` or `npm run build --report` | creates a build for production and view the bundle analyzer report |


#### Test
| Command | Description |
|---|---|
| `yarn test:unit` or `npm run test:unit` | Run the out of the box vue unit tests. [More information](https://vue-test-utils.vuejs.org/) |
| `yarn test:e2e` or `npm run test:e2e` | Run end-to-end tests |


#### Environment variables

| Variable | Description |
|---|---|
| VUE_APP_API_URL | URL to 4check backend |
