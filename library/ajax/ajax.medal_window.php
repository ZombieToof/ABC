<?php
/**
 * AJAX: Control Box
 *
 * Used by army_management.php. Updates the second label and select based on the action.
 * NOTE: The option value MUST be an integer.
 */

$m = filter_input(INPUT_POST, "m", FILTER_VALIDATE_INT);
if($m === FALSE || $m === NULL) exit;

$phpbb_root_path = '../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once '../abc_start_up.php';

if($m) {
	$query = "SELECT 
		medal_id, 
		army_id, 
		medal_name,
    medal_description,
		medal_img, 
		medal_ribbon, 
		medal_time_stamp 
		FROM abc_medals 
		WHERE medal_id = $m";
	if($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()) {
			$medal = new Medal($row, -1);
		}
	}
}

$recipients = array();
$recipients_query = "SELECT user_id FROM abc_medal_awards WHERE medal_id = $m ORDER BY user_id";
$recipients_result = $mysqli->query($recipients_query);
while ($recipients_assoc = $recipients_result->fetch_assoc()) {
  $user_id = $recipients_assoc['user_id'];
  $recipients[] = $user_id;
  }
$recipients_result->free();

$recipients = array_count_values($recipients);

echo '<div class="medal-window-left">';
echo '<br /><img src="'.$medal->img.'" /><br /><br />';
echo '<img src="'.$medal->ribbon.'" />';
echo '</div>';
echo '<div class="medal-window-right">';
echo '<div class="small-heading">'.$medal->name.'</div>';
echo '<b>Description:</b><br />';
echo ($medal->description != "") ? $medal->description : 'No description specified.';
echo '<br /><br />';
echo '<b>Recipients:</b><br />';
echo '<ul>';
foreach ($recipients as $recipient => $num_awarded) {
  $recip_query = "SELECT user_id FROM abc_users WHERE abc_user_id = $recipient";
  $recip_result = $mysqli->query($recip_query);
  if ($recip_row = $recip_result->fetch_assoc()) {
    $username_query = "SELECT username FROM phpbb_users WHERE user_id = ".$recip_row['user_id'];
    $username_result = $mysqli->query($username_query);
    if ($username_row = $username_result->fetch_assoc()) {
      echo '<li>'.$username_row['username'].' ('.$num_awarded.')</li>';
      }
    }
  }
echo '</ul>';
echo '</div>';
echo '<div class="clear"></div>';
?>