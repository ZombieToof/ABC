<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
include_once 'library/functions/functions.output_options.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ABC &bull; Control Panel</title>
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
		<?php if(!$abc_user->is_signed_up && $campaign->state <> 4) { ?>
		$(document).ready(function(e) {
			$('#soldier_sign_up_btn').click(function(e) {
                $('.sign-up-box').fadeIn(500);
            	$('.sign-up-box-inner').css("margin-top", (($('.sign-up-box').height() - $('.sign-up-box-inner').height()) / 2));
            });
			$('.close-sign-up').click(function(e) {
                $('.sign-up-box').fadeOut(500);
            });
			$('.close-medal-window').click(function(e) {
                $('.medal-window').fadeOut(500);
            });
        }); 
		<?php } ?>
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
                    <div class="small-heading"><img src="images/icon_menu.png" align="left" />ABC SOLDIER MENU</div>
                    <ul>
                        <li><a href="index.php">ABC Home</a></li>
						<li><a href="battleday_signup.php">Battle Day Sign Up</a></li>
                        <li><a href="control_panel.php">Control Panel</a></li>
						<li><a href="armies.php">Armies</a></li>
                        <li><a href="medals.php">Available Awards</a></li>
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
                    <div class="large-heading">Current Campaign</div>
                    <?php if ($campaign->is_running) { ?>
                    <div class="cp-box">
                    	<div class="small-heading">Overview</div>
                        <?php
                        if ($abc_user->army_id) {
                          echo '<table>';
                          echo '<tr><th>Username:</th><td>' . $user->data['username'] . '</td></tr>';
                          echo '<tr><th>Army:</th><td>' . $armies[$abc_user->army_ptr]['army']->name . '</td></tr>';
                          echo '<tr><th>Tags:</th><td>' . $abc_user->tags . '</td></tr>';
                          echo '<tr><th>Rank:</th><td>' . $armies[$abc_user->army_ptr]['ranks'][$abc_user->rank_ptr]->name . '</td></tr>';
                          echo '<tr><th>Division:</th><td>' . $armies[$abc_user->army_ptr]['divisions'][$abc_user->division_ptr]->name . '</td></tr>';
                          echo '</table>';
                          }
                        else {
                          echo 'You are currently not assigned to an army. Please sign up using the button to the left.';
                          }
                        ?>
                        <div class="clear"></div>
                    </div>
                    <br />
                    <div class="cp-box">
                    	<div class="small-heading">Medals</div>
                        <?php
                        $awarded_medals = array();
                        $medals_query = 'SELECT * FROM abc_medal_awards WHERE user_id = ' . $abc_user->id . ' ORDER BY award_time_stamp DESC';
                        $medals_result = $mysqli->query($medals_query);
                        if ($medals_result->num_rows > 0) {
                          echo '<table class="cp-medal-table">';
                          echo '<tr><th width="100">Medal</th><th width="420">Reason</th><th width="100">Date</th></tr>';
                          while ($medals_assoc = $medals_result->fetch_assoc()) {
                            $medal_query = 'SELECT * FROM abc_medals WHERE medal_id = ' . $medals_assoc['medal_id'];
                            $medal_result = $mysqli->query($medal_query);
                            $medal_assoc = $medal_result->fetch_assoc();
                            
                            $medal_img = $medal_assoc['medal_img'];
                            $award_reason = $medals_assoc['award_reason'];
                            if ($award_reason == '') {$award_reason = 'No reason specified.';}
                            $award_date = date('Y-m-d', $medals_assoc['award_time_stamp']);
                            
                            echo '<tr>';
                            echo '<td style="text-align: center;"><img onclick="showMedal('.$medals_assoc['medal_id'].')" src="' . $medal_img . '" /></td>';
                            echo '<td>' . $award_reason . '</td>';
                            echo '<td>' . $award_date . '</td>';
                            echo '</tr>';
                            }
                          echo '</table>';
                          $medals_result->free();
                          }
                        else {
                          echo 'No medals awarded yet.';
                          }
                        ?>
                        <div class="clear"></div>
                    </div>
                    <br />
                    <div class="cp-box">
                    	<div class="small-heading">Signature</div>
                        <?php
                        $ribbons_path = 'images/cache/sigs/' . $campaign->id . '/' . $abc_user->id . '.gif';
                        $ribbons_path_full = 'http://' . $_SERVER["SERVER_NAME"] . '/abc/' . $ribbons_path;
                        
                        if (file_exists($ribbons_path)) { ?>
                          This signature was automatically generated based on your awarded medals:<br /><br />
                          <center><img src="<?php echo $ribbons_path; ?>" /></center><br />
                          You can use the following BBCode to use it in your signature:<br />
                          BBCode: <input type="text" name="ribbons-path" id="ribbons-path" value="[img]<?php echo $ribbons_path_full; ?>[/img]" readonly="readonly" />
                        <?php } else { ?>
                          Once a medal is awarded to you, you will find an automatically generated signature here.
                        <?php } ?>
                        <div class="clear"></div>
                    </div>
                    <?php } else { ?>
                    <div class="cp-box">
                    	<div class="small-heading">No Campaign</div>
                        Currently there is no active campaign.
                        <div class="clear"></div>
                    </div>
                    <?php } ?>
                </div>
                <div class="content-middle-box">
                    <div class="large-heading">Past Campaigns</div>
                    <?php
                    $past_users_query = 'SELECT * FROM abc_users WHERE user_id = ' . $user->data['user_id'] . ' AND campaign_id != ' . $campaign->id . ' ORDER BY user_id DESC';
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
                        
                        echo '<div class="cp-box">';
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
                            
                            $past_medal_img_attr = getimagesize($past_medal_img);
                            $past_medal_img_width = $past_medal_img_attr[0];
                            $past_medal_img_height = $past_medal_img_attr[1];
                            if ($past_medal_img_width > 95) {
                              $past_medal_img_height *= 95 / $past_medal_img_width;
                              $past_medal_img_width = 95;
                              }
                            if ($past_medal_img_height > 50) {
                              $past_medal_img_width *= 50 / $past_medal_img_height;
                              $past_medal_img_height = 50;
                              }
                            
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
                        if ($campaign_index != $past_users_result->num_rows) {
                          echo '<br />';
                          }
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
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="footer">
        </div>
    </div>
    <?php if($campaign->is_running) { ?>
    <div class="sign-up-box">
    	<div class="sign-up-box-inner">
        <form name="sign-up" action="abc_soldier_sign_up.php" method="POST">
        	<div class="large-heading"><?php echo $campaign->state < 4 ? 'Draft Sign Up' : 'Campaign Sign Up'; ?><div class="close-sign-up">x</div></div>
            <label for="bf3-name">BF3 Soldier Name:</label>
            <input type="text" name="bf3_name" id="bf3-name" value="<?php echo $abc_user->bf3_name; ?>" required="required" maxlength="255" />
            <br clear="all" /><br />
            <label for="availability">Availability (e.g. All 6 hours):</label>
            <input type="text" name="availability" id="availability" value="<?php echo $abc_user->availability; ?>" maxlength="255" />
            <br clear="all" /><br />
            <label for="location">Location (e.g. U.K.):</label>
            <input type="text" name="location" id="location" value="<?php echo $abc_user->location; ?>" maxlength="255" />
            <br clear="all" /><br />
            <label for="vehicles">Ability With Vehicles:</label>
            <input type="text" name="vehicles" id="vehicles" value="<?php echo $abc_user->vehicles; ?>" maxlength="255" />
            <br clear="all" /><br />
            <label for="other_notes">Other notes:</label>
            <textarea name="other_notes" id="other_notes"><?php echo $abc_user->other_notes; ?></textarea>
            <br clear="all" /><br />
            <input type="submit" name="soldier_sign_up" value="<?php echo $campaign->state < 4 ? 'Join Draft' : 'Sign Up'; ?>" class="sign_up_submit" />
        </form>
        <div class="clear"></div>
        <?php if($campaign->army_join_pw) { ?>
        <br /><br />
        <form name="join-army" action="abc_soldier_join_army.php" method="POST">
        	<div class="large-heading">Join via Password</div>
        	If you have been given a specific password to join an army you can use that password here to join the army directly. If you haven't been given a password please use the form above.
            <br /><br />
            <select name="army"><?php oo_armies_with_passwords(); ?></select>
            <input type="text" name="army_password" id="army_password" maxlength="32" />
            <br clear="all" /><br />
            <input type="submit" name="soldier_sign_up" value="Join Army" class="sign_up_submit" />
        </form>
        <div class="clear"></div>
        <?php } ?>
        </div>
    </div>
	<?php } ?>
</body>
</html>
<?php $mysqli->close(); ?>