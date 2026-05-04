<div id="summary_panel" class="collapse row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                <div id="summaryContent" class="grey-text">
                    <div class="summary-controls">
                        <div id="regenerate-summary-box" class="regenerate-summary-box" style="display: none;">
                            <label for="summary-count" class="regenerate-label">Use top</label>
                            <input type="number" id="summary-count" class="regenerate-input" />
                            <label for="summary-count" class="regenerate-label">results.</label>
                            <span 
                                role="button" 
                                data-toggle="popover" 
                                data-placement="auto" 
                                title="AI Summary" 
                                data-content="<p>The summary format will change based on the selected number of papers:</p>
                                <ul>
                                    <li>1-5 papers: Produces a concise overview.</li>
                                    <li>6-20 papers: Creates a more detailed, literature review-style summary.</li>
                                </ul>
                                "
                                style="cursor: pointer;"
                            > 
                                <small><i class="fa fa-info-circle light-grey-link" aria-hidden="true"></i></small>
                            </span>

                            <button id="regenerate-summary-btn" class="btn btn-sm btn-custom-color regenerate-button">Summarize</button>
                        </div>
                        <div class="text-right" id="copy-summary-wrapper" style="display: none;">
                            <a id="copy-summary-btn" class="btn btn-default btn-xs fs-inherit grey-link" role="button" data-toggle="tooltip">
                                <i class="fa fa-copy" aria-hidden="true"></i> Copy to clipboard
                            </a>
                        </div>
                    </div>

                    <div id="summaryLoading" class="text-center summary-loading-centered">
                        <i class="fa fa-spinner fa-spin"></i> Generating summary...
                    </div>

                    <div id="summaryText" style="text-align: justify; display: none;"></div>
                    <div id="summary-usage-info" class="summary-usage-info"></div>
                </div>
            </div>
        </div>
    </div>
</div>

