function progressCircle( bar_color, number_color, percentage, div_id)
{
	var bluring_size = 1;
	
	var color = bar_color;

	var radius = 100;
	var border = 10;
	var padding = 30;
	var startPercent = percentage; 
	var endPercent = percentage;


	var twoPi = Math.PI * 2;
	var formatPercent = d3.format('.0%');
	var boxSize = (radius + padding) * 2;


	var count = Math.abs((endPercent - startPercent) / 0.01);
	var step = endPercent < startPercent ? -0.01 : 0.01;

	var arc = d3.svg.arc()
		.startAngle(0)
		.innerRadius(radius)
		.outerRadius(radius - border);

	var parent = d3.select('div#'+div_id);

	var svg = parent.append('svg')
		.attr('width', boxSize)
		.attr('height', boxSize);

	var defs = svg.append('defs');

	var filter = defs.append('filter')
		.attr('id', 'blur');

	filter.append('feGaussianBlur')
		.attr('in', 'SourceGraphic')
		.attr('stdDeviation', bluring_size);

	var g = svg.append('g')
		.attr('transform', 'translate(' + boxSize / 2 + ',' + boxSize / 2 + ')');

	var meter = g.append('g')
		.attr('class', 'progress-meter');

	meter.append('path')
		.attr('class', 'background')
		.attr('fill', '#ccc')
		.attr('fill-opacity', 0.5)
		.attr('d', arc.endAngle(twoPi));

	var foreground = meter.append('path')
		.attr('class', 'foreground')
		.attr('fill', color)
		.attr('fill-opacity', 1)
		.attr('stroke', color)
		.attr('stroke-width', 5)
		.attr('stroke-opacity', 1)
		.attr('filter', 'url(#blur)');

	var front = meter.append('path')
		.attr('class', 'foreground')
		.attr('fill', color)
		.attr('fill-opacity', 1);

	var numberText = meter.append('text')
		.attr('fill', number_color)
		.attr('text-anchor', 'middle')
		.attr('dy', '.35em');

	var progress = startPercent;

	foreground.attr('d', arc.endAngle(twoPi * progress));
	front.attr('d', arc.endAngle(twoPi * progress));
	numberText.text(formatPercent(progress)); 	
}

