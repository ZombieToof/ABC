<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/classes/class.army_management.php';
/* $army_to_manage contains the pointer to the armies location in the array. 
 * As armies are loaded in the same way every page it is safe to use this on
 * other pages. */
$army_to_manage = (isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;

$army_id = $armies[$army_to_manage]['army']->id;

$medals = array();

$query = "SELECT 
	medal_id, 
	army_id, 
	medal_name,
  medal_description, 
	medal_img, 
	medal_ribbon, 
	medal_time_stamp 
	FROM abc_medals 
	WHERE army_id = $army_id";
if($result = $mysqli->query($query)) {
	while($row = $result->fetch_assoc()) {
		$medals[] = new Medal($row, -1);
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>ABC &bull; Available Awards</title>
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
			$('#army-picker').change(function(e) {
                $(window).attr("location", "medals.php?army=" + $(this).val());
            });
			$('.close-medal-window').click(function(e) {
                $('.medal-window').fadeOut(500);
            });
        });
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
                    <div class="large-heading">
                        Available Awards
                        <?php 
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                         ?>
                    </div>
                    <?php
                    if (count($medals) > 0) {
                      echo '<table class="cp-medal-table">';
                      echo '<tr><th width="100">Name</th><th width="100">Medal</th><th width="420">Description</th></tr>';
                      foreach ($medals as $medal) {
                        echo '<tr>';
                        echo '<td style="font-weight: bold;">'.$medal->name.'</td>';
                        echo '<td style="text-align: center;"><img onclick="showMedal('.$medal->id.')" src="'.$medal->img.'" style="max-width: 100;" /></td>';
                        echo '<td>'.(($medal->description != "") ? $medal->description : 'No description specified.').'</td>';
                        echo '</tr>';
                        }
                      echo '</table>';
                      }
                    else {
                      echo 'No awards available.';
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
</body>
</html>
<?php $mysqli->close(); ?>