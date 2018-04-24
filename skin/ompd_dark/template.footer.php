<?php
//  +------------------------------------------------------------------------+
//  | template.footer.php                                                    |
//  +------------------------------------------------------------------------+
//  | Do NOT close the body and html tag!                                    |
//  | This is done by the footer.inc.php script to allow dynamic content.    |
//  +------------------------------------------------------------------------+
if (isset($footer) == false)
	exit();
?>
<!-- begin footer -->
	</td>
	<td class="side-margin"></td>	
</tr>
<tr>
	<td colspan="3" height="0"></td>
</tr>
</table>
</div><!-- end content -->
	
	</td>
</tr>
</table>

<?php 
if (!isset($cfg['items_count'])) $cfg['items_count'] = 0;
$curr_page = (get('page') ? get('page') : 1);
$whole_pages = floor($cfg['items_count'] / $cfg['max_items_per_page']);
$rest_items = ($cfg['items_count'] % $cfg['max_items_per_page']);
($rest_items == 0) ? $last_page = $whole_pages : $last_page = ($whole_pages + 1);

$url1 = $_SERVER["QUERY_STRING"];

$str = parse_str($url1, $output);

isset($output['action']) ? '' : $output['action'] = "viewNew";

$output['page'] = "1";
$url_first_page = $_SERVER["SCRIPT_NAME"] . "?" . http_build_query($output);

$output['page'] = $curr_page - 1;
$url_prev_page = $_SERVER["SCRIPT_NAME"] . "?" . http_build_query($output);

$output['page'] = $curr_page + 1;
$url_next_page = $_SERVER["SCRIPT_NAME"] . "?" . http_build_query($output);

$output['page'] = $last_page;
$url_last_page = $_SERVER["SCRIPT_NAME"] . "?" . http_build_query($output);


if ($last_page > 1) {
?>

<div class="paginator-nav">



<?php if ($curr_page == 1) { ?>
	<span><i class="fa fa-angle-double-left"></i></span>
<?php 
} 
else {
?>
	<a href="<?php echo $url_first_page ?>"><span><i class="fa fa-angle-double-left"></i></span></a>
<?php }?>




<?php if ($curr_page == 1) { ?>
	<span><i class="fa fa-angle-left"></i></span>
<?php 
} 
else {
?>
	<a href="<?php echo $url_prev_page ?>"><span><i class="fa fa-angle-left"></i></span></a>
<?php }?>


<span> Page <?php echo $curr_page . " of " . $last_page?> </span>


<?php if ($curr_page == $last_page) { ?>
	<span><i class="fa fa-angle-right"></i></span>
<?php 
} 
else {
?>
	<a href="<?php echo $url_next_page ?>"><span><i class="fa fa-angle-right"></i></span></a>
<?php }?>


<?php if ($curr_page == $last_page) { ?>
	<span><i class="fa fa-angle-double-right"></i></span>
<?php 
} 
else {
?>
	<a href="<?php echo $url_last_page ?>"><span><i class="fa fa-angle-double-right"></i></span></a>
<?php }?>



</div>

<?php 
}
$cfg['items_count'] = 0 ?>

</div> <!-- wrapper -->

<pre>
<?php
//var_dump($_SERVER);
?>
</pre>


<div class="bottom">
	<div class="screen_footer">	
		<?php echo $footer; ?>
	</div>
</div>



<div class="back-to-top"><i class="fa fa-2x fa-arrow-circle-o-up"></i></div>
