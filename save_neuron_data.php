<?php
include("head.html")
?>


<?php 
	//query database to get all the parameters.
	function queryDatabase($arrayForModelPara,$model){
		$server = 'localhost';
	  	$user = 'root';
	  	$pass = '';
	  	$db = 'WebInterface';

	  	try{
	  		//create connection
		  	$connection = mysqli_connect("$server",$user,$pass,$db);
		  	//$_POST['model'] is the selected model from the previous page
		  	//since the table is named with the same model we can select table with the model name
		  	$result = mysqli_query($connection, "select * from $model") 
					or die("No model found!!!!".mysql_error());
			$loopCounter = 0;
			$noOfFields = 0;
			if(mysqli_num_rows($result)>0){
				while($row = mysqli_fetch_assoc($result)){

					//echo "Model ID: ".$row['ModelID']."---Model Name: ".$row['Name']." "."<br>";
					$arrayForModelPara[$loopCounter][0] = $row['Name']; //first element of 2d array is para name and second column is the typical value
					//eg: [[Absolute_refractory_period 6.0]]
					$arrayForModelPara[$loopCounter][1] = $row['TypicalVal'];
					//echo count($arrayForModelName);
					$arrayForModelPara[$loopCounter][2] = $row['ModelID'];
					$loopCounter++;
				}
			}
			return $arrayForModelPara;
			mysqli_close($connection);
		  	}

	  	catch(Exception $e){
	  		echo "Cannot establish connection !!";
	  	}

	}



 ?>

