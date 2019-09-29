/**
 * 2019-09-30
 * Without this mixin, even the standard Magento 2.3.2 checkout
 * does not show my payment methods on a first page load with an empty browser cache.
 * Previously, I thought that the problem is only reproducible with third-party checkout modules.
 * See my previous evidences of the issue:
 * 1) «How to fix the bug of Aheadworks OneStepCheckout not showing a payment module
 * on the frontend checkout screen?» https://mage2.pro/t/5616
 * 2) «Mageplaza One Step Checkout does not show Mage2.PRO payment methods on the frontend checkout screen»:
 * https://github.com/mage2pro/core/issues/78
 * 3) «Mageplaza One Step Checkout does not show the Stripe module on the frontend checkout screen»:
 * https://github.com/mage2pro/stripe/issues/65
 * 4) «Make the Vantiv payment module compatible with a custom checkout module»:
 * https://github.com/mage2pro/vantiv/issues/3
 * 5) «Mageplaza One Step Checkout does not show the Dragonpay payment option to anonymous visitors
 * on the frontend checkout screen's initial load»:
 * https://github.com/mage2pro/dragonpay/issues/5
 */
define(['Dfe_Stripe/loader'], function() {return function(sb) {return sb;};});