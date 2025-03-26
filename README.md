## Support us

Support Opscale

At Opscale, we’re passionate about contributing to the open-source community by providing solutions that help businesses scale efficiently. If you’ve found our tools helpful, here are a few ways you can show your support:

⭐ **Star this repository** to help others discover our work and be part of our growing community. Every star makes a difference!

💬 **Share your experience** by leaving a review on [Trustpilot](https://www.trustpilot.com/review/opscale.co) or sharing your thoughts on social media. Your feedback helps us improve and grow!

📧 **Send us feedback** on what we can improve at [feedback@opscale.co](mailto:feedback@opscale.co). We value your input to make our tools even better for everyone.

🙏 **Get involved** by actively contributing to our open-source repositories. Your participation benefits the entire community and helps push the boundaries of what’s possible.

💼 **Hire us** if you need custom dashboards, admin panels, internal tools or MVPs tailored to your business. With our expertise, we can help you systematize operations or enhance your existing product. Contact us at hire@opscale.co to discuss your project needs.

Thanks for helping Opscale continue to scale! 🚀



## Description

Secure your Nova resources with roles and permissions.

One of the most basic needs for a dashboard is differentiated user access, allowing each profile to manage and view specific information. Confidently manage access to your data with roles and permissions.

![Role creation](https://raw.githubusercontent.com/opscale-co/nova-authorization/refs/heads/main/screenshots/role-creation.png)
![Role demo](https://raw.githubusercontent.com/opscale-co/nova-authorization/refs/heads/main/screenshots/role-demo.png)

## Installation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/opscale-co/nova-authorization.svg?style=flat-square)](https://packagist.org/packages/opscale-co/nova-authorization)

You can install the package in to a Laravel app that uses [Nova](https://nova.laravel.com) via composer:

```bash

composer require opscale-co/nova-authorization

```

Next up, you must register the tool with Nova. This is typically done in the `tools` method of the `NovaServiceProvider`.

```php

// in app/Providers/NovaServiceProvider.php
// ...
public function tools()
{
    return [
        // ...
        new \Opscale\NovaAuthorization\Tool(),
    ];
}

```
This package uses [Spatie Permissions](https://spatie.be/docs/laravel-permission/v6/introduction) internally to manage roles and permissions structure. Follow the installation instructions for this package.

Then modify the following items in Spatie permissions configuration file (permissions.php):

* `'permission' => Opscale\NovaAuthorization\Models\Permission::class,`
* `'role' => Opscale\NovaAuthorization\Models\Role::class,`
* `'register_permission_check_method' => false,`

> [!IMPORTANT]  
> This packages uses its own cache strategy, so we need to disable the default behavior with register_permission_check_method.

Then add the roles relationship to your User resource:
```php

// in app/Nova/User.php
// ...
public function fields(NovaRequest $request): array
{
    return [
        // ...
        Tag::make(_('Roles'), 'roles', \Opscale\NovaAuthorization\Nova\Role::class)
            ->hideFromIndex(),
    ];
}

```

## Usage

You will see a "Roles" item in your menu by default. You can create your roles here and assign them to users.

You can also automate the initial permissions setup using our built-in commands:
* `php artisan authorization:create-permissions` to automatically read all your resources and create the related permissions
* `php artisan authorization:create-role` to create a role assigning the selected permissions
* `php artisan authorization:assign-role` to assign an existing role for an user
* `php artisan authorization:super-admin` to assign all permission to an user
* `php artisan authorization:clear-cache` to clear the permissions cache for users (Recommended to execute as part of deployment pipelines)

### Custom policies

This package create a dynamic Policy class for each Model class associated to *your Nova app resources*. If you want to use authorization for other resources or modify the logic for your resources, you can create your own policy and register it in `nova-authorization` config file.

### Cache

> [!IMPORTANT]  
> This package caches the permissions for each user, the cache last 24 hours and it's flushed any time a role is attached or detached from an user or a permission is attached or detached from a role. 

If you want to avoid caching permission you can disable this behavior in `nova-authorization` config file.

## Testing

``` bash

npm run test

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/opscale-co/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email development@opscale.co instead of using the issue tracker.

## Credits

- [Opscale](https://github.com/opscale-co)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.