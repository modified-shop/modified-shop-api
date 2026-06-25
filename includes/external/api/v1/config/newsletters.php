<?php

/**
 * /includes/external/api/v1/config/newsletters.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

// newsletters
$app->get('/newsletters/recipients', \api\v1\Service\Newsletter\GetNewsletterRecipients::class);
$app->get('/newsletters/recipients/history', \api\v1\Service\Newsletter\GetNewsletterRecipientsHistory::class);
$app->get('/newsletters/recipients/{id}', \api\v1\Service\Newsletter\GetSingleNewsletterRecipients::class);
$app->get('/newsletters/recipients/history/{id}', \api\v1\Service\Newsletter\GetSingleNewsletterRecipientsHistory::class);

// insert newsletters
$app->post('/newsletters/recipients', \api\v1\Service\Newsletter\InsertNewsletterRecipients::class);
$app->post('/newsletters/recipients/{id}', \api\v1\Service\Newsletter\InsertUpdateNewsletterRecipients::class);

// update newsletters
$app->put('/newsletters/recipients/{id}', \api\v1\Service\Newsletter\InsertUpdateNewsletterRecipients::class);

// delete newsletters
$app->delete('/newsletters/recipients/{id}', \api\v1\Service\Newsletter\DeleteNewsletterRecipients::class);
