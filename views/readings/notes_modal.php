<div class="modal-content">
    <div class="modal-header">
        <div style ="display:flex; justify-content:space-between">
            <div>
                <!-- title -->
                <div>
                    <?= ($paper['title'] == '') ? 'N/A' : $paper['title'] ?>
                </div>
                <!-- venue-year -->
                <div class="year-venue-bookmarks">
                    <!-- venue -->
                    <?= ($paper['journal'] == '') ? 'N/A' : $paper['journal'] ?>
                    &middot;
                    <!-- year -->
                    <?= ($paper['year'] == '') ? 'N/A' : $paper['year'] ?>
                </div>
                <!-- tags -->
                <div class="tag-region">
                    <div class="bootstrap-tagsinput">
                        <?php if ($tags !== '') : ?>
                            <i class="fa fa-tags" aria-hidden="true"></i>
                            <?php foreach (explode(',', $tags) as $tag) { ?>
                                <span class="tag label"><?= $tag ?></span>
                            <?php } ?>
                        <?php endif ?>
                    </div>
                </div>
            </div>
            <?php if (isset($reading_status)): ?>
                <div style="flex: 0 0 15%; text-align: right; margin-left: 5px;">
                    <div class="reading-status-notes-div reading-status-color" data-color = "<?= $reading_status?>" >
                        <?php
                        $reading_fields = Yii::$app->params['reading_fields'];
                        echo $reading_fields[$reading_status]
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-right">
            <span id="insights-loading" style="display:none; margin-left:10px;">
                <i class="fa fa-spinner fa-pulse"></i> Processing... this may take a few minutes
            </span>
            <button 
            type="button" 
            class="btn btn-default btn-xs"
            id="generate-insights"
            data-pdf-url=""
            title=""
            disabled
            >
            <i class="fa fa-magic"></i> Generate Insights
            </button>

        </div>



    </div>

    <div class="modal-body">
        <!-- temporary message during tinymce initiation -->
        <span id="loading-notes-message">
                  <center><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br/><br/>
                  Loading (it may take a couple of seconds)...</center>
        </span>
        <textarea id='notes-area' style = 'visibility: hidden; height: 1px;' ><?= $editor_content ?></textarea>

    </div>
    <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" id="save-notes" class="btn btn-success ">Save</button>

    </div>
</div>


