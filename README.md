Aligent OroCommerce Shipping Estimator Bundle
==============================
This bundle adds the Shipping Estimator functionality on the OroCommerce storefront shopping list.

<img src="src/Aligent/AnnouncementBundle/Resources/doc/img/feature.png" alt="Shipping Estimator functionality">

### Features
- Enable/disable the Shipping Estimator component on the storefront shopping list

Requirements
-------------------
- OroCommerce 4.2

Installation and Usage
-------------------
**NOTE: Adjust instructions as needed for your local environment**

### Installation
Install via Composer
```shell
composer require aligent/orocommerce-shipping-estimator
```

Once installed, run platform update to perform the installation:
```shell
php bin/console oro:platform:update --env=prod
```


### Configuration Settings

<img src="src/Aligent/AnnouncementBundle/Resources/doc/img/sytem-config.png" alt="Configuration Options">

| Setting                     | Description                                                                                                               |
|------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------|
| **Enable Shipping Estimator On Shopping List** |                                        |

Database Modifications
-------------------
*This Bundle does not directly modify the database schema in any way*

All configuration is stored in System Configuration (`oro_config_value`).

Templates
-------------------
`Resources/views/layouts/default/oro_shopping_list_frontend_view/shipping_estimator.html.twig`

This includes a single `_shipping_estimator_container_widget` block which can be customized/overridden in OroCommerce themes
if needed.

Roadmap / Remaining Tasks
-------------------
- [x] Ability to restrict Announcement to one or more Customer Groups
- [x] OroCommerce 5.0 Support
- [x] Implement Unit Tests
- [x] Refactor `AnnouncementDataProvider`
- [ ] Consistent naming of `color` (deprecate `colour`)
- [ ] Reset `hideAlert` session variable when new Announcements are added
- [ ] Ability to block dismissal of Announcement Message (hides the 'X' button)
- [ ] Ability to only display on Homepage
- [ ] Ability to configure multiple messages for different scenarios
- [ ] (TBC) Move away from Content Blocks to WYSIWYG configuration fields