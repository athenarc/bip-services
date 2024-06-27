<?php 
use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="container">
	<div class="row">
	<div class="col-md-4">
		<h1>Create a new folder</h1>
	</div>
	</div>

	
	
	<div class='row'>
		<div class='col-md-10'>
			<form id='comm-form' method='post' action='<?=Url::to(['scholar/createfolder'])?>'>
				<b>Folder name:</b>&nbsp;&nbsp;
				<input type="text" id='fname' name='fname'/><br/><br/>
				<input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
		</div>
	</div>
	<div class='row'>
		<div class='col-md-2'>
			<button type="submit" class="btn btn-success">
				<i class="fa fa-folder" aria-hidden="true"></i> Create
			</button>
				</form>
		</div>
		<div class='col-md-2'>
		<form id='comm-form' method='post' action='<?=Url::to(['scholar/favorites'])?>'>
			<input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
			<button type="submit" class="btn">
				<i class='fa fa-arrow-left' aria-hidden='true'></i> Cancel
			</button>
		</form>
		</div>
	</div>
	
	<div class='row'>
	<?php 
		if( isset($err) )
		{
			echo "<p class=\"bg-danger\">";
			echo "Folder creation failed!<br/><br/>";
			foreach($err as $cur_err_entity)
			{
				foreach($cur_err_entity as $cur_prob)
					echo $cur_prob."<br/>";
			}
			echo "<br/>Please resolve the issue and continue...";
			echo "</p>";
		}
	?>
	</div>
</div>