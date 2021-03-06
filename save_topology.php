	<?php
	include("head.html")
	?>

	<div class = "container">
		<div class="col-sm-12">
			<h6><font color = "#52a25e">System Builder->Simulation Parameters->NeuronModels->NeuronModelParameter->Creating Initialisation File->Create Topology->Topology Viewer-><b>Save Topology</b></h6></font>
			<?php
			//saves the topology information into a topology initialisation file

			if ($_SESSION['flag']==1){
				$totalNeurons =  $_POST['neuron'];

				$simNum = $_POST['simNum'];
				
				echo "simulation number is : ".$simNum;

				//--------------------------------------------------
				//-------End of reading simulation id----------------

				//$simNum = 1;
				$userID = $userLogged . '_'.$simNum;
				$numberOfNeurons = 0; //counts the number of neurons from the topology file
										//this neuron number is used only for the stimulation file.
										//since this is non layered, stimulation can be applied to any neurons.
										//so we throw out all the neurons
				$data = new DOMDocument;
				$data->formatOutput = true;
				$dom=$data->createElement("Topology_Initialisation");
				// $xml = simplexml_load_file($userLogged . "/" . $userID . ".xml");

				$Topology = fopen("SimulationXML/".$userLogged . "/Topology.txt", "r") or die("Unable to open file!");
				//reads topology txt file created earlier and use that to generate topology initialisation file
				#########################################################################################
				#reading the saved array from save_neuron-data file, it contains the device id number for each neuron
				//$deviceidarray = unserialize(file_get_contents("SimulationXML/".$userLogged . "/DeviceId_" . $userID . ".bin"));
				
				//This array gives info about which neuron is to be assigned to which FPGA
				//tHIS returns a 2D array
				/*
				$arrayWithNeuronIdModelFPGANum[$counter][0] = $key; //Neuron number at index 0 
				$arrayWithNeuronIdModelFPGANum[$counter][1] = $value; //Model Name at index 1
				$arrayWithNeuronIdModelFPGANum[$counter][2] = $FPGARequired; //FPGA num at index 2*/
				$FinalSortedNeuronsFPGAArray = unserialize(file_get_contents("SimulationXML/".$userLogged . "/FinalSortedNeuronsFPGAArray_" . $userID . ".bin"));

				#print_r($deviceidarray);
				############################################################################################
				while(! feof($Topology))
	  			{
	  				$gettingLine= fgets($Topology);
	  				//to avoid any null values at the end
	  				if($gettingLine == NULL){break;}
	  				//echo $gettingLine;
	  				//separates numbers from spaces and put into an array from the file
	  				$spaceSeparatedConnections = explode(" ",$gettingLine); 
	  				//testing purpose
	  				/*for($i = 0; $i<sizeof($spaceSeparatedConnections);$i++){
	  					echo $i;
	  					echo $spaceSeparatedConnections[$i];
	  					echo "\n";
	  				}*/

	  				$packet=$data->createElement("packet");
					$destdev=$data->createElement("destdevice",$FinalSortedNeuronsFPGAArray[$numberOfNeurons+1][2]); #1 here is the fpga device number
					$packet->appendChild($destdev);
					$sourcedev=$data->createElement("sourcedevice",65532);
					$packet->appendChild($sourcedev);
					
					$simID = $data->createElement("simID",$simNum);
					$packet->appendChild($simID);

					$command=$data->createElement("command",11);
					$packet->appendChild($command);
					$timestamp=$data->createElement("timestamp",0);
					$packet->appendChild($timestamp);
					$neuronid = $data->createElement("neuronid", $spaceSeparatedConnections[0]);
					$packet->appendChild($neuronid);
					$numberofneurons = $data->createElement("numberofneurons", $totalNeurons); //passed on from topo generate
					$packet->appendChild($numberofneurons);
					//this loops from value 1 since value 0 is the desitnation device so we are only
					//interested on the synpases it receives from
					for ($connect = 1; $connect < sizeof($spaceSeparatedConnections) - 1 ; $connect++){
						
							$itemid=$data->createElement("preneuronid", $spaceSeparatedConnections[$connect]);
							$packet->appendChild($itemid);
					
					}
					$dom->appendChild($packet);
					$numberOfNeurons++;


				}

				fclose($Topology);

				$data->appendChild($dom);
				$filename="SimulationXML/".$userLogged . "/Topo_Ini_file_" . $userID . ".xml";
				$data->save($filename);

				echo "Topology initialisation data has been saved as ", "Topo_Ini_file_" . $userID . ".xml";
				?>
				<br><br>
				<p>Other initialisation files could be added before sending the data, such as muscle and stimulation. These features would be eventually added.</p>
				<p> In the case of adding other initialisation files, these buttons will send the user to the adequate page. This procedure might change. </p>
				<form action="select_stim_neurons.php" method="post">
					<br><input type="submit" value="Add stimulus initialisation data">
					<input type="hidden" name="topology" id = "topology" value='nonlayered' ?>>
					<input type = "hidden" name= "totalNeurons" id = "totalNeurons" value = <?php echo $totalNeurons; ?>>
					<input type = "hidden" name = "noOfNeurons" id = "noOfNeurons" value = <?php echo $numberOfNeurons; ?> >
					<input type="hidden" value=<?php echo $simNum; ?> name="simNum">
				</form><br>
				<form action="initialisation_file.php" method="post">
					<input type="hidden" name='topology' id = 'topology' value='nonlayered'>
					<br><input type="submit" value="Create initialisation file">
					<input type="hidden" value=<?php echo $simNum; ?> name="simNum">
				</form><br>
				
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
		</div></div>

		<?php
		include("end_page.html")
		?>
