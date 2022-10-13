The module integrates Magento 2 with the **[Stripe](https://stripe.com/)** payment service.
The module is **free** and **open source**.

## Demo videos

1. [Capture and Refund](https://www.youtube.com/watch?v=kBaiBnPNmEo)
2. [Preauthorization and Capture](https://www.youtube.com/watch?v=BnBlsafqPsM)
3. [Preauthorization and Voiding](https://www.youtube.com/watch?v=0kxVBCmhpHU)
4. [Review and Accept / Deny](https://www.youtube.com/watch?v=9W4FjZN8uKo)
5. [Capture from a Stripe account](https://www.youtube.com/watch?v=MjaOJUM9ddU)
6. [Refund from a Stripe account](https://www.youtube.com/watch?v=dUUzgnvKYCg)
7. [Save and reuse bank cards](https://www.youtube.com/watch?v=OlL6GndwOX4)
8. [Multishipping checkout](https://www.youtube.com/watch?v=Rw19I54SQTI)

## Who is using it?

[stripe.mage2.pro/customers](https://stripe.mage2.pro/customers)  
See also a [showcase](https://mage2.pro/tags/stripe-showcase) of the real clients usage.

## How to install
[Hire me in Upwork](https://www.upwork.com/fl/mage2pro), and I will: 
- install and configure the module properly on your website
- answer your questions
- solve compatiblity problems with third-party checkout, shipping, marketing modules
- implement new features you need 

### Self-installation
```
bin/magento maintenance:enable
rm -f composer.lock
composer clear-cache
composer require mage2pro/stripe:*
bin/magento setup:upgrade
bin/magento cache:enable
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales>
bin/magento maintenance:disable
```

## How to update
```
bin/magento maintenance:enable
composer remove mage2pro/stripe
rm -f composer.lock
composer clear-cache
composer require mage2pro/stripe:*
bin/magento setup:upgrade
bin/magento cache:enable
rm -rf var/di var/generation generated/code
bin/magento setup:di:compile
rm -rf pub/static/*
bin/magento setup:static-content:deploy -f en_US <additional locales>
bin/magento maintenance:disable
```

## Support
- [The extension's **forum** branch](https://mage2.pro/c/stripe).
- [Where and how to report a Mage2.PRO extension's issue?](https://mage2.pro/t/2034)

## Screenshots
See also a [showcase](https://mage2.pro/tags/stripe-showcase) of the real clients usage.
### 1. Frontend. A simple payment form without saved bank cards
![](https://mage2.pro/uploads/default/original/1X/dfc4f33ba61824ad005beb2a6c3ae77da7cb7fa9.png)
### 2. Frontend. Using a saved bank card
![](https://mage2.pro/uploads/default/original/2X/1/174398862837c092d3742388377cdc9c7edff92b.png)
### 3. Frontend. Using a new bank card
![](https://mage2.pro/uploads/default/original/2X/8/8429899325e38f4a4926ae6f3cfd333d4247a1e7.png)
### 4. Frontend. A [multishipping checkout](https://mage2.pro/t/4411).
### 5. Backend. A payment's imformation
![](https://mage2.pro/uploads/default/original/2X/b/be3f4d12792c8d00f9a27f2f83b6eb12537602ef.png)
### 6. Backend. Choosing the payments currency
![](https://mage2.pro/uploads/default/original/2X/a/a8ae59e005b34a2ffdb41c7f85acb4bd5fdf660d.png)
### 7. Backend. The extension's settings
![](https://mage2.pro/uploads/default/original/1X/abc830769aaa9251fe49db2ba0bbf20dc1a3ac77.png)
### 8. Backend. A transaction's details
![](https://mage2.pro/uploads/default/original/2X/9/9ef3c6c7dfad87620cb310e97a4c004ad4cd81ed.png)