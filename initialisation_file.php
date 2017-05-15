<?php
include("head.html")
?>

<div class = "container">
	<div class="col-sm-12">
<?php
if ($_SESSION['flag']==1){
	$topo=false;
	$muscle=false;
	$stim=false;
	$simNum =1;
	// $doc1=file($userLogged . "/" . $userLogged . $simNum . ".xml");
	// $doc2=file($userLogged . "/Neuron_Ini_file_" . $userLogged . $simNum . ".xml");
	$xmlDoc1 = new DOMDocument();
	$xmlDoc1->load("SimulationXML/".$userLogged . "/Sim_Ini_file_" . $userLogged . "_".$simNum . ".xml");
	unlink("SimulationXML/".$userLogged . "/Sim_Ini_file_" . $userLogged . $simNum . ".xml");
	$xmlDoc2 = new DOMDocument();
	$xmlDoc2->load("SimulationXML/".$userLogged . "/Neuron_Ini_file_" . $userLogged . $simNum . ".xml");
	unlink("SimulationXML/".$userLogged . "/Neuron_Ini_file_" . $userLogged . $simNum . ".xml");
	if (file_exists("SimulationXML/".$userLogged . "/Topo_Ini_file_" . $userLogged . $simNum . ".xml")){
		$xmlDoc3 = new DOMDocument();
		$xmlDoc3->load("SimulationXML/".$userLogged . "/Topo_Ini_file_" . $userLogged . $simNum . ".xml");
		$topo=true;
		unlink("SimulationXML/".$userLogged . "/Topo_Ini_file_" . $userLogged . $simNum . ".xml");
	}
	if (file_exists("SimulationXML/".$userLogged . "/Muscle_Ini_file_" . $userLogged . $simNum . ".xml")){
		$xmlDoc4 = new DOMDocument();
		$xmlDoc4->load("SimulationXML/".$userLogged . "/Muscle_Ini_file_" . $userLogged . $simNum . ".xml");
		$muscle=true;
		unlink("SimulationXML/".$userLogged . "/Muscle_Ini_file_" . $userLogged . $simNum . ".xml");
	}
	
	if (file_exists("SimulationXML/".$userLogged . "/Stim_Ini_file_" . $userLogged . $simNum . ".xml")){
		$xmlDoc5 = new DOMDocument();
		$xmlDoc5->load("SimulationXML/".$userLogged . "/Stim_Ini_file_" . $userLogged . $simNum . ".xml");
		$stim=true;
		unlink("SimulationXML/".$userLogged . "/Stim_Ini_file_" . $userLogged . $simNum . ".xml");
	}
	
	$dom = new DOMDocument("1.0");
	$dom->formatOutput = true;
	$data=$dom->createElement("newSimulation");
	// Append first packet
	$pack=$dom->createElement("packet");
	$el1=$dom->createElement("destdevice", 0);
	$pack->appendChild($el1);
	$el2=$dom->createElement("sourcedevice", 65532);
	$pack->appendChild($el2);
	$el3=$dom->createElement("command", 15);
	$pack->appendChild($el3);
	$el4=$dom->createElement("timestamp", 0);
	$pack->appendChild($el4);
	$data->appendChild($pack);
	// Append xmlDoc1
	$meta = $xmlDoc1->getElementsByTagName("packet");
	foreach($meta as $packet){
		$packet = $dom->importNode($packet, true);
		$data->appendChild($packet);
	}

	// Append xmlDoc2
	$neuronmeta = $xmlDoc2->getElementsByTagName("packet");
	foreach($neuronmeta as $packet){
		$packet = $dom->importNode($packet, true);
		$data->appendChild($packet);
	}
	
			// Append xmlDoc4
	if ($muscle){
		$musclemeta = $xmlDoc4->getElementsByTagName("packet");
		foreach($musclemeta as $packet){
			$packet = $dom->importNode($packet, true);
			$data->appendChild($packet);
		}
	}
	
		// Append xmlDoc3
	if ($topo){
		$topometa = $xmlDoc3->getElementsByTagName("packet");
		foreach($topometa as $packet){
			$packet = $dom->importNode($packet, true);
			$data->appendChild($packet);
		}
	}

	if ($stim){
		$stimmeta = $xmlDoc5->getElementsByTagName("packet");
		foreach($stimmeta as $packet){
			$packet = $dom->importNode($packet, true);
			$data->appendChild($packet);
		}
	}
	
	$dom->appendChild($data);
	$filename="SimulationXML/".$userLogged . "/Initialisation_file_" . $userID . ".xml";
	$dom->save($filename);


	?>
	<p> The metadata and neuronal XML files will be merged here. The file should be able to be downloaded.</p>
	<a id="cont" href=<?php echo $userLogged . "/Initialisation_file_" . $userLogged . $simNum . ".xml" ;?> download="Initialisation_file.xml">Save initialisation file to your computer</a>
	<br><br>

	<p> The next button will send the file to the server to transform it into HEX and start the simulation.</p>
		
	<form action="Convert_to_HEX.php" method="post">
	<input type="submit" value="Send initialisation data to server">
	<input type="hidden" name="filenameHEX" id = "filenameHEX" value=<?php echo $userLogged . "/Initialisation_file_" . $userID . ".hex" ?>>
	<input type="hidden" name="filenameXML" id = "filenameXML" value=<?php echo $userLogged . "/Initialisation_file_" . $userID . ".xml" ?>>
	</form>	
	<br><br>
	<?php
}
else{
	?>
	<p>You need to log in to see this page:</p>
	<form action="login.php" method="post">
	<input type="submit" value="Log in">
	</form>
	<br><br>
<?php
}
?>

</div>
</div>
<?php
include("end_page.html")
?>