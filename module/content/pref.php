<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_POST['action']){
		foreach($_POST['pref'] as $k => $p){
			$app->configSet('content', $k, $p);
		}
		$app->go('pref');
	}

	$pref = $app->configGet('content');

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div id="app"><div class="wrapper">
		
	<form action="pref" method="post">
		<input type="hidden" name="action" value="1" />

		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="20%"><?php echo _('Parameter'); ?></th>
					<th width="5%"><?php echo _('Value'); ?></th>
					<th width="75%">&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php echo _('Use cache'); ?></td>
					<td class="check-green">
						<input type="hidden"   name="pref[galleryItemRemove]" value="0" />
						<input type="checkbox" name="pref[galleryItemRemove]" value="1" <?php if($pref['galleryItemRemove']) echo 'checked' ?> id="usecache" />
					</td>
					<td><?php echo _('Remove a gallery item, remove the real file linked to it. Could be dangerous, if
					two items use the same file. Use with caution'); ?></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<button type="submit" class="btn btn-mini"><?php echo _('Save'); ?></button>
					</td>
				</tr>
			</tfoot>
		</table>

	</form>
		
</div></div>

<?php include(COREINC.'/end.php'); ?>

</body></html>