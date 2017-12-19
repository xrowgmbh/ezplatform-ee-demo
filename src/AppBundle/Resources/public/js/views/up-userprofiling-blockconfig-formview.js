/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('up-userprofiling-blockconfig-formview', function (Y) {
    'use strict';

    /**
     * Provides the User Profiling Block Config Form view class
     *
     * @module up-userprofiling-blockconfig-formview
     */
    Y.namespace('up');

    /**
     * The StudioUI User Profiling block config form view
     *
     * @namespace up
     * @class UserProfilingBlockConfigFormView
     * @constructor
     * @extends eZS.BlockPopupFormView
     */
    Y.up.UserProfilingBlockConfigFormView = Y.Base.create('userProfilingBlockConfigFormView', Y.eZS.BlockPopupFormView, [], {
        initializer: function () {
            this.get('container').addClass(this._generateViewClassName(Y.eZS.BlockPopupFormView.NAME));
            this.addFormFieldViewsMapItem('items', Y.upItemsFieldView);
        },

        _renderFields: function () {
            this.constructor.superclass._renderFields.apply(this, arguments);

            var itemsField,
                itemsFieldValue;

            this.get('formFieldViews').forEach(function (field) {
                if (field.get('id') === 'items') {
                    itemsField = field;
                    itemsFieldValue = field.get('values');
                }
            });

            this.set('itemsField', itemsField);
            this.set('itemsFieldValue', itemsFieldValue);

            this.get('itemsField').set('items', this.get('itemsFieldValue')[0]).render();
        }
    }, {
        ATTRS: {
            itemsField: {},

            itemsFieldValue: {}
        }
    });
});
