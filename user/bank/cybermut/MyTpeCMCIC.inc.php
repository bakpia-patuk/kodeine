<?php

// ----------------------------------------------------------------------------
// Dans le cas ou un m�me marchand d�tient plusieurs num�ros de TPE virtuels,
// plusieurs configurations de TPE peuvent �tre obtenues ici, selon le param�tre $soc . 
// ----------------------------------------------------------------------------
switch ($soc) {

    case "doNotOverwrite":
        $MyTpe = array(
        	"tpe" =>"7654321",
            "soc" => "doNot",
            "key" => "000102030405060708090A0B0C0D0E0F10111213"
		);
	break;

    default:
	    $MyTpe = array(
	    	"tpe" => "0482733",
	    	"soc" => "yovideo",
	    	"key" => "22f3db8c1c4ae25bb5227ca15b923a3b8c09580b"
	    );

	    $MyTpe["retourok"] 	= "http://".$_SERVER['HTTP_HOST']."/fr/banque-ok.html";
	    $MyTpe["retourko"] 	= "http://".$_SERVER['HTTP_HOST']."/fr/banque-error.html";
	    $MyTpe["submit"]   	= "Proc�der au paiement CB";
}

// ----------------------------------------------------------------------------
// Autres infos de configuration de TPE selon les param�tres $soc,$lang
// ----------------------------------------------------------------------------
switch ($lang){
    case "xx":
        $MyTpe["retourok"] = "http://www.google.cn";
        $MyTpe["retourko"] = "http://www.google.tv";
	break;
}
?>
