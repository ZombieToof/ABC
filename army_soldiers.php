<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/functions/functions.ribbons.php';
$soldier_to_edit = (isset($_REQUEST['soldier'])) ? (int)$_REQUEST['soldier'] : False;

if($soldier_to_edit) {
	$query = "SELECT 
		user_id 
		FROM abc_users 
		WHERE abc_user_id = $soldier_to_edit";
	if($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()) {
      $user_id = $row['user_id'];
			$soldier = new Abc_user($user_id);
      $query_username = "SELECT * FROM phpbb_users WHERE user_id = " . $user_id;
      $result_username = $mysqli->query($query_username);
      $row_username = $result_username->fetch_assoc();
      $username = $row_username['username'];
		  }
    }
  else {
    $soldier = False;
    }
  }

if ($abc_user->is_admin && isset($_REQUEST['army'])) {
  $army_to_manage = (int)$_REQUEST['army'];
} elseif ($soldier) {
  $army_to_manage = $soldier->army_ptr;
} else {
  $army_to_manage = $abc_user->army_ptr;
}
  
//Deal with submitted form
$form_head = "";
$form_msg = "";
if(isset($_POST['am-cb-submit'])) {
	$array = array(
		'am-cb-action'		=> array(
			'filter'			=> FILTER_VALIDATE_INT,
			'options'			=> array(
				'min_range'			=> 1,
				'max_range'			=> 4
								)
							),
		'am-cb-decision'	=> FILTER_VALIDATE_INT,
    'am-cb-reason'  => FILTER_REQUIRE_STRING
	);
	$input = filter_input_array(INPUT_POST, $array);
	if($input['am-cb-action'] === FALSE || $input['am-cb-action'] === NULL) {
		$form_head = 'Error: No Action';
		$form_msg = 'No action was selected. Please <a href="javascript:history.go(-1);">go back</a> and select a valid action.';
	} elseif(!$soldier) {
		$form_head = 'Error: No Soldier';
		$form_msg = 'No soldier were selected. Please <a href="javascript:history.go(-1);">go back</a> and select at least one soldier.';
	} else {
		switch($input['am-cb-action']) {
			default:
				$form_head = 'Error: Unrecognised Action';
				$form_msg = 'An unregonised action was selected. Please <a href="javascript:history.go(-1);">go back</a> and select a valid action. If this message repeats please inform an admin.';
				break;
			
			case 1:
				//Change division
				if($input['am-cb-decision']) {
					/* First we need to determine if the new division is the HC division or not. */
					$query = "SELECT division_is_hc FROM abc_divisions WHERE division_id = " . (int)$input['am-cb-decision'];
					$result = $mysqli->query($query);
					$is_hc = 0;
					while($row = $result->fetch_row())
						$is_hc = $row[0];
					/* Next we only need to grab users if their HC division status has changed (joined or left). */
					$query = "SELECT user_id FROM abc_users LEFT JOIN abc_divisions USING (division_id) WHERE division_is_hc = " . ($is_hc ? 0 : 1) . " AND abc_user_id = " . $soldier_to_edit;
          $result = $mysqli->query($query);
					$users = array();
					while($row = $result->fetch_row())
						$users[] = $row[0];
					if(count($users)) {
						/* How the groups array is formatted depends on if we are adding or removing. */
						if($is_hc) {
							$groups = array($armies[$army_to_manage]['army']->hc_forum_group => 0);
							$phpbb_interaction->group_add_users($users, $groups, $armies[$army_to_manage]['army']->colour);
						} else {
							$groups = array($armies[$army_to_manage]['army']->hc_forum_group);
							$phpbb_interaction->group_remove_users($users, $groups);
						}
					}
					
					$query = "UPDATE abc_users SET division_id = " . (int)$input['am-cb-decision'] . ", user_time_stamp = " . time() . " WHERE abc_user_id = " . $soldier_to_edit;
					if($mysqli->query($query)) {
						$form_head = 'Success: Soldier Moved';
						$form_msg = 'Soldier has been moved successfuly. If this page does not automatically return to the previous page please <a href="army_soldiers.php?soldier=' . $soldier_to_edit . '">click here</a>.
						<meta http-equiv="refresh" content="3">';
					} else {
						$form_head = 'Error: Could Not Save';
						$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
					}
				} else {
					$form_head = 'Error: No Division';
					$form_msg = 'Could not determine which division to move soldier to. Please <a href="javascript:history.go(-1);">go back</a> and select a valid division.';
				}
				break;
			
			case 2:
				//Change rank
				if($input['am-cb-decision']) {
					/* Firstly we determine if the new rank is an officer rank or not. */
					$query = "SELECT rank_phpbb_id, rank_is_officer FROM abc_ranks WHERE rank_id = " . (int)$input['am-cb-decision'];
					$result = $mysqli->query($query);
					$phpbb_id = 0;
					$is_officer = 0;
					while($row = $result->fetch_row()) {
						$phpbb_id = $row[0];
						$is_officer = $row[1];
					}
					/* Next we only need to grab users if their officer status has changed (promoted or demoted). */
					$query = "SELECT user_id FROM abc_users LEFT JOIN abc_ranks USING (rank_id) WHERE rank_is_officer = " . ($is_officer ? 0 : 1) . " AND abc_user_id = " . $soldier_to_edit;
					$result = $mysqli->query($query);
					$users = array();
					while($row = $result->fetch_row())
						$users[] = $row[0];
					if(count($users)) {
						/* How the groups array is formatted depends on if we are adding or removing. */
						if($is_officer) {
							$groups = array($armies[$army_to_manage]['army']->officer_forum_group => 0);
							$phpbb_interaction->group_add_users($users, $groups, $armies[$army_to_manage]['army']->colour);
						} else {
							$groups = array($armies[$army_to_manage]['army']->officer_forum_group);
							$phpbb_interaction->group_remove_users($users, $groups);
						}
					}				
					
					$query = "UPDATE abc_users SET rank_id = " . (int)$input['am-cb-decision'] . ", user_time_stamp = " . time() . " WHERE abc_user_id = " . $soldier_to_edit;
					if($mysqli->query($query)) {
						$query = "SELECT user_id FROM abc_users WHERE abc_user_id IN(" . $soldier_to_edit . ")";
						$result = $mysqli->query($query);						
						$query = "UPDATE phpbb_users SET user_rank = $phpbb_id WHERE user_id IN("; 
						$start = "";
						while($row = $result->fetch_row()) {
							$query .= $start . $row[0];
							$start = ",";
						}
						$query .= ")";
						if($start = ",")
							$mysqli->query($query);
						$form_head = 'Success: Soldier Promoted / Demoted';
						$form_msg = 'Soldier has been promoted / demoted successfuly. If this page does not automatically return to the previous page please <a href="army_soldiers.php?soldier=' . $soldier_to_edit . '">click here</a>.
						<meta http-equiv="refresh" content="3">';
					} else {
						$form_head = 'Error: Could Not Save';
						$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
						//Uncomment for debugging
						$form_msg .= "<br /><br />Query: $query<br /><br />Error: " . $mysqli->error;
					}
				} else {
					$form_head = 'Error: No Rank';
					$form_msg = 'Could not determine which rank to set the soldier to. Please <a href="javascript:history.go(-1);">go back</a> and select a valid rank.';
				}
				break;
			
			case 3:
				//Award medal
				if($input['am-cb-decision']) {
					$query = "INSERT INTO abc_medal_awards (campaign_id, user_id, medal_id, award_reason, award_time_stamp) VALUES ({$campaign->id}, {$soldier_to_edit}, {$input['am-cb-decision']}, '{$input['am-cb-reason']}', " . time() . ")";
					if($mysqli->query($query)) {
  						ribbons($soldier_to_edit);
						$form_head = 'Success: Medal Awarded';
						$form_msg = 'The medal has been awarded successfuly. If this page does not automatically return to the previous page please <a href="army_soldiers.php?soldier=' . $soldier_to_edit . '">click here</a>.
						<meta http-equiv="refresh" content="3">';
					} else {
						$form_head = 'Error: Could Not Save';
						$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
					}
				} else {
					$form_head = 'Error: No Medal';
					$form_msg = 'Could not determine which medal to award to the soldier. Please <a href="javascript:history.go(-1);">go back</a> and select a valid medal.';
				}
				break;
			
			case 4:
				//Remove from the army
				$users = array();
				$groups = array();
				$users[] = $soldier_to_edit;
				if($armies[$army_to_manage]['army']->hc_forum_group)
					$groups[] = $armies[$army_to_manage]['army']->hc_forum_group;
				if($armies[$army_to_manage]['army']->officer_forum_group)
					$groups[] = $armies[$army_to_manage]['army']->officer_forum_group;
				if($armies[$army_to_manage]['army']->soldiers_forum_group)
					$groups[] = $armies[$army_to_manage]['army']->soldiers_forum_group;
				if(count($users) && count($groups))
					$phpbb_interaction->group_remove_users($users, $groups);
				$query = "UPDATE abc_users SET army_id = 0, division_id = 0, rank_id = 0, user_time_stamp = " . time() . " WHERE abc_user_id IN(" . $soldier_to_edit . ")";
				if($mysqli->query($query)) {
					$form_head = 'Success: Soldiers Removed';
					$form_msg = 'Soldiers have been removed from the army successfuly. If this page does not automatically return to the previous page please <a href="army_soldiers.php?soldier=' . $soldier_to_edit . '">click here</a>.
					<meta http-equiv="refresh" content="3">';
				} else {
					$form_head = 'Error: Could Not Save';
					$form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
				}
				break;
		}
	}
}
if(isset($_POST['ams-medals-submit'])) {
	$array = array(
		'ams-medals-award'		=> array(
			'filter'			=> FILTER_VALIDATE_INT,
			'flags'				=> FILTER_REQUIRE_ARRAY
			),
		'ams-medals-reason'		=> array(
			'filter'			=> FILTER_REQUIRE_STRING,
			'flags'				=> FILTER_REQUIRE_ARRAY
			),
		'ams-medals-checked'		=> array(
			'filter'			=> FILTER_VALIDATE_INT,
			'flags'				=> FILTER_REQUIRE_ARRAY
			)
    );
	$input = filter_input_array(INPUT_POST, $array);
  if (!$input['ams-medals-checked']) {$input['ams-medals-checked'] = array();}
	$awards_query = "SELECT * FROM abc_medal_awards WHERE user_id = " . $soldier_to_edit;
  $awards_results = $mysqli->query($awards_query);
  $update_sig = False;
  while ($awards_assoc = $awards_results->fetch_assoc()) {
    $award_id = $awards_assoc['award_id'];
    $award_reason = $awards_assoc['award_reason'];
    if (in_array($award_id, $input['ams-medals-checked'])) {
      $update_sig = True;
      $query = "DELETE FROM abc_medal_awards WHERE award_id = " . $award_id;
      if (!$mysqli->query($query)) {
        $form_head = 'Error: Could Not Save';
        $form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
        break;
        }
      }
    else {
      if (!$award_reason_index = array_search($award_id, $input['ams-medals-award'])) {
        if ($award_id == $input['ams-medals-award'][0]) {
          $award_reason_index = 0;
          }
        else {
          $form_head = 'Error: Invalid Form';
          $form_msg = 'The form you are using is invalid. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
          break;
          }
        }
      if ($input['ams-medals-reason'][$award_reason_index] != $award_reason) {
        $query = "UPDATE abc_medal_awards SET award_reason = '" . $input['ams-medals-reason'][$award_reason_index] . "' WHERE award_id = " . $award_id;
        if (!$mysqli->query($query)) {
          $form_head = 'Error: Could Not Save';
          $form_msg = 'There was an error saving the changes to the database. Please <a href="javascript:history.go(-1);">go back</a> and try again. If this message repeats please inform an admin.';
          break;
          }
        }
      }
    }
  if ($update_sig) {
    ribbons($soldier_to_edit);
    }
  if ($awards_results->num_rows > 0 and $form_head2 == '') {
    $form_head = 'Award Changes Saved';
    $form_msg = 'The changes of the medal awards were saved successfully. If this page does not automatically return to the previous page please <a href="army_soldiers.php?soldier=' . $soldier_to_edit . '">click here</a>.
					<meta http-equiv="refresh" content="3">';
    } 
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ABC &bull; Soldiers</title>
    <link rel="stylesheet" type="text/css" href="css/abc_style.css" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" language="javascript">
		/* Controls the upcoming battles movements */
		var cur_battle = 1;
		var max_battle = <?php echo (count($bat_left_bar)); ?>;
		$(document).ready(function(e) {
			$('.battle-left-window').css('height', $('.battle-left-wrapper').height());
			$('.battle-left-wrapper').css('width', ((max_battle + 1) * 211));
			if(max_battle == 1)
				$('.battle-left-next').hide();
		});
		$(document).on('click', '.battle-left-prev', function(e) {
			$('.battle-left-wrapper').animate({ left: '+=210' }, 250);
			cur_battle--;
			if(cur_battle == 1)
				$('.battle-left-prev').hide();
			if(max_battle > cur_battle && !$('.battle-left-next').is(':visible'))
				$('.battle-left-next').show();
		});
		$(document).on('click', '.battle-left-next', function(e) {
			$('.battle-left-wrapper').animate({ left: '-=210' }, 250);
			cur_battle++;
			if(cur_battle == max_battle)
				$('.battle-left-next').hide();
			if(cur_battle > 0 && !$('.battle-left-prev').is(':visible'))
				$('.battle-left-prev').show();
		});
		$(document).ready(function(e) {
            <?php if($abc_user->is_admin) { ?>
			$('#army-picker').change(function(e) {
                $(window).attr("location", "army_soldiers.php?army=" + $(this).val());
            });
			<?php } ?>
			$('#am-cb-action').change(function() {
				var p = $(this).val();
                $.ajax({
					type:	'POST',
					url:	'library/ajax/ajax.control_box.php',
					data:	{ p: p, a: <?php echo $army_to_manage; ?> },
					success: function(data) {
						$('#am-cb-ajax').html(data);
					}
				});
      if (p == 3) {
        $('#am-cb-ajax-reason').slideDown(500);
      } else {
        $('#am-cb-ajax-reason').slideUp(500);
        }
            });
			$('#ams-search-form').submit(function() {
          $('#ams-ajax-results').slideUp(500);
				  var q = $('#ams-search').val();
          $.ajax({
					type:	'POST',
					url:	'library/ajax/ajax.soldier_search.php',
					data:	{ q: q, a: <?php echo $army_to_manage; ?> },
					success: function(data) {
						$('#ams-ajax-results').html(data);
					  }
				  });
          $('#ams-ajax-results').slideDown(500);
          return false;
            });
			$('.close-medal-window').click(function(e) {
                $('.medal-window').fadeOut(500);
            });
        });
    function toggleMedals(campaign_id) {
      $('#cp-history-medals-' + campaign_id).slideToggle(500);
      if ($('#cp-toggle-medals-' + campaign_id).val() == 'Show Medals') {
        $('#cp-toggle-medals-' + campaign_id).val('Hide Medals');
      } else {
        $('#cp-toggle-medals-' + campaign_id).val('Show Medals');
      }
    }
    function showMedal(medal_id) {
      $('.medal-window').fadeIn(500);
      $('.medal-window-inner').css("margin-top", (($('.medal-window').height() - $('.medal-window-inner').height()) / 2));
      $.ajax({
					type:	'POST',
					url:	'library/ajax/ajax.medal_window.php',
					data:	{ m: medal_id },
					success: function(data) {
						$('.medal-window-content').html(data);
            $('.medal-window-inner').css("margin-top", (($('.medal-window').height() - $('.medal-window-inner').height()) / 2));
					  }
          });
      }
	</script>
</head>

<body style="background: url('<?php echo $phpbb_root_path; ?>styles/DirtyBoard2/theme/images/bg_body.jpg') fixed center;">
    <div class="new-body">
        <div class="header">
            <div class="logo">
            </div>
            <div class="nav-bar">
                <ul>
                    <li><a href="../portal.php">Home</a></li>
                    <li><a href="../ucp.php">User Control Panel</a></li>
                    <li><a href="index.php">ABC Soldiers Home</a></li>
					<li><a href="battleday_signup.php">Battle Day Sign Up</a></li>
                    <?php if($abc_user->is_dc || $abc_user->is_hc || $abc_user->is_admin) { ?>
                    <li><a href="army_management.php">ABC Army Management</a></li>
                    <?php }
                    if($abc_user->is_admin) { ?>
                    <li><a href="admin_cp.php">ABC Admin CP</a></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="content">
            <div class="content-left">
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />ABC ARMY MENU</div>
                    <ul>
                        <li><a href="index.php">ABC Home</a></li>
                        <li><a href="army_management.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Army Management</a></li>
						<li><a href="army_battleday_signup.php">Army Battle Signup Review</a></li>
                        <li><a href="army_divisions.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Divisions</a></li>
                        <li><a href="army_medals.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Medals</a></li>
                        <li><a href="army_ranks.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Ranks</a></li>
                        <li><a href="army_soldiers.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Soldiers</a></li>
                        <?php if($campaign->state == 4) { ?>
                        <li><a href="army_draft.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Live Draft</a></li>
                        <?php } elseif($campaign->state > 1 && $campaign->state < 4 && $campaign->num_signed_up > 0) { ?>
                        <li><a href="army_draft.php<?php if($abc_user->is_admin) echo '?army=' . $army_to_manage; ?>">Draft List</a></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_user.png" align="left" />SOLDIER INFO</div>
                    <?php $abc_user->output_soldier_info(); ?>
                </div>
                <?php if(count($bat_left_bar)) { ?>
                <div class="content-left-box">
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />UPCOMING BATTLES</div>
                    <div class="battle-left-window">
                    	<div class="battle-left-wrapper">
							<?php $i = 0;
                            foreach($bat_left_bar as $b => $a) { ?>
                            <div class="battle-left-battle" id="battle<?php echo $i; ?>">
                                <div class="battle-left-heading"><?php echo $b; ?></div>
                                <table width="210" cellpadding="0" cellspacing="0">
                                    <tr><td>
                                    <table style="width: 100px; float: left;">
                                        <tr>
                                        <?php foreach($a[$armies[0]['army']->name] as $hours) { ?>
                                            <td><?php echo $hours; ?></td>
                                        <?php } ?>
                                        </tr><tr>
                                        <?php foreach($a[$armies[0]['army']->name] as $hours) { ?>
                                            <td height="<?php echo ($max_sign_ups * 3); ?>" valign="bottom">
                                            	<div style="width: 4px; height: <?php echo ($hours * 3); ?>px; background-color: #<?php echo $armies[0]['army']->colour; ?>; margin: <?php echo ($max_sign_ups * 3) > ($hours * 3) ? (($max_sign_ups * 3) - ($hours * 3)) : 0; ?>px auto 0 auto;"></div>
                                            </td>
                                        <?php } ?>
                                        </tr><tr>
                                            <th colspan="<?php echo count($a[$armies[0]['army']->name]); ?>"><?php echo $armies[0]['army']->name; ?></td>
                                        </tr>
                                    </table>
                                    <table style="width: 100px; float: right;">
                                        <tr>
                                        <?php foreach($a[$armies[1]['army']->name] as $hours) { ?>
                                            <td><?php echo $hours; ?></td>
                                        <?php } ?>
                                        </tr><tr>
                                        <?php foreach($a[$armies[1]['army']->name] as $hours) { ?>
                                            <td height="<?php echo ($max_sign_ups * 3); ?>" valign="bottom">
                                            	<div style="width: 4px; height: <?php echo ($hours * 3); ?>px; background-color: #<?php echo $armies[1]['army']->colour; ?>; margin: <?php echo ($max_sign_ups * 3) > ($hours * 3) ? (($max_sign_ups * 3) - ($hours * 3)) : 0; ?>px auto 0 auto;"></div>
                                            </td>
                                        <?php } ?>
                                        </tr><tr>
                                            <th colspan="<?php echo count($a[$armies[0]['army']->name]); ?>"><?php echo $armies[1]['army']->name; ?></td>
                                        </tr>
                                    </table>
                                    </td></tr>
                                </table>
                            </div>
                            <?php $i++;
                            } ?>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="battle-left-controls">
                    	<input type="button" class="battle-left-prev" value="Previous" />
                      <input type="button" class="battle-left-next" value="Next" />
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="content-middle">
              <div class="content-middle-box">
                <?php if($abc_user->is_hc || $abc_user->is_admin) {
                  if($form_msg != '') { 
                    //Display outcome of submitted form
                    echo '<div class="large-heading">' . $form_head . '</div>' . $form_msg;
                    } else { ?>
                    <div class="large-heading">
                        <?php echo ($soldier) ? $username : 'Army Soldiers' ?>
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
                    </div>
                    <?php if ($soldier) { ?>
                    <form name="am-update" id="am-update" method="POST">
                    <div class="am-control-box">
                    	<div class="small-heading">Control Box</div>
                        <label for="am-cb-action">Action:</label>
                        <select name="am-cb-action" id="am-cb-action" class="am-cb-select">
                        	<option value="0">Please Select...</option>
                            <option value="1"<?php if(!$abc_user->is_hc && !$abc_user->is_admin) echo ' disabled="disabled"'; ?>>Change Division</option>
                            <option value="2">Change Rank</option>
                            <option value="3">Award Medal</option>
                            <option value="4">Remove From Army</option>
                        </select>
                        <div class="left" id="am-cb-ajax"></div>
                        <input type="submit" name="am-cb-submit" value="Go" />
                        <div class="left" id="am-cb-ajax-reason">
                          <br />
                          <label for="am-cb-reason">Reason:</label>
                          <textarea name="am-cb-reason" id="am-cb-reason" class="am-cb-text"></textarea>
                        </div>
                        <div class="clear"></div>
                    </div>
                    </form>
                    <br />
                    <div class="ams-box">
                    	<div class="small-heading">Overview</div>
                        <?php
                        echo '<table>';
                        echo '<tr><th>Tags:</th><td>' . $soldier->tags . '</td><th>Rank:</th><td>' . $armies[$soldier->army_ptr]['ranks'][$soldier->rank_ptr]->name . '</td></tr>';
                        echo '<tr><th>Username:</th><td>' . $username . '</td><th>Division:</th><td>' . $armies[$soldier->army_ptr]['divisions'][$soldier->division_ptr]->name . '</td></tr>';
                        echo '</table>';
                        ?>
                        <div class="clear"></div>
                    </div>
                    <br />
                    <div class="ams-box">
                    	<div class="small-heading">Medals</div>
                        <?php
                        $awarded_medals = array();
                        $medals_query = 'SELECT * FROM abc_medal_awards WHERE user_id = ' . $soldier->id . ' ORDER BY award_time_stamp DESC';
                        $medals_result = $mysqli->query($medals_query);
                        if ($medals_result->num_rows > 0) {
                          echo '<form name="ams-medals" id="ams-medals" method="POST">';
                          echo '<table class="cp-medal-table">';
                          echo '<tr><th width="100">Medal</th><th width="420">Reason</th><th width="100">Date</th><th>Delete?</th></tr>';
                          while ($medals_assoc = $medals_result->fetch_assoc()) {
                            $medal_query = 'SELECT * FROM abc_medals WHERE medal_id = ' . $medals_assoc['medal_id'];
                            $medal_result = $mysqli->query($medal_query);
                            $medal_assoc = $medal_result->fetch_assoc();
                            
                            $medal_img = $medal_assoc['medal_img'];
                            $award_reason = $medals_assoc['award_reason'];
                            if ($award_reason == '') {$award_reason = 'No reason specified.';}
                            $award_date = date('Y-m-d', $medals_assoc['award_time_stamp']);
                          
                            echo '<input type="hidden" name="ams-medals-award[]" value="' . $medals_assoc['award_id'] . '" />';
                            echo '<tr>';
                            echo '<td style="text-align: center;"><img onclick="showMedal('.$medals_assoc['medal_id'].')" src="' . $medal_img . '" /></td>';
                            echo '<td><textarea name="ams-medals-reason[]" id="ams-medals-reason">' . $award_reason . '</textarea></td>';
                            echo '<td>' . $award_date . '</td>';
                            echo '<td><input type="checkbox" name="ams-medals-checked[]" id="ams-medals-check" value="' . $medals_assoc['award_id'] . '" /></td>';
                            echo '</tr>';
                            }
                          echo '</table>';
                          echo '<input type="submit" name="ams-medals-submit" id="ams-medals-submit" value="Save" />';
                          echo '<div class="clear"></div>';
                          echo '</form>';
                          $medals_result->free();
                          }
                        else {
                          echo 'No medals awarded yet.';
                          }
                        ?>
                        <div class="clear"></div>
                    </div>
                    <br />
                    <div class="ams-box">
                    	<div class="small-heading">Signature</div>
                        <?php
                        $ribbons_path = 'images/cache/sigs/' . $campaign->id . '/' . $soldier->id . '.gif';
                        $ribbons_path_full = 'http://' . $_SERVER["SERVER_NAME"] . '/abc/' . $ribbons_path;
                        
                        if (file_exists($ribbons_path)) { ?>
                          This signature was automatically generated based on this soldier's awarded medals:<br /><br />
                          <center><img src="<?php echo $ribbons_path; ?>" /></center><br />
                          He can use the following BBCode to use it in his signature:<br />
                          BBCode: <input type="text" name="ribbons-path" id="ribbons-path" value="[img]<?php echo $ribbons_path_full; ?>[/img]" readonly="readonly" />
                        <?php } else { ?>
                          Once a medal is awarded to this soldier, you will find his automatically generated signature here.
                        <?php } ?>
                        <div class="clear"></div>
                    </div>
                  </div>
                  <div class="content-middle-box">
                    <div class="large-heading">Campaign History</div>
                    <?php
                    $past_users_query = 'SELECT * FROM abc_users WHERE user_id = ' . $user_id . ' AND campaign_id != ' . $campaign->id . ' ORDER BY user_id DESC';
                    $past_users_result = $mysqli->query($past_users_query);
                    if ($past_users_result->num_rows > 0) {
                      $campaign_index = 1;
                      while ($past_users_assoc = $past_users_result->fetch_assoc()) { 
                        $past_user = $past_users_assoc['abc_user_id'];
                        
                        $past_campaign_query = 'SELECT * FROM abc_campaigns WHERE campaign_id = ' . $past_users_assoc['campaign_id'];
                        $past_campaign_result = $mysqli->query($past_campaign_query);
                        $past_campaign_assoc = $past_campaign_result->fetch_assoc();
                         
                        $past_army_query = 'SELECT * FROM abc_armies WHERE army_id = ' . $past_users_assoc['army_id'];
                        $past_army_result = $mysqli->query($past_army_query);
                        $past_army_assoc = $past_army_result->fetch_assoc();
                         
                        $past_rank_query = 'SELECT * FROM abc_ranks WHERE rank_id = ' . $past_users_assoc['rank_id'];
                        $past_rank_result = $mysqli->query($past_rank_query);
                        $past_rank_assoc = $past_rank_result->fetch_assoc();
                         
                        $past_division_query = 'SELECT * FROM abc_divisions WHERE division_id = ' . $past_users_assoc['division_id'];
                        $past_division_result = $mysqli->query($past_division_query);
                        $past_division_assoc = $past_division_result->fetch_assoc();
                        
                        echo '<div class="ams-box">';
                        echo '<div class="small-heading">' . $past_campaign_assoc['campaign_name'] . '</div>';
                        
                        echo '<div class="cp-history-left">';  
                        echo '<table>';
                        echo '<tr><th>Army:</th><td>' . $past_army_assoc['army_name'] . '</td></tr>';
                        echo '<tr><th>Rank:</th><td>' . $past_rank_assoc['rank_name'] . '</td></tr>';
                        echo '<tr><th>Division:</th><td>' . $past_division_assoc['division_name'] . '</td></tr>';
                        echo '</table>';
                        echo '</div>';
                        
                        $past_ribbons_path = 'images/cache/sigs/' . $past_campaign_assoc['campaign_id'] . '/' . $past_users_assoc['abc_user_id'] . '.gif';
                        $past_ribbons_path_full = 'http://' . $_SERVER["SERVER_NAME"] . '/abc/' . $past_ribbons_path;
                        if (file_exists($past_ribbons_path)) {
                          echo '<div class="cp-history-right">';
                          echo '<center><img src="' . $past_ribbons_path . '" /></center>';
                          echo '</div>';        
                          
                          echo '<div class="clear"></div>';
                          echo '<br />';
                          echo 'BBCode: <input type="text" name="ribbons-path" id="ribbons-path" value="[img]' . $past_ribbons_path_full . '[/img]" readonly="readonly" />';
                          }
                        else {
                          echo '<div class="cp-history-right">';
                          echo '<center>No medals awarded or file not found.</center>';
                          echo '</div>';
                              
                          echo '<div class="clear"></div>';
                          }
                        echo '<input type="button" name="cp-toggle-medals" value="Show Medals" id="cp-toggle-medals-'.$past_campaign_assoc['campaign_id'].'" class="cp-toggle-medals" onclick="toggleMedals('.$past_campaign_assoc['campaign_id'].')" />';                          
                        echo '<div class="clear"></div>';
                        echo '<div class="cp-history-medals" id="cp-history-medals-'.$past_campaign_assoc['campaign_id'].'" class="cp-history-medals">';
                        echo '<br />';
                        $past_awarded_medals = array();
                        $past_medals_query = 'SELECT * FROM abc_medal_awards WHERE user_id = ' . $past_user . ' ORDER BY award_time_stamp DESC';
                        $past_medals_result = $mysqli->query($past_medals_query);
                        if ($past_medals_result->num_rows > 0) {
                          echo '<table class="cp-medal-table">';
                          echo '<tr><th width="100">Medal</th><th width="420">Reason</th><th width="100">Date</th></tr>';
                          while ($past_medals_assoc = $past_medals_result->fetch_assoc()) {
                            $past_medal_query = 'SELECT * FROM abc_medals WHERE medal_id = ' . $past_medals_assoc['medal_id'];
                            $past_medal_result = $mysqli->query($past_medal_query);
                            $past_medal_assoc = $past_medal_result->fetch_assoc();
                            
                            $past_medal_img = $past_medal_assoc['medal_img'];
                            $past_award_reason = $past_medals_assoc['award_reason'];
                            if ($past_award_reason == '') {$past_award_reason = 'No reason specified.';}
                            $past_award_date = date('Y-m-d', $past_medals_assoc['award_time_stamp']);
                            
                            echo '<tr>';
                            echo '<td style="text-align: center;"><img onclick="showMedal('.$past_medals_assoc['medal_id'].')" src="' . $past_medal_img . '" /></td>';
                            echo '<td>' . $past_award_reason . '</td>';
                            echo '<td>' . $past_award_date . '</td>';
                            echo '</tr>';
                            }
                          echo '</table>';
                          $past_medals_result->free();
                          }
                        else {
                          echo 'No medals awarded.';
                          }
                        echo '</div>';
                        echo '</div>';
                        $campaign_index++;
                        }
                    } else {
                      echo 'There are no other recorded campaigns.';
                    }
                    echo '<div class="medal-window">';
                    echo '<div class="medal-window-inner">';
                    echo '<div class="large-heading">Award Details<div class="close-medal-window">x</div></div>';
                    echo '<div class="medal-window-content"></div>';
                    echo '</div>';
                    echo '</div>';
                    ?>
                    <?php } else { ?>
                    <form name="ams-search-form" id="ams-search-form" method="POST">
                    <div class="ams-search-box">
                    	<div class="small-heading">Search</div>
                        <label for="ams-search">Search Soldier:</label>
                        <input type="text" name="ams-search" id="ams-search" class="ams-search" />
                        <input type="submit" name="ams-search-submit" id="ams-search-submit" class="ams-search-submit" value="Go" />
                    </div>
                    </form>
                    <div id="ams-ajax-results" class="ams-box"></div>
                    <?php } ?>
                <?php }
                 } else { ?>
                    <div class="large-heading">Unauthorised access!</div>
                    You do not have permission to view this page.
                <?php } ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="footer">
        </div>
    </div>
</body>
</html>
<?php $mysqli->close(); ?>