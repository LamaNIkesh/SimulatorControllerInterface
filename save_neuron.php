<?php
include("head.html")
?>

<div class = "container">
	<div class="col-sm-12">

		<?php
		if ($_SESSION['flag']==1){
if(file_exists('Libraries/ModelLibrary_metadata.xml')){ #Load XML file
	$ModelLibrary = simplexml_load_file ("Libraries/ModelLibrary_metadata.xml");
	//echo "test";
}
else {
	exit ('Could not load the file...');
}
//echo $_POST['samemodel'];
if ($_POST['samemodel']=='yes' and $_POST['no_of_diff_neurons'] == 0){
	if ($_POST['model']==1){$modelname="Integrate and fire";}
	if ($_POST['model']==2){$modelname="Leaky integrate and fire";}
	if ($_POST['model']==3){$modelname="Izhikevich";}
	?><p>There are <?php echo $_POST['no_of_neurons']; ?> neurons to be processed with the same model.
	<br><br> The typical values for the <?php echo $modelname; ?> model are: </p>
	<form action="save_neuron_data.php" method="post">
		
		<?php
		for ($number = 1; $number < $_POST['no_of_neurons']+1; ++$number){
			?>
			<input type="hidden" name=<?php echo "name".$number?> value=<?php echo $_POST['name'.$number]; ?>>
			<?php

		}
		?>

		<input type="hidden" name="model" value=<?php echo $_POST['model']; ?>>
		<!--keeping the to neuron for the next file save_neuron_data-->
		<input type="hidden" name="neuron" value=<?php echo $_POST['no_of_neurons']; ?>>

		<input type="hidden" value=<?php echo $_POST['samemodel']; ?> name="samemodel">

		<?php
		$index = 1;
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
?>
<input type="submit" value="Next">
</form><br><br>
<?php
}
else{
	//deals with different models with combination of same models too

	if($_POST['no_of_neurons']>$_POST['no_of_diff_neurons']){
		echo "nxt stage";
		$no_of_same_neurons = $_POST['no_of_neurons'] - $_POST['no_of_diff_neurons'];
		if ($_POST['model']==1){$modelname="Integrate and fire";}
		if ($_POST['model']==2){$modelname="Leaky integrate and fire";}
		if ($_POST['model']==3){$modelname="Izhikevich";}
		?><p>There are <?php echo $no_of_same_neurons; ?> neurons to be processed with the same model.
		<br><br> The typical values for the <?php echo $modelname; ?> model are: </p>
		<form action="save_neuron_data.php" method="post">

			<?php
			for ($number = 1; $number < $no_of_same_neurons+1; ++$number){
				?>
				<input type="hidden" name=<?php echo "name".$number?> value=<?php echo $_POST['name'.$number]; ?>>
				<?php
				//echo "name ", $_POST['name'.$number];

			}
			?>

			<input type="hidden" name="model" value=<?php echo $_POST['model']; ?>>
			
			<!--keeping the to neuron for the next file save_neuron_data-->
			<input type="hidden" name="neuron" value=<?php echo $_POST['no_of_neurons']; ?>>

			<input type="hidden" value=<?php echo $_POST['samemodel']; ?> name="samemodel">

			<?php
			$index = 1;
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
?>

<?php
}


$list=file("Libraries/neuron_id.txt");
?><p>There are <?php echo $_POST['no_of_diff_neurons']; ?> neuron(s) to be processed with different models.</p>
<form action="save_neuron_data.php" method="post">
	<input type="hidden" name="neuron" value=<?php echo $_POST['no_of_diff_neurons']; ?>>
	<input type="hidden" value=<?php echo $_POST['no_of_diff_neurons']; ?> name="no_of_diff_neurons">

	<?php
	for ($number = 1; $number < $_POST['no_of_diff_neurons']+1; ++$number){
		if ($_POST['model'.$number]==1){$modelname="Integrate and fire";}
		if ($_POST['model'.$number]==2){$modelname="Leaky integrate and fire";}
		if ($_POST['model'.$number]==3){$modelname="Izhikevich";}

		foreach ($ModelLibrary->neuron as $model){
			if ($model->neuronid==$_POST['model' . $number]){
				$id=$_POST['name'.$number];
				?><br><fieldset>
				<legend>The typical values for the <?php echo $modelname; ?> model are: </legend>
				<input type="hidden" name=<?php echo 'model'.$number; ?> value=<?php echo $_POST['model'.$number]; ?>>
				<input type="hidden" name=<?php echo 'name'.$number; ?> value=<?php echo $_POST['name'.$number]; ?>><?php
				foreach ($model->item as $item){
					$DataItem= str_replace("_", " ", $item->name);
					?>
					<div class="col-sm-4">
						<?php echo $DataItem; ?>:</div><div class = "col-sm-8"> <input type="number" name=<?php echo "neuron" . $number . "item" . $item->itemid; ?> value=<?php echo $item->typicalvalue; ?> required></div><br><br>
						<?php
					}
				}?></fieldset><?php
			}
		}?>
		
		<br><input type="submit" value="Next">
	</form><br><br>
	<?php
}

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

</div></div>

<?php
include("end_page.html")
?>
