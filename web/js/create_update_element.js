$(document).ready(function(){

  $(".opt-checkbox").prop('disabled', true);

  var initialSelectedType = $("#elements-type").val();
  if (initialSelectedType != null) {
      initialSelectedType = initialSelectedType.replace(' ', '-');
      $(".element-type-section").hide();
      $("#element-type-" + initialSelectedType).show();
  }
  
  $("#elements-type").change(function(){
      var selectedType = $(this).val().replace(' ', '-');
      $(".element-type-section").hide();
      $("#element-type-" + selectedType).show();
  });

  $('#selectDeselectButton').click(function(){
      var checkboxes = $('input[type="checkbox"]');
      checkboxes.prop('checked', !checkboxes.prop('checked'));
  });

  var facetTypes = ['topics', 'roles', 'availability', 'work-type']
  
  facetTypes.forEach(function(facet) {

      var facetClass = "." + facet + '-checkbox';
      var facetOptClass = "." + facet + '-opt-checkbox';
      var checkboxOptsGroup = $(facetOptClass);

      if($(facetClass).is(':checked')){
          checkboxOptsGroup.prop('disabled', false);
      } else {
          checkboxOptsGroup.prop('disabled', true);
      }

      $(facetClass).change(function(){

          if($(this).is(':checked')){
              checkboxOptsGroup.prop('disabled', false);
          } else {
              checkboxOptsGroup.prop('disabled', true);
              checkboxOptsGroup.prop('checked', false);
          }
      });
  });

  // Function to initialize the hidden inputs
  function initializeOrderInputs() {
    var initialSemanticsOrder = [];
    $("#semantics-container .semantics-group").each(function() {
        var semantics = $(this).find(".semantics-heading h4").text().trim();
        if (semantics) {
            initialSemanticsOrder.push(semantics);
        }
    });
    $("#semantics-order").val(JSON.stringify(initialSemanticsOrder));

    logFullIndicatorOrder();
}

  // Function to log the full indicator order
  function logFullIndicatorOrder() {
      var fullIndicatorOrder = [];
      $("#semantics-container .semantics-group").each(function() {
          var semanticsGroup = $(this).find(".semantics-heading").text().trim();
          $(this).find(".indicator-container .indicator-item").each(function() {
              var indicatorId = $(this).attr('id').replace('indicator-', '');
              fullIndicatorOrder.push({ semantics: semanticsGroup, indicator: indicatorId });
          });
      });

      var indicatorOrderArray = fullIndicatorOrder.map(function(entry) {
          return entry.indicator;
      });

      $("#indicator-order").val(JSON.stringify(indicatorOrderArray));
  }

  // Initialize hidden inputs on page load
  initializeOrderInputs();

  // Make the semantics groups sortable
  $("#semantics-container").sortable({
      handle: ".semantics-heading",
      cursor: "move",
      update: function(event, ui) {
          var order = $(this).sortable('toArray', { attribute: 'id' }).map(function(id) {
              return id.replace('semantics-', '').replace(/-/g, ' ');
          });
          order = order.splice(3, 6);
          $("#semantics-order").val(JSON.stringify(order));
          logFullIndicatorOrder();
      }
  });

  // Make the indicator items sortable
  $("#semantics-container .indicator-container").sortable({
      handle: ".indicator-heading",
      axis: "y",
      containment: "parent",
      cursor: "move",
      update: function(event, ui) {
          logFullIndicatorOrder();
      }
  });

  // Collapse/Expand functionality
  $(".toggle-indicators").click(function() {
    var $button = $(this);
    var $indicators = $button.closest(".semantics-group").find(".indicator-container");
    $indicators.toggle();
    $button.text($indicators.is(":visible") ? "Collapse" : "Expand");
  });

  // Collapse All/Expand All functionality
  $(".toggle-all-indicators").click(function(event) {
      event.preventDefault(); // Prevent default action
      var $button = $(this);
      var $indicators = $(".indicator-container");
      var isVisible = $indicators.is(":visible");
      $indicators.toggle(!isVisible);
      $(".toggle-indicators").text(isVisible ? "Expand" : "Collapse");
      $button.text(isVisible ? "Expand All" : "Collapse All");
  });

  // Global combobox functionality
  $(".global-status-combobox").change(function() {
      var status = $(this).val();
      $(".indicator-container select").val(status).change();
  });

  // Individual group combobox functionality
  $(".group-status-combobox").change(function() {
      var status = $(this).val();
      $(this).closest(".semantics-group").find(".indicator-container select").val(status).change();
  });
});