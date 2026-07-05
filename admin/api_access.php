<?php

/**
 * /admin/api_access.php
 *
 * @package   modified-shop
 * @link      https://www.modified-shop.org
 *
 * Copyright (c) modified eCommerce Shopsoftware
 *
 * Released under the GNU General Public License (GPL)
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

require('includes/application_top.php');

$confirm_save_entry = ' onclick="ButtonClicked(this);"';
$confirm_submit = defined('CONFIRM_SAVE_ENTRY') && CONFIRM_SAVE_ENTRY == 'true' ? ' onsubmit="return confirmSubmit(\'\',\'' . SAVE_ENTRY . '\',this)"' : '';

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'save':
            // reset values before writing
            $admin_access_query = xtc_db_query("SELECT *
                                                  FROM `api_access`
                                                 WHERE customers_id = '" . (int)$_GET['cID'] . "'");
            $admin_access = xtc_db_fetch_array($admin_access_query);

            $fields = xtc_db_query("SHOW COLUMNS FROM ``api_access`` FROM `" . DB_DATABASE . "`");
            $columns = xtc_db_num_rows($fields);

            $valid_columns = array();
            while ($field = xtc_db_fetch_array($fields)) {
                if ($field['Field'] != 'customers_id') {
                    $valid_columns[] = $field['Field'];

                    xtc_db_query("UPDATE `api_access`
                             SET `" . $field['Field'] . "` = '0'
                           WHERE customers_id = '" . (int)$_GET['cID'] . "'");
                }
            }

            if (isset($_POST['access'])) {
                foreach ($_POST['access'] as $key) {
                    if (in_array($key, $valid_columns, true)) {
                        xtc_db_query("UPDATE `api_access`
                                 SET `" . $key . "` = '1'
                               WHERE customers_id = '" . (int)$_GET['cID'] . "'");
                    }
                }
            }
            xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID','action')) . 'cID=' . (int)$_GET['cID'], 'NONSSL'));
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
        xtc_redirect(xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('cID','action')) . 'cID=' . (int)$_GET['cID'], 'NONSSL'));
    }
}

  // Resource names and colors, keyed by resource_name directly (group_id is
  // now a database-managed auto-increment id with no meaning to the app -
  // display order comes from the dedicated sort_order column instead).
  $naming_array = array();
  $group_names_query = xtc_db_query("SELECT resource_name,
                                            color
                                       FROM `api_access_groups`
                                   ORDER BY sort_order");
  while ($group_name_row = xtc_db_fetch_array($group_names_query)) {
      $naming_array[$group_name_row['resource_name']] = array(
          'name' => $group_name_row['resource_name'],
          'color' => $group_name_row['color'],
      );
  }

  // Longest names first, so one resource name can't shadow another that
  // starts with the same letters (e.g. "Product" vs "Products").
  $resource_name_list = array_keys($naming_array);
  usort($resource_name_list, function ($a, $b) {
      return strlen($b) - strlen($a);
  });

  // Bucket for legacy/unrecognized columns - added after building
  // $resource_name_list above so "Other" itself is never matched as a prefix.
  $naming_array['Other'] = array(
      'name' => defined('TEXT_ACCOUNTING_OTHER') ? TEXT_ACCOUNTING_OTHER : 'Other',
      'color' => '#d9d9d9',
  );

  require(DIR_WS_INCLUDES . 'head.php');
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
<style>
.accounting_container {
  display: flex;
  margin: 0px -10px;
}
.accounting_col {
  width: 50%;
  padding: 10px;
  box-sizing: border-box;
}

.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow {
  cursor:pointer;
}
.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow:hover .dataTableHeadingContent {
  background-color:#ddd;
}
.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow em {
  position:relative;
  top:1px;
  left:5px;
  z-index:0;
}
.accounting_col .tableBoxCenter.collapse .dataTableHeadingRow input[type=checkbox].ChkBox:not(old) {
  position:relative;
  z-index:1;
}
</style>
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
        echo '<td class="columnLeft2">' . PHP_EOL;
        echo '<!-- left_navigation //-->' . PHP_EOL;
        require_once(DIR_WS_INCLUDES . 'column_left.php');
        echo '<!-- left_navigation eof //-->' . PHP_EOL;
        echo '</td>' . PHP_EOL;
    }
    ?>
    <!-- body_text //-->
    <td class="boxCenter" width="100%" valign="top">
      <div class="div_box mrg5">
        <div class="pageHeading pdg2"><?php echo TEXT_ACCOUNTING . ' ' . $allow_edit['customers_lastname'] . ' ' . $allow_edit['customers_firstname'] . ' [' . (int)$_GET['cID'] . ']'; ?>
          <div class="main flt-r"><?php echo xtc_draw_checkbox_field('complete', false) . ' ' . BUTTON_SET; ?></div>
        </div>
        <?php if (((int)$_GET['cID']) == '1') { ?>
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
                $customers_id = (int)$_GET['cID'];
                $admin_access_query = xtc_db_query("SELECT *
                                                      FROM `api_access`
                                                     WHERE customers_id = '" . $customers_id . "'");
                if (xtc_db_num_rows($admin_access_query) < 1) {
                    xtc_db_query("INSERT INTO `api_access` (customers_id) VALUES ('" . $customers_id . "')");
                    $admin_access_query = xtc_db_query("SELECT *
                                                          FROM `api_access`
                                                         WHERE customers_id = '" . $customers_id . "'");
                }
                $admin_access = xtc_db_fetch_array($admin_access_query);

                $accounting_array = array();

                // Permission columns are qualified as `{ResourceName}{Action}`
                $fields = xtc_db_query("SHOW COLUMNS FROM `api_access` FROM `" . DB_DATABASE . "`");
                while ($field = xtc_db_fetch_array($fields)) {
                    if ($field['Field'] != 'customers_id') {
                        $params = '';
                        $checked = false;
                        $params = '';
                        $checked = false;
                        $hidden_field = '';
                        if ($admin_access[$field['Field']] == '1') {
                            $checked = true;
                            if ($customers_id == 1) {
                                $params = ' disabled="disabled"';
                                $hidden_field =  xtc_draw_hidden_field('access[]', $field['Field']) . PHP_EOL;
                            }
                        }

                        $field_label_key = 'Other';
                        $field_label = $field['Field'];
                        foreach ($resource_name_list as $resource_name) {
                            $name_length = strlen($resource_name);
                            if (
                                strncmp($field['Field'], $resource_name, $name_length) === 0
                                && strlen($field['Field']) > $name_length
                                && ctype_upper($field['Field'][$name_length])
                            ) {
                                $field_label_key = $resource_name;
                                $field_label = substr($field['Field'], $name_length);
                                break;
                            }
                        }

                        $accounting_array[$field_label_key][$field['Field']] = array(
                            'key' => $field_label,
                            'column' => $field['Field'],
                            'hidden' => $hidden_field,
                            'params' => $params,
                            'checked' => $checked,
                        );
                        ksort($accounting_array[$field_label_key]);
                    }
                }

                // Render in $naming_array's order (sort_order, "Other" last),
                // not the arbitrary order columns happened to be returned in.
                $total = count($accounting_array);
                $divide = ceil($total / 2);

                echo '<div class="accounting_container">';
                echo '<div class="accounting_col">';
                $i = 0;
                foreach ($naming_array as $field_label_key => $naming_info) {
                    if (!isset($accounting_array[$field_label_key])) {
                        continue;
                    }
                    $group_items = $accounting_array[$field_label_key];
                    $label = $naming_info['name'];
                    $color = $naming_info['color'];
                    $totalaccess = count($group_items);
                    $totalchecked = array_sum(array_column($group_items, 'checked'));
                    ?>
                  <table class="tableBoxCenter collapse">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent column<?php echo $i; ?>" colspan="2" style="vertical-align:middle;"><?php echo $label; ?></td>
                      <td class="dataTableHeadingContent txta-c column<?php echo $i; ?>" style="width:60px;vertical-align:middle;"><?php echo $totalchecked . '/' . $totalaccess; ?></td>
                      <td class="dataTableHeadingContent" style="width:90px;vertical-align:middle;"><?php echo TEXT_ALLOWED . ' ' . xtc_draw_checkbox_field('checkall' . $i, '', ($totalchecked === $totalaccess), '', 'class="checkall' . $i . '" onclick="set_checkbox(' . $i . ', ' . $customers_id . ')"'); ?></td>
                    </tr>
                    <?php
                    foreach ($group_items as $details) {
                        ?>
                      <tr class="dataTableRow detail<?php echo $i; ?>" style="display:none;">
                        <td class="dataTableContent" style="width:18px; background:<?php echo $color; ?>;"></td>
                        <td class="dataTableContent" colspan="2"><?php echo $details['key']; ?></td>
                        <td class="dataTableContent txta-c" style="width:90px;"><?php echo xtc_draw_checkbox_field('access[]', $details['column'], $details['checked'], '', $details['params'] . ' class="access' . $i . '"') . $details['hidden']; ?></td>
                      </tr>
                        <?php
                    }
                    ?>
                    <tr><td>&nbsp;</td></tr>
                  </table>
                    <?php
                    $i++;
                    if ($i % $divide == 0 || $i == $total) {
                        echo '</div>';
                        if ($i < $total) {
                            echo '<div class="accounting_col">';
                        }
                    }
                }
                echo '</div>';
                ?>
              </td>
            </tr>
          </table>
          <a class="button" href="<?php echo xtc_href_link(FILENAME_CUSTOMERS, xtc_get_all_get_params(array('action')));?>"><?php echo BUTTON_BACK; ?></a>
          <a class="button" id="collapseall" href="#"><?php echo BUTTON_DISPLAY_ALL; ?></a>
          <input type="submit" class="button flt-r" value="<?php echo BUTTON_SAVE; ?>" <?php echo $confirm_save_entry;?>>
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
  $('input[name="complete"]').on('click', function () {
    $('input[name*="checkall"]').prop('checked', this.checked);
    $('input[name*="access"]').prop('checked', this.checked);
  });
  $('#collapseall').on('click', function () {
    $("[class*=detail]").show();
  });
  $("[class*=column]").on('click', function () {
    var num = $(this).attr('class').replace(/^\D+/g, '');
    $('.detail'+num).toggle();
  });
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