<div class = "container">
	<div class="col-sm-12">
		<h6><font color = "#52a25e">System Builder->Simulation Parameters->NeuronModels->NeuronModelParameter-><b>Creating Initialisation File</b></h6></font>
		<?php
		if ($_SESSION['flag']==1){
			$simNum = $_POST['simNum'];

			//--------------------------------

			$userID = $userLogged . '_'.$simNum;
			$data = new DOMDocument;
			$data->formatOutput = true;
			$dom=$data->createElement("Neuron_Initialisation");
// $xml = simplexml_load_file($userLogged . "/" . $userID . ".xml");

if(file_exists('Libraries/ModelLibrary_metadata.xml')){ #Load XML file
	$ModelLibrary = simplexml_load_file ("Libraries/ModelLibrary_metadata.xml");
}
else {
	exit ('Could not load the file...');
}
?>
 <?php
 $neuronlistPath = "SimulationXML/".$userLogged . "/NeuronList.txt";
$myfile = fopen($neuronlistPath, "w") or die("Unable to open file!");
?>

<form action="topology.php" method="post">
	<?php
##################################################################################################
	$DevicesWithExactNumOfNeurons= intval($_POST['totalNeurons']/8);
	if($_POST['totalNeurons']%8 > 0){
		$extraDevice = 1;
	}
	else{
		$extraDevice = 0;
	}
	$totalRequiredDevice = $DevicesWithExactNumOfNeurons + $extraDevice;

	#echo "total device required: ",($totalRequiredDevice);

	#Multiple devices per simulation
	#calcualting how many devices are required for selected number of neurons
	#each device can only have 8 neurons
	echo "total neurons: ", $_POST['totalNeurons'];
	$arraywithDevNum = array($_POST['totalNeurons']);

	for ($totalNeu=0; $totalNeu <$_POST['totalNeurons'] ; $totalNeu++) { 
		# code...
		#echo $totalNeu;
		#echo "<br>";
		for ($i=$totalRequiredDevice ; $i>0; $i--) { 
			# code...
			#echo "i: ",$i,"<br>";
			if(intval($totalNeu/8) >= intval($i-1)){
				echo "destdev: ",$i,"<br>";
				$destdevice = $i ;
				#cho 'destdevice: ',$destdevice;
				$arraywithDevNum[$totalNeu-1] = $destdevice;
				break;
			}
			#echo "destdevice: ",$destdevice;
		}
	}
	echo "array size: ",sizeof($arraywithDevNum);
	file_put_contents("SimulationXML/".$userLogged . "/DeviceId_" . $userID . ".bin",serialize($arraywithDevNum));
	
	###############################################################################################


	if ($_POST['samemodel']=='yes' and $_POST['totalDiffModelNeurons']==0){


		for ($number = 1; $number <= $_POST['totalNeurons']; $number++){
			#echo 'passed from previous :'.$_POST['neuron'.$number];
			echo "neuron number",$number,"<br>";
			fwrite($myfile, "neuron".$number."\n");
			//fwrite($myfile,'\n');

			#if($DevicesWithExactNumOfNeurons)

			?>
			<input type="hidden" value=<?php echo $_POST['neuron'.$number]; ?> name=<?php echo "neuron".$number; ?>>
			<?php

			$packet=$data->createElement("packet");
			$destdev=$data->createElement("destdevice", $arraywithDevNum[$number-2]);// 1 is the FPGA device
			$packet->appendChild($destdev);
			$sourcedev=$data->createElement("sourcedevice",65532);
			$packet->appendChild($sourcedev);
			$simID = $data->createElement("simID",$simNum);
			$packet->appendChild($simID);
			$command=$data->createElement("command",24);
			$packet->appendChild($command);
			$timestamp=$data->createElement("timestamp",0);
			$packet->appendChild($timestamp);
			$neuronid = $data->createElement("neuronid", $number); #neuron number
			$packet->appendChild($neuronid);
			//$numberofneurons = $data->createElement("numberofneurons", $_POST['totalNeurons']);
			//$packet->appendChild($numberofneurons);
			$modelid=$data->createElement("modelid",$_POST['model']);
			$packet->appendChild($modelid);
			$timestepsize = $data->createElement("timestepsize",1000);
			$packet->appendChild($timestepsize);
			foreach ($ModelLibrary->neuron as $model){
				if ($model->neuronid==$_POST['model']){
					foreach ($model->item as $modelitem){
					// $item=$data->createElement("item");
						$itemid=$data->createElement("itemid",$modelitem->itemid);
						$packet->appendChild($itemid);
						$itemtype=$data->createElement("itemtype",$modelitem->type);
						$packet->appendChild($itemtype);
						$itemdatatype=$data->createElement("itemdatatype",$modelitem->datatype);
						$packet->appendChild($itemdatatype);
						$itemintegerpart=$data->createElement("itemintegerpart",$modelitem->integerpart);
						$packet->appendChild($itemintegerpart);
						$inlsb=$data->createElement("inlsb",$modelitem->inlsb);
						$packet->appendChild($inlsb);
						$inmsb=$data->createElement("inmsb",$modelitem->inmsb);
						$packet->appendChild($inmsb);
						$outlsb=$data->createElement("outlsb",$modelitem->outlsb);
						$packet->appendChild($outlsb);
						$outmsb=$data->createElement("outmsb",$modelitem->outmsb);
						$packet->appendChild($outmsb);
						$itemvalue=$data->createElement("itemvalue",$_POST["item" . $modelitem->itemid]);
						$packet->appendChild($itemvalue);
					// $packet->appendChild($item);
					}
				}
			}
			$dom->appendChild($packet);
		}
		$data->appendChild($dom);
		$filename="SimulationXML/".$userLogged . "/Neuron_Ini_file_" . $userID . ".xml";
		$data->save($filename);
		?>



	<br>
	<input type="hidden" name="totalNeurons" value=<?php echo $_POST['totalNeurons']; ?>>
	<input type="hidden" value=<?php echo $simNum; ?> name="simNum">
	<br>
	<input type="submit" value="Create topology">
</form><br><br>
<?php
}

else{
//for mixed group of same  and different neurons
	$subtractedSameModel = 0;
	if($_POST['totalNeurons']>$_POST['totalDiffModelNeurons']){
//echo "nxt stage";
		$subtractedSameModel= $_POST['totalNeurons'] - $_POST['totalDiffModelNeurons'];


		for ($number = 1; $number < $subtractedSameModel + 1; $number++){
			fwrite($myfile,"neuron".$number."\n");
			//fwrite($myfile,'\n');
			?>

			<input type="hidden" value=<?php echo "neuron".$number; ?> name=<?php echo "neuron".$number; ?>>
			<?php 
			$packet=$data->createElement("packet");
			//$destdev=$data->createElement("destdevice",$_POST['name'.$number]+1);
			$destdev=$data->createElement("destdevice",  $arraywithDevNum[$number-2]);//temporary
			$packet->appendChild($destdev);
			$sourcedev=$data->createElement("sourcedevice",65532);
			$packet->appendChild($sourcedev);
			$simID = $data->createElement("simID",$simNum);
			$packet->appendChild($simID);
			$command=$data->createElement("command",24);
			$packet->appendChild($command);
			$timestamp=$data->createElement("timestamp",0);
			$packet->appendChild($timestamp);
			$neuronid = $data->createElement("neuronid", $number); #neuron number
			$packet->appendChild($neuronid);
			$modelid=$data->createElement("modelid",$_POST['model']);
			$packet->appendChild($modelid);

			foreach ($ModelLibrary->neuron as $model){
				if ($model->neuronid==$_POST['model']){
					foreach ($model->item as $modelitem){
			// $item=$data->createElement("item");
						$itemid=$data->createElement("itemid",$modelitem->itemid);
						$packet->appendChild($itemid);
						$itemtype=$data->createElement("itemtype",$modelitem->type);
						$packet->appendChild($itemtype);
						$itemdatatype=$data->createElement("itemdatatype",$modelitem->datatype);
						$packet->appendChild($itemdatatype);
						$itemintegerpart=$data->createElement("itemintegerpart",$modelitem->integerpart);
						$packet->appendChild($itemintegerpart);
						$inlsb=$data->createElement("inlsb",$modelitem->inlsb);
						$packet->appendChild($inlsb);
						$inmsb=$data->createElement("inmsb",$modelitem->inmsb);
						$packet->appendChild($inmsb);
						$outlsb=$data->createElement("outlsb",$modelitem->outlsb);
						$packet->appendChild($outlsb);
						$outmsb=$data->createElement("outmsb",$modelitem->outmsb);
						$packet->appendChild($outmsb);
						$itemvalue=$data->createElement("itemvalue",$_POST["item" . $modelitem->itemid]);
						$packet->appendChild($itemvalue);
			// $packet->appendChild($item);
					}
				}
			}
			$dom->appendChild($packet);
		}
		$data->appendChild($dom);
		$filename="SimulationXML/".$userLogged . "/Neuron_Ini_file_" . $userID . ".xml";
		$data->save($filename);

}//end of if statement

//writing for different models
//echo $_POST['name'.$number];
for ($number = 1; $number < $_POST['totalDiffModelNeurons'] + 1; $number++){
	fwrite($myfile, "neuron".($number+$subtractedSameModel)."\n");
	$neuronidNum= $number+$subtractedSameModel;
	echo "neronid," ,$neuronidNum
	//fwrite($myfile,'\n');
	?>
	
	<input type="hidden" value=<?php echo "neuron".($number+$subtractedSameModel); ?> name=<?php echo "neuron".($number+$subtractedSameModel); ?>>
	<?php 
	$packet=$data->createElement("packet");
//$destdev=$data->createElement("destdevice",$_POST['name'.$number]+1);
	$destdev=$data->createElement("destdevice", $arraywithDevNum[$neuronidNum-2]);
	$packet->appendChild($destdev);
	$sourcedev=$data->createElement("sourcedevice",65532);
	$packet->appendChild($sourcedev);
	
	$simID = $data->createElement("simID",$simNum);
	$packet->appendChild($simID);

	$command=$data->createElement("command",24);
	$packet->appendChild($command);
	$timestamp=$data->createElement("timestamp",0);
	$packet->appendChild($timestamp);
	$neuronid = $data->createElement("neuronid", $neuronidNum); #neuron number
	$packet->appendChild($neuronid);
$modelid=$data->createElement("modelid",$_POST['model' . ($number+$subtractedSameModel)]); // the model number are same model num + . eg if there are 3 same 
//models then starting index for diff model is 3+1 = 4 and 5,6....
$packet->appendChild($modelid);

foreach ($ModelLibrary->neuron as $model){
	if ($model->neuronid==$_POST['model' . ($number+$subtractedSameModel)]){
		foreach ($model->item as $modelitem){
// $item=$data->createElement("item");
			$itemid=$data->createElement("itemid",$modelitem->itemid);
			$packet->appendChild($itemid);
			$itemtype=$data->createElement("itemtype",$modelitem->type);
			$packet->appendChild($itemtype);
			$itemdatatype=$data->createElement("itemdatatype",$modelitem->datatype);
			$packet->appendChild($itemdatatype);
			$itemintegerpart=$data->createElement("itemintegerpart",$modelitem->integerpart);
			$packet->appendChild($itemintegerpart);
			$inlsb=$data->createElement("inlsb",$modelitem->inlsb);
			$packet->appendChild($inlsb);
			$inmsb=$data->createElement("inmsb",$modelitem->inmsb);
			$packet->appendChild($inmsb);
			$outlsb=$data->createElement("outlsb",$modelitem->outlsb);
			$packet->appendChild($outlsb);
			$outmsb=$data->createElement("outmsb",$modelitem->outmsb);
			$packet->appendChild($outmsb);
			$itemvalue=$data->createElement("itemvalue",$_POST["neuron" . ($number+$subtractedSameModel) . "item" . $modelitem->itemid]);
			$packet->appendChild($itemvalue);
// $packet->appendChild($item);
		}
	}
}
$dom->appendChild($packet);
}
$data->appendChild($dom);
$filename="SimulationXML/".$userLogged . "/Neuron_Ini_file_" . $userID . ".xml";
$data->save($filename);	

?>
<br>
<input type="hidden" name="totalNeurons" value=<?php echo $_POST['totalNeurons']; ?>>
<input type="hidden" value=<?php echo $simNum; ?> name="simNum">
<br>
<input type="submit" value="Create topology">
</form><br><br>
<?php
}
fclose($myfile);
echo "Neuronal initialisation data has been saved as ", "Neuron_Ini_file_" . $userID . ".xml";

?>

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
