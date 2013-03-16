<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Suppresion du TYPE (suppression de toute les donnes reliees)
	#
	if(sizeof($_POST['killme']) > 0){
		include(CONFIG . '/config.php');

		foreach($_POST['killme'] as $e){

			$id = array();
			$fo = false;
			$ts = $app->dbMulti("SHOW TABLES");

			foreach($ts as $t){
				if($t['Tables_in_'.$database] == 'k_content'.$t) $fo = true;
			}

			if($fo) $ids = $app->dbMulti("SELECT id_content FROM k_contentdata".$e);

			if(sizeof($ids) > 0){
				foreach($ids as $id){
					$ids_[] = $id['id_content'];
				}

				$ids_ = implode(',', $ids_);

				$app->dbQuery("DELETE FROM k_content			WHERE id_type=".$e);

				# Supprimer les valeurs des content
				$app->dbQuery("DELETE FROM k_content			WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentdata 		WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentcomment 	WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_content 			WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentgroup		WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentversion		WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentitem		WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentalbum		WHERE id_content IN(".$ids_.")");

				# Supprimer les associations
				$app->dbQuery("DELETE FROM k_contentsearch		WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentgroup		WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentchapter		WHERE id_content IN(".$ids_.")");
				$app->dbQuery("DELETE FROM k_contentcategory	WHERE id_content IN(".$ids_.")");
			}

			# Supprimer les types
			$app->dbQuery("DELETE FROM k_type			WHERE id_type=".$e);
			$app->dbQuery("DELETE FROM k_fieldaffect	WHERE id=".$e);

			# Supprimer les tables
			$app->dbQuery("DROP TABLE IF EXISTS k_content".$e);
			$app->dbQuery("DROP TABLE IF EXISTS k_contentitem".$e);
			$app->dbQuery("DROP TABLE IF EXISTS k_contentalbum".$e);
		}

		# Suppression dans le profile
		$profile		= $app->dbOne("SELECT profileRule FROM k_userprofile WHERE id_profile=".$app->user['id_profile']);
		$profileRule	= unserialize($profile['profileRule']);

	#	$app->pre('killme', $_POST['killme'], 'prof', $profileRule['id_type']);

		foreach($profileRule['id_type'] as $idx => $e){
			if(in_array($e, $_POST['killme'])) unset($profileRule['id_type'][$idx]);
		}

		$profileRule['id_type'] = array_values($profileRule['id_type']);
	#	$app->pre($profileRule['id_type']);

		$app->apiLoad('user')->userProfileSet($app->user['id_profile'], array(
			'k_userprofile' 	=> array(
				'profileRule'	=> array('value' => serialize($profileRule))
			)
		));

		$app->apiLoad('field')->fieldCacheBuild();

		header("Location: index");

	}else
	
	# Gerer le TYPE
	#
	if($_POST['action']){

		$def['k_type'] = array(
			'is_business'		=> array('value' => $_POST['is_business'],			'zero'	=> true),
			'is_gallery'		=> array('value' => $_POST['is_gallery'],			'zero'	=> true),
			'is_ad'				=> array('value' => $_POST['is_ad'],				'zero' 	=> true),
			'is_cp'				=> array('value' => $_POST['is_cp'],				'zero' 	=> true),
			'use_group'			=> array('value' => $_POST['use_group'],			'zero' 	=> true),
			'use_search'		=> array('value' => $_POST['use_search'],			'zero' 	=> true),
			'use_chapter'		=> array('value' => $_POST['use_chapter'],			'zero' 	=> true),
			'use_category'		=> array('value' => $_POST['use_category'],			'zero' 	=> true),
			'use_socialforum'	=> array('value' => $_POST['use_socialforum'],		'zero' 	=> true),

			'typeName'			=> array('value' => $_POST['typeName'], 			'check'	=> '.'),
			'typeKey' 			=> array('value' => $_POST['typeKey'], 				'check'	=> '.'),
			'typeTemplate'		=> array('value' => $_POST['typeTemplate'])
		);

		if($app->formValidation($def)){
			$result  = $app->apiLoad('type')->typeSet($_POST['id_type'], $def);
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:'.$app->db_error;

			# Cache
			if($result) $app->apiLoad('field')->fieldCacheBuild();

			// Ajouter au profile le TYPE que l'on vient de creer
			if($result && $_POST['id_type'] == NULL){
				$profile		= $app->dbOne("SELECT profileRule FROM k_userprofile WHERE id_profile=".$app->user['id_profile']);
				$profileRule	= unserialize($profile['profileRule']);

				$profileRule['id_type'][] = $app->apiLoad('content')->id_type;
				$profileRule['id_type']   = array_unique($profileRule['id_type']);

				$app->apiLoad('user')->userProfileSet($app->user['id_profile'], array(
					'k_userprofile' 	=> array(
						'profileRule'	=> array('value' => serialize($profileRule))
					)
				));

				header("Location: ./?id_type=".$app->apiLoad('content')->id_type);
			}
		}else{
			$message = 'KO: Validation failed';
		}
	}

	if($_REQUEST['id_type'] != NULL){
		$data	= $app->apiLoad('type')->typeGet(array(
			'id_type'			=> $_REQUEST['id_type']
		));
	}else{
		$data	= array(
			'is_cp'				=> true,
			'use_group'			=> true,
			'use_search'		=> false,
			'use_chapter'		=> false,
			'use_category'		=> true,
			'use_socialforum'	=> false
		);
	}
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC . '/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/type.css" />
</head>
<body>

