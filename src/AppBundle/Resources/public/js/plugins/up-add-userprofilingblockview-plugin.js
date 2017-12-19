/*
 * Copyright (C) eZ Systems AS. All rights reserved.
 * For full copyright and license information view LICENSE file distributed with this source code.
 */
YUI.add('up-add-userprofilingblockview-plugin', function (Y) {
    'use strict';

    /**
     * Provides a plugin to add the User Profiling block to landing page creator
     *
     * @module up-add-userprofilingblockview-plugin
     */
    Y.namespace('up.Plugin');

    var PLUGIN_NAME = 'addUserProfilingBlockPlugin';

    /**
     * Adds the User Profiling block view to landing page creator view
     *
     * @namespace up.Plugin
     * @class AddUserProfilingBlock
     * @constructor
     * @extends Plugin.Base
     */
    Y.up.Plugin.AddUserProfilingBlock = Y.Base.create(PLUGIN_NAME, Y.Plugin.Base, [], {
        initializer: function () {
            this.get('host').addBlock('userprofiling', Y.up.UserProfilingBlockView);
        }
    }, {
        NS: PLUGIN_NAME
    });

    Y.eZ.PluginRegistry.registerPlugin(
        Y.up.Plugin.AddUserProfilingBlock,
        [
            'landingPageCreatorView',
            'dynamicLandingPageCreatorView',
            'dynamicLandingPageEditorView'
        ]
    );
});
