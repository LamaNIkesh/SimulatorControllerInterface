<?php
include("head.html")
?>

<div class = "container">
	<div class="col-sm-12">
		<h6><font color = "#52a25e">System Builder->Simulation Parameters->NeuronModels->NeuronModelParameter->Creating Initialisation File->Create Topology->Topology Viewer->Save Topology-><b>Save Initialisation file</b></h6></font>
<?php
//to avoid loding problem, not sure of the cause but this seems to get rid of loading entity issu
//still looking into it
libxml_disable_entity_loader(false);
if ($_SESSION['flag']==1){

	//this section appends all three xml files; simulation, neuron initialisation, topology and stimulation (if present)
	//into a single simulation file that is ready to send to the IM 
	$simNum = $_POST['simNum'];
	//Reading simulation id from the database

	echo "simulation number is : ".$simNum;
	//------------------------------------------------

	$topo=false;
	$stim=false;
	
	$userID = $userLogged .'_'.$simNum;
	echo $userID;
	//$doc1=file($userLogged . "/" . $userLogged . $simNum . ".xml");
	//$doc2=file($userLogged . "/Neuron_Ini_file_" . $userLogged . $simNum . ".xml");
	$topology = '';
	#echo "topology: ".$_POST['topology'];
	if($_POST['topology'] == 'layeredTopology'){
		$topology = '/Layered';
	}
	else{	
		$topology = '';
	}


	##################################################################################################
	# Update UserSimulation database with number of neurons, this will be used for results xml parsing
	##################################################################################################

	#Get total number of neurons from Sim_init file already created at the early stage of the network creation
	if(file_exists("SimulationXML/".$userLogged .$topology. "/Sim_Ini_file_" . $userID. ".xml")){ #Load XML file
		$SimInitXML = simplexml_load_file ("SimulationXML/".$userLogged .$topology. "/Sim_Ini_file_" . $userID. ".xml");
		//echo "test";
	}
	#Gives the total neurons 
	echo $SimInitXML->packet->neuronsnum;
	#reading total neuron numbers
	$totalNeurons = $SimInitXML->packet->neuronsnum;
	#reading simulation duration in ms, 
	$simulationTime = $SimInitXML->packet->cyclesNum;

	#print_r($xmlDoc_totalneurons);

	$server = 'localhost';
	$user = 'root';
	$pass = '';
	$db = 'WebInterface';
	try{
		$connection = mysqli_connect("$server",$user,$pass,$db);
		
		$updateNeuronNum = "UPDATE UserSimulation SET NoOfNeurons = '$totalNeurons', SimTime_ms = '$simulationTime' WHERE SimulationId = '$simNum'";
		#mysqli_query($sql);
		if(mysqli_query($connection,$updateNeuronNum) === TRUE){
			echo "Record updated successfully";
		}	
		else{
			echo "Error updating the record: ".$connection->error;
			}	
	}
	catch (Exception $e) {
		echo "error: ".$e->getMessage();
					}





	

	$xmlDoc1 = new DOMDocument();
	$xmlDoc1->load("SimulationXML/".$userLogged .$topology. "/Sim_Ini_file_" . $userID. ".xml");
	
	unlink("SimulationXML/".$userLogged .$topology. "/Sim_Ini_file_" . $userID. ".xml");
	$xmlDoc2 = new DOMDocument();

	$xmlDoc2->load("SimulationXML/".$userLogged . $topology."/Neuron_Ini_file_" . $userID. ".xml");
	
	unlink("SimulationXML/".$userLogged . $topology. "/Neuron_Ini_file_" . $userID . ".xml");
	
	if (file_exists("SimulationXML/".$userLogged .$topology. "/Topo_Ini_file_" . $userID . ".xml")){
		$xmlDoc3 = new DOMDocument();
		$xmlDoc3->load("SimulationXML/".$userLogged . $topology. "/Topo_Ini_file_" . $userID . ".xml");
		
		$topo=true;
		unlink("SimulationXML/".$userLogged . $topology. "/Topo_Ini_file_" . $userID . ".xml");
	}
	
	if (file_exists("SimulationXML/".$userLogged . $topology."/Stim_Ini_file_" . $userID . ".xml")){
		$xmlDoc5 = new DOMDocument();
		$xmlDoc5->load("SimulationXML/".$userLogged . $topology. "/Stim_Ini_file_" . $userID . ".xml");
		$stim=true;
		unlink("SimulationXML/".$userLogged . $topology. "/Stim_Ini_file_" . $userID . ".xml");
	}
	
	$dom = new DOMDocument("1.0");
	$dom->formatOutput = true;
	$data=$dom->createElement("newSimulation");
	// Append first packet----Reset packet----------not needed anymore
	//$pack=$dom->createElement("packet");
	//$el1=$dom->createElement("destdevice", 0);
	//$pack->appendChild($el1);
	//$el2=$dom->createElement("sourcedevice", 65532);
	//$pack->appendChild($el2);
	//$el3=$dom->createElement("command", 15);
	//$pack->appendChild($el3);
	//$el4=$dom->createElement("timestamp", 0);
	//$pack->appendChild($el4);
	//$data->appendChild($pack);
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
	$filename="SimulationXML/".$userLogged . $topology. "/Initialisation_file_" . $userID . ".xml";
	$dom->save($filename);


	?>
	<p> The metadata and neuronal XML files will be merged here. The file should be able to be downloaded.</p>
	<a id="cont" href=<?php echo "SimulationXML/".$userLogged . $topology. "/Initialisation_file_" . $userID. ".xml" ;?> download= <?php echo "Initialisation_file_" . $userID. ".xml"?>>Save initialisation file to your computer</a>
	<br><br>

	<p> The next button will send the file to the server to transform it into HEX and start the simulation.</p>
		
	<form action="PublishToTopic.php" method="post">
	<input type="submit" value="Send initialisation data to server">
	<input type="hidden" name="filenameHEX" id = "filenameHEX" value=<?php echo $userLogged . $topology."/Initialisation_file_" . $userID . ".hex" ?>>
	<input type="hidden" name="filenameXML" id = "filenameXML" value=<?php echo $filename ?>>
	<input type="hidden" value=<?php echo $simNum; ?> name="simNum">
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
