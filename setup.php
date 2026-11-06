<?php

/**
 * ---------------------------------------------------------------------
 * MailTo — "Send an email to the requester" button in the ticket form.
 * ---------------------------------------------------------------------
 * Adds a one-click button that opens a pre-filled email:
 *   - subject: [<entity tag> #<ticket id>] <ticket title>
 *   - recipients: ticket requester(s) + the mail collector
 * The subject tag is read from GLPI's native per-entity setting
 * (notification_subject_tag), so the collector's mailgate always matches it.
 *
 * @copyright 2025 SOFIA.nc
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 */

define('PLUGIN_MAILTO_VERSION', '1.0.0');

// Minimum GLPI version (inclusive) and maximum (exclusive)
define('PLUGIN_MAILTO_MIN_GLPI_VERSION', '11.0.0');
define('PLUGIN_MAILTO_MAX_GLPI_VERSION', '12.9.99');

/**
 * Plugin initialization: hook declaration.
 */
function plugin_init_mailto()
{
    global $PLUGIN_HOOKS;

    // Button injected at the top of a ticket form (before the form itself).
    // In GLPI 11 the pre_item_form/post_item_form hooks are no longer triggered,
    // pre_show_item is the one actually fired by the core.
    $PLUGIN_HOOKS['pre_show_item']['mailto'] = 'plugin_mailto_pre_show_item';
}

/**
 * Version and metadata shown in Configuration > Plugins.
 */
function plugin_version_mailto()
{
    return [
        'name'         => 'MailTo',
        'version'      => PLUGIN_MAILTO_VERSION,
        'author'       => 'SOFIA.nc',
        'license'      => 'GPL-2.0-or-later',
        'homepage'     => 'https://github.com/sofia-nc/mailto',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_MAILTO_MIN_GLPI_VERSION,
                'max' => PLUGIN_MAILTO_MAX_GLPI_VERSION,
            ],
        ],
    ];
}

/**
 * Configuration check (nothing blocking for this lightweight plugin).
 */
function plugin_mailto_check_config($verbose = false)
{
    return true;
}
