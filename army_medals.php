<?php 
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once 'library/abc_start_up.php';
require_once 'library/functions/functions.output_options.php';
$army_to_manage = ($abc_user->is_admin && isset($_REQUEST['army'])) ? (int)$_REQUEST['army'] : $abc_user->army_ptr;
$medal_to_edit = (isset($_REQUEST['medal'])) ? (int)$_REQUEST['medal'] : 0;
$msg_head = '';
$msg_body = '';

if($medal_to_edit) {
	$query = "SELECT 
		medal_id, 
		army_id, 
		medal_name, 
		medal_img, 
		medal_ribbon, 
		medal_time_stamp 
		FROM abc_medals 
		WHERE medal_id = $medal_to_edit";
	if($result = $mysqli->query($query)) {
		while($row = $result->fetch_assoc()) {
			$medal = new Medal($row, -1);
		}
	}
}

if(isset($_POST['action'])) {
	switch($_POST['action']) {
		case 'del':
			if($medal->delete()) {
				$msg_head = 'Error!';
				$msg_body = 'There was a problem deleting the medal. Please check the database connection.';
			} else {
				$msg_head = 'Medal Deleted';
				$msg_body = 'The medal has been successfully deleted.';
				unset($medal);
			}
			break;

    case 'edit':
			$medal->name = $_POST['medal_name'];
      $filen = 0;
			foreach($_FILES as $file) {
				$dir = 'images/cache/medals/' . $campaign->id . '/' . $armies[$army_to_manage]['army']->id . '/'; //REMOVE . "phpbb/" ON LIVE SERVER
				if(!is_dir($dir))
					mkdir($dir,0777,true);
				if($file['error']) {
					$msg_head = 'Error!';
					switch($file['error']) {
						default:
							$msg_body = 'There was a problem uploading your file. Please try again.';
							break;
						case 1:
						case 2:
							//1 - file size exceeds limit in php.ini, 2 - file size exceeds limit set by form.
							$msg_body = 'The file you uploaded was too big. Please try a smaller image.';
							break;
						case 3:
							//Partial upload
							$msg_body = 'The file did not completely upload. Please try again.';
						case 4:
							//No file uploaded
							break;
						case 6:
							//No temp folder
							$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 6.';
							break;
						case 7:
							//Failed to write to disk
							$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 7.';
							break;
					}
				} else {
					if($file['type'] == "image/gif" || $file['type'] == "image/jpeg" || $file['type'] == "image/pjpeg" || $file['type'] == "image/png") {
						if(file_exists($dir . $file['name']))
							unlink($dir . $file['name']);
						if(!move_uploaded_file($file['tmp_name'], $dir . $file['name'])) {
							$msg_head = 'Error!';
							$msg_body = 'Unable to upload file to the images/cache directory. Please check the folder permissions.';
						} elseif ($ribbon != 1 and $_FILES['medal_img']['name'] != "") {
							$medal->img = str_replace($_SERVER['DOCUMENT_ROOT'] . '/abc/', '', $dir) . $file['name'];
              $ribbon = 1;
            } else {
              $medal->ribbon = str_replace($_SERVER['DOCUMENT_ROOT'] . '/abc/', '', $dir) . $file['name'];
            }
					} else {
						$msg_head = 'Error!';
						$msg_body = 'You tried to upload an invalid file type. You may only upload a jpg, gif or png image file type.';
					}
				}
			}
			if(!$msg_body) {
				if($medal->save()) {
					$msg_head = 'Error!';
					$msg_body = 'There was a problem whilst trying to save your changes. Please check the database connection.';
				} else {
					$msg_head = 'Medal Updated';
					$msg_body = 'The medal has been successfully updated.';
				}
			}
			break;
    
		case 'new':
			$medal = new medal(array(), -1);
			$medal->army_id = $armies[$army_to_manage]['army']->id;
			$medal->name = $_POST['medal_name'];
      $filen = 0;
			foreach($_FILES as $file) {
				$dir = 'images/cache/medals/' . $campaign->id . '/' . $armies[$army_to_manage]['army']->id . '/'; //REMOVE . "phpbb/" ON LIVE SERVER
				if(!is_dir($dir))
					mkdir($dir);
				if($file['error']) {
					$msg_head = 'Error!';
					switch($file['error']) {
						default:
							$msg_body = 'There was a problem uploading your file. Please try again.';
							break;
						case 1:
						case 2:
							//1 - file size exceeds limit in php.ini, 2 - file size exceeds limit set by form.
							$msg_body = 'The file you uploaded was too big. Please try a smaller image.';
							break;
						case 3:
							//Partial upload
							$msg_body = 'The file did not completely upload. Please try again.';
						case 4:
							//No file uploaded
							break;
						case 6:
							//No temp folder
							$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 6.';
							break;
						case 7:
							//Failed to write to disk
							$msg_body = 'An error occured during the file upload process. Please contact an admin quoting file upload error 7.';
							break;
					}
				} else {
					if($file['type'] == "image/gif" || $file['type'] == "image/jpeg" || $file['type'] == "image/pjpeg" || $file['type'] == "image/png") {
						if(file_exists($dir . $file['name']))
							unlink($dir . $file['name']);
						if(!move_uploaded_file($file['tmp_name'], $dir . $file['name'])) {
							$msg_head = 'Error!';
							$msg_body = 'Unable to upload file to the images/cache directory. Please check the folder permissions.';
						} elseif ($ribbon != 1  and $_FILES['medal_img']['name'] != "") {
							$medal->img = str_replace($_SERVER['DOCUMENT_ROOT'] . '/abc/', '', $dir) . $file['name'];
              $ribbon = 1;
            } else {
              $medal->ribbon = str_replace($_SERVER['DOCUMENT_ROOT'] . '/abc/', '', $dir) . $file['name'];
            }
					} else {
						$msg_head = 'Error!';
						$msg_body = 'You tried to upload an invalid file type. You may only upload a jpg, gif or png image file type.';
					}
				}
			}
			if($medal->create()) {
				$msg_head = 'Error!';
				$msg_body = 'There was an error creating your new medal. Please check the database connection.';
				unset($medal);
			} else {
				$msg_head = 'Medal Created';
				$msg_body = 'The medal has been successfully created.';
			}
			break;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Army Base Camp &bull; Army Management</title>
    <link rel="stylesheet" type="text/css" href="css/abc_style.css" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.fullscreen.js"></script>
    <script type="text/javascript" language="javascript">
		/* Controls the upcoming battles movements */
		var cur_battle = 0;
		var max_battle = <?php echo (count($bat_left_bar) - 1); ?>;
		$(document).ready(function(e) {
			$('.battle-left-window').css('height', $('.battle-left-wrapper').height());
			$('.battle-left-wrapper').css('width', ((max_battle + 1) * 211));
			if(max_battle == 1)
				$('.battle-left-next').hide();
		});
		$(document).on('click', '.battle-left-prev', function(e) {
			$('.battle-left-wrapper').animate({ left: '+=210' }, 250);
			cur_battle--;
			if(cur_battle == 0)
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
                $(window).attr("location", "army_medals.php?army=" + $(this).val());
            });
			<?php } ?>
      <?php if($msg_body) { ?>
			setTimeout(function() {
				$('#msg-box').slideUp(500);
			}, 5000);
			<?php } ?>
        });
		
		function add() {
			<?php if(isset($medal)) { ?>
			$('.amm-edit').slideUp(500);
			<?php } ?>
			$('.amm-new').slideDown(500);
		}
		$('#medal_del_btn').live("click", function(e) {
			if(confirm("Once deleted you cannot undo this action. Are you sure you wish to continue?")) {
				$('#amm-action').val('del');
				$('#amm-medal').submit();
			}
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
                        Army Medals
                        <?php if($abc_user->is_admin) {
                            echo '<select name="army-picker" id="army-picker">' . PHP_EOL;
                            for($i = 0; $i < $campaign->num_armies; $i++) {
                                echo '<option value="' . $i . '"' . ($army_to_manage == $i ? ' selected="selected"' : '') . '>' . $armies[$i]['army']->name . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                        } ?>
                    </div>
                    <div class="am-control-box">
                    <form name="amm" method="POST">
                        <label for="rank">Medal: </label>
                        <select name="medal" class="am-cb-select">
                        <?php oo_medals($army_to_manage, $medal_to_edit, TRUE); ?>
                        </select>
                        <input type="submit" name="submit" value="Go" />
                        <input type="button" name="new" value="New" onclick="add();" />
                    </form>
                    </div>
                	<?php if(isset($medal)) { ?>
                    <br />
                    <div class="amm-edit">
                    <form name="amm-medal" id="amm-medal" method="POST" enctype="multipart/form-data">
                    	<input type="hidden" name="action" id="amm-action" value="edit" />
                        <input type="hidden" name="medal" value="<?php echo $medal->id; ?>" />
                    	<label for="medal_name">Name: </label>
                        <input type="text" name="medal_name" id="medal_name" value="<?php echo $medal->name; ?>" required="required" />
                      <br clear="all" /><br />
                        <img src="<?php echo $medal->img; ?>" />
                        <label for="medal_img">New Image: </label>
                        <input type="file" name="medal_img" id="medal_img" />
                      <br clear="all" /><br />
                        <img src="<?php echo $medal->ribbon; ?>" />
                        <label for="medal_ribbon">New Ribbon Image: </label>
                        <input type="file" name="medal_ribbon" id="medal_ribbon" />
                      <br clear="all" /><br />
                        <input type="button" name="delete" id="medal_del_btn" value="Delete" />
                        <input type="submit" name="submit-edit" value="Save" />
                    </form>
                    <div class="clear"></div>
                    </div>
                    <?php } ?>
                    <br />
                    <div class="amm-new">
                    <form name="amm-new" id="amm-new" method="POST" enctype="multipart/form-data">
                    	<input type="hidden" name="action" id="amn-action" value="new" />
                    	<label for="nmedal_name">Name: </label>
                        <input type="text" name="medal_name" id="nmedal_name" required="required" />
                      <br clear="all" /><br />
                        <label for="nmedal_img">New Image: </label>
                        <input type="file" name="medal_img" id="nmedal_img" />
                      <br clear="all" /><br />
                        <label for="medal_ribbon">New Ribbon Image: </label>
                        <input type="file" name="medal_ribbon" id="medal_ribbon" />
                      <br clear="all" /><br />
                        <input type="submit" name="submit-new" value="Create" />
                    </form>
                    </div>
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