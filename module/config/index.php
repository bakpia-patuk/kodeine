<?php

	# Save
	#
	if($_POST['action']){

		$keys = array(
			'configMailTo', 'configMailCc', 'configMailBcc',
			'defaultIdTheme', 'defaultIdChapter', 'defaultLanguage',
			'dateFormat', 'timeFormat',
			'defautAnalytic'
		);
	
		foreach($keys as $k){
			$exi = $app->dbOne("SELECT 1 FROM k_config WHERE configModule='boot' AND configName='".$k."'");
			$q	 = ($exi[1])
				? "UPDATE k_config SET configValue='".addslashes($_POST[$k])."' WHERE configModule='boot' AND configName='".$k."'"
				: "INSERT INTO k_config (configModule, configName, configValue) VALUES ('boot', '".$k."', '".addslashes($_POST[$k])."')";
				
			$app->dbQuery($q);
		}

		if(sizeof($_POST['domain']) > 0){
			foreach($_POST['domain'] as $tag => $dom){
				if($tag == 'new' && trim($dom['domain']) != ''){
					$exi = $app->dbOne("SELECT 1 FROM k_config WHERE configModule='boot' AND configName='domain:".$dom['domain']."'");
					if(!$exi[1]) $app->dbQuery("INSERT INTO k_config (configModule, configName) VALUES ('boot', 'domain:".$dom['domain']."')");
				}else
				if(trim($dom['domain']) == ''){
					$app->dbQuery("DELETE FROM k_config WHERE configModule='boot' AND configName='domain:".$tag."'");
				}

				if(trim($dom['domain']) != ''){
					$app->dbQuery("UPDATE k_config SET configValue='".addslashes(json_encode($dom))."' WHERE configModule='boot' AND configName='domain:".trim($dom['domain'])."'");
				}
			}
		}
	
		if(sizeof($_POST['ext']) > 0){
			$i = 0; 
			foreach($_POST['ext'] as $ext_id => $ext_value){
				$val = $app->apiLoad('field')->fieldSaveValue($ext_id, $ext_value);

				/*if(is_array($ext_value)){
					unset($ext_value[sizeof($ext_value)-1]);
					$val = implode('@@', $ext_value);
				}else{
					$val = $ext_value;
				}*/

				$app->dbQuery("UPDATE k_config SET configValue='".addslashes($val)."' WHERE configModule='bootExt' AND configName='".$i.":id_field:".$ext_id."'");
			#	$app->pre($app->db_query, $app->db_error);
				$i++;
			}
		}

		$app->go("./?saved");
	}

	# Data
	#
	$l		= $app->loc('config');
	$db 	= $app->dbMulti("SELECT * FROM k_config WHERE configModule='boot'");
	$ext 	= $app->dbMulti("SELECT * FROM k_config WHERE configModule='bootExt'");
	$dom	= array();
	foreach($db as $e){
		if(preg_match("#^domain:#", $e['configName'])){
			$dom[] = json_decode(stripslashes($e['configValue']), true);
		}else{
			$data[$e['configName']] = $e['configValue'];
		}
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<title>Kodeine</title>
	<?php
		echo $app->less('/admin/content/ui/css/data.less');
		include(COREINC.'/head.php');
	?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li>
		<a href="./" class="btn btn-small"><?php echo $l['CANCEL'] ?></a>
	</li>
	<li>
		<a onclick="$('#data').submit()" class="btn btn-small btn-success"><?php echo $l['VALIDATE'] ?></a>
	</li>
</div>

<div id="app"><div class="wrapper">

	<?php
		if(isset($_GET['saved'])) echo '<div class="message messageValid">'.$l['UPDATED'].'</div>';
	 ?>

	<form action="./" method="post" id="data">
	
		<input type="hidden" name="action" value="1" />
	
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="25%"><?php echo $l['PARAMETERS'] ?></th>
					<th width="25%"><?php echo $l['VALUE'] ?></th>
					<th width="50%"><?php echo $l['EXPLANATION'] ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td width="100">Destinataire</td>
					<td><input type="text" name="configMailTo" value="<?php echo $app->formValue($data['configMailTo'], $_POST['configMailTo']) ?>" style="width:80%;" /></td>
					<td>Destinataire des mails exp�di�s depuis le site</td>
				</tr>
				<tr>
					<td>Copie</td>
					<td><input type="text" name="configMailCc" value="<?php echo $app->formValue($data['configMailCc'], $_POST['configMailCc']) ?>" style="width:80%;" /></td>
					<td>Destinataire en copie des mails exp�di�s depuis le site</td>
				</tr>
				<tr>
					<td>Copie cach�e</td>
					<td><input type="text" name="configMailBcc" value="<?php echo $app->formValue($data['configMailBcc'], $_POST['configMailBcc']) ?>" style="width:80%;" /></td>
					<td>Destinataire invisible des mails exp�di�s depuis le site</td>
				</tr>
				<tr>
					<td width="75">Theme</td>
					<td><select name="defaultIdTheme"><?php
						$theme = $app->dbMulti("SELECT * FROM k_theme");
						foreach($theme as $e){
							$sel = ($e['id_theme'] == $app->formValue($data['defaultIdTheme'], $_POST['defaultIdTheme'])) ? ' selected' : NULL;
							echo "<option value=\"".$e['id_theme']."\"".$sel.">".$e['themeName']."</option>";
						}
					?></select></td>
					<td>Le theme qui est choisi par d�faut (il peut �tre chang� par le chapitre et/ou le module)</td>
				</tr>
				<tr>
					<td>Chapitre</td>
					<td><?php echo
						$app->apiLoad('chapter')->chapterSelector(array(
							'value'		=> $app->formValue($data['defaultIdChapter'], $_POST['defaultIdChapter']),
							'name'		=> 'defaultIdChapter',
							'language'	=> 'fr',
							'one'		=> true
						))
					?></td>
					<td>Le chapitre par d�faut qui est utilis� � l'ouverture du site</td>
				</tr>
				<tr>
					<td>Langue</td>
					<td><select name="defaultLanguage"><?php
						$language = $app->countryGet();
						foreach($language as $e){
							$sel = ($e['iso'] == $app->formValue($data['defaultLanguage'], $_POST['defaultLanguage'])) ? ' selected' : NULL;
							echo "<option value=\"".$e['iso']."\"".$sel.">".$e['countryLanguage']."</option>";
						}
					?></select></td>
					<td>La langue qui est choisie par d�faut � l'ouverture du site</td>
				</tr>
				<tr>
					<td>Format de la date</td>
					<td>
						<select name="dateFormat"><?php
							$dates = array(
								'%A %e %B %G',		// jeudi 13 fevrier 2008
								'%a. %d %b. %g',	// jeu. 9 fev. 2008
								'%e %B %G',			// 13 fevrier 2008
								'%d %B %G',			// 9 Fevrier 2008
								'%d %b %g',			// 9 fev 2008
								'%e/%m/%g',			// 9/2/06
								'%e/%m/%G',			// 09/02/2006
								'%d/%m', 			// 9/2
								'%d-%b-%G', 		// 9-FEV-2006
								'%d-%b-%g', 		// 9-FEV-06
								'%b %G', 			// FEV.-06
								'%e %b %G'  		// 9 Fev 2006
							);
							foreach($dates as $e){
								$sel = ($e == $app->formValue($data['dateFormat'], $_POST['dateFormat'])) ? ' selected' : NULL;
								echo "<option value=\"".$e."\"".$sel.">".utf8_decode(strftime($e))."</option>";
							}
						?></select>
					</td>
					<td>Les dates seront format�es de cette mani�re</td>
				</tr>
				<tr>
					<td>Format de l'heure</td>
					<td>
						<select name="timeFormat"><?php
							$times = array(
								'%R',				// 14:10
								'%R:%S',			// 14:20:30
								'%Hh%M'				// 14h10
							);
							foreach($times as $e){
								$sel = ($e == $app->formValue($data['timeFormat'], $_POST['timeFormat'])) ? ' selected' : NULL;
								echo "<option value=\"".$e."\"".$sel.">".strftime($e)."</option>";
							}
						?></select>
					</td>
					<td>Les heures seront format�es de cette mani�re</td>
				</tr>
				<tr>
					<td>Google Analytics</td>
					<td><input type="text" name="defautAnalytic" value="<?php echo $app->formValue($data['defautAnalytic'], $_POST['defautAnalytic']) ?>" style="width:80%;" /></td>
					<td>ID Numerique du compte Google Analytics (UA-YYYYYYY-XX)</td>
				</tr>
			</tbody>
		</table>
	
		<div class="mar-top-20">
			Il est possible de changer ponctuellement les param�res par d�faut pour certaines nom de domaine<br />
			Note, vous pouvez utiliser des expressions reguli�res pour d�finir un domaine. Exemple (*)?(.)?kappuccino.org pour satisfaire www.kappuccino.org et kappuccino.org
		</div>

		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing mar-top-10">
			<thead>
				<tr>
					<th width="20%"><?php echo $l['DOMAINE_NAME'] ?></th>
					<th width="20%"><?php echo $l['CHAPTER'] ?></th>
					<th width="20%"><?php echo $l['THEME'] ?></th>
					<th width="20%"><?php echo $l['LANGUAGE'] ?></th>
					<th width="20%"><?php echo $l['GOOGLE_ANALYTICS'] ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($dom as $e){ $k = $e['domain'] ?>
				<tr>
					<td><input type="text" name="domain[<?php echo $k ?>][domain]" value="<?php echo $app->formValue($e['domain'], $_POST['domain'][$k]['domain']) ?>" size="35" /></td>
					<td><?php echo
						$app->apiLoad('chapter')->chapterSelector(array(
							'value'		=> $app->formValue($e['id_chapter'], $_POST['domain'][$k]['id_chapter']),
							'name'		=> 'domain['.$k.'][id_chapter]',
							'language'	=> 'fr',
							'one'		=> true,
							'empty'		=> true
						));
					?></td>
					<td>
						<select name="domain[<?php echo $k ?>][id_theme]"><option></option><?php
							foreach($app->dbMulti("SELECT * FROM k_theme") as $t){
								$sel = ($t['id_theme'] == $app->formValue($e['id_theme'], $_POST['domain'][$k]['id_theme'])) ? ' selected' : NULL;
								echo "<option value=\"".$t['id_theme']."\"".$sel.">".$t['themeName']."</option>";
							}
						?></select>
					</td>
					<td>
						<select name="domain[<?php echo $k ?>][language]"><option></option><?php
							foreach($app->countryGet() as $l){
								$sel = ($l['iso'] == $app->formValue($l['language'], $_POST['domain'][$k]['language'])) ? ' selected' : NULL;
								echo "<option value=\"".$l['iso']."\"".$sel.">".$l['countryLanguage']."</option>";
							}
						?></select>
					</td>
					<td><input type="text" name="domain[<?php echo $k ?>][analytic]" value="<?php echo $app->formValue($e['analytic'], $_POST['domain'][$k]['analytic']) ?>" size="20" /></td>
				</tr>
				<?php }�?>
				<tr>
					<td><input type="text" name="domain[new][domain]" value="<?php echo $app->formValue('', $_POST['domain']['new']['domain']) ?>" size="35" /></td>
					<td><?php echo
						$app->apiLoad('chapter')->chapterSelector(array(
							'value'		=> $app->formValue('', $_POST['domain']['new']['id_chapter']),
							'name'		=> 'domain[new][id_chapter]',
							'language'	=> 'fr',
							'one'		=> true,
							'empty'		=> true
						))
					?></td>
					<td>
						<select name="domain[new][id_theme]"><option></option><?php
							foreach($app->dbMulti("SELECT * FROM k_theme") as $e){
								echo "<option value=\"".$e['id_theme']."\">".$e['themeName']."</option>";
							}
						?></select>
					</td>
					<td>
						<select name="domain[new][language]"><option></option><?php
							foreach($app->countryGet() as $e){
								echo "<option value=\"".$e['iso']."\">".$e['countryLanguage']."</option>";
							}
						?></select>
					</td>
					<td><input type="text" name="domain[new][analytic]" value="<?php echo $app->formValue('', $_POST['domain']['new']['analytic']) ?>" size="20" /></td>
				</tr>
			</tbody>
		</table>
		
		<table cellpadding="0" cellspacing="0" border="0" class="listing form mar-top-20">
			<thead>
				<tr>
					<th colspan="2">Param&egrave;tres compl&eacute;mentaires</th>
				</tr>
			</thead>
			<tbody>
			<?php

				function fieldTrace($app, $data, $e){

					$field = $app->apiLoad('field')->fieldForm(
						$e['id_field'],
						$data,
						array(
							'style' => 'width:100%; '.$e['fieldStyle']
						)
					);

					if(preg_match("#richtext#", 	$field)) $GLOBALS['textarea'][]		= 'form-field-'.$e['id_field'];
					if(preg_match("#media\-list#", 	$field)) $GLOBALS['mediaList'][]	= "'form-field-".$e['id_field']."'";



					echo '<tr>';
						echo '<td width="25%">'.$e['fieldName'];
						if($e['is_needed']) echo ' *';
						echo '</td>';
	
						echo '<td>';
							if(preg_match("#richtext#", $field)){
								echo "<br /><a href=\"javascript:toggleEditor('form-field-".$e['id_field']."');\">Activer/D�sactiver l'�diteur</a>";
							}

							echo $field;
						echo '</td>';
					echo '</tr>';
				}
		
				foreach($ext as $e){
					list($pos, $n, $id_field) = explode(':', $e['configName']);

					$field = $app->apiLoad('field')->fieldGet(array(
						'debug'		=> false,
						'id_field'	=> $id_field
					));
		
					fieldTrace($app, $app->formValue($e['configValue'], $_POST['ext'][$e['id_field']]), $field); 
				}	
		
			?></tbody>
		</table>
	</form>

</div></div>

<?php include(COREINC.'/end.php'); ?>

</body>
</html>