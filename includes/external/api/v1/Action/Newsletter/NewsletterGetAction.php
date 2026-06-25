<?php

/**
 * /includes/external/api/v1/Action/Newsletter/NewsletterGetAction.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace api\v1\Action\Newsletter;

use api\v1\Action\BaseAction;
use api\v1\Utility\LoggerHandler;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Service.
 */
trait NewsletterGetAction
{
    /**
     * Read a newsletter by the given newsletter id.
     *
     * @param int $newsletterId The newsletter id
     *
     * @throws Exception
     *
     * @return array The newsletter data
     */
    public function GetSingleNewsletterRecipients(int $newsletterId): array
    {
        // Input validation
        if (empty($newsletterId)) {
            throw new Exception('Newsletter ID required');
        }

        $newsletter_query = xtc_db_query("SELECT *
                                              FROM " . TABLE_NEWSLETTER_RECIPIENTS . "
                                             WHERE mail_id = '" . (int)$newsletterId . "'");
        if (xtc_db_num_rows($newsletter_query) < 1) {
            return $this->errormessage(sprintf('Newsletter not found: %s', $newsletterId));
        } else {
            $newsletter = xtc_db_fetch_array($newsletter_query);

            if (isset($this->options['with'])) {
                $with = explode(',', $this->options['with']);
                if (in_array('history', $with) !== false) {
                    $this->throw_exception = false;
                    $newsletter['history'] = $this->GetSingleNewsletterRecipientsHistory($newsletter['customers_email_address']);
                }
            }
        }

        $result = $this->encode_request($newsletter);
        return $result;
    }

    /**
     * Read newsletters by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The newsletters data
     */
    public function GetNewsletterRecipients(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $conditions = [];
        if (isset($this->options['status']) && !empty(preg_replace('/[^\d\,]/', '', $this->options['status']))) {
            $conditions[] = " mail_status IN (" . preg_replace('/[^\d\,]/', '', $this->options['status']) . ") ";
        }
        if (isset($this->options['mail'])) {
            require_once(DIR_FS_INC . 'xtc_validate_email.inc.php');
            if (xtc_validate_email($this->options['mail']) !== false) {
                $conditions[] = " customers_email_address LIKE ('%" . xtc_db_input($this->options['mail']) . "%') ";
            }
        }
        if ((int)$this->options['from'] > 0) {
            $conditions[] = " date_added >= '" . date('Y-m-d H:i:s', (int)$this->options['from']) . "' ";
        }
        if ((int)$this->options['to'] > 0) {
            $conditions[] = " date_added <= '" . date('Y-m-d H:i:s', (int)$this->options['to']) . "' ";
        }

        $where = '';
        if (count($conditions) > 0) {
            $where = " WHERE " . implode(' AND ', $conditions);
        }

        $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM " . TABLE_NEWSLETTER_RECIPIENTS . "
                                              " . $where);
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Newsletter found');
        }

        $data = [];
        $newsletters_query = xtc_db_query("SELECT mail_id
                                               FROM " . TABLE_NEWSLETTER_RECIPIENTS . "
                                                    " . $where . "
                                           ORDER BY mail_id ASC
                                              LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($newsletters = xtc_db_fetch_array($newsletters_query)) {
            $data[] = $this->GetSingleNewsletterRecipients($newsletters['mail_id']);
        }

        $result = [
            'paging' => [
                'total' => $count['total']
            ],
            'data' => $data
        ];

        if ($count['total'] > count($data)) {
            if ($this->options['page'] > 1) {
                $result['paging']['prev'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] - 1);
            }
            if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                $result['paging']['next'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] + 1);
            }
        }

        return $result;
    }

    /**
     * Read a newsletter by the given newsletter id.
     *
     * @param int $newsletterId The newsletter id
     *
     * @throws Exception
     *
     * @return array The newsletter data
     */
    public function GetSingleNewsletterRecipientsHistory(string $newsletterEmailAddress): array
    {
        // Input validation
        if (empty($newsletterEmailAddress)) {
            throw new Exception('Newsletter Email Address required');
        }

        $newsletter_query = xtc_db_query("SELECT *
                                              FROM " . TABLE_NEWSLETTER_RECIPIENTS_HISTORY . "
                                             WHERE customers_email_address = '" . xtc_db_input($newsletterEmailAddress) . "'");
        if (xtc_db_num_rows($newsletter_query) < 1 && $this->throw_exception === true) {
            return $this->errormessage(sprintf('Newsletter Email Address not found: %s', $newsletterEmailAddress));
        } else {
            $data = [];
            while ($newsletter = xtc_db_fetch_array($newsletter_query)) {
                $data[] = $newsletter;
            }
        }

        $result = $this->encode_request($data);
        return $result;
    }

    /**
     * Read newsletters by given conditions
     *
     * @param mixed[] $options
     *
     * @return array The newsletters data
     */
    public function GetNewsletterRecipientsHistory(array $options): array
    {
        /* Store passed in options overwriting any defaults */
        $this->hydrate($options);

        $conditions = [];
        if ((int)$this->options['from'] > 0) {
            $conditions[] = " date_added >= '" . date('Y-m-d H:i:s', (int)$this->options['from']) . "' ";
        }
        if ((int)$this->options['to'] > 0) {
            $conditions[] = " date_added <= '" . date('Y-m-d H:i:s', (int)$this->options['to']) . "' ";
        }

        $where = '';
        if (count($conditions) > 0) {
            $where = " WHERE " . implode(' AND ', $conditions);
        }

        $count_query = xtc_db_query("SELECT count(DISTINCT customers_email_address) as total
                                         FROM " . TABLE_NEWSLETTER_RECIPIENTS_HISTORY . "
                                              " . $where);
        $count = xtc_db_fetch_array($count_query);

        if ($count['total'] < 1) {
            return $this->errormessage('no Newsletter History found');
        }

        $data = [];
        $newsletters_query = xtc_db_query("SELECT customers_email_address
                                             FROM " . TABLE_NEWSLETTER_RECIPIENTS_HISTORY . "
                                                  " . $where . "
                                         GROUP BY customers_email_address
                                         ORDER BY customers_email_address ASC
                                            LIMIT " . (($this->options['page'] - 1) * $this->options['limit']) . ", " . $this->options['limit']);
        while ($newsletters = xtc_db_fetch_array($newsletters_query)) {
            $data[$newsletters['customers_email_address']] = $this->GetSingleNewsletterRecipientsHistory($newsletters['customers_email_address']);
        }

        $result = [
            'paging' => [
                'total' => $count['total']
            ],
            'data' => $data
        ];

        if ($count['total'] > count($data)) {
            if ($this->options['page'] > 1) {
                $result['paging']['prev'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] - 1);
            }
            if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                $result['paging']['next'] = HTTPS_SERVER . DIR_WS_CATALOG . ltrim($this->options['path'], '/') . '?' . xtc_get_all_get_params(array('page')) . 'page=' . ($this->options['page'] + 1);
            }
        }

        return $result;
    }
}
