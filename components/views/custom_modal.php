<div class="modal fade" id="<?= $id ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg ">
        <div class="modal-content article-info">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><span id="<?= $id ?>-title"></span></h4>
            </div>
            <div class="modal-body">
                <span id="<?= $id ?>-contents">
                  <center><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><br/><br/>
                  Loading (it may take a couple of seconds)...</center>
                </span>
            </div>
        </div>
    </div>
</div>