<div class="fnName fnNameFirst">Le chargement des fichiers</div>

<div class="fnDesc">
	Les fichiers utilis�s pour afficher du contenu sont plac�s dans diff�rents dossier.<br />
	Un  design pattern d'inspiration "MVC" est utilis� pour manipuler les infos, mais il n'est
	pas aussi pr�cis que le mod�le MVC au sens strict.<br /><br />

	Ici on se contente d'avoir:<br />
	- une vue (qui affiche les donn�es)<br />
	- controlleur (qui recup�re/insert les donn�es dans la base de donn�es pour les fournir � la vue).
</div>

<div class="fnName">Ordre des fichiers</div>

<div class="fnDesc">
	<b>1 :</b>
	Le premier fichier qui appel tous les autres se trouve dans le dossier /app.<br />
	Le fichier index.php (commodit�)<br /><br />
	Ce fichier permet de regler plusieurs variables syst�me, initialiser Kodeine
	et permet de lancer la vue pour le theme. (vous ne devriez pas modifier ce fichier)
</div>
<pre class="fnSample">/app/index.php</pre>

<div class="fnDesc">
	<b>2 :</b>
	Le second fichier qui est appel� se trouve dans le theme. Il est imp�ratif de
	conserver le m�me nom pour tous les themes.<br /><br />
	Ce fichier permet de param�trer la mani�re dont la vue sera affich�
</div>
<pre class="fnSample">/user/theme/montheme/html.build.php</pre>



<div class="fnName">Autres fichiers utilis�s</div>

<div class="fnDesc">
	D'autre fichier sont utilis� pour rendre la vue de la page.<br /><br />
	Il y a 2 autres fichiers utilis�s pour modifier le comportement de l'affichage
</div>
<pre class="fnSample">
controller	= /user/montheme/controller/module/fichier
view		= /user/montheme/view/module/fichier
</pre>

<div class="fnDesc">
	<b>CONTROLLER</b><br />
	Ce fichier g�re les connections � la base de donn�e, retournes les data, met �
	jour ou insert des infos.<br />
	Ce fichier ne doit pas afficher de HTML.<br /><br />

	<b>VIEW</b><br />
	Ce fichier repr�sente la vue HTML qui sera affich�.<br />
	Ce fichier contient le code HTML, et souvent affiche les donn�es qui sont charg� par le CONTROLLER .<br /><br />
</div>

<pre class="fnSample">
Exemple pour l'URL /fr/content/cart

CONTROLLER	/theme/montheme/interface/controller/content/cart.php
SHARE 		/app/share/content/cart.php

Si le fichier CONTROLLER existe
	le fichier CONTROLLER est charg�
	
	Si le fichier VIEW existe il est charg�

Si non,
	
	le fichier VIEW existe il est charg�

Si non
	on affiche le dump 404 qui pr�cise pourquoi aucune fichier ne peut �tre charg�
</pre>


