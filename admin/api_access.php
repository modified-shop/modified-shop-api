<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2021 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require('includes/application_top.php');

  $confirm_save_entry = ' onclick="ButtonClicked(this);"';
  $confirm_submit = defined('CONFIRM_SAVE_ENTRY') && CONFIRM_SAVE_ENTRY == 'true' ? ' onsubmit="return confirmSubmit(\'\',\''. SAVE_ENTRY .'\',this)"' : '';

  if (isset($_GET['action'])) {
    switch ($_GET['action']) {
      case 'save':

        // reset values before writing
        $admin_access_query = xtc_db_query("SELECT *
                                              FROM `api_access`
                                             WHERE customers_id = '" . (int)$_GET['cID'] . "'");
        $admin_access = xtc_db_fetch_array($admin_access_query);

        $fields = xtc_db_query("SHOW COLUMNS FROM ``api_access`` FROM `".DB_DATABASE."`");
        $columns = xtc_db_num_rows($fields);

        while ($field = xtc_db_fetch_array($fields)) {
          if ($field['Field'] != 'customers_id') {
            xtc_db_query("UPDATE `api_access`
                             SET ".$field['Field']." = '0'
                           WHERE customers_id = '".(int)$_GET['cID']."'");
          }
        }

        if (isset($_POST['access'])) foreach($_POST['access'] as $key){
          xtc_db_query("UPDATE `api_access`
                           SET ".$key." = '1'
                         WHERE customers_id = '".(int)$_GET['cID']."'");
        }
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID','action')).'cID=' . (int)$_GET['cID'], 'NONSSL'));
        break;
    }
  }

  if ($_GET['cID'] != '') {
    $allow_edit_query = xtc_db_query("SELECT customers_status,
                                             customers_firstname,
                                             customers_lastname
                                        FROM " . TABLE_CUSTOMERS . "
                                       WHERE customers_id = '" . (int)$_GET['cID'] . "'");
    $allow_edit = xtc_db_fetch_array($allow_edit_query);
    if (xtc_db_num_rows($allow_edit_query) < 1 || $allow_edit['customers_status'] == DEFAULT_CUSTOMERS_STATUS_ID_GUEST) {
      xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID','action')).'cID=' . (int)$_GET['cID'], 'NONSSL'));
    }
  }
  
  $naming_array = array(
    '10' => array(
      'name' =>  TEXT_HEADING_CUSTOMERS,
      'color' => '#eeeeee',
    ),
    '20' => array(
      'name' =>  TEXT_HEADING_CATEGORIES,
      'color' => '#ebbb97',
    ),
    '30' => array(
      'name' =>  TEXT_HEADING_PRODUCTS,
      'color' => '#aacfe2',
    ),   
    '31' => array(
      'name' =>  TEXT_HEADING_MANUFACTURERS,
      'color' => '#ebd397',
    ),
    '32' => array(
      'name' =>  TEXT_HEADING_ATTRIBUTES,
      'color' => '#afd088',
    ),
    '40' => array(
      'name' =>  TEXT_HEADING_ORDERS,
      'color' => '#617d8d',
    ),
    '50' => array(
      'name' =>  TEXT_HEADING_COUNTRIES,
      'color' => '#666666',
    ),
    '60' => array(
      'name' =>  TEXT_HEADING_SHIPPING,
      'color' => '#cb7272',
    ),
    '70' => array(
      'name' =>  TEXT_HEADING_CAMPAIGNS,
      'color' => '#8cd1ba',
    ),
    '80' => array(
      'name' =>  TEXT_HEADING_CURRENCIES,
      'color' => '#c689ab',
    ),
    '90' => array(
      'name' =>  TEXT_HEADING_LANGUAGES,
      'color' => '#ffaaa5',
    ),
    '100' => array(
      'name' =>  TEXT_HEADING_NEWSLETTER,
      'color' => '#dcedc1',
    ),
    '110' => array(
      'name' =>  TEXT_HEADING_CONFIGURATIONS,
      'color' => '#66545e',
    ),
    '120' => array(
      'name' =>  TEXT_HEADING_CONTENTS,
      'color' => '#a39193',
    ),
  );

require (DIR_WS_INCLUDES.'head.php');
?>
<script type="text/javascript">
  function set_checkbox(val, cid) {
    if (cid == 1) {
      var checked = 1;
    } else {
      var checked = $(".checkall"+val).is(':checked');
    }
    $(".access"+val).attr('checked', checked);
  }
</script>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <?php //left_navigation
    if (USE_ADMIN_TOP_MENU == 'false') {
      echo '<td class="columnLeft2">'.PHP_EOL;
      echo '<!-- left_navigation //-->'.PHP_EOL;
      require_once(DIR_WS_INCLUDES . 'column_left.php');
      echo '<!-- left_navigation eof //-->'.PHP_EOL;
      echo '</td>'.PHP_EOL;
    }
    ?>
    <!-- body_text //-->
    <td class="boxCenter" width="100%" valign="top">
      <div class="div_box mrg5">
        <div class="pageHeading pdg2"><?php echo TEXT_ACCOUNTING.' '.$allow_edit['customers_lastname'].' '.$allow_edit['customers_firstname'] . ' ['. (int)$_GET['cID'] .']'; ?>
          <div class="main flt-r"><?php echo xtc_draw_checkbox_field('complete', false) . ' ' . BUTTON_SET; ?></div>
        </div>
        <?php if ($_GET['cID'] == '1') { ?>
        <div class="main important_info" style="margin-top: 5px;">
          <?php  echo TEXT_ACCOUNTING_INFO ?> 
        </div>
        <?php } ?>
        <br/>

        <?php echo xtc_draw_form('accounting', basename($PHP_SELF), xtc_get_all_get_params(array('action'))  . 'action=save', 'post', 'enctype="multipart/form-data"' . $confirm_submit); ?>
          <table class="tableBoxCenter collapse">
            <tr>
              <td>
                <?php
                $customers_id = xtc_db_prepare_input($_GET['cID']);
                $admin_access_query = xtc_db_query("SELECT *
                                                      FROM `api_access`
                                                     WHERE customers_id = '" . (int)$_GET['cID'] . "'");
                if (xtc_db_num_rows($admin_access_query) < 1) {
                  xtc_db_query("INSERT INTO `api_access` (customers_id) VALUES ('" . (int)$_GET['cID'] . "')");
                  $admin_access_query = xtc_db_query("SELECT *
                                                        FROM `api_access`
                                                       WHERE customers_id = '" . (int)$_GET['cID'] . "'");
                }
                $admin_access = xtc_db_fetch_array($admin_access_query);

                $group_query = xtc_db_query("SELECT *
                                               FROM `api_access`
                                              WHERE customers_id = 'groups'");
                $group_access = xtc_db_fetch_array($group_query);

                $fields = xtc_db_query("SHOW COLUMNS FROM `api_access` FROM `".DB_DATABASE."`");
                while ($field = xtc_db_fetch_array($fields)) {
              
                  if ($field['Field'] != 'customers_id') {
                
                    $params = '';
                    $checked = false;
                    $params = '';
                    $checked = false;
                    $hidden_field = '';
                    if ($admin_access[$field['Field']] == '1') {
                      $checked = true;
                      if ($_GET['cID'] == '1') {
                        $params = ' disabled="disabled"';
                        $hidden_field =  xtc_draw_hidden_field('access[]', $field['Field']).PHP_EOL;
                      }
                    }

                    $accounting_array[$group_access[$field['Field']]][$field['Field']] = array(
                      'key' => $field['Field'],
                      'val' => $hidden_field.xtc_draw_checkbox_field('access[]', $field['Field'], $checked, '', $params.' class="access'.$group_access[$field['Field']].'"'),
                    );
                    ksort($accounting_array[$group_access[$field['Field']]]);
                  }
                }
                ksort($accounting_array);
                
                if (isset($accounting_array[0])) {
                  $accounting_tmp = $accounting_array[0];
                  unset($accounting_array[0]);
                  $accounting_array[0] = $accounting_tmp;
                }
                
                echo '<div class="multicolumn">';
                foreach ($accounting_array as $field => $accounting) {
                  ?>
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent" colspan="2" style="vertical-align:middle;"><?php echo $naming_array[$field]['name']; ?></td>
                      <td class="dataTableHeadingContent" style="vertical-align:middle;"><?php echo TEXT_ALLOWED.' '.xtc_draw_checkbox_field('checkall'.$field, '', '', '', 'class="checkall'.$field.'" onclick="set_checkbox('.$field.', '.$_GET['cID'].')"'); ?></td>
                    </tr>
                    <?php
                    foreach ($accounting as $array) {
                      ?>
                      <tr class="dataTableRow">
                        <td class="dataTableContent" style="width:18px; background:<?php echo $naming_array[$field]['color']; ?>;"></td>
                        <td class="dataTableContent" style="width:200px;"><?php echo $array['key']; ?></td>
                        <td class="dataTableContent" align="center"><?php echo $array['val']; ?></td>
                      </tr>
                      <?php
                    }
                    ?>
                    <tr><td>&nbsp;</td></tr>
                  </table>
                  <?php
                }
                echo '</div>';
                ?>
              </td>
            </tr>
          </table>
          <a class="button" href="<?php echo xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action')));?>"><?php echo BUTTON_BACK; ?></a>
          <input type="submit" class="button" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
        </form>
          
      </div>
    </td>
  </tr>
<!-- body_eof //-->
</table>

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<script>
  $('input[name="complete"]').click(function () {    
    $('input[name*="checkall"]').prop('checked', this.checked);
    $('input[name*="access"]').prop('checked', this.checked);
  });
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>