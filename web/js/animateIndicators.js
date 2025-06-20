$(document).ready(function(){

  animateIndicators();

});

function animateIndicators() {

  const CountUpOptions = {
    startVal: 0, // number to start at (0)
    decimalPlaces: 0, // number of decimal places (0)
    duration: 2, // animation duration in seconds (2)
    useGrouping: true, // example: 1,000 vs 1000 (true)
    useEasing: true, // ease animation (true)
    separator: ',', // grouping separator (',')
    decimal: '.', // decimal ('.')
    enableScrollSpy: true, // start animation when target is in view
    // scrollSpyDelay?: number, // delay (ms) after target comes into view
    scrollSpyOnce: true, // run only once
  }


  $(".animate-indicator").each(function(index) {
      // endVal: string
      let endVal = $(this).attr('data-target');

      // check if number
      if (!isNaN(endVal) && !isNaN(parseFloat(endVal))) {
        // check if float and adjust decimal places
        CountUpOptions.decimalPlaces = (endVal.indexOf('.') != -1) ? endVal.split(".")[1].length : 0;
        const indicator = new countUp.CountUp(this, endVal, CountUpOptions);
      }

  });
}
