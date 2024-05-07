# Bizhost Authentication Bundle
For the Symfony Bundle see the package: [gwsn/bizhost-auth-bundle](https://github.com/gwsn/bizhost-auth-bundle)

## Installation
You can install the package via composer:

``` bash
composer require gwsn/bizhost-auth-bundle
```

## First configuration to start usage

You need to request a new clientId and clientSecret for the application

1. Go to `bizhost auth portal` https://auth.bizhost.nl/
2. Go to `Applications` https://auth.bizhost.nl/admin/clients
3. Go to `Register new application` and follow the wizard.  
   (give it a name like mine is 'example-app-authentication')
5. When created the application is created write down the following details
6. 'Application Identifier', this will be your `$clientId`
7. 'Application Secret', this will be your `$clientSecret`
   (Make sure you write this one down as it will be only shown once)

   Example:
    - Auth meta url: `https://auth.bizhost.nl/.well-known/oauth-authorization-server`


## Basic setup for the Bizhost Authentication Bundle


### Enable the bundle
Add the bundle to your `config/bundles.php` file:

``` php
...
Bizhost\Authentication\Bundle\AuthenticateBundle::class => ['all' => true],
...
```

### Setup Security.yaml
In case you want full authentication where the application is redirected to bizhost auth with code flow. 

Add Providers, custom_authenticators and access_control to your `config/packages/security.yaml` file:
``` yaml
security:
    enable_authenticator_manager: true

    providers:
        authenticated_account_provider:
            id: Bizhost\Authentication\Bundle\Authenticate\AuthenticatedAccountProvider

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            pattern: ^(/account|/auth)
            stateless: false
            custom_authenticators:
              - Bizhost\Authentication\Bundle\Authenticate\BizhostAuthAuthenticator
            entry_point: Bizhost\Authentication\Bundle\Authenticate\BizhostAuthAuthenticator
            logout:
                path: app_logout

    access_control:
         - { path: ^/account, roles: ROLE_USER }
         - { path: ^/profile, roles: ROLE_USER }

```

In case you want full authentication where the application is redirected to bizhost auth with code flow.

Add Providers, custom_authenticators and access_control to your `config/packages/security.yaml` file:
``` yaml
security:
    enable_authenticator_manager: true

    firewalls:
        api:
            pattern: ^/api
            stateless: true
            custom_authenticators:
              - Bizhost\Authentication\Bundle\Authenticate\BizhostAuthAccessTokenAuthenticator

    access_control:
         - { path: ^/api, roles: ROLE_USER }

```

### Routes
To start authentication the entry_point is used to redirect the user to the bizhost auth server.
So every route behind access_control should trigger the redirect.

When authentication is successful it will redirect the user to /auth/success, 
you should add route to handle this in your `config/routes.yaml` or in one of `config/routes/*.yaml` files:

It is also good practice to add a logout route to handle the logout of the user.

### Environment Variables
The bundle expect the following environment variables to be set with the correct values:

`BIZHOST_AUTH_CLIENT_ID` and `BIZHOST_AUTH_CLIENT_SECRET` are the values you get from the bizhost auth portal.
`BIZHOST_AUTH_REDIRECT_URL` is the url where the user is redirected to after successful authentication and should point
to your application and path that is in the scope of the firewall.

`BIZHOST_AUTH_API_URL` is the url to the bizhost auth server. for test we use https://auth-test.bizhost.nl
`BIZHOST_AUTH_ISSUER_META_DATA_PATH` if the path is different then the default `/.well-known/oauth-authorization-server`. You can change the path over here.

Example of the environment variables in your `.env` file:
``` bash

###> bizhost/auth-bundle ###
BIZHOST_AUTH_API_URL='https://auth.bizhost.nl'
BIZHOST_AUTH_CLIENT_ID='client_id_value'
BIZHOST_AUTH_CLIENT_SECRET='client_secret_value'
BIZHOST_AUTH_REDIRECT_URL='http://url-to-redirect-to-after-authentication' 

# Optional
BIZHOST_AUTH_ISSUER_META_DATA_PATH='/.well-known/oauth-authorization-server'
###< bizhost/auth-bundle ###
```

### Usage of AccountService

The AccountService is a service that can be used to get the authenticated user, you can update the user and update the following properties
- firstname
- insertion
- lastname
- userMetadata
- appMetadata

The roles can be set in Bizhost Auth, if you need custom roles you can set them in the appMetadata.

To fetch the current account that is logged in you can use the `Bizhost\Authentication\Bundle\Service\AccountService` service and call the `getAuthorizedAccount()`.
Make sure you use the correct AccountService the one from the Bundle and not the one from the SDK!

``` php
use Bizhost\Authentication\Bundle\Service\AccountService;


class Example ;
{
   public function __construct(
      private AccountService $accountService
   ) {
   }
   
   publi function exampleMethod() {
      $authenticatedAccount = $this->accountService->getAuthorizedAccount();
      
      # To get the account
      $account = $authenticatedAccount->getAccount();
      
      # To get the token and if you need the AccessToken:
      $token = $authenticatedAccount->getToken();
   }
}
```


## Testing

``` bash
$ composer run-script test
```

## Security

If you discover any security related issues, please email support@bizhost.nl instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
