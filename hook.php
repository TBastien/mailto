<?php

/**
 * ---------------------------------------------------------------------
 * MailTo — installation and display hook.
 * ---------------------------------------------------------------------
 * No database table, no configuration: all logic lives here.
 *
 * @license https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 */

/**
 * Installation: nothing to create in the database.
 *
 * @return bool
 */
function plugin_mailto_install()
{
    return true;
}

/**
 * Uninstallation.
 *
 * @return bool
 */
function plugin_mailto_uninstall()
{
    return true;
}

/**
 * Hook pre_show_item: render the button at the top of a ticket form.
 *
 * @param array $params ['item' => CommonDBTM, 'options' => array]
 */
function plugin_mailto_pre_show_item(array $params)
{
    $item = $params['item'] ?? null;

    if (!$item instanceof Ticket || !$item->getID()) {
        return;
    }

    // Reserved to people who can act on the ticket (techs, admins).
    if (!$item->canUpdateItem()) {
        return;
    }

    $mailto = plugin_mailto_build_mailto($item);
    if ($mailto === '') {
        return;
    }

    echo '<div class="mailto-toolbar" style="margin: 0 0 15px 0;">';
    echo '<a class="btn btn-primary" href="' . htmlspecialchars($mailto, ENT_QUOTES, 'UTF-8') . '">';
    echo '<i class="fa-solid fa-envelope"></i>&nbsp;' . __('Send an email to the requester', 'mailto');
    echo '</a>';
    echo '</div>';
}

/**
 * Build the pre-filled mailto: link.
 *
 * @return string mailto link, or empty string if no valid recipient.
 */
function plugin_mailto_build_mailto(Ticket $ticket): string
{
    $id    = (int) $ticket->getID();
    $title = trim((string) ($ticket->fields['name'] ?? ''));

    $recipients = array_merge(
        plugin_mailto_get_requester_emails($ticket),
        [plugin_mailto_get_collector_email()]
    );

    // Clean, validate, deduplicate.
    $recipients = array_filter(array_map('trim', $recipients), static function ($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    });
    $recipients = array_values(array_unique($recipients));

    if (empty($recipients)) {
        return '';
    }

    $tag     = plugin_mailto_get_subject_tag($ticket);
    $subject = sprintf('[%s #%07d] %s', $tag, $id, $title);
    $subject = trim($subject);

    return 'mailto:' . implode(',', $recipients)
        . '?subject=' . rawurlencode($subject);
}

/**
 * Read the notification subject tag for the ticket entity.
 *
 * Mirrors NotificationTargetCommonITILObject::getSubjectPrefix(): uses the
 * per-entity "notification_subject_tag" setting, defaulting to "GLPI".
 *
 * @return string
 */
function plugin_mailto_get_subject_tag(Ticket $ticket): string
{
    $tag = trim((string) Entity::getUsedConfig(
        'notification_subject_tag',
        $ticket->getEntityID(),
        '',
        ''
    ));

    return $tag !== '' ? $tag : 'GLPI';
}

/**
 * Requester email addresses of the ticket.
 *
 * Handles both cases:
 *  - requester is a GLPI user (account default email)
 *  - anonymous requester (alternative email set without an account)
 *
 * @return string[]
 */
function plugin_mailto_get_requester_emails(Ticket $ticket): array
{
    $emails = [];

    $actor      = new Ticket_User();
    $actors     = $actor->getActors((int) $ticket->getID());
    $requesters = $actors[CommonITILActor::REQUESTER] ?? [];

    foreach ($requesters as $requester) {
        $email = '';
        if ((int) ($requester['users_id'] ?? 0) > 0) {
            $email = UserEmail::getDefaultForUser((int) $requester['users_id']);
        } elseif (!empty($requester['alternative_email'])) {
            $email = $requester['alternative_email'];
        }
        $email = plugin_mailto_extract_email((string) $email);
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $emails[] = $email;
        }
    }

    return $emails;
}

/**
 * Email address of the mail collector (first active one found).
 *
 * @return string
 */
function plugin_mailto_get_collector_email(): string
{
    global $DB;

    $iterator = $DB->request([
        'SELECT' => ['name'],
        'FROM'   => MailCollector::getTable(),
        'WHERE'  => ['is_active' => 1],
    ]);

    foreach ($iterator as $row) {
        $email = plugin_mailto_extract_email((string) ($row['name'] ?? ''));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            return $email;
        }
    }

    return '';
}

/**
 * Extract an email address from a possibly "Name <email>" value.
 *
 * @return string
 */
function plugin_mailto_extract_email(string $value): string
{
    $value = trim($value);
    if (preg_match('/<([^>]+@[^>]+)>/', $value, $m)) {
        $value = trim($m[1]);
    }
    return $value;
}
