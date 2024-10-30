<?php
/*
Plugin Name: Chesser AutoReplace
Plugin URI: http://chesser.ru/blog/wordpress-plugin-chesser-autoreplace-text/
Description: <a href="http://chesser.ru">Chesser AutoReplace</a> plugin finds and replaces substrings according to configured replacement rules
Author: Chesser
Version: 1.0
Author URI: http://chesser.ru/blog/
*/
/*  Copyright 2009  Chesser  (email: chesser@inbox.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function chesser_ar($content) {

  if(!get_option('chesser_autoreplace_active'))
    return $content;

  $replace = get_option('chesser_autoreplace_rules');

  function cmp($a, $b) {
    $a = strlen($a);
    $b = strlen($b);
    if ($a == $b) return 0;
    return ($a < $b) ? 1 : -1;
  }
  uksort($replace, 'cmp');

  $replace_after = array();
  $tmp = $replace;
  array_shift($tmp);
  foreach($replace as $k=>$v) {
    foreach($tmp as $kk=>$vv)
      if(strpos($v, $kk) !== false) {
        $md5_kk = md5($kk) . 'gofuck';
        $replace[$k] = str_replace($kk, $md5_kk, $replace[$k]);
        $replace_after[$md5_kk] = $kk;
      }
    array_shift($tmp);
  }

  $content = str_replace(array_keys($replace), array_values($replace), $content);
  $content = str_replace(array_keys($replace_after), array_values($replace_after), $content);

  return $content;
}

function chesser_ar_options_page() {
?>
<div class="wrap">
<h2 style="padding: 30px 0px 30px 0px;">Chesser AutoReplace options</h2>
<?php
  if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $chesser_ar_rules = $_POST['chesser_ar_rules'];

    if(!empty($chesser_ar_rules)) {
      $tmp = array();
      foreach($chesser_ar_rules as $k=>$rule) {
        if($rule['from'] != '') 
          $tmp[stripslashes($rule['from'])] = stripslashes($rule['to']);
      }
      $chesser_ar_rules = $tmp;
    }
    update_option('chesser_autoreplace_rules', $chesser_ar_rules);

    update_option('chesser_autoreplace_active', $_POST['chesser_ar_active']);
    update_option('chesser_autoreplace_use_mb', $_POST['chesser_ar_use_mb']);
    echo '<div class="updated"><p>The changes have been saved.</p></div>';
  }

  $chesser_ar_rules  = get_option('chesser_autoreplace_rules');
  $chesser_ar_active = get_option('chesser_autoreplace_active');

  $k =-1;
?>
<form method="post">
<br/>
<label>Active the plugin: <input type="checkbox" name="chesser_ar_active"<?php if($chesser_ar_active) echo ' checked="checked"' ?> /></label>
<table cellpadding="1" cellspacing="1" style="width:100%; margin: 15px 0px 20px 0px; padding:5px;">
<tr style="background-color: #DFDFDF;">
  <th> # </th>
  <th width="49%">From</th>
  <th width="49%">To</th>
</tr>
<?php if(!empty($chesser_ar_rules)): ?>
  <?php foreach($chesser_ar_rules as $from=>$to): ?>
<tr style="background-color: <?php echo ($k++%2==0)?'#DFDFDF':'#EFEFEF'; ?>;">
  <td align="right"><?php echo $k+1;?></td>
  <td><input type="text" style="width:90%" name="chesser_ar_rules[<?php echo $k;?>][from]" value="<?php echo htmlspecialchars($from);?>"/></td>
  <td><input type="text" style="width:90%" name="chesser_ar_rules[<?php echo $k;?>][to]"   value="<?php echo htmlspecialchars($to);  ?>"/></td>
</tr>
  <?php endForeach ?>
<?php else: ?>
<tr>
  <th colspan="3" align="center"><br/>No rules for AutoReplace.</th>
</tr>
<?php endIf ?>
<tr>
  <th colspan="3" style="padding-top:30px;" align="left">Add a new rule</th>
</tr>
<tr>
  <td></td>
  <td><input style="width:90%" name="chesser_ar_rules[new][from]" /></td>
  <td><input style="width:90%" name="chesser_ar_rules[new][to]" /></td>
</tr>
<tr>
  <td colspan="3" align="left" style="padding-top:50px;">
    <i> To remove the rules save the From column as empty in both columns</i>
  </td>
</tr>
</table>
<input type="submit" class="button-primary" value="Save Changes" />
</form>
</div>
<?php
}


function chesser_ar_add_menu() {
  add_options_page('Chesser AutoReplace', 'Chesser AR', 9, __FILE__, 'chesser_ar_options_page');
}

add_filter('the_content', 'chesser_ar', 100);
add_action('admin_menu' , 'chesser_ar_add_menu');
?>
