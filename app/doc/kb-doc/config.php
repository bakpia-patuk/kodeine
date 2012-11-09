<div class="fnName fnNameFirst">Configuration de la base de donn�es</div>

<h3>Fichier de configuration</h3>
<pre class="fnSample">
/user/config/db.php
</pre>

<div class="fnArg">
	<table border="1" width="100%" class="tablo">
		<tr>
			<td width="150">$host</td>
			<td>Le nom ou l'IP du serveur auquel se connecter (le plus souvent localhost)</td>
		</tr>
		<tr>
			<td>$login</td>
			<td>L'identifiant utilis� pour se connecter au serveur</td>
		</tr>
		<tr>
			<td>$passwd</td>
			<td>Le mot de passe associ� au login</td>
		</tr>
		<tr>
			<td>$database</td>
			<td>Le nom de la base de donn�es</td>
		</tr>
		<tr>
			<td>$type</td>
			<td>Le type de base de donn�e, pour le moment uniquement MySQL</td>
		</tr>
		<tr>
			<td>$GLOBALS['dblog']</td>
			<td>true | false indique si les erreurs MySQL sont enregistr� dans des fichiers log</td>
		</tr>
	</table>
</div>

<div class="fnName">Configuration suppl�mentaire</div>

<h3>Fichier de configuration</h3>
<pre class="fnSample">
/user/config/app.php
</pre>

<div class="fnArg">
	<p>Ce fichier est charg� pour d�finir des constantes personnalis�es.
	Si les valeurs suivantes ne sont pas d�finir, alors les valeurs par d�faut
	seront utilis� : THEME, TEMPLATE, DBLOG, DUMPDIR, DUMPBIN, IMGENGINE</p>
</div>
