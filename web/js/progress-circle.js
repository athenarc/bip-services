function progressCircle(bar_color, number_color, percentage, div_id) {
    const bluring_size = 1;

    const color = bar_color;

    const radius = 100;
    const border = 10;
    const padding = 30;
    const startPercent = percentage;
    const endPercent = percentage;


    const twoPi = Math.PI * 2;
    const formatPercent = d3.format('.0%');
    const boxSize = (radius + padding) * 2;


    const count = Math.abs((endPercent - startPercent) / 0.01);
    const step = endPercent < startPercent ? -0.01 : 0.01;

    const arc = d3.svg.arc()
        .startAngle(0)
        .innerRadius(radius)
        .outerRadius(radius - border);

    const parent = d3.select(`div#${div_id}`);

    const svg = parent.append('svg')
        .attr('width', boxSize)
        .attr('height', boxSize);

    const defs = svg.append('defs');

    const filter = defs.append('filter')
        .attr('id', 'blur');

    filter.append('feGaussianBlur')
        .attr('in', 'SourceGraphic')
        .attr('stdDeviation', bluring_size);

    const g = svg.append('g')
        .attr('transform', `translate(${ boxSize / 2 },${ boxSize / 2 })`);

    const meter = g.append('g')
        .attr('class', 'progress-meter');

    meter.append('path')
        .attr('class', 'background')
        .attr('fill', '#ccc')
        .attr('fill-opacity', 0.5)
        .attr('d', arc.endAngle(twoPi));

    const foreground = meter.append('path')
        .attr('class', 'foreground')
        .attr('fill', color)
        .attr('fill-opacity', 1)
        .attr('stroke', color)
        .attr('stroke-width', 5)
        .attr('stroke-opacity', 1)
        .attr('filter', 'url(#blur)');

    const front = meter.append('path')
        .attr('class', 'foreground')
        .attr('fill', color)
        .attr('fill-opacity', 1);

    const numberText = meter.append('text')
        .attr('fill', number_color)
        .attr('text-anchor', 'middle')
        .attr('dy', '.35em');

    const progress = startPercent;

    foreground.attr('d', arc.endAngle(twoPi * progress));
    front.attr('d', arc.endAngle(twoPi * progress));
    numberText.text(formatPercent(progress));
}

