<?php
/**
 * AJAX: Soldier Search
 *
 * Used by army_soldiers.php. Outputs the results of the search.
 */

$q = strtolower(filter_input(INPUT_POST, "q", FILTER_SANITIZE_STRING));
$a = filter_input(INPUT_POST, "a", FILTER_VALIDATE_INT);
if($q === FALSE || $q === NULL || $a === FALSE || $a === NULL) exit;

$phpbb_root_path = '../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once '../abc_start_up.php';

$army_id = $armies[$a]['army']->id;

$search_query = "SELECT abc.abc_user_id, abc.user_id, abc.army_id, abc.division_id, abc.rank_id, phpbb.username FROM abc_users AS abc LEFT JOIN phpbb_users AS phpbb ON abc.user_id = phpbb.user_id WHERE phpbb.username_clean LIKE '%$q%' AND abc.army_id = $army_id GROUP BY abc.abc_user_id ORDER BY abc.division_id, abc.rank_id";
$search_results = $mysqli->query($search_query);

if ($search_results->num_rows > 0) {
  echo '<div class="small-heading">Search Results</div>';
  echo '<table>';
  echo '<tr><th></th><th>Username</th><th>Rank</th><th>Division</th></tr>';
  while ($search_result_assoc = $search_results->fetch_assoc()) {
    $result_user = new Abc_user($search_result_assoc['user_id']);
    echo '<tr>';
    echo '<td width="25"><img src="' . $armies[$a]['ranks'][$result_user->rank_ptr]->img . '" alt="Rank image" title="' . $armies[$a]['ranks'][$result_user->rank_ptr]->name . '" /></td>';
    echo '<td><a href="army_soldiers.php?soldier=' . $search_result_assoc['abc_user_id'] . '" title="Edit soldier">' . $armies[$a]['ranks'][$result_user->rank_ptr]->short . ' ' . $search_result_assoc['username'] . '</a></td>';
    echo '<td>' . $armies[$a]['ranks'][$result_user->rank_ptr]->name . '</td>';
    echo '<td>' . $armies[$a]['divisions'][$result_user->division_ptr]->name . '</td>';
    echo '</tr>';
    }
  echo '</table>';
  echo '</div>';
  }
else {
  echo 'No soldiers found.';
  }
?>