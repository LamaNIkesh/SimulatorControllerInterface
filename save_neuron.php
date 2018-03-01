<?php
//head included
include("head.html")
?>

<?php 
	
	//function to read database and return the list of neuron models present
	function queryDatabase($arrayForModelPara){
	$server = 'localhost';
  	$user = 'root';
  	$pass = '';
  	$db = 'WebInterface';

  	try{
  		//create connection
	  	$connection = mysqli_connect("$server",$user,$pass,$db);
	  	//$_POST['model'] is the selected model from the previous page
	  	//since the table is named with the same model we can select table with the model name
	  	$result = mysqli_query($connection, "select * from ".$_POST['model']."") 
				or die("No model found!!!!".mysql_error());
		$loopCounter = 0;
		$noOfFields = 0;
		if(mysqli_num_rows($result)>0){
			while($row = mysqli_fetch_assoc($result)){

				echo "Model ID: ".$row['ModelID']."---Model Name: ".$row['Name']." "."<br>";
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

#echo $arrayForModelPara[1][1];
#echo count($arrayForModelPara);

?>


<div class = "container">
	<div class="col-sm-12">
		<h6><font color = "#52a25e">System Builder->Simulation Parameters->NeuronModels-><b>NeuronModelParameter</b></h6></font>
<?php
//echo $_POST['samemodel'];
if ($_SESSION['flag']==1){
	$simNum = $_POST['simNum'];

	if ($_POST['samemodel']=='yes' and $_POST['totalDiffModelNeurons'] == 0){

		//Lets get the model parameters
		$arrayForModelPara = array(array());
		$arrayForModelPara = queryDatabase($arrayForModelPara); //return 2D array with parameter name and value for user input
		#echo $arrayForModelPara[1][1];
		#echo count($arrayForModelPara);



		/*if ($_POST['model']==1){$modelname="Integrate and fire";}
		if ($_POST['model']==2){$modelname="Leaky integrate and fire";}
		if ($_POST['model']==3){$modelname="Izhikevich";}*/
		?><p>There are <?php echo $_POST['totalNeurons']; ?> neurons to be processed with the same model.
		<br><br> The typical values for the <?php echo $_POST['model']; ?> model are: </p>
		<form action="save_neuron_data.php" method="post">
			
			<?php
			for ($number = 1; $number < $_POST['totalNeurons']+1; ++$number){
				?>
				<input type="hidden" name=<?php echo "neuron".$number?> value=<?php echo $_POST['neuron'.$number]; ?>>
				<?php

			}
			?>

			<input type="hidden" name="model" value=<?php echo $_POST['model']; ?>>
			<!--keeping the to neuron for the next file save_neuron_data-->
			<input type="hidden" name="totalNeurons" value=<?php echo $_POST['totalNeurons']; ?>>
			<input type="hidden" name="totalDiffModelNeurons" value=<?php echo $_POST['totalDiffModelNeurons']; ?>>
			<input type="hidden" value=<?php echo $_POST['samemodel']; ?> name="samemodel">
			<input type="hidden" value=<?php echo $simNum; ?> name="simNum">

			<?php
			/*
			foreach ($ModelLibrary->neuron as $model)
			{
				if ($model->neuronid==$_POST['model']){
					foreach ($model->item as $item){
						$DataItem= str_replace("_", " ", $item->name);
						?>
						<div class="col-sm-4">
							<?php echo $index,")"; ?> <?php echo $DataItem; ?>:</div><div class="col-sm-8"><input type="number" name=<?php echo "item" . $item->itemid; ?> value=<?php echo $item->typicalvalue; ?> required></div><br><br>
						<?php
				$index++;
					}
				}
			}
			*/

			for ($i=0; $i <count($arrayForModelPara) ; $i++) { 
				#this array contains how many parameters for selected model
				/*
					the way it is stored is:
								[0]				  [1]		  [2]	
					------------------------------------------------
					|absolute_refractory_period | 6.0		| 1    |
					----------------------------|-------------------
				*/
				?>
						<div class="col-sm-4">
							<!-- grabbing parameters and default values for each parameter from the database -->
							<?php echo ($i+1) ,")"; ?> <?php echo $arrayForModelPara[$i][0]; ?>:</div>
							<div class="col-sm-8">
								<input type="number" name=<?php echo "item" . $arrayForModelPara[$i][2]; ?> value=<?php echo $arrayForModelPara[$i][1]; ?> required>
							</div>
							<br><br>
						<?php
				


			}

	?>
	<input type="submit" value="Next">
	</form><br><br>
	<?php
	} //end of if samemodel==true

	else{
		//deals with different models with combination of same models too

		if($_POST['totalNeurons']>$_POST['totalDiffModelNeurons']){


			//Lets get the model parameters
			$arrayForModelPara = array(array());
			$arrayForModelPara = queryDatabase($arrayForModelPara); //return 2D array with parameter name and value for user input
			#echo $arrayForModelPara[1][1];
			#echo count($arrayForModelPara);


			//echo "nxt stage";
			$subtractedSameModel= $_POST['totalNeurons'] - $_POST['totalDiffModelNeurons'];
			/*if ($_POST['model']==1){$modelname="Integrate and fire";}
			if ($_POST['model']==2){$modelname="Leaky integrate and fire";}
			if ($_POST['model']==3){$modelname="Izhikevich";}*/
			?><p>There are <?php echo $subtractedSameModel; ?> neurons to be processed with the same model.
			<br><br> <legend>The typical values for the <?php echo $_POST['model']; ?> model are: </legend></p>
			<form action="save_neuron_data.php" method="post">

				<?php
				for ($loopCounter = 1; $loopCounter < $subtractedSameModel+1; $loopCounter++){
					?>
					<input type="hidden" name=<?php echo "neuron".$loopCounter?> value=<?php echo $_POST['neuron'.$loopCounter]; ?>>
					<?php
					//echo "name ", $_POST['name'.$number];

				}
				?>

				<input type="hidden" name="model" value=<?php echo $_POST['model']; ?>>
				
				<!--keeping the to neuron for the next file save_neuron_data-->
				<input type="hidden" name="totalNeurons" value=<?php echo $_POST['totalNeurons']; ?>>

				<input type="hidden" value=<?php echo $_POST['samemodel']; ?> name="samemodel">
				<input type="hidden" value=<?php echo $simNum; ?> name="simNum">

				<?php
				$index = 1;
				foreach ($ModelLibrary->neuron as $model)
				{
					if ($model->neuronid==$_POST['model']){
						foreach ($model->item as $item){
							$DataItem= str_replace("_", " ", $item->name);
							?>
							<div class="col-sm-4">
								<?php echo $index,")"; ?> <?php echo $DataItem; ?>:
							</div>
							<div class="col-sm-8">
								<input type="number" name=<?php echo "item" . $item->itemid; ?> value=<?php echo $item->typicalvalue; ?> required>
							</div>
							<br><br>
						<?php
					$index++;
						}
					}
				} //endo of first for each ----- these braces are driving me crazy
			?>

		<?php
		} //end of if $_POST['totalNeurons']>$_POST['totalDiffModelNeurons']


	//$list=file("Libraries/neuron_id.txt");
	?><p>There are <?php echo $_POST['totalDiffModelNeurons']; ?> neuron(s) to be processed with different models.</p>

	<?php 
		//Lets get the model parameters
		$arrayForModelPara = array(array());
		$arrayForModelPara = queryDatabase($arrayForModelPara); //return 2D array with parameter name and value for user input
		#echo $arrayForModelPara[1][1];
		#echo count($arrayForModelPara)

	 ?>

	<form action="save_neuron_data.php" method="post">
		<!--<input type="hidden" name="neuron" value=<?php echo $_POST['totalDiffModelNeurons']; ?>>-->
		<input type="hidden" value=<?php echo $_POST['totalDiffModelNeurons']; ?> name="totalDiffModelNeurons">

		<?php
		for ($loopCounter = 1; $loopCounter < $_POST['totalDiffModelNeurons']+1; $loopCounter++){
			//echo $loopCounter;
			$modelNumber = $loopCounter + $subtractedSameModel;
			//echo 'passed model : '.$_POST['model'.$modelNumber];
			if ($_POST['model'.$modelNumber]==1){$modelname="Integrate and fire";}
			if ($_POST['model'.$modelNumber]==2){$modelname="Leaky integrate and fire";}
			if ($_POST['model'.$modelNumber]==3){$modelname="Izhikevich";}

			foreach ($ModelLibrary->neuron as $model){
				if ($model->neuronid==$_POST['model'.$modelNumber]){

					$id=$_POST['name'.($loopCounter + $subtractedSameModel)];
					echo 'id '.$id;
					?><br><fieldset>
					<legend>The typical values for the <?php echo $modelname; ?> model are: </legend>
					<input type="hidden" name=<?php echo 'model'.$modelNumber; ?> value=<?php echo $_POST['model'.$modelNumber]; ?>>
					<input type="hidden" name=<?php echo 'name'.$modelNumber; ?> value=<?php echo $id; ?>><?php
					foreach ($model->item as $item){
						$DataItem= str_replace("_", " ", $item->name);
						?>
						<div class="col-sm-4">
							<?php echo $DataItem; ?>:</div>
							<div class = "col-sm-8"> 
								<input type="number" name=<?php echo "neuron" . $modelNumber . "item" . $item->itemid; ?> value=<?php echo $item->typicalvalue; ?> required>
							</div><br><br>
							<?php
						}
					}?></fieldset><?php
				} //end of foreach
			}?>
			
			<br><input type="submit" value="Next">
		</form><br><br>
		<?php
	} //end of else
	?>
	<?php
} //end of main if

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
