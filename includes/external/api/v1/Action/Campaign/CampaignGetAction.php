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
  trait CampaignGetAction
  {
      /**
       * Read campaigns by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The campaigns data
       */
      public function GetCampaigns(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CAMPAIGNS);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Campaign found');
          }
          
          $data = [];
          $campaigns_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_CAMPAIGNS."
                                                  ".$where."
                                         ORDER BY campaigns_id ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($campaigns = xtc_db_fetch_array($campaigns_query)) {
              $data[] = $this->encode_request($campaigns);
          }
          
          $result = [
              'paging' => [
                  'total' => $count['total']
              ],
              'data' => $data
          ];
          
          if ($count['total'] > count($data)) {
              if ($this->options['page'] > 1) {
                  $result['paging']['prev'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] - 1);
              }
              if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                  $result['paging']['next'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] + 1);
              }
          }
          
          return $result;
      }

      /**
       * Read campaigns by given conditions
       *
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The campaigns data
       */
      public function GetCampaignsIp(string $refId, array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          // Input validation
          if (empty($refId)) {
              throw new Exception('Ref ID required');
          }

          if ($this->options['limit'] > 50) $this->options['limit'] = 50;
          $this->options['page'] = (abs((int)$this->options['page']) > 0) ? abs((int)$this->options['page']) : 1;
                                                        
          if ((int)$this->options['from'] > 0) {
              $conditions[] = " time >= '".date('Y-m-d H:i:s', (int)$this->options['from'])."' ";
          }
          if ((int)$this->options['to'] > 0) {
              $conditions[] = " time <= '".date('Y-m-d H:i:s', (int)$this->options['to'])."' ";
          }
          
          $where = '';
          if (count($conditions) > 0) {
            $where = " AND ".implode(' AND ', $conditions);
          }

          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CAMPAIGNS_IP."
                                        WHERE campaign = '".xtc_db_input($refId)."'
                                              ".$where);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              throw new Exception('no Campaign Ip found');
          }
          
          $data = [];
          $campaigns_query = xtc_db_query("SELECT *
                                             FROM ".TABLE_CAMPAIGNS."
                                            WHERE campaign = '".xtc_db_input($refId)."'
                                                  ".$where."
                                         ORDER BY time ASC
                                            LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($campaigns = xtc_db_fetch_array($campaigns_query)) {
              $data[] = $this->encode_request($campaigns);
          }
          
          $result = [
              'paging' => [
                  'total' => $count['total']
              ],
              'data' => $data
          ];
          
          if ($count['total'] > count($data)) {
              if ($this->options['page'] > 1) {
                  $result['paging']['prev'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] - 1);
              }
              if (((($this->options['page'] - 1) * $this->options['limit']) + $this->options['limit']) < $count['total']) {
                  $result['paging']['next'] = HTTPS_SERVER.DIR_WS_CATALOG.ltrim($this->options['path'], '/').'?'.xtc_get_all_get_params(array('page')).'page='.($this->options['page'] + 1);
              }
          }
          
          return $result;
      }
    
  }
