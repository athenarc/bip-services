<div id="top_topics" class="row grey-text" style="margin-bottom: 15px; align-items: center;">
    <div class="col-md-12" style="display: flex; align-items: center; flex-wrap: nowrap; gap: 10px; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;">
            <div style="display: flex; align-items: center; gap: 5px; font-size: 1.2em; font-weight: 500; white-space: nowrap; flex-shrink: 0;">
                <span>Key topics</span>
                <i class="fa fa-info-circle" aria-hidden="true" title="List of the most common topics related to the results displayed." style="font-size: 0.9em; opacity: 0.7;"></i>
            </div>
            <div id="top_topics_in_results" style="flex: 1; min-width: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <!-- This will be populated by AJAX -->
                <i class="fa fa-spinner fa-spin grey-text"></i>
                <span>Loading...</span>
            </div>
        </div>
        <i class="fa-solid fa-chart-line" id="visualize-topic-evolution-btn" data-toggle="modal" data-target="#top-topics-modal" title="Visualize topic evolution" style="font-size: 1.3em; cursor: pointer; color: var(--main-color, #4CAF50); transition: opacity 0.2s; flex-shrink: 0;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'"></i>
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
