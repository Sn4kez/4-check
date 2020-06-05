# 4-check Checklist App

Description of 4-check

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

1. Clone Backend Branch from this repository to your server
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

If you want to see what other options you have with artisan use:
`artisan list`