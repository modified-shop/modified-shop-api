<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  namespace api\v1\Action\Configuration;

  use api\v1\Action\BaseAction;
  use api\v1\Utility\LoggerHandler;
  use Psr\Log\LoggerInterface;
  use Exception;
  
  /**
   * Service.
   */
  trait ConfigurationGetAction
  {
      /**
       * Read a configuration by the given configuration group id.
       *
       * @param int $configurationGroupId The configuration group id
       *
       * @throws Exception
       *
       * @return array The configuration data
       */
      public function GetConfiguration(int $configurationGroupId): array
      {
          // Input validation
          if (empty($configurationGroupId)) {
              throw new Exception('Configuration Group ID required');
          }
          
          $configuration_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_CONFIGURATION."
                                                WHERE configuration_group_id = '".(int)$configurationGroupId."'");
          if (xtc_db_num_rows($configuration_query) < 1) {
              $this->errormessage(sprintf('Configuration Group not found: %s', $configurationGroupId));
          } else {
              $data = [];
              while ($configuration = xtc_db_fetch_array($configuration_query)) {
                unset($configuration['use_function']);
                unset($configuration['set_function']);

                $data[] = $configuration;
              }
          }
          
          $result = $this->encode_request($data);
          return $result;
      }

      /**
       * Read a configuration group by the given configuration group id.
       *
       * @param int $configurationGroupId The configuration group id
       *
       * @throws Exception
       *
       * @return array The configuration group data
       */
      public function GetConfigurationGroupsDetails(int $configurationGroupId): array
      {
          // Input validation
          if (empty($configurationGroupId)) {
              throw new Exception('Configuration Group ID required');
          }
          
          $configuration_query = xtc_db_query("SELECT *
                                                 FROM ".TABLE_CONFIGURATION_GROUP."
                                                WHERE configuration_group_id = '".(int)$configurationGroupId."'");
          if (xtc_db_num_rows($configuration_query) < 1) {
              $this->errormessage(sprintf('Configuration Group not found: %s', $configurationGroupId));
          } else {
              $configuration = xtc_db_fetch_array($configuration_query);

              if (isset($this->options['with'])) {
                  $with = explode(',', $this->options['with']);
                  if (in_array('configuration', $with) !== false) {
                      $configuration['configuration'] = $this->GetConfiguration($configurationGroupId);
                  }
              }
          }
          
          $result = $this->encode_request($configuration);
          return $result;
      }

      /**
       * Read a configuration group by the given configuration group id.
       *
       * @param int $configurationGroupId The configuration group id
       * @param mixed[] $options
       *
       * @throws Exception
       *
       * @return array The configuration group data
       */
      public function GetSingleConfigurationGroup(int $configurationGroupId, array $options): array
      {
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);

          // Input validation
          if (empty($configurationGroupId)) {
              throw new Exception('Configuration Group ID required');
          }

          $result = $this->GetConfigurationGroupsDetails($configurationGroupId);
          return $result;
      }

      /**
       * Read configuration by given conditions
       *
       * @param mixed[] $options
       *
       * @return array The configuration data
       */
      public function GetConfigurationGroups(array $options): array
      {          
          /* Store passed in options overwriting any defaults */
          $this->hydrate($options);
          
          $count_query = xtc_db_query("SELECT count(*) as total
                                         FROM ".TABLE_CONFIGURATION_GROUP);
          $count = xtc_db_fetch_array($count_query);
          
          if ($count['total'] < 1) {
              $this->errormessage('no Configuration found');
          }
          
          $data = [];
          $configuration_query = xtc_db_query("SELECT configuration_group_id
                                                 FROM ".TABLE_CONFIGURATION_GROUP."
                                             ORDER BY configuration_group_id ASC
                                                LIMIT ".(($this->options['page'] - 1) * $this->options['limit']).", ".$this->options['limit']);
          while ($configuration = xtc_db_fetch_array($configuration_query)) {
              $data[] = $this->GetConfigurationGroupsDetails($configuration['configuration_group_id']);
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
