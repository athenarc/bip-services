<?php 
use yii\helpers\Html;
use yii\helpers\Url;
?>

<!-- Display the heading. -->
<div class="row">
<div class="col-md-12">
	<h1>Move the bookmark to the folder: </h1>
</div>
</div>

<!-- Display all user folders. -->

<div class="row">
	<div class="col-md-6">
		<form id='move-form' method='post' action='<?=Url::to(['scholar/movefolder'])?>'>
<?php
foreach ($folders as $folder)
{
?>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="fid" value='<?=$folder['id']?>' <?php if ($bookmark->folder_id==$folder['id'] ){echo ' checked="checked"';}?> >
			&nbsp;&nbsp;&nbsp;
			<i class="fa fa-folder-o" aria-hidden="true"></i>&nbsp;
			<?= $folder['name']?>
			<br/>
<?php
}
?>

<!-- Also display the "dummy folder", the one containing all the not-organized.-->
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="fid" value='-1' <?php if (empty($bookmark->folder_id)) {echo ' checked="checked"';}?> >
			&nbsp;&nbsp;&nbsp;
			<i class="fa fa-folder-o" aria-hidden="true"></i>&nbsp
			Not organized
			<br/>


			<br/>
			
			
			
			
			
		</div>
		</div>
		<div class="container">
		<div class='row'>
        <div class='col-md-2'>
		<input type="hidden" name="bookmark_id" value="<?=$bookmark->id?>"/>
		<input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
		<button type="submit" class="btn btn-success">
			<i class="fa fa-arrows" aria-hidden="true"></i> Move
		</button>
		</form>
		</div>
        <div class='col-md-2'>
		<form id='comm-form' method='post' action='<?=Url::to(['site/favorites'])?>'>
	    <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" /> <!--required for security -->
	     <button type="submit" class="btn">
		<i class='fa fa-arrow-left' aria-hidden='true'></i> Back
	    </button>
        </form>
		</div>
</div>
	</div>


