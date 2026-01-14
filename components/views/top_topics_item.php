<div id="top_topics" class="row grey-text">
    <div class="text-left col-md-2" style="font-size: 1.2em;">
        Key topics <small><i class="fa fa-info-circle" aria-hidden="true" title="List of the most common topics related to the results displayed."></i></small>: 
        <button type="button" class="btn btn-xs btn-default" id="visualize-topic-evolution-btn" data-toggle="modal" data-target="#top-topics-modal" title="Visualize topic evolution" style="margin-left: 10px;">
            <i class="fa-solid fa-chart-line"></i> Visualize topic evolution
        </button>
    </div>
    <div id="top_topics_in_results" class="col-md-10">
        <!-- This will be populated by AJAX -->
        Loading...
    </div>
</div>

<!-- Modal -->
<div id="top-topics-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title main-green" id="topicModalLabel" style="font-size: 1.2em;">Topic Evolution (Last 10 Years)</span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Loading...
            </div>
        </div>
    </div>
</div>
