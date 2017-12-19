/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('up-userprofiling-blockview', function (Y) {
    'use strict';

    /**
     * Provides the User Profiling Block view class
     *
     * @module up-userprofiling-blockview
     */
    Y.namespace('up');

    /**
     * The StudioUI userprofiling block view
     *
     * @namespace up
     * @class UserProfilingBlockView
     * @constructor
     * @extends eZS.BlockView
     */
    Y.up.UserProfilingBlockView = Y.Base.create('userProfilingBlockView', Y.eZS.BlockView, [], {
    }, {
        ATTRS: {
            viewClassName: {
                value: 'up.UserProfilingBlockView',
                readOnly: true
            },

            /**
             * Block edit form view instance {{#crossLink "up.UserProfilingBlockConfigFormView"}}up.UserProfilingBlockConfigFormView{{/crossLink}}
             *
             * @attribute editForm
             * @type Y.View
             * @default up.UserProfilingBlockConfigFormView
             */
            editForm: {
                valueFn: function () {
                    return new Y.up.UserProfilingBlockConfigFormView({bubbleTargets: this});
                }
            }
        }
    });
});
