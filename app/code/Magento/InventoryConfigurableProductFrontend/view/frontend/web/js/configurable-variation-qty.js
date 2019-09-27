/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Configurable variation left qty.
 */
define([
    'jquery',
    'underscore',
    'mage/url'
], function ($, _, urlBuilder) {
    'use strict';

    return function (productId, salesChannel, code) {
        var selectorInfoStockSkuQty = '.availability.only',
            selectorInfoStockSkuQtyValue = '.availability.only > strong',
            productQtyInfoBlock = $(selectorInfoStockSkuQty),
            productQtyInfo = $(selectorInfoStockSkuQtyValue);

        if (!_.isUndefined(productId) && productId !== null) {
            $.ajax({
                url: urlBuilder.build('inventory_catalog/product/getQty/'),
                dataType: 'json',
                data: {
                    'id': productId,
                    'channel': salesChannel,
                    'code': code
                }
            }).done(function (response) {
                if (response.qty !== null) {
                    productQtyInfo.text(response.qty);
                    productQtyInfoBlock.show();
                } else {
                    productQtyInfoBlock.hide();
                }
            }).fail(function () {
                productQtyInfoBlock.hide();
            });
        } else {
            productQtyInfoBlock.hide();
        }
    };
});