<header><?php
	include(COREINC . '/top.php');
	include(dirname(__DIR__) . '/content/ui/menu.php')
?></header>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<?php if(sizeof($_POST['remove']) > 0 ){ ?>
	<div class="message messageWarning">
		<p><?php echo _('<b>WARNING</b> you are about to remove data, this action is not cancelable (Database table destruction)') ?></p>
		
		<form action="./" method="post">
			<?php foreach($_POST['remove'] as $e){ ?>
			<input type="text" name="killme[]" value="<?php echo $e ?>" />
			<?php } ?>
			<input type="submit" value="valider" />
		</form>
	</div>
	<?php }else if(isset($_GET['noData'])){ ?>
	<div class="message messageWarning">
		<?php echo _('You can not add, see, browse data while there is no type defined'); ?>
	</div>
	<?php } ?>

	<form action="./" method="post" id="listing" class="span6">

		<table border="0" cellspacing="0" cellpadding="0" class="listing">
			<thead>
				<tr>
					<th width="20" class="icone"><i class="icon-remove icon-white"></i></th>
					<th><?php echo _('Name'); ?></th>
				</tr>
			</thead>
		</table>
			
		<ul id="items"><?php
	
			$types = $app->apiLoad('type')->typeGet(array(
				'profile'	=> true,
				'debug'		=> false
			));
			
			if(sizeof($types) > 0){
				foreach($types as $e){

					$color = ($_REQUEST['id_type'] == $e['id_type']) ? ' selected' : ''; 
					$lien  = ($e['is_gallery']) ? 'item=1' : 'sort=1';

					echo '<li id="'.$e['id_type'].'" class="clearfix '.$color.'"><div class="holder">';
						echo '<div class="check"><input type="checkbox" name="killme[]" value="'.$e['id_type'].'" /></div>';
						echo '<div class="handle"><i class="icon-move"></i></div>';

						echo '<div class="data">';
						echo '<a href="type?id_type='.$e['id_type'].'">'.$e['typeName'].'</a>';
						echo '</div>';

						echo '<div class="content">';
						echo '<a href="../content/?id_type='.$e['id_type'].'">'._('View').'</a> &nbsp; &nbsp; ';
                        echo '<a href="../field/asso?id_type='.$e['id_type'].'">'._('Manage fields').'</a> &nbsp; &nbsp; ';
                        echo '<a href="../type/row?id_type='.$e['id_type'].'">'._('Columns').'</a>';
						echo '</div>';

					echo '</div></li>';
				}
			
			}else{
				echo '<div class="noType">';
				echo _('You need to create a type to create new contents');
				echo '</div>';
			}
		?></ul>
	
		<div class="clearfix">
			<div class="left">
				<?php if(sizeof($types) > 0){ ?>
				<a onclick="apply();" class="btn btn-mini"><?php echo _('Remove selection'); ?></a>
				<a href="type" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
				<?php } ?>
			</div>
			<div class="right">
                <a href="../field/" class="nomargin btn btn-mini"><?php echo _('Manage fields'); ?></a>
			</div>
		</div>
	</form>

	<form action="./" method="post" id="data" class="span6">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_type" value="<?php echo $data['id_type'] ?>" />
		
		<?php if($data['id_type'] == NULL){ ?>
			<div class="alert message messageWarning"><?php echo _('Once a type crated, you will not be able to change business, ad, or gallery settings'); ?></div>
		<?php } ?>
	
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<?php if($data['id_type'] == NULL){ ?>
			<tr valign="top">
				<td><?php echo _('Business'); ?></td>
				<td><input type="checkbox" name="is_business" value="1" <?php if($app->formValue($data['is_business'], $_POST['is_business'])) echo " checked" ?>/></td>
			</tr>
			<tr valign="top">
				<td><?php echo _('Gallery'); ?></td>
				<td><input type="checkbox" name="is_gallery" value="1" <?php if($app->formValue($data['is_galery'], $_POST['is_gallery'])) echo " checked" ?>/></td>
			</tr>
			<tr valign="top">
				<td><?php echo _('Ad'); ?></td>
				<td><input type="checkbox" name="is_ad" value="1" <?php if($app->formValue($data['is_ad'], $_POST['is_ad'])) echo " checked" ?> /></td>
			</tr>
			<?php }else{ ?>
			<tr valign="top">
				<td width="100"><?php echo _('Business'); ?></td>
				<td><?php echo ($data['is_business']) ? "Oui" : "Non" ?>	<input type="hidden" name="is_business" value="<?php echo $data['is_business'] ?>" /></td>
			</tr>
			<tr valign="top">
                <td><?php echo _('Gallery'); ?></td>
				<td><?php echo ($data['is_gallery']) ? "Oui" : "Non" ?>		<input type="hidden" name="is_gallery" value="<?php echo $data['is_gallery'] ?>" /></td>
			</tr>
			<tr valign="top">
                <td><?php echo _('Ad'); ?></td>
				<td><?php echo ($data['is_ad']) 	 ? "Oui" : "Non" ?>		<input type="hidden" name="is_ad" value="<?php echo $data['is_ad'] ?>" /></td>
			</tr>
			<?php } ?>
			<tr valign="top">
				<td width="150"><?php echo _('Back office view'); ?></td>
				<td><input type="checkbox" name="is_cp" value="1" <?php if($app->formValue($data['is_cp'], $_POST['is_cp'])) echo " checked" ?> /></td>
			</tr>
			<tr valign="top">
				<td><?php echo _('Relationships'); ?></td>
				<td>
					<input type="checkbox" name="use_group"			value="1" <?php if($app->formValue($data['use_group'], 			$_POST['use_group'])) 			echo "checked" ?> /> Les groupes<br />
					<input type="checkbox" name="use_search"		value="1" <?php if($app->formValue($data['use_search'],			$_POST['use_search'])) 			echo "checked" ?> /> Les groupes intelligents<br />
					<input type="checkbox" name="use_chapter"		value="1" <?php if($app->formValue($data['use_chapter'],		$_POST['use_chapter'])) 		echo "checked" ?> /> L'arborescence<br />
					<input type="checkbox" name="use_category"		value="1" <?php if($app->formValue($data['use_category'],		$_POST['use_category'])) 		echo "checked" ?> /> Les catégories<br />
					<input type="checkbox" name="use_socialforum"	value="1" <?php if($app->formValue($data['use_socialforum'],	$_POST['use_socialforum'])) 	echo "checked" ?> /> Les Forums sociaux
				</td>
			</tr>
			<tr>
				<td><?php echo _('Name'); ?></td>
				<td><input type="text" name="typeName" value="<?php echo $app->formValue($data['typeName'], $_POST['typeName']); ?>" /></td>
			</tr>
			<tr>
				<td><?php echo _('Key'); ?></td>
				<td><input type="text" name="typeKey" value="<?php echo $app->formValue($data['typeKey'], $_POST['typeKey']); ?>" /></td>
			</tr>
			<tr>
				<td><?php echo _('Template'); ?></td>
				<td><?php
					echo $app->apiLoad('template')->templateSelector(array(
						'name'	=> 'typeTemplate',
						'value'	=> $app->formValue($data['typeTemplate'], $_POST['typeTemplate'])
					));
				?></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a onclick="$('#data').submit()" class="btn btn-mini"><?php echo _('Save'); ?></a>
					<a href="./" class="btn btn-mini"><?php echo _('New'); ?></a>
				</td>
			</tr>
		</table>
	</form>

</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="../content/ui/js/type.js" type="text/javascript"></script>
<script>

    function apply(){
        if(confirm("<?php echo addslashes(_('SUPPRIMER ?')) ?>")) $('#listing').submit();
    }
</script>

</body></html>