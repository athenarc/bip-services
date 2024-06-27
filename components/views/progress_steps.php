<div class="row bs-wizard" style="border-bottom:0;">
  <?php foreach ($this->context->steps as $step){ ?>
    <div class="<?= $this->context->col_class ?> bs-wizard-step <?= $step['status'] ?>">
      <div class="text-center bs-wizard-stepnum"><?= $step['title'] ?></div>
      <div class="progress"><div class="progress-bar"></div></div>
      <span href="#" class="bs-wizard-dot"></span>
      <?php if($step['status'] === 'active') { ?>
        <div class="bs-wizard-info text-center"><?= $step['message'] ?></div>
      <?php } ?>
    </div>
  <?php } ?>
</div>