<?php

	if(!defined('COREINC')) die('Direct access not allowed');


	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
		subs = la liste de tous les albums
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	function sub($app, $id_album, &$subs){
		$items = $app->dbMulti("SELECT * FROM k_contentalbum WHERE id_album=".$id_album);
		foreach($items as $e){
			$subs[] = $e['id_content'];
			sub($app, $e['id_content'], $subs);
		}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	$id_album   = $_GET['id_album'] ?: -5000;
	$id_content = $_GET['id_content'];
	$pref       = $app->configGet('content');


	# On supprimer tous les ITEMS d'un ALBUM
	#
	if($_GET['action'] == 'removeItemAll'){

		$items = $app->apiLoad('content')->contentGet(array(
			'debug'    => true,
			'id_album' => $id_album,
			'is_item'  => true,
			'id_type'  => $_GET['id_type'],
			'raw'      => true
		));

#		echo count($items);
#		die();

		if(count($items) > 0){

			foreach($items as $e){
				$app->apiLoad('content')->contentRemove($e['id_type'], $e['id_content'], 'fr');

				// Remove linked fil
				$file = KROOT.$e['contentItemUrl'];
				if($pref['galleryItemRemove'] && file_exists($file) && is_file($file)) unlink($file);
			}

			$data['success'] = true;
		}else{
			$data['success'] = false;
		}
	}else


	# On supprime ITEM
	#
	if($_GET['action'] == 'removeItem'){

		$item = $app->apiLoad('content')->contentGet(array(
			'id_content' => $id_content,
			'raw'        => true
		));

		if(!empty($item['id_content'])){

			$app->apiLoad('content')->contentRemove($item['id_type'], $item['id_content'], 'fr');

			// Remove linked fil
			$file = KROOT.$item['contentItemUrl'];
			if($pref['galleryItemRemove'] && file_exists($file) && is_file($file)) unlink($file);

			$data['success'] = true;
		}else{
			$data['success'] = false;
		}

	}else


	# On supprime ALBUM
	#
	if($_GET['action'] == 'removeAlbum'){

		$me = $app->dbOne("SELECT id_type, is_album FROM k_content WHERE id_content = ".$id_content);

		$subs = array($id_content);
		sub($app, $id_content, $subs);

		// Suppression des sous-albums
		foreach($subs as $e){
			$app->apiLoad('content')->contentRemove($_GET['id_type'], $e, 'fr');
		}
		
		// Supprimer les id_content de tous ces albums
		$items = $app->dbMulti("SELECT id_content FROM k_contentitem WHERE id_album IN(".implode(',', $subs).")");
		foreach($items as $e){
			$app->apiLoad('content')->contentRemove($me['id_type'], $e['id_content'], 'fr');
		}

		if($me['is_album']) $app->apiLoad('content')->contentAlbumFamily();

		$data['success'] = true;

	}else


	# Gerer la positions des elements (ALBUM / ITEMS)
	#
	if($_GET['action'] == 'order'){

		$albums = explode('.', $_GET['albums']);
		$items  = explode('.', $_GET['items']);

		foreach($albums as $idx => $id_content){
			if($id_content != NULL){
				$app->dbQuery("UPDATE k_contentalbum SET contentAlbumPos=".$idx." WHERE id_content=".$id_content." AND id_album=".$id_album);
			}
		}

		foreach($items as $idx => $id_content){
			if($id_content != NULL){
				$app->dbQuery("UPDATE k_contentitem SET contentItemPos=".$idx." WHERE id_content=".$id_content." AND id_album=".$id_album);
			}
		}

		$data['success'] = true;

	}else


	# Deplacement d'un ITEM
	#
	if($_GET['action'] == 'moveItem'){

		$last = $app->dbOne("SELECT MAX(contentItemPos) AS la FROM k_contentitem WHERE id_album=".$id_album);
		$app->dbQuery("UPDATE k_contentitem SET id_album=".$id_album.", contentItemPos=".($last['la']+1)." WHERE id_content=".$id_content);

		$data['success'] = true;

	}else


	# Deplacement d'un ALBUM
	#
	if($_GET['action'] == 'moveAlbum'){

		$last = $app->dbOne("SELECT MAX(contentAlbumPos) AS la FROM k_contentalbum WHERE id_album=".$id_album);
		$app->dbQuery("UPDATE k_contentalbum SET id_album=".$id_album.", contentAlbumPos=".($last['la']+1)." WHERE id_content=".$id_content);

		$data['success'] = true;

		$app->apiLoad('content')->contentAlbumFamily();
	
	}else
	

	# Modifier la visibilite d'un element (ALBUM / ITEM)
	#
	if($_GET['action'] == 'toggleView'){

		$see = ($_GET['state'] == 'ON') ? '0' : '1';
		$app->dbQuery("UPDATE k_content SET contentSee=".$see." WHERE id_content=".$id_content);

		$data['success'] = true;

	}else


	# Activer un POSTER
	#
	if($_GET['action'] == 'togglePoster'){

		$poster = ($_GET['state'] == 'OFF') ? $id_content : 0;
		$app->dbQuery("UPDATE k_contentalbum SET id_poster=".$poster." WHERE id_content=".$id_album);
		$data['success'] = true;

		if($poster > 0){
			$poster = $app->apiLoad('content')->contentGet(array(
				'id_content' => $poster,
				'raw'		 => true
			));

			if(file_exists(KROOT.$poster['contentItemUrl']) && is_file(KROOT.$poster['contentItemUrl'])){
				$tmp['hasPoster'] = true;

				$opt = array(
					'url'	=> $poster['contentItemUrl'],
					'admin'	=> true,
					'debug'	=> false,
					'cache'	=> true
				);

				if($poster['contentItemWidth'] >= $poster['contentItemHeight']){
					$preview = $app->mediaUrlData(array_merge($opt, array(
						'mode'	=> 'width',
						'value'	=> 300
					)));
				}else{
					$preview = $app->mediaUrlData(array_merge($opt, array(
						'mode'	=> 'height',
						'value'	=> 300
					)));
				}

				$data['preview'] = array(
					'url'    => $preview['img'],
					'width'  => intval($preview['width']),
					'height' => intval($preview['height']),
				);
			}
		}

	}else


	# Sauver le mode de DISPLAY
	#
	if($_GET['action'] == 'toggleDisplay'){
		$app->filterSet('content'.$_GET['id_type'], $_GET['display'], 'display');
		$data['success'] = true;
	}





	else{
		$data['message'] = 'Action non reconnue';
	}


	// Sortie
	$json = $app->helperJsonEncode($data);
	echo $app->helperJsonBeautifier($json);


