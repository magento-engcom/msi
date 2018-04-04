/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        wasInitialValueSet: false,
        defaults: {
            links: {
                value: null
            }
        },

        /** @inheritdoc */
        getInitialValue: function () {
            var values = [this.source.get(this.dataScope), this.default],
                value;

            values.some(function (v) {
                if (v !== null && v !== undefined) {
                    value = v;

                    return true;
                }

                return false;
            });

            return this.normalizeData(value);
        },

        /** @inheritdoc */
        setInitialValue: function () {
            this.wasInitialValueSet = !!this.initialValue;

            return this._super();
        },

        /** @inheritdoc */
        setDifferedFromDefault: function () {
            this._super();

            if (parseFloat(this.initialValue) !== parseFloat(this.value())) {
                this.source.set(this.dataScope, this.value());
            } else if (this.wasInitialValueSet === true) {
                this.source.remove(this.dataScope);
            }

            this.wasInitialValueSet = !!this.initialValue;
        }
    });
});
