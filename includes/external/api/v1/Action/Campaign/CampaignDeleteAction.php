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
  trait CampaignDeleteAction
  {
      /**
       * Delete a campaign by the given campaign id.
       *
       * @param int $campaignId The currency id
       *
       * @throws Exception
       *
       * @return void
       */
      public function DeleteCampaign(int $campaignId): void
      {
          // Input validation
          if (empty($campaignId)) {
              throw new Exception('Campaign ID required');
          }

          $campaign_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_CAMPAIGNS."
                                           WHERE campaigns_id = '".(int)$campaignId."'");
          if (xtc_db_num_rows($campaign_query) < 1) {
            $this->errormessage(sprintf('Campaign not found: %s', $campaignId));
          } else {
              //delete
              xtc_db_query("DELETE FROM ".TABLE_CAMPAIGNS." WHERE campaigns_id = '".(int)$campaignId."'");
          }
          
          $this->logger->info(sprintf('Campaign deleted successfully: %s', $campaignId));
      }

  }
