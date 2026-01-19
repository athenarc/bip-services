<?php

/*
 *  Reproducibility badges view!
 *
 * (First Version: January 2025)
 *
 */

?>

<span class="reproducibility-badges">
    <?php if ($has_dataset): ?>
        <span role="button" class="reproducibility-badge" data-toggle="popover" data-placement="auto" data-hover-title="Dataset(s) available" title="<b>Dataset(s) available</b>" data-content="Key research datasets relevant to the reproducibility or replicability of this work are available.">
            <i class="fa fa-certificate" aria-hidden="true"></i>
            <span class="badge-letter">D</span>
        </span>
    <?php endif; ?>
    <?php if ($has_software): ?>
        <span role="button" class="reproducibility-badge" data-toggle="popover" data-placement="auto" data-hover-title="Software available" title="<b>Software available</b>" data-content="Software tools relevant to the reproducibility or replicability of this work are available.">
            <i class="fa fa-certificate" aria-hidden="true"></i>
            <span class="badge-letter">S</span>
        </span>
    <?php endif; ?>
</span>
