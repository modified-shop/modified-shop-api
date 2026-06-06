<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Campaign;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  final class CampaignAction extends BaseAction
  {
      use CampaignGetAction;
      use CampaignDeleteAction;
      
      /**
       * Insert an campaign by the given options.
       *
       * @param mixed[] $options
       *
       * @return array The campaign data
       */
      public function InsertCampaign(array $options): array
      {
          $campaign = $this->InsertUpdateCampaign(0, $options);
          
          return $campaign;
      }

      /**
       * Insert or Update an campaign by the given campaign id.
       *
       * @param int $campaignId The campaign id
       * @param mixed[] $options
       *
       * @return array The campaign data
       */
      public function InsertUpdateCampaign(int $campaignId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          if ($campaignId > 0) {
              $action = 'update';
              $campaign_query = xtc_db_query("SELECT *
                                                FROM ".TABLE_CAMPAIGNS."
                                               WHERE campaigns_id = '".(int)$campaignId."'");
              if (xtc_db_num_rows($campaign_query) < 1) {
                  return $this->errormessage(sprintf('Campaign not found: %s', $campaignId));
              } else {
                  $campaign = xtc_db_fetch_array($campaign_query);
                  $campaign['last_modified'] = 'now()';
              }
          } else {
              $action = 'insert';
              $campaign = $this->getDefaultTableValues(TABLE_CAMPAIGNS);
              $campaign['date_added'] = 'now()';
          }

          foreach ($campaign as $key => $value) {
              if (isset($this->options[$key])) {
                  $campaign[$key] = $this->options[$key];
              }
          }

          if ($action == 'insert') {
              $check_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_CAMPAIGNS."
                                            WHERE campaigns_refID = '".xtc_db_input($campaign['campaigns_refID'])."'");
              if (xtc_db_num_rows($check_query) > 0) {
                  return $this->errormessage('Campaign refId already exists', 400);
              }
          }
          
          // Input validation
          $this->checkTableData(TABLE_CAMPAIGNS, $campaign);
          unset($campaign['campaigns_id']);

          xtc_db_perform(TABLE_CAMPAIGNS, $campaign, $action, "campaigns_id = '".(int)$campaignId."'");
          if ($action == 'insert') {
              $campaignId = xtc_db_insert_id();
          }

          return $this->GetSingleCampaign($campaignId);
      }

  }
