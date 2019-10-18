/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'uiComponent',
    'uiRegistry',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_InventoryInStorePickupFrontend/js/model/pickup-locations-service',
    'Magento_Checkout/js/checkout-data'
], function (
    $,
    _,
    Component,
    registry,
    modal,
    quote,
    customer,
    stepNavigator,
    addressConverter,
    setShippingInformationAction,
    pickupLocationsService,
    checkoutData
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_InventoryInStorePickupFrontend/store-selector',
            selectedLocationTemplate:
                'Magento_InventoryInStorePickupFrontend/store-selector/selected-location',
            storeSelectorPopupTemplate:
                'Magento_InventoryInStorePickupFrontend/store-selector/popup',
            storeSelectorPopupItemTemplate:
                'Magento_InventoryInStorePickupFrontend/store-selector/popup-item',
            loginFormSelector:
                '#store-selector form[data-role=email-with-possible-login]',
            defaultCountryId: window.checkoutConfig.defaultCountryId,
            selectedLocation: pickupLocationsService.selectedLocation,
            quoteIsVirtual: quote.isVirtual,
            searchQuery: '',
            nearbyLocations: null,
            isLoading: pickupLocationsService.isLoading,
            popup: null,
            searchDebounceTimeout: 300,
            imports: {
                nearbySearchRadius: '${ $.parentName }:nearbySearchRadius',
                nearbySearchLimit: '${ $.parentName }:nearbySearchLimit'
            }
        },

        /**
         * Init component
         *
         * @return {exports}
         */
        initialize: function () {
            var updateNearbyLocations,
                postcode,
                city;

            this._super();

            updateNearbyLocations = _.debounce(function (searchQuery) {
                postcode = null;
                city = searchQuery.replace(/(\d+[\-]?\d+)/, function (match) {
                    postcode = match;

                    return '';
                });

                this.updateNearbyLocations(
                    addressConverter.formAddressDataToQuoteAddress({
                        city: city,
                        postcode: postcode,
                        'country_id': quote.shippingAddress().countryId
                    })
                );
            }, this.searchDebounceTimeout).bind(this);
            this.searchQuery.subscribe(updateNearbyLocations);

            return this;
        },

        /**
         * Init component observable variables
         *
         * @return {exports}
         */
        initObservable: function () {
            return this._super().observe(['nearbyLocations', 'searchQuery']);
        },

        /**
         * Set shipping information handler
         */
        setPickupInformation: function () {
            var shippingAddress = quote.shippingAddress();

            if (this.validatePickupInformation()) {
                shippingAddress = addressConverter.quoteAddressToFormAddressData(shippingAddress);
                checkoutData.setShippingAddressFromData(shippingAddress);
                setShippingInformationAction().done(function () {
                    stepNavigator.next();
                });
            }
        },

        /**
         * @return {*}
         */
        getPopup: function () {
            if (!this.popup) {
                this.popup = modal(
                    this.popUpList.options,
                    $(this.popUpList.element)
                );
            }

            return this.popup;
        },

        /**
         * @returns void
         */
        openPopup: function () {
            var shippingAddress = quote.shippingAddress();

            this.getPopup().openModal();

            if (shippingAddress.city && shippingAddress.postcode) {
                this.updateNearbyLocations(shippingAddress);
            }
        },

        /**
         * @param {Object} location
         * @returns void
         */
        selectPickupLocation: function (location) {
            pickupLocationsService.selectForShipping(location);
            this.getPopup().closeModal();
        },

        /**
         * @param {Object} location
         * @returns {*|Boolean}
         */
        isPickupLocationSelected: function (location) {
            return _.isEqual(this.selectedLocation(), location);
        },

        /**
         * @param {Object} address
         * @returns {*}
         */
        updateNearbyLocations: function (address) {
            var self = this;

            return pickupLocationsService
                .getNearbyLocations({
                    distanceFilter: {
                        radius: this.nearbySearchRadius,
                        country: this.defaultCountryId,
                        city: address.city,
                        postcode: address.postcode,
                        region: address.region
                    },
                    pageSize: this.nearbySearchLimit
                })
                .then(function (locations) {
                    self.nearbyLocations(locations);
                })
                .fail(function () {
                    self.nearbyLocations([]);
                });
        },

        /**
         * @returns {Boolean}
         */
        validatePickupInformation: function () {
            var emailValidationResult,
                loginFormSelector = this.loginFormSelector;

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = $(loginFormSelector + ' input[name=username]').valid() ? true : false;

                if (!emailValidationResult) {
                    $(this.loginFormSelector + ' input[name=username]').focus();

                    return false;
                }
            }

            return true;
        }
    });
});
