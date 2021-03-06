<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
$army_to_manage = ($abc_user->is_admin && isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;

$draftsoldiers = array();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Army Base Camp &bull; Army Draft</title>
    <link rel="stylesheet" type="text/css" href="css/abc_style.css" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.fullscreen.js"></script>
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
		var FullscreenrOptions = {  width: 1920, height: 1080, bgID: '#bgimg' };
		jQuery.fn.fullscreenr(FullscreenrOptions);
		$(document).ready(function(e) {
            <?php if($abc_user->is_admin) { ?>
			$('#army-picker').change(function(e) {
                $(window).attr("location", "army_ranks.php?army=" + $(this).val());
            });
			<?php } 
			if($campaign->state > 1 && $campaign->state < 4) { ?>
			$('.draft-list-soldier').click(function(e) {
				$("#" + $(this).attr("id") + "-hidden").slideToggle(500);
            });
			<?php } ?>
        });
	</script>
</head>

<body>
	<!-- Background image, uses the same image as the forum -->
	<img src="<?php echo $phpbb_root_path; ?>styles/DirtyBoard2/theme/images/bg_body.jpg" id="bgimg" />
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
                    	<span class="battle-left-prev">Previous</span>
                        <span class="battle-left-next">Next</span>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class="content-middle">
                <div class="content-middle-box">
                <?php if($abc_user->is_hc || $abc_user->is_admin) { ?>
                    <div class="large-heading">
                        <?php echo $campaign->state == 4 ? 'Army Live Draft' : 'Army Draft List'; ?>
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
                    </div>
                    <?php if($campaign->state == 4) { ?>
                    <?php } elseif($campaign->state > 1 && $campaign->state < 4 && $campaign->num_signed_up > 0) {
						$query = "SELECT p.username, a.user_bf3_name, a.user_availability, a.user_location, a.user_vehicles, a.user_other_notes, a.Role FROM phpbb_users p LEFT JOIN abc_users a USING (user_id) WHERE a.campaign_id = " . $campaign->id . " AND a.user_is_signed_up = 1 ORDER BY a.Role, p.username_clean";
						$result = $mysqli->query($query);
					while($row = $result->fetch_assoc()) {
							$draftsoldiers[] = $row;
						}
						
					for($c = 0; $c < count($draftsoldiers); $c++){
					if ($draftsoldiers[$c]['Role'] == 'Air') {
					$sign_up_air[] = $draftsoldiers[$c];
					} else if ($draftsoldiers[$c]['Role'] == 'Armour') {
					$sign_up_armour[] = $draftsoldiers[$c];
					} else if($draftsoldiers[$c]['Role'] == 'Infantry') {
					$sign_up_infantry[] = $draftsoldiers[$c];
						}
					}
					
					if((isset($sign_up_air)) == true){
					echo ' <div class="small-heading"> Air Sign Ups </div> ';
					$a = 1;
					foreach ($sign_up_air as $airsoldier){
                        echo '<div class="asu-soldier"><div class="asu-info">
							<strong>' . $a++ . ". " . htmlentities($airsoldier['username']) . '</strong> [<a href="http://battlelog.battlefield.com/bf3/user/' . $airsoldier['user_bf3_name'] . '" title="BF3 Soldier" target="_blank">' . $airsoldier['user_bf3_name'] . '</a>]<br />
							<strong>Availability</strong>: ' . $airsoldier['user_availability'] . '<br />
							<strong>Location</strong>: ' . $airsoldier['user_location'] . '<br />
							<strong>Vehicles</strong>: ' . $airsoldier['user_vehicles'] . '<br />
							<strong>Other Notes</strong>: ' . $airsoldier['user_other_notes'] . '<br />
							</div>
                       <br clear="all" /><br />
						<div class="clear"></div></div>';
						}
					}
					if((isset($sign_up_armour)) == true){
					echo ' <div class="small-heading"> Armour Sign Ups </div> ';
					
					$ar = 1;
					foreach ($sign_up_armour as $armoursoldier){
                        echo '<div class="asu-soldier"><div class="asu-info">
							<strong>' . $ar++ . ". " . htmlentities($armoursoldier['username']) . '</strong> [<a href="http://battlelog.battlefield.com/bf3/user/' . $armoursoldier['user_bf3_name'] . '" title="BF3 Soldier" target="_blank">' . $armoursoldier['user_bf3_name'] . '</a>]<br />
							<strong>Availability</strong>: ' . $armoursoldier['user_availability'] . '<br />
							<strong>Location</strong>: ' . $armoursoldier['user_location'] . '<br />
							<strong>Vehicles</strong>: ' . $armoursoldier['user_vehicles'] . '<br />
							<strong>Other Notes</strong>: ' . $armoursoldier['user_other_notes'] . '<br />
							</div>
                        <br clear="all" /><br />
						<div class="clear"></div></div>';
						}
					}
					if((isset($sign_up_infantry)) == true){
					echo ' <div class="small-heading"> Infantry Sign Ups </div> ';
					
					$s = 1;
					foreach($sign_up_infantry as $infantrysoldier) {
								
                        echo '<div class="asu-soldier"><div class="asu-info">
							<strong>' . $s++ . ". " . htmlentities($infantrysoldier['username']) . '</strong> [<a href="http://battlelog.battlefield.com/bf3/user/' . $infantrysoldier['user_bf3_name'] . '" title="BF3 Soldier" target="_blank">' . $infantrysoldier['user_bf3_name'] . '</a>]<br />
							<strong>Availability</strong>: ' . $infantrysoldier['user_availability'] . '<br />
							<strong>Location</strong>: ' . $infantrysoldier['user_location'] . '<br />
							<strong>Vehicles</strong>: ' . $infantrysoldier['user_vehicles'] . '<br />
							<strong>Other Notes</strong>: ' . $infantrysoldier['user_other_notes'] . '<br />
							</div>
                        <br clear="all" /><br />
						<div class="clear"></div></div>';
						}
						 }
					
                   	} else { ?>
                    This page is currently inactive.
                    <?php } ?>
                <?php } else { ?>
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